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
 * @author Eugene Venter <eugene@catalyst.net.nz>
 * @package totara
 * @subpackage reportbuilder
 */

define('AJAX_SCRIPT', true);

define('REPORTBUIDLER_MANAGE_REPORTS_PAGE', true);
define('REPORT_BUILDER_IGNORE_PAGE_PARAMETERS', true); // We are setting up report here, do not accept source params.

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot.'/totara/reportbuilder/lib.php');

$PAGE->set_context(context_system::instance());

/// Check access
require_login();
require_sesskey();

/// Get params
$action = required_param('action', PARAM_ALPHA);
$reportid = required_param('id', PARAM_INT);

// Make sure the report actually exists.
$rawreport = $DB->get_record('report_builder', array('id' => $reportid), '*', MUST_EXIST);

$capability = $rawreport->embedded ? 'totara/reportbuilder:manageembeddedreports' : 'totara/reportbuilder:managereports';
require_capability($capability, context_system::instance());

$result = new stdClass();

switch ($action) {
    case 'add' :
        $column = required_param('col', PARAM_ALPHANUMEXT);
        $advanced = required_param('advanced', PARAM_ALPHANUMEXT);
        $customheading = required_param('customheading', PARAM_BOOL);
        $heading = optional_param('heading', '', PARAM_TEXT);

        $config = (new rb_config())->set_nocache(true);
        $report = reportbuilder::create($reportid, $config, false); // No access control for managing of reports here.

        $allowedadvanced = $report->src->get_allowed_advanced_column_options();
        $grouped = $report->src->get_grouped_column_options();
        $advoptions = $report->src->get_all_advanced_column_options();

        $parts = explode('-', $column);
        $coltype = $parts[0];
        $colvalue = $parts[1];

        if (!$columnoption = reportbuilder::get_single_item($report->columnoptions, $coltype, $colvalue)) {
            $result->error = get_string('error');
            break;
        }

        if (in_array($column, $grouped)) {
            $advanced = '';
        } else if (empty($advanced)) {
            $advanced = '';
        } else if (!in_array($advanced, $allowedadvanced[$column], true)) {
            $advanced = '';
        }

        $transform = null;
        $aggregate = null;
        if (empty($advanced)) {
            // Nothing.
        } else if (strpos($advanced, 'transform_') === 0) {
            $transform = str_replace('transform_', '', $advanced);
            $aggregate = null;
        } else if (strpos($advanced, 'aggregate_') === 0) {
            $transform = null;
            $aggregate = str_replace('aggregate_', '', $advanced);
        }

        if ($customheading) {
            if (trim($heading) === '') {
                $result->error = get_string('noemptycols', 'totara_reportbuilder');
                $result->noreload = true;
            }
        } else {
            $heading = null;
        }

        /// Prevent duplicates
        $params = array('reportid' => $reportid, 'type' => $coltype, 'value' => $colvalue);
        if ($DB->record_exists('report_builder_columns', $params)) {
            $result->error = get_string('norepeatcols', 'totara_reportbuilder');
            break;
        }

        /// Save column
        $todb = new stdClass();
        $todb->reportid = $reportid;
        $todb->type = $coltype;
        $todb->value = $colvalue;
        $todb->transform = $transform;
        $todb->aggregate = $aggregate;
        $todb->customheading = $customheading;
        $todb->heading = $heading;
        $sortorder = 1 + $DB->get_field('report_builder_columns', 'MAX(sortorder)', array('reportid' => $reportid));
        $todb->sortorder = $sortorder;
        $id = $DB->insert_record('report_builder_columns', $todb);
        reportbuilder_set_status($reportid);

        $result->success = true;
        $result->result = $id;
        break;

    case 'delete':
        $colid = required_param('cid', PARAM_INT);
        $deprecated = optional_param('deprecated', false, PARAM_BOOL);
        $sql = 'SELECT rbc.*, rb.source
                  FROM {report_builder_columns} rbc
                  JOIN {report_builder} rb ON rbc.reportid = rb.id
                 WHERE rbc.id = ?';
        $params = array($colid);

        $column = $DB->get_record_sql($sql, $params);

        $graphseries = $DB->get_field('report_builder_graph', 'series', array('reportid' => $reportid));
        if ($graphseries) {
            $source = implode('-', array($column->type, $column->value));
            $datasources = json_decode($graphseries, true);
            if (in_array($source, $datasources)) {
                $result->success = false;
                $result->noalert = true;
                totara_set_notification(get_string('error:graphdeleteseries', 'totara_reportbuilder'));
                break;
            }
        }

        $DB->delete_records('report_builder_columns', array('id' => $colid, 'reportid' => $reportid));
        reportbuilder_set_status($reportid);

        if ($column) {
            $column->deprecated = false;
            // Get the column group name.
            // If the column is deprecated, just put it back to its deprecated option group.
            if ($deprecated) {
                $column->optgroup_label = get_string('type_deprecated', 'totara_reportbuilder');
                $column->deprecated = true;
            } else if (get_string_manager()->string_exists('type_' . $column->type, 'rb_source_' . $column->source)) {
                // Is there a type string in the source file?
                $column->optgroup_label = get_string('type_' . $column->type, 'rb_source_' . $column->source);
                // How about in report builder?
            } else if (get_string_manager()->string_exists('type_' . $column->type, 'totara_reportbuilder')) {
                $column->optgroup_label = get_string('type_' . $column->type, 'totara_reportbuilder');
            } else {
                // Not found, display in missing string format to make it obvious.
                $column->optgroup_label = get_string_manager()->get_string('type_' . $column->type, 'rb_source_' . $column->source);
            }

            $result->success = true;
            $result->result = $column;
        } else {
            $result->error = true;
        }
        break;

    case 'hide':
        $colid = required_param('cid', PARAM_INT);

        $todb = new stdClass();
        $todb->id = $colid;
        $todb->hidden = 1;
        $todb->reportid = $reportid;
        $DB->update_record('report_builder_columns', $todb);
        reportbuilder_set_status($reportid);

        // Do not bother with errors here.
        $result->success = true;
        break;

    case 'show':
        $colid = required_param('cid', PARAM_INT);

        $todb = new stdClass();
        $todb->id = $colid;
        $todb->hidden = 0;
        $todb->reportid = $reportid;
        $DB->update_record('report_builder_columns', $todb);
        reportbuilder_set_status($reportid);

        // Do not bother with errors here.
        $result->success = true;
        break;

    case 'movedown':
    case 'moveup':
        $colid = required_param('cid', PARAM_INT);

        $operator = ($action == 'movedown') ? '>' : '<';
        $sortorder = ($action == 'movedown') ? 'ASC' : 'DESC';

        $col = $DB->get_record('report_builder_columns', array('id' => $colid));
        $sql = "SELECT *
                  FROM {report_builder_columns}
                 WHERE reportid = ? AND sortorder $operator ?
              ORDER BY sortorder $sortorder";
        if (!$sibling = $DB->get_record_sql($sql, array($reportid, $col->sortorder), IGNORE_MULTIPLE)) {
            $result->error = true;
            break;
        }

        $transaction = $DB->start_delegated_transaction();

        $todb = new stdClass();
        $todb->id = $col->id;
        $todb->sortorder = $sibling->sortorder;
        $DB->update_record('report_builder_columns', $todb);

        $todb = new stdClass();
        $todb->id = $sibling->id;
        $todb->sortorder = $col->sortorder;
        $DB->update_record('report_builder_columns', $todb);
        reportbuilder_set_status($reportid);

        $transaction->allow_commit();

        $result->success = true;
        break;

    default:
        $result->error = get_string('error');
        break;
}

// Update current session.
if (!isset($result->error) && isset($SESSION->rb_showhide_columns[$rawreport->shortname])) {
    unset($SESSION->rb_showhide_columns[$rawreport->shortname]);
}

echo $OUTPUT->header();
echo json_encode($result);
die;
