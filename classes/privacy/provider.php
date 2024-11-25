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
 * Privacy provider for eportfolio plugin
 *
 * @package local_eportfolio
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_eportfolio\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\writer;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\core_userlist_provider;

/**
 * Privacy provider implementation for local_eportfolio plugin.
 */
class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\plugin\provider,
        core_userlist_provider {

    /**
     * Returns metadata about the data stored by the plugin.
     *
     * @param collection $collection The collection to add metadata to.
     * @return collection The updated collection.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
                'local_eportfolio',
                [
                        'usermodified' => 'privacy:metadata:local_eportfolio:usermodified',
                        'userenrolled' => 'privacy:metadata:local_eportfolio:enrolled',
                ],
                'privacy:metadata:local_eportfolio'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information.
     *
     * @param int $userid The user ID.
     * @return contextlist The list of contexts.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        return contextlist();
    }

    /**
     * Get the list of users within a specific context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        return;
    }

    /**
     * Exports all user data for the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved context list.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;

        // Get user specific data from local_eportfolio_share.
        $data = $DB->get_records('local_eportfolio_share', [
                'usermodified' => $userid,
        ]);

        if (!empty($data)) {
            $exportdata = [];
            foreach ($data as $record) {
                $exportdata[] = [
                        'record' => $record->usermodified,
                ];
            }

            writer::with_context($context)->export_data(
                    [],
                    (object) ['eportfolio' => $exportdata]
            );
        }

        // Get user specific data from local_eportfolio_share, where current user is enrolled.
        $sql = "SELECT timecreated, usermodified FROM {local_eportfolio_share} WHERE enrolled LIKE :enrolled";
        $params = [
                'enrolled' => '%' . (int) $userid . '%',
        ];

        $data = $DB->get_records_sql($sql, $params);

        if (!empty($data)) {
            $exportdata = [];
            foreach ($data as $record) {
                $exportdata[] = [
                        'usermodified' => $record->usermodified,
                        'shared_with_userid' => $userid,
                        'timecreated' => $record->timecreated,
                ];
            }

            writer::with_context($context)->export_data(
                    [],
                    (object) ['eportfolio_share' => $exportdata]
            );
        }

        // Get user specific data from local_eportfolio.
        $data = $DB->get_records('local_eportfolio', [
                'usermodified' => $userid,
        ]);

        if (!empty($data)) {
            $exportdata = [];
            foreach ($data as $record) {
                $exportdata[] = [
                        'eportfolio_entry' => $record->title,
                        'eportfolio_description' => $record->description,
                        'usermodified' => $record->usermodified,
                        'timecreated' => $record->timecreated,
                ];
            }

            writer::with_context($context)->export_data(
                    [],
                    (object) ['eportfolio' => $exportdata]
            );
        }
    }

    /**
     * Deletes all user data for the specified contexts.
     *
     * @param context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        return;
    }

    /**
     * Deletes all user data for the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved context list.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;

        $eports = $DB->get_records('local_eportfolio', ['usermodified' => $userid]);

        foreach ($eports as $eport) {

            // Delete the file.
            $fs = get_file_storage();
            $file = $fs->get_file_by_id($eport->fileid);

            // We use the pathnamehash to get the H5P file.
            $pathnamehash = $file->get_pathnamehash();

            $h5pfile = $DB->get_record('h5p', ['pathnamehash' => $pathnamehash]);

            // If H5P, delete it from the H5P table as well.
            // Note: H5P will create an entry when the file was viewed for the first time.
            if ($h5pfile) {
                $DB->delete_records('h5p', ['id' => $h5pfile->id]);
                // Also delete from files where context = 1, itemid = h5p id component core_h5p, filearea content.
                $fs->delete_area_files('1', 'core_h5p', 'content', $h5pfile->id);
            }

            // Finally delete the selected file.
            $file->delete();
        }

        // Delete all entries from local_eportfolio_share for specific userid.
        $DB->delete_records('local_eportfolio_share', [
                'usermodified' => $userid,
        ]);

        // Delete all entries from local_eportfolio for specific userid.
        $DB->delete_records('local_eportfolio', [
                'usermodified' => $userid,
        ]);

    }

    /**
     * Delete data for all users.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     * @return void
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $users = $userlist->get_userids();

        foreach ($users as $userid) {

            $eports = $DB->get_records('local_eportfolio', ['usermodified' => $userid]);

            foreach ($eports as $eport) {

                // Delete the file.
                $fs = get_file_storage();
                $file = $fs->get_file_by_id($eport->fileid);

                // We use the pathnamehash to get the H5P file.
                $pathnamehash = $file->get_pathnamehash();

                $h5pfile = $DB->get_record('h5p', ['pathnamehash' => $pathnamehash]);

                // If H5P, delete it from the H5P table as well.
                // Note: H5P will create an entry when the file was viewed for the first time.
                if ($h5pfile) {
                    $DB->delete_records('h5p', ['id' => $h5pfile->id]);
                    // Also delete from files where context = 1, itemid = h5p id component core_h5p, filearea content.
                    $fs->delete_area_files('1', 'core_h5p', 'content', $h5pfile->id);
                }

                // Finally delete the selected file.
                $file->delete();
            }

            $DB->delete_records('local_eportfolio_share', [
                    'usermodified' => $userid,
            ]);

            $DB->delete_records('local_eportfolio', [
                    'usermodified' => $userid,
            ]);
        }

    }
}
