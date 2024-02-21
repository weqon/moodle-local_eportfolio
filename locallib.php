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
 * Locallib eportfolio
 *
 * @package local_eportfolio
 * @copyright 2023 weQon UG {@link https://weqon.net}
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Get ePortfolio courses.
function get_eportfolio_courses($roleid = '') {
    global $DB, $USER;

    // Get the field id to identify the custm field data.
    $customfield = $DB->get_record('customfield_field', ['shortname' => 'eportfolio_course']);

    // Get the value for custom field id.
    $customfielddata = $DB->get_records('customfield_data', ['fieldid' => $customfield->id]);

    $courses = array();

    foreach ($customfielddata as $cd) {
        if ($cd->value) {

            $coursecontext = context_course::instance($cd->instanceid);

            // Check if current user is enrolled in the course.
            if (is_enrolled($coursecontext, $USER->id)) {

                if ($roleid) {
                    // Get only assigned role.
                    if (get_assigned_role_by_course($roleid, $coursecontext->id)) {
                        $courses[] = $cd->instanceid;
                    }

                } else {
                    // We can return all courses.
                    $courses[] = $cd->instanceid;

                }

            }

        }
    }

    return $courses;

}

// Get my ePortfolios.
function get_my_eportfolios($context, $tsort = '', $tdir = '') {
    global $DB, $USER;

    $eportfolios = array();

    // We use a counter for the array.
    $i = 0;

    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'local_eportfolio', 'eportfolio');

    if (!empty($files)) {
        foreach ($files as $file) {

            if ($file->get_filename() != '.') {

                $eporturl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                        $file->get_filearea(), $file->get_itemid(), $file->get_filepath(),
                        $file->get_filename(), false);

                $fileediturl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                        $file->get_filearea(), $file->get_itemid(), $file->get_filepath(),
                        $file->get_filename(), false);

                $eportfolios[$i]['fileurl'] = $eporturl;
                $eportfolios[$i]['fileviewurl'] = new moodle_url('/local/eportfolio/view.php', ['id' => $file->get_id()]);
                $eportfolios[$i]['fileediturl'] = new moodle_url('/h5p/edit.php', ['url' => $fileediturl]);
                $eportfolios[$i]['filename'] = $file->get_filename();
                $eportfolios[$i]['filenameh5p'] = get_h5p_title($file->get_pathnamehash());
                $eportfolios[$i]['fileitemid'] = $file->get_id();
                $eportfolios[$i]['filesize'] = display_size($file->get_filesize());

                // Get the times for created and modified based on h5p file.
                // In case additional file types will be allowed we have to replace this.
                $pathnamehash = $file->get_pathnamehash();
                $h5pfile = $DB->get_record('h5p', ['pathnamehash' => $pathnamehash]);

                $eportfolios[$i]['filetimecreated'] = (!empty($h5pfile->timecreated)) ? date('d.m.Y', $h5pfile->timecreated) : '';
                $eportfolios[$i]['filetimemodified'] =
                        (!empty($h5pfile->timemodified)) ? date('d.m.Y', $h5pfile->timemodified) : '';

                // In case a file was uploaded or shared as template, let's add a hint.
                $templatefile = $DB->get_record('local_eportfolio_share', ['fileitemid' => $file->get_id(),
                        'shareoption' => 'template', 'userid' => $USER->id]);

                $eportfolios[$i]['istemplate'] = false;

                if ($templatefile) {
                    $eportfolios[$i]['istemplate'] = true;

                    $eportfolios[$i]['undourl'] = new moodle_url('/local/eportfolio/index.php',
                            ['id' => $templatefile->id, 'action' => 'undo']);

                }

            }

            $i++;

        }
    }

    if ($tsort && $tdir) {

        $sortorder = get_sort_order($tdir);

        // Rearange the array values to return numeric indexes.
        $results = array_values($eportfolios);

        $keyvalue = array_column($eportfolios, $tsort);
        if ($keyvalue) {
            array_multisort($keyvalue, $sortorder, $results);
        }

    } else {
        // Rearange the array values to return numeric indexes.
        $results = array_values($eportfolios);
    }

    return $results;
}

// Get my shared ePortfolios.

function get_my_shared_eportfolios($context, $shareoption = 'share', $courseid = '', $tsort = '', $tdir = '') {
    global $USER, $DB;

    $sql = "SELECT * FROM {local_eportfolio_share} WHERE userid = ? AND shareoption = ?";

    if ($courseid) {
        $sql .= " AND courseid = ?";
    }

    $params = array(
            'userid' => $USER->id,
            'shareoption' => $shareoption,
            'courseid' => $courseid,
    );

    $sharedeportfolios = $DB->get_records_sql($sql, $params);

    $eportfolios = array();

    // Default component for files.
    $component = 'local_eportfolio';

    foreach ($sharedeportfolios as $sp) {

        // In case we are in the activity ePortfolio.
        if ($courseid && $context->instanceid == $sp->cmid && $shareoption === 'grade') {
            $component = 'mod_eportfolio';
        }

        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, $component, 'eportfolio');

        if (!empty($files)) {
            foreach ($files as $file) {

                if ($file->get_filename() != '.') {

                    $fileid = $sp->fileitemid;

                    if ($courseid) {
                        $fileid = $sp->fileidcontext;
                    }

                    if ($fileid == $file->get_id() && $sp->shareoption == $shareoption) {

                        // Since I am viewing my own.

                        if ($shareoption == 'grade') {
                            $viewurlid = $sp->fileidcontext;
                        } else {
                            $viewurlid = $file->get_id();
                        }

                        $eportfolios[$sp->id]['fileviewurl'] =
                                new moodle_url('/local/eportfolio/view.php', ['id' => $viewurlid]);
                        $eportfolios[$sp->id]['fileitemid'] = $file->get_id();
                        $eportfolios[$sp->id]['fileidcontext'] = $sp->fileidcontext;
                        $eportfolios[$sp->id]['filename'] = $file->get_filename();
                        $eportfolios[$sp->id]['filenameh5p'] = get_h5p_title($file->get_pathnamehash());
                        $eportfolios[$sp->id]['filesize'] = display_size($file->get_filesize());
                        $eportfolios[$sp->id]['filetimemodified'] = date('d.m.Y', $file->get_timemodified());
                        $eportfolios[$sp->id]['filetimecreated'] = date('d.m.Y', $file->get_timecreated());

                        $eportfolios[$sp->id]['sharestart'] = date('d.m.Y', $sp->timecreated);
                        $eportfolios[$sp->id]['shareend'] = (!empty($sp->enddate)) ? date('d.m.Y', $sp->enddate) : './.';

                        // Removed from mustache template. Maybe we don't need this here as well.
                        switch ($sp->shareoption) {
                            case 'share':
                                $eportfolios[$sp->id]['shareoption'] = get_string('overview:shareoption:share', 'local_eportfolio');
                                break;
                            case 'grade':
                                $eportfolios[$sp->id]['shareoption'] = get_string('overview:shareoption:grade', 'local_eportfolio');
                                break;
                        }

                        $eportfolios[$sp->id]['userid'] = $USER->id;

                        $course = $DB->get_record('course', ['id' => $sp->courseid]);

                        $courseurl = new moodle_url('/course/view.php', ['id' => $course->id]);
                        $eportfolios[$sp->id]['courseid'] = $course->id;
                        $eportfolios[$sp->id]['coursename'] = $course->fullname;
                        $eportfolios[$sp->id]['courseurl'] = $courseurl;

                        $eportfolios[$sp->id]['undourl'] = new moodle_url('/local/eportfolio/index.php',
                                ['id' => $sp->id, 'action' => 'undo']);

                        // Get participants who have access to my shared eportfolios.
                        $participants = get_shared_participants($course->id, $sp->fullcourse,
                                $sp->enrolled, $sp->roles, $sp->coursegroups);

                        $participants = implode(', ', $participants);

                        $eportfolios[$sp->id]['participants'] = $participants;

                    }

                }
            }
        }
    }

    if ($tsort && $tdir) {

        $sortorder = get_sort_order($tdir);

        // Rearange the array values to return numeric indexes.
        $results = array_values($eportfolios);

        $keyvalue = array_column($eportfolios, $tsort);
        if ($keyvalue) {
            array_multisort($keyvalue, $sortorder, $results);
        }

    } else {
        // Rearange the array values to return numeric indexes.
        $results = array_values($eportfolios);
    }

    return $results;

}

function get_shared_eportfolios($shareoption = 'share', $courseid = '', $tsort = '', $tdir = '') {
    global $USER, $DB;

    $sql = "SELECT * FROM {local_eportfolio_share} WHERE shareoption = ?";

    if ($courseid) {
        $sql .= " AND courseid = ?";
    }

    $params = array(
            'shareoption' => $shareoption,
            'courseid' => $courseid,
    );

    $eportfoliosshare = $DB->get_records_sql($sql, $params);

    $sharedeportfolios = array();

    foreach ($eportfoliosshare as $es) {

        $coursecontext = context_course::instance($es->courseid);

        // First we have to check, if current user is editingteacher in selected course to view shared ePortfolios for grading.
        if ($shareoption === 'grade') {
            if (!is_enrolled($coursecontext, $USER, 'mod/eportfolio:grade_eport')) {
                continue;
            }
        }

        if (is_enrolled($coursecontext, $USER) && $es->userid != $USER->id) {

            $sharedeportfolios[$es->id]['itemid'] = $es->fileitemid;
            $sharedeportfolios[$es->id]['fileidcontext'] = $es->fileidcontext;
            $sharedeportfolios[$es->id]['userid'] = $es->userid;
            $sharedeportfolios[$es->id]['courseid'] = $es->courseid;
            $sharedeportfolios[$es->id]['cmid'] = ($shareoption === 'grade') ? $es->cmid : '';
            $sharedeportfolios[$es->id]['fullcourse'] = ($shareoption === 'grade') ? '1' : $es->fullcourse;
            $sharedeportfolios[$es->id]['roles'] = $es->roles;
            $sharedeportfolios[$es->id]['enrolled'] = $es->enrolled;
            $sharedeportfolios[$es->id]['groups'] = $es->coursegroups;
            $sharedeportfolios[$es->id]['enddate'] = $es->enddate;
            $sharedeportfolios[$es->id]['timecreated'] = $es->timecreated;
        }
    }

    // Rearange the array values to return numeric indexes.
    $sharedeportfolios = array_values($sharedeportfolios);

    $eportfolios = array();

    foreach ($sharedeportfolios as $key => $value) {

        $enddate = true;

        if ($value['enddate'] != 0 && $value['enddate'] < time()) {
            $enddate = false;
        }

        // First check if end date for sharing is reached.
        if ($enddate) {

            $coursecontext = context_course::instance($value['courseid']);

            // First, check, if I am eligible to view this eportfolio.
            $eligible = false;

            if ($value['fullcourse'] == '1' && !$eligible) {
                $eligible = true;
            }

            if (!empty($value['roles']) && !$eligible) {

                $roles = explode(', ', $value['roles']);

                foreach ($roles as $ro) {
                    $isenrolled = $DB->get_record('role_assignments',
                            ['contextid' => $coursecontext->id, 'roleid' => $ro, 'userid' => $USER->id]);

                    if (!empty($isenrolled)) {
                        $eligible = true;
                    }

                }
            }

            if (!empty($value['enrolled']) && !$eligible) {

                $enrolledusers = explode(', ', $value['enrolled']);

                if (in_array($USER->id, $enrolledusers)) {
                    $eligible = true;
                }
            }

            if (!empty($value['coursegroups']) && !$eligible) {

                $groups = explode(', ', $value['coursegroups']);

                foreach ($groups as $gr) {
                    $coursegroups = groups_get_all_groups($value['courseid'], $USER->id);

                    if (in_array($gr, $coursegroups)) {
                        $eligible = true;
                    }
                }

            }

            // Get course module context or user context.
            $fs = get_file_storage();

            if ($shareoption === 'grade' && $value['cmid']) {
                $modcontext = context_module::instance($value['cmid']);
                $files = $fs->get_area_files($modcontext->id, 'mod_eportfolio', 'eportfolio');
            } else if ($shareoption === 'share') {
                $context = context_course::instance($value['courseid']);
                $files = $fs->get_area_files($context->id, 'local_eportfolio', 'eportfolio');
            } else if ($shareoption === 'template') {
                $context = context_course::instance($value['courseid']);
                $files = $fs->get_area_files($context->id, 'local_eportfolio', 'eportfolio');
            } else {
                // Just in case.
                continue;
            }

            if (!empty($files) && $eligible) {
                // We use a counter for the array.
                $i = 0;

                foreach ($files as $file) {

                    if ($file->get_filename() != '.') {

                        $fileid = $file->get_id();

                        if ($value['fileidcontext'] == $fileid) {

                            $course = $DB->get_record('course', ['id' => $value['courseid']]);

                            $fileviewurlparams = array(
                                    'id' => $fileid,
                                    'course' => $course->id,
                                    'userid' => $value['userid'],
                            );

                            if ($value['cmid']) {
                                $fileviewurlparams['cmid'] = $value['cmid'];
                            }

                            $eportfolios[$i]['fileviewurl'] = new moodle_url('/local/eportfolio/view.php',
                                    $fileviewurlparams);
                            $eportfolios[$i]['fileitemid'] = $file->get_id();
                            $eportfolios[$i]['fileidcontext'] = $value['fileidcontext'];
                            $eportfolios[$i]['filename'] = $file->get_filename();
                            $eportfolios[$i]['filenameh5p'] = get_h5p_title($file->get_pathnamehash());
                            $eportfolios[$i]['filesize'] = display_size($file->get_filesize());
                            $eportfolios[$i]['filetimemodified'] = date('d.m.Y', $file->get_timemodified());
                            $eportfolios[$i]['filetimecreated'] = date('d.m.Y', $file->get_timecreated());

                            $eportfolios[$i]['sharestart'] = date('d.m.Y', $value['timecreated']);
                            $eportfolios[$i]['shareend'] = (!empty($value['enddate'])) ? date('d.m.Y', $value['enddate']) : './.';

                            $user = $DB->get_record('user', ['id' => $value['userid']]);

                            $eportfolios[$i]['userid'] = $user->id;
                            $eportfolios[$i]['userfullname'] = fullname($user);

                            $courseurl = new moodle_url('/course/view.php', ['id' => $course->id]);

                            $eportfolios[$i]['courseid'] = $course->id;
                            $eportfolios[$i]['coursename'] = $course->fullname;
                            $eportfolios[$i]['courseurl'] = $courseurl;

                        }

                    }

                    $i++;

                }
            }
        }
    }

    if ($tsort && $tdir) {

        $sortorder = get_sort_order($tdir);

        // Rearange the array values to return numeric indexes.
        $results = array_values($eportfolios);

        $keyvalue = array_column($eportfolios, $tsort);
        if ($keyvalue) {
            array_multisort($keyvalue, $sortorder, $results);
        }

    } else {
        // Rearange the array values to return numeric indexes.
        $results = array_values($eportfolios);
    }

    return $results;
}

// Get H5P title.
function get_h5p_title($pathnamehash) {
    global $DB;

    $h5pfile = $DB->get_record('h5p', ['pathnamehash' => $pathnamehash]);

    if ($h5pfile) {
        $json = $h5pfile->jsoncontent;
        $jsondecode = json_decode($json);

        if (isset($jsondecode->metadata)) {
            if ($jsondecode->metadata->title) {
                $title = $jsondecode->metadata->title;
            }
        } else {
            $title = $jsondecode->title;
        }

        if (!empty($title)) {
            return $title;
        }
    }
}

// Get enrolled users for sharing form.
function get_course_user_to_share($courseid) {
    global $USER;

    $coursecontext = context_course::instance($courseid);

    // Get enrolled users by course id.
    $enrolledusers = get_enrolled_users($coursecontext);

    $returnusers = array();

    foreach ($enrolledusers as $eu) {
        if ($eu->id != $USER->id) {
            $returnusers[$eu->id] = fullname($eu);
        }
    }

    return $returnusers;
}

// Get course roles for sharing form.
function get_course_roles_to_share($courseid) {
    global $DB;

    // We need a little more to do here.
    $coursecontext = context_course::instance($courseid);

    $sql = "SELECT roleid FROM {role_assignments} WHERE contextid = ? GROUP BY roleid";
    $params = array(
            'contextid' => $coursecontext->id,
    );

    // Get only assigned roles.
    $courseroles = $DB->get_records_sql($sql, $params);

    $rolenames = role_get_names($coursecontext, ROLENAME_ALIAS, true);

    $returnroles = array();

    foreach ($courseroles as $cr) {
        $returnroles[$cr->roleid] = $rolenames[$cr->roleid];
    }

    return $returnroles;
}

// Get course groups for sharing form.
function get_course_groups_to_share($courseid) {

    // Get course groups by course id.
    $coursegroups = groups_get_all_groups($courseid);

    $returngroups = array();

    foreach ($coursegroups as $cg) {
        $returngroups[$cg->id] = $cg->name;
    }

    return $returngroups;
}

// Just a temp solution until we found a better one...

function reset_session_data() {
    global $SESSION;

    unset($SESSION->eportfolio);
    save_to_session('step', 0);
}

function load_from_session($name, $default, $save = false) {
    global $SESSION;

    if (!isset($SESSION->eportfolio) || !array_key_exists($name, $SESSION->eportfolio)) {
        if ($save) {
            save_to_session($name, $default);
        }
        return $default;
    }

    return $SESSION->eportfolio[$name];
}

function save_to_session($name, $value, $default = null) {
    global $SESSION;

    if (!isset($SESSION->eportfolio)) {
        $SESSION->eportfolio = array();
    }

    if (isset($value)) {
        $SESSION->eportfolio[$name] = $value;
    } else if (isset($default)) {
        $SESSION->eportfolio[$name] = $default;
    }
}

function get_shared_participants($courseid, $fullcourse = false, $enrolled = null, $roleids = null, $groupids = null) {
    global $DB;

    $allenrolledusers = array();
    $selecteduser = array();
    $usersbyrole = array();
    $groupmembers = array();

    // Get the course context.
    $coursecontext = context_course::instance($courseid);

    // In case of shared with full course.
    if ($fullcourse) {

        $getenrolledusers = get_enrolled_users($coursecontext);

        foreach ($getenrolledusers as $eu) {
            $allenrolledusers[$eu->id] = fullname($eu);
        }

    }

    if ($enrolled) {

        $enrolled = explode(', ', $enrolled);

        foreach ($enrolled as $us) {

            $user = $DB->get_record('user', ['id' => $us]);

            $selecteduser[$user->id] = fullname($user);

        }
    }

    if ($roleids) {

        $roleids = explode(', ', $roleids);

        foreach ($roleids as $ro) {

            $user = get_role_users($ro, $coursecontext);

            foreach ($user as $us) {

                $usersbyrole[$us->id] = fullname($us);
            }

        }
    }

    if ($groupids) {

        // A little mess. Clean up...

        $groupids = explode(', ', $groupids);

        foreach ($groupids as $grp) {

            $group = groups_get_members($grp);

            foreach ($group as $gp) {
                $groupmembers[$gp->id] = fullname($gp);
            }

        }
    }

    // Put all together. Since user ids are unique we can use array replace to provide user ids as key for further usage.
    $sharedusers = array_replace($allenrolledusers, $selecteduser, $groupmembers, $usersbyrole);

    return $sharedusers;

}

function get_eportfolio_cm($courseid, $fromform = false) {
    global $DB;

    // First check, if the eportfolio activity is available.
    $activityplugin = core_plugin_manager::instance()->get_plugin_info('mod_eportfolio');
    if (!$activityplugin || !$activityplugin->is_enabled()) {
        return false;
    }

    // Get the cm ID for the eportfolio activity for the current course.
    $sql = "SELECT cm.id
        FROM {modules} m
        JOIN {course_modules} cm
        ON m.id = cm.module
        WHERE cm.course = ? AND m.name = ?";

    $params = array(
            'cm.course' => $courseid,
            'm.name' => 'eportfolio',
    );

    // We take the first activity we find for the current course.
    $coursemodule = $DB->get_record_sql($sql, $params);

    if ($coursemodule) {
        // At last but not least, let's do an availability check.
        $modinfo = get_fast_modinfo($courseid);
        $cm = $modinfo->get_cm($coursemodule->id);

        if ($cm->uservisible) {
            // User can access the activity.
            return $coursemodule->id;

        } else if ($cm->availableinfo) {
            if ($fromform) {
                // User cannot access the activity, but is still able to share an ePortfolio for grading.
                return $coursemodule->id;
            } else {
                // User cannot access the activity.
                // But on the course page they will see a why they can't access it.
                return false;
            }

        } else {
            // User cannot access the activity.
            return false;

        }
    }

}

function get_assigned_role_by_course($roleid, $coursecontextid) {
    global $DB, $USER;

    // Just return course where the user has the specified role assigned.
    $sql = "SELECT * FROM {role_assignments} WHERE contextid = ? AND userid = ? AND roleid = ?";
    $params = array(
            'contextid' => $coursecontextid,
            'userid' => $USER->id,
            'roleid' => $roleid,
    );

    return $DB->get_record_sql($sql, $params);
}

function get_sort_order($sortorder) {
    switch ($sortorder) {
        case '3':
            return SORT_DESC;
            break;
        case '4':
            return SORT_ASC;
            break;
        default:
            $dir = SORT_ASC;
    }
}

function eportfolio_send_message($courseid, $userfrom, $userto, $shareoption, $filename, $itemid) {
    global $DB, $USER;

    // If the ePortfolio is shared for grading we need the course module and the right context.
    if ($shareoption === 'grade') {
        $cmid = get_eportfolio_cm($courseid);
    }

    // View url for shared ePortfolio.
    // If shared for grading add URL to mod_eportfolio.
    if ($shareoption === 'grade') {
        $contexturl = new moodle_url('/mod/eportfolio/view.php', array('id' => $cmid));
    } else {
        $contexturl = new moodle_url('/local/eportfolio/view.php',
                array('id' => $itemid, 'course' => $courseid, 'userid' => $userfrom));
    }

    // Holds values for the string for the email message.
    $a = new stdClass;

    $a->shareoption = get_string('overview:shareoption:' . $shareoption, 'local_eportfolio');

    $userfromdata = $DB->get_record('user', ['id' => $userfrom]);
    $a->userfrom = fullname($userfromdata);

    $a->filename = $filename;
    $a->viewurl = (string) $contexturl;

    // Fetch message HTML and plain text formats
    $messagehtml = get_string('message:emailmessage', 'local_eportfolio', $a);
    $plaintext = format_text_email($messagehtml, FORMAT_HTML);

    $smallmessage = get_string('message:smallmessage', 'local_eportfolio', $a);
    $smallmessage = format_text_email($smallmessage, FORMAT_HTML);

    // Subject
    $subject = get_string('message:subject', 'local_eportfolio');

    $message = new \core\message\message();

    $message->courseid = $courseid;
    $message->component = 'local_eportfolio'; // Your plugin's name
    $message->name = 'sharing'; // Your notification name from message.php

    $message->userfrom = core_user::get_noreply_user();

    $usertodata = $DB->get_record('user', ['id' => $userto]);
    $message->userto = $usertodata;

    $message->subject = $subject;
    $message->smallmessage = $smallmessage;
    $message->fullmessage = $plaintext;
    $message->fullmessageformat = FORMAT_PLAIN;
    $message->fullmessagehtml = $messagehtml;
    $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message.
    $message->contexturl = $contexturl->out(false);
    $message->contexturlname = get_string('message:contexturlname', 'local_eportfolio');

    // Finally send the message
    message_send($message);

}
