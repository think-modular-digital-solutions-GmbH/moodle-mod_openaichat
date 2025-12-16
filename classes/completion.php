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

    /** @var array Additional settings */
    protected $additionalsettings = [];

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
        $instance = $DB->get_record('openaichat', ['id' => $this->modid], '*', MUST_EXIST);

        // Set default values.
        $this->model = $model;
        $this->apikey = $modsettings['apikey'];

        // We fetch defaults for both chat and assistant APIs, even though only one can be active at a time.
        // In the past, multiple different completion classes shared API types, so this might happen again.
        // Any settings that don't apply to the current API type are just ignored.

        $this->prompt = $this->get_setting('prompt', get_string('defaultprompt', 'mod_openaichat'));
        $this->assistantname = $this->get_setting('assistantname', get_string('defaultassistantname', 'mod_openaichat'));
        $this->username = $this->get_setting('username', get_string('defaultusername', 'mod_openaichat'));

        $additionalsettings = $this->get_setting('advanced', []);
        foreach (explode("\n", $additionalsettings) as $line) {
            $parts = explode(':', $line, 2);
            if (count($parts) === 2) {
                $name = trim($parts[0]);
                $value = trim($parts[1]);
                if (is_numeric($value)) {
                    if (strpos($value, '.') !== false) {
                        $value = (float) $value;
                    } else {
                        $value = (int) $value;
                    }
                }
                $this->additionalsettings[$name] = $value;
            }
        }
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
        global $DB;
        $instance = $DB->get_record('openaichat', ['id' => $this->modid], '*', MUST_EXIST);
        if (get_config('mod_openaichat', 'allowinstancesettings') === "1" && isset($instance->$settingname) && $instance->$settingname !== null && $instance->$settingname !== '') {
            $setting = $instance->$settingname;
        } else {
            $setting = get_config('mod_openaichat', $settingname);
        }
        return $setting;
    }

    /**
     * Check if user has questions left based on question limit.
     *
     * @param int $modid The module instance ID.
     * @param int $userid The user ID.
     * @return bool True if user has questions left, false otherwise.
     */
    public function user_has_questions_left() {
        global $DB, $USER;

        $questionlimit = $this->get_setting('questionlimit', 0);
        if ($questionlimit == 0) {
            return true;
        }
        $counter = $DB->get_record('openaichat_userlog', ['modid' => $this->modid, 'userid' => $USER->id])->questioncounter;

        return ($counter < $questionlimit);
    }
}
