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

/**
 * Get courses marked as ePortfolio course.
 *
 * @param array $roleids
 * @return array
 */
function get_eportfolio_courses($roleids = '') {
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
                        if (get_assigned_role_by_course($roleid, $coursecontext->id)) {
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
 * @return array
 */
function get_shared_participants($courseid, $fullcourse = false, $enrolled = null, $roleids = null, $groupids = null) {
    global $DB;

    $allenrolledusers = [];
    $selecteduser = [];
    $usersbyrole = [];
    $groupmembers = [];

    // Get the course context.
    $coursecontext = context_course::instance($courseid);

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

/**
 * Get enrolled users for sharing form.
 *
 * @param int $courseid
 * @return array
 */
function get_course_user_to_share($courseid) {
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
 * @return array
 */
function get_course_roles_to_share($courseid) {
    global $DB;

    // We need a little more to do here.
    $coursecontext = context_course::instance($courseid);

    $sql = "SELECT roleid FROM {role_assignments} WHERE contextid = ? GROUP BY roleid";
    $params = [
            'contextid' => $coursecontext->id,
    ];

    // Get only assigned roles.
    $courseroles = $DB->get_records_sql($sql, $params);

    $rolenames = role_get_names($coursecontext, ROLENAME_ALIAS, true);

    $returnroles = [];

    foreach ($courseroles as $cr) {
        $returnroles[$cr->roleid] = $rolenames[$cr->roleid];
    }

    return $returnroles;
}

/**
 * Get course groups for sharing form.
 *
 * @param int $courseid
 * @return array
 */
function get_course_groups_to_share($courseid) {

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
 * @param bool $fromform
 * @return false|void
 */
function get_eportfolio_cm($courseid, $fromform = false) {
    global $DB;

    // There must be a better solution.

    // First check, if the eportfolio activity is available and enabled.
    $activityplugin = \core_plugin_manager::instance()->get_plugin_info('mod_eportfolio');
    if (!$activityplugin || !$activityplugin->is_enabled()) {
        return false;
    }

    // Only one instance per course is allowed.
    // Get the cm ID for the eportfolio activity for the current course.
    $sql = "SELECT cm.id
        FROM {modules} m
        JOIN {course_modules} cm
        ON m.id = cm.module
        WHERE cm.course = ? AND m.name = ?";

    $params = [
            'cm.course' => $courseid,
            'm.name' => 'eportfolio',
    ];

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

/**
 * Reset data written into global session.
 *
 * @return void
 */
function reset_session_data() {
    global $SESSION;

    unset($SESSION->eportfolio);
    save_to_session('step', 0);
}

/**
 * Load data from global session.
 *
 * @param string $name
 * @param string $default
 * @param bool $save
 * @return mixed
 */
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

/**
 * Save data into global session.
 *
 * @param string $name
 * @param string $value
 * @param string $default
 * @return void
 */
function save_to_session($name, $value, $default = null) {
    global $SESSION;

    if (!isset($SESSION->eportfolio)) {
        $SESSION->eportfolio = [];
    }

    if (isset($value)) {
        $SESSION->eportfolio[$name] = $value;
    } else if (isset($default)) {
        $SESSION->eportfolio[$name] = $default;
    }
}

/**
 * Get roles by course.
 *
 * @param int $roleid
 * @param int $coursecontextid
 * @param int $userid
 * @return mixed
 */
function get_assigned_role_by_course($roleid, $coursecontextid, $userid = '') {
    global $DB, $USER;

    // Just return course where the user has the specified role assigned.
    $sql = "SELECT * FROM {role_assignments} WHERE contextid = ? AND userid = ? AND roleid = ?";
    $params = [
            'contextid' => $coursecontextid,
            'userid' => (!empty($userid)) ? $userid : $USER->id,
            'roleid' => $roleid,
    ];

    return $DB->get_record_sql($sql, $params);
}

/**
 * Check if ePortfolio was already shared.
 *
 * @param int $id
 * @param int $fileid
 * @return array|false
 */
function check_already_shared($id, $fileid) {
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
function check_config($context) {

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
