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
 * @package totara
 * @subpackage reportbuilder
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/totara/hierarchy/prefix/goal/lib.php');
require_once($CFG->dirroot . '/totara/hierarchy/prefix/goal/renderer.php');
require_once($CFG->dirroot . '/totara/reportbuilder/lib.php');

$summaryreportid = required_param('summaryreportid', PARAM_INT);

// Set page context.
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);

$output = $PAGE->get_renderer('hierarchy_goal');

$report = reportbuilder::create($summaryreportid, null, true);

$fullname = $report->fullname;

// Start page output.
$PAGE->set_url('/totara/hierarchy/rb_source/goalsummaryselector.php', array('summaryreportid' => $summaryreportid));
$PAGE->set_totara_menu_selected('myreports');
$PAGE->set_pagelayout('noblocks');
$heading = get_string('goals', 'totara_hierarchy');
$PAGE->set_title($heading);
$PAGE->set_heading($heading);
$PAGE->navbar->add(get_string('reports', 'totara_core'), new moodle_url('/my/reports.php'));
$PAGE->navbar->add($fullname);

echo $output->header();

echo $output->heading($fullname);
$goal = new goal();
$goalframeworks = $goal->get_frameworks();
echo $output->summary_report_table($summaryreportid, $goalframeworks);

echo $output->footer();
