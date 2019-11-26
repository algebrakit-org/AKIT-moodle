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


/**
 * Generates the output for short answer questions.
 *
 * @copyright 2009 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_algebrakit_renderer extends qtype_renderer {

    protected $session;
    protected $solutionMode = false;
    protected $continued = false;

    public function formulation_and_controls(question_attempt $qa,
            question_display_options $options) {

        $question = $qa->get_question();

        $qtData = $qa->get_last_qt_data();
        if (in_array('solutionMode', $qtData)) {
            $this->solutionMode = true;
        } 

        $this->session = $question->session;
        $this->continued = $question->continued;

        $result = $this->continueSession();

        $currentanswer = $qa->get_last_qt_var('answer');
        $ansInputname = $qa->get_qt_field_name('answer');

        $anwerAttributes = array(
            'type' => 'hidden',
            'name' => $ansInputname,
            'value' => $currentanswer,
            'id' => $ansInputname,
        );

        $sessionInputname = $qa->get_qt_field_name('_session');
        $sessionJSON = json_encode($this->session);

        $sessionAttributes = array(
            'type' => 'hidden',
            'name' => $sessionInputname,
            'value' => $sessionJSON,
            'id' => $sessionInputname,
        );

        $result .= html_writer::empty_tag('input', $sessionAttributes);

        return $result;
    }

    public function continueSession() {
        global $CFG;
        $html = "";
        if (!isset($this->session) || (isset($this->session->success) && $this->session->success === false)) {
            $html .= "Failed to generate session for exercise: <br/>";
            if (isset($this->session) && isset($this->session->error)) {
                $html .= $this->session->error;
            }
        }
        else {
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
                        $anwerAttributes = array(
                            'session-id' => $sessionId,
                        );
                        if ($this->solutionMode || $this->continued) {
                            if ($this->solutionMode) {
                                $anwerAttributes['solution-mode'] = true;
                            }
                            $html .= html_writer::empty_tag('akit-exercise', $anwerAttributes);
                        }
                        else {
                            $html .= $ex->sessions[$nn]->html;
                        }
                        
                    }
                } else if ($ex != null) {
                    $html .= "Failed to generate session for exercise.";
                }
            } 
            $script = file_get_contents($CFG->dirroot . '/question/type/algebrakit/widgetLoader.js');
            $html .= "<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/katex@0.10.1/dist/katex.min.css'></script>";
            $html .= "<script src='https://cdn.jsdelivr.net/npm/katex@0.10.1/dist/katex.min.js'></script>";
            $html .= "
            <script>
                $script
            </script>
            ";
        }
        return $html;
    }
}
