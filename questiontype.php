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
 * Question type class for the numerical question type.
 *
 * @package    qtype
 * @subpackage numerical
 * @copyright  1999 onwards Martin Dougiamas {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/type/algebrakit/question.php');


/**
 * The Algebrakit question type class.
 *
 * @copyright  2024 Algebrakit BV
 */
class qtype_algebrakit extends question_type
{

    public function move_files($questionid, $oldcontextid, $newcontextid)
    {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_hints($questionid, $oldcontextid, $newcontextid);
    }

    protected function delete_files($questionid, $contextid)
    {
        parent::delete_files($questionid, $contextid);
        $this->delete_files_in_hints($questionid, $contextid);
    }

    public function save_question_options($fromform)
    {
        global $DB;
        $context = $fromform->context;

        parent::save_question_options($fromform);

        $options = $DB->get_record('question_algebrakit', array('question_id' => $fromform->id));

        if (!$options) {

            $options = new stdClass();
            $options->exercise_id = $fromform->exercise_id;
            $options->exercise_in_json = $fromform->exercise_in_json;
            $options->assessment_mode = $fromform->assessment_mode;
            $options->question_id = $fromform->id;

            $DB->insert_record('question_algebrakit', $options);

        } else {

            $options->exercise_id = $fromform->exercise_id;
            $options->exercise_in_json = $fromform->exercise_in_json;
            $options->assessment_mode = $fromform->assessment_mode;
            $DB->update_record('question_algebrakit', $options);
        }
    }

    public function get_question_options($question)
    {
        global $DB;

        parent::get_question_options($question);

        $question->options = $DB->get_record(
            'question_algebrakit',
            array('question_id' => $question->id),
            '*',
            MUST_EXIST
        );

        return true;
    }



    protected function initialise_question_instance(question_definition $question, $questiondata)
    {
        parent::initialise_question_instance($question, $questiondata);
        $question->exercise_id = $questiondata->options->exercise_id;
        $question->question_id = $questiondata->options->question_id;
        $question->assessment_mode = $questiondata->options->assessment_mode;
        if (isset($questiondata->options->exercise_in_json)) {
            $question->exercise_in_json = $questiondata->options->exercise_in_json;
            $question->exercise_id = null;
        } else if (isset($questiondata->options->exercise_id)) {
            $question->exercise_id = $questiondata->options->exercise_id;
            $question->exercise_in_json = null;
        } 
    }

    public function get_random_guess_score($questiondata)
    {
        // TODO.
        return 0;
    }

    public function get_possible_responses($questiondata)
    {
        // TODO.
        return array();
    }

}
