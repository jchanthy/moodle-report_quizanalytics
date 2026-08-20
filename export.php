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
 * Export endpoint for Quiz Analytics report (Excel & CSV with custom field selection).
 *
 * @package    report_quizanalytics
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$id       = required_param('id', PARAM_INT);                 // Course ID.
$quizid   = required_param('quizid', PARAM_INT);             // Quiz ID.
$format   = optional_param('format', 'excel', PARAM_ALPHA);  // 'excel' or 'csv'.
$fields   = optional_param_array('fields', [], PARAM_ALPHA); // Selected fields.

require_sesskey();

$course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);
$context = context_course::instance($course->id);

require_login($course);
require_capability('report/quizanalytics:view', $context);

$quiz = $DB->get_record('quiz', ['id' => $quizid, 'course' => $course->id], '*', MUST_EXIST);

$analyzer = new \report_quizanalytics\quiz_analyzer($course->id);
$attempts = $analyzer->get_student_attempts($quizid);

// Define all possible field mappings.
$fieldmap = [
    'fullname'    => get_string('field_fullname', 'report_quizanalytics'),
    'firstname'   => get_string('field_firstname', 'report_quizanalytics'),
    'lastname'    => get_string('field_lastname', 'report_quizanalytics'),
    'email'       => get_string('field_email', 'report_quizanalytics'),
    'idnumber'    => get_string('field_idnumber', 'report_quizanalytics'),
    'department'  => get_string('field_department', 'report_quizanalytics'),
    'quizname'    => get_string('field_quizname', 'report_quizanalytics'),
    'attempt'     => get_string('field_attempt', 'report_quizanalytics'),
    'timestart'   => get_string('field_timestart', 'report_quizanalytics'),
    'timefinish'  => get_string('field_timefinish', 'report_quizanalytics'),
    'timetaken'   => get_string('field_timetaken', 'report_quizanalytics'),
    'grade'       => get_string('field_grade', 'report_quizanalytics'),
    'percentage'  => get_string('field_percentage', 'report_quizanalytics'),
    'status'      => get_string('field_status', 'report_quizanalytics'),
];

// If no fields selected, default to a standard set.
if (empty($fields)) {
    $fields = ['fullname', 'email', 'idnumber', 'quizname', 'attempt', 'timefinish', 'timetaken', 'grade', 'percentage', 'status'];
}

// Build headers.
$columns = [];
$headers = [];
foreach ($fields as $f) {
    if (isset($fieldmap[$f])) {
        $columns[] = $f;
        $headers[] = $fieldmap[$f];
    }
}

// Build row data.
$exportrows = [];
foreach ($attempts as $att) {
    $row = [];
    foreach ($columns as $col) {
        $val = $att[$col] ?? '';
        if ($col === 'percentage') {
            $val = $val . '%';
        }
        $row[] = $val;
    }
    $exportrows[] = $row;
}

// File name generation.
$cleanname = clean_filename($quiz->name . '_analytics_' . date('Ymd_His'));

// Export using Moodle Dataformat API if available.
if ($format === 'excel' && class_exists('\core\dataformat')) {
    \core\dataformat::download_data(
        $cleanname,
        'excel',
        $headers,
        $exportrows
    );
    exit;
} else if ($format === 'csv' && class_exists('\core\dataformat')) {
    \core\dataformat::download_data(
        $cleanname,
        'csv',
        $headers,
        $exportrows
    );
    exit;
}

// Direct Fallback for CSV output.
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $cleanname . '.csv"');

$output = fopen('php://output', 'w');
// Add UTF-8 BOM for Excel CSV compatibility.
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
fputcsv($output, $headers);

foreach ($exportrows as $row) {
    fputcsv($output, $row);
}

fclose($output);
exit;
