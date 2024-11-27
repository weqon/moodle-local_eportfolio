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
use coding_exception;
use core_privacy\local\request\helper;
use context;
use context_system;
use dml_exception;
use moodle_exception;

/**
 * Privacy provider implementation for local_eportfolio plugin.
 */
class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\plugin\provider,
        \core_privacy\local\request\core_userlist_provider {

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
                        'title' => 'privacy:metadata:local_eportfolio:title',
                        'timecreated' => 'privacy:metadata:local_eportfolio:timecreated',
                        'timemodified' => 'privacy:metadata:local_eportfolio:timemodified',
                ],
                'privacy:metadata:local_eportfolio'
        );

        $collection->add_database_table(
                'local_eportfolio_share',
                [
                        'usermodified' => 'privacy:metadata:local_eportfolio_share:usermodified',
                        'title' => 'privacy:metadata:local_eportfolio_share:title',
                        'shareoption' => 'privacy:metadata:local_eportfolio_share:shareoption',
                        'enddate' => 'privacy:metadata:local_eportfolio_share:enddate',
                        'courseid' => 'privacy:metadata:local_eportfolio_share:courseid',
                        'timecreated' => 'privacy:metadata:local_eportfolio_share:timecreated',
                        'timemodified' => 'privacy:metadata:local_eportfolio_share:timemodified',
                ],
                'privacy:metadata:local_eportfolio_share'
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
        $contextlist = new contextlist();

        $sql = "
            SELECT ctx.id
            FROM {context} ctx
            WHERE ctx.instanceid = :instanceid
            ";

        $contextlist->add_from_sql($sql, ['instanceid' => 0]); // System context.

        return $contextlist;
    }

    /**
     * Get the list of users within a specific context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {

        $context = $userlist->get_context();
        if ($context->contextlevel != CONTEXT_SYSTEM) {
            return;
        }

        $sql = "
            SELECT usermodified
            FROM {local_eportfolio}
            ";

        $userlist->add_from_sql('usermodified', $sql, []);

        $sql = "
            SELECT usermodified
            FROM {local_eportfolio_share}
            ";

        $userlist->add_from_sql('usermodified', $sql, []);
    }

    /**
     * Exports all user data for the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved context list.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $context = \context_system::instance();
        $writer = \core_privacy\local\request\writer::with_context($context);

        $user = $contextlist->get_user();

        // Get user specific data from local_eportfolio.
        $sql = "
            SELECT le.*, u.firstname, u.lastname
            FROM {local_eportfolio} le
            JOIN {user} u ON le.usermodified=u.id
            WHERE le.usermodified = :usermodified
            ";

        $params = [
                'usermodified' => $user->id,
        ];

        $data = $DB->get_records_sql($sql, $params);

        if (!empty($data)) {
            $exportdatale = [];
            foreach ($data as $record) {
                $subcontext = [
                        get_string('privacy:metadata:myeportfolios', 'local_eportfolio'),
                        $record->title,
                ];

                $exportdatale = (object) [
                        'usermodified' => $record->firstname . ' - ' . $record->lastname,
                        'title' => $record->title,
                        'timecreated' => date('d.m.Y', $record->timecreated),
                        'timemodified' => date('d.m.Y', $record->timemodified),
                ];

                $writer->export_data($subcontext, $exportdatale);
            }
        }

        // Get user specific data from local_eportfolio_share.
        $sql = "
            SELECT le.*, u.firstname, u.lastname
            FROM {local_eportfolio_share} le
            JOIN {user} u ON le.usermodified = u.id
            WHERE le.usermodified = :usermodified
            ";

        $params = [
                'usermodified' => $user->id,
        ];

        $data = $DB->get_records_sql($sql, $params);

        if (!empty($data)) {
            $exportdatales = [];
            foreach ($data as $record) {

                $subcontext = [
                        get_string('privacy:metadata:mysharedeportfolios', 'local_eportfolio'),
                        $record->title,
                ];

                $course = $DB->get_record('course', ['id' => $record->courseid]);

                $exportdatales = (object) [
                        'usermodified' => $record->firstname . ' - ' . $record->lastname,
                        'title' => $record->title,
                        'shareoption' => $record->shareoption,
                        'enddate' => (!empty($record->enddate)) ? date('d.m.Y', $record->enddate) : './.',
                        'courseid' => $course->fullname,
                        'timecreated' => date('d.m.Y', $record->timecreated),
                        'timemodified' => date('d.m.Y', $record->timemodified),
                ];

                $writer->export_data($subcontext, $exportdatales);

            }
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
