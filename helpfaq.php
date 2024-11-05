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
 * Overview page for eportfolio
 *
 * @package local_eportfolio
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

// First check, if user is logged in before accessing this page.
require_login();

if (isguestuser()) {
    redirect(new moodle_url($CFG->wwwroot),
            get_string('error:noguestaccess', 'local_eportfolio'),
            null, \core\output\notification::NOTIFY_ERROR);
}

if (!has_capability('local/eportfolio:view_eport', context_system::instance())) {
    redirect(new moodle_url($CFG->wwwroot),
            get_string('error:missingcapability', 'local_eportfolio'),
            null, \core\output\notification::NOTIFY_ERROR);
}

$url = new moodle_url('/local/eportfolio/index.php');
$overviewurl = new moodle_url('/local/eportfolio/index.php');
$context = context_user::instance($USER->id);

// Set page layout.
$PAGE->set_url($url);
$PAGE->set_context(context_user::instance($USER->id));
$PAGE->set_title(get_string('helpfaq:header', 'local_eportfolio'));
$PAGE->set_heading(get_string('helpfaq:header', 'local_eportfolio'));
$PAGE->set_pagelayout('base');
$PAGE->add_body_class('limitedwith');

// Print the header.
echo $OUTPUT->header();

$data = new stdClass();

$data->backurl = new moodle_url('/local/eportfolio/index.php');
$data->backurlstring = get_string('view:eportfolio:button:backtoeportfolio', 'local_eportfolio');

echo $OUTPUT->render_from_template('local_eportfolio/helpfaq', $data);

echo $OUTPUT->footer();
