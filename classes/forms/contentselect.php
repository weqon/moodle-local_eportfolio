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
 * H5P content type form for eportfolio.
 *
 * @package local_eportfolio
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_eportfolio\forms;

use core_h5p\editor_ajax;
use core_h5p\file_storage;
use core_h5p\local\library\autoloader;
use Moodle\H5PCore;

defined('MOODLE_INTERNAL') || die();

/**
 * Display all available H5P content types.
 *
 * @package local_eportfolio
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class contentselect {

    /**
     * Building the select box for all available H5P content types.
     *
     * @return array
     */
    public static function get_contenttype_types(): array {
        // Get the H5P content types available.
        autoloader::register();
        $editorajax = new editor_ajax();
        $h5pcontenttypes = $editorajax->getLatestLibraryVersions();

        $types = [];
        $h5pfilestorage = new file_storage();
        foreach ($h5pcontenttypes as $h5pcontenttype) {
            if ($h5pcontenttype->enabled) {
                // Only enabled content-types will be displayed.
                $library = [
                        'name' => $h5pcontenttype->machine_name,
                        'majorVersion' => $h5pcontenttype->major_version,
                        'minorVersion' => $h5pcontenttype->minor_version,
                ];
                $key = H5PCore::libraryToString($library);
                $type = new \stdClass();
                $type->key = $key;
                $type->typename = $h5pcontenttype->title;
                $type->typeeditorparams = 'library=' . $key;
                $type->typeicon = $h5pfilestorage->get_icon_url(
                        $h5pcontenttype->id,
                        $h5pcontenttype->machine_name,
                        $h5pcontenttype->major_version,
                        $h5pcontenttype->minor_version);
                $types[] = $type;
            }
        }

        return $types;
    }
}
