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
 * Sharing page for eportfolio
 *
 * @package local_eportfolio
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('locallib.php');
require_once('classes/forms/sharing_form_1.php');
require_once('classes/forms/sharing_form_2.php');
require_once('classes/forms/sharing_form_3.php');
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->dirroot . '/mod/h5pactivity/lib.php');

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

$cache = cache::make_from_params(cache_store::MODE_SESSION, 'local_eportfolio', 'sharing');

// Reset session in case form was reopened, but already used.
$referer = $_SERVER['HTTP_REFERER'];
// Codechecker error: The function str_contains() is not present in PHP version 7.4 or earlier.
if (!str_contains($referer, 'share.php')) {
    $cache->purge();
}

// Let's save the data to the current session. Maybe there is a better way.
$cache->set('id', $id);

if ($laststep = $cache->get('step')) {
    $step = $laststep;
} else {
    $step = '0';
}

// Build the URL.
$params = [
        'id' => $id,
        'step' => $step,
];

$url = new moodle_url('/local/eportfolio/share.php', $params);

$context = context_user::instance($USER->id);

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('sharing:header', 'local_eportfolio'));
$PAGE->set_heading(get_string('sharing:header', 'local_eportfolio'));
$PAGE->set_pagelayout('base');
$PAGE->add_body_class('limitedwith');

$mform1 = new sharing_form_1($url);

if ($step == '0') {
    if ($formdata1 = $mform1->is_cancelled()) {

        $cache->purge();

        $redirecturl = new moodle_url('/local/eportfolio/index.php');
        redirect($redirecturl, get_string('uploadform:cancelled', 'local_eportfolio'), null,
                \core\output\notification::NOTIFY_WARNING);

    } else if ($formdata1 = $mform1->get_data()) {

        $cache->set('sharedcourse', $formdata1->sharedcourse);
        $cache->set('step', '1');

        redirect(new moodle_url('/local/eportfolio/share.php', ['id' => $id]));

    } else {
        $renderform = $mform1->render();
    }
}

if ($step == '1') {

    $sharedcourse = $cache->get('sharedcourse', '1');
    $id = $cache->get('id', '1');

    $customdata['sharedcourse'] = $sharedcourse;

    $mform2 = new sharing_form_2($url, $customdata);

    if ($formdata2 = $mform2->is_cancelled()) {

        $cache->purge();

        $redirecturl = new moodle_url('/local/eportfolio/index.php');
        redirect($redirecturl, get_string('uploadform:cancelled', 'local_eportfolio'), null,
                \core\output\notification::NOTIFY_WARNING);

    } else if ($formdata2 = $mform2->get_data()) {

        $cache->set('shareoption', $formdata2->shareoption);

        if (!empty($formdata2->shareendenabled)) {
            $cache->set('shareend', $formdata2->shareend);
            $cache->set('shareendenabled', $formdata2->shareendenabled);
        }

        if (!empty($formdata2->cmid)) {
            $cache->set('cmid', $formdata2->cmid);
        }

        $cache->set('step', '2');

        redirect(new moodle_url('/local/eportfolio/share.php', ['id' => $id]));

    } else {
        $renderform = $mform2->render();
    }
}

if ($step == '2') {

    $sharedcourse = $cache->get('sharedcourse');
    $shareoption = $cache->get('shareoption');
    $shareend = $cache->get('shareend');
    $shareendenabled = $cache->get('shareendenabled');
    $cmid = $cache->get('cmid');
    $id = $cache->get('id');

    $customdata['sharedcourse'] = $sharedcourse;
    $customdata['shareoption'] = $shareoption;

    $mform3 = new sharing_form_3($url, $customdata);

    if ($formdata3 = $mform3->is_cancelled()) {

        $cache->purge();

        $redirecturl = new moodle_url('/local/eportfolio/index.php');
        redirect($redirecturl, get_string('uploadform:cancelled', 'local_eportfolio'), null,
                \core\output\notification::NOTIFY_WARNING);

    } else if ($formdata3 = $mform3->get_data()) {

        $eport = $DB->get_record('local_eportfolio', ['id' => $id]);

        $data = new stdClass();

        $data->eportid = $eport->id;
        $data->title = $eport->title;
        $data->courseid = $cache->get('sharedcourse');
        $data->cmid = '0';
        $data->fileid = $eport->fileid;
        $data->shareoption = $shareoption;
        $data->enddate = (isset($shareendenabled)) ? $shareend : '';
        $data->timecreated = time();
        $data->timemodified = time();
        $data->usermodified = $USER->id;
        $data->h5pid = '0'; // Default value.

        // Only relevant when ePortfolios is shared for grading.
        if ($shareoption == 'grade') {
            $data->cmid = $cmid;
        }

        // Set empty values to avoid undefined property warning.
        $data->roles = '';
        $data->enrolled = '';
        $data->coursegroups = '';

        // Let's collect the target groups.
        $data->fullcourse = ($formdata3->fullcourse == '1') ? $formdata3->fullcourse : '0';

        // We only need the following steps, if ePortfolio isn't shared for the complete course.
        if ($formdata3->fullcourse === '2') {

            if (isset($formdata3->roles)) {
                $roles = [];
                foreach ($formdata3->roles as $key => $value) {
                    if ($value) {
                        $roles[] = $key;
                    }
                }
                $data->roles = implode(', ', $roles);
            }

            if (isset($formdata3->enrolled)) {
                $enrolled = [];
                foreach ($formdata3->enrolled as $key => $value) {
                    if ($value) {
                        $enrolled[] = $key;
                    }
                }
                $data->enrolled = implode(', ', $enrolled);
            }

            if (isset($formdata3->groups)) {
                $groups = [];
                foreach ($formdata3->groups as $key => $value) {
                    if ($value) {
                        $groups[] = $key;
                    }
                }
                $data->coursegroups = implode(', ', $groups);
            }
        }

        $cache->purge();

        // Check, if the user already shared this file in the specific course with the same option.
        if (!$DB->get_record('local_eportfolio_share', ['usermodified' => $data->usermodified, 'courseid' => $data->courseid,
                'shareoption' => $data->shareoption, 'fileid' => $data->fileid])) {

            // Get the file we want to create a copy of and for sending a message to the users this ePortfolio was shared with.
            $fs = get_file_storage();
            $file = $fs->get_file_by_id($data->fileid);

            $pathnamehash = $file->get_pathnamehash();

            $h5pfile = $DB->get_record('h5p', ['pathnamehash' => $pathnamehash]);

            if (!empty($h5pfile)) {
                $data->h5pid = $h5pfile->id;
            }
            $filename = $file->get_filename();

            // If the file is shared with a course let's create a copy of it in course context.
            if (!empty($data->courseid)) {

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

            if ($insertid = $DB->insert_record('local_eportfolio_share', $data)) {

                if (!empty($data->title)) {
                    $filename = $data->title;
                }

                // Let's send a message to the users shared with.
                $participants = local_eportfolio_get_shared_participants($data->courseid, $data->fullcourse,
                        $data->enrolled, $data->roles, $data->coursegroups, true);

                foreach ($participants as $key => $value) {
                    // Generate and queue adhoc task.

                    // Prepare task data.
                    $task = new \local_eportfolio\task\send_messages();

                    $taskdata = new stdClass();

                    $taskdata->courseid = $data->courseid;
                    $taskdata->cmid = $data->cmid;
                    $taskdata->userfrom = $data->usermodified;
                    $taskdata->userto = $key;
                    $taskdata->shareoption = $data->shareoption;
                    $taskdata->filename = $filename;
                    $taskdata->fileid = $data->fileidcontext;
                    $taskdata->eportid = $data->eportid;
                    $taskdata->eportshareid = $insertid;

                    $task->set_custom_data($taskdata);

                    // Queue the task.
                    \core\task\manager::queue_adhoc_task($task);

                }

                // Trigger event for sharing ePortfolio.
                \local_eportfolio\event\eportfolio_shared::create([
                        'objectid' => $eport->fileid,
                        'other' => [
                                'description' => get_string('event:eportfolio:shared:' . $data->shareoption, 'local_eportfolio',
                                        ['userid' => $USER->id, 'filename' => $filename, 'fileid' => $eport->fileid]),
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

        $renderform = $mform3->render();

    }
}

echo $OUTPUT->header();

$data = new stdClass();

$data->renderform = $renderform;

// Check, if this ePortfolio was already shared in any way and inform user.
$eport = $DB->get_record('local_eportfolio', ['id' => $id]);
$alreadyshared = local_eportfolio_check_already_shared($eport->id, $eport->fileid);

if (!empty($alreadyshared)) {
    $data->alreadyshared = true;
    $data->shared = $alreadyshared;
}

echo $OUTPUT->render_from_template('local_eportfolio/share', $data);

echo $OUTPUT->footer();
