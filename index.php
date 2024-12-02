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
 * Overview page for ePortfolio
 *
 * @package local_eportfolio
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('locallib.php');
require_once('classes/local/overview.php');
require_once($CFG->libdir . '/tablelib.php');

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

// What page are we displaying?
$section = optional_param('section', 'my', PARAM_ALPHA);

$id = optional_param('id', 0, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);
$confirm = optional_param('confirm', '', PARAM_ALPHANUM);

$tsort = optional_param('tsort', '', PARAM_ALPHA);
$tdir = optional_param('tdir', 0, PARAM_INT);

$urlparams = [];
$urlparams['section'] = $section;

if ($tsort) {
    $urlparams['tsort'] = $tsort;
}
if ($tdir) {
    $urlparams['tdir'] = $tdir;
}

$url = new moodle_url('/local/eportfolio/index.php', $urlparams);
$usercontext = context_user::instance($USER->id);
$context = context_system::instance();

$setheading = 'Hallo ' . $USER->firstname . '! &#128075;';

// Set page layout.
$PAGE->set_url($url);
$PAGE->set_context($usercontext);
$PAGE->set_title(get_string('overview:header', 'local_eportfolio'));
$PAGE->set_heading($setheading);
$PAGE->set_pagelayout('base');
$PAGE->add_body_class('limitedwith');

// Print the header.
echo $OUTPUT->header();

// To use this plugin the user needs the moodle/h5p:deploy capability.
// Also check, if the other settings were set, before using this plugin.
// Otherwise, the user can't create and share H5P content.

$configcheck = local_eportfolio_check_config($context);

if (!empty((array) $configcheck)) {
    $data = new stdClass();
    $data = $configcheck;
    echo $OUTPUT->render_from_template('local_eportfolio/missingconfiguration', $data);
} else {
    $renderer = new local_eportfolio\local\overview\overview($url, $section, $tsort, $tdir);
    $renderer->display();
}

echo $OUTPUT->footer();
