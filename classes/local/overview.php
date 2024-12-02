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
 * Renderer for eportfolio
 *
 * @package local_eportfolio
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_eportfolio\local\overview;

defined('MOODLE_INTERNAL') || die();

require_once('locallib.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->libdir . '/formslib.php');

/**
 * Output class for local_eportfolio.
 */
class overview {

    /**
     * Construct the base stuff.
     *
     * @param string $url
     * @param string $section
     * @param string $tsort
     * @param int $tdir
     */
    public function __construct($url, $section, $tsort = null, $tdir = null) {
        $this->url = $url;
        $this->section = $section;
        $this->tsort = $tsort;
        $this->tdir = $tdir;
    }

    /**
     * Return the view.
     *
     * @return void
     */
    public function display() {
        global $OUTPUT;

        // Output the file selector part and the navbar for the page.
        $fileselector = new \stdClass();

        $fileselector->uploadh5pfile = 'upload.php';
        $fileselector->createh5pfile = 'create.php';

        $navitems = self::generate_navbar();

        echo $OUTPUT->render_from_template('local_eportfolio/fileselector', $fileselector);
        echo $OUTPUT->render_from_template('local_eportfolio/navbar', $navitems);

        $entries = self::get_eportfolios();

        if ($entries) {
            // Let's build the table for displaying the results.

            // Create overview table.
            $table = new \flexible_table('eportfolios:' . $this->section);

            $getcolumns = self::get_table_columns();
            $table->define_columns($getcolumns);

            $getheaders = self::get_table_headers();
            $table->define_headers($getheaders);

            $table->define_baseurl($this->url);
            $table->set_attribute('class', 'table table-hover');
            $table->sortable(true, 'filename', SORT_ASC);
            $table->initialbars(true);
            $table->no_sorting('actions');
            $table->no_sorting('filesize');
            $table->no_sorting('coursefullname');
            $table->no_sorting('participants');
            $table->no_sorting('grading');
            $table->no_sorting('sharedby');
            $table->setup();

            // Add a checkbox to select single files for download.
            if ($this->section === 'my') {
                $customdata = [];

                foreach ($entries as $ent) {
                    $customdata[$ent->fileid] = $ent->fileid;
                }

                $formurl = new \moodle_url('/local/eportfolio/download.php');
                $formattributes = [
                        'action' => $formurl,
                        'method' => 'post',
                        'id' => 'fileids',
                ];

                echo \html_writer::start_tag('form', $formattributes);

                echo \html_writer::empty_tag('input',
                        ['id' => 'fileids', 'type' => 'hidden', 'name' => 'fileids', 'value' => 'fileids',
                                'alt' => get_string('overview:eportfolio:fileselect', 'local_eportfolio')]);
            }

            foreach ($entries as $ent) {

                $gettabledata = self::get_table_data($ent);

                $table->add_data($gettabledata);
            }

            $table->finish_html();

            // Download is only available on the my page.
            if ($this->section === 'my') {
                echo \html_writer::empty_tag('input',
                        ['class' => 'btn btn-primary', 'type' => 'submit', 'name' => 'submit',
                                'value' => get_string('overview:eportfolio:downloadfiles', 'local_eportfolio'),
                                'role' => 'button', 'alt' => get_string('overview:eportfolio:downloadfiles', 'local_eportfolio'),
                                'title' => get_string('overview:eportfolio:downloadfiles', 'local_eportfolio')]);

                echo \html_writer::end_tag('form');
            }

        } else {
            // No files found for selected section.
            $nofilesdata = new \stdClass();

            $nofilesdata->infostring = get_string('overview:eportfolio:nofiles:' . $this->section, 'local_eportfolio');

            echo $OUTPUT->render_from_template('local_eportfolio/nofiles', $nofilesdata);
        }

        $footerdata = new \stdClass();

        $footerdata->helpfaqurl = 'https://github.com/weqon/moodle-local_eportfolio/wiki';

        echo $OUTPUT->render_from_template('local_eportfolio/footer', $footerdata);

    }

    /**
     * Get ePortfolios by section.
     *
     * @return mixed
     */
    public function get_eportfolios() {
        global $DB, $USER;

        if ($this->section === 'my') {
            // Only user specific files are stored here.
            $sql = "SELECT * FROM {local_eportfolio}";
        } else {
            // If we are not in my ePortfolios section.
            $sql = "SELECT * FROM {local_eportfolio_share}";
        }

        $params = [];

        $whereclauses = [];

        switch ($this->section) {
            case 'my': // My personal ePortfolios.
                $params['usermodified'] = (int) $USER->id;
                $whereclauses[] = 'usermodified = :usermodified';
                break;
            case 'myshared': // My ePortfolios shared for viewing.
                $params['shareoption'] = 'share';
                $whereclauses[] = 'shareoption = :shareoption';
                $params['usermodified'] = (int) $USER->id;
                $whereclauses[] = 'usermodified = :usermodified';
                break;
            case 'mygrade': // My ePortfolios shared for grading.
                $params['shareoption'] = 'grade';
                $whereclauses[] = 'shareoption = :shareoption';
                $params['usermodified'] = (int) $USER->id;
                $whereclauses[] = 'usermodified = :usermodified';
                break;
            case 'shared': // Shared ePortfolios with me for viewing.
                // We will sort the results later. First we take all results.
                // ToDo: Might be a performance issue, if there are too many results.
                $params['shareoption'] = 'share';
                $whereclauses[] = 'shareoption = :shareoption';
                $params['usermodified'] = (int) $USER->id;
                $whereclauses[] = 'usermodified != :usermodified';
                break;
            case 'grade': // Shared ePortfolios with me for grading.
                $params['shareoption'] = 'grade';
                $whereclauses[] = 'shareoption = :shareoption';
                $params['usermodified'] = (int) $USER->id;
                $whereclauses[] = 'usermodified != :usermodified';
                break;
            case 'template': // Shared ePortfolios as template.
                $params['shareoption'] = 'template';
                $whereclauses[] = 'shareoption = :shareoption';
                $params['usermodified'] = (int) $USER->id;
                $whereclauses[] = 'usermodified != :usermodified';
                break;
        }

        if (!empty($whereclauses)) {
            $sql .= " WHERE ";
            $sql .= "(" . implode(" AND ", $whereclauses) . ")";

        }

        // If tsort and tdir is set.
        $sortorder = '';

        if ($this->tsort) {

            $orderby = self::get_sort_order($this->tdir);

            if ($this->tsort === 'filename') {
                $orderbyfield = 'title';
            } else if ($this->tsort === 'filetimecreated') {
                $orderbyfield = 'timecreated';
            } else if ($this->tsort === 'filetimemodified') {
                $orderbyfield = 'timemodified';
            } else if ($this->tsort === 'sharestart') {
                $orderbyfield = 'timecreated';
            } else if ($this->tsort === 'shareend') {
                $orderbyfield = 'enddate';
            }

            $sortorder = " ORDER BY " . $orderbyfield . " " . $orderby;

        }

        if (!empty($sortorder)) {
            $sql .= $sortorder;
        }

        $eportfolios = $DB->get_records_sql($sql, $params);

        $returneports = [];

        // Check, if user is enrolled in same course as the ePortfolio was shared.
        if ($this->section === 'shared' || $this->section === 'grade' || $this->section === 'template') {

            foreach ($eportfolios as $eport) {
                $coursecontext = \context_course::instance($eport->courseid);

                if (is_enrolled($coursecontext, $USER) || is_siteadmin($USER->id)) {
                    $returneports[] = $eport;
                }
            }

            return $returneports;

        }

        return $eportfolios;

    }

    /**
     *  Output content based on set sort order.
     *
     * @param int $sortorder
     * @return int|void
     */
    private function get_sort_order($sortorder) {
        switch ($sortorder) {
            case '3':
                return 'DESC';
                break;
            case '4':
                return 'ASC';
                break;
            default:
                return 'ASC';
        }
    }

    /**
     * Define columns for the table.
     *
     * @return string|string[]
     */
    private function get_table_columns() {

        $columns = '';

        switch ($this->section) {
            case 'my':
                $columns = [
                        'filename',
                        'filetimecreated',
                        'filetimemodified',
                        'filesize',
                        'actions',
                ];
                break;
            case 'myshared':
                $columns = [
                        'filename',
                        'filetimemodified',
                        'filesize',
                        'coursefullname',
                        'participants',
                        'sharestart',
                        'shareend',
                        'actions',
                ];
                break;
            case 'mygrade':
                $columns = [
                        'filename',
                        'filetimemodified',
                        'filesize',
                        'coursefullname',
                        'sharestart',
                        'grading',
                        'actions',
                ];
                break;
            case 'grade':
                $columns = [
                        'filename',
                        'sharedby',
                        'coursefullname',
                        'sharestart',
                        'graded',
                        'actions',
                ];
                break;
            case 'shared':
            case 'template':
                $columns = [
                        'filename',
                        'sharedby',
                        'coursefullname',
                        'sharestart',
                        'shareend',
                        'actions',
                ];
                break;
        }

        return $columns;

    }

    /**
     * Define table headers.
     *
     * @return array|string
     */
    private function get_table_headers() {

        $headers = '';

        switch ($this->section) {
            case 'my':
                $headers = [
                        get_string('overview:table:filename', 'local_eportfolio'),
                        get_string('overview:table:filetimecreated', 'local_eportfolio'),
                        get_string('overview:table:filetimemodified', 'local_eportfolio'),
                        get_string('overview:table:filesize', 'local_eportfolio'),
                        get_string('overview:table:actions', 'local_eportfolio'),
                ];
                break;
            case 'myshared':
                $headers = [
                        get_string('overview:table:filename', 'local_eportfolio'),
                        get_string('overview:table:filetimemodified', 'local_eportfolio'),
                        get_string('overview:table:filesize', 'local_eportfolio'),
                        get_string('overview:table:coursefullname', 'local_eportfolio'),
                        get_string('overview:table:participants', 'local_eportfolio'),
                        get_string('overview:table:sharestart', 'local_eportfolio'),
                        get_string('overview:table:shareend', 'local_eportfolio'),
                        get_string('overview:table:actions', 'local_eportfolio'),
                ];
                break;
            case 'mygrade':
                $headers = [
                        get_string('overview:table:filename', 'local_eportfolio'),
                        get_string('overview:table:filetimemodified', 'local_eportfolio'),
                        get_string('overview:table:filesize', 'local_eportfolio'),
                        get_string('overview:table:coursefullname', 'local_eportfolio'),
                        get_string('overview:table:sharestart', 'local_eportfolio'),
                        get_string('overview:table:grading', 'local_eportfolio'),
                        get_string('overview:table:actions', 'local_eportfolio'),
                ];
                break;
            case 'grade':
                $headers = [
                        get_string('overview:table:filename', 'local_eportfolio'),
                        get_string('overview:table:sharedby', 'local_eportfolio'),
                        get_string('overview:table:coursefullname', 'local_eportfolio'),
                        get_string('overview:table:sharestart', 'local_eportfolio'),
                        get_string('overview:table:graded', 'local_eportfolio'),
                        get_string('overview:table:actions', 'local_eportfolio'),
                ];
                break;
            case 'shared':
            case 'template':
                $headers = [
                        get_string('overview:table:filename', 'local_eportfolio'),
                        get_string('overview:table:sharedby', 'local_eportfolio'),
                        get_string('overview:table:coursefullname', 'local_eportfolio'),
                        get_string('overview:table:sharestart', 'local_eportfolio'),
                        get_string('overview:table:shareend', 'local_eportfolio'),
                        get_string('overview:table:actions', 'local_eportfolio'),
                ];
                break;
        }

        return $headers;
    }

    /**
     * Get data to fill the table.
     *
     * @param \stdClass $ent
     * @return array|void
     */
    private function get_table_data($ent) {
        global $DB, $USER;

        $actions = '';
        $filewasdeleted = false;

        // View url - same for all.
        $viewurl = new \moodle_url('/local/eportfolio/view.php', ['id' => $ent->id, 'section' => $this->section]);
        $actions .= self::action_button_view($viewurl);

        // First get the file for the following steps.
        $fs = get_file_storage();

        if ($ent->title) {
            $file = $fs->get_file_by_id($ent->fileid);
            $filename = $ent->title;
        } else if (!empty($ent->shareoption) && $ent->shareoption === 'grade') {
            $file = $fs->get_file_by_id($ent->fileidcontext);
            $filename = $file->get_filename();
            if ($ent->fileid === '0') {
                // In case, the file was deleted, but still shared for grading.
                // Show hint, that the shared for grading eportfolio was deleted by the user.
                $filewasdeleted = true;
            }
        } else {
            $file = $fs->get_file_by_id($ent->fileid);
            $filename = self::get_h5p_title($file->get_pathnamehash());
        }

        if (!empty($file)) {
            $filesize = display_size($file->get_filesize());
        } else {
            $filesize = './';
        }

        switch ($this->section) {
            case 'my':

                // Share URL.
                $shareurl = new \moodle_url('/local/eportfolio/share.php', ['id' => $ent->id, 'step' => '0']);
                $actions .= self::action_button_share($shareurl);

                $editurl = new \moodle_url('/local/eportfolio/edit.php', ['id' => $ent->id]);
                $actions .= self::action_button_edit($editurl);

                // Delete URL.
                $deleteurl = new \moodle_url('/local/eportfolio/actions.php', ['id' => $ent->id, 'sesskey' => sesskey(),
                        'action' => 'delete']);
                $actions .= self::action_button_delete($deleteurl, $filename);

                // Add a checkbox to download the file.
                $checkboxform = \html_writer::empty_tag('input', ['id' => $ent->fileid, 'type' => 'checkbox',
                        'value' => $ent->fileid, 'name' => 'fileids[]', 'class' => 'mr-3',
                        'alt' => get_string('overview:eportfolio:fileselect', 'local_eportfolio')]);

                // Add a hint if file is uploaded/shared as template and an undo icon to stop sharing as template.
                $istemplatefile = '';

                $checktemplate = $DB->get_record('local_eportfolio_share', ['eportid' => $ent->id, 'usermodified' => $USER->id,
                        'shareoption' => 'template', 'fileid' => $ent->fileid]);

                if (!empty($checktemplate)) {

                    $istemplatefile =
                            \html_writer::tag('i', '', ['class' => 'icon fa fa-info-circle fa-fw ml-3', 'data-toggle' => 'tooltip',
                                    'data-placement' => 'right', 'role' => 'img',
                                    'title' => get_string('overview:table:istemplate', 'local_eportfolio'),
                                    'aria-label' => get_string('overview:table:istemplate', 'local_eportfolio')]);

                    // Undo URL.
                    // We need the entry from local_eportfolio_share.
                    $eportshared = $DB->get_record('local_eportfolio_share', ['eportid' => $ent->id, 'fileid' => $ent->fileid,
                            'shareoption' => 'template']);

                    if (!empty($eportshared)) {
                        $undourl =
                                new \moodle_url('/local/eportfolio/actions.php',
                                        ['id' => $eportshared->id, 'section' => $this->section,
                                                'sesskey' => sesskey(), 'action' => 'undo']);
                        $actions .= self::action_button_undo($undourl, $filename);
                    }

                }

                $tabledata = [
                        $checkboxform . \html_writer::link($viewurl, $filename . $istemplatefile,
                                ['title' => get_string('overview:table:viewfile', 'local_eportfolio')]),
                        date('d.m.Y', $ent->timecreated),
                        date('d.m.Y', $ent->timemodified),
                        $filesize,
                        $actions,
                ];

                return $tabledata;

            case 'myshared':

                // Undo URL.
                $undourl = new \moodle_url('/local/eportfolio/actions.php', ['id' => $ent->id, 'section' => $this->section,
                        'sesskey' => sesskey(), 'action' => 'undo']);
                $actions .= self::action_button_undo($undourl, $filename);

                // Course URL.
                $course = $DB->get_record('course', ['id' => $ent->courseid]);
                $courseurl = new \moodle_url('/course/view.php', ['id' => $ent->courseid]);
                $courseurlfull = \html_writer::link($courseurl, $course->fullname);

                // Additional entry details.
                $sharestart = date('d.m.Y', $ent->timecreated);
                $shareend = (!empty($ent->enddate)) ? date('d.m.Y', $ent->enddate) : './.';

                // Get participants who have access to my shared eportfolios.
                $participants = local_eportfolio_get_shared_participants($course->id, $ent->fullcourse,
                        $ent->enrolled, $ent->roles, $ent->coursegroups);

                $sharedwith = implode(', ', $participants);

                $tabledata = [
                        \html_writer::link($viewurl, $filename,
                                ['title' => get_string('overview:table:viewfile', 'local_eportfolio')]),
                        date('d.m.Y', $ent->timemodified),
                        $filesize,
                        $courseurlfull,
                        $sharedwith,
                        $sharestart,
                        $shareend,
                        $actions,
                ];

                return $tabledata;

            case 'mygrade':

                // Course URL.
                $course = $DB->get_record('course', ['id' => $ent->courseid]);
                $courseurl = new \moodle_url('/course/view.php', ['id' => $ent->courseid]);
                $courseurlfull = \html_writer::link($courseurl, $course->fullname);

                $sharestart = date('d.m.Y', $ent->timecreated);

                // Check, if the course module is still available and visible.
                $cmid = local_eportfolio_get_eportfolio_cm($ent->courseid);

                if (!empty($cmid)) {
                    // Grade URL is only visible, if the CM is available and visible.
                    $gradeurl = new \moodle_url('/mod/eportfolio/grade.php', ['id' => $cmid, 'eportid' => $ent->id]);
                    $actions .= self::action_button_grade($gradeurl);

                    // Check, if grade exists.
                    $gradeexists = $DB->get_record('eportfolio_grade',
                            ['courseid' => $ent->courseid, 'userid' => $ent->usermodified, 'fileidcontext' => $ent->fileidcontext,
                                    'cmid' => $cmid]);

                    if ($gradeexists) {
                        $grade = $gradeexists->grade . ' %';
                    } else {
                        $grade = './.';
                    }
                }

                $filedeleted = '';

                if ($filewasdeleted) {

                    $filedeleted =
                            \html_writer::tag('i', '', ['class' => 'icon fa fa-info-circle fa-fw ml-3 text-danger',
                                    'data-toggle' => 'tooltip', 'data-placement' => 'right', 'role' => 'img',
                                    'title' => get_string('overview:table:filedeleted', 'local_eportfolio'),
                                    'aria-label' => get_string('overview:table:filedeleted', 'local_eportfolio')]);
                }

                $tabledata = [
                        \html_writer::link($viewurl, $filename . $filedeleted,
                                ['title' => get_string('overview:table:viewfile', 'local_eportfolio')]),
                        date('d.m.Y', $ent->timemodified),
                        $filesize,
                        $courseurlfull,
                        $sharestart,
                        $grade,
                        $actions,
                ];

                return $tabledata;

            case 'shared':

                // Course URL.
                $course = $DB->get_record('course', ['id' => $ent->courseid]);
                $courseurl = new \moodle_url('/course/view.php', ['id' => $ent->courseid]);
                $courseurlfull = \html_writer::link($courseurl, $course->fullname);

                // Additional entry details.
                $sharestart = date('d.m.Y', $ent->timecreated);
                $shareend = (!empty($ent->enddate)) ? date('d.m.Y', $ent->enddate) : './.';

                $user = $DB->get_record('user', ['id' => $ent->usermodified]);
                $userfullname = fullname($user);

                $tabledata = [
                        \html_writer::link($viewurl, $filename,
                                ['title' => get_string('overview:table:viewfile', 'local_eportfolio')]),
                        $userfullname,
                        $courseurlfull,
                        $sharestart,
                        $shareend,
                        $actions,
                ];

                return $tabledata;

            case 'grade':

                $hasgrade = get_string('overview:table:graded:pending', 'local_eportfolio');

                // Check, if the course module is (still) available and visible.
                $cmid = local_eportfolio_get_eportfolio_cm($ent->courseid);

                if (!empty($cmid)) {
                    // Grade URL is only visible, if the CM is available and visible.
                    $gradeurl = new \moodle_url('/mod/eportfolio/grade.php', ['id' => $cmid, 'eportid' => $ent->id]);
                    $actions .= self::action_button_grade($gradeurl);

                    // Check, if grade exists.
                    $gradeexists = $DB->get_record('eportfolio_grade',
                            ['courseid' => $ent->courseid, 'userid' => $ent->usermodified, 'fileidcontext' => $ent->fileidcontext,
                                    'cmid' => $cmid]);

                    if ($gradeexists) {
                        $hasgrade = get_string('overview:table:graded:done', 'local_eportfolio') . ' ' .
                                $gradeexists->grade . ' %';
                    }

                }

                // Course URL.
                $course = $DB->get_record('course', ['id' => $ent->courseid]);
                $courseurl = new \moodle_url('/course/view.php', ['id' => $ent->courseid]);
                $courseurlfull = \html_writer::link($courseurl, $course->fullname);

                // Additional entry details.
                $sharestart = date('d.m.Y', $ent->timecreated);

                $user = $DB->get_record('user', ['id' => $ent->usermodified]);
                $userfullname = fullname($user);

                $tabledata = [
                        \html_writer::link($viewurl, $filename,
                                ['title' => get_string('overview:table:viewfile', 'local_eportfolio')]),
                        $userfullname,
                        $courseurlfull,
                        $sharestart,
                        $hasgrade,
                        $actions,
                ];

                return $tabledata;

            case 'template':

                // Create new URL with action "reuse" and create a copy from existing file in user context -> index.php.
                $useurl = new \moodle_url('/local/eportfolio/actions.php', ['id' => $ent->id, 'sesskey' => sesskey(),
                        'courseid' => $ent->courseid, 'action' => 'reuse']);
                $actions .= self::action_button_reuse($useurl, $filename);

                $course = $DB->get_record('course', ['id' => $ent->courseid]);
                $courseurl = new \moodle_url('/course/view.php', ['id' => $ent->courseid]);
                $courseurlfull = \html_writer::link($courseurl, $course->fullname);

                $user = $DB->get_record('user', ['id' => $ent->usermodified]);
                $userfullname = fullname($user);

                $sharestart = date('d.m.Y', $ent->timecreated);
                $shareend = (!empty($ent->enddate)) ? date('d.m.Y', $ent->enddate) : './.';

                $tabledata = [
                        \html_writer::link($viewurl, $filename,
                                ['title' => get_string('overview:table:viewfile', 'local_eportfolio')]),
                        $userfullname,
                        $courseurlfull,
                        $sharestart,
                        $shareend,
                        $actions,
                ];

                return $tabledata;
        }

    }

    /**
     * Generate view button.
     *
     * @param string $url
     * @return mixed
     */
    public function action_button_view($url) {
        global $OUTPUT;

        $icon = $OUTPUT->pix_icon('i/search', get_string('overview:table:actions:view', 'local_eportfolio'));
        return \html_writer::link($url, $icon, ['class' => 'mr-2']);
    }

    /**
     * Generate share button.
     *
     * @param string $url
     * @return mixed
     */
    public function action_button_share($url) {
        global $OUTPUT;

        $icon = $OUTPUT->pix_icon('i/publish', get_string('overview:table:actions:share', 'local_eportfolio'));
        return \html_writer::link($url, $icon, ['class' => 'mr-2']);
    }

    /**
     * Generate edit button.
     *
     * @param string $url
     * @return mixed
     */
    public function action_button_edit($url) {
        global $OUTPUT;

        $icon = $OUTPUT->pix_icon('i/permissions', get_string('overview:table:actions:edit', 'local_eportfolio'));
        return \html_writer::link($url, $icon, ['class' => 'mr-2']);
    }

    /**
     * Generate delete button.
     *
     * @param string $url
     * @param string $filename
     * @return mixed
     */
    public function action_button_delete($url, $filename) {
        global $OUTPUT;

        $data = new \stdClass();
        $data->deleteurl = $url->out(false);
        $data->title = $filename;

        return $OUTPUT->render_from_template('local_eportfolio/button_delete', $data);
    }

    /**
     * Generate undo button.
     *
     * @param string $url
     * @param string $filename
     * @return mixed
     */
    public function action_button_undo($url, $filename) {
        global $OUTPUT;

        $data = new \stdClass();
        $data->undourl = $url->out(false);
        $data->title = $filename;

        return $OUTPUT->render_from_template('local_eportfolio/button_undo', $data);
    }

    /**
     * Generate grading button.
     *
     * @param string $url
     * @return mixed
     */
    public function action_button_grade($url) {
        global $OUTPUT;

        $icon = $OUTPUT->pix_icon('e/table', get_string('overview:table:actions:viewgradeform', 'local_eportfolio'));
        return \html_writer::link($url, $icon, ['class' => 'mr-2']);
    }

    /**
     * Generate reuse button.
     *
     * @param string $url
     * @param string $filename
     * @return mixed
     */
    public function action_button_reuse($url, $filename) {
        global $OUTPUT;

        $data = new \stdClass();
        $data->reuseurl = $url->out(false);
        $data->title = $filename;

        return $OUTPUT->render_from_template('local_eportfolio/button_reuse', $data);
    }

    /**
     * Generate the navbar.
     *
     * @return \stdClass
     */
    private function generate_navbar() {
        global $DB;

        $navitems = new \stdClass();

        // Set aria-selected to false by default.
        $navitems->myselected = 'false';
        $navitems->mysharedselected = 'false';
        $navitems->mygradeselected = 'false';
        $navitems->sharedselected = 'false';
        $navitems->gradeselected = 'false';
        $navitems->templateselected = 'false';

        // Get the active tab class and set aria-selected to true.
        switch ($this->section) {
            case 'my':
                $navitems->myactive = 'active';
                $navitems->myselected = 'true';
                $navitems->myariaselected = 'aria-selected="true"';
                break;
            case 'myshared':
                $navitems->mysharedactive = 'active';
                $navitems->mysharedselected = 'true';
                $navitems->mysharedariaselected = 'aria-selected="true"';
                break;
            case 'mygrade':
                $navitems->mygradeactive = 'active';
                $navitems->mygradeselected = 'true';
                $navitems->mygradeariaselected = 'aria-selected="true"';
                break;
            case 'shared':
                $navitems->sharedactive = 'active';
                $navitems->sharedselected = 'true';
                $navitems->sharedariaselected = 'aria-selected="true"';
                break;
            case 'grade':
                $navitems->gradeactive = 'active';
                $navitems->gradeselected = 'true';
                $navitems->gradeariaselected = 'aria-selected="true"';
                break;
            case 'template':
                $navitems->templateactive = 'active';
                $navitems->templateselected = 'true';
                $navitems->templateariaselected = 'aria-selected="true"';
                break;
        }

        // General links.
        $myurl = new \moodle_url($this->url, ['section' => 'my']);
        $navitems->myurl = $myurl->out(false);
        $mysharedurl = new \moodle_url($this->url, ['section' => 'myshared']);
        $navitems->mysharedurl = $mysharedurl->out(false);
        $mygradeurl = new \moodle_url($this->url, ['section' => 'mygrade']);
        $navitems->mygradeurl = $mygradeurl->out(false);
        $sharedurl = new \moodle_url($this->url, ['section' => 'shared']);
        $navitems->sharedurlurl = $sharedurl->out(false);
        $templateurl = new \moodle_url($this->url, ['section' => 'template']);
        $navitems->templateurl = $templateurl->out(false);

        // Get all shared ePortfolios with type 'grade'.
        $sharedtograde = $DB->get_records('local_eportfolio_share', ['shareoption' => 'grade']);

        if ($sharedtograde) {
            $isgradingteacher = false;

            // We will check, if user has a specific capability in course context.
            foreach ($sharedtograde as $sg) {
                $coursecontext = \context_course::instance($sg->courseid);
                if (has_capability('moodle/backup:backupcourse', $coursecontext)) {
                    $isgradingteacher = true;
                }
            }

            if ($isgradingteacher) {
                $navitems->sharedeportfoliosgrade = true;
                $gradeurl = new \moodle_url($this->url, ['section' => 'grade']);
                $navitems->gradeurl = $gradeurl->out(false);

            }
        }

        return $navitems;

    }

    /**
     * Get H5P title.
     *
     * @param string $pathnamehash
     * @return void
     */
    public function get_h5p_title($pathnamehash) {
        global $DB;

        $h5pfile = $DB->get_record('h5p', ['pathnamehash' => $pathnamehash]);

        if ($h5pfile) {
            $json = $h5pfile->jsoncontent;
            $jsondecode = json_decode($json);

            if (isset($jsondecode->metadata)) {
                if ($jsondecode->metadata->title) {
                    $title = $jsondecode->metadata->title;
                }
            } else {
                $title = $jsondecode->title;
            }

            if (!empty($title)) {
                return $title;
            }
        }
    }
}
