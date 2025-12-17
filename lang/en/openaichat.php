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
 * Plugin strings are defined here.
 *
 * @package    mod_openaichat
 * @copyright  2024 think modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['advanced'] = 'Advanced settings';
$string['advanced_default'] = 'temperature: 0.5
max_completion_tokens: 500
top_p: 1
';
$string['advanced_help'] = 'Advanced arguments sent to OpenAI. One setting per line. Be aware that some setttings are only supported by some models';
$string['allowinstancesettings'] = 'Instance-level settings';
$string['allowinstancesettings_help'] = 'This setting will allow teachers, or anyone with the capability to add an activity in a context, to adjust settings at a per-activity level. Enabling this could incur additional charges by allowing non-admins to choose higher-cost models or other settings.';
$string['apikey'] = 'OpenAI API Key';
$string['apikey_help'] = 'The API Key for your OpenAI account. <a href=?section=modsettingopenaichat&testconnection=1>Test connection</a>.';
$string['apikeymissing'] = 'Please add your OpenAI API key to the activity settings.';
$string['apisettings'] = 'API settings';
$string['askaquestion'] = 'Ask a question...';
$string['assistant'] = 'Assistant';
$string['assistant_help'] = 'The assistant attached to your OpenAI account that you would like to use for the response. The assistant must be in the same project as the API key used.';
$string['assistantheading'] = 'Assistant API settings';
$string['assistantheading_help'] = 'These settings only apply to the Assistant API type.';
$string['assistantname'] = 'Assistant name';
$string['assistantname_help'] = 'The name that the AI will use for itself internally. It is also used for the UI headings in the chat window.';
$string['chatheading'] = 'Chat API settings';
$string['chatheading_help'] = 'These settings only apply to API type "chat", not "assistant".';
$string['connection:error'] = 'Error connecting to OpenAI: {$a}';
$string['connection:success'] = 'Connection to OpenAI successful.';
$string['defaultassistantname'] = 'Assistant';
$string['defaultprompt'] = "Below is a conversation between a user and a support assistant for a Moodle site, where users go for online learning:";
$string['defaultusername'] = 'User';
$string['disclaimer'] = 'Attention! In this chat you communicate with ChatGPT. All information you enter here is sent to OpenAI and all information you receive in the chat comes from OpenAI-ChatGPT. You can find OpenAI\'s terms of use <a href="https://openai.com/policies/terms-of-use" class="alert-link">here</a>. The OpenAI-ChatGPT AI-Bot can make mistakes. Please check important information yourself.';
$string['erroroccured'] = 'An error occurred. Please try again later.';
$string['event:modviewed'] = 'Open AI Chat viewed';
$string['instancelevelsettingsdisabled'] = 'Instance-level settings are disabled by the administrator. Your instance will use the site default settings.';
$string['model'] = 'Model';
$string['model_help'] = 'The model which will generate the completion. Some models are suitable for natural language tasks, others specialize in code.';
$string['models'] = 'Available models';
$string['models_desc'] = 'A list of available AI models that can be used in the Open AI Chat activity. Format: one model per line. eg: "gpt-5.2"';
$string['modulename'] = 'Open AI Chat';
$string['modulenameplural'] = 'Open AI Chats';
$string['noassistantmessagesfound'] = 'No assistant message found for run {$a}';
$string['noassistants'] = 'You have not created any assistants yet. You need to create one <a target="_blank" href="https://platform.openai.com/assistants">in your OpenAI account</a> before you can select it here.';
$string['noopenaichatinstances'] = 'There are no Open AI Chat instances in this course.';
$string['noquestionsleft'] = 'You have no more questions left in this activity.';
$string['openaichat:addinstance'] = 'Add an Open AI Chat activity';
$string['openaichat:seeopenailog'] = 'See the Open AI report log';
$string['openaichat:view'] = 'View Open AI Chat activity';
$string['openaichatname'] = 'Open AI Chat name';
$string['openailog'] = 'Open AI Log';
$string['persistconvo'] = 'Persist conversations';
$string['persistconvo_help'] = 'If this box is checked, the assistant will remember the conversation between page loads. However, separate activity instances will maintain separate conversations. For example, a user\'s conversation will be retained between page loads within the same course, but chatting with an assistant in a different course will not carry on the same conversation.';
$string['pluginadministration'] = 'Open AI Administration';
$string['pluginname'] = 'Open AI Chat';
$string['prompt'] = 'Completion prompt';
$string['prompt_help'] = 'The prompt the AI will be given before the conversation transcript';
$string['questionlimit'] = 'Question Limit';
$string['questionlimit_help'] = 'The number of questions a user is allowed to ask inside an openai chat activity.';
$string['restrictusage'] = 'Restrict chat usage to logged-in users';
$string['restrictusage_help'] = 'If this box is checked, only logged-in users will be able to use the chat box.';
$string['settingsheading'] = 'Open AI Chat settings';
$string['sourceoftruth'] = 'Source of truth';
$string['sourceoftruth_help'] = 'Although the AI is very capable out-of-the-box, if it doesn\'t know the answer to a question, it is more likely to give incorrect information confidently than to refuse to answer. In this textbox, you can add common questions and their answers for the AI to pull from. Please put questions and answers in the following format: <pre>Q: Question 1<br />A: Answer 1<br /><br />Q: Question 2<br />A: Answer 2</pre>';
$string['sourceoftruthpreamble'] = "Below is a list of questions and their answers. This information should be used as a reference for any inquiries:\n\n";
$string['table:activity'] = "Activity";
$string['table:answers'] = "Answers";
$string['table:order'] = "Order";
$string['table:questions'] = "Questions";
$string['table:sessionid'] = "Session ID";
$string['termsaccept'] = 'Accept';
$string['termsdecline'] = 'Decline';
$string['termsofuse'] = 'Please read the <a href="https://openai.com/policies/terms-of-use" target="_blank">OpenAI Terms of Use</a> carefully before using this activity. By clicking "Accept", you agree to comply with and be bound by these terms. If you do not agree to these terms, click "Decline" and you will not be able to use this activity.';
$string['type'] = 'API Type';
$string['type_help'] = 'The API type that the plugin should use.';
$string['username'] = 'User name';
$string['username_help'] = 'The name that the AI will use for the user internally. It is also used for the UI headings in the chat window.';
