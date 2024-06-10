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
 * Plugin strings are defined here.
 *
 * @package     local_eportfolio
 * @category    string
 * @copyright   2023 weQon UG <info@weqon.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'ePortfolio';
$string['navbar'] = 'ePortfolio';

// db/access - permissions.
$string['eportfolio:view_eport'] = 'View ePortfolio';

$string['error:noguestaccess'] = 'You are logged in as a guest. Guest access is not allowed for this plugin.';

// Overview.
$string['overview:header'] = 'ePortfolio - Overview';

$string['overview:shareoption:share'] = 'Shared';
$string['overview:shareoption:grade'] = 'Grading';
$string['overview:shareoption:template'] = 'Template';
$string['overview:helpfaq:title'] = 'Help & FAQ';

$string['overview:tab:myeportfolios'] = 'My ePortfolios';
$string['overview:tab:mysharedeportfolios'] = 'My shared ePortfolios';
$string['overview:tab:mysharedeportfoliosgrade'] = 'My shared ePortfolios for grading';
$string['overview:tab:sharedeportfolios'] = 'ePortfolios shared with me';
$string['overview:tab:sharedeportfoliosgrade'] = 'ePortfolios shared for grading';
$string['overview:tab:sharedtemplates'] = 'ePortfolio templates';

$string['overview:table:actions'] = 'Actions';
$string['overview:table:actions:share'] = 'Share ePortfolio';
$string['overview:table:actions:edit'] = 'Edit file';
$string['overview:table:actions:delete'] = 'Delete file';
$string['overview:table:actions:view'] = 'View file';
$string['overview:table:actions:viewgradeform'] = 'View grading form';
$string['overview:table:actions:undo'] = 'Undo share';
$string['overview:table:actions:undo:template'] = 'Undo file sharing as template';
$string['overview:table:actions:template'] = 'Use template';

$string['overview:table:viewfile'] = 'View file';
$string['overview:table:viewcourse'] = 'View course';
$string['overview:table:viewgradeform'] = 'View grading form';
$string['overview:table:selection'] = 'Select';
$string['overview:table:filename'] = 'Filename';
$string['overview:table:filetimecreated'] = 'Created/Uploaded';
$string['overview:table:filetimemodified'] = 'Last modified';
$string['overview:table:filesize'] = 'Filesize';
$string['overview:table:coursefullname'] = 'Shared in course';
$string['overview:table:sharedby'] = 'Shared by';
$string['overview:table:participants'] = 'Shared with';
$string['overview:table:sharestart'] = 'Shared on';
$string['overview:table:shareend'] = 'Shared until';
$string['overview:table:grading'] = 'Grade';
$string['overview:table:graded'] = 'Graded?';
$string['overview:table:graded:pending'] = 'Pending';
$string['overview:table:graded:done'] = 'Done';
$string['overview:table:istemplate'] = 'This file was uploaded or shared as template for other users.';

$string['overview:eportfolio:fileselect'] = 'File selection';
$string['overview:eportfolio:uploadnewfile'] = 'Upload H5P file';
$string['overview:eportfolio:createnewfile'] = 'Create H5P file';
$string['overview:eportfolio:downloadfiles'] = 'Download selected ePortfolios';

$string['overview:eportfolio:nofiles:myeportfolios'] = 'You have not yet created or uploaded any files to your ePortfolio.';
$string['overview:eportfolio:nofiles:mysharedeportfolios'] = 'You have not yet shared any files from your ePortfolio for viewing.';
$string['overview:eportfolio:nofiles:mysharedeportfoliosgrade'] =
        'You have not yet shared any files from your ePortfolio for grading.';
$string['overview:eportfolio:nofiles:sharedeportfolios'] = 'No ePortfolios have been shared with you for viewing yet.';
$string['overview:eportfolio:nofiles:sharedeportfoliosgrade'] = 'No ePortfolios have been shared with you for grading yet.';
$string['overview:eportfolio:nofiles:sharedtemplates'] = 'No templates have been shared with you yet.';

// Customfield.
$string['customfield:name'] = 'ePortfolio';
$string['customfield:description'] = 'Share this course for ePortfolios';

// View.
$string['view:header'] = 'View ePortfolio';
$string['view:eportfolio:button:backtoeportfolio'] = 'Back to overview';
$string['view:eportfolio:button:backtocourse'] = 'Back to course';
$string['view:eportfolio:button:edit'] = 'Edit H5P file';
$string['view:eportfolio:sharedby'] = 'Shared by';
$string['view:eportfolio:timecreated'] = 'Created at';
$string['view:eportfolio:timemodified'] = 'Last modified';

// Sharing.
$string['sharing:header'] = 'Share ePortfolio';
$string['sharing:form:step:nocourseselection'] = 'Currently there is no course available to share your ePortfolio.';
$string['sharing:form:step:courseselection'] = 'Select course';
$string['sharing:form:step:shareoptionselection'] = 'Select share option';
$string['sharing:form:select:hint'] = 'Please select a course';
$string['sharing:form:step:userselection'] = 'Select participants';
$string['sharing:form:step:confirm'] = 'Share ePortfolio';
$string['sharing:form:courseselection'] = 'Select a course to share';
$string['sharing:form:shareoptionselection'] = 'Select a sharing type';
$string['sharing:form:sharedcourses'] = 'Select course';
$string['sharing:form:sharedcourses_help'] = 'You can only select courses in which you are enrolled.';
$string['sharing:form:select:allcourses'] = 'All courses';
$string['sharing:form:select:singlecourse'] = 'Select course';
$string['sharing:form:shareoption'] = 'Type of sharing';
$string['sharing:form:shareoption_help'] = 'Share:<br>
Course participants will only be able to view this ePortfolio.<br><br>
Grade:<br>
Teachers will be able to grade your ePortfolio.<br><br>
Template:<br>
Participants can reuse your ePortfolio as template.';
$string['sharing:form:select:share'] = 'Share';
$string['sharing:form:select:grade'] = 'Grade';
$string['sharing:form:select:template'] = 'Template';
$string['sharing:form:enddate'] = 'Available until';
$string['sharing:form:enddate_help'] = 'Please activate and select a date by which the ePortfolio will
be available in the course or to the participants.';
$string['sharing:form:sharedusers'] = 'Share ePortfolio with whole course or only selected participants';
$string['sharing:form:fullcourse'] = 'Share ePortfolio with';
$string['sharing:form:select:pleaseselect'] = 'Please select';
$string['sharing:form:select:fullcourse'] = 'Share with complete course';
$string['sharing:form:select:targetgroup'] = 'Share with selected participants';
$string['sharing:form:roles'] = 'Roles to share with';
$string['sharing:form:roles_help'] = 'Only participants with this role assignments are able to view/grade the ePortfolio';
$string['sharing:form:enrolledusers'] = 'Participants to share with';
$string['sharing:form:enrolledusers_help'] = 'Only selected participants are able to view/grade the ePortfolio';
$string['sharing:form:groups'] = 'Course groups to share with';
$string['sharing:form:groups_help'] = 'Only group members are able to view/grade the ePortfolio';

$string['sharing:share:successful'] = 'You successfully shared your ePortfolio!';
$string['sharing:share:inserterror'] = 'An error occurred while sharing the ePortfolio. Please try again!';
$string['sharing:share:alreadyexists'] = 'The ePortfolio has already been shared under the same conditions!';

// Upload form.
$string['uploadform:header'] = 'Upload H5P file';
$string['uploadform:file'] = 'Select a file';
$string['uploadform:template:header'] = 'Share this file as template';
$string['uploadform:template:check'] = 'This is a template file';
$string['uploadform:template:check_help'] = 'If you share the portfolio as a template, other users can copy and use it.';
$string['uploadform:template:checklabel'] = 'Upload as template';
$string['uploadform:successful'] = 'The file has been uploaded successfully.';
$string['uploadform:error'] = 'An error occurred while uploading the file! Please try again!';
$string['uploadform:cancelled'] = 'The operation has been cancelled!';

// HelpFAQ.
$string['helpfaq:header'] = 'Help & FAQ';

// Delete files & Undo shared files.
$string['undo:header'] = 'Undo shared file';
$string['undo:confirm'] = 'Confirm';
$string['undo:checkconfirm'] = 'Do you really want to undo the shared file?<br><br>
Filename: {$a->filename}<br><br>Course: {$a->course}<br><br>Share type: {$a->shareoption}';
$string['undo:success'] = 'Undo successfull!';
$string['undo:error'] = 'There was an error while undo the sharing for this file! Please try again!';
$string['delete:header'] = 'Delete file';
$string['delete:confirm'] = 'Confirm';
$string['delete:nocourses'] = 'Not shared in any courses.';
$string['delete:checkconfirm'] = 'Do you really want to delete this file?<br><br>
Filename: {$a->filename}<br><br>Shared in courses: {$a->courses}';
$string['delete:success'] = 'The selected file was deleted successfully!';
$string['delete:error'] = 'There was an error while deleting the file! Please try again!';
$string['use:template:success'] = 'The template was successfully copied to your ePortfolio for further use!';
$string['use:template:error'] = 'There was an error while copying the template file! Please try again!!';

// Create new H5P File.
$string['create:header'] = 'ePortfolio - Create new H5P File';
$string['contenteditor'] = 'Content Editor';
$string['create:success'] = 'H5P Content has been created successfully.';
$string['create:error'] = 'There was a problem creating the new H5P Content.';
$string['create:library'] = 'Library Select';
$string['h5plibraries'] = 'H5P Libraries';

// Events.
$string['event:eportfolio:viewed:name'] = 'ePortfolio viewed';
$string['event:eportfolio:shared:name'] = 'ePortfolio sharing';
$string['event:eportfolio:created:name'] = 'ePortfolio created';
$string['event:eportfolio:deleted:name'] = 'ePortfolio deleted';
$string['event:eportfolio:viewed'] =
        'The user with the id \'{$a->userid}\' viewed the ePortfolio {$a->filename} (itemid: \'{$a->itemid}\')';
$string['event:eportfolio:shared:share'] =
        'The user with the id \'{$a->userid}\' shared the ePortfolio {$a->filename} (itemid: \'{$a->itemid}\')';
$string['event:eportfolio:shared:grade'] =
        'The user with the id \'{$a->userid}\' shared the ePortfolio {$a->filename} for grading (itemid: \'{$a->itemid}\')';
$string['event:eportfolio:shared:template'] =
        'The user with the id \'{$a->userid}\' shared the ePortfolio {$a->filename} as template (itemid: \'{$a->itemid}\')';
$string['event:eportfolio:undo'] =
        'The user with the id \'{$a->userid}\' withdrawn the sharing of the ePortfolio {$a->filename} (itemid: \'{$a->itemid}\')';
$string['event:eportfolio:created'] =
        'The user with the id \'{$a->userid}\' created a new ePortfolio {$a->filename} (itemid: \'{$a->itemid}\')';
$string['event:eportfolio:deleted'] =
        'The user with the id \'{$a->userid}\' deleted ePortfolio {$a->filename} (itemid: \'{$a->itemid}\')';

// Message provider.
$string['messageprovider:sharing'] = 'Message about a shared ePortfolio';
$string['message:emailmessage'] =
        '<p>New ePortfolio shared with you. Type: {$a->shareoption}<br>Shared by{$a->userfrom}<br>Filename: {$a->filename}<br>URL: {$a->viewurl}</p>';
$string['message:smallmessage'] =
        '<p>New ePortfolio shared with you. Type: {$a->shareoption}<br>Shared by{$a->userfrom}<br>Filename: {$a->filename}<br>URL: {$a->viewurl}</p>';
$string['message:subject'] = 'Message about a shared ePortfolio';
$string['message:contexturlname'] = 'View shared ePortfolio';

// Download ePortfolio.
$string['download:error'] = 'No files found!';
