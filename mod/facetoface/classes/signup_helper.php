<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2018 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author  Valerii Kuznetsov <valerii.kuznetsov@totaralearning.com>
 * @package mod_facetoface
 */

namespace mod_facetoface;

use \stdClass;
use mod_facetoface\signup\state\state as state;
use mod_facetoface\signup\state\booked as booked;
use mod_facetoface\signup\state\requested as requested;
use mod_facetoface\signup\state\waitlisted as waitlisted;
use mod_facetoface\signup\state\user_cancelled as user_cancelled;

defined('MOODLE_INTERNAL') || die();

/**
 * Manage signups
 * Create new signup with all required parameters
 */
final class signup_helper {
    /**
     * Attempt to perform signup process.
     * Check that user can sign up must be done separately.
     * @param signup $signup
     * @return signup
     */
    public static function signup(signup $signup) : signup {
        global $DB;

        // User cannot signup - no effect.
        if (!self::can_signup($signup)) {
            throw new \coding_exception("Cannot signup.");
        }

        $trans = $DB->start_delegated_transaction();
        $signup->save();


        $signup->switch_state(booked::class, waitlisted::class, requested::class);

        static::trigger_event($signup);
        static::set_default_job_assignment($signup);
        static::withdraw_interest($signup);

        $trans->allow_commit();

        return $signup;
    }

    /**
     * Check if user can signup.
     *
     * User can signup if it is their initial signup and they match all requirements
     * or if it is subsequential signup and state is cancelled and they match all requirements.
     *
     * @param signup $signup
     * @return bool
     */
    public static function can_signup(signup $signup) : bool {
        // Cannot sign up when already signed up.
        if ($signup->get_state() instanceof booked
            || $signup->get_state() instanceof waitlisted) {
            return false;
        }
        return $signup->can_switch(booked::class, waitlisted::class, requested::class);
    }

    /**
     * Get expected state upon signup
     *
     * @param signup $signup
     * @return bool
     */
    public static function expected_signup_state(signup $signup) : state {
        $oldstate = $signup->get_state();
        if ($oldstate->can_switch(booked::class, waitlisted::class, requested::class)) {
            return $oldstate->switch_to(booked::class, waitlisted::class, requested::class);
        }
        return $oldstate;
    }
    /**
     * Get the reasons a signup is failing
     * @param signup $signup
     * @return array
     */
    public static function get_failures(signup $signup) : array {
        // Cannot sign up when already signed up.
        if ($signup->get_state() instanceof booked
            || $signup->get_state() instanceof waitlisted) {
            return ['addalreadysignedupattendee' => get_string('error:addalreadysignedupattendee', 'mod_facetoface')];
        }
        return $signup->get_failures( booked::class, waitlisted::class, requested::class);
    }

    /**
     * Cancel a users signup to a seminar event.
     * @param signup $signup
     * @param string $cancellationreason
     * @return signup
     */
    public static function user_cancel(signup $signup, string $cancellationreason = '') : signup {
        global $DB;

        // User cannot cancel their own signup - no effect.
        if (!self::can_user_cancel($signup)) {
            throw new \coding_exception("Cannot cancel signup.");
        }

        $seminarevent = $signup->get_seminar_event();
        $trans = $DB->start_delegated_transaction();

        $signup->switch_state(user_cancelled::class);

        // Write or update the cancellation field when necessary/possible.
        if (!empty($cancellationreason)) {
            $params = array('shortname' => 'cancellationnote', 'datatype' => 'text');
            if ($cancelfieldid = $DB->get_field('facetoface_cancellation_info_field', 'id', $params)) {
                $canceldataparams = array('fieldid' => $cancelfieldid, 'facetofacecancellationid' => $signup->get_id());
                if ($DB->record_exists('facetoface_cancellation_info_data', $canceldataparams)) {
                    $DB->set_field('facetoface_cancellation_info_data', 'data', $cancellationreason, $canceldataparams);
                } else {
                    $todb = new stdClass();
                    $todb->data = $cancellationreason;
                    $todb->fieldid = $cancelfieldid;
                    $todb->facetofacecancellationid = $signup->get_id();
                    $DB->insert_record('facetoface_cancellation_info_data', $todb);
                }
            }
        }

        // Remove the calendar entry for the seminar event.
        \mod_facetoface\calendar::remove_seminar_event($seminarevent, 0, $signup->get_userid());

        // Open the spot up for anyone on the waitlist.
        self::update_attendees($seminarevent);

        $trans->allow_commit();

        return $signup;
    }

    /**
     * A simple function to check whether a user has cancelled their signup or not.
     * @param signup $signup
     * @return bool
     */
    public static function is_cancelled(signup $signup) : bool {
        $state = $signup->get_state();
        return $state instanceof \mod_facetoface\signup\state\user_cancelled;
    }

    /**
     * Check if the user can cancel their signup or not.
     * @param signup $signup
     * @return bool
     */
    public static function can_user_cancel(signup $signup) : bool {
        return $signup->can_switch(user_cancelled::class);
    }

    /**
     * Process the attendance records for a seminar event.
     *
     * @param seminar_event $seminarevent
     * @param array         $attendance
     * @return bool
     */
    public static function process_attendance(seminar_event $seminarevent, array $attendance) : bool {
        global $CFG, $USER;
        require_once($CFG->dirroot . '/mod/facetoface/lib.php'); // Necessary for facetoface_grade_item_update()

        foreach ($attendance as $signupid => $value) {
            $signup = new signup($signupid);
            $desiredstate = state::from_code($value);

            if (!$signup->can_switch($desiredstate)) {
                error_log("Seminar: could not switch signup id '$signupid' to '$desiredstate'");
                continue;
            }

            $signup->switch_state($desiredstate);

            $timenow = time();
            $seminar = $seminarevent->get_seminar();
            $facetoface = $seminar->get_properties();

            $grade = new \stdclass();
            $grade->userid = $signup->get_userid();
            $grade->rawgrade = $desiredstate::get_grade();
            $grade->rawgrademin = 0;
            $grade->rawgrademax = 100;
            $grade->timecreated = $timenow;
            $grade->timemodified = $timenow;
            $grade->usermodified = $USER->id;

            // Grade functions stay in lib file.
            if (!facetoface_grade_item_update($facetoface, $grade)) {
                    error_log("F2F: could grade signup '$signupid' as '$grade'");
                    continue;
            }
        }
        return true;
    }

    /**
     * Update attendees status regarding new event settingss
     * @param seminar_event $seminarevent
     */
    public static function update_attendees(seminar_event $seminarevent) {
        if ($seminarevent->is_started()) {
            return;
        }

        $users = facetoface_get_attendees($seminarevent->get_id(), [booked::get_code(), waitlisted::get_code()], true);
        \core_collator::asort_objects_by_property($users, 'timesignedup', \core_collator::SORT_NUMERIC);

        if ($users) {
            // We want to book users from waitlist...
            $oldstate = waitlisted::class;
            $newstate = booked::class;
            // Unless there no sessions, in which case we want to waitlist booked users.
            if (!$seminarevent->is_sessions()) {
                $oldstate = booked::class;
                $newstate = waitlisted::class;
            }

            foreach ($users as $user) {
                $signup = new \mod_facetoface\signup((int)$user->submissionid);
                $signup->set_actorid($signup->get_userid());
                $state = $signup->get_state();
                if ($state instanceof $oldstate) {
                    if ($state->can_switch($newstate)) {
                        $signup->switch_state($newstate);
                    }
                }
            }
        }
    }

    /**
     * Add default job assignment if required
     */
    protected static function set_default_job_assignment(signup $signup) {
        $seminar = $signup->get_seminar_event()->get_seminar();
        $selectjobassignmentonsignupglobal = get_config(null, 'facetoface_selectjobassignmentonsignupglobal');
        $jobassignmentrequired = !empty($selectjobassignmentonsignupglobal) && !empty($seminar->get_selectjobassignmentonsignup());

        if ($jobassignmentrequired) {
            $jobassignment = \totara_job\job_assignment::get_first($signup->get_userid(), false);

            if (!empty($jobassignment)) {
                $signup->set_jobassignmentid((int)$jobassignment->id);
            }
        }
    }

    /**
     * Trigger signup event
     * @param signup $signup
     */
    protected static function trigger_event(signup $signup) {
        $cm = $signup->get_seminar_event()->get_seminar()->get_coursemodule();
        $context = \context_module::instance($cm->id);
        \mod_facetoface\event\session_signup::create_from_signup($signup, $context)->trigger();
    }

    /**
     * Remove user expression of interest since they are already signed up
     *
     * @param signup $signup
     */
    protected static function withdraw_interest(signup $signup) {
        $interest = interest::from_seminar($signup->get_seminar_event()->get_seminar(), $signup->get_userid());
        $interest->withdraw();
    }
}
