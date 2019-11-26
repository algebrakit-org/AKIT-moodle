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
 * AlgebraKiT question definition class.
 *
 * @package    qtype
 * @subpackage numerical
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/questionbase.php');
require_once($CFG->dirroot . '/question/type/algebrakit/lib.php');

/**
 * Represents a numerical question.
 *
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_algebrakit_question extends question_graded_automatically {
    /** @var array of question_answer. */
    public $answers = array();

    public $exercise_id;
    public $major_version;
    public $question_id;

    public $session;
    public $continued = false;

    protected $apiKey;

    /** @var qtype_algebrakit_answer_processor */
    public $ap;

    public function __construct() {
        $this->apiKey = get_config('qtype_algebrakit', 'apikey');
    }

    public function get_expected_data() {
        return array();
    }

    public function start_attempt(question_attempt_step $step, $variant) {
        $this->session = json_decode($step->get_qt_var('_session'));

        if ($this->session !== null) {
            //Create exercise tag and continue
            $this->continued = true;
        }
        else {
            $this->createSession($this->exercise_id, $this->major_version);
            $step->set_qt_var('_session', json_encode($this->session));
        }
    }

    public function apply_attempt_state(question_attempt_step $step) {
        $this->session = json_decode($step->get_qt_var('_session'));
        if ($this->session != null) {
            $this->continued = true;
        }
    }

    public function createSession($exerciseId, $majorVersion) {
        $exList = [
            0 => [
                'exerciseId' => $exerciseId,
                'version' => intval($majorVersion) ? intval($majorVersion) : $majorVersion
            ]
        ];
        $data = array(
            'apiVersion' => 2,
            'exercises' => $exList
        );
        $this->session = akitPost('/session/create', $data, $this->apiKey);
    }

    public function summarise_response(array $response) {
        return array();
    }

    public function un_summarise_response(string $summary) {
        return array();
    }

    public function is_gradable_response(array $response) {
        return true;
    }

    public function is_complete_response(array $response) {
        return true;
    }

    public function get_validation_error(array $response) {
        return '';
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        return false;
    }

    public function get_correct_response() {
        return ["solutionMode"];
    }

    public function get_right_answer_summary() {
        return "";
    }

    /**
     * Get an answer that contains the feedback and fraction that should be
     * awarded for this response.
     * @param number $value the numerical value of a response.
     * @param number $multiplier for the unit the student gave, if any. When no
     *      unit was given, or an unrecognised unit was given, $multiplier will be null.
     * @return question_answer the matching answer.
     */
    public function get_matching_answer($value, $multiplier) {
        return null;
    }

    public function grade_response(array $response) {
       return array();
    }

    public function classify_response(array $response) {
        return array();
    }

    public function check_file_access($qa, $options, $component, $filearea,
            $args, $forcedownload) {
        // TODO.
        if ($component == 'question' && $filearea == 'hint') {
            return $this->check_hint_file_access($qa, $options, $args);

        } else {
            return parent::check_file_access($qa, $options, $component, $filearea,
                    $args, $forcedownload);
        }
    }
}