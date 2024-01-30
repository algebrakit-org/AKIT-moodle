<?php
/**
 * Serve question type files
 *
 * @since      Moodle 2.0
 * @package    qtype_algebrakit
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/algebrakit/constants.php');

/**
 * Checks file access for numerical questions.
 *
 * @package  qtype_algebrakit
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool
 */
function qtype_algebrakit_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array())
{
    global $CFG;
    require_once($CFG->libdir . '/questionlib.php');
    question_pluginfile($course, $context, 'qtype_algebrakit', $filearea, $args, $forcedownload, $options);
}

function akitPost($url, $data, $apiKey)
{
    global $AK_API_URL;
    
    $url = $AK_API_URL . $url;
    $dataString = json_encode($data);
    error_log("" . $url . "" . $dataString);

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json", "x-api-key: $apiKey"));
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $dataString);
    $json_response = curl_exec($curl);

    error_log("" . $json_response);
    return json_decode($json_response);
}


// TODO algebrakit logo
// AlgebraKiT 
// Preview
// view derivation