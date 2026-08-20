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
 * Library functions and navigation callbacks for report_quizanalytics.
 *
 * @package    report_quizanalytics
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Extends the course navigation to add Quiz & Assessment Analytics report.
 *
 * @param navigation_node $navigation The navigation node to extend.
 * @param stdClass $course The course object.
 * @param context $context The course context.
 */
function report_quizanalytics_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/quizanalytics:view', $context)) {
        $url = new moodle_url('/report/quizanalytics/index.php', ['id' => $course->id]);
        $navigation->add(
            get_string('pluginname', 'report_quizanalytics'),
            $url,
            navigation_node::TYPE_SETTING,
            null,
            'quizanalytics',
            new pix_icon('i/report', '')
        );
    }
}
