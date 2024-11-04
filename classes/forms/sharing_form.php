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
 *
 * @package     local_eportfolio
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/eportfolio/locallib.php');

/**
 * Class for sharing form.
 *
 * @package     local_eportfolio
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sharing_form_1 extends moodleform {

    /**
     * Build the form.
     *
     * @return void
     */
    public function definition() {
        global $DB;

        $mform = $this->_form; // Don't forget the underscore!

        $mform->addElement('html', '<div role="group" aria-label="progress">');

        $mform->addElement('html',
                '<span class="icon-round icon-round-primary mr-2 mt-4 mb-5" aria-current="true">1. </span>' .
                get_string('sharing:form:step:courseselection', 'local_eportfolio'));
        $mform->addElement('html', '<span class="fa fa-arrow-right mx-3"></span>');
        $mform->addElement('html',
                '<span class="icon-round icon-round-secondary mr-2 mt-4 mb-5">2. </span>' .
                get_string('sharing:form:step:shareoptionselection', 'local_eportfolio'));
        $mform->addElement('html', '<span class="fa fa-arrow-right mx-3"></span>');
        $mform->addElement('html',
                '<span class="icon-round icon-round-secondary mr-2 mt-4 mb-5">3. </span>' .
                get_string('sharing:form:step:userselection', 'local_eportfolio'));
        $mform->addElement('html', '<span class="fa fa-arrow-right mx-3"></span>');
        $mform->addElement('html',
                '<span class="icon-round icon-round-secondary mr-2 mt-4 mb-5">4. </span>' .
                get_string('sharing:form:step:confirm', 'local_eportfolio'));

        $mform->addElement('html', '</div>');

        // Course selection.
        $mform->addElement('html',
                '<h3>' . get_string('sharing:form:courseselection', 'local_eportfolio') . '</h3>');
        $mform->addElement('html',
                '<p class="mb-5">' . get_string('sharing:form:courseselection:desc', 'local_eportfolio') . '</p>');

        // Get all courses marked as eportfolio course and the specific user is enrolled as student.
        $config = get_config('local_eportfolio');
        $roleids = explode(',', $config->studentroles);

        $searchcourses = get_eportfolio_courses($roleids);
        $courses = [];

        if ($searchcourses) {
            foreach ($searchcourses as $sco) {
                $course = $DB->get_record('course', ['id' => $sco]);
                $courses[$course->id] = $course->fullname . "<br>";
            }

            $options = [
                    'multiple' => false,
                    'noselectionstring' => get_string('sharing:form:select:allcourses', 'local_eportfolio'),
                    'placeholder' => get_string('sharing:form:select:singlecourse', 'local_eportfolio'),
            ];
            $mform->addElement('autocomplete', 'sharedcourse', get_string('sharing:form:sharedcourses',
                    'local_eportfolio'), $courses, $options);
            $mform->addHelpButton('sharedcourse', 'sharing:form:sharedcourses', 'local_eportfolio');
            $mform->addRule('sharedcourse', get_string('sharing:form:select:hint', 'local_eportfolio'), 'required', null, 'client');

            // Add standard buttons.
            $this->add_action_buttons();

        } else {
            redirect(new moodle_url('index.php'),
                    get_string('sharing:form:step:nocourseselection', 'local_eportfolio'),
                    null, \core\output\notification::NOTIFY_ERROR);
        }

    }

}

/**
 * Class for sharing form.
 *
 * @package     local_eportfolio
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sharing_form_2 extends moodleform {

    /**
     * Build the form.
     *
     * @return void
     */
    public function definition() {

        $mform = $this->_form; // Don't forget the underscore!

        $sharedcourseid = $this->_customdata['sharedcourse'];

        $mform->addElement('html', '<div role="group" aria-label="progress">');

        $mform->addElement('html',
                '<span class="icon-round icon-round-secondary mr-2 mt-4 mb-5">1. </span>' .
                get_string('sharing:form:step:courseselection', 'local_eportfolio'));
        $mform->addElement('html', '<span class="fa fa-arrow-right mx-3"></span>');
        $mform->addElement('html',
                '<span class="icon-round icon-round-primary mr-2 mt-4 mb-5" aria-current="true">2. </span>' .
                get_string('sharing:form:step:shareoptionselection', 'local_eportfolio'));
        $mform->addElement('html', '<span class="fa fa-arrow-right mx-3"></span>');
        $mform->addElement('html',
                '<span class="icon-round icon-round-secondary mr-2 mt-4 mb-5">3. </span>' .
                get_string('sharing:form:step:userselection', 'local_eportfolio'));
        $mform->addElement('html', '<span class="fa fa-arrow-right mx-3"></span>');
        $mform->addElement('html',
                '<span class="icon-round icon-round-secondary mr-2 mt-4 mb-5">4. </span>' .
                get_string('sharing:form:step:confirm', 'local_eportfolio'));

        $mform->addElement('html', '</div>');

        // Select shareing type and optional enddate.
        $mform->addElement('html',
                '<h3>' . get_string('sharing:form:shareoptionselection', 'local_eportfolio') . '</h3>');
        $mform->addElement('html',
                '<p class="mb-5">' . get_string('sharing:form:shareoptionselection:desc', 'local_eportfolio') . '</p>');

        // Add select to choose sharing or grading.
        // Before we add "grade" as an option, check if the activity is available and enabled.
        $selectvalues = [];
        $selectvalues['share'] = get_string('sharing:form:select:share', 'local_eportfolio');

        if ($cmid = get_eportfolio_cm($sharedcourseid, true)) {
            $selectvalues['grade'] = get_string('sharing:form:select:grade', 'local_eportfolio');

            // Also submit the cm id as hidden value.
            $mform->addElement('hidden', 'cmid', $cmid);
        }

        // If current user is enrolled as editingteacher in the selected course show the share as template option.
        // Currently only default role for editingteacher is allowed.
        // ToDo: Make this configurable.
        $roleid = '3';
        $coursecontext = context_course::instance($sharedcourseid);

        $roleassigned = get_assigned_role_by_course($roleid, $coursecontext->id);

        if ($roleassigned) {
            $selectvalues['template'] = get_string('sharing:form:select:template', 'local_eportfolio');
        }

        $mform->addElement('select', 'shareoption',
                get_string('sharing:form:shareoption', 'local_eportfolio'), $selectvalues);
        $mform->setType('shareoption', PARAM_TEXT);

        $mform->addElement('advcheckbox', 'shareendenabled', get_string('sharing:form:enddate:enable', 'local_eportfolio'),
                get_string('sharing:form:enddate:label', 'local_eportfolio'), ['group' => 1], [0, 1]);

        $currentyear = date('Y', time());

        $dateparams = [
                'startyear' => $currentyear,
                'stopyear' => $currentyear + 3,
                'step' => 5,
                'optional' => false,
        ];

        // Set enddate when the file will be removed from the course.
        $mform->addElement('date_time_selector', 'shareend', get_string('sharing:form:enddate:select', 'local_eportfolio'),
                $dateparams);

        $mform->disabledIf('shareend', 'shareendenabled', 'eq', '0');

        $mform->hideIf('shareend', 'shareoption', 'eq', 'grade');
        $mform->hideIf('shareendenabled', 'shareoption', 'eq', 'grade');

        // Add standard buttons.
        $this->add_action_buttons();

    }

}

/**
 * Class for sharing form.
 *
 * @package     local_eportfolio
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sharing_form_3 extends moodleform {

    /**
     * Build the form.
     *
     * @return void
     */
    public function definition() {

        $mform = $this->_form; // Don't forget the underscore!

        $sharedcourseid = $this->_customdata['sharedcourse'];
        $shareoption = $this->_customdata['shareoption'];

        $mform->addElement('html', '<div role="group" aria-label="progress">');

        $mform->addElement('html',
                '<span class="icon-round icon-round-secondary mr-2 mt-4 mb-5">1. </span>' .
                get_string('sharing:form:step:courseselection', 'local_eportfolio'));
        $mform->addElement('html', '<span class="fa fa-arrow-right mx-3"></span>');
        $mform->addElement('html',
                '<span class="icon-round icon-round-secondary mr-2 mt-4 mb-5">2. </span>' .
                get_string('sharing:form:step:shareoptionselection', 'local_eportfolio'));
        $mform->addElement('html', '<span class="fa fa-arrow-right mx-3"></span>');
        $mform->addElement('html',
                '<span class="icon-round icon-round-primary mr-2 mt-4 mb-5" aria-current="true">3. </span>' .
                get_string('sharing:form:step:userselection', 'local_eportfolio'));
        $mform->addElement('html', '<span class="fa fa-arrow-right mx-3"></span>');
        $mform->addElement('html',
                '<span class="icon-round icon-round-secondary mr-2 mt-4 mb-5">4. </span>' .
                get_string('sharing:form:step:confirm', 'local_eportfolio'));

        $mform->addElement('html', '</div>');

        // Select complete course, users, groups or roles to share with.
        $mform->addElement('html',
                '<h3>' . get_string('sharing:form:sharedusers', 'local_eportfolio') . '</h3>');
        $mform->addElement('html',
                '<p class="mb-5">' . get_string('sharing:form:sharedusers:desc', 'local_eportfolio') . '</p>');

        $selectcourse = [
                '0' => get_string('sharing:form:select:pleaseselect', 'local_eportfolio'),
        ];

        // Maybe there is a better option, but for now it's working.
        if ($shareoption == 'grade') {
            $selectcourse['2'] = get_string('sharing:form:select:targetgroup', 'local_eportfolio');
        } else {
            $selectcourse['1'] = get_string('sharing:form:select:fullcourse', 'local_eportfolio');
            $selectcourse['2'] = get_string('sharing:form:select:targetgroup', 'local_eportfolio');
        }

        // Add select to share with complete course.
        $mform->addElement('select', 'fullcourse', get_string('sharing:form:fullcourse', 'local_eportfolio'),
                $selectcourse);

        $mform->addRule('fullcourse', get_string('sharing:form:select:pleaseselect', 'local_eportfolio'),
                'nonzero', null, 'client');

        // Get assigned course roles.
        $courseroles = get_course_roles_to_share($sharedcourseid);

        if ($courseroles) {
            $roles = [];
            foreach ($courseroles as $key => $value) {
                if ($shareoption != 'grade') {
                    $roles[] = &$mform->createElement('advcheckbox', $key, '', $value, ['name' => $key, 'group' => 1], $key);
                    $mform->setDefault("roles[$key]", false);
                } else if ($key == '3') {
                    $roles[] = &$mform->createElement('advcheckbox', $key, '', $value, ['name' => $key, 'group' => 1], $key);
                    $mform->setDefault("roles[$key]", false);
                }
            }
            $mform->addGroup($roles, 'roles', get_string('sharing:form:roles', 'local_eportfolio'));
            $this->add_checkbox_controller(1, ' ');
            $mform->addHelpButton('roles', 'sharing:form:roles', 'local_eportfolio');
        }

        // Get enrolled users.
        $enrolledusers = get_course_user_to_share($sharedcourseid);

        // Get course context.
        $coursecontext = context_course::instance($sharedcourseid);

        if ($enrolledusers) {
            $enrolled = [];
            foreach ($enrolledusers as $key => $value) {
                if ($shareoption != 'grade') {
                    $enrolled[] = &$mform->createElement('advcheckbox', $key, '', $value,
                            ['name' => $key, 'group' => 2], $key);
                    $mform->setDefault("enrolled[$key]", false);
                } else if (has_capability('mod/eportfolio:grade_eport', $coursecontext, $key)) {
                    $enrolled[] = &$mform->createElement('advcheckbox', $key, '', $value,
                            ['name' => $key, 'group' => 2], $key);
                    $mform->setDefault("enrolled[$key]", false);
                }
            }
            $mform->addGroup($enrolled, 'enrolled', get_string('sharing:form:enrolledusers', 'local_eportfolio'));
            $this->add_checkbox_controller(2, ' ');
            $mform->addHelpButton('enrolled', 'sharing:form:enrolledusers', 'local_eportfolio');
        }

        // Get available course groups only if it's not shared for grading.
        if ($shareoption != 'grade') {
            $coursegroups = get_course_groups_to_share($sharedcourseid);

            if ($coursegroups) {
                $groups = [];
                foreach ($coursegroups as $key => $value) {
                    $groups[] = &$mform->createElement('advcheckbox', $key, '', $value, ['name' => $key, 'group' => 3], $key);
                    $mform->setDefault("groups[$key]", false);
                }
                $mform->addGroup($groups, 'groups', get_string('sharing:form:groups', 'local_eportfolio'));
                $this->add_checkbox_controller(3, ' ');
                $mform->addHelpButton('groups', 'sharing:form:groups', 'local_eportfolio');
            }
        }

        // Disable checkboxes in case fullcourse is selected for sharing.
        $mform->hideIf('roles', 'fullcourse', 'eq', '0');
        $mform->hideIf('enrolled', 'fullcourse', 'eq', '0');
        $mform->hideIf('groups', 'fullcourse', 'eq', '0');

        $mform->hideIf('roles', 'fullcourse', 'eq', '1');
        $mform->hideIf('enrolled', 'fullcourse', 'eq', '1');
        $mform->hideIf('groups', 'fullcourse', 'eq', '1');

        // Add standard buttons.
        $this->add_action_buttons();

    }

}
