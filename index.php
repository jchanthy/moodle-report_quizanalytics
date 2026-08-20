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
 * Main entry point for Quiz & Assessment Analytics report.
 *
 * @package    report_quizanalytics
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT); // Course ID.
$quizid = optional_param('quizid', 0, PARAM_INT); // Selected Quiz ID.

$course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);
$context = context_course::instance($course->id);

require_login($course);
require_capability('report/quizanalytics:view', $context);

// Trigger course report viewed event.
$event = \core\event\course_report_viewed::create([
    'context' => $context,
    'other' => [
        'report' => 'quizanalytics',
    ],
]);
$event->trigger();

// Setup page.
$url = new moodle_url('/report/quizanalytics/index.php', ['id' => $id]);
if ($quizid > 0) {
    $url->param('quizid', $quizid);
}

$PAGE->set_url($url);
$PAGE->set_pagelayout('report');
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'report_quizanalytics') . ': ' . $course->fullname);
$PAGE->set_heading($course->fullname);

// Render output.
$renderer = $PAGE->get_renderer('report_quizanalytics');
$renderable = new \report_quizanalytics\output\main_page($course->id, $quizid);

echo $renderer->header();
echo $renderer->heading(get_string('pluginname', 'report_quizanalytics'));
echo $renderer->render_main_page($renderable);
echo $renderer->footer();
