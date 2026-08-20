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
 * Language strings for report_quizanalytics.
 *
 * @package    report_quizanalytics
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Quiz & Assessment Analytics';
$string['quizanalytics:view'] = 'View quiz and assessment analytics report';

// General UI
$string['selectquiz'] = 'Select Quiz';
$string['allquizzes'] = 'All Quizzes Overview';
$string['noquizzesfound'] = 'No quizzes found in this course.';
$string['noattemptsfound'] = 'No student attempts have been submitted for this quiz yet.';
$string['courseoverview'] = 'Course Overview';
$string['quizoverview'] = 'Quiz Overview: {$a}';
$string['refreshedat'] = 'Data calculated at: {$a}';

// Metrics Cards
$string['totalquizzes'] = 'Total Quizzes';
$string['totalattempts'] = 'Total Attempts';
$string['avggrade'] = 'Average Grade';
$string['passrate'] = 'Passing Rate';
$string['highestgrade'] = 'Highest Score';
$string['lowestgrade'] = 'Lowest Score';
$string['mediangrade'] = 'Median Grade';
$string['avgduration'] = 'Avg. Time Spent';
$string['passed'] = 'Passed';
$string['failed'] = 'Failed';

// Score Distribution
$string['scoredistribution'] = 'Score Distribution';
$string['scoredistribution_desc'] = 'Breakdown of student scores across performance tiers.';
$string['bucket_excellent'] = '85% - 100% (Excellent)';
$string['bucket_good'] = '70% - 84% (Good)';
$string['bucket_average'] = '50% - 69% (Average)';
$string['bucket_poor'] = 'Below 50% (Needs Support)';

// Question Insights
$string['questioninsights'] = 'Question Difficulty & Analysis';
$string['questioninsights_desc'] = 'Identifies questions that students found most challenging.';
$string['question'] = 'Question';
$string['questiontext'] = 'Question Prompt';
$string['questiontype'] = 'Type';
$string['facilityindex'] = 'Pass Rate (% Correct)';
$string['difficulty_hard'] = 'High Difficulty';
$string['difficulty_medium'] = 'Moderate Difficulty';
$string['difficulty_easy'] = 'Low Difficulty';

// Student Attempts Table
$string['studentperformance'] = 'Student Performance & Attempts';
$string['studentname'] = 'Student Name';
$string['email'] = 'Email';
$string['idnumber'] = 'ID Number';
$string['department'] = 'Department';
$string['groups'] = 'Groups';
$string['attemptnumber'] = 'Attempt';
$string['attemptstate'] = 'Status';
$string['timestart'] = 'Started';
$string['timefinish'] = 'Completed';
$string['timetaken'] = 'Duration';
$string['grade'] = 'Grade';
$string['percentage'] = 'Percentage';
$string['status'] = 'Result';
$string['state_finished'] = 'Finished';
$string['state_inprogress'] = 'In Progress';
$string['state_abandoned'] = 'Never Submitted';

// Export & Custom Fields
$string['exportreport'] = 'Export Report';
$string['exportformat'] = 'Export Format';
$string['format_excel'] = 'Microsoft Excel (.xlsx)';
$string['format_csv'] = 'CSV File (.csv)';
$string['choosefields'] = 'Select Fields to Export';
$string['selectall'] = 'Select All';
$string['deselectall'] = 'Deselect All';
$string['download'] = 'Download File';
$string['cancel'] = 'Cancel';

// Field labels for export
$string['field_fullname'] = 'Full Name';
$string['field_firstname'] = 'First Name';
$string['field_lastname'] = 'Last Name';
$string['field_email'] = 'Email Address';
$string['field_idnumber'] = 'ID Number';
$string['field_department'] = 'Department';
$string['field_groups'] = 'Course Groups';
$string['field_quizname'] = 'Quiz Name';
$string['field_attempt'] = 'Attempt #';
$string['field_state'] = 'Attempt State';
$string['field_timestart'] = 'Time Started';
$string['field_timefinish'] = 'Time Completed';
$string['field_timetaken'] = 'Duration';
$string['field_grade'] = 'Final Grade';
$string['field_maxgrade'] = 'Max Grade';
$string['field_percentage'] = 'Percentage (%)';
$string['field_status'] = 'Pass/Fail Status';
$string['privacy:metadata'] = 'The Quiz & Assessment Analytics report plugin does not store any personal data. It only reads and displays assessment records already in Moodle.';
$string['eventreportviewed'] = 'Quiz analytics report viewed';

// Search & Filter
$string['searchstudents'] = 'Search students';
$string['searchplaceholder'] = 'Search by student name, email, or ID...';
$string['filterstatus'] = 'Filter by result';
$string['allstatuses'] = 'All Results (Pass & Fail)';
$string['onlypassed'] = 'Passed Only';
$string['onlyfailed'] = 'Failed Only';
$string['clearfilter'] = 'Clear';
$string['showingrecords'] = 'Showing <strong id="visible-count">{$a}</strong> records';
$string['nomatchingrecords'] = 'No matching student attempts found.';

