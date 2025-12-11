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
 * Base completion object class
 *
 * @package    mod_openaichat
 * @copyright  2024 think modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_openaichat;

/**
 * Base completion object class
 *
 * @package    mod_openaichat
 */
class completion {
    /** @var int The module instance ID */
    protected $modid;

    /** @var string The API key to use for this request */
    protected $apikey;

    /** @var string The most recent message sent by the user */
    protected $message;

    /** @var array An array of objects containing the history of the conversation */
    protected $history;

    /** @var string The assistant's name */
    protected $assistantname;

    /** @var string The user's name */
    protected $username;

    /** @var string The prompt to use */
    protected $prompt;

    /** @var string The source of truth to use */
    protected $sourceoftruth;

    /** @var string The name of the model we're using */
    protected $model;

    /** @var float The temperature setting */
    protected $temperature;

    /** @var int The maximum length of the response */
    protected $maxlength;

    /** @var float The top-p setting */
    protected $topp;

    /** @var float The frequency penalty setting */
    protected $frequency;

    /** @var float The presence penalty setting */
    protected $presence;

    /** @var string The assistant description */
    protected $assistant;

    /** @var string Additional instructions for the assistant */
    protected $instructions;

    /**
     * Initialize all the class properties that we'll need regardless of model.
     *
     * @param string model: The name of the model we're using
     * @param string message: The most recent message sent by the user
     * @param array history: An array of objects containing the history of the conversation
     * @param string block_settings: An object containing the instance-level settings if applicable
     */
    public function __construct($model, $message, $history, $modsettings) {
        global $DB;

        $this->modid = $modsettings['modid'];
        $instance = $DB->get_record('openaichat', ['id' => $modid], '*', MUST_EXIST);

        // Set default values.
        $this->model = $model;
        $this->apikey = $modsettings['apikey'];

        // We fetch defaults for both chat and assistant APIs, even though only one can be active at a time.
        // In the past, multiple different completion classes shared API types, so this might happen again.
        // Any settings that don't apply to the current API type are just ignored.

        $this->prompt = $this->get_setting('prompt', get_string('defaultprompt', 'mod_openaichat'));
        $this->assistantname = $this->get_setting('assistantname', get_string('defaultassistantname', 'mod_openaichat'));
        $this->username = $this->get_setting('username', get_string('defaultusername', 'mod_openaichat'));

        $this->temperature = $this->get_setting('temperature', 0.5);
        $this->maxlength = $this->get_setting('maxlength', 500);
        $this->topp = $this->get_setting('topp', 1);
        $this->frequency = $this->get_setting('frequency', 1);
        $this->presence = $this->get_setting('presence', 1);

        $this->assistant = $this->get_setting('assistant');

        // Then override with block settings if applicable
        if (get_config('mod_openaichat', 'allowinstancesettings') === "1") {
            foreach ($modsettings as $name => $value) {
                if ($value) {
                    $this->$name = $value;
                }
            }
        }

        $this->message = $message;
        $this->history = $history;
        $this->sourceoftruth = $this->get_setting('sourceoftruth');
    }

    /**
     * Attempt to get the saved value for a setting; if this isn't set, return a passed default instead
     * @param string settingname: The name of the setting to fetch
     * @param mixed default: The default value to return if the setting isn't already set
     * @return mixed: The saved or default value
     */
    protected function get_setting($settingname, $default = null) {
        $instance = $DB->get_record('openaichat', ['id' => $this->modid], '*', MUST_EXIST);
        $setting = get_mod_config($this->modid, $settingname);
        return $setting;
    }
}
