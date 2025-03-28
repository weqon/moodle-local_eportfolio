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
        global $DB;

        $mform = $this->_form; // Don't forget the underscore!

        $sharedcourseid = $this->_customdata['sharedcourse'];

        $mform->addElement('html', '<div role="group" aria-label="progress">');

        $mform->addElement('html',
                '<span class="icon-round icon-round-primary mr-2 mt-4 mb-5">1. </span>' .
                get_string('sharing:form:step:courseselection', 'local_eportfolio'));
        $mform->addElement('html', '<span class="fa fa-arrow-right mx-3"></span>');
        $mform->addElement('html',
                '<span class="icon-round icon-round-primary mr-2 mt-4 mb-5">2. </span>' .
                get_string('sharing:form:step:shareoptionselection', 'local_eportfolio'));
        $mform->addElement('html', '<span class="fa fa-arrow-right mx-3"></span>');
        $mform->addElement('html',
                '<span class="icon-round icon-round-primary mr-2 mt-4 mb-5" aria-current="true">3. </span>' .
                get_string('sharing:form:step:activityselection', 'local_eportfolio'));
        $mform->addElement('html', '<span class="fa fa-arrow-right mx-3"></span>');
        $mform->addElement('html',
                '<span class="icon-round icon-round-secondary mr-2 mt-4 mb-5">4. </span>' .
                get_string('sharing:form:step:userselection', 'local_eportfolio'));
        $mform->addElement('html', '<span class="fa fa-arrow-right mx-3"></span>');
        $mform->addElement('html',
                '<span class="icon-round icon-round-secondary mr-2 mt-4 mb-5">5. </span>' .
                get_string('sharing:form:step:confirm', 'local_eportfolio'));

        $mform->addElement('html', '</div>');

        // Select activity to share ePortfolio for grading.
        $mform->addElement('html',
                '<h3>' . get_string('sharing:form:sharedactivity', 'local_eportfolio') . '</h3>');
        $mform->addElement('html',
                '<p class="mb-5">' . get_string('sharing:form:sharedactivity:desc', 'local_eportfolio') . '</p>');

        // Add select to choose sharing or grading.

        // Target group. Radio-Buttons
        $targetgroup = [];

        $coursemodules = local_eportfolio_get_eportfolio_cm($sharedcourseid);

        foreach ($coursemodules as $cmods) {
            if ($cmods->canaccess) {
                $instance = $DB->get_record('eportfolio', ['id' => $cmods->instance]);

                $targetgroup[] = $mform->createElement('radio', 'activitygrade', '',
                        $instance->name, $cmods->id);
            }
        }

        $mform->addGroup($targetgroup, 'activitygradedata', get_string('sharing:form:sharedactivity:select', 'local_eportfolio'),
                ['<br>'], false);
        $mform->addRule('activitygradedata', get_string('sharing:form:sharedactivity:error', 'local_eportfolio'), 'required', '',
                'client');

        // Add standard buttons.
        $this->add_action_buttons();

    }

}
