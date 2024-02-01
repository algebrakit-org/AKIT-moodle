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
 * Defines the editing form for the numerical question type.
 *
 * @package    qtype
 * @subpackage numerical
 * @copyright  2007 Jamie Pratt me@jamiep.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/edit_question_form.php');
require_once($CFG->dirroot . '/question/type/algebrakit/questiontype.php');
require_once($CFG->dirroot . '/question/type/algebrakit/constants.php');

/**
 * numerical editing form definition.
 *
 * @copyright  2007 Jamie Pratt me@jamiep.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_algebrakit_edit_form extends question_edit_form
{
    /** @var int we always show at least this many sets of unit fields. */
    const UNITS_MIN_REPEATS = 1;
    const UNITS_TO_ADD = 2;

    protected $ap = null;
    protected $useEditor = true;  // Use exercise editor (true) or exercise ID (false)

    protected function definition_inner($mform)
    {
        $this->useEditor = get_config('qtype_algebrakit', 'enable_embedded_editor');

        // the stem is not mandatory, generally set in the Algebrakit exercise
        $i = array_search("questiontext", $mform->_required);
        array_splice($mform->_required, $i, 1);
        $mform->_rules['questiontext'] = array();

        if($this->useEditor){
            $this->add_exercise_editor($mform);
        } else {
            $this->add_exerciseID_options($mform);
        }

    }

    /**
     * Add the input fields for referring to an exercise in the CMS
     * @param object $mform the form being built.
     */
    protected function add_exerciseID_options($mform)
    {
        $mform->addElement(
            'header',
            'akit_exercise',
            get_string('akit_exerciseref', 'qtype_algebrakit')
        );

        $mform->addElement(
            'text',
            'exercise_id',
            get_string('exerciseid', 'qtype_algebrakit')
        );

        $mform->setType('exercise_id', PARAM_NOTAGS);
    }

    protected function add_exercise_editor($mform)
    {
        global $CFG, $AK_MOODLE_WIDGET_URL, $AK_CDN_URL, $AK_PROXY_URL;

        $mform->addElement(
            'header',
            'akit_exercise',
            get_string('akit_exerciseeditor', 'qtype_algebrakit')
        );

        $mform->addElement(
            'hidden',
            'exercise_in_json'
        );
        $mform->setType('exercise_in_json', PARAM_RAW);

        $html = "
                           
        <!--- Global object AlgebraKIT will be the front end API of AlgebraKiT and is used for configuration -->
        <script>

            AlgebraKIT = {
                config: {
                    secureProxy: {
                        url: '{$AK_PROXY_URL}'
                    }
                }
            }
        </script>
        
        <script src=\"{$AK_CDN_URL}/akit-widgets.js\"></script>
                           
        <script type=\"module\" src='{$AK_MOODLE_WIDGET_URL}/moodle-widget.esm.js'></script>
        <script src=\"https://cdn.jsdelivr.net/npm/quill@2.0.0-beta.0/dist/quill.min.js\"></script>
        <moodle-algebrakit-exercise-loader></moodle-algebrakit-exercise-loader>
        ";
        //add html to the form
        $mform->addElement('html', $html);
    }

    public function validation($data, $files)
    {
        $errors = parent::validation($data, $files);

        $json = $data['exercise_in_json'];
        $exerciseId = $data['exercise_id'];

        if (isset($json) && strlen($json)>5) {
            //remove errors for exercise_id
            $data['exercise_id'] = '';
        }  else if (isset($exerciseId) && strlen(trim($exerciseId)>4)) {
            $data['exercise_in_json'] = '';         
        } else {
            $errors['exerciseId'] = get_string('exerciseIdRequired', 'qtype_algebrakit');
        }
        return $errors;
    }

    public function data_preprocessing($question)
    {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_options($question);
        return $question;
    }

    public function data_preprocessing_options($question)
    {
        if (!isset($question->options)) {
            return $question;
        }

        $opt = $question->options;
        $question->question_id = $opt->question_id;

        if (isset($opt->exercise_id)) {
            $question->exercise_id = $opt->exercise_id;
        } else {
            $question->exercise_id = null;
        }
        if (isset($opt->exercise_in_json)) {
            $question->exercise_in_json = $opt->exercise_in_json;
        } else {
            $question->exercise_in_json = null;
        }

        return $question;
    }

    public function qtype()
    {
        return 'algebrakit';
    }
}
