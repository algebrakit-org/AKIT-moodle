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
 * @subpackage algebrakit
 * @copyright  20024 Algebrakit BV, the Netherlands
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/questionbase.php');
require_once($CFG->dirroot . '/question/type/algebrakit/lib.php');

class SessionResponse
{
    public $success;
    public $msg;
    public $sessions;
}

/**
 * Represents an Algebrakit question.
 *
 */
class qtype_algebrakit_question extends question_graded_automatically
{
    /** @var array of question_answer. */
    public $answers = array();

    // either exercise ID or exercise spec in JSON is required
    public $exercise_id;
    public $exercise_in_json;
    public $question_id;

    // number of marks for this exercuse (all interactions combined)
    public $marksTotal;

    protected $apiKey = null;
    public $session;

    // True if the question was already started before and we are returning to it
    // This value is used in renderer.php to improve performance
    public $continued = false;

    public function __construct()
    {
        $this->apiKey = get_config('qtype_algebrakit', 'apikey');
    }

    public function get_expected_data()
    {
        return array('_session' => PARAM_RAW_TRIMMED);
    }

    /**
     * Start a new question.
     * @param question_attempt_step $step The step to start the attempt for.
     * @param int $variant The variant to start the attempt for.
     */
    public function start_attempt(question_attempt_step $step, $variant)
    {
        $this->marksTotal = (int) $step->get_qt_var('_marksTotal');
        
        $sessionVar = $step->get_qt_var('_session');
        if($sessionVar!=null) {
            $this->session = json_decode($step->get_qt_var('_session'));
        }

        if ($this->session == null) {
            $this->session = $this->createSession($this->exercise_id, $this->exercise_in_json);

            $marks = 0;
            if (isset($this->session) && is_array($this->session)) {
                foreach ($this->session as $sess) {
                    if ($sess->success == false) {
                        continue;
                    }
                    foreach ($sess->sessions as $s) {
                        $marks += $s->marksTotal;
                    }
                }
            }
            $this->marksTotal = $marks;

            $step->set_qt_var('_session', json_encode($this->session));
            $step->set_qt_var('_marksTotal', json_encode($this->marksTotal));
        } else {
            error_log('AK ERROR question was already started');
        }
    }

    /**
     * Get the state information of a question that was already started before
     */
    public function apply_attempt_state(question_attempt_step $step)
    {
        $this->session = json_decode($step->get_qt_var('_session'));
        $this->marksTotal = (int) $step->get_qt_var('_marksTotal');
        $this->continued = true;

        parent::apply_attempt_state($step);
    }

    /**
     * Create a session for the given exercise.
     * @return The session object
     */
    public function createSession($exerciseId, $jsonBlob = null)
    {
        if (empty($this->apiKey)) {
            $sess = new SessionResponse();
            $sess->success = false;
            $sess->msg = 'No API Key is set. Go to the settings for the Algebrakit plugin to enter an API Key.';
            $sess->sessions = [];
            $this->session = [
                $sess
            ];
            return;
        }
        //check jsonblob length > 5
        if ($jsonBlob && strlen($jsonBlob)>5) {
            
            //convert string to json
            $jsonBlob = json_decode($jsonBlob);

            $exList = [
                0 => [
                    'exerciseSpec' => $jsonBlob,
                    'version' => 'latest'
                ]
            ];
        } else {
            $exList = [
                0 => [
                    'exerciseId' => $exerciseId,
                    'version' => 'latest'
                ]
            ];
        }

        $data = array(
            'apiVersion' => 2,
            'exercises' => $exList
        );
        return akitPost('/session/create', $data, $this->apiKey);
    }

    public function summarise_response(array $response)
    {
        if (isset($response['_session'])) {
            return "View the individual questions to see the responses";
        } else {
            return null;
        }
    }

    public function un_summarise_response(string $summary)
    {
        if (!empty($summary)) {
            return ['_session' => $summary];
        } else {
            return [];
        }
    }

    public function is_gradable_response(array $response)
    {
        return $this->marksTotal > 0;
    }

    public function is_complete_response(array $response)
    {
        return true;
    }

    public function get_validation_error(array $response)
    {
        return '';
    }

    public function is_same_response(array $prevresponse, array $newresponse)
    {
        return false;
    }

    public function get_correct_response()
    {
        return ["solutionMode"];
    }

    public function get_right_answer_summary()
    {
        return "Open the exercise to view the correct answer";
    }

    public function grade_response(array $response)
    {
        if($this->marksTotal==0) {
            // non-scorable question
            return 1;
        }

        $sessionId = $this->session[0]->sessions[0]->sessionId;
        $data = array(
            'sessionId' => $sessionId
        );
        $scoreObj = akitPost('/session/score', $data, $this->apiKey);
        if (isset($scoreObj->success) && $scoreObj->success === false) {
            $fraction = 0;
        } else if($scoreObj->scoring->marksTotal==0) {
            // should never happen
            $fraction = 1;
        } else {
            $fraction = $scoreObj->scoring->marksEarned / $scoreObj->scoring->marksTotal;
        }

        return array($fraction, question_state::graded_state_for_fraction($fraction));
    }

    public function check_file_access(
        $qa,
        $options,
        $component,
        $filearea,
        $args,
        $forcedownload
    ) {
        // TODO.
        if ($component == 'question' && $filearea == 'hint') {
            return $this->check_hint_file_access($qa, $options, $args);

        } else {
            return parent::check_file_access(
                $qa,
                $options,
                $component,
                $filearea,
                $args,
                $forcedownload
            );
        }
    }
}