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

namespace report_quizanalytics;

defined('MOODLE_INTERNAL') || die();

use stdClass;

/**
 * Quiz Analyzer service for report_quizanalytics.
 *
 * @package    report_quizanalytics
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_analyzer {

    /** @var int Course ID */
    protected $courseid;

    /**
     * Constructor.
     *
     * @param int $courseid
     */
    public function __construct(int $courseid) {
        $this->courseid = $courseid;
    }

    /**
     * Retrieve all quizzes in the course with attempt counts.
     *
     * @return array
     */
    public function get_course_quizzes(): array {
        global $DB;

        $sql = "SELECT q.id, q.name, q.grade, q.sumgrades,
                       COUNT(qa.id) AS totalattempts,
                       COUNT(DISTINCT qa.userid) AS totalstudents
                  FROM {quiz} q
             LEFT JOIN {quiz_attempts} qa ON qa.quiz = q.id AND qa.state = 'finished'
                 WHERE q.course = :courseid
              GROUP BY q.id, q.name, q.grade, q.sumgrades
              ORDER BY q.name ASC";

        return $DB->get_records_sql($sql, ['courseid' => $this->courseid]);
    }

    /**
     * Get aggregate overview for all quizzes combined in the course.
     *
     * @return stdClass
     */
    public function get_course_overview(): stdClass {
        global $DB;

        $overview = new stdClass();
        $overview->totalquizzes = $DB->count_records('quiz', ['course' => $this->courseid]);

        $sql = "SELECT COUNT(qa.id) AS totalattempts,
                       COUNT(DISTINCT qa.userid) AS totalstudents,
                       AVG(qg.grade) AS avggrade
                  FROM {quiz} q
                  JOIN {quiz_attempts} qa ON qa.quiz = q.id AND qa.state = 'finished'
             LEFT JOIN {quiz_grades} qg ON qg.quiz = q.id AND qg.userid = qa.userid
                 WHERE q.course = :courseid";

        $record = $DB->get_record_sql($sql, ['courseid' => $this->courseid]);
        $overview->totalattempts = (int)($record->totalattempts ?? 0);
        $overview->totalstudents = (int)($record->totalstudents ?? 0);
        $overview->avggrade = $record->avggrade !== null ? round((float)$record->avggrade, 2) : 0;

        return $overview;
    }

    /**
     * Calculate summary metrics for a specific quiz.
     *
     * @param int $quizid
     * @return stdClass
     */
    public function get_quiz_summary(int $quizid): stdClass {
        global $DB;

        $quiz = $DB->get_record('quiz', ['id' => $quizid, 'course' => $this->courseid], '*', MUST_EXIST);
        $summary = new stdClass();
        $summary->id = $quiz->id;
        $summary->name = $quiz->name;
        $summary->maxgrade = (float)$quiz->grade;

        // Query finished attempts.
        $sql = "SELECT qa.id, qa.userid, qa.sumgrades, qa.timestart, qa.timefinish,
                       (qa.timefinish - qa.timestart) AS duration,
                       qg.grade AS finalgrade
                  FROM {quiz_attempts} qa
             LEFT JOIN {quiz_grades} qg ON qg.quiz = qa.quiz AND qg.userid = qa.userid
                 WHERE qa.quiz = :quizid AND qa.state = 'finished'
              ORDER BY qa.timefinish ASC";

        $attempts = $DB->get_records_sql($sql, ['quizid' => $quizid]);

        $summary->totalattempts = count($attempts);
        $distinctusers = [];
        $grades = [];
        $durations = [];
        $passcount = 0;
        $failcount = 0;

        $passgrade = $summary->maxgrade * 0.50; // Standard 50% pass mark threshold.

        foreach ($attempts as $att) {
            $distinctusers[$att->userid] = true;
            $grade = $att->finalgrade !== null ? (float)$att->finalgrade : (float)($att->sumgrades ?? 0);
            $grades[] = $grade;

            if ($att->duration > 0) {
                $durations[] = (int)$att->duration;
            }

            if ($grade >= $passgrade) {
                $passcount++;
            } else {
                $failcount++;
            }
        }

        $summary->distinctstudents = count($distinctusers);
        $summary->passcount = $passcount;
        $summary->failcount = $failcount;
        $summary->passrate = $summary->totalattempts > 0 ? round(($passcount / $summary->totalattempts) * 100, 1) : 0;

        if (!empty($grades)) {
            $summary->avggrade = round(array_sum($grades) / count($grades), 2);
            $summary->highestgrade = round(max($grades), 2);
            $summary->lowestgrade = round(min($grades), 2);

            sort($grades);
            $count = count($grades);
            $mid = floor($count / 2);
            $summary->mediangrade = ($count % 2) ? $grades[$mid] : round(($grades[$mid - 1] + $grades[$mid]) / 2, 2);
        } else {
            $summary->avggrade = 0;
            $summary->highestgrade = 0;
            $summary->lowestgrade = 0;
            $summary->mediangrade = 0;
        }

        if (!empty($durations)) {
            $avgseconds = (int)(array_sum($durations) / count($durations));
            $summary->avgduration = $this->format_duration($avgseconds);
        } else {
            $summary->avgduration = 'N/A';
        }

        return $summary;
    }

    /**
     * Calculate score distribution buckets for a quiz.
     *
     * @param int $quizid
     * @return array
     */
    public function get_score_distribution(int $quizid): array {
        global $DB;

        $quiz = $DB->get_record('quiz', ['id' => $quizid, 'course' => $this->courseid], '*', MUST_EXIST);
        $maxgrade = (float)$quiz->grade > 0 ? (float)$quiz->grade : 100.0;

        $sql = "SELECT qa.id, COALESCE(qg.grade, qa.sumgrades, 0) AS grade
                  FROM {quiz_attempts} qa
             LEFT JOIN {quiz_grades} qg ON qg.quiz = qa.quiz AND qg.userid = qa.userid
                 WHERE qa.quiz = :quizid AND qa.state = 'finished'";

        $attempts = $DB->get_records_sql($sql, ['quizid' => $quizid]);
        $total = count($attempts);

        $buckets = [
            'excellent' => ['label' => get_string('bucket_excellent', 'report_quizanalytics'), 'count' => 0, 'color' => '#198754'],
            'good'      => ['label' => get_string('bucket_good', 'report_quizanalytics'),      'count' => 0, 'color' => '#0d6efd'],
            'average'   => ['label' => get_string('bucket_average', 'report_quizanalytics'),   'count' => 0, 'color' => '#ffc107'],
            'poor'      => ['label' => get_string('bucket_poor', 'report_quizanalytics'),      'count' => 0, 'color' => '#dc3545'],
        ];

        foreach ($attempts as $att) {
            $pct = ((float)$att->grade / $maxgrade) * 100;
            if ($pct >= 85) {
                $buckets['excellent']['count']++;
            } else if ($pct >= 70) {
                $buckets['good']['count']++;
            } else if ($pct >= 50) {
                $buckets['average']['count']++;
            } else {
                $buckets['poor']['count']++;
            }
        }

        $result = [];
        foreach ($buckets as $key => $bucket) {
            $pct = $total > 0 ? round(($bucket['count'] / $total) * 100, 1) : 0;
            $result[] = [
                'key'        => $key,
                'label'      => $bucket['label'],
                'count'      => $bucket['count'],
                'percentage' => $pct,
                'color'      => $bucket['color'],
            ];
        }

        return $result;
    }

    /**
     * Retrieve question difficulty analysis for a quiz.
     *
     * @param int $quizid
     * @return array
     */
    public function get_question_insights(int $quizid): array {
        global $DB;

        $sql = "SELECT qs.id AS slotid, qs.slot, qs.maxmark, q.id AS questionid, q.name, q.qtype,
                       COALESCE(AVG(qa.fraction), 0) AS avgfraction,
                       COUNT(qa.id) AS attemptcount
                  FROM {quiz_slots} qs
                  JOIN {question} q ON q.id = qs.questionid
             LEFT JOIN {question_usages} qu ON qu.component = 'mod_quiz'
             LEFT JOIN {question_attempts} qa ON qa.questionusageid = qu.id AND qa.slot = qs.slot
                 WHERE qs.quizid = :quizid
              GROUP BY qs.id, qs.slot, qs.maxmark, q.id, q.name, q.qtype
              ORDER BY qs.slot ASC";

        try {
            $questions = $DB->get_records_sql($sql, ['quizid' => $quizid]);
        } catch (\Exception $e) {
            return [];
        }

        $insights = [];
        foreach ($questions as $q) {
            $facility = round((float)$q->avgfraction * 100, 1);
            if ($facility < 40) {
                $difficulty = get_string('difficulty_hard', 'report_quizanalytics');
                $badgeclass = 'badge bg-danger text-white';
            } else if ($facility < 70) {
                $difficulty = get_string('difficulty_medium', 'report_quizanalytics');
                $badgeclass = 'badge bg-warning text-dark';
            } else {
                $difficulty = get_string('difficulty_easy', 'report_quizanalytics');
                $badgeclass = 'badge bg-success text-white';
            }

            $insights[] = [
                'slot'       => $q->slot,
                'name'       => format_string($q->name),
                'qtype'      => $q->qtype,
                'maxmark'    => round((float)$q->maxmark, 2),
                'facility'   => $facility,
                'difficulty' => $difficulty,
                'badgeclass' => $badgeclass,
            ];
        }

        return $insights;
    }

    /**
     * Fetch detailed student attempts for UI display and export.
     *
     * @param int $quizid
     * @param int $limitfrom
     * @param int $limitnum
     * @return array
     */
    public function get_student_attempts(int $quizid, int $limitfrom = 0, int $limitnum = 0): array {
        global $DB;

        $quiz = $DB->get_record('quiz', ['id' => $quizid, 'course' => $this->courseid], '*', MUST_EXIST);
        $maxgrade = (float)$quiz->grade > 0 ? (float)$quiz->grade : 100.0;
        $passgrade = $maxgrade * 0.50;

        $userfields = \core_user\fields::for_name()->with_userpic()->with_identity(\context_course::instance($this->courseid));
        $userfieldsselect = $userfields->get_sql('u', false, '', '', false)->selects;

        $sql = "SELECT qa.id AS attemptid, qa.attempt, qa.state, qa.timestart, qa.timefinish,
                       qa.sumgrades, COALESCE(qg.grade, qa.sumgrades, 0) AS finalgrade,
                       q.name AS quizname,
                       {$userfieldsselect}, u.id AS userid, u.department, u.idnumber, u.email
                  FROM {quiz_attempts} qa
                  JOIN {quiz} q ON q.id = qa.quiz
                  JOIN {user} u ON u.id = qa.userid
             LEFT JOIN {quiz_grades} qg ON qg.quiz = qa.quiz AND qg.userid = qa.userid
                 WHERE qa.quiz = :quizid
              ORDER BY qa.timefinish DESC, qa.attempt DESC";

        $records = $DB->get_records_sql($sql, ['quizid' => $quizid], $limitfrom, $limitnum);

        $results = [];
        foreach ($records as $row) {
            $grade = (float)$row->finalgrade;
            $percentage = round(($grade / $maxgrade) * 100, 1);
            $duration = ($row->timefinish > $row->timestart) ? ($row->timefinish - $row->timestart) : 0;
            $ispass = ($grade >= $passgrade);

            $results[] = [
                'attemptid'   => $row->attemptid,
                'userid'      => $row->userid,
                'fullname'    => fullname($row),
                'firstname'   => $row->firstname,
                'lastname'    => $row->lastname,
                'email'       => $row->email,
                'idnumber'    => $row->idnumber ?? '',
                'department'  => $row->department ?? '',
                'quizname'    => $row->quizname,
                'attempt'     => $row->attempt,
                'state'       => $row->state,
                'statelabel'  => get_string('state_' . $row->state, 'report_quizanalytics'),
                'timestart'   => $row->timestart ? userdate($row->timestart, get_string('strftimedatetime', 'langconfig')) : '-',
                'timefinish'  => $row->timefinish ? userdate($row->timefinish, get_string('strftimedatetime', 'langconfig')) : '-',
                'timetaken'   => $this->format_duration($duration),
                'grade'       => round($grade, 2),
                'maxgrade'    => round($maxgrade, 2),
                'percentage'  => $percentage,
                'status'      => $ispass ? get_string('passed', 'report_quizanalytics') : get_string('failed', 'report_quizanalytics'),
                'ispass'      => $ispass,
            ];
        }

        return $results;
    }

    /**
     * Helper to format duration seconds to human-readable string.
     *
     * @param int $seconds
     * @return string
     */
    protected function format_duration(int $seconds): string {
        if ($seconds <= 0) {
            return '-';
        }
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%dh %02dm %02ds', $hours, $minutes, $secs);
        }
        return sprintf('%02dm %02ds', $minutes, $secs);
    }
}
