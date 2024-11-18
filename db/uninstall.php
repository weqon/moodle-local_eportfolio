<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Code that is executed before the tables and data are dropped during the plugin uninstallation.
 *
 * @package     local_eportfolio
 * @category    upgrade
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Custom uninstallation procedure.
 */
function xmldb_local_eportfolio_uninstall() {
    global $DB;

    // Remove entries for customfield category, field and data.
    // First we need to collect some data from customfield_field.
    // ToDo: Check, if there is a core function/API available to remove it.
    $customfieldfield = $DB->get_record('customfield_field', ['shortname' => 'eportfolio_course']);

    // We need the id and categoryid for the next steps.
    $categoryid = $customfieldfield->categoryid;
    $fieldid = $customfieldfield->id;

    // Delete the existing entries in customfield_data.
    $DB->delete_records('customfield_data', ['fieldid' => $fieldid]);

    // Delete the existing entry in customfield_field.
    $DB->delete_records('customfield_field', ['id' => $fieldid]);

    // Delete the existing entry in customfield_field.
    $DB->delete_records('customfield_category', ['id' => $categoryid]);

    // Delete all associated H5P files.
    $eportfoliofiles = $DB->get_records('files', ['component' => 'local_eportfolio', 'filearea' => 'eportfolio']);

    foreach ($eportfoliofiles as $eport) {

        // Get H5P files and delete them.
        if ($eport->filename != '.') {

            $h5pfile = $DB->get_record('h5p', ['pathnamehash' => $eport->pathnamehash]);

            if (!empty($h5pfile)) {
                $DB->delete_records('h5p', ['id' => $h5pfile->id]);
            }
        }

    }

    // Finally delete all related files. Files are stored in system context by default.
    $context = context_system::instance();
    delete_area_files($context->id, 'local_eportfolio', 'eportfolio');

    return true;
}
