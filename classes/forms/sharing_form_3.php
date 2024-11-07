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
 * Class for sharing form step 3.
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
                } else {
                    // Check, if user is enrolled as grading teacher.
                    $config = get_config('local_eportfolio');
                    $roleids = explode(',', $config->gradingteacher);

                    foreach ($roleids as $rid) {
                        $hasrole = get_assigned_role_by_course($rid, $coursecontext->id, $key);
                        if (!empty($hasrole)) {
                            $enrolled[] = &$mform->createElement('advcheckbox', $key, '', $value,
                                    ['name' => $key, 'group' => 2], $key);
                            $mform->setDefault("enrolled[$key]", false);
                        }
                    }
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
