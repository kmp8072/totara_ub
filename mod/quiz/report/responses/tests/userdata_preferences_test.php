<?php
/*
 * This file is part of Totara Learn
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
 * @author Sam Hemelryk <sam.hemelryk@totaralearning.com>
 * @package quiz_responses
 */

namespace quiz_responses\userdata;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/user/tests/userdata_plugin_preferences_test.php');

/**
 * @group totara_userdata
 */
class quiz_responses_userdata_preferences_testcase extends \core_user_userdata_plugin_preferences_testcase {

    protected function get_preferences_class(): string {
        return preferences::class;
    }

    protected function get_preferences(): array {
        global $CFG;
        require_once($CFG->dirroot . '/question/engine/questionattempt.php');
        return [
            'quiz_report_responses_qtext' => [true, false],
            'quiz_report_responses_resp' => [true, false],
            'quiz_report_responses_right' => [true, false],
            'quiz_report_responses_which_tries' => [\question_attempt::LAST_TRY, \question_attempt::FIRST_TRY],
        ];
    }

}
