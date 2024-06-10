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
 * @copyright 2023 weQon UG {@link https://weqon.net}
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once('../../config.php');
require_once('locallib.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->libdir . '/formslib.php');

require_login();

// Since mustache templates don't support flexible tables, we have to build our own html stuff.

function renderer_output_myeportfolios($tsort = '', $tdir = '') {
    global $USER;

    $url = new moodle_url('/local/eportfolio/index.php');

    $context = context_user::instance($USER->id);

    $entries = get_my_eportfolios($context, $tsort, $tdir);

    // Start output tab content.
    echo html_writer::start_tag('div', array('class' => 'tab-pane fade show active', 'id' => 'home', 'role' => 'tabpanel',
            'aria-labelledby' => 'home-tab'));

    if (!empty($entries)) {

        // Create overview table.
        $table = new flexible_table('eportfolios:myeportfolios');
        $table->define_columns(array(
                'filename',
                'filetimecreated',
                'filetimemodified',
                'filesize',
                'actions',
        ));
        $table->define_headers(array(
                get_string('overview:table:filename', 'local_eportfolio'),
                get_string('overview:table:filetimecreated', 'local_eportfolio'),
                get_string('overview:table:filetimemodified', 'local_eportfolio'),
                get_string('overview:table:filesize', 'local_eportfolio'),
                get_string('overview:table:actions', 'local_eportfolio'),
        ));
        $table->define_baseurl($url);
        $table->set_attribute('class', 'table table-hover');
        $table->sortable(true, 'filename', SORT_ASC);
        $table->initialbars(true);
        $table->no_sorting('actions');
        $table->no_sorting('selection');
        $table->setup();

        $customdata = array();

        foreach ($entries as $ent) {
            $customdata[$ent['fileitemid']] = $ent['fileitemid'];
        }

        $formurl = new moodle_url('/local/eportfolio/download.php');
        $formattributes = array(
                'action' => $formurl,
                'method' => 'post',
                'id' => 'fileids',
        );

        echo html_writer::start_tag('form', $formattributes);

        echo html_writer::empty_tag('input',
                array('id' => 'fileids', 'type' => 'hidden', 'name' => 'fileids', 'value' => 'fileids'));

        foreach ($entries as $ent) {

            $checkboxform = html_writer::empty_tag('input', array('id' => $ent['fileitemid'], 'type' => 'checkbox',
                    'value' => $ent['fileitemid'], 'name' => 'fileids[]', 'class' => 'mr-3'));

            $actions = '';
            $actions .= html_writer::link(new moodle_url('/local/eportfolio/sharing.php', array('id' => $ent['fileitemid'],
                    'step' => '0')), '', array('class' => 'fa fa-share-alt mr-3',
                    'title' => get_string('overview:table:actions:share', 'local_eportfolio')));

            $actions .= html_writer::link($ent['fileediturl'], '', array('class' => 'fa fa-edit mr-3',
                    'title' => get_string('overview:table:actions:edit', 'local_eportfolio')));

            $actions .= html_writer::link(new moodle_url('/local/eportfolio/index.php', array('id' => $ent['fileitemid'],
                    'action' => 'delete')), '', array('class' => 'fa fa-trash mr-3',
                    'title' => get_string('overview:table:actions:delete', 'local_eportfolio')));

            // Add a hint if file is uploaded/shared as template and an undo icon to stop sharing as template.
            $istemplatefile = '';

            if ($ent['istemplate']) {

                $istemplatefile = html_writer::tag('i', '', array('class' => 'fa fa-info-circle ml-3', 'data-toggle' => 'tooltip',
                        'data-placement' => 'right', 'title' => get_string('overview:table:istemplate', 'local_eportfolio')));

                $actions .= html_writer::link($ent['undourl'], '', array('class' => 'fa fa-undo mr-3',
                        'title' => get_string('overview:table:actions:undo:template', 'local_eportfolio')));

            }

            if (!empty($ent['filenameh5p'])) {
                $filename = $ent['filenameh5p'] . ' (' . $ent['filename'] . ') ';
            } else {
                $filename = $ent['filename'];
            }

            $table->add_data(
                    array(
                            $checkboxform . html_writer::link($ent['fileviewurl'], $filename . $istemplatefile,
                            array('title' => get_string('overview:table:viewfile', 'local_eportfolio'))),
                            $ent['filetimecreated'],
                            $ent['filetimemodified'],
                            $ent['filesize'],
                            $actions,
                    )
            );
        }

        $table->finish_html();

        echo html_writer::empty_tag('input',
                array('class' => 'btn btn-primary', 'type' => 'submit', 'name' => 'submit',
                        'value' => get_string('overview:eportfolio:downloadfiles', 'local_eportfolio')));

        echo html_writer::end_tag('form');

    } else {

        echo html_writer::start_tag('p', array('class' => 'alert alert-info'));
        echo html_writer::tag('i', '', array('class' => 'fa fa-info-circle mr-1'));
        echo get_string('overview:eportfolio:nofiles:myeportfolios', 'local_eportfolio');
        echo html_writer::end_tag('p');

    }

    echo html_writer::end_tag('div');

}

function renderer_output_mysharedeportfolios($tsort = '', $tdir = '') {
    global $USER;

    $url = new moodle_url('/local/eportfolio/index.php');

    $context = context_user::instance($USER->id);

    $entries = get_my_shared_eportfolios($context, 'share', '', $tsort, $tdir);

    // Start output tab content.
    echo html_writer::start_tag('div', array('class' => 'tab-pane fade', 'id' => 'myshared', 'role' => 'tabpanel',
            'aria-labelledby' => 'myshared-tab'));

    if (!empty($entries)) {

        // Create overview table.
        $table = new flexible_table('eportfolios:mysharedeportfolios');
        $table->define_columns(array(
                'filename',
                'filetimemodified',
                'filesize',
                'coursefullname',
                'participants',
                'sharestart',
                'shareend',
                'actions',
        ));
        $table->define_headers(array(
                get_string('overview:table:filename', 'local_eportfolio'),
                get_string('overview:table:filetimemodified', 'local_eportfolio'),
                get_string('overview:table:filesize', 'local_eportfolio'),
                get_string('overview:table:coursefullname', 'local_eportfolio'),
                get_string('overview:table:participants', 'local_eportfolio'),
                get_string('overview:table:sharestart', 'local_eportfolio'),
                get_string('overview:table:shareend', 'local_eportfolio'),
                get_string('overview:table:actions', 'local_eportfolio'),
        ));
        $table->define_baseurl($url);
        $table->set_attribute('class', 'table table-hover');
        $table->sortable(true, 'filename', SORT_ASC);
        $table->initialbars(true);
        $table->no_sorting('actions');
        $table->setup();

        foreach ($entries as $ent) {

            $actions = '';
            $actions .= html_writer::link($ent['fileviewurl'], '', array('class' => 'fa fa-search mr-3',
                    'title' => get_string('overview:table:actions:view', 'local_eportfolio')));

            $actions .= html_writer::link($ent['undourl'], '', array('class' => 'fa fa-undo mr-3',
                    'title' => get_string('overview:table:actions:undo', 'local_eportfolio')));

            if (!empty($ent['filenameh5p'])) {
                $filename = $ent['filenameh5p'] . ' (' . $ent['filename'] . ') ';
            } else {
                $filename = $ent['filename'];
            }

            $table->add_data(
                    array(
                            html_writer::link($ent['fileviewurl'], $filename,
                                    array('title' => get_string('overview:table:viewfile', 'local_eportfolio'))),
                            $ent['filetimemodified'],
                            $ent['filesize'],
                            html_writer::link($ent['courseurl'], $ent['coursename'],
                                    array('title' => get_string('overview:table:viewcourse', 'local_eportfolio'))),
                            $ent['participants'],
                            $ent['sharestart'],
                            $ent['shareend'],
                            $actions,
                    )
            );
        }

        $table->finish_html();

    } else {

        echo html_writer::start_tag('p', array('class' => 'alert alert-info'));
        echo html_writer::tag('i', '', array('class' => 'fa fa-info-circle mr-1'));
        echo get_string('overview:eportfolio:nofiles:mysharedeportfolios', 'local_eportfolio');
        echo html_writer::end_tag('p');

    }

    echo html_writer::end_tag('div');

}

function renderer_output_mysharedeportfoliosgrade($tsort = '', $tdir = '') {
    global $CFG, $DB, $USER;

    $url = new moodle_url('/local/eportfolio/index.php');

    $context = context_user::instance($USER->id);

    $entries = get_my_shared_eportfolios($context, 'grade', '', $tsort, $tdir);

    // Start output tab content.
    echo html_writer::start_tag('div', array('class' => 'tab-pane fade', 'id' => 'mygrade', 'role' => 'tabpanel',
            'aria-labelledby' => 'mygrade-tab'));

    if (!empty($entries)) {

        // Create overview table.
        $table = new flexible_table('eportfolios:mysharedeportfoliosgrade');
        $table->define_columns(array(
                'filename',
                'filetimemodified',
                'filesize',
                'coursefullname',
                'sharestart',
                'grading',
                'actions',
        ));
        $table->define_headers(array(
                get_string('overview:table:filename', 'local_eportfolio'),
                get_string('overview:table:filetimemodified', 'local_eportfolio'),
                get_string('overview:table:filesize', 'local_eportfolio'),
                get_string('overview:table:coursefullname', 'local_eportfolio'),
                get_string('overview:table:sharestart', 'local_eportfolio'),
                get_string('overview:table:grading', 'local_eportfolio'),
                get_string('overview:table:actions', 'local_eportfolio'),
        ));
        $table->define_baseurl($url);
        $table->set_attribute('class', 'table table-hover');
        $table->sortable(true, 'filename', SORT_ASC);
        $table->initialbars(true);
        $table->no_sorting('actions');
        $table->setup();

        foreach ($entries as $ent) {

            if (!empty($ent['filenameh5p'])) {
                $filename = $ent['filenameh5p'] . ' (' . $ent['filename'] . ') ';
            } else {
                $filename = $ent['filename'];
            }

            $actions = '';

            // Get course module id.
            $cmid = get_eportfolio_cm($ent['courseid']);

            if ($cmid) {

                // Check, if grade exists.
                $gradeexists = $DB->get_record('eportfolio_grade',
                        ['courseid' => $ent['courseid'], 'userid' => $ent['userid'], 'itemid' => $ent['fileidcontext'],
                                'cmid' => $cmid]);

                if ($gradeexists) {
                    $grade = $gradeexists->grade . ' %';

                    // Add additional info icon for showing feedbacktext.
                    $gradefeedback =
                            html_writer::tag('i', '', array('class' => 'fa fa-info-circle ml-3', 'data-toggle' => 'tooltip',
                                    'data-placement' => 'bottom', 'title' => format_string($gradeexists->feedbacktext)));

                    $grade .= $gradefeedback;

                    // Add view icon for students.
                    $viewurl = new moodle_url($CFG->wwwroot . '/mod/eportfolio/view.php', ['id' => $cmid,
                            'fileid' => $ent['fileidcontext'], 'userid' => $ent['userid'], 'action' => 'view']);

                    $actions .= html_writer::link($viewurl, '', array('class' => 'fa fa-table mr-3',
                            'title' => get_string('overview:table:actions:viewgradeform', 'local_eportfolio')));

                } else {
                    $grade = './.';
                }
            }

            $actions .= html_writer::link($ent['fileviewurl'], '', array('class' => 'fa fa-search mr-3',
                    'title' => get_string('overview:table:actions:view', 'local_eportfolio')));

            $table->add_data(
                    array(
                            html_writer::link($ent['fileviewurl'], $filename,
                                    array('title' => get_string('overview:table:viewfile', 'local_eportfolio'))),
                            $ent['filetimemodified'],
                            $ent['filesize'],
                            html_writer::link($ent['courseurl'], $ent['coursename'],
                                    array('title' => get_string('overview:table:viewcourse', 'local_eportfolio'))),
                            $ent['sharestart'],
                            $grade,
                            $actions,
                    )
            );
        }

        $table->finish_html();

    } else {

        echo html_writer::start_tag('p', array('class' => 'alert alert-info'));
        echo html_writer::tag('i', '', array('class' => 'fa fa-info-circle mr-1'));
        echo get_string('overview:eportfolio:nofiles:mysharedeportfoliosgrade', 'local_eportfolio');
        echo html_writer::end_tag('p');

    }

    echo html_writer::end_tag('div');

}

function renderer_output_sharedeportfolios($tsort = '', $tdir = '') {

    $url = new moodle_url('/local/eportfolio/index.php');

    $entries = get_shared_eportfolios('share', '', $tsort, $tdir);

    // Start output tab content.
    echo html_writer::start_tag('div', array('class' => 'tab-pane fade', 'id' => 'shared', 'role' => 'tabpanel',
            'aria-labelledby' => 'shared-tab'));

    if (!empty($entries)) {

        // Create overview table.
        $table = new flexible_table('eportfolios:sharedeportfolios');
        $table->define_columns(array(
                'filename',
                'sharedby',
                'coursefullname',
                'sharestart',
                'shareend',
                'actions',
        ));
        $table->define_headers(array(
                get_string('overview:table:filename', 'local_eportfolio'),
                get_string('overview:table:sharedby', 'local_eportfolio'),
                get_string('overview:table:coursefullname', 'local_eportfolio'),
                get_string('overview:table:sharestart', 'local_eportfolio'),
                get_string('overview:table:shareend', 'local_eportfolio'),
                get_string('overview:table:actions', 'local_eportfolio'),
        ));
        $table->define_baseurl($url);
        $table->set_attribute('class', 'table table-hover');
        $table->sortable(true, 'filename', SORT_ASC);
        $table->initialbars(true);
        $table->no_sorting('actions');
        $table->setup();

        foreach ($entries as $ent) {

            $actions = '';
            $actions .= html_writer::link($ent['fileviewurl'], '', array('class' => 'fa fa-search mr-3',
                    'title' => get_string('overview:table:actions:view', 'local_eportfolio')));

            if (!empty($ent['filenameh5p'])) {
                $filename = $ent['filenameh5p'] . ' (' . $ent['filename'] . ') ';
            } else {
                $filename = $ent['filename'];
            }

            $table->add_data(
                    array(
                            html_writer::link($ent['fileviewurl'], $filename,
                                    array('title' => get_string('overview:table:viewfile', 'local_eportfolio'))),
                            $ent['userfullname'],
                            html_writer::link($ent['courseurl'], $ent['coursename'],
                                    array('title' => get_string('overview:table:viewcourse', 'local_eportfolio'))),
                            $ent['sharestart'],
                            $ent['shareend'],
                            $actions,
                    )
            );
        }

        $table->finish_html();

    } else {

        echo html_writer::start_tag('p', array('class' => 'alert alert-info'));
        echo html_writer::tag('i', '', array('class' => 'fa fa-info-circle mr-1'));
        echo get_string('overview:eportfolio:nofiles:sharedeportfolios', 'local_eportfolio');
        echo html_writer::end_tag('p');

    }

    echo html_writer::end_tag('div');

}

function renderer_output_sharedeportfoliosgrade($tsort = '', $tdir = '') {
    global $CFG, $DB;

    $url = new moodle_url('/local/eportfolio/index.php');

    $entries = get_shared_eportfolios('grade', '', $tsort, $tdir);

    // Start output tab content.
    echo html_writer::start_tag('div', array('class' => 'tab-pane fade', 'id' => 'grade', 'role' => 'tabpanel',
            'aria-labelledby' => 'grade-tab'));

    if (!empty($entries)) {

        // Create overview table.
        $table = new flexible_table('eportfolios:sharedeportfoliosgrade');
        $table->define_columns(array(
                'filename',
                'sharedby',
                'coursefullname',
                'sharestart',
                'graded',
                'actions',
        ));
        $table->define_headers(array(
                get_string('overview:table:filename', 'local_eportfolio'),
                get_string('overview:table:sharedby', 'local_eportfolio'),
                get_string('overview:table:coursefullname', 'local_eportfolio'),
                get_string('overview:table:sharestart', 'local_eportfolio'),
                get_string('overview:table:graded', 'local_eportfolio'),
                get_string('overview:table:actions', 'local_eportfolio'),
        ));
        $table->define_baseurl($url);
        $table->set_attribute('class', 'table table-hover');
        $table->sortable(true, 'filename', SORT_ASC);
        $table->initialbars(true);
        $table->no_sorting('actions');
        $table->setup();

        foreach ($entries as $ent) {

            $actions = '';

            // Check, if the course module is still available and visible.
            $cmid = get_eportfolio_cm($ent['courseid']);

            if ($cmid) {

                // Check, if grade exists.
                $gradeexists = $DB->get_record('eportfolio_grade',
                        ['courseid' => $ent['courseid'], 'userid' => $ent['userid'], 'itemid' => $ent['fileidcontext'],
                                'cmid' => $cmid]);

                if ($gradeexists) {
                    $graded = html_writer::tag('i', '', array('class' => 'fa fa-check text-success mr-2',
                            'title' => get_string('overview:table:graded', 'local_eportfolio')));
                    $graded .= get_string('overview:table:graded:done', 'local_eportfolio');
                } else {
                    $graded = html_writer::tag('i', '', array('class' => 'fa fa-clock-o text-danger mr-2',
                            'title' => get_string('overview:table:graded', 'local_eportfolio')));
                    $graded .= get_string('overview:table:graded:pending', 'local_eportfolio');
                }

                $viewurl = new moodle_url($CFG->wwwroot . '/mod/eportfolio/view.php', ['id' => $cmid,
                        'fileid' => $ent['fileitemid'], 'userid' => $ent['userid'], 'action' => 'grade']);

                $actions .= html_writer::link($viewurl, '', array('class' => 'fa fa-table mr-3',
                        'title' => get_string('overview:table:actions:viewgradeform', 'local_eportfolio')));

            }

            $actions .= html_writer::link($ent['fileviewurl'], '', array('class' => 'fa fa-search mr-3',
                    'title' => get_string('overview:table:actions:view', 'local_eportfolio')));

            if (!empty($ent['filenameh5p'])) {
                $filename = $ent['filenameh5p'] . ' (' . $ent['filename'] . ') ';
            } else {
                $filename = $ent['filename'];
            }

            $table->add_data(
                    array(
                            html_writer::link($ent['fileviewurl'], $filename,
                                    array('title' => get_string('overview:table:viewfile', 'local_eportfolio'))),
                            $ent['userfullname'],
                            html_writer::link($ent['courseurl'], $ent['coursename'],
                                    array('title' => get_string('overview:table:viewcourse', 'local_eportfolio'))),
                            $ent['sharestart'],
                            $graded,
                            $actions,
                    )
            );
        }

        $table->finish_html();

    } else {

        echo html_writer::start_tag('p', array('class' => 'alert alert-info'));
        echo html_writer::tag('i', '', array('class' => 'fa fa-info-circle mr-1'));
        echo get_string('overview:eportfolio:nofiles:sharedeportfoliosgrade', 'local_eportfolio');
        echo html_writer::end_tag('p');

    }

    echo html_writer::end_tag('div');

}

function renderer_output_eportfolio_templates($tsort = '', $tdir = '') {

    $url = new moodle_url('/local/eportfolio/index.php');

    $entries = get_shared_eportfolios('template', '', $tsort, $tdir);

    // Start output tab content.
    echo html_writer::start_tag('div', array('class' => 'tab-pane fade', 'id' => 'template', 'role' => 'tabpanel',
            'aria-labelledby' => 'template-tab'));

    if (!empty($entries)) {

        // Create overview table.
        $table = new flexible_table('eportfolios:sharedtemplates');
        $table->define_columns(array(
                'filename',
                'sharedby',
                'coursefullname',
                'sharestart',
                'shareend',
                'actions',
        ));
        $table->define_headers(array(
                get_string('overview:table:filename', 'local_eportfolio'),
                get_string('overview:table:sharedby', 'local_eportfolio'),
                get_string('overview:table:coursefullname', 'local_eportfolio'),
                get_string('overview:table:sharestart', 'local_eportfolio'),
                get_string('overview:table:shareend', 'local_eportfolio'),
                get_string('overview:table:actions', 'local_eportfolio'),
        ));
        $table->define_baseurl($url);
        $table->set_attribute('class', 'table table-hover');
        $table->sortable(true, 'filename', SORT_ASC);
        $table->initialbars(true);
        $table->no_sorting('actions');
        $table->setup();

        foreach ($entries as $ent) {

            $actions = '';
            $actions .= html_writer::link($ent['fileviewurl'], '', array('class' => 'fa fa-search mr-3',
                    'title' => get_string('overview:table:actions:view', 'local_eportfolio')));

            // Create new URL with action "use" and create a copy from existing file in user context -> index.php.
            $useurl = new moodle_url('index.php', ['action' => 'reuse', 'courseid' => $ent['courseid'],
                    'id' => $ent['fileitemid']]);

            $actions .= html_writer::link($useurl, '', array('class' => 'fa fa-plus-circle mr-3',
                    'title' => get_string('overview:table:actions:template', 'local_eportfolio')));

            if (!empty($ent['filenameh5p'])) {
                $filename = $ent['filenameh5p'] . ' (' . $ent['filename'] . ') ';
            } else {
                $filename = $ent['filename'];
            }

            $table->add_data(
                    array(
                            html_writer::link($ent['fileviewurl'], $filename,
                                    array('title' => get_string('overview:table:viewfile', 'local_eportfolio'))),
                            $ent['userfullname'],
                            html_writer::link($ent['courseurl'], $ent['coursename'],
                                    array('title' => get_string('overview:table:viewcourse', 'local_eportfolio'))),
                            $ent['sharestart'],
                            $ent['shareend'],
                            $actions,
                    )
            );
        }

        $table->finish_html();

    } else {

        echo html_writer::start_tag('p', array('class' => 'alert alert-info'));
        echo html_writer::tag('i', '', array('class' => 'fa fa-info-circle mr-1'));
        echo get_string('overview:eportfolio:nofiles:sharedtemplates', 'local_eportfolio');
        echo html_writer::end_tag('p');

    }

    echo html_writer::end_tag('div');

}
