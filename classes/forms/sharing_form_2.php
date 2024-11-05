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
 * Class for sharing form step 2.
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

        // If current user is enrolled as grading teacher in the selected course show the share as template option.
        $config = get_config('local_eportfolio');
        $roleids = explode(',', $config->gradingteacher);
        $coursecontext = context_course::instance($sharedcourseid);

        $roleassigned = false;
        
        foreach ($roleids as $rid) {
            $hasrole = get_assigned_role_by_course($rid, $coursecontext->id);
            if (!empty($hasrole)) {
                $roleassigned = true;
            }
        }

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
