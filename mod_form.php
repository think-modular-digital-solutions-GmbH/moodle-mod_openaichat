<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * The main mod_openaichat configuration form.
 *
 * @package    mod_openaichat
 * @copyright  2024 think modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use mod_openaichat\openaichat;

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/openaichat/lib.php');
/**
 * Module instance settings form.
 *
 * @package     mod_openaichat
 * @copyright   2023 think modular
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_openaichat_mod_form extends moodleform_mod {
    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG, $OUTPUT, $PAGE;

        $mform = $this->_form;

        // General fieldset.
        $mform->addElement(
            'header',
            'general',
            get_string('general', 'form')
        );

        // The standard "name" field.
        $mform->addElement(
            'text',
            'name',
            get_string('openaichatname', 'mod_openaichat')
        );
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        // Standard elements.
        $this->standard_intro_elements();

        // OpenAI Chat settings fieldset.
        $mform->addElement('header', 'openaichatsettings', get_string('settingsheading', 'mod_openaichat'));

        // Get module id if available.
        $modid = null;
        $cmid = null;
        if (isset($PAGE->cm->instance)) {
            $modid = $PAGE->cm->instance;
            $cmid = $PAGE->cm->id;
        }
        $assistantarray = openaichat::fetch_assistants($modid);

        // Link to log report.
        if ($modid) {
            $url = new moodle_url('/mod/openaichat/report.php', ['id' => $cmid, 'modid' => $modid]);
            $mform->addElement(
                'html',
                html_writer::link($url, get_string('openailog', 'mod_openaichat'))
            );
        }

        if (get_config('mod_openaichat', 'allowinstancesettings') == 1) {

            // API Key.
            $mform->addElement(
                'password',
                'apikey',
                get_string('apikey', 'mod_openaichat'),
            );
            $mform->setDefault('apikey', get_config('mod_openaichat', 'apikey'));
            $mform->addHelpButton('apikey', 'apikey', 'mod_openaichat');
            $mform->setType('apikey', PARAM_TEXT);

            // Type of chat.
            $types = [
                'chat' => 'chat',
                'assistant' => 'assistant',
            ];
            $mform->addElement(
                'select',
                'type',
                get_string('type', 'mod_openaichat'),
                $types
            );
            $mform->setDefault('type', get_config('mod_openaichat', 'type'));
            $mform->addHelpButton('type', 'type', 'mod_openaichat');
            $mform->setType('type', PARAM_TEXT);

            // Question limit.
            $mform->addElement(
                'text',
                'questionlimit',
                get_string('questionlimit', 'mod_openaichat'),
                ['size' => '4']
            );
            $mform->setDefault('questionlimit', get_config('mod_openaichat', 'questionlimit'));
            $mform->addHelpButton('questionlimit', 'questionlimit', 'mod_openaichat');
            $mform->setType('questionlimit', PARAM_INT);

            // Assistant name.
            $mform->addElement(
                'text',
                'assistantname',
                get_string('assistantname', 'mod_openaichat'),
                ['size' => '30']
            );
            $mform->setDefault('assistantname', get_config('mod_openaichat', 'assistantname'));
            $mform->addHelpButton('assistantname', 'assistantname', 'mod_openaichat');
            $mform->setType('assistantname', PARAM_TEXT);

            // Username.
            $mform->addElement(
                'text',
                'username',
                get_string('username', 'mod_openaichat'),
                ['size' => '30']
            );
            $mform->setDefault('username', get_config('mod_openaichat', 'username'));
            $mform->addHelpButton('username', 'username', 'mod_openaichat');
            $mform->setType('username', PARAM_TEXT);

            // API settings fieldset.
            $mform->addElement(
                'header',
                'apisettingsheading',
                get_string('apisettings', 'mod_openaichat')
            );

            // Assistant selection.
            if (count($assistantarray)) {
                $mform->addElement(
                    'select',
                    'assistant',
                    get_string('assistant', 'mod_openaichat'),
                    $assistantarray
                );
                $firstoption = count($assistantarray) ? reset($assistantarray) : null;
                $mform->setDefault('assistant', $firstoption);
                $mform->addHelpButton('assistant', 'assistant', 'mod_openaichat');
            } else {
                $mform->addElement(
                    'static',
                    'assistant',
                    get_string('assistant', 'mod_openaichat'),
                    get_string('noassistants', 'mod_openaichat')
                );
            }
            $mform->setType('assistant', PARAM_TEXT);
            $mform->hideIf('assistant', 'type', 'neq', 'assistant');

            // Persist conversation.
            $mform->addElement(
                'advcheckbox',
                'persistconvo',
                get_string('persistconvo', 'mod_openaichat')
            );
            $mform->setDefault('persistconvo', get_config('mod_openaichat', 'persistconvo'));
            $mform->addHelpButton('persistconvo', 'persistconvo', 'mod_openaichat');
            $mform->setType('persistconvo', PARAM_INT);
            $mform->hideIf('persistconvo', 'type', 'neq', 'assistant');

            // Prompt.
            $mform->addElement(
                'textarea',
                'prompt',
                get_string('prompt', 'mod_openaichat'),
                'wrap="virtual" rows="10" cols="80"'
            );
            $mform->setDefault('prompt', get_config('mod_openaichat', 'prompt'));
            $mform->addHelpButton('prompt', 'prompt', 'mod_openaichat');
            $mform->setType('prompt', PARAM_TEXT);
            $mform->hideIf('prompt', 'type', 'neq', 'chat');

            // Source of truth.
            $mform->addElement(
                'textarea',
                'sourceoftruth',
                get_string('sourceoftruth', 'mod_openaichat'),
                'wrap="virtual" rows="10" cols="80"'
            );
            $mform->setDefault('sourceoftruth', get_config('mod_openaichat', 'sourceoftruth'));
            $mform->addHelpButton('sourceoftruth', 'sourceoftruth', 'mod_openaichat');
            $mform->setType('sourceoftruth', PARAM_TEXT);
            $mform->hideIf('sourceoftruth', 'type', 'neq', 'chat');

            // Advanced settings fieldset.
            $mform->addElement(
                'static',
                'advancedsettingsheading',
                get_string('advanced', 'mod_openaichat'),
                '<strong>' . get_string('advanced_help', 'mod_openaichat') . '</strong>'
            );
            $mform->hideIf('advancedsettingsheading', 'type', 'neq', 'chat');

            // Model.
            $mform->addElement(
                'select',
                'model',
                get_string('model', 'mod_openaichat'),
                openaichat::get_ai_models()
            );
            $mform->setDefault('model', get_config('mod_openaichat', 'model'));
            $mform->addHelpButton('model', 'model', 'mod_openaichat');
            $mform->setType('model', PARAM_TEXT);
            $mform->hideIf('model', 'type', 'neq', 'chat');

            // Advanced JSON settings.
            $mform->addElement(
                'textarea',
                'advanced',
                get_string('advanced', 'mod_openaichat'),
                'wrap="virtual" rows="10" cols="80"'
            );
            $mform->setDefault('advanced', get_config('mod_openaichat', 'advanced'));
            $mform->addHelpButton('advanced', 'advanced', 'mod_openaichat');
            $mform->setType('advanced', PARAM_TEXT);
            $mform->hideIf('advanced', 'type', 'neq', 'chat');

        } else {
            // Instance-level settings disabled message.
            $mform->addElement(
                'html',
                $OUTPUT->notification(
                    get_string('instancelevelsettingsdisabled', 'mod_openaichat'),
                    'info'
                )
            );
        }

        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }
}
