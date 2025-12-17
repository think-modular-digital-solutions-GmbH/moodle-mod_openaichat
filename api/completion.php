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

use mod_openaichat\openaichat;
use mod_openaichat\completion;
use mod_openaichat\completion\assistant;
use mod_openaichat\completion\chat;

define('AJAX_SCRIPT', true);

require_once('../../../config.php');

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
$modid = clean_param($body['modId'], PARAM_INT, true);
$message = clean_param($body['message'], PARAM_NOTAGS);
$history = clean_param_array($body['history'], PARAM_NOTAGS, true);
$threadid = clean_param($body['threadId'], PARAM_NOTAGS, true);
$instance = $DB->get_record('openaichat', ['id' => $modid], '*', MUST_EXIST);

// Set up context.
$cm = get_coursemodule_from_instance(
    'openaichat',
    $modid,
    0,
    false,
    MUST_EXIST
);
$context = context_module::instance($cm->id);
$PAGE->set_context($context);

// Fetch module settings.
$modsettings = [];
$settings = [
    'sourceoftruth',
    'prompt',
    'username',
    'assistantname',
    'apikey',
    'model',
    'advanced',
    'assistant',
];
foreach ($settings as $setting) {
    if ($instance && property_exists($instance, $setting)) {
        $modsettings[$setting] = $instance->$setting;
    } else {
        $modsettings[$setting] = "";
    }
}

// Get mod settings.
$model = null;
if (get_config('mod_openaichat', 'allowinstancesettings') === "1") {
    $model = $instance->model;
    $apitype = $instance->type;
}
if (!$model) {
    $model = get_config('mod_openaichat', 'model');
    $apitype = get_config('mod_openaichat', 'type');
}
$modsettings['modid'] = $modid;

switch ($apitype) {
    case 'chat':
        $classname = \mod_openaichat\completion\chat::class;
        break;

    case 'assistant':
        $classname = \mod_openaichat\completion\assistant::class;
        break;
}

$completion = new $classname(...[$model, $message, $history, $modsettings, $threadid]);

$response = $completion->create_completion($PAGE->context);

$response["message"] = format_text($response["message"], FORMAT_MARKDOWN, ['context' => $context]);
$response = json_encode($response);

echo $response;
