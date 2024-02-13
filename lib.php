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

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json", "x-api-key: $apiKey"));
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $dataString);
    $json_response = curl_exec($curl);

    return json_decode($json_response);
}

/**
 * Create a session for the given exercise.
 * @return The session object
 */
function qtype_algebrakit_createSession($exerciseId, $jsonBlob = null, $assessment_mode = false) {
    $apiKey = get_config('qtype_algebrakit', 'apikey');

    if (empty($apiKey)) {
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
        'exercises' => $exList,
        'assessmentMode' => $assessment_mode
    );
    return akitPost('/session/create', $data, $apiKey);
}

function qtype_algebrakit_getScore($sessionId) {
    $apiKey = get_config('qtype_algebrakit', 'apikey');
    if(empty($apiKey)) return null;

    $data = array(
        'sessionId' => $sessionId,
        'lockSession' => true
    );
    return akitPost('/session/score', $data, $apiKey);
}

function qtype_algebrakit_getAudiences($baseOnly = true) {
    $apiKey = get_config('qtype_algebrakit', 'apikey');
    if(empty($apiKey)) return null;

    $result = array();
    try{
        $audience_list = akitPost('/audiences/get', array(), $apiKey);
        foreach ($audience_list as $key => $value) {
            if($baseOnly && $value->audienceType == 'course_specific') {
                continue;
            }

            $lang_list = $value->languages;
            if($lang_list==null || count($lang_list)==0) {
                continue;
            }

            array_push($result, $value);
        }
    } catch (Exception $e) {return null;}

    return $result;
}
/**
 * Returns a list of i18n codes for the regions of the audiences defined in Algebrakit
 * e.g. ['uk', 'es', 'fr', 'fr-BE']
 * Only base regions are included (not publisher-specific)
 */
function qtype_algebrakit_getAudienceRegions() {
    $region_set = array();

    $audience_list = qtype_algebrakit_getAudiences();
    if(empty($audience_list)) return null;

    foreach ($audience_list as $key => $value) {

        $lang_list = $value->languages;
        $lang = $lang_list[0];
        if($lang=='en' || $lang=='uk') {
            continue;
        }

        $region_set[$lang] = 1;
    }

    $sorted = array_keys($region_set);
    sort($sorted);
    array_unshift($sorted, 'uk');

    $result = array();
    for($ii=0; $ii<count($sorted); $ii++) {
        $result[$sorted[$ii]] = $sorted[$ii];
    }
    return $result;
}

/** 
 * Get all audiences for this region
 * e.g. [{ "name": "English Higher Secondary", "id": "uk_KS5" }, { "name": "English Lower Secondary", "id": "uk_KS3" }]
 */
function qtype_algebrakit_getAudiencesForRegion($audience_region) {
    $audience_list = qtype_algebrakit_getAudiences();
    if(empty($audience_list)) return null;

    usort($audience_list, function ($a, $b) {
        if($a->order != $b->order) {
            return $a->order - $b->order;
        }
        $aname = $a->name? $a->name : $a->audienceID;
        $bname = $b->name? $b->name : $b->audienceID;

        return strcasecmp($aname, $bname);
    });

    $result = array();

    foreach ($audience_list as $key => $value) {
        $lang_list = $value->languages;
        $lang = $lang_list[0];

        if($lang == $audience_region) {
            $item = array(
                'name' => $value->name? $value->name : $value->audienceID,
                'id' => $value->audienceID
            );
            
            array_push($result, $item);
        }
    }
    return $result;
}
