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
 * Module class
 *
 * @package    mod_openaichat
 * @copyright  2024 think modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_openaichat;

use mod_openaichat\form\termsacceptform;
use core\output\notification;
use moodle_url;
use stdClass;

/**
 * Module class
 *
 * @package    openaichat
 */
class openaichat {

    const DEFAULT_MODELS = "gpt-5.2
gpt-5.1
gpt-5
gpt-5-mini
gpt-5-nano
gpt-4.1
gpt-4.1-mini
gpt-4.1-nano
gpt-4o
gpt-4o-mini
o3
o3-mini
o4-mini";

    /**
     * Make the API call to OpenAI.
     *
     * @param string $url The API endpoint URL.
     * @param string $modid The module instance ID to get specific API key.
     * @param array|null $data The data to send in the request body (for POST requests).
     * @param array $additionalheaders Additional headers to include in the request.
     * @return object The decoded JSON response from the API.
     */
    public static function api_call($url, $modid, $data = null, $additionalheaders = []) {
        $apikey = self::get_api_key($modid);
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apikey,
        ];
        $headers = array_merge($headers, $additionalheaders);

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        if ($data !== null) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response);
    }

    /**
     * Redirec to to terms of use if not accepted yet.
     */
    public static function termsofuse() {
        global $PAGE, $USER, $DB;

        $modid = $PAGE->cm->instance;
        $userid = $USER->id;

        $rowtermsaccepted = $DB->get_record('openaichat_usertermsofuse', ['modid' => $modid, 'userid' => $userid]);
        if ($rowtermsaccepted == false || $rowtermsaccepted->termsofuseaccepted == 0) {
            $form = new termsacceptform();
            if ($form->is_submitted()) {
                $data = $form->get_data();
                $termsacceptedtime = time();
                $termsaccepted = isset($data->termsaccept) ? 1 : 0;
                $redirecturl = isset($data->termsaccept)
                        ? new moodle_url('/mod/openaichat/view.php', ['id' => $PAGE->cm->id])
                        : new moodle_url('/course/view.php', ['id' => $PAGE->course->id]);

                if ($rowtermsaccepted) {
                    $DB->update_record('openaichat_usertermsofuse', [
                        'id' => $rowtermsaccepted->id,
                        'termsofuseaccepted' => $termsaccepted,
                        'termsofuseacceptedtime' => $termsacceptedtime,
                    ]);
                    redirect($redirecturl);
                } else {
                    $DB->insert_record('openaichat_usertermsofuse', [
                        'modid' => $modid,
                        'userid' => $userid,
                        'termsofuseaccepted' => $termsaccepted,
                        'termsofuseacceptedtime' => $termsacceptedtime,
                    ]);
                    redirect($redirecturl);
                }
            }
            return $form->render();
        }
    }

    /**
     * Render the OpenAI chat module content.
     */
    public static function render() {
        $c = self::get_content();
        return '<div class="mod_openaichat"><div class="alert alert-warning"><p>' . get_string('disclaimer', 'mod_openaichat') . '</p></div><p id="remaining-questions"></p>' . $c->text . $c->footer . '</div>';
    }

    /**
     * This is for site level settings.
     */
    public static function get_type_to_display() {
        $type = get_config('mod_openaichat', 'type');
        if ($type) {
            return $type;
        }

        return 'chat';
    }

    /**
     * Fetch assistants from OpenAI API and return as an array.
     *
     * @param int|null $modid The module instance ID to get specific API key.
     * @return array Associative array of assistant IDs and names.
     */
    public static function fetch_assistants($modid = null) {
        // API endpoint to fetch assistants.
        $url = 'https://api.openai.com/v1/assistants?order=desc';
        $additionalheaders = ['OpenAI-Beta: assistants=v2'];
        $response = self::api_call($url, $modid, null, $additionalheaders);

        // Check for errors in the response.
        if (isset($response->error)) {

            // Show error.
            if (isset($_GET['testconnection'])) {
                $error = get_string('connection:error', 'mod_openaichat', $response->error->message);
                \core\notification::add($error, notification::NOTIFY_ERROR);
            }
            return [];
        }

        // Show success.
        if (isset($_GET['testconnection'])) {
            $error = get_string('connection:success', 'mod_openaichat');
            \core\notification::add($error, notification::NOTIFY_SUCCESS);
        }

        // Process and return assistants.
        $assistants = [];
        if (property_exists($response, 'data') && is_array($response->data)) {
            foreach ($response->data as $assistant) {
                $name = isset($assistant->name) ? $assistant->name : $assistant->id;
                $assistants[$assistant->id] = $assistant->name;
            }
        }
        return $assistants;
    }

    /**
     * Get available AI models and their types.
     *
     * @return array Associative array containing models and their types.
     */
    public static function get_ai_models() {
        $modelsconfig = get_config('mod_openaichat', 'models');
        if (empty($modelsconfig)) {
            $modelsconfig = self::DEFAULT_MODELS;
        }

        $lines = explode("\n", trim($modelsconfig));
        $models = [];
        foreach ($lines as $line) {
            $line = trim($line);
            $models[$line] = $line;
        }
        return $models;
    }

    /**
     * Check if user has questions left based on question limit.
     *
     * @param int $modid The module instance ID.
     * @param int $userid The user ID.
     * @return bool True if user has questions left, false otherwise.
     */
    public static function user_has_questions_left($modid, $userid) {
        global $DB;

        $instance = $DB->get_record('openaichat', ['id' => $modid], '*', MUST_EXIST);
        if ($instance->questionlimit == 0) {
            return true;
        }

        $counter = $DB->get_record('openaichat_userlog', ['modid' => $modid, 'userid' => $userid])->questioncounter;

        return ($counter < $instance->questionlimit);
    }

    /**
     * Get the content for the OpenAI chat module.
     */
    private static function get_content() {
        global $DB, $PAGE, $USER;

        $modid = $PAGE->cm->instance;
        $context = $PAGE->cm->context;
        $instance = $DB->get_record('openaichat', ['id' => $modid], '*', MUST_EXIST);
        $apikey = self::get_api_key($modid);

        // Send data to front end.
        $persistconvo = $instance->persistconvo;

        $PAGE->requires->js_call_amd('mod_openaichat/lib', 'init', [[
            'modId' => $modid,
            'api_type' => $instance->type,
            'persistConvo' => $persistconvo,
            'userId' => $USER->id,
        ]]);

        // First, fetch the global settings for these (and the defaults if not set).
        $assistantname = $instance->assistantname ? $instance->assistantname : get_config('mod_openaichat', 'assistantname');
        $username = $instance->username ? $instance->username : get_config('mod_openaichat', 'username');
        $assistantname = format_string($assistantname, true, ['context' => $context]);
        $username = format_string($username, true, ['context' => $context]);

        $content = new stdClass();
        $content->text = '
            <script>
                var assistantName = "' . $assistantname . '";
                var userName = "' . $username . '";
            </script>

            <style>
                .openai_message.user:before {
                    content: "' . $username . '";
                }
                .openai_message.bot:before {
                    content: "' . $assistantname . '";
                }
            </style>

            <div id="openai_chat_log" role="log"></div>
        ';

        $content->footer = $apikey ? '
            <div id="control_bar">
                <div id="input_bar">
                    <textarea id="openai_input" placeholder="' . get_string('askaquestion', 'mod_openaichat') . '" type="text" name="message" rows="4" cols="50"" /></textarea>
                    <button title="Submit" id="go"><i class="fa fa-arrow-right"></i></button>
                </div>
                <button title="New chat" id="refresh"><i class="fa fa-refresh"></i></button>
            </div>'
        : get_string('apikeymissing', 'mod_openaichat');

        return $content;
    }

    /**
     * Get API key for a given module instance or site-wide.
     *
     * @param int|null $modid The module instance ID.
     * @return string The API key.
     */
    private static function get_api_key($modid = null) {
        global $DB;

        if ($modid !== null) {
            $instance = $DB->get_record('openaichat', ['id' => $modid], '*', MUST_EXIST);
            if (!empty($instance->apikey)) {
                return $instance->apikey;
            }
        }

        return get_config('mod_openaichat', 'apikey');
    }
}
