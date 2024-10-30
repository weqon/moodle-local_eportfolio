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
 * Plugin administration pages are defined here.
 *
 * @package     local_eportfolio
 * @category    admin
 * @copyright   2024 weQon UG
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @var $ADMIN
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    $settings = new admin_category('local_eportfolio_settings', new lang_string('pluginname', 'local_eportfolio'));

    $settingspage = new admin_settingpage('local_eportfolio_general',
            get_string('settings:general', 'local_eportfolio'));

    // Add ePortfolio entry to main navbar.
    $settingspage->add(
            new admin_setting_configcheckbox(
                    'local_eportfolio/eportfolionavbar',
                    get_string('settings:globalnavbar:enable', 'local_eportfolio'),
                    get_string('settings:globalnavbar:enable:desc', 'local_eportfolio'),
                    true
            )
    );

    // Default role for gradingteacher.
    $settingspage->add(new admin_setting_pickroles(
            'local_eportfolio/gradingteacher',
            get_string('settings:gradingteacher', 'local_eportfolio'),
            get_string('settings:gradingteacher:desc', 'local_eportfolio'),
            ['editingteacher'],
    ));

    // Default role for students.
    $settingspage->add(new admin_setting_pickroles(
            'local_eportfolio/studentroles',
            get_string('settings:studentroles', 'local_eportfolio'),
            get_string('settings:studentroles:desc', 'local_eportfolio'),
            ['student'],
    ));

    $settings->add('local_eportfolio_settings', $settingspage);

    $ADMIN->add('localplugins', $settings);
}

