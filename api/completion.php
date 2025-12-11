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
 * API endpoint for retrieving GPT completion
 *
 * @package    mod_openaichat
 * @copyright  2024 think modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_openaichat;

use mod_openaichat\openaichat;

require_once('../../../config.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/mod/openaichat/lib.php');
require_once($CFG->dirroot . '/mod/openaichat/classes/completion.php');
require_once($CFG->dirroot . '/mod/openaichat/classes/completion/chat.php');

global $DB, $PAGE;

// Enforce login if usage is restricted.
if (get_config('mod_openaichat', 'restrictusage') !== "0") {
    require_login();
}

// Only allow POST requests.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: $CFG->wwwroot");
    die();
}

$body = json_decode(file_get_contents('php://input'), true);
$message = clean_param($body['message'], PARAM_NOTAGS);
$history = clean_param_array($body['history'], PARAM_NOTAGS, true);
$modid = clean_param($body['modId'], PARAM_INT, true);
$threadid = clean_param($body['threadId'], PARAM_NOTAGS, true);
$instance = $DB->get_record('openaichat', ['id' => $modid], '*', MUST_EXIST);

// Fetch module settings.
$modsettings = [];
$settings = [
    'sourceoftruth',
    'prompt',
    'username',
    'assistantname',
    'apikey',
    'model',
    'temperature',
    'maxlength',
    'topp',
    'frequency',
    'presence',
    'assistant',
];
foreach ($settings as $setting) {
    if ($instance && property_exists($instance, $setting)) {
        $modsettings[$setting] = $instance->$setting;
    } else {
        $modsettings[$setting] = "";
    }
}

$modsettings['modid'] = $modid;
$engineclass;
$model = $instance->model;
$apitype = $instance->type;

if ($apitype === 'assistant') {
    $engineclass = '\mod_openaichat\completion\assistant';
} else {
    $engines = openaichat::get_ai_models()['types'];
    if (get_config('mod_openaichat', 'allowinstancesettings') === "1" && $model) {
        $model = $model;
    }
    if (!$model) {
        $model = 'gpt-3.5-turbo';
    }
    $engineclass = '\mod_openaichat\completion\\' . $engines[$model];
}
$completion = new $engineclass(...[$model, $message, $history, $modsettings, $threadid]);
$response = $completion->create_completion($PAGE->context);

$response["message"] = format_text($response["message"], FORMAT_MARKDOWN, ['context' => $context]);
$response = json_encode($response);

echo $response;
