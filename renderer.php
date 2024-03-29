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
 * AlgebraKiT question renderer class.
 *
 * @package qtype_algebrakit
 * @copyright 2009 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 require_once($CFG->dirroot . '/question/type/algebrakit/constants.php');

/**
 * Generates the html for an Algebrakit question.
 *
 * @copyright 2009 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_algebrakit_renderer extends qtype_renderer {

    protected $session;
    protected $solutionMode = false;
    protected $reviewMode = false;

    // True if the question was already started before and we are returning to it
    protected $continued = false;

    protected $questionText;

    public function formulation_and_controls(question_attempt $qa,
            question_display_options $options) {

        $question = $qa->get_question();

        $this->questionsummary = $question->questiontext;

        $qtData = $qa->get_last_qt_data();
        if (in_array('solutionMode', $qtData)) {
            $this->solutionMode = true;
        } 

        $this->reviewMode = $this->is_review($qa);

        $this->session = $question->session;
        $this->continued = $question->continued;

        $result = $this->showQuestion();


        return $result;
    }

    /**
     * Returns an error message if session has an error, otherwise null;
     */
    private static function getSessionError($session) {
        if (!isset($session)) {
            return "No session";
        }
        if (!is_array($session)) {
            if (isset($session->error)) {
                return $session->error;
            }
            if (isset($session->msg)) {
                return $session->msg;
            }
        }
        if (is_array($session) && isset($session[0])) {
            if (isset($session[0]->error)) {
                return $session[0]->error;
            }
            if (isset($session[0]->msg)) {
                return $session[0]->msg;
            }
        }
        else if (is_array($session) && !isset($session[0])) {
            return "No session";
        }
        return null;
    }

    public function showQuestion() {
        global $CFG, $AK_CDN_URL, $PAGE;

        $html = "";
        if (!empty($this->questionsummary)) {
            $html .= $this->questionsummary;
        }

        $err = qtype_algebrakit_renderer::getSessionError($this->session);
        if ($err != null) {
            $html .= "Failed to generate session for exercise: <br/> $err";
        } else {
            for ($ii = 0; $ii < count($this->session); $ii++) {
                $ex = $this->session[$ii];
                if($ex->success) {
                    //for each of the requested nr of instances...
                    for($nn=0; $nn < count($ex->sessions); $nn++) {
                        // insert a tag for this interaction.
                        // use the html returned with the session data for better performance
                        // (initialization data is inlined)
                        $html .= '<br><br>';
                        $sessionId = $ex->sessions[$nn]->sessionId;
                        $attributes = array(
                            'session-id' => $sessionId,
                        );
                        if ($this->solutionMode || $this->reviewMode || $this->continued) {
                            if ($this->solutionMode) {
                                $attributes['solution-mode'] = true;
                            }
                            if ($this->reviewMode) {
                                $attributes['review-mode'] = true;
                            }
                            $html .= html_writer::empty_tag('akit-exercise', $attributes);
                        }
                        else {
                            $html .= $ex->sessions[$nn]->html;
                        }
                    }
                } else if ($ex != null) {
                    $html .= "Failed to generate session for exercise.";
                }
            } 

            $PAGE->requires->js_call_amd('qtype_algebrakit/question', 'init', [$AK_CDN_URL]);
        }
        return $html;
    }

    public function is_review(question_attempt $qa) {
        return !$qa->get_state()->is_active();
    }
}
