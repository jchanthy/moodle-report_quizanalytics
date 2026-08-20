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

namespace report_quizanalytics\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use stdClass;
use moodle_url;
use report_quizanalytics\quiz_analyzer;

/**
 * Renderable & Templatable main page for Quiz Analytics report.
 *
 * @package    report_quizanalytics
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class main_page implements renderable, templatable {

    /** @var int Course ID */
    protected $courseid;

    /** @var int Selected Quiz ID (0 for all) */
    protected $quizid;

    /** @var quiz_analyzer */
    protected $analyzer;

    /**
     * Constructor.
     *
     * @param int $courseid
     * @param int $quizid
     */
    public function __construct(int $courseid, int $quizid = 0) {
        $this->courseid = $courseid;
        $this->quizid = $quizid;
        $this->analyzer = new quiz_analyzer($courseid);
    }

    /**
     * Export data for Mustache template.
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output): stdClass {
        global $CFG;
        $data = new stdClass();
        $data->courseid = $this->courseid;
        $data->refreshedat = userdate(time(), get_string('strftimedatetime', 'langconfig'));

        // Retrieve quizzes.
        $quizzes = $this->analyzer->get_course_quizzes();
        $data->hasquizzes = !empty($quizzes);

        // Build quiz selector options.
        $quizoptions = [];
        foreach ($quizzes as $q) {
            $quizoptions[] = [
                'id'       => $q->id,
                'name'     => format_string($q->name),
                'selected' => ($this->quizid == $q->id),
                'attempts' => $q->totalattempts,
            ];
        }
        $data->quizoptions = $quizoptions;

        // If no specific quiz selected and quizzes exist, default to the first quiz.
        if ($this->quizid == 0 && !empty($quizzes)) {
            $first = reset($quizzes);
            $this->quizid = (int)$first->id;
            // Update selected in options.
            foreach ($data->quizoptions as &$opt) {
                if ($opt['id'] == $this->quizid) {
                    $opt['selected'] = true;
                }
            }
        }

        $data->selectedquizid = $this->quizid;
        $data->exporturl = (new moodle_url('/report/quizanalytics/export.php', [
            'id'     => $this->courseid,
            'quizid' => $this->quizid,
        ]))->out(false);

        if ($this->quizid > 0) {
            $summary = $this->analyzer->get_quiz_summary($this->quizid);
            $data->quizname = format_string($summary->name);
            $data->summary = $summary;
            $data->hasattempts = ($summary->totalattempts > 0);

            // Distribution
            $distribution = $this->analyzer->get_score_distribution($this->quizid);
            $data->distribution = $distribution;
            $data->distribution_json = json_encode($distribution);

            // Question Insights
            $questions = $this->analyzer->get_question_insights($this->quizid);
            $data->hasquestions = !empty($questions);
            $data->questions = $questions;

            // Student Attempts
            $attempts = $this->analyzer->get_student_attempts($this->quizid);
            $data->attempts = $attempts;
        }

        return $data;
    }
}


