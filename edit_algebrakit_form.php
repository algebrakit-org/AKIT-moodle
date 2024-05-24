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
 * Input form for an Algebrakit question.
 *
 * @copyright  2024 algebrakit.com
 */
class qtype_algebrakit_edit_form extends question_edit_form
{
    protected $useEditor = true;  // Use exercise editor (true) or exercise ID (false)
    protected $audienceSpec;      // JSON definition of the audiences in the editor
    protected $blacklist;         // JSON array of question types that should not be visible in the editor

    protected function definition_inner($mform)
    {
        global $CFG, $AK_CDN_URL, $AK_PROXY_URL, $PAGE;


        // add the checkbox for assessment mode to the general section
        $mform = $this->_form;
        $mform->addElement('advcheckbox', 'assessment_mode', get_string('assessment_mode', 'qtype_algebrakit'), get_string('assessment_mode', 'qtype_algebrakit'));
        $mform->addHelpButton('assessment_mode', 'assessment_mode', 'qtype_algebrakit');
        $mform->setType('assessment_mode', PARAM_BOOL);

        // the stem is not mandatory, generally set in the Algebrakit exercise. It will be hidden by javascript.
        $i = array_search("questiontext", $mform->_required);
        array_splice($mform->_required, $i, 1);
        $mform->_rules['questiontext'] = array();

        $mform->addElement(
            'header',
            'akit_exercise',
            get_string('akit_exerciseheader', 'qtype_algebrakit')
        );

        // add hidden input field that contains the exercise in JSON format. This input field serves as
        // the bridge between the editor and the Moodle question type.
        $mform->addElement(
            'hidden',
            'exercise_in_json'
        );
        $mform->setType('exercise_in_json', PARAM_RAW);

        $mform->addElement(
            'text',
            'exercise_id',
            get_string('exerciseid', 'qtype_algebrakit')
        );
        $mform->setType('exercise_id', PARAM_NOTAGS);

        // the Algebrakit editor will be inserted here, if requird
        $html = '<div class="qtype_algebrakit-editor-container" data-action="qtype_algebrakit/editor-container_div"></div>';
        $mform->addElement('html', $html);

        // Get setting to use editor or exercise ID.
        // Note that some old questions might deviate. E.g. the editor is enabled in the settings
        // but the question uses an exercise ID.
        $this->useEditor = get_config('qtype_algebrakit', 'enable_embedded_editor');

        $audience_region = get_config('qtype_algebrakit', 'audience_region');
        if(empty($audience_region)) $audience_region = 'uk';
        $this->audienceSpec = json_encode(qtype_algebrakit_getAudiencesForRegion($audience_region));
        $this->blacklist = '["NUMBER_LINE", "STAT_SINGLE_VIEW", "STAT_MULTI_VIEW","STATISTICS"]';


        $PAGE->requires->js_call_amd('qtype_algebrakit/editor', 'init', [$AK_CDN_URL, $AK_PROXY_URL,$this->useEditor,$this->audienceSpec,$this->blacklist]);
        // $this->add_exercise_editor($mform);
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

        $question->assessment_mode = isset($opt->assessment_mode) 
            && (strcmp($opt->assessment_mode,'1') == 0 || $opt->assessment_mode == true);

        return $question;
    }

    public function qtype()
    {
        return 'algebrakit';
    }
}
