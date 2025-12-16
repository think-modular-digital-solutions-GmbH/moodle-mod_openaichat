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
 * API endpoint for retrieving thread history
 *
 * @package    mod_openaichat
 * @copyright  2024 think modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/mod/openaichat/lib.php');

if (get_config('mod_openai_chat', 'restrictusage') !== "0") {
    require_login();
}

global $DB;

$modid = required_param('modId', PARAM_INT);
$threadid = required_param('threadid', PARAM_NOTAGS);
$instance = $DB->get_record('openaichat', ['id' => $modid], '*', MUST_EXIST);
$apikey = $instance->apikey;

$curl = new \curl();
$curl->setopt([
    'CURLOPT_HTTPHEADER' => [
        'Authorization: Bearer ' . $apikey,
        'Content-Type: application/json',
        'OpenAI-Beta: assistants=v2',
    ],
]);

$response = $curl->get("https://api.openai.com/v1/threads/$threadid/messages");
$response = json_decode($response);

if (property_exists($response, 'error')) {
    throw new \Exception($response->error->message);
}

$apiresponse = [];
$messages = array_reverse($response->data);

foreach ($messages as $message) {
    array_push($apiresponse, [
        "id" => $message->id,
        "role" => $message->role,
        "message" => $message->content[0]->text->value,
    ]);
}

$apiresponse = json_encode($apiresponse);
echo $apiresponse;
