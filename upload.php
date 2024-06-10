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
 * Upload page for eportfolio
 *
 * @package local_eportfolio
 * @category file upload
 * @copyright 2023 weQon UG {@link https://weqon.net}
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('locallib.php');
require_once('classes/forms/upload_form.php');

// First check, if user is logged in before accessing this page.
require_login();

if (isguestuser()) {
    redirect(new moodle_url($CFG->wwwroot),
            get_string('error:noguestaccess', 'local_eportfolio'),
            null, \core\output\notification::NOTIFY_ERROR);
}

$url = new moodle_url('/local/eportfolio/upload.php');
$context = context_user::instance($USER->id);

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title("ePortfolio - Übersicht");
$PAGE->set_heading("ePortfolio - Übersicht");
$PAGE->set_pagelayout('base');
$PAGE->add_body_class('limitedwith');
$PAGE->set_pagetype('user-files');

// ToDo: Make this configurable.
$filemanageropts = array(
        'subdirs' => 0,
        'maxbytes' => 26214400,
        'areamaxbytes' => 26214400,
        'maxfiles' => 1,
        'context' => $context,
        'accepted_types' => array('.h5p'),
);

$customdata = array(
        'filemanageropts' => $filemanageropts
);

$itemid = file_get_unused_draft_itemid();

$draftfile = file_get_submitted_draft_itemid('eportfolio');
file_prepare_draft_area($draftfile, $context->id, 'local_eportfolio', 'eportfolio',
        $itemid, $filemanageropts);

$mform = new upload_form($url, $customdata);

if ($formdata = $mform->is_cancelled()) {

    $redirecturl = new moodle_url('/local/eportfolio/index.php');
    redirect($redirecturl, get_string('uploadform:cancelled', 'local_eportfolio'), null,
            \core\output\notification::NOTIFY_WARNING);

} else if ($formdata = $mform->get_data()) {

    $newfile = file_save_draft_area_files($draftfile, $context->id, 'local_eportfolio', 'eportfolio', $itemid, $filemanageropts);

    // After upload redirect the user to the edit form. Otherwise H5P will throw a capability error.
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'local_eportfolio', 'eportfolio', $itemid, 'id', false);
    $files = array_reverse($files);
    $file = reset($files);

    if ($formdata->uploadtemplate) {

        if (!$DB->get_record('local_eportfolio_share', ['userid' => $USER->id, 'courseid' => $formdata->sharedcourse,
                'shareoption' => 'template', 'fileitemid' => $file->get_id()])) {

            // Create a copy of the file in course context as well, so that other users can use it.
            $coursecontext = context_course::instance($formdata->sharedcourse);
            file_save_draft_area_files($draftfile, $coursecontext->id, 'local_eportfolio', 'eportfolio', $itemid, $filemanageropts);

            $filescopy = $fs->get_area_files($coursecontext->id, 'local_eportfolio', 'eportfolio', $itemid, 'id', false);
            $filescopy = array_reverse($filescopy);
            $filecopy = reset($filescopy);

            // Prepare data for entry in local_eportfolio_share table.
            $data = new stdClass();

            $data->userid = $USER->id;
            $data->courseid = $formdata->sharedcourse;
            $data->cmid = '';
            $data->fileitemid = $file->get_id();
            $data->fileidcontext = $filecopy->get_id();
            $data->shareoption = 'template';
            $data->fullcourse = '1';
            $data->roles = '';
            $data->enrolled = '';
            $data->coursegroups = '';
            $data->enddate = '';
            $data->timecreated = time();
            $data->h5pid = '0'; // Default value.

            // Add entry to local_eportfolio_share table and mark as template.
            $DB->insert_record('local_eportfolio_share', $data);

        }
    }

    $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
            $file->get_filearea(), $file->get_itemid(), $file->get_filepath(),
            $file->get_filename(), false);

    // H5P core edit will redirect the user to this URL after editing the content.
    $returnurl = $CFG->wwwroot . '/local/eportfolio/index.php';

    $editurl = $CFG->wwwroot . '/h5p/edit.php?url=' . $fileurl . '&returnurl=' . $returnurl;

    // Trigger event for creating ePortfolio.
    \local_eportfolio\event\eportfolio_created::create([
            'other' => [
                    'description' => get_string('event:eportfolio:created', 'local_eportfolio',
                            array('userid' => $USER->id, 'filename' => $file->get_filename(), 'itemid' => $file->get_id())),
            ],
    ])->trigger();

    redirect($editurl, get_string('uploadform:successful', 'local_eportfolio'), null, \core\output\notification::NOTIFY_SUCCESS);

} else {

    echo $OUTPUT->header();

    $mform->display();

    echo $OUTPUT->footer();
}
