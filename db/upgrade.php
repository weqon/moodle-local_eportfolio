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
 * Plugin upgrade steps are defined here.
 *
 * @package     local_eportfolio
 * @category    upgrade
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Execute local_eportfolio upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_eportfolio_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // For further information please read {@link https://docs.moodle.org/dev/Upgrade_API}.
    //
    // You will also have to create the db/install.xml file by using the XMLDB Editor.
    // Documentation for the XMLDB Editor can be found at {@link https://docs.moodle.org/dev/XMLDB_editor}.

    if ($oldversion < 2023072100) {

        // Define table local_eportfolio_share to be created.
        $table = new xmldb_table('local_eportfolio_share');

        // Adding fields to table local_eportfolio_share.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('cmid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('fileitemid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('shareoption', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('enddate', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_eportfolio_share.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for local_eportfolio_share.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Eportfolio savepoint reached.
        upgrade_plugin_savepoint(true, 2023072100, 'local', 'eportfolio');
    }

    if ($oldversion < 2023072400) {

        // Define field id to be dropped from local_eportfolio_share.
        $table = new xmldb_table('local_eportfolio_share');
        $field = new xmldb_field('cmid');

        // Conditionally launch drop field id.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field fullcourse to be added to local_eportfolio_share.
        $table = new xmldb_table('local_eportfolio_share');
        $field = new xmldb_field('fullcourse', XMLDB_TYPE_INTEGER, '1', null, null, null, null, 'shareoption');

        // Conditionally launch add field fullcourse.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field roles to be added to local_eportfolio_share.
        $table = new xmldb_table('local_eportfolio_share');
        $field = new xmldb_field('roles', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'fullcourse');

        // Conditionally launch add field roles.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field enrolled to be added to local_eportfolio_share.
        $table = new xmldb_table('local_eportfolio_share');
        $field = new xmldb_field('enrolled', XMLDB_TYPE_TEXT, null, null, null, null, null, 'roles');

        // Conditionally launch add field enrolled.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field groups to be added to local_eportfolio_share.
        $table = new xmldb_table('local_eportfolio_share');
        $field = new xmldb_field('groups', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'enrolled');

        // Conditionally launch add field groups.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Eportfolio savepoint reached.
        upgrade_plugin_savepoint(true, 2023072400, 'local', 'eportfolio');
    }

    if ($oldversion < 2023091100) {

        // Define field cmid to be added to local_eportfolio_share.
        $table = new xmldb_table('local_eportfolio_share');
        $field = new xmldb_field('cmid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'groups');

        // Conditionally launch add field cmid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Eportfolio savepoint reached.
        upgrade_plugin_savepoint(true, 2023091100, 'local', 'eportfolio');
    }

    if ($oldversion < 2023091201) {

        // Define field fileidcontext to be added to local_eportfolio_share.
        $table = new xmldb_table('local_eportfolio_share');
        $field = new xmldb_field('fileidcontext', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'fileitemid');

        // Conditionally launch add field fileidcontext.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Eportfolio savepoint reached.
        upgrade_plugin_savepoint(true, 2023091201, 'local', 'eportfolio');
    }

    if ($oldversion < 2023091202) {

        // Define field h5pid to be added to local_eportfolio_share.
        $table = new xmldb_table('local_eportfolio_share');
        $field = new xmldb_field('h5pid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'fileidcontext');

        // Conditionally launch add field h5pid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Get the h5p id for shared ePortfolios to fill the new field h5pid for existing entries.
        $eportfolios = $DB->get_records('local_eportfolio_share');

        if (!empty($eportfolios)) {
            foreach ($eportfolios as $eport) {

                $sql = "SELECT h.id FROM {files} f
                            JOIN {h5p} h
                            ON f.pathnamehash = h.pathnamehash
                            WHERE f.id = :fileid";

                $params = [
                        'fileid' => (int) $eport->fileidcontext,
                ];

                $h5pfile = $DB->get_record_sql($sql, $params);

                if (!empty($h5pfile)) {
                    $data = new stdClass();

                    $data->id = $eport->id;
                    $data->h5pid = $h5pfile->id;

                    $DB->update_record('local_eportfolio_share', $data);
                }
            }
        }

        // Eportfolio savepoint reached.
        upgrade_plugin_savepoint(true, 2023091202, 'local', 'eportfolio');

    }

    if ($oldversion < 2023092800) {

        // Rename field groups on table local_eportfolio_share to coursegroups.
        // "Groups" might be a reserved word depending on server configuration.
        $table = new xmldb_table('local_eportfolio_share');
        $field = new xmldb_field('groups', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'enrolled');

        // Launch rename field coursegroups.
        $dbman->rename_field($table, $field, 'coursegroups');

        // Eportfolio savepoint reached.
        upgrade_plugin_savepoint(true, 2023092800, 'local', 'eportfolio');
    }

    if ($oldversion < 2024082100) {

        // Define table local_eportfolio to be created.
        $table = new xmldb_table('local_eportfolio');

        // Adding fields to table local_eportfolio.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('fileid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('h5pid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_eportfolio.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for local_eportfolio.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Eportfolio savepoint reached.
        upgrade_plugin_savepoint(true, 2024082100, 'local', 'eportfolio');

        // Now create new DB entries for user specific files.
        // First get all files for component local_eportfolio.
        $eportfoliofiles = $DB->get_records('files', ['component' => 'local_eportfolio', 'filearea' => 'eportfolio']);

        if (!empty($eportfoliofiles)) {
            foreach ($eportfoliofiles as $efile) {

                // If the current file id is in the table local_eportfolio_share as fileidcontext - skip!
                if ($efile->filename != '.' && !$DB->record_exists('local_eportfolio_share', ['fileidcontext' => $efile->id])) {

                    // Get the H5P file.
                    $h5pfile = $DB->get_record('h5p', ['pathnamehash' => $efile->pathnamehash]);

                    // In case there is no H5P file we assume the entry was deleted.
                    if (empty($h5pfile)) {

                        $insertfile = new stdClass();

                        $insertfile->fileid = $efile->id;
                        $insertfile->h5pid = $h5pfile->id;
                        $insertfile->usermodified = $efile->userid;
                        $insertfile->timecreated = $efile->timecreated;
                        $insertfile->timemodified = $efile->timemodified;

                        // To avoid duplicate entries. File IDs should be unique table local_eportfolio.
                        if (!$DB->get_record('local_eportfolio', ['fileid' => $efile->id])) {
                            $DB->insert_record('local_eportfolio', $insertfile);
                        }

                    }
                }
            }
        }
    }

    if ($oldversion < 2024082101) {

        // Rename field fileid on table local_eportfolio_share to fileid.
        $table = new xmldb_table('local_eportfolio_share');
        $field = new xmldb_field('fileitemid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'courseid');

        // Launch rename field fileitemid.
        $dbman->rename_field($table, $field, 'fileid');

        // Define field usermodified to be added to local_eportfolio_share.
        $table = new xmldb_table('local_eportfolio_share');
        $field = new xmldb_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'enddate');

        // Conditionally launch add field usermodified.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Migrate userid to usermodified.
        $eportfolios = $DB->get_records('local_eportfolio_share');

        if (!empty($eportfolios)) {
            foreach ($eportfolios as $eport) {
                $data = $eport;
                $eport->usermodified = $eport->userid;

                $DB->update_record('local_eportfolio_share', $data);
            }
        }

        // Define field timemodified to be added to local_eportfolio_share.
        $table = new xmldb_table('local_eportfolio_share');
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timecreated');

        // Conditionally launch add field timemodified.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Eportfolio savepoint reached.
        upgrade_plugin_savepoint(true, 2024082101, 'local', 'eportfolio');
    }

    if ($oldversion < 2024082102) {

        // Define field eportid to be added to local_eportfolio_share.
        $table = new xmldb_table('local_eportfolio_share');
        $field = new xmldb_field('eportid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');

        // Conditionally launch add field eportid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Update local_eportfolio_share to add eportid.
        // First get all files for component local_eportfolio_share.
        $eportfoliofiles = $DB->get_records('local_eportfolio_share');

        if (!empty($eportfoliofiles)) {
            foreach ($eportfoliofiles as $efile) {
                // Get eport entry.
                $eport = $DB->get_record('local_eportfolio', ['fileid' => $efile->fileid]);

                // In case there is no entry we assume the entry was deleted.
                if (!empty($eport)) {
                    $updatedata = $efile;
                    $efile->eportid = $eport->id;
                    $DB->update_record('local_eportfolio_share', $updatedata);
                }
            }
        }

        // Eportfolio savepoint reached.
        upgrade_plugin_savepoint(true, 2024082102, 'local', 'eportfolio');
    }

    if ($oldversion < 2024082103) {

        // Define field title to be added to local_eportfolio.
        $table = new xmldb_table('local_eportfolio');
        $field = new xmldb_field('title', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'id');

        // Conditionally launch add field title.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field description to be added to local_eportfolio.
        $table = new xmldb_table('local_eportfolio');
        $field = new xmldb_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null, 'title');

        // Conditionally launch add field description.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Eportfolio savepoint reached.
        upgrade_plugin_savepoint(true, 2024082103, 'local', 'eportfolio');
    }

    if ($oldversion < 2024082104) {

        // Define field title to be added to local_eportfolio_share.
        $table = new xmldb_table('local_eportfolio_share');
        $field = new xmldb_field('title', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'eportid');

        // Conditionally launch add field title.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Eportfolio savepoint reached.
        upgrade_plugin_savepoint(true, 2024082104, 'local', 'eportfolio');
    }

    if ($oldversion < 2024111100) {

        // Define field userid to be dropped from local_eportfolio_share.
        $table = new xmldb_table('local_eportfolio_share');
        $field = new xmldb_field('userid');

        // Conditionally launch drop field title.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Eportfolio savepoint reached.
        upgrade_plugin_savepoint(true, 2024111101, 'local', 'eportfolio');
    }

    if ($oldversion < 2024111500) {
        global $CFG;

        // Get custommenuitems - Site administration -> Appearance -> Themes -> Theme settings.
        $custommenuitem = $CFG->custommenuitems;

        if (!empty($custommenuitem)) {
            // Remove old menu entry for ePortfolio.
            $custommenuitem = str_replace('ePortfolio|/local/eportfolio/index.php', '', $custommenuitem);

            // Update the config entry.
            set_config('custommenuitems', $custommenuitem);
        }

        // Eportfolio savepoint reached.
        upgrade_plugin_savepoint(true, 2024111500, 'local', 'eportfolio');
    }

    if ($oldversion < 2024112700) {

        // Changing type of field courseid on table local_eportfolio_share to int.
        $table = new xmldb_table('local_eportfolio_share');
        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'title');

        // Launch change of type for field courseid.
        $dbman->change_field_type($table, $field);

        // Eportfolio savepoint reached.
        upgrade_plugin_savepoint(true, 2024112700, 'local', 'eportfolio');
    }

    return true;
}
