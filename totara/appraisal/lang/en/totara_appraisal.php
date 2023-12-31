<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2010 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * totara_appraisal specific language strings.
 * these should be called like get_string('key', 'totara_appraisal');
 * Replaces lang/[lang]/local.php from 1.1 series
 *
 * @author Valerii Kuznetsov <valerii.kuznetsov@totaralms.com>
 * @author Ciaran Irvine <ciaran.irvine@totaralms.com>
 * @package totara
 * @subpackage totara_appraisal
 */
$string['activate'] = 'Activate';
$string['activatenow'] = 'Activate now';
$string['appraisal_activation'] = 'Assignee gains access to the appraisal';
$string['active'] = 'Active';
$string['activeappraisals'] = 'Active appraisals';
$string['addpage'] = 'Add new page';
$string['addstage'] = 'Add stage';
$string['aggregateselectordisplay'] = '{$a->stagename} - {$a->pagename} : {$a->questionname}';
$string['ahead'] = 'Ahead';
$string['allappraisals'] = 'All Appraisals';
$string['allappraisalsfor'] = 'All Appraisals for {$a}';
$string['answer'] = 'Answer';
$string['appraisal:manageappraisals'] = 'Manage appraisals';
$string['appraisal:cloneappraisal'] = 'Clone appraisals';
$string['appraisal:assignappraisaltogroup'] = 'Assign appraisal to group';
$string['appraisal:viewassignedusers'] = 'View assigned users';
$string['appraisal:managenotifications'] = 'Manage notifications';
$string['appraisal:manageactivation'] = 'Manage activation of appraisal';
$string['appraisal:managepageelements'] = 'Manage page elements';
$string['appraisal:printstaffappraisals'] = 'Print staff appraisals';
$string['appraisal:viewownappraisals'] = 'View own appraisals';
$string['appraisal:viewallappraisals'] = 'View full details of all appraisals';
$string['appraisal:printownappraisals'] = 'Print own appraisals';
$string['appraisal'] = 'Appraisal';
$string['appraisalcreated'] = 'Appraisal Created';
$string['appraisals'] = 'Appraisals';
$string['appraisalactivated'] = 'Appraisal {$a} activated';
$string['appraisalactivenochangesallowed'] = 'This appraisal is active, no changes can be made to learner assignments';
$string['appraisalcloned'] = 'Appraisal Cloned';
$string['appraisalclosed'] = 'Appraisal \'{$a}\' closed';
$string['appraisalclosedalertoncron'] = 'Appraisal \'{$a}\' closed and alert has been set to send on the next cron run';
$string['appraisalclosedforuser'] = 'This appraisal has been closed because {$a} is no longer assigned.';
$string['appraisalclosednochangesallowed'] = 'This appraisal is closed, no changes can be made to learner assignments';
$string['appraisalcontent'] = 'Appraisal content';
$string['appraisalsdisabled'] = 'Appraisals are not enabled on this site';
$string['appraisalfixerrors'] = 'You must fix the following errors prior to appraisal activation:';
$string['appraisalfixwarnings'] = 'Fixing the following warnings is also recommended:';
$string['appraisalhasstages'] = 'Appraisal has the following stages:';
$string['appraisalhistory'] = 'appraisal history';
$string['appraisalinvalid'] = 'Appraisal not ready for activation';
$string['appraisalinvalid:missingjob'] = 'Learner {$a->user} has not selected a job assignment yet.';
$string['appraisalinvalid:missingrole'] = 'Learner {$a->user} is missing their {$a->role}.';
$string['appraisalinvalid:pageempty'] = 'Page \'{$a}\' must have at least one element.';
$string['appraisalinvalid:redisplayfuture'] = 'Page \'{$a}\' has an invalid redisplay question.';
$string['appraisalinvalid:roles'] = 'There is no role that can answer at least one question.';
$string['appraisalinvalid:stagedue'] = 'Stage \'{$a}\' has invalid due date. It must be in the future.';
$string['appraisalinvalid:stageempty'] = 'Stage \'{$a}\' must have at least one page.';
$string['appraisalinvalid:stagenoonecananswer'] = 'Stage \'{$a}\' has no questions that can be answered.';
$string['appraisalinvalid:stagesamedue'] = 'Two or more stages have the same due date.';
$string['appraisalinvalid:status'] = 'Cannot activate appraisal that is not draft.';
$string['appraisalinvalid:learners'] = 'There are no assigned learners.';
$string['appraisalstaticlastwarning'] = 'Activating this appraisal will disable changes to all stages, pages and questions,
    and will lock the list of assigned users. It will make the appraisal available to those users and send out any messages you
    have configured.';
$string['appraisaldynamiclastwarning'] = 'Activating this appraisal will disable changes to all stages, pages and questions.
    It will make the appraisal available to those users and send out any messages you have configured.';
$string['appraisalupdated'] = 'Appraisal Updated';
$string['asroleappraiser'] = 'As Appraiser';
$string['asrolelearner'] = 'My Appraisals';
$string['asrolemanager'] = 'As Manager';
$string['asroleteamlead'] = 'As Manager\'s Manager';
$string['assigncurrentgroups'] = 'Assigned Groups';
$string['assigncurrentusers'] = 'Assigned Learners';
$string['assigncompletedusers'] = 'Learners who completed the appraisal';
$string['assigngroup'] = 'Assign Learner Group To Appraisal';
$string['assigngrouptypename'] = 'Assignment Type';
$string['assignincludechildren'] = 'Include Child Groups?';
$string['assignments'] = 'Assignments';
$string['assigned'] = 'Assigned';
$string['assignnumusers'] = 'Learners';
$string['assignnumusers_help'] = 'Shows the current number of users in the specific user group(s). This may differ from the number that were in the group when the group was originally assigned.';
$string['assignsourcename'] = 'Assigned Group';
$string['assignnumcompleted'] = '{$a->total} ({$a->completed} completed)';
$string['backtoappraisal'] = 'Back to appraisal';
$string['backtoappraisalx'] = '&lt; Back to appraisal: {$a}';
$string['cancelled'] = 'Cancelled';
$string['changesnotlive'] = 'Changes within the assigned groups have occurred but are not yet live. Appraisal assignments will be updated on the next cron run';
$string['changessaved'] = 'Changes saved';
$string['cleanuptask'] = 'Cleanup appraisals';
$string['close'] = 'Close';
$string['closeappraisal'] = 'Close appraisal';
$string['closed'] = 'Closed';
$string['closealertadminbody'] = '<p>The following staff, whose appraisals you are involved in, will no longer be able to complete this appraisal:</p>
        <p>{$a->staff}</p>
        <p>Message from the administrator follows:</p><p>{$a->alerttitle}</p><p>{$a->alertbody}</p>';
$string['closealertbody'] = 'Alert body';
$string['closealertbodydefault'] = '<p>An administrator has closed your appraisal "{$a->name}".</p>
        <p>You no longer need to complete this appraisal.</p>';
$string['closealerttitle'] = 'Alert title';
$string['closealerttitledefault'] = 'Appraisal "{$a->name}" has been closed by an administrator';
$string['closesendalert'] = 'Send alert to affected users';
$string['closeusersincomplete'] = '{$a} users have not yet completed this appraisal. Closing will prevent them continuing with their appraisal. This cannot be undone.';
$string['closedon'] = 'This appraisal was cancelled on {$a}';
$string['complete'] = 'Complete';
$string['completeby'] = 'Complete by';
$string['completebydate'] = 'Complete by <br>{$a}';
$string['completebystage_help'] = 'Leave the dates empty if you don\'t know them yet, but note that the appraisal can\'t be activate without them.';
$string['completed'] = 'Completed';
$string['completedon'] = 'This appraisal was completed on {$a}';
$string['completestage'] = 'Complete stage';
$string['configenableappraisals'] = 'This option will let you: Enable(show)/Disable Appraisal features from users on this site.

* If Show is chosen, all links, menus, tabs and option related to appraisals will be accessible.
* If Disable is chosen, appraisals will disappear from any menu on the site and will not be accessible.
';
$string['confirmactivateappraisal'] = 'Do you really want to activate this appraisal?';
$string['confirmactivatewarning'] = 'You can activate the appraisal without fixing these warnings, but users may encounter some issues. Do you really want to activate this appraisal?';
$string['confirmcloseappraisal'] = 'Do you really want to close this appraisal?';
$string['confirmdeleteappraisal'] = 'Do you really want to remove this appraisal?';
$string['confirmdeletegroup'] = 'Do you really want to remove the {$a->grouptype} "{$a->groupname}" from appraisal "{$a->appraisalname}"? This will close assignments for any users not currently assigned via other group assignments.';
$string['confirmdeletemessage'] = 'Do you really want to remove this message?';
$string['confirmdeletepage'] = 'Do you really want to remove this page?';
$string['confirmdeletestage'] = 'Do you really want to remove this stage?';
$string['confirmdeletestagewithredisplay'] = 'This stage has one or more redisplayed items linked to it. If you delete it, all linked redisplay items will also be deleted. Are you sure you want to delete this stage?';
$string['confirmdeletequestion'] = 'Do you really want to remove this question?';
$string['confirmdeleteitem'] = 'Are you sure you want to delete this item?';
$string['confirmdeleteitemwithredisplay'] = 'This item has one or more redisplay items linked to it. If you delete it, all linked redisplay items will also be deleted. Are you sure you want to delete this item?';
$string['content'] = 'Content';
$string['continue'] = 'Continue';
$string['createappraisal'] = 'Create appraisal';
$string['createappraisalheading'] = 'Create a new appraisal';
$string['createpageheading'] = 'Create a new page';
$string['currentstage'] = 'Current stage';
$string['dateend'] = 'End date';
$string['datestart'] = 'Start date';
$string['delete'] = 'Delete';
$string['deleteappraisals'] = 'Delete \'{$a}\' appraisal';
$string['deletepage'] = 'Delete \'{$a}\' page';
$string['deletestage'] = 'Delete \'{$a}\' stage';
$string['deletedappraisal'] = 'Appraisal deleted';
$string['deletedpage'] = 'Page deleted';
$string['deletedstage'] = 'Stage deleted';
$string['description'] = 'Description';
$string['description_help'] = 'When a appraisal description is created the information displays after appraisal name.';
$string['descriptionstage'] = 'Description';
$string['descriptionstage_help'] = 'When a description is created the information displays after appraisal stage name.';
$string['detailreport'] = 'Detail report';
$string['detailreportforx'] = '{$a} detail report: ';
$string['downloadnow'] = 'Download now';
$string['draft'] = 'Draft';
$string['editpageheading'] = 'Edit page';
$string['editstageheading'] = 'Edit stage';
$string['empty'] = 'Empty';
$string['emptyassignments'] = 'Due to be removed';
$string['enableappraisals'] = 'Enable Appraisals';
$string['example:appraisalname'] = 'Example appraisal';
$string['example:appraisaldescription'] = '<p>This is an example appraisal. You can use this as a starting point for building your own appraisal or just browse around to see how the functionality works.</p><p>If you don\'t need it, just delete it and create your own.</p>';
$string['example:scaleyesnoname'] = 'Yes or no';
$string['example:stage1name'] = 'Set Up';
$string['example:stage2name'] = 'Mid-Year Review';
$string['example:stage3name'] = 'End of Year Review';
$string['example:stage1description'] = 'Set Up stage. This stage happens at the start of the appraisal period.';
$string['example:stage2description'] = 'Mid-Year Review provides an opportunity to track progress and take corrective action if necessary';
$string['example:stage3description'] = 'The End of Year Review is the final stage in the appraisal.';
$string['example:stage1page1name'] = 'Goals';
$string['example:stage1page2name'] = 'Personal Development';
$string['example:stage1page3name'] = 'Competencies';
$string['example:stage1page4name'] = 'Summary';
$string['example:stage2page1name'] = 'Goals (Mid-Year)';
$string['example:stage2page2name'] = 'Competencies (Mid-Year)';
$string['example:stage2page3name'] = 'Summary (Mid-Year)';
$string['example:stage3page1name'] = 'Goals (End-Year)';
$string['example:stage3page2name'] = 'Competencies (End-Year)';
$string['example:stage3page3name'] = 'Summary (End-Year)';
$string['example:stage1page1quest1name'] = 'Review your goals';
$string['example:stage1page2quest1name'] = 'Enter personal development details';
$string['example:stage1page3quest1name'] = 'Review competencies';
$string['example:stage1page4quest1name'] = 'Has this been agreed between learner and manager?';
$string['example:stage2page1quest1name'] = 'Review your goals';
$string['example:stage2page1quest2name'] = 'Overall goal rating (Mid-year)';
$string['example:stage2page2quest1name'] = 'Review competencies';
$string['example:stage2page2quest2name'] = 'Overall competency rating (Mid-year)';
$string['example:stage2page3quest1name'] = 'Overall comments';
$string['example:stage3page1quest1name'] = 'Review your goals';
$string['example:stage3page1quest2name'] = 'Overall goal rating (End-year)';
$string['example:stage3page2quest1name'] = 'Review competencies';
$string['example:stage3page2quest2name'] = 'Overall competency rating (End-year)';
$string['example:stage3page3quest1name'] = 'Overall comments';
$string['error:cannotaccessappraisal'] = 'You do not have the capabilities required to access this appraisal';
$string['error:cannotassignjob'] = 'Invalid job assignment specified';
$string['error:appraisalisactive'] = 'Appraisal cannot be removed if it is active';
$string['error:appraisalnoteditable'] = 'Appraisal can not be edited while it is in an \'Active\' state';
$string['error:appraisalmustdraft'] = 'Parts of appraisal cannot be removed after appraisal activation';
$string['error:appraisalnotdraft'] = 'Appraisal must be in \'Draft\' or \'Closed\' state to be modified';
$string['error:attemptupdatestatic'] = 'Active appraisals cannot be updated, to enable dynamic appraisals go to the advanced features page';
$string['error:beforedisabled'] = 'This type of event cannot be predicted';
$string['error:cannotchangestatus'] = 'Current status {$a->oldstatus} cannot be changed to {$a->newstatus}';
$string['error:completebyinvalid'] = 'The complete by date must be in the future';
$string['error:cannotdelete'] = 'The item could not be deleted. Please make sure it still exists.';
$string['error:dateformat'] = 'Please enter a date in the format {$a}.';
$string['error:dialognotreeitemscoursefromplan'] = 'There are no courses in this plan';
$string['error:dialognotreeitemscompfromplan'] = 'There are no competencies in this plan';
$string['error:dialognotreeitemsevidencefromplan'] = 'There is no linked evidence in this plan';
$string['error:dialognotreeitemsgoals'] = 'No goals in this framework';
$string['error:dialognotreeitemsobjfromplan'] = 'There are no objectives in this plan';
$string['error:dialognotreeitemsprogfromplan'] = 'There are no programs in this plan';
$string['error:invalidgrouptype'] = 'Unrecognised group type';
$string['error:invalidquestiontype'] = 'Invalid question type, only numeric or custom rating questions can be aggregated';
$string['error:messagetitleyrequired'] = 'Message title is required';
$string['error:messagebodyrequired'] = 'Message body is required';
$string['error:nopermissions'] = 'You do not have permissions to perform that action';
$string['error:noquestionstoaggregate'] = 'No questions to aggregate were found';
$string['error:pagenotfound'] = 'Page not found';
$string['error:pagepermissions'] = 'You do not have permissions to view this page';
$string['error:redisplayoutoforder'] = 'Redisplay question must be placed on a page after the question it links to';
$string['error:redisplayrequired'] = 'At least one role must see the redisplayed question';
$string['error:stagenotfound'] = 'Stage not found';
$string['error:submitteddatainvalid'] = 'There were problems with the data you submitted';
$string['error:movestagenopages'] = 'The stage that the question is being moved to contains no pages';
$string['error:subjecthasnoappraisals'] = 'User has no appraisals';
$string['error:numberrequired'] = 'Number greater than zero is required';
$string['error:unknownbuttonclicked'] = 'Unknown button clicked';
$string['error:writerequired'] = 'At least one role must have write access';
$string['error:viewrequired'] = 'At least one role must have visibility access';
$string['error:rolemessage'] = 'At least one role should be selected to receive message';
$string['error:toomanyquestions'] = 'This appraisal contains too many questions. Please remove questions to make activation possible.';
$string['event'] = 'Event';
$string['eventactivation'] = 'Assignee gains access to the appraisal';
$string['eventafter'] = '{$a->delta} {$a->period} after event';
$string['eventbefore'] = '{$a->delta} {$a->period} before event';
$string['eventcreatedappraisal'] = 'Created Appraisal';
$string['eventcreatedpage'] = 'Created Appraisal Page';
$string['eventcreatedquestion'] = 'Created Appraisal Question';
$string['eventcreatedstage'] = 'Created Appraisal Stage';
$string['eventdeletedappraisal'] = 'Deleted Appraisal';
$string['eventdeletedpage'] = 'Deleted Appraisal Page';
$string['eventdeletedquestion'] = 'Deleted Appraisal Question';
$string['eventdeletedstage'] = 'Deleted Appraisal Stage';
$string['eventmessagetitle'] = 'Message title';
$string['eventmessagebody'] = 'Message body';
$string['eventmessageroletitle'] = '{$a} message title';
$string['eventmessagerolebody'] = '{$a} message body';
$string['eventrecipients'] = 'Recipients';
$string['eventrecipients_help'] = 'When multiple job assignments are enabled, recipients in roles other than learner are only determined once the learner has selected a job assignment in the appraisal. Messages scheduled to be sent to these recipients when the assignee gains access will be sent on job assignment selection.';
$string['eventsendroleall'] = 'Send same message to all roles';
$string['eventsendroleeach'] = 'Send different message for each role';
$string['eventsendstagecompleted'] = 'Only send to people if their stage is';
$string['eventstagecompleted'] = 'Appraisal Stage Completed';
$string['eventstageiscomplete'] = 'complete';
$string['eventstageisincomplete'] = 'incomplete';
$string['eventstage'] = '{$a} Stage';
$string['eventstagecomplete'] = 'Upon completion';
$string['eventstagedue'] = 'Complete by date';
$string['eventtimeafter'] = 'Send after';
$string['eventtimebefore'] = 'Send before';
$string['eventtimenowcron'] = 'Send immediately';
$string['eventtiming'] = 'Timing';
$string['eventtiming_help'] = '<p>This setting determines when the message will be sent out. There are three options:</p>

* **Send Immedately** - Primarily for a user activated event like appraisal activation or stage completion, the message will be triggered on the next cron after the event occurs.
* **Send Before** - Only for scheduled events like stage due dates, this will cause the message to be sent via the cron X days/weeks/months before the event is scheduled to happen. Other events can not use this timing since the system doesn\'t know when the event is going to happen.
* **Send After** - For all events scheduled or user activated, this will cause the message to be sent via the cron X days/weeks/months after the event occurs. This can be set to 0 days to send on the first cron run after the event occurs.';
$string['eventupdatedappraisal'] = 'Updated Appraisal';
$string['eventupdatedpage'] = 'Updated Appraisal Page';
$string['eventupdatedquestion'] = 'Updated Appraisal Question';
$string['eventupdatedstage'] = 'Updated Appraisal Stage';
$string['finished'] = 'Finished';
$string['hasredisplayitems'] = 'There are one or more redisplay items linked to this item';
$string['inactiveappraisals'] = 'Inactive appraisals';
$string['incomplete'] = 'Incomplete';
$string['inprogress'] = 'In progress';
$string['immediatecron'] = 'Immediate (via cron)';
$string['itemstoadd'] = 'Items to add';
$string['jobassignment'] = 'Job assignment linked to this appraisal';
$string['jobassignmentempty'] = 'Job assignment currently empty';
$string['jobassignmentselect'] = 'Select a job assignment to link to this appraisal';
$string['jobassignmentselected'] = 'Successfully linked job assignment to this appraisal';
$string['latestappraisal'] = 'Latest Appraisal';
$string['latestappraisals'] = 'Latest Appraisals';
$string['latestappraisalfor'] = 'Latest Appraisal for {$a}';
$string['latestappraisalsfor'] = 'Latest Appraisals for {$a}';
$string['learners'] = 'Learners';
$string['leavespace'] = 'Leave space on print-out to write comments';
$string['locked'] = 'Locked';
$string['locks'] = 'Lock stage after completion';
$string['locks_help'] = 'Locking a stage after completion means the user\'s own answers are no longer editable.';
$string['lockwhencompleted'] = 'Lock stage when completed';
$string['manageappraisals'] = 'Manage appraisals';
$string['managestage'] = 'Manage {$a}\'s content';
$string['messagecreate'] = 'Create Message';
$string['messagedeleted'] = 'Message deleted';
$string['messageedit'] = 'Edit Message';
$string['messages'] = 'Messages';
$string['messagesheading'] = 'Messages';
$string['messagetitle'] = 'Title';
$string['messageevent'] = 'Event';
$string['messagetiming'] = 'Timing';
$string['messagerecipients'] = 'Recipients';
$string['messagesaved'] = 'Message saved';
$string['messageplaceholders'] = 'Message placeholders';
$string['messageplaceholders_help'] = 'The following placeholders are available:

* [sitename]
  <br />The site name.
* [siteurl]
  <br /> The site URL.

* [appraisalname]
  <br /> The appraisal name.
* [appraisaldescription]
  <br /> The appraisal description.
* [expectedappraisalcompletiondate]
  <br /> The expected appraisal completion date.

* [listofstagenames]
  <br />List of all stage names.
* [currentstagename]
  <br />The users current stage name.
* [expectedstagecompletiondate]
  <br />The expected completion date for the users current stage name.
* [previousstagename]
  <br />The users previous stage name (defaults to \'No previous stage\').

* [userusername]
  <br />The appraisee\'s username.
* [userfirstname]
  <br />The appraisee\'s first name.
* [userlastname]
  <br />The appraisee\'s last name.
* [userfullname]
  <br />The appraisee\'s full name.

* [managerusername]
  <br /> The appraisee\'s managers username.
* [managerfirstname]
  <br />The appraisee\'s managers first name.
* [managerlastname]
  <br />The appraisee\'s managers last name.
* [managerfullname]
  <br />The appraisee\'s managers full name (defaults to \'Manager not known\').

* [managersmanagerusername]
  <br />The manager\'s manager username.
* [managersmanagerfirstname]
  <br />The manager\'s manager first name.
* [managersmanagerlastname]
  <br />The manager\'s manager last name.
* [managersmanagerfullname]
  <br />The manager\'s manager full name (defaults to \'Managers manager not known\').

* [appraiserusername]
  <br /> The appraiser\'s username.
* [appraiserfirstname]
  <br />The appraiser\'s first name.
* [appraiserlastname]
  <br />The appraiser\'s last name.
* [appraiserfullname]
  <br />The appraiser\'s full name (defaults to \'Appraiser not known\').';
$string['missingroles'] = 'Some assigned users are missing important role assignments or have not yet selected a job assignment for this appraisal.';
$string['missingrolesbelow'] = ' Users may not be able to complete the appraisal without these roles, they are listed below:';
$string['missingrolesinfo'] = 'Some assigned users are missing important role assignments or have not yet selected a job assignment for this appraisal. {$a}';
$string['missingroleslink'] = 'View full list of missing roles';
$string['missingrolesnone'] = 'There are no missing roles for the user assignments of this appraisal.';
$string['missingrolestitle'] = 'Appraisals Missing Roles - {$a}';
$string['myappraisals'] = 'My Appraisals';
$string['teamappraisals'] = 'Team\'s Appraisals';
$string['name'] = 'Name';
$string['name_help'] = 'This is the name that will appear at the top of your appraisal forms and reports.';
$string['namestage'] = 'Name';
$string['namestage_help'] = 'This is the name that will appear at the top of your appraisal stages.';
$string['next'] = 'Next';
$string['noappraisals'] = 'No appraisals have been created.';
$string['noappraisalsactive'] = 'No appraisals are active.';
$string['noappraisalsinactive'] = 'No appraisals are inactive.';
$string['noassignments'] = 'No current user assignments';
$string['nomessages'] = 'No messages have been added.';
$string['none'] = '-';
$string['noobjectives'] = 'No Objectives are available';
$string['nopagestoview'] = 'There are no pages available at this time.';
$string['nostages'] = 'No stages have been created.';
$string['ontarget'] = 'On target';
$string['options'] = 'Options';
$string['overdue'] = 'Overdue';
$string['overrideviewother'] = 'Override view other role\'s answers';
$string['overrideviewother_help'] = 'If **Override view other role\'s answers** is set then the role will see all other roles\'
answers, otherwise viewing answers of other roles is determined by the original question.';
$string['pagename'] = 'Page: {$a}';
$string['pagedefaultname'] = 'New Page';
$string['pageupdated'] = 'Page updated';
$string['participants'] = 'Participants:';
$string['performance'] = 'Performance';
$string['periodchoose'] = 'How much earlier/later?';
$string['perioddays'] = 'days';
$string['periodmonths'] = 'months';
$string['periodweeks'] = 'weeks';
$string['permissions'] = 'Permissions';
$string['preview'] = 'Preview';
$string['placeholders:default_previousstagename'] = 'No previous stage';
$string['placeholders:default_managerfullname'] = 'Manager not known';
$string['placeholders:default_teamleadfullname'] = 'Manager\'s manager not known';
$string['placeholders:default_appraiserfullname'] = 'Appraiser not known';
$string['previewdeprecated'] = 'Preview {$a->appraisal}:{$a->stage}:{$a->page} as {$a->role}';
$string['previewingappraisal'] = 'Previewing "{$a->appraisalname}" as {$a->rolename}';
$string['previewinfo'] = 'This window displays how the appraisal will appear to a user with the "{$a}" role, including which stages, pages and questions will be visible.';
$string['previewappraisal'] = 'Preview appraisal';
$string['previewappraisalas'] = 'Preview appraisal as:';
$string['previewusername'] = 'Preview User';
$string['print'] = 'Print';
$string['printnow'] = 'Print now';
$string['printyourappraisal'] = 'Print your appraisal';
$string['progresssaved'] = 'Progress saved';
$string['pluginname'] = 'Totara Appraisals';
$string['redisplay'] = 'Redisplay';
$string['redisplay_help'] = 'If **Redisplay** is set then visibility and editing of the redisplayed question is controlled by the
original question and (where applicable) stage locking settings, otherwise the question will not be redisplayed.';
$string['reportappraisals'] = 'Reports';
$string['required'] = 'Required';
$string['requirements'] = 'Requirements';
$string['role'] = 'Role';
$string['role_answer_roleappraiser'] = 'Appraiser\'s answer';
$string['role_answer_rolelearner'] = 'Learner\'s answer';
$string['role_answer_rolemanager'] = 'Manager\'s answer';
$string['role_answer_roleteamlead'] = 'Manager\'s Manager answer';
$string['role_answer_you'] = 'Your answer';
$string['roleaccessnotice'] = 'If a role cannot answer or view other\'s answers then the question is not shown to that role';
$string['roleadministrator'] = 'Administrator';
$string['roleappraiser'] = 'Appraiser';
$string['roleall'] = 'All';
$string['rolecompletedyou'] = 'You have completed this stage';
$string['rolecompletedyour'] = 'Your {$a} has completed this stage';
$string['rolecompleteduser'] = '{$a} has completed this stage';
$string['rolecompletedusers'] = '{$a->username}\'s {$a->rolename} has completed this stage';
$string['rolecompleteyou'] = 'You must complete this stage';
$string['rolecompleteyour'] = 'Your {$a} must complete this stage';
$string['rolecompletenotassigned'] = '{$a} not assigned at the time of completion. No action required.';
$string['rolecompleteuser'] = '{$a} must complete this stage';
$string['rolecompleteusers'] = '{$a->username}\'s {$a->rolename} must complete this stage';
$string['rolecurrentlyempty'] = 'Role currently empty';
$string['rolelearner'] = 'Learner';
$string['rolemanager'] = 'Manager';
$string['rolescananswer'] = 'Roles that can answer';
$string['rolescananswer_help'] = 'These are the roles that can answer at least one question on a page within this stage.';
$string['rolescanview'] = 'Roles that can view';
$string['rolescanview_help'] = 'These are the roles that can view at least one other person\'s answer but cannot answer any questions themselves.';
$string['rolesmissing'] = 'Warning: there are missing roles which may prevent this appraisal being completed.';
$string['roleteamlead'] = 'Manager\'s Manager';
$string['reports'] = 'Reports';
$string['savechanges'] = 'Save changes';
$string['savepdfsnapshot'] = 'Save PDF Snapshot';
$string['saveprogress'] = 'Save progress';
$string['sameaspreceding'] = 'Same as preceding question';
$string['selectappraisal'] = 'Select an appraisal';
$string['selectquestiontype'] = 'Select type of content...';
$string['selectquestiontype_notselected'] = 'You must select the question type you want to add.';
$string['sendscheduledmessagestask'] = 'Send scheduled appraisal messages';
$string['settings'] = 'Settings';
$string['appraisal_stage_completion'] = 'Stage completion';
$string['sectioninclude'] = 'Choose which sections to include';
$string['snapshotdialogtitle'] = 'Save PDF Snapshot';
$string['snapshotdone'] = 'A snapshot of your appraisal has been saved. You can view it by going to {$a->link}';
$string['snapshoterror'] = 'Error generating PDF snapshot';
$string['snapshotgeneration'] = 'Saving snapshot... Please wait.';
$string['snapshotname'] = 'Snapshot {$a->time}';
$string['stagecompleted'] = 'You have completed this stage';
$string['stage_due'] = 'Stage due';
$string['stageheader'] = 'Stage name';
$string['stageinitialpagesheader'] = 'Create pages for this stage';
$string['stageinitialpagetitles'] = 'Page names (optional)';
$string['stageinitialpagetitles_help'] = 'You can use this field to quickly create a set of pages for this stage by entering one page name per line. If you don\'t know the page names yet you can still create new pages or edit existing ones later.';
$string['stageupdated'] = 'Stage updated';
$string['stagesendingoption'] = 'Stage sending option';
$string['stagename'] = 'Stage: {$a}';
$string['stages'] = 'Stages';
$string['start'] = 'Start';
$string['status'] = 'Status';
$string['statusat'] = 'Status:';
$string['statusreport'] = 'Status report';
$string['statusreportforx'] = '{$a} status report: ';
$string['temporarypage'] = 'Temporary page';
$string['toomanyquestionswarning'] = 'The large number of questions in this appraisal may prevent your appraisal from being activated.';
$string['toomanyquestions'] = 'Too many questions';
$string['toomanyquestions_help'] = 'Limitations of your database system can lead to an error when you try to activate an appraisal with a large number of questions. The limitations depend on a combination of the type and total number of questions. To make sure this appraisal can be activated, please remove questions until this warning disappears.';
$string['unavailable'] = 'Unavailable';
$string['update'] = 'Update';
$string['updatenow'] = 'Update Now';
$string['userdataitemappraisal'] = 'As the learner';
$string['userdataitemappraisal_excluding_hidden_answers'] = 'As the learner, excluding hidden answers from other roles';
$string['userdataitemappraisal_including_hidden_answers'] = 'As the learner, including hidden answers from other roles';
$string['userdataitemparticipation'] = 'Participation in other users\' appraisals';
$string['userdataitemparticipation_history'] = 'Participation history';
$string['view'] = 'View';
$string['viewother'] = 'View other role\'s answers';
$string['viewreport'] = 'View report';
$string['viewstageheading'] = 'View stage';
$string['visibility'] = 'Visibility';
$string['questupdated'] = 'Page content updated';
$string['unrecognizedaction'] = 'Unrecognized action';
$string['updatelearnerassignmentstask'] = 'Update learner assignments to appraisals';
$string['xmoremissingroles'] = '{$a} more user(s) are also missing roles.';
$string['youareprintingxsappraisal'] = '<strong>{$a->rolename}\'s version of&nbsp;<a href="{$a->site}/user/view.php?id={$a->userid}">{$a->name}\'s</a> appraisal.</strong>';
$string['youareviewingxsappraisal'] = '<strong>You are viewing <a href="{$a->site}/user/view.php?id={$a->userid}">{$a->name}\'s</a> appraisal.</strong>';


// DEPRECATED OR UNUSED STRINGS
$string['appraisalclosedalertssent'] = 'Appraisal \'{$a}\' closed and alert have been sent';
$string['eventtimenow'] = 'Send immediately when event happens';
$string['immediate'] = 'Immediate';
$string['rolehaschanged'] = 'This role is due to change';
$string['myteamappraisals'] = 'My Team\'s Appraisals';
