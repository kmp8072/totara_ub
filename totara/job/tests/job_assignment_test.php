<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2010 onwards Totara Learning Solutions LTD
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
 * @author Nathan Lewis <nathan.lewis@totaralms.com>
 * @package totara_job
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir . '/formslib.php');

/**
 * This set of tests covers all methods of the job_assignment class.
 *
 * To test, run this from the command line from the $CFG->dirroot.
 * vendor/bin/phpunit --verbose totara_job_job_assignment_testcase totara/job/tests/job_assignment_test.php
 */
class totara_job_job_assignment_testcase extends advanced_testcase {

    private $users = array();

    /**
     * Set up some stuff that will be useful for most tests.
     */
    public function setUp() {
        parent::setup();
        $this->resetAfterTest();
        $this->setAdminUser();

        for ($i = 1; $i <= 10; $i++) {
            $this->users[$i] = $this->getDataGenerator()->create_user();
        }
    }

    /**
     * Tests create(), create_default(), _get().
     *
     * This test (as well as most others) implicitly tests __construct().
     */
    public function test_create_default_and_create_and_get() {
        global $USER;

        // Create a default manager job assignment.
        $timebefore = time();
        $managerja = \totara_job\job_assignment::create_default($this->users[2]->id);
        $timeafter = time();

        // Try accessing each property.
        $this->assertGreaterThan(0, $managerja->id); // Checks that retrieving the id does not cause a failure.
        $this->assertEquals($this->users[2]->id, $managerja->userid);
        $this->assertEquals(get_string('jobassignmentdefaultfullname', 'totara_job', 1), $managerja->fullname);
        $this->assertNull($managerja->shortname);
        $this->assertEquals("1", $managerja->idnumber);
        $this->assertEquals('', $managerja->description);
        $this->assertGreaterThanOrEqual($timebefore, $managerja->timecreated);
        $this->assertLessThanOrEqual($timeafter, $managerja->timecreated);
        $this->assertGreaterThanOrEqual($timebefore, $managerja->timemodified);
        $this->assertLessThanOrEqual($timeafter, $managerja->timemodified);
        $this->assertEquals($USER->id, $managerja->usermodified);
        $this->assertNull($managerja->positionid);
        $this->assertGreaterThanOrEqual($timebefore, $managerja->positionassignmentdate);
        $this->assertLessThanOrEqual($timeafter, $managerja->positionassignmentdate);
        $this->assertNull($managerja->organisationid);
        $this->assertNull($managerja->startdate);
        $this->assertNull($managerja->enddate);
        $this->assertNull($managerja->managerid);
        $this->assertNull($managerja->managerjaid);
        $this->assertEquals('/' . $managerja->id, $managerja->managerjapath);
        $this->assertNull($managerja->tempmanagerid);
        $this->assertNull($managerja->tempmanagerjaid);
        $this->assertNull($managerja->tempmanagerexpirydate);
        $this->assertNull($managerja->appraiserid);
        $this->assertEquals(1, $managerja->sortorder);

        // Create a temporary manager job assignment and check the optional param available in create_default.
        $data = array(
            'shortname' => 'sn',
            'fullname' => 'fn',
            'positionid' => '1234',
        );
        $tempmanagerja = \totara_job\job_assignment::create_default($this->users[3]->id, $data);
        $this->assertEquals($this->users[2]->id, $managerja->userid);
        $this->assertEquals('fn', $tempmanagerja->fullname);
        $this->assertEquals('sn', $tempmanagerja->shortname);
        $this->assertEquals($managerja->idnumber, $tempmanagerja->idnumber); // Shows that idnumber is not site-wide unique.
        $this->assertEquals('1234', $tempmanagerja->positionid);

        // Create a normal job assignment with all the possible data.
        $data = array(
            'userid' => $this->users[1]->id,
            'fullname' => 'fullname1',
            'shortname' => 'shortname1',
            'idnumber' => 'id1',
            'description' => 'description pre-processed',
            'positionid' => 123,
            'organisationid' => 234,
            'startdate' => 1234567,
            'enddate' => 2345678,
            'managerjaid' => $managerja->id, // User 2.
            'tempmanagerjaid' => $tempmanagerja->id, // User 3.
            'tempmanagerexpirydate' => 3456789,
            'appraiserid' => $this->users[4]->id,
        );
        $timebefore = time();
        $jobassignment = \totara_job\job_assignment::create($data);
        $timeafter = time();

        // Check that the correct data was recorded.
        $this->assertGreaterThan(0, $jobassignment->id);
        $this->assertEquals($data['userid'], $jobassignment->userid);
        $this->assertEquals($data['fullname'], $jobassignment->fullname);
        $this->assertEquals($data['shortname'], $jobassignment->shortname);
        $this->assertEquals($data['idnumber'], $jobassignment->idnumber);
        $this->assertEquals($data['description'], $jobassignment->description);
        $this->assertGreaterThanOrEqual($timebefore, $jobassignment->timecreated);
        $this->assertLessThanOrEqual($timeafter, $jobassignment->timecreated);
        $this->assertGreaterThanOrEqual($timebefore, $jobassignment->timemodified);
        $this->assertLessThanOrEqual($timeafter, $jobassignment->timemodified);
        $this->assertEquals($USER->id, $jobassignment->usermodified);
        $this->assertEquals($data['positionid'], $jobassignment->positionid);
        $this->assertGreaterThanOrEqual($timebefore, $jobassignment->positionassignmentdate);
        $this->assertLessThanOrEqual($timeafter, $jobassignment->positionassignmentdate);
        $this->assertEquals($data['organisationid'], $jobassignment->organisationid);
        $this->assertEquals($data['startdate'], $jobassignment->startdate);
        $this->assertEquals($data['enddate'], $jobassignment->enddate);
        $this->assertEquals($this->users[2]->id, $jobassignment->managerid);
        $this->assertEquals($data['managerjaid'], $jobassignment->managerjaid);
        $this->assertEquals('/' . $jobassignment->managerjaid . '/' . $jobassignment->id, $jobassignment->managerjapath);
        $this->assertEquals($this->users[3]->id, $jobassignment->tempmanagerid);
        $this->assertEquals($data['tempmanagerjaid'], $jobassignment->tempmanagerjaid);
        $this->assertEquals($data['tempmanagerexpirydate'], $jobassignment->tempmanagerexpirydate);
        $this->assertEquals($data['appraiserid'], $jobassignment->appraiserid);
        $this->assertEquals(1, $jobassignment->sortorder);

        // Check the description editor is being processed.
        $this->assertEquals($data['description'], $jobassignment->description_editor['text']);
        $this->assertEquals(FORMAT_HTML, $jobassignment->description_editor['format']);
        $this->assertGreaterThan(0, $jobassignment->description_editor['itemid']);

        // Create a second job assignment for a user - will have the next sortorder.
        $ja2 = \totara_job\job_assignment::create_default($data['userid']);
        $this->assertEquals(2, $ja2->sortorder);

        // Check that the idnumber must be unique for a given user.
        try {
            $ja3 = \totara_job\job_assignment::create_default($data['userid'], array('idnumber' => $ja2->idnumber));
            $this->assertEquals(0, 1, 'Exception was not triggered!');
        } catch (Exception $e) {
            $this->assertEquals('Tried to create job assignment idnumber which is not unique for this user', $e->getMessage());
        }

        // Check that both temp manager jaid and expiry date must be specified together.
        try {
            $ja3 = \totara_job\job_assignment::create_default($this->users[9]->id, array('tempmanagerjaid' => $ja2->id));
            $this->assertEquals(0, 1, 'Exception was not triggered!');
        } catch (Exception $e) {
            $this->assertEquals('Temporary manager and expiry date must either both be provided or both be empty in job_assignment::create', $e->getMessage());
        }
        try {
            $ja3 = \totara_job\job_assignment::create_default($this->users[9]->id, array('tempmanagerexpirydate' => time() + YEARSECS * 2));
            $this->assertEquals(0, 1, 'Exception was not triggered!');
        } catch (Exception $e) {
            $this->assertEquals('Temporary manager and expiry date must either both be provided or both be empty in job_assignment::create', $e->getMessage());
        }
    }

    /**
     * Tests calculate_managerjapath().
     */
    public function test_calculate_managerjapath() {
        // Tested indirectly through create and update.
        $teamleaderja = \totara_job\job_assignment::create_default($this->users[3]->id);
        $this->assertEquals('/' . $teamleaderja->id, $teamleaderja->managerjapath);

        $managerja = \totara_job\job_assignment::create_default($this->users[1]->id);
        $managerja->update(array('managerjaid' => $teamleaderja->id));

        $staffja = \totara_job\job_assignment::create_default($this->users[4]->id);
        $staffja->update(array('managerjaid' => $managerja->id));

        $this->assertEquals('/' . $teamleaderja->id . '/' . $managerja->id . '/' . $staffja->id, $staffja->managerjapath);

        // Make sure that loops are not allowed.
        try {
            $teamleaderja->update(array('managerjaid' => $staffja->id));
            $this->assertEquals(0, 1, 'Exception was not triggered!');
        } catch (Exception $e) {
            $this->assertEquals('Tried to create a manager path loop in job_assignment::calculate_managerjapath', $e->getMessage());
        }
        try {
            $managerja->update(array('managerjaid' => $staffja->id));
            $this->assertEquals(0, 1, 'Exception was not triggered!');
        } catch (Exception $e) {
            $this->assertEquals('Tried to create a manager path loop in job_assignment::calculate_managerjapath', $e->getMessage());
        }
        try {
            $staffja->update(array('managerjaid' => $staffja->id));
            $this->assertEquals(0, 1, 'Exception was not triggered!');
        } catch (Exception $e) {
            $this->assertEquals('Tried to create a manager path loop in job_assignment::calculate_managerjapath', $e->getMessage());
        }
    }

    /**
     * Tests get_data().
     */
    public function test_get_data() {
        // TODO update for recent changes.
        global $USER;

        $managerja = \totara_job\job_assignment::create_default($this->users[2]->id);
        $tempmanagerja = \totara_job\job_assignment::create_default($this->users[3]->id);

        $savedata = array(
            'userid' => $this->users[5]->id,
            'fullname' => 'fullname1',
            'shortname' => 'shortname1',
            'idnumber' => 'id1',
            'description' => 'description pre-processed',
            'positionid' => 123,
            'organisationid' => 234,
            'startdate' => 1234567,
            'enddate' => 2345678,
            'managerjaid' => $managerja->id, // User 2.
            'tempmanagerjaid' => $tempmanagerja->id, // User 3.
            'tempmanagerexpirydate' => 3456789,
            'appraiserid' => $this->users[4]->id,
        );
        $timebefore = time();
        $jobassignment = \totara_job\job_assignment::create($savedata);
        $timeafter = time();

        $retrieveddata = $jobassignment->get_data();

        // Check that the correct data was returned.
        $this->assertGreaterThan(0, $retrieveddata->id);
        $this->assertEquals($savedata['userid'], $retrieveddata->userid);
        $this->assertEquals($savedata['fullname'], $retrieveddata->fullname);
        $this->assertEquals($savedata['shortname'], $retrieveddata->shortname);
        $this->assertEquals($savedata['idnumber'], $retrieveddata->idnumber);
        $this->assertGreaterThanOrEqual($timebefore, $retrieveddata->timecreated);
        $this->assertLessThanOrEqual($timeafter, $retrieveddata->timecreated);
        $this->assertGreaterThanOrEqual($timebefore, $retrieveddata->timemodified);
        $this->assertLessThanOrEqual($timeafter, $retrieveddata->timemodified);
        $this->assertEquals($USER->id, $retrieveddata->usermodified);
        $this->assertEquals($savedata['positionid'], $retrieveddata->positionid);
        $this->assertGreaterThanOrEqual($timebefore, $retrieveddata->positionassignmentdate);
        $this->assertLessThanOrEqual($timeafter, $retrieveddata->positionassignmentdate);
        $this->assertEquals($savedata['organisationid'], $retrieveddata->organisationid);
        $this->assertEquals($savedata['startdate'], $retrieveddata->startdate);
        $this->assertEquals($savedata['enddate'], $retrieveddata->enddate);
        $this->assertEquals($this->users[2]->id, $retrieveddata->managerid);
        $this->assertEquals($savedata['managerjaid'], $retrieveddata->managerjaid);
        $this->assertEquals('/' . $savedata['managerjaid'] . '/' . $retrieveddata->id, $retrieveddata->managerjapath);
        $this->assertEquals($this->users[3]->id, $retrieveddata->tempmanagerid);
        $this->assertEquals($savedata['tempmanagerjaid'], $retrieveddata->tempmanagerjaid);
        $this->assertEquals($savedata['tempmanagerexpirydate'], $retrieveddata->tempmanagerexpirydate);
        $this->assertEquals($savedata['appraiserid'], $retrieveddata->appraiserid);
        $this->assertEquals(1, $jobassignment->sortorder);

        $this->assertEquals($savedata['description'], $retrieveddata->description_editor['text']);
        $this->assertEquals(FORMAT_HTML, $retrieveddata->description_editor['format']);
        $this->assertGreaterThan(0, $retrieveddata->description_editor['itemid']);
    }

    /**
     * Tests update() and update_internal().
     */
    public function test_update_and_update_internal() {
        global $USER;

        $createtimebefore = time();
        $jobassignment = \totara_job\job_assignment::create_default($this->users[4]->id);
        $createtimeafter = time();

        $managerja = \totara_job\job_assignment::create_default($this->users[2]->id);
        $tempmanagerja = \totara_job\job_assignment::create_default($this->users[3]->id);

        $updatedata = array(
            'fullname' => 'fullname1',
            'shortname' => 'shortname1',
            'idnumber' => 'idnumber1',
            'description' => 'description pre-processed',
            'positionid' => 123,
            'organisationid' => 234,
            'startdate' => 1234567,
            'enddate' => 2345678,
            'managerjaid' => $managerja->id, // User 2.
            'tempmanagerjaid' => $tempmanagerja->id, // User 3.
            'tempmanagerexpirydate' => 3456789,
            'appraiserid' => $this->users[4]->id,
        );

        $this->setGuestUser(); // A different user is doing the update.
        sleep(1); // Ensure that the time has moved forward.
        $updatetimebefore = time();
        $jobassignment->update($updatedata);
        $updatetimeafter = time();

        $this->assertGreaterThan(0, $jobassignment->id);
        $this->assertEquals($this->users[4]->id, $jobassignment->userid);
        $this->assertEquals($updatedata['fullname'], $jobassignment->fullname);
        $this->assertEquals($updatedata['shortname'], $jobassignment->shortname);
        $this->assertEquals($updatedata['idnumber'], $jobassignment->idnumber);
        $this->assertEquals($updatedata['description'], $jobassignment->description);
        $this->assertGreaterThanOrEqual($createtimebefore, $jobassignment->timecreated);
        $this->assertLessThanOrEqual($createtimeafter, $jobassignment->timecreated);
        $this->assertGreaterThanOrEqual($updatetimebefore, $jobassignment->timemodified);
        $this->assertLessThanOrEqual($updatetimeafter, $jobassignment->timemodified);
        $this->assertEquals($USER->id, $jobassignment->usermodified);
        $this->assertEquals($updatedata['positionid'], $jobassignment->positionid);
        $this->assertGreaterThanOrEqual($updatetimebefore, $jobassignment->positionassignmentdate);
        $this->assertLessThanOrEqual($updatetimeafter, $jobassignment->positionassignmentdate);
        $this->assertEquals($updatedata['organisationid'], $jobassignment->organisationid);
        $this->assertEquals($updatedata['startdate'], $jobassignment->startdate);
        $this->assertEquals($updatedata['enddate'], $jobassignment->enddate);
        $this->assertEquals($this->users[2]->id, $jobassignment->managerid);
        $this->assertEquals($updatedata['managerjaid'], $jobassignment->managerjaid);
        $this->assertEquals('/' . $jobassignment->managerjaid . '/' . $jobassignment->id, $jobassignment->managerjapath);
        $this->assertEquals($this->users[3]->id, $jobassignment->tempmanagerid);
        $this->assertEquals($updatedata['tempmanagerjaid'], $jobassignment->tempmanagerjaid);
        $this->assertEquals($updatedata['tempmanagerexpirydate'], $jobassignment->tempmanagerexpirydate);
        $this->assertEquals($updatedata['appraiserid'], $jobassignment->appraiserid);
        $this->assertEquals(1, $jobassignment->sortorder);

        // Show that positionassignmentdate does not change if the positionid is specified but does not change.
        $previousposassignmentdate = $jobassignment->positionassignmentdate;
        sleep(1);
        $posupdatetimebefore = time();
        $jobassignment->update(array('positionid' => $updatedata['positionid'], 'organisationid' => 777));
        $posupdatetimeafter = time();

        $this->assertGreaterThanOrEqual($posupdatetimebefore, $jobassignment->timemodified);
        $this->assertLessThanOrEqual($posupdatetimeafter, $jobassignment->timemodified);
        $this->assertEquals($updatedata['positionid'], $jobassignment->positionid);
        $this->assertEquals($previousposassignmentdate, $jobassignment->positionassignmentdate);
        $this->assertEquals(777, $jobassignment->organisationid);

        // Make sure that the userid cannot be changed.
        try {
            $jobassignment->update(array('positionid' => $updatedata['positionid'], 'organisationid' => 777));
            $this->assertEquals(0, 1, 'Exception was not thrown!');
        } catch (Exception $e) {
            // Exception was thrown, so all good.
        }

        // Make sure that passing no data doesn't fail and doesn't update the timemodified or usermodified.
        $previoustimemodified = $jobassignment->timemodified;
        $previoususermodified = $jobassignment->usermodified;
        sleep(1);
        $this->setAdminUser();
        $this->assertNotEquals($previoususermodified, $USER->id);
        $jobassignment->update(array()); // Empty array.
        $jobassignment->update((object)array()); // Empty object.

        $this->assertEquals($previoustimemodified, $jobassignment->timemodified);
        $this->assertEquals($previoususermodified, $jobassignment->usermodified);

        // Check that the idnumber must be unique for a given user.
        $previousidnumber = $jobassignment->idnumber;
        $jobassignment->update(array('shortname' => $previousidnumber)); // Can update to the same idnumber, no problem.
        $seconddata = array(
            'userid' => $jobassignment->userid,
            'fullname' => 'newfullname',
            'shortname' => 'newshortname',
            'idnumber' => 'newidnumber',
        );
        \totara_job\job_assignment::create($seconddata); // Create a second job assignment.
        try {
            $jobassignment->update(array('idnumber' => $seconddata['idnumber'])); // Update first to match second.
            $this->assertEquals(0, 1, 'Exception was not triggered!');
        } catch (Exception $e) {
            $this->assertEquals('Tried to update job assignment to a idnumber which is not unique for this user', $e->getMessage());
        }

        // Make sure that update of $jobassignment isn't messing with other job assignment records.
        $managerja = \totara_job\job_assignment::get_with_idnumber($managerja->userid, $managerja->idnumber); // Reload the record from db.
        $this->assertEquals($this->users[2]->id, $managerja->userid);
        $this->assertEquals(get_string('jobassignmentdefaultfullname', 'totara_job', 1), $managerja->fullname); // Default calculated.
        $this->assertNull($managerja->shortname);
        $this->assertEquals(1, $managerja->idnumber);
        $this->assertEquals('', $managerja->description);
        $this->assertGreaterThanOrEqual($createtimebefore, $managerja->timecreated);
        $this->assertLessThanOrEqual($createtimeafter, $managerja->timecreated);
        $this->assertGreaterThanOrEqual($createtimebefore, $managerja->timemodified);
        $this->assertLessThanOrEqual($createtimeafter, $managerja->timemodified);
        $this->assertEquals($USER->id, $managerja->usermodified);
        $this->assertNull($managerja->positionid);
        $this->assertGreaterThanOrEqual($createtimebefore, $managerja->positionassignmentdate);
        $this->assertLessThanOrEqual($createtimeafter, $managerja->positionassignmentdate);
        $this->assertNull($managerja->organisationid);
        $this->assertNull($managerja->startdate);
        $this->assertNull($managerja->enddate);
        $this->assertNull($managerja->managerid);
        $this->assertNull($managerja->managerjaid);
        $this->assertEquals('/' . $managerja->id, $managerja->managerjapath);
        $this->assertNull($managerja->tempmanagerid);
        $this->assertNull($managerja->tempmanagerjaid);
        $this->assertNull($managerja->tempmanagerexpirydate);
        $this->assertNull($managerja->appraiserid);
        $this->assertEquals(1, $managerja->sortorder);

        // Check that both temp manager jaid and expiry date must be specified together.
        try {
            $jobassignment->update(array('tempmanagerjaid' => $tempmanagerja->id));
            $this->assertEquals(0, 1, 'Exception was not triggered!');
        } catch (Exception $e) {
            $this->assertEquals('Temporary manager and expiry date must either both be provided or both be empty in job_assignment::update_internal', $e->getMessage());
        }
        try {
            $jobassignment->update(array('tempmanagerexpirydate' => time() + DAYSECS * 100));
            $this->assertEquals(0, 1, 'Exception was not triggered!');
        } catch (Exception $e) {
            $this->assertEquals('Temporary manager and expiry date must either both be provided or both be empty in job_assignment::update_internal', $e->getMessage());
        }
    }

    /**
     * Tests updated_manager.
     */
    public function test_updated_manager() {
        // TODO Writeme.
    }

    /**
     * Tests update_manager_role_assignments.
     */
    public function test_update_manager_role_assignments() {
        // TODO Writeme.
    }

    /**
     * Tests update_descendant_manager_paths.
     */
    public function test_update_descendant_manager_paths() {
        // TODO Writeme.
    }

    /**
     * Tests updated_temporary_manager.
     */
    public function test_updated_temporary_manager() {
        // TODO Writeme.
    }

    /**
     * Tests delete().
     */
    public function test_delete() {
        global $DB;

        $this->assertEquals(0, $DB->count_records('job_assignment'));

        $u1ja1 = \totara_job\job_assignment::create_default($this->users[1]->id);

        $u2ja1 = \totara_job\job_assignment::create_default($this->users[2]->id);
        $u2ja2 = \totara_job\job_assignment::create_default($this->users[2]->id);
        $u2ja3 = \totara_job\job_assignment::create_default($this->users[2]->id);

        $u3ja1 = \totara_job\job_assignment::create_default($this->users[3]->id);

        $this->assertEquals(5, $DB->count_records('job_assignment'));

        // Only the specified job assignment is deleted for the user.
        \totara_job\job_assignment::delete($u2ja3);
        $this->assertEmpty($u2ja3);
        $this->assertEquals(4, $DB->count_records('job_assignment'));
        $this->assertEmpty($DB->get_records('job_assignment', array('userid' => $this->users[2]->id, 'idnumber' => '3')));

        // Only the specified user's job assignment is deleted, even if other users share the same idnumber.
        \totara_job\job_assignment::delete($u3ja1);
        $this->assertEmpty($u3ja1);
        $this->assertEquals(3, $DB->count_records('job_assignment'));
        $this->assertEmpty($DB->get_records('job_assignment', array('userid' => $this->users[3]->id, 'idnumber' => '1')));
        $this->assertEquals(2, $DB->count_records('job_assignment', array('idnumber' => '1')));

        // TODO Check that role assignments have been updated.
    }

    /**
     * Tests get_with_idnumber().
     */
    public function test_get_with_idnumber() {
        $u1ja1 = \totara_job\job_assignment::create_default($this->users[1]->id);

        $u2ja1 = \totara_job\job_assignment::create_default($this->users[2]->id);
        $u2ja2 = \totara_job\job_assignment::create_default($this->users[2]->id);
        $u2ja3 = \totara_job\job_assignment::create_default($this->users[2]->id);

        $u3ja1 = \totara_job\job_assignment::create_default($this->users[3]->id);

        // User with multiple job assignments, gets the one with the correct short name.
        $retrievedja = \totara_job\job_assignment::get_with_idnumber($u2ja2->userid, $u2ja2->idnumber, false);
        $this->assertEquals($u2ja2, $retrievedja);

        // Several users have the same shortname, gets the correct user's record.
        $retrievedja = \totara_job\job_assignment::get_with_idnumber($u3ja1->userid, $u3ja1->idnumber);
        $this->assertEquals($u3ja1, $retrievedja);

        // Test mustexist true (default).
        try {
            $retrievedja = \totara_job\job_assignment::get_with_idnumber($u3ja1->userid, 'notarealidnumber');
            $this->assertEquals(0, 1, 'Exception not triggered!');
        } catch (Exception $e) {
            $this->assertStringStartsWith('Can not find data record in database', $e->getMessage());
        }

        // Test mustexist false.
        $retrievedja = \totara_job\job_assignment::get_with_idnumber($u3ja1->userid, 'notarealidnumber', false);
        $this->assertNull($retrievedja);
    }

    /**
     * Tests get_with_id().
     */
    public function test_get_with_id() {
        $u1ja1 = \totara_job\job_assignment::create_default($this->users[1]->id);

        $u2ja1 = \totara_job\job_assignment::create_default($this->users[2]->id);
        $u2ja2 = \totara_job\job_assignment::create_default($this->users[2]->id);
        $u2ja3 = \totara_job\job_assignment::create_default($this->users[2]->id);

        $u3ja1 = \totara_job\job_assignment::create_default($this->users[3]->id);

        $retrievedja = \totara_job\job_assignment::get_with_id($u2ja1->id);
        $this->assertEquals($u2ja1, $retrievedja);

        // Test mustexist true (default).
        try {
            $retrievedja = \totara_job\job_assignment::get_with_id(-1);
            $this->assertEquals(0, 1, 'Exception not triggered!');
        } catch (Exception $e) {
            $this->assertStringStartsWith('Can not find data record in database', $e->getMessage());
        }

        // Test mustexist false.
        $retrievedja = \totara_job\job_assignment::get_with_id(-1, false);
        $this->assertNull($retrievedja);
    }

    /**
     * Tests get_all().
     */
    public function test_get_all() {
        $u1ja1 = \totara_job\job_assignment::create_default($this->users[1]->id);

        $u3ja1 = \totara_job\job_assignment::create_default($this->users[3]->id);

        $u2ja1 = \totara_job\job_assignment::create_default($this->users[2]->id);
        $u2ja2 = \totara_job\job_assignment::create_default($this->users[2]->id,
            array('managerjaid' => $u1ja1->id));
        $u2ja3 = \totara_job\job_assignment::create_default($this->users[2]->id,
            array('tempmanagerjaid' => $u3ja1->id, 'tempmanagerexpirydate' => time() + DAYSECS * 2));

        $retrievedjas = \totara_job\job_assignment::get_all($this->users[1]->id);
        $this->assertEquals(1, count($retrievedjas));
        $this->assertEquals($u1ja1, $retrievedjas[1]);

        $retrievedjas = \totara_job\job_assignment::get_all($this->users[2]->id);
        $this->assertEquals(3, count($retrievedjas));
        $this->assertEquals($u2ja1, $retrievedjas[1]);
        $this->assertEquals($u2ja2, $retrievedjas[2]);
        $this->assertEquals($u2ja3, $retrievedjas[3]);

        // Test managerreq true.
        $retrievedjas = \totara_job\job_assignment::get_all($this->users[1]->id, true);
        $this->assertEquals(0, count($retrievedjas));

        $retrievedjas = \totara_job\job_assignment::get_all($this->users[2]->id, true);
        $this->assertEquals(2, count($retrievedjas));
        $this->assertEquals($u2ja2, $retrievedjas[2]);
        $this->assertEquals($u2ja3, $retrievedjas[3]);
    }

    /**
     * Tests get_all_by_criteria().
     */
    public function test_get_all_by_criteria() {
        $u1ja1 = \totara_job\job_assignment::create_default($this->users[1]->id, array('appraiserid' => 123));

        $u2ja1 = \totara_job\job_assignment::create_default($this->users[2]->id, array('appraiserid' => 123));
        $u2ja2 = \totara_job\job_assignment::create_default($this->users[2]->id, array('appraiserid' => 123));
        $u2ja3 = \totara_job\job_assignment::create_default($this->users[2]->id, array('positionid' => 123));

        $u3ja1 = \totara_job\job_assignment::create_default($this->users[3]->id, array('organisationid' => 123));

        $this->assertEquals(array($u1ja1->id => $u1ja1, $u2ja1->id => $u2ja1, $u2ja2->id => $u2ja2),
            \totara_job\job_assignment::get_all_by_criteria('appraiserid', 123));
        $this->assertEquals(array($u2ja3->id => $u2ja3), \totara_job\job_assignment::get_all_by_criteria('positionid', 123));
        $this->assertEquals(array($u3ja1->id => $u3ja1), \totara_job\job_assignment::get_all_by_criteria('organisationid', 123));
        $this->assertEmpty(\totara_job\job_assignment::get_all_by_criteria('appraiserid', 444));
        $this->assertEmpty(\totara_job\job_assignment::get_all_by_criteria('positionid', 555));
        $this->assertEmpty(\totara_job\job_assignment::get_all_by_criteria('organisationid', 666));
    }

    /**
     * Tests update_to_empty_by_criteria().
     */
    public function test_update_to_empty_by_criteria() {
        $u1ja1 = \totara_job\job_assignment::create_default($this->users[1]->id,
            array('appraiserid' => 124));

        $u2ja1 = \totara_job\job_assignment::create_default($this->users[2]->id,
            array('appraiserid' => 123, 'positionid' => 234, 'organisationid' => 234));
        $u2ja2 = \totara_job\job_assignment::create_default($this->users[2]->id,
            array('appraiserid' => 123));
        $u2ja3 = \totara_job\job_assignment::create_default($this->users[2]->id,
            array('positionid' => 123));

        $u3ja1 = \totara_job\job_assignment::create_default($this->users[3]->id,
            array('organisationid' => 123));

        \totara_job\job_assignment::update_to_empty_by_criteria('appraiserid', 123);
        \totara_job\job_assignment::update_to_empty_by_criteria('positionid', 234);
        \totara_job\job_assignment::update_to_empty_by_criteria('organisationid', 234);

        $u1ja1 = \totara_job\job_assignment::get_with_id($u1ja1->id);
        $u2ja1 = \totara_job\job_assignment::get_with_id($u2ja1->id);
        $u2ja2 = \totara_job\job_assignment::get_with_id($u2ja2->id);
        $u2ja3 = \totara_job\job_assignment::get_with_id($u2ja3->id);
        $u3ja1 = \totara_job\job_assignment::get_with_id($u3ja1->id);

        $this->assertEquals(array($u1ja1->id => $u1ja1),
            \totara_job\job_assignment::get_all_by_criteria('appraiserid', 124));
        $this->assertEquals(array($u2ja3->id => $u2ja3),
            \totara_job\job_assignment::get_all_by_criteria('positionid', 123));
        $this->assertEquals(array($u3ja1->id => $u3ja1),
            \totara_job\job_assignment::get_all_by_criteria('organisationid', 123));

        $this->assertEmpty(\totara_job\job_assignment::get_all_by_criteria('appraiserid', 123));
        $this->assertEmpty(\totara_job\job_assignment::get_all_by_criteria('positionid', 234));
        $this->assertEmpty(\totara_job\job_assignment::get_all_by_criteria('organisationid', 234));
    }

    /**
     * Tests get_first().
     */
    public function test_get_first() {
        $u2ja1 = \totara_job\job_assignment::create_default($this->users[2]->id);
        $u2ja2 = \totara_job\job_assignment::create_default($this->users[2]->id);
        $u2ja3 = \totara_job\job_assignment::create_default($this->users[2]->id);

        $retrievedja = \totara_job\job_assignment::get_first($this->users[2]->id);
        $this->assertEquals($u2ja1, $retrievedja);

        // Test mustexist true (default).
        try {
            $retrievedja = \totara_job\job_assignment::get_first($this->users[1]->id);
            $this->assertEquals(0, 1, 'Exception not triggered!');
        } catch (Exception $e) {
            $this->assertStringStartsWith('Can not find data record in database', $e->getMessage());
        }

        // Test mustexist false.
        $retrievedja = \totara_job\job_assignment::get_first($this->users[1]->id, false);
        $this->assertNull($retrievedja);
    }

    /**
     * Tests get_staff() and get_direct_staff().
     */
    public function test_get_staff() {
        $managerid = $this->users[5]->id;
        $managerja1 = \totara_job\job_assignment::create_default($managerid);
        $managerja2 = \totara_job\job_assignment::create_default($managerid);

        $u2ja1 = \totara_job\job_assignment::create_default($this->users[2]->id,
            array('managerjaid' => $managerja1->id));
        $u2ja2 = \totara_job\job_assignment::create_default($this->users[2]->id,
            array('tempmanagerjaid' => $managerja1->id, 'tempmanagerexpirydate' => time() + DAYSECS * 2));

        $u3ja1 = \totara_job\job_assignment::create_default($this->users[3]->id,
            array('managerjaid' => $managerja1->id));

        $u4ja1 = \totara_job\job_assignment::create_default($this->users[4]->id,
            array('tempmanagerjaid' => $managerja2->id, 'tempmanagerexpirydate' => time() + DAYSECS * 2));

        $u1ja1 = \totara_job\job_assignment::create_default($this->users[1]->id,
            array('managerjaid' => $u2ja1->id));

        // Test when only managerid is specified.
        $staffjas = \totara_job\job_assignment::get_staff($managerid);
        $this->assertEquals(4, count($staffjas));
        $this->assertEquals($u2ja1, $staffjas[$u2ja1->id]);
        $this->assertEquals($u2ja2, $staffjas[$u2ja2->id]);
        $this->assertEquals($u3ja1, $staffjas[$u3ja1->id]);
        $this->assertEquals($u4ja1, $staffjas[$u4ja1->id]);

        // Test when managerjaid is specified.
        $staffjas = \totara_job\job_assignment::get_staff($managerid, $managerja1->id);
        $this->assertEquals(3, count($staffjas));
        $this->assertEquals($u2ja1, $staffjas[$u2ja1->id]);
        $this->assertEquals($u2ja2, $staffjas[$u2ja2->id]);
        $this->assertEquals($u3ja1, $staffjas[$u3ja1->id]);

        $staffjas = \totara_job\job_assignment::get_staff($managerid, $managerja2->id);
        $this->assertEquals(1, count($staffjas));
        $this->assertEquals($u4ja1, $staffjas[$u4ja1->id]);

        // Test when excluding temp staff.
        $staffjas = \totara_job\job_assignment::get_staff($managerid, null, false);
        $this->assertEquals(2, count($staffjas));
        $this->assertEquals($u2ja1, $staffjas[$u2ja1->id]);
        $this->assertEquals($u3ja1, $staffjas[$u3ja1->id]);

        $staffjas = \totara_job\job_assignment::get_staff($managerid, $managerja2->id, false);
        $this->assertEquals(0, count($staffjas));

        // Test mismatched managerid and managerjaid (should return nothing).
        $staffjas = \totara_job\job_assignment::get_staff($managerid, $u1ja1->id);
        $this->assertEquals(0, count($staffjas));
    }

    /**
     * Tests get_staff_userids() and get_direct_staff_userids().
     */
    public function test_get_staff_userids() {
        $managerid = $this->users[5]->id;
        $managerja1 = \totara_job\job_assignment::create_default($managerid);
        $managerja2 = \totara_job\job_assignment::create_default($managerid);

        $u2ja1 = \totara_job\job_assignment::create_default($this->users[2]->id,
            array('managerjaid' => $managerja1->id));
        $u2ja2 = \totara_job\job_assignment::create_default($this->users[2]->id,
            array('tempmanagerjaid' => $managerja1->id, 'tempmanagerexpirydate' => time() + DAYSECS * 2));

        $u3ja1 = \totara_job\job_assignment::create_default($this->users[3]->id,
            array('managerjaid' => $managerja1->id));

        $u4ja1 = \totara_job\job_assignment::create_default($this->users[4]->id,
            array('tempmanagerjaid' => $managerja2->id, 'tempmanagerexpirydate' => time() + DAYSECS * 2));

        $u1ja1 = \totara_job\job_assignment::create_default($this->users[1]->id,
            array('managerjaid' => $u2ja1->id));

        // Test when only managerid is specified.
        $staffids = \totara_job\job_assignment::get_staff_userids($managerid);
        sort($staffids); // Make sure they are in order.
        $this->assertEquals(array($this->users[2]->id, $this->users[3]->id, $this->users[4]->id), $staffids);

        // Test when managerjaid is specified.
        $staffids = \totara_job\job_assignment::get_staff_userids($managerid, $managerja1->id);
        sort($staffids); // Make sure they are in order.
        $this->assertEquals(array($this->users[2]->id, $this->users[3]->id), $staffids);

        $staffids = \totara_job\job_assignment::get_staff_userids($managerid, $managerja2->id);
        sort($staffids); // Make sure they are in order.
        $this->assertEquals(array($this->users[4]->id), $staffids);

        // Test when excluding temp staff.
        $staffids = \totara_job\job_assignment::get_staff_userids($managerid, null, false);
        sort($staffids); // Make sure they are in order.
        $this->assertEquals(array($this->users[2]->id, $this->users[3]->id), $staffids);

        $staffids = \totara_job\job_assignment::get_staff_userids($managerid, $managerja2->id, false);
        $this->assertEquals(0, count($staffids));

        // Test mismatched managerid and managerjaid (should return nothing).
        $staffids = \totara_job\job_assignment::get_staff_userids($managerid, $u1ja1->id);
        $this->assertEquals(0, count($staffids));
    }

    /**
     * Tests get_all_manager_userids().
     */
    public function test_get_all_manager_userids() {
        $future = time() + DAYSECS * 100;

        $manager8ja = \totara_job\job_assignment::create_default($this->users[8]->id);
        $manager9ja = \totara_job\job_assignment::create_default($this->users[9]->id);
        $tempmanager10ja = \totara_job\job_assignment::create_default($this->users[10]->id);

        $u1ja1 = \totara_job\job_assignment::create_default($this->users[1]->id, array('managerjaid' => $manager8ja->id));

        $u2ja1 = \totara_job\job_assignment::create_default($this->users[2]->id, array('managerjaid' => $manager8ja->id));
        $u2ja2 = \totara_job\job_assignment::create_default($this->users[2]->id, array('managerjaid' => $manager9ja->id));
        $u2ja3 = \totara_job\job_assignment::create_default($this->users[2]->id, array('tempmanagerjaid' => $tempmanager10ja->id, 'tempmanagerexpirydate' => $future));

        $u3ja1 = \totara_job\job_assignment::create_default($this->users[3]->id, array('managerjaid' => $manager8ja->id));
        $u3ja2 = \totara_job\job_assignment::create_default($this->users[3]->id, array('managerjaid' => $manager8ja->id));

        $u4ja1 = \totara_job\job_assignment::create_default($this->users[4]->id, array('managerjaid' => $manager9ja->id));

        $u5ja1 = \totara_job\job_assignment::create_default($this->users[5]->id, array('tempmanagerjaid' => $tempmanager10ja->id, 'tempmanagerexpirydate' => $future));

        $u6ja1 = \totara_job\job_assignment::create_default($this->users[5]->id);

        $manageruserids = \totara_job\job_assignment::get_all_manager_userids($this->users[1]->id);
        $this->assertEquals(array($manager8ja->userid), array_values($manageruserids));
        $manageruserids = \totara_job\job_assignment::get_all_manager_userids($this->users[2]->id);
        $this->assertEquals(array($manager8ja->userid, $manager9ja->userid, $tempmanager10ja->userid), array_values($manageruserids));
        $manageruserids = \totara_job\job_assignment::get_all_manager_userids($this->users[3]->id);
        $this->assertEquals(array($manager8ja->userid), array_values($manageruserids));
        $manageruserids = \totara_job\job_assignment::get_all_manager_userids($this->users[4]->id);
        $this->assertEquals(array($manager9ja->userid), array_values($manageruserids));
        $manageruserids = \totara_job\job_assignment::get_all_manager_userids($this->users[5]->id);
        $this->assertEquals(array($tempmanager10ja->userid), array_values($manageruserids));
        $manageruserids = \totara_job\job_assignment::get_all_manager_userids($this->users[6]->id);
        $this->assertEmpty($manageruserids);

        $manageruserids = \totara_job\job_assignment::get_all_manager_userids($this->users[1]->id, $u1ja1->id);
        $this->assertEquals(array($manager8ja->userid), array_values($manageruserids));
        $manageruserids = \totara_job\job_assignment::get_all_manager_userids($this->users[2]->id, $u2ja1->id);
        $this->assertEquals(array($manager8ja->userid), array_values($manageruserids));
        $manageruserids = \totara_job\job_assignment::get_all_manager_userids($this->users[2]->id, $u2ja2->id);
        $this->assertEquals(array($manager9ja->userid), array_values($manageruserids));
        $manageruserids = \totara_job\job_assignment::get_all_manager_userids($this->users[2]->id, $u2ja3->id);
        $this->assertEquals(array($tempmanager10ja->userid), array_values($manageruserids));
        $manageruserids = \totara_job\job_assignment::get_all_manager_userids($this->users[3]->id, $u3ja1->id);
        $this->assertEquals(array($manager8ja->userid), array_values($manageruserids));
        $manageruserids = \totara_job\job_assignment::get_all_manager_userids($this->users[3]->id, $u3ja2->id);
        $this->assertEquals(array($manager8ja->userid), array_values($manageruserids));
        $manageruserids = \totara_job\job_assignment::get_all_manager_userids($this->users[4]->id, $u4ja1->id);
        $this->assertEquals(array($manager9ja->userid), array_values($manageruserids));
        $manageruserids = \totara_job\job_assignment::get_all_manager_userids($this->users[5]->id, $u5ja1->id);
        $this->assertEquals(array($tempmanager10ja->userid), array_values($manageruserids));
        $manageruserids = \totara_job\job_assignment::get_all_manager_userids($this->users[6]->id, $u6ja1->id);
        $this->assertEmpty($manageruserids);

        $manageruserids = \totara_job\job_assignment::get_all_manager_userids($this->users[1]->id, null, false);
        $this->assertEquals(array($manager8ja->userid), array_values($manageruserids));
        $manageruserids = \totara_job\job_assignment::get_all_manager_userids($this->users[2]->id, null, false);
        $this->assertEquals(array($manager8ja->userid, $manager9ja->userid), array_values($manageruserids));
        $manageruserids = \totara_job\job_assignment::get_all_manager_userids($this->users[3]->id, null, false);
        $this->assertEquals(array($manager8ja->userid), array_values($manageruserids));
        $manageruserids = \totara_job\job_assignment::get_all_manager_userids($this->users[4]->id, null, false);
        $this->assertEquals(array($manager9ja->userid), array_values($manageruserids));
        $manageruserids = \totara_job\job_assignment::get_all_manager_userids($this->users[5]->id, null, false);
        $this->assertEmpty($manageruserids);
        $manageruserids = \totara_job\job_assignment::get_all_manager_userids($this->users[6]->id, null, false);
        $this->assertEmpty($manageruserids);
    }

    /**
     * Tests is_managing().
     */
    public function test_is_managing() {
        $managerid = $this->users[5]->id;
        $managerja1 = \totara_job\job_assignment::create_default($managerid);
        $managerja2 = \totara_job\job_assignment::create_default($managerid);
        $manager2id = $this->users[6]->id;
        $manager2ja = \totara_job\job_assignment::create_default($manager2id);

        $u2ja1 = \totara_job\job_assignment::create_default($this->users[2]->id,
            array('managerjaid' => $managerja1->id));
        $u2ja2 = \totara_job\job_assignment::create_default($this->users[2]->id,
            array('tempmanagerjaid' => $managerja2->id, 'tempmanagerexpirydate' => time() + DAYSECS * 2));

        $u3ja1 = \totara_job\job_assignment::create_default($this->users[3]->id,
            array('managerjaid' => $managerja1->id));

        $u4ja1 = \totara_job\job_assignment::create_default($this->users[4]->id,
            array('tempmanagerjaid' => $managerja2->id, 'tempmanagerexpirydate' => time() + DAYSECS * 2));

        $u1ja1 = \totara_job\job_assignment::create_default($this->users[1]->id,
            array('managerjaid' => $u2ja1->id));

        $u7ja1 = \totara_job\job_assignment::create_default($this->users[7]->id,
            array('managerjaid' => $managerja1->id));
        $u7ja2 = \totara_job\job_assignment::create_default($this->users[7]->id,
            array('managerjaid' => $manager2ja->id));

        // Test including temp managers (default).
        $this->assertFalse(\totara_job\job_assignment::is_managing($managerid, $this->users[1]->id));
        $this->assertTrue(\totara_job\job_assignment::is_managing($managerid, $this->users[2]->id));
        $this->assertTrue(\totara_job\job_assignment::is_managing($managerid, $this->users[3]->id));
        $this->assertTrue(\totara_job\job_assignment::is_managing($managerid, $this->users[4]->id));
        $this->assertFalse(\totara_job\job_assignment::is_managing($this->users[4]->id, $this->users[2]->id));
        $this->assertTrue(\totara_job\job_assignment::is_managing($this->users[2]->id, $this->users[1]->id));

        // Test with user job assignment ids.
        $this->assertTrue(\totara_job\job_assignment::is_managing($managerid, $this->users[7]->id));
        $this->assertTrue(\totara_job\job_assignment::is_managing($manager2id, $this->users[7]->id));
        $this->assertTrue(\totara_job\job_assignment::is_managing($managerid, $this->users[7]->id, $u7ja1->id));
        $this->assertFalse(\totara_job\job_assignment::is_managing($manager2id, $this->users[7]->id, $u7ja1->id));
        $this->assertFalse(\totara_job\job_assignment::is_managing($managerid, $this->users[7]->id, $u7ja2->id));
        $this->assertTrue(\totara_job\job_assignment::is_managing($manager2id, $this->users[7]->id, $u7ja2->id));

        // Excluding temp managers.
        $this->assertFalse(\totara_job\job_assignment::is_managing($managerid, $this->users[1]->id, null, false));
        $this->assertTrue(\totara_job\job_assignment::is_managing($managerid, $this->users[2]->id, null, false));
        $this->assertTrue(\totara_job\job_assignment::is_managing($managerid, $this->users[3]->id, null, false));
        $this->assertFalse(\totara_job\job_assignment::is_managing($managerid, $this->users[4]->id, null, false)); // Changed to false.
        $this->assertFalse(\totara_job\job_assignment::is_managing($this->users[4]->id, $this->users[2]->id, null, false));
        $this->assertTrue(\totara_job\job_assignment::is_managing($this->users[2]->id, $this->users[1]->id, null, false));
    }
    /**
     * Tests has_staff().
     */
    public function test_has_staff() {
        $managerid = $this->users[5]->id;
        $managerja1 = \totara_job\job_assignment::create_default($managerid);
        $managerja2 = \totara_job\job_assignment::create_default($this->users[5]->id);

        $u2ja1 = \totara_job\job_assignment::create_default($this->users[2]->id,
            array('managerjaid' => $managerja1->id));
        $u2ja2 = \totara_job\job_assignment::create_default($this->users[2]->id,
            array('tempmanagerjaid' => $managerja1->id, 'tempmanagerexpirydate' => time() + DAYSECS * 2));

        $u3ja1 = \totara_job\job_assignment::create_default($this->users[3]->id,
            array('managerjaid' => $managerja1->id));

        $u4ja1 = \totara_job\job_assignment::create_default($this->users[4]->id,
            array('tempmanagerjaid' => $managerja2->id, 'tempmanagerexpirydate' => time() + DAYSECS * 2));

        $u1ja1 = \totara_job\job_assignment::create_default($this->users[1]->id,
            array('managerjaid' => $u2ja1->id));
        $u1ja2 = \totara_job\job_assignment::create_default($this->users[1]->id,
            array('tempmanagerjaid' => $u3ja1->id, 'tempmanagerexpirydate' => time() + DAYSECS * 2));

        // Test including temp managers (default).
        $this->assertTrue(\totara_job\job_assignment::has_staff($managerid));
        $this->assertFalse(\totara_job\job_assignment::has_staff($this->users[1]->id));
        $this->assertTrue(\totara_job\job_assignment::has_staff($this->users[2]->id));
        $this->assertTrue(\totara_job\job_assignment::has_staff($this->users[3]->id));
        $this->assertFalse(\totara_job\job_assignment::has_staff($this->users[4]->id));

        // Test including temp managers (default), with managerjaid.
        $this->assertTrue(\totara_job\job_assignment::has_staff($managerid, $managerja1->id));
        $this->assertTrue(\totara_job\job_assignment::has_staff($managerid, $managerja2->id));
        $this->assertFalse(\totara_job\job_assignment::has_staff($this->users[1]->id, $u1ja1->id));
        $this->assertFalse(\totara_job\job_assignment::has_staff($this->users[1]->id, $u1ja2->id));
        $this->assertTrue(\totara_job\job_assignment::has_staff($this->users[2]->id, $u2ja1->id));
        $this->assertFalse(\totara_job\job_assignment::has_staff($this->users[2]->id, $u2ja2->id));
        $this->assertTrue(\totara_job\job_assignment::has_staff($this->users[3]->id, $u3ja1->id));
        $this->assertFalse(\totara_job\job_assignment::has_staff($this->users[4]->id, $u4ja1->id));

        // Excluding temp managers.
        $this->assertTrue(\totara_job\job_assignment::has_staff($managerid, null, false));
        $this->assertFalse(\totara_job\job_assignment::has_staff($this->users[1]->id, null, false));
        $this->assertTrue(\totara_job\job_assignment::has_staff($this->users[2]->id, null, false));
        $this->assertFalse(\totara_job\job_assignment::has_staff($this->users[3]->id, null, false)); // Changed to false.
        $this->assertFalse(\totara_job\job_assignment::has_staff($this->users[4]->id, null, false));

        // Excluding temp managers, with managerjaid.
        $this->assertTrue(\totara_job\job_assignment::has_staff($managerid, $managerja1->id, false));
        $this->assertFalse(\totara_job\job_assignment::has_staff($managerid, $managerja2->id, false)); // Changed to false.
        $this->assertFalse(\totara_job\job_assignment::has_staff($this->users[1]->id, $u1ja1->id, false));
        $this->assertFalse(\totara_job\job_assignment::has_staff($this->users[1]->id, $u1ja2->id, false));
        $this->assertTrue(\totara_job\job_assignment::has_staff($this->users[2]->id, $u2ja1->id, false));
        $this->assertFalse(\totara_job\job_assignment::has_staff($this->users[2]->id, $u2ja2->id, false));
        $this->assertFalse(\totara_job\job_assignment::has_staff($this->users[3]->id, $u3ja1->id, false)); // Changed to false.
        $this->assertFalse(\totara_job\job_assignment::has_staff($this->users[4]->id, $u4ja1->id, false));
    }

    /**
     * Tests has_manager().
     */
    public function test_has_manager() {
        $managerid = $this->users[5]->id;
        $managerja1 = \totara_job\job_assignment::create_default($managerid);
        $managerja2 = \totara_job\job_assignment::create_default($this->users[5]->id);

        $u1ja1 = \totara_job\job_assignment::create_default($this->users[1]->id);

        $u2ja1 = \totara_job\job_assignment::create_default($this->users[2]->id,
            array('managerjaid' => $managerja1->id));
        $u2ja2 = \totara_job\job_assignment::create_default($this->users[2]->id,
            array('tempmanagerjaid' => $managerja1->id, 'tempmanagerexpirydate' => time() + DAYSECS * 2));

        $u3ja1 = \totara_job\job_assignment::create_default($this->users[3]->id,
            array('managerjaid' => $managerja1->id));

        $u4ja1 = \totara_job\job_assignment::create_default($this->users[4]->id,
            array('tempmanagerjaid' => $managerja2->id, 'tempmanagerexpirydate' => time() + DAYSECS * 2));

        // Test including temp managers (default).
        $this->assertFalse(\totara_job\job_assignment::has_manager($this->users[1]->id));
        $this->assertTrue(\totara_job\job_assignment::has_manager($this->users[2]->id));
        $this->assertTrue(\totara_job\job_assignment::has_manager($this->users[3]->id));
        $this->assertTrue(\totara_job\job_assignment::has_manager($this->users[4]->id));

        // Test including temp managers (default), with job assignment id.
        $this->assertFalse(\totara_job\job_assignment::has_manager($this->users[1]->id, $u1ja1->id));
        $this->assertTrue(\totara_job\job_assignment::has_manager($this->users[2]->id, $u2ja1->id));
        $this->assertTrue(\totara_job\job_assignment::has_manager($this->users[2]->id, $u2ja2->id));
        $this->assertTrue(\totara_job\job_assignment::has_manager($this->users[3]->id, $u3ja1->id));
        $this->assertTrue(\totara_job\job_assignment::has_manager($this->users[4]->id, $u4ja1->id));

        // Excluding temp managers.
        $this->assertFalse(\totara_job\job_assignment::has_manager($this->users[1]->id, null, false));
        $this->assertTrue(\totara_job\job_assignment::has_manager($this->users[2]->id, null, false));
        $this->assertTrue(\totara_job\job_assignment::has_manager($this->users[3]->id, null, false));
        $this->assertFalse(\totara_job\job_assignment::has_manager($this->users[4]->id, null, false)); // Changed to false.

        // Excluding temp managers, with managerjaid.
        $this->assertFalse(\totara_job\job_assignment::has_manager($this->users[1]->id, $u1ja1->id, false));
        $this->assertTrue(\totara_job\job_assignment::has_manager($this->users[2]->id, $u2ja1->id, false));
        $this->assertFalse(\totara_job\job_assignment::has_manager($this->users[2]->id, $u2ja2->id, false)); // Changed to false.
        $this->assertTrue(\totara_job\job_assignment::has_manager($this->users[3]->id, $u3ja1->id, false));
        $this->assertFalse(\totara_job\job_assignment::has_manager($this->users[4]->id, $u4ja1->id, false));
    }

    /**
     * Tests update_temporary_managers().
     */
    public function test_update_temporary_managers() {
        // TODO Write me.
    }

    /**
     * Test swap_order() and swap_order_internal().
     */
    public function test_swap_order() {
        $u1ja1 = \totara_job\job_assignment::create_default($this->users[1]->id);
        $u1ja2 = \totara_job\job_assignment::create_default($this->users[1]->id);
        $u1ja3 = \totara_job\job_assignment::create_default($this->users[1]->id);

        $u2ja1 = \totara_job\job_assignment::create_default($this->users[2]->id);
        $u2ja2 = \totara_job\job_assignment::create_default($this->users[2]->id);
        $u2ja3 = \totara_job\job_assignment::create_default($this->users[2]->id);

        $u3ja1 = \totara_job\job_assignment::create_default($this->users[3]->id);

        // Check that they are swapped.
        $previoustimemodified = max(array($u2ja1->timemodified, $u2ja2->timemodified, $u2ja3->timemodified));
        sleep(1);
        $this->assertEquals(1, $u2ja1->sortorder);
        $this->assertEquals(2, $u2ja2->sortorder);
        $this->assertEquals(3, $u2ja3->sortorder);
        \totara_job\job_assignment::swap_order($u2ja1->id, $u2ja3->id);
        // Reload from the db because the objects are invalid.
        $u2ja1 = \totara_job\job_assignment::get_with_id($u2ja1->id);
        $u2ja2 = \totara_job\job_assignment::get_with_id($u2ja2->id);
        $u2ja3 = \totara_job\job_assignment::get_with_id($u2ja3->id);
        $this->assertEquals(3, $u2ja1->sortorder);
        $this->assertEquals(2, $u2ja2->sortorder);
        $this->assertEquals(1, $u2ja3->sortorder);
        // Swap uses update(), so timemodified is changed.
        $this->assertGreaterThan($previoustimemodified, $u2ja1->timemodified);
        $this->assertLessThanOrEqual($previoustimemodified, $u2ja2->timemodified);
        $this->assertGreaterThan($previoustimemodified, $u2ja3->timemodified);

        // Mess around with them a bit more, just for fun.
        \totara_job\job_assignment::swap_order($u2ja3->id, $u2ja2->id);
        \totara_job\job_assignment::swap_order($u2ja1->id, $u2ja2->id);
        \totara_job\job_assignment::swap_order($u2ja1->id, $u2ja3->id);
        $u2ja1 = \totara_job\job_assignment::get_with_id($u2ja1->id);
        $u2ja2 = \totara_job\job_assignment::get_with_id($u2ja2->id);
        $u2ja3 = \totara_job\job_assignment::get_with_id($u2ja3->id);
        $this->assertEquals(2, $u2ja1->sortorder);
        $this->assertEquals(3, $u2ja2->sortorder);
        $this->assertEquals(1, $u2ja3->sortorder);

        // Check that no other job assignment was affected.
        $u1ja1 = \totara_job\job_assignment::get_with_id($u1ja1->id);
        $u1ja2 = \totara_job\job_assignment::get_with_id($u1ja2->id);
        $u1ja3 = \totara_job\job_assignment::get_with_id($u1ja3->id);
        $u3ja1 = \totara_job\job_assignment::get_with_id($u3ja1->id);
        $this->assertEquals(1, $u1ja1->sortorder);
        $this->assertEquals(2, $u1ja2->sortorder);
        $this->assertEquals(3, $u1ja3->sortorder);
        $this->assertEquals(1, $u3ja1->sortorder);

        // Check that you can't swap job assigments belonging to two different users.
        try {
            \totara_job\job_assignment::swap_order($u1ja3->id, $u2ja2->id);
            $this->assertEquals(0, 1, 'Exception not triggered!');
        } catch (Exception $e) {
            $this->assertEquals('Cannot swap order of two job assignments belonging to different users.', $e->getMessage());
        }
    }

    /**
     * Test move_up().
     */
    public function test_move_up() {
        $u1ja1 = \totara_job\job_assignment::create_default($this->users[1]->id);
        $u1ja2 = \totara_job\job_assignment::create_default($this->users[1]->id);
        $u1ja3 = \totara_job\job_assignment::create_default($this->users[1]->id);

        $u2ja1 = \totara_job\job_assignment::create_default($this->users[2]->id);
        $u2ja2 = \totara_job\job_assignment::create_default($this->users[2]->id);
        $u2ja3 = \totara_job\job_assignment::create_default($this->users[2]->id);

        $u3ja1 = \totara_job\job_assignment::create_default($this->users[3]->id);

        // Check that it is moved up.
        $previoustimemodified = max(array($u2ja1->timemodified, $u2ja2->timemodified, $u2ja3->timemodified));
        sleep(1);
        $this->assertEquals(1, $u2ja1->sortorder);
        $this->assertEquals(2, $u2ja2->sortorder);
        $this->assertEquals(3, $u2ja3->sortorder);
        \totara_job\job_assignment::move_up($u2ja3->id);
        // Reload from the db because the objects in memory are invalid.
        $u2ja1 = \totara_job\job_assignment::get_with_id($u2ja1->id);
        $u2ja2 = \totara_job\job_assignment::get_with_id($u2ja2->id);
        $u2ja3 = \totara_job\job_assignment::get_with_id($u2ja3->id);
        $this->assertEquals(1, $u2ja1->sortorder);
        $this->assertEquals(3, $u2ja2->sortorder);
        $this->assertEquals(2, $u2ja3->sortorder);
        // Move up uses update(), so timemodified is changed.
        $this->assertLessThanOrEqual($previoustimemodified, $u2ja1->timemodified);
        $this->assertGreaterThan($previoustimemodified, $u2ja2->timemodified);
        $this->assertGreaterThan($previoustimemodified, $u2ja3->timemodified);

        // Mess around with them a bit more, just for fun.
        \totara_job\job_assignment::move_up($u2ja3->id);
        \totara_job\job_assignment::move_up($u2ja2->id);
        \totara_job\job_assignment::move_up($u2ja1->id);
        $u2ja1 = \totara_job\job_assignment::get_with_id($u2ja1->id);
        $u2ja2 = \totara_job\job_assignment::get_with_id($u2ja2->id);
        $u2ja3 = \totara_job\job_assignment::get_with_id($u2ja3->id);
        $this->assertEquals(2, $u2ja1->sortorder);
        $this->assertEquals(3, $u2ja2->sortorder);
        $this->assertEquals(1, $u2ja3->sortorder);

        // Check that no other job assignment was affected.
        $u1ja1 = \totara_job\job_assignment::get_with_id($u1ja1->id);
        $u1ja2 = \totara_job\job_assignment::get_with_id($u1ja2->id);
        $u1ja3 = \totara_job\job_assignment::get_with_id($u1ja3->id);
        $u3ja1 = \totara_job\job_assignment::get_with_id($u3ja1->id);
        $this->assertEquals(1, $u1ja1->sortorder);
        $this->assertEquals(2, $u1ja2->sortorder);
        $this->assertEquals(3, $u1ja3->sortorder);
        $this->assertEquals(1, $u3ja1->sortorder);

        // Check that moving the first job assignment up does not work.
        try {
            \totara_job\job_assignment::move_up($u2ja3->id);
            $this->assertEquals(0, 1, 'Exception not triggered!');
        } catch (Exception $e) {
            $this->assertEquals('Tried to move the first job assignment up.', $e->getMessage());
        }
    }

    /**
     * Test move_down().
     */
    public function test_move_down() {
        $u1ja1 = \totara_job\job_assignment::create_default($this->users[1]->id);
        $u1ja2 = \totara_job\job_assignment::create_default($this->users[1]->id);
        $u1ja3 = \totara_job\job_assignment::create_default($this->users[1]->id);

        $u2ja1 = \totara_job\job_assignment::create_default($this->users[2]->id);
        $u2ja2 = \totara_job\job_assignment::create_default($this->users[2]->id);
        $u2ja3 = \totara_job\job_assignment::create_default($this->users[2]->id);

        $u3ja1 = \totara_job\job_assignment::create_default($this->users[3]->id);

        // Check that it is moved down.
        $previoustimemodified = max(array($u2ja1->timemodified, $u2ja2->timemodified, $u2ja3->timemodified));
        sleep(1);
        $this->assertEquals(1, $u2ja1->sortorder);
        $this->assertEquals(2, $u2ja2->sortorder);
        $this->assertEquals(3, $u2ja3->sortorder);
        \totara_job\job_assignment::move_down($u2ja1->id);
        // Reload from the db because the objects in memory are invalid.
        $u2ja1 = \totara_job\job_assignment::get_with_id($u2ja1->id);
        $u2ja2 = \totara_job\job_assignment::get_with_id($u2ja2->id);
        $u2ja3 = \totara_job\job_assignment::get_with_id($u2ja3->id);
        $this->assertEquals(2, $u2ja1->sortorder);
        $this->assertEquals(1, $u2ja2->sortorder);
        $this->assertEquals(3, $u2ja3->sortorder);
        // Move up uses update(), so timemodified is changed.
        $this->assertGreaterThan($previoustimemodified, $u2ja1->timemodified);
        $this->assertGreaterThan($previoustimemodified, $u2ja2->timemodified);
        $this->assertLessThanOrEqual($previoustimemodified, $u2ja3->timemodified);

        // Mess around with them a bit more, just for fun.
        \totara_job\job_assignment::move_down($u2ja1->id);
        \totara_job\job_assignment::move_down($u2ja2->id);
        \totara_job\job_assignment::move_down($u2ja3->id);
        $u2ja1 = \totara_job\job_assignment::get_with_id($u2ja1->id);
        $u2ja2 = \totara_job\job_assignment::get_with_id($u2ja2->id);
        $u2ja3 = \totara_job\job_assignment::get_with_id($u2ja3->id);
        $this->assertEquals(3, $u2ja1->sortorder);
        $this->assertEquals(1, $u2ja2->sortorder);
        $this->assertEquals(2, $u2ja3->sortorder);

        // Check that no other job assignment was affected.
        $u1ja1 = \totara_job\job_assignment::get_with_id($u1ja1->id);
        $u1ja2 = \totara_job\job_assignment::get_with_id($u1ja2->id);
        $u1ja3 = \totara_job\job_assignment::get_with_id($u1ja3->id);
        $u3ja1 = \totara_job\job_assignment::get_with_id($u3ja1->id);
        $this->assertEquals(1, $u1ja1->sortorder);
        $this->assertEquals(2, $u1ja2->sortorder);
        $this->assertEquals(3, $u1ja3->sortorder);
        $this->assertEquals(1, $u3ja1->sortorder);

        // Check that moving the first job assignment up does not work.
        try {
            \totara_job\job_assignment::move_down($u2ja1->id);
            $this->assertEquals(0, 1, 'Exception not triggered!');
        } catch (Exception $e) {
            $this->assertEquals('Tried to move the last job assignment down.', $e->getMessage());
        }
    }

    // TODO Write tests for the functions added after move_down (resort_user_job_assignments is being renamed, export_for_template).
}