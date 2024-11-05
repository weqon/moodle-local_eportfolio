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
 * Capabilities for ePortfolio
 *
 * @package local_eportfolio
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();

if (isguestuser()) {
    redirect(new moodle_url($CFG->wwwroot),
            get_string('error:noguestaccess', 'local_eportfolio'),
            null, \core\output\notification::NOTIFY_ERROR);
}

if (!has_capability('local/eportfolio:view_eport', context_system::instance()) || !is_siteadmin()) {
    redirect(new moodle_url($CFG->wwwroot),
            get_string('error:missingcapability', 'local_eportfolio'),
            null, \core\output\notification::NOTIFY_ERROR);
}

$id = required_param('id', PARAM_INT);
$contenturl = optional_param('contenturl', 0, PARAM_LOCALURL);

$url = new moodle_url('/local/eportfolio/edit.php', ['id' => $id]);

$context = context_system::instance();

$eport = $DB->get_record('local_eportfolio', ['id' => $id], '*', MUST_EXIST);
$fileid = $eport->fileid;

// Set page layout.
$PAGE->set_url($url);
$PAGE->set_context(context_user::instance($USER->id));
$PAGE->set_title(get_string('edit:header', 'local_eportfolio'));
$PAGE->set_heading(get_string('edit:header', 'local_eportfolio'));
$PAGE->set_pagelayout('base');
$PAGE->add_body_class('limitedwith');

$redirecturl = new moodle_url('/local/eportfolio/index.php');

if ($eport->fileid) {
    $fs = get_file_storage();
    $file = $fs->get_file_by_id($eport->fileid);

    $contenturl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
            $file->get_filearea(), $file->get_itemid(), $file->get_filepath(),
            $file->get_filename(), false);
}

if ($contenturl) {

    $contentid = '';

    if (!empty($contenturl)) {
        list($originalfile, $h5p, $file) = \core_h5p\api::get_original_content_from_pluginfile_url($contenturl);
        if ($originalfile) {
            // Check if the user can edit the content behind the given URL.
            if (\core_h5p\api::can_edit_content($originalfile)) {
                if (!$h5p) {
                    // This H5P file hasn't been deployed yet, so it should be saved to create the entry into the H5P DB.
                    \core_h5p\local\library\autoloader::register();
                    $factory = new \core_h5p\factory();
                    $config = new \stdClass();
                    $onlyupdatelibs = !\core_h5p\helper::can_update_library($originalfile);
                    $contentid = \core_h5p\helper::save_h5p($factory, $originalfile, $config, $onlyupdatelibs, false);
                } else {
                    // The H5P content exists. Update the contentid value.
                    $contentid = $h5p->id;
                }
            }
            if ($file) {
                list($context, $course, $cm) = get_context_info_array($file->get_contextid());
                if ($course) {
                    $context = \context_course::instance($course->id);
                }
            } else {
                list($context, $course, $cm) = get_context_info_array($originalfile->get_contextid());
                if ($course) {
                    $context = \context_course::instance($course->id);
                }
            }
        }
    }

    $returnurl = new moodle_url('/local/eportfolio/edit.php', ['id' => $id]);

    if (empty($contentid)) {
        throw new \moodle_exception('error:emptycontentid', 'core_h5p', $redirecturl);
    }

    $customdata = [];

    $customdata['eportid'] = $id;
    $customdata['contentid'] = $contentid;
    $customdata['contenturl'] = $contenturl;
    $customdata['returnurl'] = $returnurl;
    $customdata['contextid'] = $context->id;

    $mform = new \local_eportfolio\forms\edit_form($returnurl, $customdata);
    $mform->set_data($eport);

    if ($formdata = $mform->is_cancelled()) {


        redirect($redirecturl, get_string('form:cancelled', 'local_eportfolio'),
                null, \core\output\notification::NOTIFY_WARNING);

    } else if ($formdata = $mform->get_data()) {

        $fileid = $mform->save_content($formdata);

        if ($fileid) {

            $fs = get_file_storage();
            $file = $fs->get_file_by_id($fileid);

            $h5pfile = $DB->get_record('h5p', ['pathnamehash' => $file->get_pathnamehash()]);

            $data = new stdClass();

            $data->id = $formdata->eportid;
            $data->title = $formdata->title;
            $data->description = $formdata->description;
            $data->fileid = $fileid;
            $data->h5pid = $h5pfile->id;
            $data->timemodified = time();
            $data->usermodified = $USER->id;

            if ($DB->update_record('local_eportfolio', $data)) {

                // Check, if the file was shared.
                $getshared = $DB->get_records('local_eportfolio_share', ['eportid' => $formdata->eportid]);

                if ($getshared) {

                    foreach ($getshared as $sh) {

                        $newh5p = new stdClass();

                        if ($sh->shareoption === 'grade') {
                            $modcontext = context_module::instance($sh->cmid);

                            $newh5p->component = 'mod_eportfolio';
                            $newh5p->contextid = $modcontext->id; // Coursemodule context.

                        } else {
                            // Get course context for the new file.
                            $coursecontext = context_course::instance($sh->courseid);

                            $newh5p->component = 'local_eportfolio';
                            $newh5p->contextid = $coursecontext->id; // Coursemodule context.
                        }

                        // First create a copy of the original h5p file in the right context!
                        $fs = get_file_storage();
                        $file = $fs->get_file_by_hash($h5pfile->pathnamehash);

                        $itemid = file_get_unused_draft_itemid();

                        $newh5p->userid = $USER->id;
                        $newh5p->itemid = $itemid;
                        $newh5p->component = 'mod_eportfolio';

                        $filecontentcopy = $fs->create_file_from_storedfile($newh5p, $file);

                        unset($h5pfile->id);
                        unset($h5pfile->contenthash);
                        unset($h5pfile->pathnamehash);

                        // Create a copy for h5p to get a unique item id for media content.
                        $newh5pfile = $h5pfile;

                        $newh5pfile->contenthash = $filecontentcopy->get_contenthash();
                        $newh5pfile->pathnamehash = $filecontentcopy->get_pathnamehash();

                        $newh5pfileid = $DB->insert_record('h5p', $newh5pfile);

                        $h5pcontentfiles = $DB->get_records('files',
                                ['itemid' => $h5pfile->id, 'component' => 'core_h5p', 'filearea' => 'content']);

                        // Now create a copy of the images!
                        foreach ($h5pcontentfiles as $h5pcontent) {
                            if ($h5pcontent->filename != '.') {

                                // Get the file we want to create a copy of.
                                $fs = get_file_storage();
                                $contentfile = $fs->get_file_by_id($h5pcontent->id);

                                $itemidcontent = $newh5pfileid;

                                $newcontentfile = new stdClass();
                                $newcontentfile->contextid = '1'; // Must be system context.
                                $newcontentfile->userid = $USER->id;
                                $newcontentfile->itemid = $itemidcontent;
                                $newcontentfile->timecreated = time();
                                $newcontentfile->timemodified = time();

                                $newcontentfile->component = 'core_h5p';
                                $newcontentfile->filearea = 'content';
                                $newcontentfile->filename = $h5pcontent->filename;
                                $newcontentfile->filepath = $h5pcontent->filepath;

                                $filecontentcopy = $fs->create_file_from_storedfile($newcontentfile, $contentfile);

                            }
                        }
                    }

                }

                // Trigger event for editing ePortfolio.
                // ToDo: Add event for editing.
                \local_eportfolio\event\eportfolio_created::create([
                        'other' => [
                                'description' => get_string('event:eportfolio:created', 'local_eportfolio',
                                        ['userid' => $USER->id, 'filename' => '', 'itemid' => $fileid]),
                        ],
                ])->trigger();

                redirect($redirecturl, get_string('edit:success', 'local_eportfolio'), null,
                        \core\output\notification::NOTIFY_SUCCESS);
            }

        } else {
            redirect($redirecturl, get_string('edit:error', 'local_eportfolio'), null,
                    \core\output\notification::NOTIFY_ERROR);
        }

    } else {

        echo $OUTPUT->header();
        $mform->display();
        echo $OUTPUT->footer();
    }
}

