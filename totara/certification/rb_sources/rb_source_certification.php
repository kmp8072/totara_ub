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
 * @author Valerii Kuznetsov <valerii.kuznetsov@totaralms.com>
 * @package totara
 * @subpackage reportbuilder
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

class rb_source_certification extends rb_base_source {
    use \core_course\rb\source\report_trait;
    use \totara_cohort\rb\source\report_trait;
    use \totara_certification\rb\source\certification_trait;

    public function __construct() {
        $this->base = "(SELECT p.*, c.learningcomptype, c.activeperiod, c.minimumactiveperiod, c.windowperiod, c.recertifydatetype
                          FROM {prog} p
                          JOIN {certif} c ON c.id = p.certifid)";
        $this->joinlist = $this->define_joinlist();
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->contentoptions = $this->define_contentoptions();
        $this->paramoptions = $this->define_paramoptions();
        $this->defaultcolumns = $this->define_defaultcolumns();
        $this->defaultfilters = $this->define_defaultfilters();
        $this->requiredcolumns = $this->define_requiredcolumns();
        $this->sourcetitle = get_string('sourcetitle', 'rb_source_certification');
        $this->usedcomponents[] = 'totara_certification';
        $this->usedcomponents[] = "totara_program";
        $this->usedcomponents[] = 'totara_cohort';

        $this->cacheable = false;

        // Add custom fields.
        $this->add_totara_customfield_component(
            'prog', 'base', 'programid',
            $this->joinlist, $this->columnoptions, $this->filteroptions
        );

        parent::__construct();
    }

    /**
     * Hide this source if feature disabled or hidden.
     * @return bool
     */
    public function is_ignored() {
        return !totara_feature_visible('certifications');
    }

    /**
     * Global report restrictions are implemented in this source.
     * @return boolean
     */
    public function global_restrictions_supported() {
        return false;
    }

    protected function define_joinlist() {
        global $CFG;

        $joinlist = array(
            new rb_join(
                'ctx',
                'INNER',
                '{context}',
                'ctx.instanceid = base.id AND ctx.contextlevel = ' . CONTEXT_PROGRAM,
                REPORT_BUILDER_RELATION_ONE_TO_ONE
            ),
        );

        $this->add_core_course_category_tables($joinlist, 'base', 'category');
        $this->add_totara_cohort_program_tables($joinlist, 'base', 'id');

        return $joinlist;
    }

    protected function define_columnoptions() {

        // Include some standard columns
        $this->add_totara_certification_columns($columnoptions, 'base');
        $this->add_core_course_category_columns($columnoptions, 'course_category', 'base', 'programcount');
        $this->add_totara_cohort_program_columns($columnoptions);

        return $columnoptions;
    }


    protected function define_filteroptions() {
        $filteroptions = array();

        // Include some standard filters
        $this->add_totara_certification_filters($filteroptions);
        $this->add_core_course_category_filters($filteroptions);
        $this->add_totara_cohort_program_filters($filteroptions, 'totara_certification');

        return $filteroptions;
    }

    protected function define_paramoptions() {
        $paramoptions = array(
            new rb_param_option(
                'programid',
                'base.id'
            ),
            new rb_param_option(
                'visible',
                'base.visible'
            ),
            new rb_param_option(
                'category',
                'base.category'
            ),
        );
        return $paramoptions;
    }

    protected function define_defaultcolumns() {
        $defaultcolumns = array(
            array(
                'type' => 'certif',
                'value' => 'proglinkicon',
            ),
            array(
                'type' => 'course_category',
                'value' => 'namelink',
            ),
        );
        return $defaultcolumns;
    }

    protected function define_defaultfilters() {
        $defaultfilters = array(
            array(
                'type' => 'certif',
                'value' => 'fullname',
                'advanced' => 0,
            ),
            array(
                'type' => 'course_category',
                'value' => 'path',
                'advanced' => 0,
            ),
        );
        return $defaultfilters;
    }

    protected function define_requiredcolumns() {
        $requiredcolumns = array();

        $requiredcolumns[] = new rb_column(
            'ctx',
            'id',
            '',
            "ctx.id",
            array('joins' => 'ctx')
        );

        // Visibility.
        $requiredcolumns[] = new rb_column(
            'visibility',
            'id',
            '',
            "base.id"
        );

        $requiredcolumns[] = new rb_column(
            'visibility',
            'visible',
            '',
            "base.visible"
        );

        $requiredcolumns[] = new rb_column(
            'visibility',
            'audiencevisible',
            '',
            "base.audiencevisible"
        );

        $requiredcolumns[] = new rb_column(
            'base',
            'available',
            '',
            "base.available"
        );

        $requiredcolumns[] = new rb_column(
            'base',
            'availablefrom',
            '',
            "base.availablefrom"
        );

        $requiredcolumns[] = new rb_column(
            'base',
            'availableuntil',
            '',
            "base.availableuntil"
        );

        return $requiredcolumns;
    }

    public function post_config(reportbuilder $report) {
        $reportfor = $report->reportfor; // ID of the user the report is for.
        $report->set_post_config_restrictions($report->post_config_visibility_where('certification', 'base', $reportfor));
    }

}
