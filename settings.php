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
 * Plugin settings
 *
 * @package    mod_openaichat
 * @copyright  2024 think modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use mod_openaichat\openaichat;

require_once($CFG->dirroot . '/mod/openaichat/lib.php');

global $PAGE, $ADMIN;

// JS for conditionally showing settings.
$PAGE->requires->js_call_amd('mod_openaichat/settings', 'init');

// Get type and assistants - only if on own settings page.
$type = 'chat';
$assistants = [];
if (array_key_exists('section', $_GET) && $_GET['section'] == 'modsettingopenaichat') {
    $type = openaichat::get_type_to_display();
    $assistants = openaichat::fetch_assistants();
}

// Report log page.
$reporturl = new moodle_url('/mod/openaichat/report.php');
$ADMIN->add(
    'reports',
    new admin_externalpage(
        'mod_openaichat_reportlog',
        get_string('openailog', 'mod_openaichat'),
        $reporturl,
        'report/log:view'
    )
);

// Link to reports on settings page.
$settings->add(new \admin_setting_heading(
    'mod_openaichat/reportloglink',
    '',
    html_writer::link($reporturl, get_string('openailog', 'mod_openaichat'))
));

// API Key.
$settings->add(new \admin_setting_configtext(
    'mod_openaichat/apikey',
    get_string('apikey', 'mod_openaichat'),
    get_string('apikey_help', 'mod_openaichat'),
    '',
    PARAM_TEXT
));

// Type of AI interaction.
$settings->add(new \admin_setting_configselect(
    'mod_openaichat/type',
    get_string('type', 'mod_openaichat'),
    get_string('type_help', 'mod_openaichat'),
    'chat',
    ['chat' => 'chat', 'assistant' => 'assistant']
));

// Restrict usage.
$settings->add(new \admin_setting_configcheckbox(
    'mod_openaichat/restrictusage',
    get_string('restrictusage', 'mod_openaichat'),
    get_string('restrictusage_help', 'mod_openaichat'),
    1
));

// Assistant name.
$settings->add(new \admin_setting_configtext(
    'mod_openaichat/assistantname',
    get_string('assistantname', 'mod_openaichat'),
    get_string('assistantname_help', 'mod_openaichat'),
    'Assistant',
    PARAM_TEXT
));

// Username.
$settings->add(new \admin_setting_configtext(
    'mod_openaichat/username',
    get_string('username', 'mod_openaichat'),
    get_string('username_help', 'mod_openaichat'),
    'User',
    PARAM_TEXT
));

// Question limit.
$settings->add(new \admin_setting_configtext(
    'mod_openaichat/questionlimit',
    get_string('questionlimit', 'mod_openaichat'),
    get_string('questionlimit_help', 'mod_openaichat'),
    '',
    PARAM_TEXT
));

// Models.
$settings->add(new \admin_setting_configtextarea(
    'mod_openaichat/models',
    get_string('models', 'mod_openaichat'),
    get_string('models_desc', 'mod_openaichat'),
    openaichat::DEFAULT_MODELS,
));

// Allow instance.
$settings->add(new \admin_setting_configcheckbox(
    'mod_openaichat/allowinstancesettings',
    get_string('allowinstancesettings', 'mod_openaichat'),
    get_string('allowinstancesettings_help', 'mod_openaichat'),
    0
));

// Assistant settings.
if ($type === 'assistant') {
    // Assistant settings.
    $settings->add(new \admin_setting_heading(
        'mod_openaichat/assistantheading',
        get_string('assistantheading', 'mod_openaichat'),
        get_string('assistantheading_help', 'mod_openaichat')
    ));

    if (count($assistants) > 0) {
        // Assistants available.
        $settings->add(new \admin_setting_configselect(
            'mod_openaichat/assistant',
            get_string('assistant', 'mod_openaichat'),
            get_string('assistant_help', 'mod_openaichat'),
            count($assistants) ? reset($assistants) : null,
            $assistants,
        ));
    } else {
        // No assistants available.
        $settings->add(new \admin_setting_description(
            'mod_openaichat/noassistants',
            get_string('assistant', 'mod_openaichat'),
            get_string('noassistants', 'mod_openaichat'),
        ));
    }

    $settings->add(new \admin_setting_configcheckbox(
        'mod_openaichat/persistconvo',
        get_string('persistconvo', 'mod_openaichat'),
        get_string('persistconvo_help', 'mod_openaichat'),
        1
    ));
} else {
    // Chat settings.
    $settings->add(new \admin_setting_heading(
        'mod_openaichat/chatheading',
        get_string('chatheading', 'mod_openaichat'),
        get_string('chatheading_help', 'mod_openaichat')
    ));

    // Prompt.
    $settings->add(new \admin_setting_configtextarea(
        'mod_openaichat/prompt',
        get_string('prompt', 'mod_openaichat'),
        get_string('prompt_help', 'mod_openaichat'),
        "Below is a conversation between a user and a support assistant for a Moodle site, where users go for online learning.",
        PARAM_TEXT
    ));

    // Source of Truth.
    $settings->add(new \admin_setting_configtextarea(
        'mod_openaichat/sourceoftruth',
        get_string('sourceoftruth', 'mod_openaichat'),
        get_string('sourceoftruth_help', 'mod_openaichat'),
        '',
        PARAM_TEXT
    ));
}

// Advanced Settings.
$settings->add(new \admin_setting_heading(
    'mod_openaichat/advanced',
    get_string('advanced', 'mod_openaichat'),
    get_string('advanced_help', 'mod_openaichat')
));

// AI Model.
$models = openaichat::get_ai_models();
$models = array_combine(array_keys($models), array_keys($models));
$settings->add(new \admin_setting_configselect(
    'mod_openaichat/model',
    get_string('model', 'mod_openaichat'),
    get_string('model_help', 'mod_openaichat'),
    reset($models),
    $models,
));

$settings->add(new \admin_setting_configtext(
    'mod_openaichat/temperature',
    get_string('temperature', 'mod_openaichat'),
    get_string('temperature_help', 'mod_openaichat'),
    0.5,
    PARAM_FLOAT
));

$settings->add(new \admin_setting_configtext(
    'mod_openaichat/maxlength',
    get_string('maxlength', 'mod_openaichat'),
    get_string('maxlength_help', 'mod_openaichat'),
    500,
    PARAM_INT
));

$settings->add(new \admin_setting_configtext(
    'mod_openaichat/topp',
    get_string('topp', 'mod_openaichat'),
    get_string('topp_help', 'mod_openaichat'),
    1,
    PARAM_FLOAT
));

$settings->add(new \admin_setting_configtext(
    'mod_openaichat/frequency',
    get_string('frequency', 'mod_openaichat'),
    get_string('frequency_help', 'mod_openaichat'),
    1,
    PARAM_FLOAT
));

$settings->add(new \admin_setting_configtext(
    'mod_openaichat/presence',
    get_string('presence', 'mod_openaichat'),
    get_string('presence_help', 'mod_openaichat'),
    1,
    PARAM_FLOAT
));

