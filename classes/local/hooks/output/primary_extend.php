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

namespace local_eportfolio\local\hooks\output;

/**
 * Allows plugins to extend the primary navigation.
 *
 * @package local_eportfolio
 * @copyright   2025 weQon UG <support@weqon.net>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Callback to allow modifying the primary navigation for local_eportfolio.
 */
class primary_extend {
    /**
     * Callback to allow modify headers.
     *
     * @param \core\hook\navigation\primary_extend $hook
     */
    public static function callback(\core\hook\navigation\primary_extend $hook): void {
        global $PAGE;

        // Get eportfolionavbar from settings.
        $config = get_config('local_eportfolio');

        if (!empty($config->eportfolionavbar)) {

            $context = \context_system::instance();

            if (has_capability('local/eportfolio:view_eport', $context) || has_capability('moodle/site:config', $context)) {
                $hook->get_primaryview()->add(get_string('navbar', 'local_eportfolio'),
                        new \moodle_url('/local/eportfolio/index.php'));
            }
        }
    }
}
