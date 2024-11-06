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
 * @copyright   2024 weQon UG <support@weqon.net>
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

    // Add editingteacher as default value for settings.
    set_config('gradingteacher', '3', 'local_eportfolio');
    // Add user as default value for settings.
    set_config('studentroles', '5', 'local_eportfolio');

    return true;
}
