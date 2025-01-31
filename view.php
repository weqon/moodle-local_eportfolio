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

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

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

$id = required_param('id', PARAM_INT);
$courseid = optional_param('course', 0, PARAM_INT);
$cmid = optional_param('cmid', 0, PARAM_INT);
$tocourse = optional_param('tocourse', 0, PARAM_INT);
$section = optional_param('section', '', PARAM_ALPHA);

$url = new moodle_url('/local/eportfolio/view.php', ['id' => $id, 'section' => $section]);

// Default component.
$component = 'local_eportfolio';

// Get the right context.
if ($cmid) {
    $context = context_module::instance($cmid);
    $component = 'mod_eportfolio';
} else if ($courseid) {
    $context = context_course::instance($courseid);
} else {
    $context = context_system::instance();
}

// Set page layout.
$PAGE->set_url($url);
$PAGE->set_context(context_user::instance($USER->id));
$PAGE->set_title(get_string('view:header', 'local_eportfolio'));
$PAGE->set_heading(get_string('view:header', 'local_eportfolio'));
$PAGE->set_pagelayout('base');
$PAGE->add_body_class('limitedwith');

// Get the ePortfolio entry and file storage.
$fs = get_file_storage();

// Set objectid for triggering the event.
$objectid = 0;

if ($section === 'my') {
    $eport = $DB->get_record('local_eportfolio', ['id' => $id]);

    $editid = $eport->id;
    $objectid = $eport->fileid;

    if (empty($empty->h5pid)) {
        // Get the file for user context.
        $file = $fs->get_file_by_id($eport->fileid);

        // We need a better solution for this.
        // Move this to edit.php and also update local_eportfolio_shared, in case file was uploaded as template.
        $getpathnamehash = $file->get_pathnamehash();
        $h5pbypathnamehash = $DB->get_record('h5p', ['pathnamehash' => $getpathnamehash]);

        if (!empty($h5pbypathnamehash)) {
            $updatedata = $eport;
            $updatedata->h5pid = $h5pbypathnamehash->id;

            $DB->update_record('local_eportfolio', $updatedata);
        }
    }

} else {
    // File view was accessed from course or course module.
    $eport = $DB->get_record('local_eportfolio_share', ['id' => $id]);
    $editid = $eport->eportid;
    $objectid = $eport->fileidcontext;

    // Get the file for shared context.
    $file = $fs->get_file_by_id($eport->fileidcontext);
}

// In case additional file types will be allowed we have to replace this.
// Convert display options to a valid object.
$factory = new \core_h5p\factory();
$core = $factory->get_core();
$config = core_h5p\helper::decode_display_options($core, $context->id);

$fileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
        $file->get_filearea(), $file->get_itemid(), $file->get_filepath(),
        $file->get_filename(), false);

// Get the times for created and modified based on h5p file.
$pathnamehash = $file->get_pathnamehash();
$h5pfile = $DB->get_record('h5p', ['pathnamehash' => $pathnamehash]);

// Let's build the backurl.
if ($tocourse) {
    $backurl = new moodle_url('/course/view.php', ['id' => $courseid]);
    $backurlstring = get_string('view:eportfolio:button:backtocourse', 'local_eportfolio');
} else {
    $backurl = new moodle_url('/local/eportfolio/index.php', ['section' => $section]);
    $backurlstring = get_string('view:eportfolio:button:backtoeportfolio', 'local_eportfolio');
}

$userfullname = '';
$user = $DB->get_record('user', ['id' => $eport->usermodified]);
$userfullname = fullname($user);

// Let's check if user "owns" the ePortfolio and can edit it and also, if user isn't in course context.
if ($USER->id == $file->get_userid() && !$tocourse && $file->get_component() != 'mod_eportfolio') {
    $editurl = new moodle_url('/local/eportfolio/edit.php', ['id' => $editid, 'section' => $section]);
} else {
    $editurl = '';
}

// Prepare data for template files.
$eportfolio = new stdClass();

$eportfolio->title = (!empty($eport->title)) ? $eport->title : '';
$eportfolio->description = (!empty($eport->description)) ? $eport->description : '';
$eportfolio->backurl = $backurl;
$eportfolio->backurlstring = $backurlstring;
$eportfolio->editurl = (!empty($editurl)) ? $editurl->out(false) : '';
$eportfolio->userfullname = $userfullname;
$eportfolio->timecreated = date('d.m.Y', $eport->timecreated);
$eportfolio->timemodified = date('d.m.Y', $eport->timemodified);
$eportfolio->h5pplayer = \core_h5p\player::display($fileurl, $config, false, 'local_eportfolio', false);;

// Trigger event for viewing ePortfolio.
$filename = '';
if (!empty($eport->title)) {
    $filename = $eport->title;
} else {
    $filename = $file->get_filename();
}

\local_eportfolio\event\eportfolio_viewed::create([
        'objectid' => $objectid,
        'other' => [
                'description' => get_string('event:eportfolio:viewed', 'local_eportfolio',
                        ['userid' => $USER->id, 'filename' => $filename, 'fileid' => $objectid]),
        ],
])->trigger();

echo $OUTPUT->header();

echo $OUTPUT->render_from_template('local_eportfolio/view_h5p_player', $eportfolio);

echo $OUTPUT->footer();
