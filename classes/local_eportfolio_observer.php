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
 * Renderer for eportfolio
 *
 * @package local_eportfolio
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_eportfolio;

defined('MOODLE_INTERNAL') || die();

/**
 * Observer class for local_eportfolio.
 */
class local_eportfolio_observer {

    /**
     * Triggered via course_deleted event.
     *
     * @param \core\event\course_deleted $event
     * @return bool true on success
     */
    public static function course_deleted(\core\event\course_deleted $event) {
        global $DB;

        // Get all records from local_eportfolio_share for affected courseid => $event->objectid.
        // Shareoption doesn't matter, since the course is gone.
        $sharedeportfolios = $DB->get_records('local_eportfolio_share', ['courseid' => $event->objectid]);

        if (!empty($sharedeportfolios)) {
            foreach ($sharedeportfolios as $shep) {
                // Delete files from shared context => fileidcontext.

                // Get file storage and file.
                $fs = get_file_storage();
                $file = $fs->get_file_by_id($shep->fileidcontext);

                if (!empty($file)) {
                    $file->delete();
                    $DB->delete_records('local_eportfolio_share', ['id' => $shep->id]);
                }
            }

        }

        return true;
    }

}