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
 * Upload form class.
 *
 * @package     local_eportfolio
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class upload_form extends moodleform {

    /**
     * Build the form.
     *
     * @return void
     */
    public function definition() {
        global $CFG, $DB;

        $mform = $this->_form; // Don't forget the underscore!

        $filemanageropts = $this->_customdata['filemanageropts'];

        $mform->addElement('text', 'title', get_string('uploadform:title', 'local_eportfolio'));
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', get_string('form:field:required', 'local_eportfolio'), 'required', '', 'client');

        $mform->addElement('textarea', 'description', get_string('uploadform:description', 'local_eportfolio'));
        $mform->setType('description', PARAM_TEXT);

        $mform->addElement('filemanager', 'eportfolio', get_string('uploadform:file', 'local_eportfolio'), null, $filemanageropts);
        $mform->addRule('eportfolio', get_string('form:field:required', 'local_eportfolio'), 'required', '', 'client');

        // Get all courses marked as ePortfolio course and the specific user is enrolled as "gradingteacher".
        // Only "gradingteacher" are allowed to upload files as templates.
        $config = get_config('local_eportfolio');
        $roleids = explode(',', $config->gradingteacher);

        $searchcourses = get_eportfolio_courses($roleids);
        $courses = [];

        if ($searchcourses) {

            // Course selection.
            $mform->addElement('header', 'courseselection', get_string('uploadform:template:header', 'local_eportfolio'));
            $mform->setExpanded('courseselection');

            $mform->addElement('checkbox', 'uploadtemplate', get_string('uploadform:template:check', 'local_eportfolio'),
                    get_string('uploadform:template:checklabel', 'local_eportfolio'));
            $mform->addHelpButton('uploadtemplate', 'uploadform:template:check', 'local_eportfolio');

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

            $mform->hideIf('sharedcourse', 'uploadtemplate', 'notchecked');

        }

        // Add standard buttons.
        $this->add_action_buttons(true, get_string('uploadform:save', 'local_eportfolio'));

    }

}
