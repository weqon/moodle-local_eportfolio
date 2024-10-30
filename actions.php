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
 * Used for all actions related to ePortfolio file.
 *
 * @package     local_eportfolio
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once('locallib.php');

$id = required_param('id', PARAM_INT);  // Entry ID.
$action = required_param('action', PARAM_ALPHA);
$section = optional_param('section', 'my', PARAM_ALPHA);
$courseid = optional_param('courseid', 0, PARAM_INT);

require_login();
require_sesskey();

$redirecturl = new moodle_url('/local/eportfolio/index.php', ['section' => $section]);

if ($action === 'undo') {

    // First, get the record.
    $eport = $DB->get_record('local_eportfolio_share', ['id' => $id]);

    // First delete the shared file from course context.
    $coursecontext = context_course::instance($eport->courseid);

    $fs = get_file_storage();
    $file = $fs->get_file_by_id($eport->fileidcontext);

    // Delete the file from H5P-Table.
    $pathnamehash = $file->get_pathnamehash();

    $h5pfile = $DB->get_record('h5p', ['pathnamehash' => $pathnamehash]);

    if ($h5pfile) {
        $DB->delete_records('h5p', ['id' => $h5pfile->id]);
    }

    // Now delete the file.
    $file->delete();

    if ($DB->delete_records('local_eportfolio_share', ['id' => $eport->id])) {

        // Trigger event for withdrawing sharing of ePortfolio.
        \local_eportfolio\event\eportfolio_shared::create([
                'other' => [
                        'description' => get_string('event:eportfolio:undo', 'local_eportfolio',
                                ['userid' => $USER->id, 'filename' => $file->get_filename(), 'itemid' => $eport->fileid]),
                ],
        ])->trigger();

        redirect($redirecturl, get_string('undo:success', 'local_eportfolio'),
                null, \core\output\notification::NOTIFY_SUCCESS);

    } else {

        redirect($redirecturl, get_string('undo:error', 'local_eportfolio'),
                null, \core\output\notification::NOTIFY_ERROR);
    }

}

if ($action === 'delete') {

    // First, get the record.
    $eport = $DB->get_record('local_eportfolio', ['id' => $id]);

    // Second, check, if the file was shared.
    // Note, we can only delete files, which were shared for view or as template.
    // Shared files for grading can only be deleted in the activity by the "gradingteacher".
    $eportshared = $DB->get_records('local_eportfolio_share', ['eportid' => $eport->id]);

    if ($eportshared) {
        foreach ($eportshared as $es) {

            // Note, we can only delete files, which were shared for view or as template.
            // Shared files for grading can only be deleted in the activity by the "gradingteacher".
            if ($es->shareoption === 'grade') {
                $updatedata = $eportshared;
                $updatedata->fileid = '0';
                $updatedata->eportid = '0';

                $DB->update_record('local_eportfolio_share', $updatedata);

            } else {
                // Delete the shared file from course context.
                $coursecontext = context_course::instance($es->courseid);

                $fs = get_file_storage();
                $file = $fs->get_file_by_id($es->fileidcontext);
                $file->delete();

                // Delete the entry in eportfolio_share table.
                $DB->delete_records('local_eportfolio_share', ['id' => $es->id]);
            }
        }
    }

    // Now delete the main file.
    $fs = get_file_storage();
    $file = $fs->get_file_by_id($eport->fileid);

    // We use the pathnamehash to get the H5P file.
    $pathnamehash = $file->get_pathnamehash();

    $h5pfile = $DB->get_record('h5p', ['pathnamehash' => $pathnamehash]);

    // If H5P, delete it from the H5P table as well.
    // Note: H5P will create an entry when the file was viewed for the first time.
    if ($h5pfile) {
        $DB->delete_records('h5p', ['id' => $h5pfile->id]);
        // Also delete from files where context = 1, itemid = h5p id component core_h5p, filearea content.
        $fs->delete_area_files('1', 'core_h5p', 'content', $h5pfile->id);
    }

    // Finally delete the selected file.
    $file->delete();

    if ($DB->delete_records('local_eportfolio', ['id' => $eport->id])) {

        // Trigger event for withdrawing sharing of ePortfolio.
        \local_eportfolio\event\eportfolio_deleted::create([
                'other' => [
                        'description' => get_string('event:eportfolio:deleted', 'local_eportfolio',
                                ['userid' => $USER->id, 'filename' => $file->get_filename(),
                                        'itemid' => $file->get_id()]),
                ],
        ])->trigger();

        redirect($redirecturl, get_string('delete:success', 'local_eportfolio'),
                null, \core\output\notification::NOTIFY_SUCCESS);

    } else {
        redirect($redirecturl, get_string('delete:error', 'local_eportfolio'),
                null, \core\output\notification::NOTIFY_ERROR);

    }
}

if ($action == 'reuse') {

    if ($courseid) {

        // First, get the record.
        $eport = $DB->get_record('local_eportfolio_share', ['id' => $id, 'shareoption' => 'template']);

        // First we need the course context.
        $coursecontext = context_course::instance($courseid);
        $context = context_system::instance();

        // Get the file we want to create a copy of.
        $fs = get_file_storage();
        $file = $fs->get_file_by_id($eport->fileid);

        // Create a new itemid to avoid conflicts.
        $itemid = file_get_unused_draft_itemid();

        $newfile = new stdClass();
        $newfile->contextid = $context->id; // System context.
        $newfile->userid = $USER->id;
        $newfile->itemid = $itemid;

        $filecopy = $fs->create_file_from_storedfile($newfile, $file);

        // After the new file was created let's build the fileurl for the H5P Editor.
        $fileurl = moodle_url::make_pluginfile_url($filecopy->get_contextid(), $filecopy->get_component(),
                $filecopy->get_filearea(), $filecopy->get_itemid(), $filecopy->get_filepath(),
                $filecopy->get_filename(), false);

        // Also we have to add a new entry in the h5p table.
        // First get h5p file by "old" pathnamehash.
        $pathnamehash = $file->get_pathnamehash();

        $newh5pfile = $DB->get_record('h5p', ['pathnamehash' => $pathnamehash]);

        // Override contenthash & pathnamehash to new file.
        $newh5pfile->pathnamehash = $filecopy->get_pathnamehash();
        $newh5pfile->contenthash = $filecopy->get_contenthash();

        // We need this for the next step.
        $oldh5pfileid = $newh5pfile->id;

        unset($newh5pfile->id);

        $newh5pfileid = $DB->insert_record('h5p', $newh5pfile);

        // We need to create a copy of the H5P content as well in case the file contains additional content like images.
        $h5pcontentfiles =
                $DB->get_records('files', ['itemid' => $oldh5pfileid, 'component' => 'core_h5p', 'filearea' => 'content']);

        foreach ($h5pcontentfiles as $h5pcontent) {
            if ($h5pcontent->filename != '.') {
                // Get the file we want to create a copy of.
                $fs = get_file_storage();
                $file = $fs->get_file_by_id($h5pcontent->id);

                $contentitemid = $newh5pfileid;

                $newcontentfile = new stdClass();
                $newcontentfile->contextid = '1';
                $newcontentfile->userid = $USER->id;
                $newcontentfile->itemid = $contentitemid;
                $newcontentfile->component = 'core_h5p';
                $newcontentfile->filearea = 'content';

                $filecontentcopy = $fs->create_file_from_storedfile($newcontentfile, $file);

            }
        }

        if ($filecopy && $newh5pfileid) {

            $insertfile = new stdClass();

            $insertfile->title = $eport->title;
            $insertfile->fileid = $filecopy->get_id();
            $insertfile->h5pid = $newh5pfileid;
            $insertfile->usermodified = $USER->id;
            $insertfile->timecreated = time();
            $insertfile->timemodified = time();

            if ($neweport = $DB->insert_record('local_eportfolio', $insertfile)) {

                // H5P core edit will redirect the user to this URL after editing the content.
                $returnurl = new moodle_url('/local/eportfolio/index.php');
                $editurl = new moodle_url('/local/eportfolio/edit.php', ['id' => $neweport]);

                redirect($editurl, get_string('use:template:success', 'local_eportfolio'),
                        null, \core\output\notification::NOTIFY_SUCCESS);
            } else {
                redirect(new moodle_url('/local/eportfolio/index.php'),
                        get_string('use:template:error', 'local_eportfolio'),
                        null, \core\output\notification::NOTIFY_ERROR);
            }
        } else {
            redirect(new moodle_url('/local/eportfolio/index.php'),
                    get_string('use:template:error', 'local_eportfolio'),
                    null, \core\output\notification::NOTIFY_ERROR);
        }
    } else {
        // No course ID provided.
        redirect(new moodle_url('/local/eportfolio/index.php'),
                get_string('use:template:error', 'local_eportfolio'),
                null, \core\output\notification::NOTIFY_ERROR);
    }

}

