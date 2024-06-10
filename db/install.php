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
 * Code to be executed after the plugin's database scheme has been installed is defined here.
 *
 * @package     local_eportfolio
 * @category    upgrade
 * @copyright   2023 weQon UG <info@weqon.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Custom code to be run on installing the plugin.
 */
function xmldb_local_eportfolio_install() {
    global $CFG, $DB;

    // Add checkbox to course settings to mark it as an eportfolio course.
    // First step: Add customfield category.

    $addcategory = new stdClass();

    $addcategory->name = get_string('pluginname', 'local_eportfolio');
    $addcategory->description = '';
    $addcategory->timecreated = time();
    $addcategory->timemodified = '0';
    $addcategory->component = 'core_course';
    $addcategory->area = 'course';
    $addcategory->contextid = '1';

    $categoryid = $DB->insert_record('customfield_category', $addcategory);

    // Second step: Add customfield field.

    $addfield = new stdClass();

    $addfield->shortname = 'eportfolio_course';
    $addfield->name = get_string('customfield:name', 'local_eportfolio');
    $addfield->type = 'checkbox';
    $addfield->description = get_string('customfield:description', 'local_eportfolio');;
    $addfield->categoryid = $categoryid;
    $addfield->timecreated = time();
    $addfield->timemodified = '0';

    $DB->insert_record('customfield_field', $addfield);

    // Add required capabilities to student role.
    $cap = 'moodle/h5p:deploy'; // Required for students to create and share H5P files.
    $roleid = '5'; // Default role id for student.
    $contextid = context_system::instance()->id;

    // Finally add additional capabilities to the student role.
    assign_capability($cap, CAP_ALLOW, $roleid, $contextid);

    return true;
}
