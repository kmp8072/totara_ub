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
 * @author David Curry <david.curry@totaralms.com>
 * @package mod_facetoface
 */

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . '/mod/facetoface/lib.php');

ajax_require_login();

$users = required_param('users', PARAM_SEQUENCE);
$fid = required_param('fid', PARAM_INT);

$PAGE->set_context(context_system::instance());

$out = html_writer::start_tag('div', array('id' => 'activityapproverbox', 'class' => 'activity_approvers'));

foreach (explode(',', trim($users, ',')) as $userid) {
    $user = core_user::get_user($userid);
    $out .= facetoface_display_approver($user, true);
}

$out .= html_writer::end_tag('div');

echo "DONE{$out}";
exit();