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
 * Library functions and callbacks for report_quizanalytics.
 *
 * @package    report_quizanalytics
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Extend course navigation with link to Quiz Analytics report.
 *
 * @param navigation_node $navigation The course navigation node.
 * @param stdClass $course The course object.
 * @param context_course $context The course context.
 */
function report_quizanalytics_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/quizanalytics:view', $context)) {
        $url = new moodle_url('/report/quizanalytics/index.php', ['id' => $course->id]);
        $reportnode = navigation_node::create(
            get_string('pluginname', 'report_quizanalytics'),
            $url,
            navigation_node::TYPE_CUSTOM,
            null,
            'quizanalytics',
            new pix_icon('i/report', '')
        );

        if ($coursereports = $navigation->find('coursereports', navigation_node::TYPE_CONTAINER)) {
            $coursereports->add_node($reportnode);
        } else {
            $navigation->add_node($reportnode);
        }
    }
}
