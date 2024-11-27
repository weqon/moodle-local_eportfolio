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
 * lib.php for ePortfolio.
 *
 * @package     local_eportfolio
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Add entry to main navigation.
 *
 * @return void
 */
function local_eportfolio_before_http_headers() {
    global $PAGE;

    // Get eportfolionavbar from settings.
    $config = get_config('local_eportfolio');

    if (!empty($config->eportfolionavbar)) {

        $context = context_system::instance();

        if (has_capability('local/eportfolio:view_eport', $context) || has_capability('moodle/site:config', $context)) {
            $PAGE->primarynav->add(get_string('navbar', 'local_eportfolio'),
                    new moodle_url('/local/eportfolio/index.php'));
        }
    }

}
