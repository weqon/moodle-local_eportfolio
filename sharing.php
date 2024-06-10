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

// ToDo: Modal for Sharing.
// https://docs.moodle.org/dev/Modal_and_AJAX_forms.

require_once('../../config.php');
require_once('locallib.php');
require_once('classes/forms/sharing_form.php');
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->dirroot . '/mod/h5pactivity/lib.php');

// First check, if user is logged in before accessing this page.
require_login();

if (isguestuser()) {
    redirect(new moodle_url($CFG->wwwroot),
            get_string('error:noguestaccess', 'local_eportfolio'),
            null, \core\output\notification::NOTIFY_ERROR);
}

$id = required_param('id', PARAM_INT);

// Maybe add courseid as optional param?
// Let's save the data to the current session. Maybe there is a better way.

$url = new moodle_url('/local/eportfolio/sharing.php', array('id' => $id));

$context = context_user::instance($USER->id);

// Reset session in case form was reopened, but already used.
$referer = $_SERVER['HTTP_REFERER'];
if (!str_contains($referer, 'sharing.php')) {
    reset_session_data();
}

// Check, if current step is saved to session.
if (load_from_session('step', null)) {
    $step = load_from_session('step', null);
} else {
    $step = '0';
}

save_to_session('id', $id);

$mform1 = new sharing_form_1($url);

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('sharing:header', 'local_eportfolio'));
$PAGE->set_heading(get_string('sharing:header', 'local_eportfolio'));
$PAGE->set_pagelayout('base');
$PAGE->add_body_class('limitedwith');
$PAGE->set_pagetype('user-files');

if ($step == '0') {
    if ($formdata1 = $mform1->is_cancelled()) {

        reset_session_data();
        redirect(new moodle_url('/local/eportfolio/index.php'));

    } else if ($formdata1 = $mform1->get_data()) {

        save_to_session('sharedcourse', $formdata1->sharedcourse);
        save_to_session('step', '1');

        redirect(new moodle_url('/local/eportfolio/sharing.php', ['id' => $id]));

    } else {

        echo $OUTPUT->header();

        $mform1->display();

        echo $OUTPUT->footer();

    }
}

if ($step == '1') {

    $sharedcourse = load_from_session('sharedcourse', 0);
    $id = load_from_session('id', 0);

    $customdata = array(
            'sharedcourse' => $sharedcourse,
    );

    $mform2 = new sharing_form_2($url, $customdata);

    if ($formdata2 = $mform2->is_cancelled()) {

        reset_session_data();
        redirect(new moodle_url('/local/eportfolio/index.php'));

    } else if ($formdata2 = $mform2->get_data()) {

        save_to_session('shareoption', $formdata2->shareoption);

        if($formdata2->shareend) {
            save_to_session('shareend', $formdata2->shareend);
        }

        if($formdata2->cmid) {
            save_to_session('cmid', $formdata2->cmid);
        }

        save_to_session('step', '2');

        redirect(new moodle_url('/local/eportfolio/sharing.php', ['id' => $id]));

    } else {

        echo $OUTPUT->header();

        $mform2->display();

        echo $OUTPUT->footer();

    }
}

if ($step == '2') {

    $sharedcourse = load_from_session('sharedcourse', 0);
    $shareoption = load_from_session('shareoption', 0);
    $shareend = load_from_session('shareend', 0);
    $cmid = load_from_session('cmid', 0);
    $id = load_from_session('id', 0);

    $customdata = array(
            'sharedcourse' => $sharedcourse,
            'shareoption' => $shareoption,
    );

    $mform3 = new sharing_form_3($url, $customdata);

    if ($formdata3 = $mform3->is_cancelled()) {

        reset_session_data();
        redirect(new moodle_url('/local/eportfolio/index.php'));

    } else if ($formdata3 = $mform3->get_data()) {

        $data = new stdClass();

        $data->userid = $USER->id;
        $data->courseid = load_from_session('sharedcourse', 0);
        $data->cmid = '0';
        $data->fileitemid = $id;
        $data->shareoption = $shareoption;
        $data->enddate = (isset($shareend)) ? $shareend : '';
        $data->timecreated = time();
        $data->h5pid = '0'; // Default value.

        // Only relevant when ePortfolios is shared for grading.
        if ($shareoption == 'grade') {
            $data->cmid = $cmid;
        }

        // Let's collect the target groups.
        $data->fullcourse = ($formdata3->fullcourse == '1') ? $formdata3->fullcourse : '';

        $roles = array();

        foreach ($formdata3->roles as $key => $value) {
            if ($value) {
                $roles[] = $key;
            }
        }

        $data->roles = implode(', ', $roles);

        $enrolled = array();
        foreach ($formdata3->enrolled as $key => $value) {
            if ($value) {
                $enrolled[] = $key;
            }
        }

        $data->enrolled = implode(', ', $enrolled);

        $groups = array();
        foreach ($formdata3->groups as $key => $value) {
            if ($value) {
                $groups[] = $key;
            }
        }

        $data->coursegroups = implode(', ', $groups);

        reset_session_data();

        // Check, if the user already shared this file in the specific course with the same option.
        if (!$DB->get_record('local_eportfolio_share', ['userid' => $data->userid, 'courseid' => $data->courseid,
                'shareoption' => $data->shareoption, 'fileitemid' => $data->fileitemid])) {

            // Get the file we want to create a copy of and for sending a message to the users this ePortfolio was shared with.
            $fs = get_file_storage();
            $file = $fs->get_file_by_id($id);

            $pathnamehash = $file->get_pathnamehash();

            $h5pfile = $DB->get_record('h5p', ['pathnamehash' => $pathnamehash]);

            if ($h5pfile) {
                $data->h5pid = $h5pfile->id;
            }
            $filename = $file->get_filename();

            // If the file is shared with a course let's create a copy of it in course context.
            if ($data->courseid) {

                $newfile = new stdClass();

                $newfile->itemid = file_get_unused_draft_itemid();

                // If ePortfolio is shared for grading, create a copy for the mod_eportfolio component.
                // Files for grading are tied to the course module and can't be deleted by the user.
                if ($data->shareoption === 'grade') {
                    // Get the module context.
                    $modcontext = context_module::instance($data->cmid);

                    $newfile->component = 'mod_eportfolio';
                    $newfile->contextid = $modcontext->id; // Coursemodule context.

                } else {
                    // Get course context for the new file.
                    $coursecontext = context_course::instance($data->courseid);

                    $newfile->contextid = $coursecontext->id; // Coursemodule context.
                }

                $filecopy = $fs->create_file_from_storedfile($newfile, $file);

                $data->fileidcontext = $filecopy->get_id();

            }

            if ($DB->insert_record('local_eportfolio_share', $data)) {

                // Let's send a message to the users shared with.
                $participants = get_shared_participants($data->courseid, $data->fullcourse,
                        $data->enrolled, $data->roles, $data->coursegroups, true);

                foreach ($participants as $key => $value) {
                    $message = eportfolio_send_message($data->courseid, $data->userid, $key,
                            $data->shareoption, $filename, $data->fileidcontext);
                }

                // Trigger event for sharing ePortfolio.
                \local_eportfolio\event\eportfolio_shared::create([
                        'other' => [
                                'description' => get_string('event:eportfolio:shared:' . $data->shareoption, 'local_eportfolio',
                                        array('userid' => $USER->id, 'filename' => $file->get_filename(), 'itemid' => $id)),
                        ],
                ])->trigger();

                redirect(new moodle_url('/local/eportfolio/index.php'),
                        get_string('sharing:share:successful', 'local_eportfolio'),
                        null, \core\output\notification::NOTIFY_SUCCESS);

            } else {

                redirect(new moodle_url('/local/eportfolio/index.php'),
                        get_string('sharing:share:inserterror', 'local_eportfolio'),
                        null, \core\output\notification::NOTIFY_ERROR);
            }
        } else {

            redirect(new moodle_url('/local/eportfolio/index.php'),
                    get_string('sharing:share:alreadyexists', 'local_eportfolio'),
                    null, \core\output\notification::NOTIFY_ERROR);
        }

    } else {

        echo $OUTPUT->header();

        $mform3->display();

        echo $OUTPUT->footer();
    }
}
