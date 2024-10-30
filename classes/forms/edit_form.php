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
 * Create new H5P content file.
 *
 * @package local_eportfolio
 * @copyright 2023 weQon UG {@link https://weqon.net}
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_eportfolio\forms;

use core_h5p\editor as h5peditor;
use core_h5p\factory;
use core_h5p\helper;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

/**
 * Display the H5P editor form.
 *
 * @package local_eportfolio
 * @copyright 2023 weQon UG {@link https://weqon.net}
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class edit_form extends \moodleform {

    /**
     * @var \stdClass
     */
    private $h5peditor;

    /**
     * Build the form.
     *
     * @return void
     */
    protected function definition() {
        global $OUTPUT;

        $mform = $this->_form;

        $customdata = $this->_customdata;

        $mform->addElement('hidden', 'contextid', $customdata['contextid']);
        $this->_form->setType('contextid', PARAM_INT);

        $mform->addElement('hidden', 'eportid', $customdata['eportid']);
        $this->_form->setType('eportid', PARAM_INT);

        $mform->addElement('hidden', 'contentid', $customdata['contentid']);
        $this->_form->setType('contentid', PARAM_INT);

        $mform->addElement('hidden', 'contenturl', $customdata['contenturl']);
        $this->_form->setType('contenturl', PARAM_LOCALURL);

        $mform->addElement('hidden', 'returnurl', $customdata['returnurl']);
        $this->_form->setType('returnurl', PARAM_LOCALURL);

        $mform->addElement('html', '<h2>' . get_string('contenteditor', 'local_eportfolio') . '</h2>');

        $mform->addElement('text', 'title', get_string('uploadform:title', 'local_eportfolio'));
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', get_string('form:field:required', 'local_eportfolio'), 'required', '', 'client');

        $mform->addElement('textarea', 'description', get_string('uploadform:description', 'local_eportfolio'));
        $mform->setType('description', PARAM_TEXT);

        $mform->addElement('html', '<div class="divider my-5"></div>');

        $editor = new h5peditor();
        $editor->set_content($customdata['contentid']);
        $this->editor = $editor;

        $mformid = 'h5peditor';
        $mform->setAttributes(['id' => $mformid] + $mform->getAttributes());

        $this->set_display_vertical();

        $this->editor->add_editor_to_form($mform);

        $mform->addElement('html', '<div class="divider my-5"></div>');

        $this->add_action_buttons();

    }

    /**
     * Save the H5P file.
     *
     * @param \stdClass $data
     * @return mixed
     */
    public function save_content(\stdClass $data) {

        // The H5P libraries expect data->id as the H5P content id.
        // The method H5PCore::saveContent throws an error if id is set but empty.
        if (empty($data->id)) {
            unset($data->id);
        }

        $h5pcontentid = $this->editor->save_content($data);

        $factory = new factory();
        $h5pfs = $factory->get_framework();

        // Needs the H5P file id to create or update the content bank record.
        $h5pcontent = $h5pfs->loadContent($h5pcontentid);
        $fs = get_file_storage();
        $file = $fs->get_file_by_hash($h5pcontent['pathnamehash']);

        return $file->get_id();
    }

}
