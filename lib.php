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
 * Lib.php of eportfolio.
 *
 * @package     local_eportfolio
 * @copyright   2023 weQon UG <info@weqon.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function local_eportfolio_before_http_headers() {
    global $PAGE;

    $context = context_system::instance();

    if (has_capability('local/eportfolio:view_eport', $context) || is_siteadmin()) {
        $PAGE->navbar->ignore_active();
        $PAGE->primarynav->add(get_string('navbar', 'local_eportfolio'),
                new moodle_url('/local/eportfolio/index.php'));
    }

}
