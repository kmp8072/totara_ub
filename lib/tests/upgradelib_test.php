<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Unit tests for the lib/upgradelib.php library.
 * Totara: and incorrectly for lib/db/upgradelib.php too, thanks Moodle!
 *
 * @package   core
 * @category  phpunit
 * @copyright 2013 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir.'/upgradelib.php');

/**
 * Tests various classes and functions in upgradelib.php library.
 */
class core_upgradelib_testcase extends advanced_testcase {

    /**
     * Test the {@link upgrade_stale_php_files_present() function
     */
    public function test_upgrade_stale_php_files_present() {
        global $CFG;
        require_once($CFG->libdir.'/upgradelib.php');

        // Just call the function, must return bool false always
        // if there aren't any old files in the codebase.
        $this->assertFalse(upgrade_stale_php_files_present());
    }

    /**
     * Populate some fake grade items into the database with specified
     * sortorder and course id.
     *
     * NOTE: This function doesn't make much attempt to respect the
     * gradebook internals, its simply used to fake some data for
     * testing the upgradelib function. Please don't use it for other
     * purposes.
     *
     * @param int $courseid id of course
     * @param int $sortorder numeric sorting order of item
     * @return stdClass grade item object from the database.
     */
    private function insert_fake_grade_item_sortorder($courseid, $sortorder) {
        global $DB, $CFG;
        require_once($CFG->libdir.'/gradelib.php');

        $item = new stdClass();
        $item->courseid = $courseid;
        $item->sortorder = $sortorder;
        $item->gradetype = GRADE_TYPE_VALUE;
        $item->grademin = 30;
        $item->grademax = 110;
        $item->itemnumber = 1;
        $item->iteminfo = '';
        $item->timecreated = time();
        $item->timemodified = time();

        $item->id = $DB->insert_record('grade_items', $item);

        return $DB->get_record('grade_items', array('id' => $item->id));
    }

    public function test_upgrade_fix_missing_root_folders_draft() {
        global $DB, $SITE;

        $this->resetAfterTest(true);

        $user = $this->getDataGenerator()->create_user();
        $usercontext = context_user::instance($user->id);
        $this->setUser($user);
        $resource1 = $this->getDataGenerator()->get_plugin_generator('mod_resource')
            ->create_instance(array('course' => $SITE->id));
        $context = context_module::instance($resource1->cmid);
        $draftitemid = 0;
        file_prepare_draft_area($draftitemid, $context->id, 'mod_resource', 'content', 0);

        $queryparams = array(
            'component' => 'user',
            'contextid' => $usercontext->id,
            'filearea' => 'draft',
            'itemid' => $draftitemid,
        );

        // Make sure there are two records in files for the draft file area and one of them has filename '.'.
        $records = $DB->get_records_menu('files', $queryparams, '', 'id, filename');
        $this->assertEquals(2, count($records));
        $this->assertTrue(in_array('.', $records));
        $originalhash = $DB->get_field('files', 'pathnamehash', $queryparams + array('filename' => '.'));

        // Delete record with filename '.' and make sure it does not exist any more.
        $DB->delete_records('files', $queryparams + array('filename' => '.'));

        $records = $DB->get_records_menu('files', $queryparams, '', 'id, filename');
        $this->assertEquals(1, count($records));
        $this->assertFalse(in_array('.', $records));

        // Run upgrade script and make sure the record is restored.
        upgrade_fix_missing_root_folders_draft();

        $records = $DB->get_records_menu('files', $queryparams, '', 'id, filename');
        $this->assertEquals(2, count($records));
        $this->assertTrue(in_array('.', $records));
        $newhash = $DB->get_field('files', 'pathnamehash', $queryparams + array('filename' => '.'));
        $this->assertEquals($originalhash, $newhash);
    }

    /**
     * Test upgrade minmaxgrade step.
     */
    public function test_upgrade_minmaxgrade() {
        global $CFG, $DB;
        require_once($CFG->libdir . '/gradelib.php');
        $initialminmax = $CFG->grade_minmaxtouse;
        $this->resetAfterTest();

        $c1 = $this->getDataGenerator()->create_course();
        $c2 = $this->getDataGenerator()->create_course();
        $c3 = $this->getDataGenerator()->create_course();
        $u1 = $this->getDataGenerator()->create_user();
        $a1 = $this->getDataGenerator()->create_module('assign', array('course' => $c1, 'grade' => 100));
        $a2 = $this->getDataGenerator()->create_module('assign', array('course' => $c2, 'grade' => 100));
        $a3 = $this->getDataGenerator()->create_module('assign', array('course' => $c3, 'grade' => 100));

        $cm1 = get_coursemodule_from_instance('assign', $a1->id);
        $ctx1 = context_module::instance($cm1->id);
        $assign1 = new assign($ctx1, $cm1, $c1);

        $cm2 = get_coursemodule_from_instance('assign', $a2->id);
        $ctx2 = context_module::instance($cm2->id);
        $assign2 = new assign($ctx2, $cm2, $c2);

        $cm3 = get_coursemodule_from_instance('assign', $a3->id);
        $ctx3 = context_module::instance($cm3->id);
        $assign3 = new assign($ctx3, $cm3, $c3);

        // Give a grade to the student.
        $ug = $assign1->get_user_grade($u1->id, true);
        $ug->grade = 10;
        $assign1->update_grade($ug);

        $ug = $assign2->get_user_grade($u1->id, true);
        $ug->grade = 20;
        $assign2->update_grade($ug);

        $ug = $assign3->get_user_grade($u1->id, true);
        $ug->grade = 30;
        $assign3->update_grade($ug);


        // Run the upgrade.
        upgrade_minmaxgrade();

        // Nothing has happened.
        $this->assertFalse($DB->record_exists('config', array('name' => 'show_min_max_grades_changed_' . $c1->id)));
        $this->assertSame(false, grade_get_setting($c1->id, 'minmaxtouse', false, true));
        $this->assertFalse($DB->record_exists('grade_items', array('needsupdate' => 1, 'courseid' => $c1->id)));
        $this->assertFalse($DB->record_exists('config', array('name' => 'show_min_max_grades_changed_' . $c2->id)));
        $this->assertSame(false, grade_get_setting($c2->id, 'minmaxtouse', false, true));
        $this->assertFalse($DB->record_exists('grade_items', array('needsupdate' => 1, 'courseid' => $c2->id)));
        $this->assertFalse($DB->record_exists('config', array('name' => 'show_min_max_grades_changed_' . $c3->id)));
        $this->assertSame(false, grade_get_setting($c3->id, 'minmaxtouse', false, true));
        $this->assertFalse($DB->record_exists('grade_items', array('needsupdate' => 1, 'courseid' => $c3->id)));

        // Create inconsistency in c1 and c2.
        $giparams = array('itemtype' => 'mod', 'itemmodule' => 'assign', 'iteminstance' => $a1->id,
                'courseid' => $c1->id, 'itemnumber' => 0);
        $gi = grade_item::fetch($giparams);
        $gi->grademin = 5;
        $gi->update();

        $giparams = array('itemtype' => 'mod', 'itemmodule' => 'assign', 'iteminstance' => $a2->id,
                'courseid' => $c2->id, 'itemnumber' => 0);
        $gi = grade_item::fetch($giparams);
        $gi->grademax = 50;
        $gi->update();


        // C1 and C2 should be updated, but the course setting should not be set.
        $CFG->grade_minmaxtouse = GRADE_MIN_MAX_FROM_GRADE_GRADE;

        // Run the upgrade.
        upgrade_minmaxgrade();

        // C1 and C2 were partially updated.
        $this->assertTrue($DB->record_exists('config', array('name' => 'show_min_max_grades_changed_' . $c1->id)));
        $this->assertSame(false, grade_get_setting($c1->id, 'minmaxtouse', false, true));
        $this->assertTrue($DB->record_exists('grade_items', array('needsupdate' => 1, 'courseid' => $c1->id)));
        $this->assertTrue($DB->record_exists('config', array('name' => 'show_min_max_grades_changed_' . $c2->id)));
        $this->assertSame(false, grade_get_setting($c2->id, 'minmaxtouse', false, true));
        $this->assertTrue($DB->record_exists('grade_items', array('needsupdate' => 1, 'courseid' => $c2->id)));

        // Nothing has happened for C3.
        $this->assertFalse($DB->record_exists('config', array('name' => 'show_min_max_grades_changed_' . $c3->id)));
        $this->assertSame(false, grade_get_setting($c3->id, 'minmaxtouse', false, true));
        $this->assertFalse($DB->record_exists('grade_items', array('needsupdate' => 1, 'courseid' => $c3->id)));


        // Course setting should not be set on a course that has the setting already.
        $CFG->grade_minmaxtouse = GRADE_MIN_MAX_FROM_GRADE_ITEM;
        grade_set_setting($c1->id, 'minmaxtouse', -1); // Sets different value than constant to check that it remained the same.

        // Run the upgrade.
        upgrade_minmaxgrade();

        // C2 was updated.
        $this->assertSame((string) GRADE_MIN_MAX_FROM_GRADE_GRADE, grade_get_setting($c2->id, 'minmaxtouse', false, true));

        // Nothing has happened for C1.
        $this->assertSame('-1', grade_get_setting($c1->id, 'minmaxtouse', false, true));

        // Nothing has happened for C3.
        $this->assertFalse($DB->record_exists('config', array('name' => 'show_min_max_grades_changed_' . $c3->id)));
        $this->assertSame(false, grade_get_setting($c3->id, 'minmaxtouse', false, true));
        $this->assertFalse($DB->record_exists('grade_items', array('needsupdate' => 1, 'courseid' => $c3->id)));


        // Final check, this time we'll unset the default config.
        unset($CFG->grade_minmaxtouse);
        grade_set_setting($c1->id, 'minmaxtouse', null);

        // Run the upgrade.
        upgrade_minmaxgrade();

        // C1 was updated.
        $this->assertSame((string) GRADE_MIN_MAX_FROM_GRADE_GRADE, grade_get_setting($c1->id, 'minmaxtouse', false, true));

        // Nothing has happened for C3.
        $this->assertFalse($DB->record_exists('config', array('name' => 'show_min_max_grades_changed_' . $c3->id)));
        $this->assertSame(false, grade_get_setting($c3->id, 'minmaxtouse', false, true));
        $this->assertFalse($DB->record_exists('grade_items', array('needsupdate' => 1, 'courseid' => $c3->id)));

        // Restore value.
        $CFG->grade_minmaxtouse = $initialminmax;
    }

    public function test_upgrade_extra_credit_weightoverride() {
        global $DB, $CFG;

        $this->resetAfterTest(true);

        require_once($CFG->libdir . '/db/upgradelib.php');

        $c = array();
        $a = array();
        $gi = array();
        for ($i=0; $i<5; $i++) {
            $c[$i] = $this->getDataGenerator()->create_course();
            $a[$i] = array();
            $gi[$i] = array();
            for ($j=0;$j<3;$j++) {
                $a[$i][$j] = $this->getDataGenerator()->create_module('assign', array('course' => $c[$i], 'grade' => 100));
                $giparams = array('itemtype' => 'mod', 'itemmodule' => 'assign', 'iteminstance' => $a[$i][$j]->id,
                    'courseid' => $c[$i]->id, 'itemnumber' => 0);
                $gi[$i][$j] = grade_item::fetch($giparams);
            }
        }

        // Case 1: Course $c[0] has aggregation method different from natural.
        $coursecategory = grade_category::fetch_course_category($c[0]->id);
        $coursecategory->aggregation = GRADE_AGGREGATE_WEIGHTED_MEAN;
        $coursecategory->update();
        $gi[0][1]->aggregationcoef = 1;
        $gi[0][1]->update();
        $gi[0][2]->weightoverride = 1;
        $gi[0][2]->update();

        // Case 2: Course $c[1] has neither extra credits nor overrides

        // Case 3: Course $c[2] has extra credits but no overrides
        $gi[2][1]->aggregationcoef = 1;
        $gi[2][1]->update();

        // Case 4: Course $c[3] has no extra credits and has overrides
        $gi[3][2]->weightoverride = 1;
        $gi[3][2]->update();

        // Case 5: Course $c[4] has both extra credits and overrides
        $gi[4][1]->aggregationcoef = 1;
        $gi[4][1]->update();
        $gi[4][2]->weightoverride = 1;
        $gi[4][2]->update();

        // Run the upgrade script and make sure only course $c[4] was marked as needed to be fixed.
        upgrade_extra_credit_weightoverride();

        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $c[0]->id}));
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $c[1]->id}));
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $c[2]->id}));
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $c[3]->id}));
        $this->assertEquals(20150619, $CFG->{'gradebook_calculations_freeze_' . $c[4]->id});

        set_config('gradebook_calculations_freeze_' . $c[4]->id, null);

        // Run the upgrade script for a single course only.
        upgrade_extra_credit_weightoverride($c[0]->id);
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $c[0]->id}));
        upgrade_extra_credit_weightoverride($c[4]->id);
        $this->assertEquals(20150619, $CFG->{'gradebook_calculations_freeze_' . $c[4]->id});
    }

    /**
     * Test the upgrade function for flagging courses with calculated grade item problems.
     */
    public function test_upgrade_calculated_grade_items_freeze() {
        global $DB, $CFG;

        $this->resetAfterTest();

        require_once($CFG->libdir . '/db/upgradelib.php');

        // Create a user.
        $user = $this->getDataGenerator()->create_user();

        // Create a couple of courses.
        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $course3 = $this->getDataGenerator()->create_course();

        // Enrol the user in the courses.
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $maninstance1 = $DB->get_record('enrol', array('courseid' => $course1->id, 'enrol' => 'manual'), '*', MUST_EXIST);
        $maninstance2 = $DB->get_record('enrol', array('courseid' => $course2->id, 'enrol' => 'manual'), '*', MUST_EXIST);
        $maninstance3 = $DB->get_record('enrol', array('courseid' => $course3->id, 'enrol' => 'manual'), '*', MUST_EXIST);
        $manual = enrol_get_plugin('manual');
        $manual->enrol_user($maninstance1, $user->id, $studentrole->id);
        $manual->enrol_user($maninstance2, $user->id, $studentrole->id);
        $manual->enrol_user($maninstance3, $user->id, $studentrole->id);

        // To create the data we need we freeze the grade book to use the old behaviour.
        set_config('gradebook_calculations_freeze_' . $course1->id, 20150627);
        set_config('gradebook_calculations_freeze_' . $course2->id, 20150627);
        set_config('gradebook_calculations_freeze_' . $course3->id, 20150627);
        $CFG->grade_minmaxtouse = 2;

        // Creating a category for a grade item.
        $gradecategory = new grade_category();
        $gradecategory->fullname = 'calculated grade category';
        $gradecategory->courseid = $course1->id;
        $gradecategory->insert();
        $gradecategoryid = $gradecategory->id;

        // This is a manual grade item.
        $gradeitem = new grade_item();
        $gradeitem->itemname = 'grade item one';
        $gradeitem->itemtype = 'manual';
        $gradeitem->categoryid = $gradecategoryid;
        $gradeitem->courseid = $course1->id;
        $gradeitem->idnumber = 'gi1';
        $gradeitem->insert();

        // Changing the category into a calculated grade category.
        $gradecategoryitem = grade_item::fetch(array('iteminstance' => $gradecategory->id));
        $gradecategoryitem->calculation = '=##gi' . $gradeitem->id . '##/2';
        $gradecategoryitem->update();

        // Setting a grade for the student.
        $grade = $gradeitem->get_grade($user->id, true);
        $grade->finalgrade = 50;
        $grade->update();
        // Creating all the grade_grade items.
        grade_regrade_final_grades($course1->id);
        // Updating the grade category to a new grade max and min.
        $gradecategoryitem->grademax = 50;
        $gradecategoryitem->grademin = 5;
        $gradecategoryitem->update();

        // Different manual grade item for course 2. We are creating a course with a calculated grade item that has a grade max of
        // 50. The grade_grade will have a rawgrademax of 100 regardless.
        $gradeitem = new grade_item();
        $gradeitem->itemname = 'grade item one';
        $gradeitem->itemtype = 'manual';
        $gradeitem->courseid = $course2->id;
        $gradeitem->idnumber = 'gi1';
        $gradeitem->grademax = 25;
        $gradeitem->insert();

        // Calculated grade item for course 2.
        $calculatedgradeitem = new grade_item();
        $calculatedgradeitem->itemname = 'calculated grade';
        $calculatedgradeitem->itemtype = 'manual';
        $calculatedgradeitem->courseid = $course2->id;
        $calculatedgradeitem->calculation = '=##gi' . $gradeitem->id . '##*2';
        $calculatedgradeitem->grademax = 50;
        $calculatedgradeitem->insert();

        // Assigning a grade for the user.
        $grade = $gradeitem->get_grade($user->id, true);
        $grade->finalgrade = 10;
        $grade->update();

        // Setting all of the grade_grade items.
        grade_regrade_final_grades($course2->id);

        // Different manual grade item for course 3. We are creating a course with a calculated grade item that has a grade max of
        // 50. The grade_grade will have a rawgrademax of 100 regardless.
        $gradeitem = new grade_item();
        $gradeitem->itemname = 'grade item one';
        $gradeitem->itemtype = 'manual';
        $gradeitem->courseid = $course3->id;
        $gradeitem->idnumber = 'gi1';
        $gradeitem->grademax = 25;
        $gradeitem->insert();

        // Calculated grade item for course 2.
        $calculatedgradeitem = new grade_item();
        $calculatedgradeitem->itemname = 'calculated grade';
        $calculatedgradeitem->itemtype = 'manual';
        $calculatedgradeitem->courseid = $course3->id;
        $calculatedgradeitem->calculation = '=##gi' . $gradeitem->id . '##*2';
        $calculatedgradeitem->grademax = 50;
        $calculatedgradeitem->insert();

        // Assigning a grade for the user.
        $grade = $gradeitem->get_grade($user->id, true);
        $grade->finalgrade = 10;
        $grade->update();

        // Setting all of the grade_grade items.
        grade_regrade_final_grades($course3->id);
        // Need to do this first before changing the other courses, otherwise they will be flagged too early.
        set_config('gradebook_calculations_freeze_' . $course3->id, null);
        upgrade_calculated_grade_items($course3->id);
        $this->assertEquals(20150627, $CFG->{'gradebook_calculations_freeze_' . $course3->id});

        // Change the setting back to null.
        set_config('gradebook_calculations_freeze_' . $course1->id, null);
        set_config('gradebook_calculations_freeze_' . $course2->id, null);
        // Run the upgrade.
        upgrade_calculated_grade_items();
        // The setting should be set again after the upgrade.
        $this->assertEquals(20150627, $CFG->{'gradebook_calculations_freeze_' . $course1->id});
        $this->assertEquals(20150627, $CFG->{'gradebook_calculations_freeze_' . $course2->id});
    }

    function test_upgrade_calculated_grade_items_regrade() {
        global $DB, $CFG;

        $this->resetAfterTest();

        require_once($CFG->libdir . '/db/upgradelib.php');

        // Create a user.
        $user = $this->getDataGenerator()->create_user();

        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        // Enrol the user in the course.
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $maninstance1 = $DB->get_record('enrol', array('courseid' => $course->id, 'enrol' => 'manual'), '*', MUST_EXIST);
        $manual = enrol_get_plugin('manual');
        $manual->enrol_user($maninstance1, $user->id, $studentrole->id);

        set_config('upgrade_calculatedgradeitemsonlyregrade', 1);

        // Creating a category for a grade item.
        $gradecategory = new grade_category();
        $gradecategory->fullname = 'calculated grade category';
        $gradecategory->courseid = $course->id;
        $gradecategory->insert();
        $gradecategoryid = $gradecategory->id;

        // This is a manual grade item.
        $gradeitem = new grade_item();
        $gradeitem->itemname = 'grade item one';
        $gradeitem->itemtype = 'manual';
        $gradeitem->categoryid = $gradecategoryid;
        $gradeitem->courseid = $course->id;
        $gradeitem->idnumber = 'gi1';
        $gradeitem->insert();

        // Changing the category into a calculated grade category.
        $gradecategoryitem = grade_item::fetch(array('iteminstance' => $gradecategory->id));
        $gradecategoryitem->calculation = '=##gi' . $gradeitem->id . '##/2';
        $gradecategoryitem->grademax = 50;
        $gradecategoryitem->grademin = 15;
        $gradecategoryitem->update();

        // Setting a grade for the student.
        $grade = $gradeitem->get_grade($user->id, true);
        $grade->finalgrade = 50;
        $grade->update();

        grade_regrade_final_grades($course->id);
        $grade = grade_grade::fetch(array('itemid' => $gradecategoryitem->id, 'userid' => $user->id));
        $grade->rawgrademax = 100;
        $grade->rawgrademin = 0;
        $grade->update();
        $this->assertNotEquals($gradecategoryitem->grademax, $grade->rawgrademax);
        $this->assertNotEquals($gradecategoryitem->grademin, $grade->rawgrademin);

        // This is the function that we are testing. If we comment out this line, then the test fails because the grade items
        // are not flagged for regrading.
        upgrade_calculated_grade_items();
        grade_regrade_final_grades($course->id);

        $grade = grade_grade::fetch(array('itemid' => $gradecategoryitem->id, 'userid' => $user->id));

        $this->assertEquals($gradecategoryitem->grademax, $grade->rawgrademax);
        $this->assertEquals($gradecategoryitem->grademin, $grade->rawgrademin);
    }

    /**
     * Test libcurl custom check api.
     */
    public function test_check_libcurl_version() {
        global $CFG;
        require_once("$CFG->dirroot/lib/environmentlib.php");

        $supportedversion = 0x071304;
        $curlinfo = curl_version();
        $currentversion = $curlinfo['version_number'];

        $result = new environment_results("custom_checks");
        if ($currentversion < $supportedversion) {
            $this->assertFalse(check_libcurl_version($result)->getStatus());
        } else {
            $this->assertNull(check_libcurl_version($result));
        }
    }

    /**
     * Test that the upgrade script correctly flags courses to be frozen due to letter boundary problems.
     */
    public function test_upgrade_course_letter_boundary() {
        global $CFG, $DB;
        $this->resetAfterTest(true);

        require_once($CFG->libdir . '/db/upgradelib.php');

        // Create a user.
        $user = $this->getDataGenerator()->create_user();

        // Create some courses.
        $courses = array();
        $contexts = array();
        for ($i = 0; $i < 45; $i++) {
            $course = $this->getDataGenerator()->create_course();
            $context = context_course::instance($course->id);
            if (in_array($i, array(2, 5, 10, 13, 14, 19, 23, 25, 30, 34, 36))) {
                // Assign good letter boundaries.
                $this->assign_good_letter_boundary($context->id);
            }
            if (in_array($i, array(3, 6, 11, 15, 20, 24, 26, 31, 35))) {
                // Assign bad letter boundaries.
                $this->assign_bad_letter_boundary($context->id);
            }

            if (in_array($i, array(3, 9, 10, 11, 18, 19, 20, 29, 30, 31, 40))) {
                grade_set_setting($course->id, 'displaytype', '3');
            } else if (in_array($i, array(8, 17, 28))) {
                grade_set_setting($course->id, 'displaytype', '2');
            }

            if (in_array($i, array(37, 43))) {
                // Show.
                grade_set_setting($course->id, 'report_user_showlettergrade', '1');
            } else if (in_array($i, array(38, 42))) {
                // Hide.
                grade_set_setting($course->id, 'report_user_showlettergrade', '0');
            }

            $assignrow = $this->getDataGenerator()->create_module('assign', array('course' => $course->id, 'name' => 'Test!'));
            $gi = grade_item::fetch(
                    array('itemtype' => 'mod',
                          'itemmodule' => 'assign',
                          'iteminstance' => $assignrow->id,
                          'courseid' => $course->id));
            if (in_array($i, array(6, 13, 14, 15, 23, 24, 34, 35, 36, 41))) {
                grade_item::set_properties($gi, array('display' => 3));
                $gi->update();
            } else if (in_array($i, array(12, 21, 32))) {
                grade_item::set_properties($gi, array('display' => 2));
                $gi->update();
            }
            $gradegrade = new grade_grade();
            $gradegrade->itemid = $gi->id;
            $gradegrade->userid = $user->id;
            $gradegrade->rawgrade = 55.5563;
            $gradegrade->finalgrade = 55.5563;
            $gradegrade->rawgrademax = 100;
            $gradegrade->rawgrademin = 0;
            $gradegrade->timecreated = time();
            $gradegrade->timemodified = time();
            $gradegrade->insert();

            $contexts[] = $context;
            $courses[] = $course;
        }

        upgrade_course_letter_boundary();

        // No system setting for grade letter boundaries.
        // [0] A course with no letter boundaries.
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[0]->id}));
        // [1] A course with letter boundaries which are default.
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[1]->id}));
        // [2] A course with letter boundaries which are custom but not affected.
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[2]->id}));
        // [3] A course with letter boundaries which are custom and will be affected.
        $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[3]->id});
        // [4] A course with no letter boundaries, but with a grade item with letter boundaries which are default.
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[4]->id}));
        // [5] A course with no letter boundaries, but with a grade item with letter boundaries which are not default, but not affected.
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[5]->id}));
        // [6] A course with no letter boundaries, but with a grade item with letter boundaries which are not default which will be affected.
        $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[6]->id});

        // System setting for grade letter boundaries (default).
        set_config('grade_displaytype', '3');
        for ($i = 0; $i < 45; $i++) {
            unset_config('gradebook_calculations_freeze_' . $courses[$i]->id);
        }
        upgrade_course_letter_boundary();

        // [7] A course with no grade display settings for the course or grade items.
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[7]->id}));
        // [8] A course with grade display settings, but for something that isn't letters.
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[8]->id}));
        // [9] A course with grade display settings of letters which are default.
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[9]->id}));
        // [10] A course with grade display settings of letters which are not default, but not affected.
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[10]->id}));
        // [11] A course with grade display settings of letters which are not default, which will be affected.
        $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[11]->id});
        // [12] A grade item with display settings that are not letters.
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[12]->id}));
        // [13] A grade item with display settings of letters which are default.
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[13]->id}));
        // [14] A grade item with display settings of letters which are not default, but not affected.
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[14]->id}));
        // [15] A grade item with display settings of letters which are not default, which will be affected.
        $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[15]->id});

        // System setting for grade letter boundaries (custom with problem).
        $systemcontext = context_system::instance();
        $this->assign_bad_letter_boundary($systemcontext->id);
        for ($i = 0; $i < 45; $i++) {
            unset_config('gradebook_calculations_freeze_' . $courses[$i]->id);
        }
        upgrade_course_letter_boundary();

        // [16] A course with no grade display settings for the course or grade items.
        $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[16]->id});
        // [17] A course with grade display settings, but for something that isn't letters.
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[17]->id}));
        // [18] A course with grade display settings of letters which are default.
        $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[18]->id});
        // [19] A course with grade display settings of letters which are not default, but not affected.
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[19]->id}));
        // [20] A course with grade display settings of letters which are not default, which will be affected.
        $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[20]->id});
        // [21] A grade item with display settings which are not letters. Grade total will be affected so should be frozen.
        $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[21]->id});
        // [22] A grade item with display settings of letters which are default.
        $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[22]->id});
        // [23] A grade item with display settings of letters which are not default, but not affected. Course uses new letter boundary setting.
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[23]->id}));
        // [24] A grade item with display settings of letters which are not default, which will be affected.
        $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[24]->id});
        // [25] A course which is using the default grade display setting, but has updated the grade letter boundary (not 57) Should not be frozen.
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[25]->id}));
        // [26] A course that is using the default display setting (letters) and altered the letter boundary with 57. Should be frozen.
        $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[26]->id});

        // System setting not showing letters.
        set_config('grade_displaytype', '2');
        for ($i = 0; $i < 45; $i++) {
            unset_config('gradebook_calculations_freeze_' . $courses[$i]->id);
        }
        upgrade_course_letter_boundary();

        // [27] A course with no grade display settings for the course or grade items.
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[27]->id}));
        // [28] A course with grade display settings, but for something that isn't letters.
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[28]->id}));
        // [29] A course with grade display settings of letters which are default.
        $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[29]->id});
        // [30] A course with grade display settings of letters which are not default, but not affected.
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[30]->id}));
        // [31] A course with grade display settings of letters which are not default, which will be affected.
        $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[31]->id});
        // [32] A grade item with display settings which are not letters.
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[32]->id}));
        // [33] All system defaults.
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[33]->id}));
        // [34] A grade item with display settings of letters which are not default, but not affected. Course uses new letter boundary setting.
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[34]->id}));
        // [35] A grade item with display settings of letters which are not default, which will be affected.
        $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[35]->id});
        // [36] A course with grade display settings of letters with modified and good boundary (not 57) Should not be frozen.
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[36]->id}));

        // Previous site conditions still exist.
        for ($i = 0; $i < 45; $i++) {
            unset_config('gradebook_calculations_freeze_' . $courses[$i]->id);
        }
        upgrade_course_letter_boundary();

        // [37] Site setting for not showing the letter column and course setting set to show (frozen).
        $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[37]->id});
        // [38] Site setting for not showing the letter column and course setting set to hide.
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[38]->id}));
        // [39] Site setting for not showing the letter column and course setting set to default.
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[39]->id}));
        // [40] Site setting for not showing the letter column and course setting set to default. Course display set to letters (frozen).
        $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[40]->id});
        // [41] Site setting for not showing the letter column and course setting set to default. Grade item display set to letters (frozen).
        $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[41]->id});

        // Previous site conditions still exist.
        for ($i = 0; $i < 45; $i++) {
            unset_config('gradebook_calculations_freeze_' . $courses[$i]->id);
        }
        set_config('grade_report_user_showlettergrade', '1');
        upgrade_course_letter_boundary();

        // [42] Site setting for showing the letter column, but course setting set to hide.
        $this->assertTrue(empty($CFG->{'gradebook_calculations_freeze_' . $courses[42]->id}));
        // [43] Site setting for showing the letter column and course setting set to show (frozen).
        $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[43]->id});
        // [44] Site setting for showing the letter column and course setting set to default (frozen).
        $this->assertEquals(20160518, $CFG->{'gradebook_calculations_freeze_' . $courses[44]->id});
    }

    /**
     * Test upgrade_letter_boundary_needs_freeze function.
     */
    public function test_upgrade_letter_boundary_needs_freeze() {
        global $CFG;

        $this->resetAfterTest();

        require_once($CFG->libdir . '/db/upgradelib.php');

        $courses = array();
        $contexts = array();
        for ($i = 0; $i < 3; $i++) {
            $courses[] = $this->getDataGenerator()->create_course();
            $contexts[] = context_course::instance($courses[$i]->id);
        }

        // Course one is not using a letter boundary.
        $this->assertFalse(upgrade_letter_boundary_needs_freeze($contexts[0]));

        // Let's make course 2 use the bad boundary.
        $this->assign_bad_letter_boundary($contexts[1]->id);
        $this->assertTrue(upgrade_letter_boundary_needs_freeze($contexts[1]));
        // Course 3 has letter boundaries that are fine.
        $this->assign_good_letter_boundary($contexts[2]->id);
        $this->assertFalse(upgrade_letter_boundary_needs_freeze($contexts[2]));
        // Try the system context not using a letter boundary.
        $systemcontext = context_system::instance();
        $this->assertFalse(upgrade_letter_boundary_needs_freeze($systemcontext));
    }

    /**
     * Assigns letter boundaries with comparison problems.
     *
     * @param int $contextid Context ID.
     */
    private function assign_bad_letter_boundary($contextid) {
        global $DB;
        $newlettersscale = array(
                array('contextid' => $contextid, 'lowerboundary' => 90.00000, 'letter' => 'A'),
                array('contextid' => $contextid, 'lowerboundary' => 85.00000, 'letter' => 'A-'),
                array('contextid' => $contextid, 'lowerboundary' => 80.00000, 'letter' => 'B+'),
                array('contextid' => $contextid, 'lowerboundary' => 75.00000, 'letter' => 'B'),
                array('contextid' => $contextid, 'lowerboundary' => 70.00000, 'letter' => 'B-'),
                array('contextid' => $contextid, 'lowerboundary' => 65.00000, 'letter' => 'C+'),
                array('contextid' => $contextid, 'lowerboundary' => 57.00000, 'letter' => 'C'),
                array('contextid' => $contextid, 'lowerboundary' => 50.00000, 'letter' => 'C-'),
                array('contextid' => $contextid, 'lowerboundary' => 40.00000, 'letter' => 'D+'),
                array('contextid' => $contextid, 'lowerboundary' => 25.00000, 'letter' => 'D'),
                array('contextid' => $contextid, 'lowerboundary' => 0.00000, 'letter' => 'F'),
            );

        $DB->delete_records('grade_letters', array('contextid' => $contextid));
        foreach ($newlettersscale as $record) {
            // There is no API to do this, so we have to manually insert into the database.
            $DB->insert_record('grade_letters', $record);
        }
    }

    /**
     * Assigns letter boundaries with no comparison problems.
     *
     * @param int $contextid Context ID.
     */
    private function assign_good_letter_boundary($contextid) {
        global $DB;
        $newlettersscale = array(
                array('contextid' => $contextid, 'lowerboundary' => 90.00000, 'letter' => 'A'),
                array('contextid' => $contextid, 'lowerboundary' => 85.00000, 'letter' => 'A-'),
                array('contextid' => $contextid, 'lowerboundary' => 80.00000, 'letter' => 'B+'),
                array('contextid' => $contextid, 'lowerboundary' => 75.00000, 'letter' => 'B'),
                array('contextid' => $contextid, 'lowerboundary' => 70.00000, 'letter' => 'B-'),
                array('contextid' => $contextid, 'lowerboundary' => 65.00000, 'letter' => 'C+'),
                array('contextid' => $contextid, 'lowerboundary' => 54.00000, 'letter' => 'C'),
                array('contextid' => $contextid, 'lowerboundary' => 50.00000, 'letter' => 'C-'),
                array('contextid' => $contextid, 'lowerboundary' => 40.00000, 'letter' => 'D+'),
                array('contextid' => $contextid, 'lowerboundary' => 25.00000, 'letter' => 'D'),
                array('contextid' => $contextid, 'lowerboundary' => 0.00000, 'letter' => 'F'),
            );

        $DB->delete_records('grade_letters', array('contextid' => $contextid));
        foreach ($newlettersscale as $record) {
            // There is no API to do this, so we have to manually insert into the database.
            $DB->insert_record('grade_letters', $record);
        }
    }
}
