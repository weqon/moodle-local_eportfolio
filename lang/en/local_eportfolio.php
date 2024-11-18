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
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'ePortfolio';
$string['navbar'] = 'ePortfolio';

$string['actions:header'] = 'Perform action';

// Set db/access - permissions.
$string['eportfolio:view_eport'] = 'View ePortfolio';

$string['error:noguestaccess'] = 'You are logged in as a guest. Guest access is not allowed for this plugin!';
$string['error:missingcapability'] = 'You do not have the necessary capability to access the “eportfolio” plugin!';

$string['error:missingconfig'] = 'Contact your Moodle administrator.';
$string['error:missingconfig:description'] = 'The ePortfolio has not been fully configured yet.<br>
Please follow the installation instructions for the plugin.';

// Overview.
$string['overview:header'] = 'ePortfolio - Overview';

$string['overview:shareoption:share'] = 'Shared';
$string['overview:shareoption:grade'] = 'Grading';
$string['overview:shareoption:template'] = 'Template';
$string['overview:helpfaq:title'] = 'Help & FAQ';

$string['navbar:tab:myeportfolios'] = 'My ePortfolios';
$string['navbar:tab:mysharedeportfolios'] = 'My shared ePortfolios';
$string['navbar:tab:mysharedeportfoliosgrade'] = 'My shared ePortfolios for grading';
$string['navbar:tab:sharedeportfolios'] = 'ePortfolios shared with me';
$string['navbar:tab:sharedeportfoliosgrade'] = 'ePortfolios shared for grading';
$string['navbar:tab:sharedtemplates'] = 'ePortfolio templates';

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
$string['overview:table:graded:done'] = 'Graded with:';
$string['overview:table:istemplate'] = 'This file was uploaded or shared as template for other users.';
$string['overview:table:filedeleted'] = 'You have deleted the ePortfolio from your personal overview.
 The file shared for grading must be permanently deleted in the course.';

$string['overview:eportfolio:fileselect'] = 'File selection';
$string['overview:eportfolio:uploadnewfile'] = 'Upload H5P file';
$string['overview:eportfolio:createnewfile'] = 'Create H5P file';
$string['overview:eportfolio:downloadfiles'] = 'Download selected ePortfolios';

$string['overview:eportfolio:nofiles:my'] = 'You have not yet created or uploaded any files to your ePortfolio.';
$string['overview:eportfolio:nofiles:myshared'] = 'You have not yet shared any files from your ePortfolio for viewing.';
$string['overview:eportfolio:nofiles:mygrade'] = 'You have not yet shared any files from your ePortfolio for grading.';
$string['overview:eportfolio:nofiles:shared'] = 'No ePortfolios have been shared with you for viewing yet.';
$string['overview:eportfolio:nofiles:grade'] = 'No ePortfolios have been shared with you for grading yet.';
$string['overview:eportfolio:nofiles:template'] = 'No templates have been shared with you yet.';

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
$string['sharing:form:courseselection:desc'] = 'Please select a course in which you would like to share your ePortfolio.<br>
You can only select courses that have been marked as an ePortfolio course and in which you are enrolled.';
$string['sharing:form:shareoptionselection'] = 'Select a sharing type';
$string['sharing:form:shareoptionselection:desc'] = 'Please select how you would like to share the ePortfolio.<br><br>
<b>Share:</b>
Course participants will only be able to view this ePortfolio.<br>
<b>Grade:</b>
Teachers will be able to grade your ePortfolio.<br>
<b>Template:</b>
Participants can reuse your ePortfolio as template.<br><br>
Optionally, you can also select a date for how long the ePortfolio should be available.';
$string['sharing:form:sharedcourses'] = 'Currently selected course';
$string['sharing:form:sharedcourses_help'] = 'You can only select courses in which you are enrolled.';
$string['sharing:form:select:allcourses'] = 'All courses';
$string['sharing:form:select:singlecourse'] = 'Select course';
$string['sharing:form:shareoption'] = 'Type of sharing';
$string['sharing:form:select:share'] = 'Share';
$string['sharing:form:select:grade'] = 'Grade';
$string['sharing:form:select:template'] = 'Template';
$string['sharing:form:enddate:enable'] = 'Set enddate';
$string['sharing:form:enddate:label'] = 'Activate date selection';
$string['sharing:form:enddate:select'] = 'Available until';
$string['sharing:form:sharedusers'] = 'Share ePortfolio with whole course or only selected participants';
$string['sharing:form:sharedusers:desc'] = 'Please select whether you would like to share your ePortfolio with the entire course or with selected participants<br>
You can share your ePortfolio with all enrolled participants in the course or only with certain roles, participants or course groups.';
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

$string['sharing:alreadyshared:info'] = 'The ePortfolio has already been shared in the following courses:';
$string['sharing:alreadyshared:course'] = 'Course';
$string['sharing:alreadyshared:shareoption'] = 'Type of sharing';

$string['sharing:share:successful'] = 'You successfully shared your ePortfolio!';
$string['sharing:share:inserterror'] = 'An error occurred while sharing the ePortfolio. Please try again!';
$string['sharing:share:alreadyexists'] = 'The ePortfolio has already been shared under the same conditions!';

// Forms general.
$string['form:field:required'] = 'Please fill in this field!';
$string['form:cancelled'] = 'The operation has been cancelled!';

// Upload form.
$string['uploadform:header'] = 'Upload H5P file';
$string['uploadform:title'] = 'Title/Name';
$string['uploadform:description'] = 'Description';
$string['uploadform:file'] = 'Select a file';
$string['uploadform:save'] = 'Upload file';
$string['uploadform:template:header'] = 'Share this file as template';
$string['uploadform:template:check'] = 'This is a template file';
$string['uploadform:template:check_help'] = 'If you share the portfolio as a template, other users can copy and use it.';
$string['uploadform:template:checklabel'] = 'Upload as template';
$string['uploadform:successful'] = 'The file has been uploaded successfully.';
$string['uploadform:error'] = 'An error occurred while uploading the file! Please try again!';
$string['uploadform:cancelled'] = 'The operation has been cancelled!';

// Create new H5P File.
$string['create:header'] = 'ePortfolio - Create new H5P File';
$string['contenteditor'] = 'Content Editor';
$string['create:success'] = 'H5P Content has been created successfully.';
$string['create:error'] = 'There was a problem creating the new H5P Content.';
$string['create:library'] = 'Library Select';
$string['h5plibraries'] = 'H5P Libraries';

// Edit H5P file.
$string['edit:header'] = 'ePortfolio - edit';
$string['edit:success'] = 'The H5P content has been successfully updated.';
$string['edit:error'] = 'An error occurred while saving the changes!';

// HelpFAQ.
$string['helpfaq:header'] = 'Help & FAQ';

// Delete files & Undo shared files.
$string['undo:header'] = 'Undo shared ePortfolio';
$string['undo:confirm'] = 'Confirm';
$string['undo:checkconfirm'] = 'Do you really want to undo the shared ePortfolio?';
$string['undo:success'] = 'Undo successfull!';
$string['undo:error'] = 'There was an error while undo the sharing for this file! Please try again!';
$string['delete:header'] = 'Delete file';
$string['delete:confirm'] = 'Confirm';
$string['delete:nocourses'] = 'Not shared in any courses.';
$string['delete:checkconfirm'] = 'Do you really want to delete the selected ePortfolio?}';
$string['delete:success'] = 'The selected file was deleted successfully!';
$string['delete:error'] = 'There was an error while deleting the file! Please try again!';
$string['use:template:header'] = 'Use ePortfolio template';
$string['use:template:confirm'] = 'Confirm';
$string['use:template:checkconfirm'] = 'Would you like to use the selected ePortfolio template?';
$string['use:template:success'] = 'The template was successfully copied to your ePortfolio for further use!';
$string['use:template:error'] = 'There was an error while copying the template file! Please try again!!';

// Events.
$string['event:eportfolio:viewed:name'] = 'ePortfolio viewed';
$string['event:eportfolio:shared:name'] = 'ePortfolio sharing';
$string['event:eportfolio:created:name'] = 'ePortfolio created';
$string['event:eportfolio:edited:name'] = 'ePortfolio edited';
$string['event:eportfolio:deleted:name'] = 'ePortfolio deleted';
$string['event:eportfolio:viewed'] =
        'The user with the id \'{$a->userid}\' viewed the ePortfolio {$a->filename} (fileid: \'{$a->fileid}\')';
$string['event:eportfolio:shared:share'] =
        'The user with the id \'{$a->userid}\' shared the ePortfolio {$a->filename} (fileid: \'{$a->fileid}\')';
$string['event:eportfolio:shared:grade'] =
        'The user with the id \'{$a->userid}\' shared the ePortfolio {$a->filename} for grading (fileid: \'{$a->fileid}\')';
$string['event:eportfolio:shared:template'] =
        'The user with the id \'{$a->userid}\' shared the ePortfolio {$a->filename} as template (fileid: \'{$a->fileid}\')';
$string['event:eportfolio:undo'] =
        'The user with the id \'{$a->userid}\' withdrawn the sharing of the ePortfolio {$a->filename} (fileid: \'{$a->fileid}\')';
$string['event:eportfolio:created'] =
        'The user with the id \'{$a->userid}\' created a new ePortfolio {$a->filename} (fileid: \'{$a->fileid}\')';
$string['event:eportfolio:edited'] =
        'The user with the id \'{$a->userid}\' edited the ePortfolio {$a->filename} (fileid: \'{$a->fileid}\')';
$string['event:eportfolio:deleted'] =
        'The user with the id \'{$a->userid}\' deleted ePortfolio {$a->filename} (fileid: \'{$a->fileid}\')';

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

// Settings.
$string['settings:general'] = 'Settings';
$string['settings:gradingteacher'] = 'Roles for grading';
$string['settings:gradingteacher:desc'] = 'Please select the roles that are allowed to grade shared
 ePortfolios in the “ePortfolio” activity.';
$string['settings:studentroles'] = 'Roles for students';
$string['settings:studentroles:desc'] = 'Please select the roles in which your students are
enrolled in the course.';
$string['settings:globalnavbar:enable'] = 'Main navigation entry';
$string['settings:globalnavbar:enable:desc'] = 'An entry for the ePortfolio is displayed in the main navigation.';

// Privacy provider.
$string['privacy:metadata:local_eportfolio'] = 'Data shared by the ePortfolio plugin';
$string['privacy:metadata:local_eportfolio:usermodified'] = 'The ID of the user who created/shared the ePortfolio data';
$string['privacy:metadata:local_eportfolio:enrolled'] = 'The ID of the user with whom the ePortfolio data was shared';
