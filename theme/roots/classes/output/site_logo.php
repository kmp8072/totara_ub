<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2016 onwards Totara Learning Solutions LTD
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
 * @copyright 2016 onwards Totara Learning Solutions LTD
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Joby Harding <joby.harding@totaralearning.com>
 * @package   theme_roots
 */

namespace theme_roots\output;

defined('MOODLE_INTERNAL') || die();

class site_logo implements \renderable, \templatable {

    /**
     * @var string
     */
    public $siteshortname = '';

    /***
     * site_logo constructor.
     *
     * @param string $shortname Site shortname.
     */
    public function __construct($shortname) {

        $this->siteshortname = $shortname;

    }

    /**
     * Implements export_for_template().
     * 
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(\renderer_base $output) {

        global $PAGE, $SITE, $OUTPUT, $CFG;

        $templatecontext = array(
            'siteurl' => $CFG->wwwroot,
            'shortname' => $this->siteshortname,
        );

        $templatecontext['logourl'] = $PAGE->theme->setting_file_url('logo', 'logo');
        $templateocontext['logoalt'] = get_string('logo', 'theme_standardtotararesponsive', $SITE->fullname);

        if (!empty($PAGE->theme->settings->logo)) {
            $templatecontext['logourl'] = $PAGE->theme->setting_file_url('logo', 'logo');
            $templatecontext['logoalt'] = get_string('logo', 'theme_standardtotararesponsive', $SITE->fullname);
        } else {
            $templatecontext['logourl'] = $OUTPUT->pix_url('logo', 'theme');
            $templatecontext['logoalt'] = get_string('totaralogo', 'theme_standardtotararesponsive');
        }

        if (!empty($PAGE->theme->settings->alttext)) {
            $templatecontext['logoalt'] = format_string($PAGE->theme->settings->alttext);
        }

        if (!empty($PAGE->theme->settings->favicon)) {
            $templatecontext['faviconurl'] = $PAGE->theme->setting_file_url('favicon', 'favicon');
        } else {
            $templatecontext['faviconurl'] = $OUTPUT->favicon();
        }

        return $templatecontext;

    }

}