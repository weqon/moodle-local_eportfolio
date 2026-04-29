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
 * Locallib for ePortfolio.
 *
 * @package local_eportfolio
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Get courses marked as ePortfolio course.
 *
 * @param array $roleids
 * @return array
 */
function local_eportfolio_get_eportfolio_courses($roleids = null) {
    global $DB, $USER;

    // Get the field id to identify the custm field data.
    $customfield = $DB->get_record('customfield_field', ['shortname' => 'eportfolio_course']);

    // Get the value for custom field id.
    $customfielddata = $DB->get_records('customfield_data', ['fieldid' => $customfield->id]);

    $courses = [];

    foreach ($customfielddata as $cd) {
        // Check, if "value" -> "is ePortfolio course" is set to 1.
        if ($cd->value) {

            $context = context_system::instance();
            $coursecontext = context_course::instance($cd->instanceid);

            // Check if current user is enrolled in the course at all and is not siteadmin.
            if (is_enrolled($coursecontext, $USER->id) && !has_capability('moodle/site:config', $context)) {
                if ($roleids) {
                    // Get only assigned role.
                    foreach ($roleids as $roleid) {
                        if (local_eportfolio_get_assigned_role_by_course($roleid, $coursecontext->id)) {
                            $courses[] = $cd->instanceid; // Course ID.
                        }
                    }
                }
            } else if (has_capability('moodle/site:config', $context)) {
                // We can return all courses.
                $courses[] = $cd->instanceid;
            }
        }
    }

    return $courses;
}

/**
 * Get users who have been shared with.
 *
 * @param int $courseid
 * @param bool $fullcourse
 * @param array $enrolled
 * @param array $roleids
 * @param array $groupids
 * @return mixed
 */
function local_eportfolio_get_shared_participants($courseid, $fullcourse = null, $enrolled = null, $roleids = null,
        $groupids = null) {
    global $DB;

    // Get current config settings.
    $config = get_config('local_eportfolio');

    // Get the course context.
    $coursecontext = context_course::instance($courseid);

    $isgradingteacher = local_eportfolio_is_grading_teacher($config, $coursecontext);

    if ($config->disableuserselection && !$isgradingteacher) {
        $sharedusers = '';

        // Just in case the setting was enabled after users already shared with selected participants.
        if (!empty($enrolled)) {
            $sharedusers = get_string('overview:table:participants:enrolled', 'local_eportfolio');
        }

        if (!empty($fullcourse)) {
            $sharedusers = get_string('overview:table:participants:fullcourse', 'local_eportfolio');
        }

        if (!empty($roleids)) {
            $roleids = explode(', ', $roleids);
            $rolenames = role_get_names($coursecontext, ROLENAME_ALIAS, true);

            $sharedusers = get_string('overview:table:participants:courserole', 'local_eportfolio');

            foreach ($roleids as $rid) {
                $roles[] = $rolenames[$rid];
            }
            $sharedusers .= implode(', ', $roles);
        }

        if (!empty($groupids)) {
            // Get course groups by course id.
            $groupids = explode(', ', $groupids);
            $coursegroups = groups_get_all_groups($courseid);

            $sharedusers = get_string('overview:table:participants:coursegroup', 'local_eportfolio');

            foreach ($groupids as $gid) {
                $groups[] = $coursegroups[$gid]->name;
            }

            $sharedusers .= implode(', ', $groups);
        }
    } else {

        $allenrolledusers = [];
        $selecteduser = [];
        $usersbyrole = [];
        $groupmembers = [];

        // In case of shared with full course.
        if (!empty($fullcourse)) {
            $getenrolledusers = get_enrolled_users($coursecontext);

            foreach ($getenrolledusers as $eu) {
                $allenrolledusers[$eu->id] = fullname($eu);
            }

        }

        if (!empty($enrolled)) {
            $enrolled = explode(', ', $enrolled);

            foreach ($enrolled as $us) {
                $user = $DB->get_record('user', ['id' => $us]);
                $selecteduser[$user->id] = fullname($user);
            }
        }

        if (!empty($roleids)) {
            $roleids = explode(', ', $roleids);

            foreach ($roleids as $ro) {
                $user = get_role_users($ro, $coursecontext);

                foreach ($user as $us) {
                    $usersbyrole[$us->id] = fullname($us);
                }
            }
        }

        if (!empty($groupids)) {
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
    }

    return $sharedusers;
}

/**
 * Get users who have been shared with for sending message.
 *
 * @param int $courseid
 * @param bool $fullcourse
 * @param array $enrolled
 * @param array $roleids
 * @param array $groupids
 * @return mixed
 */
function local_eportfolio_get_shared_participants_message($courseid, $fullcourse = null, $enrolled = null, $roleids = null,
        $groupids = null) {
    global $DB;

    // Get the course context.
    $coursecontext = context_course::instance($courseid);

    $allenrolledusers = [];
    $selecteduser = [];
    $usersbyrole = [];
    $groupmembers = [];

    // In case of shared with full course.
    if (!empty($fullcourse)) {
        $getenrolledusers = get_enrolled_users($coursecontext);

        foreach ($getenrolledusers as $eu) {
            $allenrolledusers[$eu->id] = fullname($eu);
        }

    }

    if (!empty($enrolled)) {
        $enrolled = explode(', ', $enrolled);

        foreach ($enrolled as $us) {
            $user = $DB->get_record('user', ['id' => $us]);
            $selecteduser[$user->id] = fullname($user);
        }
    }

    if (!empty($roleids)) {
        $roleids = explode(', ', $roleids);

        foreach ($roleids as $ro) {
            $user = get_role_users($ro, $coursecontext);

            foreach ($user as $us) {
                $usersbyrole[$us->id] = fullname($us);
            }
        }
    }

    if (!empty($groupids)) {
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

/**
 * Get enrolled users for sharing form.
 *
 * @param int $courseid
 * @return array
 */
function local_eportfolio_get_course_user_to_share($courseid) {
    global $USER;

    $coursecontext = context_course::instance($courseid);

    // Get enrolled users by course id.
    $enrolledusers = get_enrolled_users($coursecontext);

    if (!empty($enrolledusers)) {
        $returnusers = [];

        foreach ($enrolledusers as $eu) {
            if ($eu->id != $USER->id) {
                $returnusers[$eu->id] = fullname($eu);
            }
        }

        return $returnusers;
    }
}

/**
 * Get course roles for sharing form.
 *
 * @param int $courseid
 * @param string $shareoption
 * @return array
 */
function local_eportfolio_get_course_roles_to_share($courseid, $shareoption = null) {

    // Get roles from config.
    $config = get_config('local_eportfolio');

    $studentroleids = [];

    if ($shareoption != 'grade') {
        $studentroleids = explode(',', $config->studentroles);
    }

    $gradingtecherroleids = explode(',', $config->gradingteacher);

    $courseroles = array_merge($studentroleids, $gradingtecherroleids);

    $coursecontext = context_course::instance($courseid);

    $rolenames = role_get_names($coursecontext, ROLENAME_ALIAS, true);

    $returnroles = [];

    foreach ($courseroles as $cr) {
        $returnroles[$cr] = $rolenames[$cr];
    }

    return $returnroles;
}

/**
 * Get course groups for sharing form.
 *
 * @param int $courseid
 * @return array
 */
function local_eportfolio_get_course_groups_to_share($courseid) {

    // Get course groups by course id.
    $coursegroups = groups_get_all_groups($courseid);

    $returngroups = [];

    foreach ($coursegroups as $cg) {
        $returngroups[$cg->id] = $cg->name;
    }

    return $returngroups;
}

/**
 * Get course module for the ePortfolio activity.
 *
 * @param int $courseid
 * @return array
 */
function local_eportfolio_get_eportfolio_cm($courseid) {
    global $DB;

    // First check, if the eportfolio activity is available and enabled.
    $activityplugin = \core_plugin_manager::instance()->get_plugin_info('mod_eportfolio');
    if (!$activityplugin || !$activityplugin->is_enabled()) {
        return false;
    }

    // Only one instance per course is allowed.
    // Get the cm ID for the eportfolio activity for the current course.
    $sql = "SELECT cm.id, cm.instance
        FROM {modules} m
        JOIN {course_modules} cm
        ON m.id = cm.module
        WHERE cm.course = :cmcourse AND m.name = :mname";

    $params = [
            'cmcourse' => (int) $courseid,
            'mname' => 'eportfolio',
    ];

    $coursemodules = $DB->get_records_sql($sql, $params);

    $cmarr = [];

    if ($coursemodules) {
        foreach ($coursemodules as $cmod) {
            $cmoddata = new stdClass();

            // At last but not least, let's do an availability check.
            $modinfo = get_fast_modinfo($courseid);
            $cm = $modinfo->get_cm($cmod->id);

            if ($cm->uservisible) {
                // User can access the activity.
                $cmoddata->canaccess = true;
                $cmoddata->id = $cmod->id;
                $cmoddata->instance = $cmod->instance;
                $cmarr[] = $cmoddata;
            }
        }
    }

    return $cmarr;

}

/**
 * Get roles by course.
 *
 * @param int $roleid
 * @param int $coursecontextid
 * @param int $userid
 * @return mixed
 */
function local_eportfolio_get_assigned_role_by_course($roleid, $coursecontextid, $userid = null) {
    global $DB, $USER;

    // Just return course where the user has the specified role assigned.
    $sql = "SELECT * FROM {role_assignments} WHERE contextid = :contextid AND roleid = :roleid AND userid = :userid";
    $params = [
            'contextid' => (int) $coursecontextid,
            'roleid' => (int) $roleid,
    ];

    if (!empty($userid)) {
        $params['userid'] = (int) $userid;
    } else {
        $params['userid'] = (int) $USER->id;

    }

    return $DB->get_record_sql($sql, $params);
}

/**
 * Check if ePortfolio was already shared.
 *
 * @param int $id
 * @param int $fileid
 * @return array|false
 */
function local_eportfolio_check_already_shared($id, $fileid) {
    global $DB;

    $eport = $DB->get_records('local_eportfolio_share', ['eportid' => $id, 'fileid' => $fileid]);

    if ($eport) {
        $i = 0;
        $sharedeport = [];

        foreach ($eport as $ep) {
            $course = $DB->get_record('course', ['id' => $ep->courseid]);

            $shareoption = '';

            switch ($ep->shareoption) {
                case 'share':
                    $shareoption = get_string('sharing:form:select:share', 'local_eportfolio');
                    break;
                case 'grade':
                    $shareoption = get_string('sharing:form:select:grade', 'local_eportfolio');
                    break;
                case 'template':
                    $shareoption = get_string('sharing:form:select:template', 'local_eportfolio');
                    break;
            }

            $sharedeport[$i]['course'] = $course->fullname;
            $sharedeport[$i]['shareoption'] = $shareoption;

            $i++;
        }

        return $sharedeport;

    } else {
        return false;
    }

}

/**
 * Check, if the plugin was configured properly.
 *
 * @param stdClass $context
 * @return stdClass
 */
function local_eportfolio_check_config($context) {

    $config = get_config('local_eportfolio');

    $configset = new stdClass();

    if (empty($config->gradingteacher)) {
        $configset->missinggradingteacher = true;
    }
    if (empty($config->studentroles)) {
        $configset->missingstudentroles = true;
    }

    if (!has_capability('moodle/h5p:deploy', $context)) {
        $configset->missingh5pcapability = true;
    }

    return $configset;
}

/**
 * Check, if the plugin was configured properly.
 *
 * @param stdClass $config
 * @param stdClass $coursecontext
 * @return bool
 */

function local_eportfolio_is_grading_teacher($config, $coursecontext, $userid = null) {

    // Check, if current user is enrolled as grading teacher.
    $checkroleids = explode(',', $config->gradingteacher);

    $isgradingteacher = false;

    foreach ($checkroleids as $rid) {
        $hasrole = local_eportfolio_get_assigned_role_by_course($rid, $coursecontext->id, $userid);
        if (!empty($hasrole)) {
            $isgradingteacher = true;
        }
    }

    return $isgradingteacher;
}

/**
 * Get the upload max file size.
 *
 * @return int
 */

function local_eportfolio_get_upload_max_file_size() {
    global $CFG;

    $config = get_config('local_eportfolio');

    if ($config->maxbytes != 0) {
        // Check, if plugin has an upload limit set.
        $filemaxbytes = $config->maxbytes;
    } else if ($CFG->maxbytes == 0) {
        // In case global setting is set to default.
        $filemaxbytes = get_max_upload_file_size();
    } else if ($CFG->maxbytes != 0) {
        // check, if the global upload limit was set.
        $filemaxbytes = $CFG->maxbytes;
    } else {
        // Just in case.
        $filemaxbytes = $CFG->maxbytes;
    }

    // Check, if global upload limit is lower than the limit set by plugin.
    // Just in case the global upload limit was lowered afterwards.
    if ($config->maxbytes != 0 && $CFG->maxbytes != 0 && $config->maxbytes > $CFG->maxbytes) {
        $filemaxbytes = $CFG->maxbytes;
    }

    return (int) $filemaxbytes;

}

/**
 * Check, if user can view shared ePortfolio
 *
 * @param stdClass $share DB record local_eportfolio_share
 * @param int $userid
 *
 * @return bool
 */
function local_eportfolio_user_can_view_share($share, int $userid) {

    // If user is the owner of the file it can be accessed no matter which share option.
    if ((int) $share->usermodified === $userid) {
        return true;
    }

    $coursecontext = context_course::instance($share->courseid);
    if (!is_enrolled($coursecontext, $userid)) {
        return false;
    }

    if ((int) $share->fullcourse === 1) {
        return true;
    }

    // Explicit user list.
    if (!empty($share->enrolled)) {
        $list = explode(', ', $share->enrolled);
        if (in_array($userid, $list)) {
            return true;
        }
    }

    // Role intersection.
    if (!empty($share->roles)) {
        $shareroles = explode(', ', $share->roles);
        $userroles = get_user_roles($coursecontext, $userid);
        foreach ($userroles as $r) {
            if (in_array((int) $r->roleid, $shareroles)) {
                return true;
            }
        }
    }

    // Group membership.
    if (!empty($share->coursegroups)) {
        $sharegroups = explode(',', $share->coursegroups);
        foreach ($sharegroups as $gid) {
            if (groups_is_member($gid, $userid)) {
                return true;
            }
        }
    }

    return false;
}
