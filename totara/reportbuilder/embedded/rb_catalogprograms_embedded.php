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
 * @author Simon Coggins <simon.coggins@totaralms.com>
 * @package totara
 * @subpackage reportbuilder
 */

require_once($CFG->dirroot . '/totara/reportbuilder/embedded/rb_findprograms_embedded.php');

/**
 * We just need another embedded report source for enhanced catalog to keep settings separately
 */
class rb_catalogprograms_embedded extends rb_base_embedded {

    public function __construct($data) {
        $this->url = $this->url = '/totara/coursecatalog/programs.php';
        $this->source = 'program';
        $this->shortname = 'catalogprograms';
        $this->fullname = get_string('reportbasedprograms', 'totara_coursecatalog');

        $this->columns = array(
            array(
                'type' => 'prog',
                'value' => 'progexpandlink',
                'heading' => get_string('programname', 'totara_program'),
            ),
            array(
                'type' => 'prog',
                'value' => 'summary',
                'heading' => get_string('programsummary', 'totara_program'),
            )
        );

        $this->filters = array(
        );

        $this->toolbarsearchcolumns = array(
            array(
                'type' => 'prog',
                'value' => 'fullname'
            ),
            array(
                'type' => 'prog',
                'value' => 'summary'
            ),
        );

        parent::__construct($data);
    }

    /**
     * Check if the user is capable of accessing this report.
     * We use $reportfor instead of $USER->id and $report->get_param_value() instead of getting report params
     * some other way so that the embedded report will be compatible with the scheduler (in the future).
     *
     * @param int $reportfor userid of the user that this report is being generated for
     * @param reportbuilder $report the report object - can use get_param_value to get params
     * @return boolean true if the user can access this report
     */
    public function is_capable($reportfor, $report) {
        return true;
    }

    /**
     * Returns true if require_login should be executed when the report is access through a page other than
     * report.php or an embedded report's webpage, e.g. through ajax calls.
     *
     * @return boolean True if require_login should be executed
     */
    public function needs_require_login() {
        global $CFG;
        return $CFG->forcelogin;
    }

    public function get_extrabuttons() {
        global $OUTPUT;

        $categoryid = totara_get_categoryid_with_capability('totara/program:createprogram');

        if ($categoryid !== false) {
            $createurl = new moodle_url("/totara/program/add.php", array('category' => $categoryid));
            $createbutton = new single_button($createurl, get_string('addprogram', 'totara_coursecatalog'), 'get');
            return $OUTPUT->render($createbutton);
        }

        return false;
    }
}
