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
 * Class for sharing form step 1.
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

        $searchcourses = local_eportfolio_get_eportfolio_courses($roleids);
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
