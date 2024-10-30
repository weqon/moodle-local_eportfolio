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
 * The local_eportfolio created event.
 *
 * @package     local_eportfolio
 * @copyright   2023 weQon UG <support@weqon.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_eportfolio\event;

/**
 * The local_eportfolio created event class.
 *
 * @package    local_eportfolio
 * @copyright   2023 weQon UG <support@weqon.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class eportfolio_created extends \core\event\base {

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        global $USER;
        $this->context = \context_user::instance($USER->id);
        $this->data['objecttable'] = 'eportfolio';
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    /**
     * Returns localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('event:eportfolio:created:name', 'local_eportfolio');
    }

    /**
     * Returns non-localised event description with id's for admin use only.
     *
     * @return string
     */
    public function get_description() {
        $description = $this->other['description'];

        return $description;

    }

    /**
     * Custom validation.
     *
     * @return void
     * @throws \coding_exception when validation does not pass.
     */
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['description'])) {
            throw new \coding_exception('The \'description\' value must be set.');
        }
    }

}

