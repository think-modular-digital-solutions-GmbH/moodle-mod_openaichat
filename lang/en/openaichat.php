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

$string['advanced'] = 'Advanced';
$string['advanced_help'] = 'Advanced arguments sent to OpenAI. Don\'t touch unless you know what you\'re doing!';
$string['advanceddesc'] = 'Advanced arguments sent to OpenAI. Don\'t touch unless you know what you\'re doing!';
$string['allowinstancesettings'] = 'Instance-level settings';
$string['allowinstancesettings_help'] = 'This setting will allow teachers, or anyone with the capability to add an activity in a context, to adjust settings at a per-activity level. Enabling this could incur additional charges by allowing non-admins to choose higher-cost models or other settings.';
$string['allowinstancesettingsdesc'] = 'This setting will allow teachers, or anyone with the capability to add an activity in a context, to adjust settings at a per-activity level. Enabling this could incur additional charges by allowing non-admins to choose higher-cost models or other settings.';
$string['apikey'] = 'OpenAI API Key';
$string['apikey_help'] = 'The API Key for your OpenAI account';
$string['apikeydesc'] = 'The API Key for your OpenAI account';
$string['apikeymissing'] = 'Please add your OpenAI API key to the activity settings.';
$string['askaquestion'] = 'Ask a question...';
$string['assistant'] = 'Assistant';
$string['assistant_help'] = 'The default assistant attached to your OpenAI account that you would like to use for the response';
$string['assistantdesc'] = 'The default assistant attached to your OpenAI account that you would like to use for the response';
$string['assistantheading'] = 'Assistant API Settings';
$string['assistantheading_help'] = 'These settings only apply to the Assistant API type.';
$string['assistantheadingdesc'] = 'These settings only apply to the Assistant API type.';
$string['assistantname'] = 'Assistant name';
$string['assistantname_help'] = 'The name that the AI will use for itself internally. It is also used for the UI headings in the chat window.';
$string['assistantnamedesc'] = 'The name that the AI will use for itself internally. It is also used for the UI headings in the chat window.';
$string['chatheading'] = 'Chat API Settings';
$string['chatheading_help'] = 'These settings only apply to the Chat API type.';
$string['chatheadingdesc'] = 'These settings only apply to the Chat API type.';
$string['defaultassistantname'] = 'Assistant';
$string['defaultprompt'] = "Below is a conversation between a user and a support assistant for a Moodle site, where users go for online learning:";
$string['defaultusername'] = 'User';
$string['disclaimer'] = 'Attention! In this chat you communicate with ChatGPT. All information you enter here is sent to OpenAI and all information you receive in the chat comes from OpenAI-ChatGPT. You can find OpenAI\'s terms of use <a href="https://openai.com/de-DE/policies/eu-terms-of-use" class="alert-link">HERE</a>. The OpenAI-ChatGPT AI-Bot can make mistakes. Please check important information yourself.';
$string['event:modviewed'] = 'Open AI Chat viewed';
$string['frequency'] = 'Frequency penalty';
$string['frequency_help'] = 'How much to penalize new tokens based on their existing frequency in the text so far. Decreases the model\'s likelihood to repeat the same line verbatim.';
$string['frequencydesc'] = 'How much to penalize new tokens based on their existing frequency in the text so far. Decreases the model\'s likelihood to repeat the same line verbatim.';
$string['maxlength'] = 'Maximum length';
$string['maxlength_help'] = 'The maximum number of token to generate. Requests can use up to 2,048 or 4,000 tokens shared between prompt and completion. The exact limit varies by model. (One token is roughly 4 characters for normal English text)';
$string['maxlengthdesc'] = 'The maximum number of token to generate. Requests can use up to 2,048 or 4,000 tokens shared between prompt and completion. The exact limit varies by model. (One token is roughly 4 characters for normal English text)';
$string['model'] = 'Model';
$string['model_help'] = 'The model which will  generate the completion. Some models are suitable for natural language tasks, others specialize in code.';
$string['modeldesc'] = 'The model which will  generate the completion. Some models are suitable for natural language tasks, others specialize in code.';
$string['modulename'] = 'Open AI Chat';
$string['modulenameplural'] = 'Open AI Chats';
$string['noassistants'] = 'You have not created any assistants yet. You need to create one <a target="_blank" href="https://platform.openai.com/assistants">in your OpenAI account</a> before you can select it here.';
$string['noopenaichatinstances'] = 'There are no Open AI Chat instances in this course.';
$string['openaichat:addinstance'] = 'Add an Open AI Chat activity';
$string['openaichat:seeopenailog'] = 'See the Open AI report log';
$string['openaichat:view'] = 'View Open AI Chat activity';
$string['openaichatname'] = 'Open AI Chat name';
$string['openailog'] = 'Open AI Log';
$string['persistconvo'] = 'Persist conversations';
$string['persistconvo_help'] = 'If this box is checked, the assistant will remember the conversation between page loads. However, separate activity instances will maintain separate conversations. For example, a user\'s conversation will be retained between page loads within the same course, but chatting with an assistant in a different course will not carry on the same conversation.';
$string['persistconvodesc'] = 'If this box is checked, the assistant will remember the conversation between page loads. However, separate activity instances will maintain separate conversations. For example, a user\'s conversation will be retained between page loads within the same course, but chatting with an assistant in a different course will not carry on the same conversation.';
$string['pluginadministration'] = 'Open AI Administration';
$string['pluginname'] = 'Open AI Chat';
$string['presence'] = 'Presence penalty';
$string['presence_help'] = 'How much to penalize new tokens based on whether they appear in the text so far. Increases the model\'s likelihood to talk about new topics.';
$string['presencedesc'] = 'How much to penalize new tokens based on whether they appear in the text so far. Increases the model\'s likelihood to talk about new topics.';
$string['prompt'] = 'Completion prompt';
$string['prompt_help'] = 'The prompt the AI will be given before the conversation transcript';
$string['promptdesc'] = 'The prompt the AI will be given before the conversation transcript';
$string['questionlimit'] = 'Question Limit';
$string['questionlimit_help'] = 'The number of questions a user is allowed to ask inside an openai chat activity.';
$string['questionlimitdesc'] = 'The number of questions a user is allowed to ask inside an openai chat activity.';
$string['restrictusage'] = 'Restrict chat usage to logged-in users';
$string['restrictusage_help'] = 'If this box is checked, only logged-in users will be able to use the chat box.';
$string['restrictusagedesc'] = 'If this box is checked, only logged-in users will be able to use the chat box.';
$string['sourceoftruth'] = 'Source of truth';
$string['sourceoftruth_help'] = 'Although the AI is very capable out-of-the-box, if it doesn\'t know the answer to a question, it is more likely to give incorrect information confidently than to refuse to answer. In this textbox, you can add common questions and their answers for the AI to pull from. Please put questions and answers in the following format: <pre>Q: Question 1<br />A: Answer 1<br /><br />Q: Question 2<br />A: Answer 2</pre>';
$string['sourceoftruthdesc'] = 'Although the AI is very capable out-of-the-box, if it doesn\'t know the answer to a question, it is more likely to give incorrect information confidently than to refuse to answer. In this textbox, you can add common questions and their answers for the AI to pull from. Please put questions and answers in the following format: <pre>Q: Question 1<br />A: Answer 1<br /><br />Q: Question 2<br />A: Answer 2</pre>';
$string['sourceoftruthpreamble'] = "Below is a list of questions and their answers. This information should be used as a reference for any inquiries:\n\n";
$string['temperature'] = 'Temperature';
$string['temperature_help'] = 'Controls randomness: Lowering results in less random completions. As the temperature approaches zero, the model will become deterministic and repetitive.';
$string['temperaturedesc'] = 'Controls randomness: Lowering results in less random completions. As the temperature approaches zero, the model will become deterministic and repetitive.';
$string['termsaccept'] = 'Accept Terms of Use';
$string['termsdecline'] = 'Decline Terms of Use';
$string['termsofuse'] = '<p><em>Terms of Use</em> Text</p>';
$string['topp'] = 'Top P';
$string['topp_help'] = 'Controls diversity via nucleus sampling: 0.5 means half of all likelihood-weighted options are considered.';
$string['toppdesc'] = 'Controls diversity via nucleus sampling: 0.5 means half of all likelihood-weighted options are considered.';
$string['type'] = 'API Type';
$string['type_help'] = 'The API type that the plugin should use';
$string['typedesc'] = 'The API type that the plugin should use';
$string['username'] = 'User name';
$string['username_help'] = 'The name that the AI will use for the user internally. It is also used for the UI headings in the chat window.';
$string['usernamedesc'] = 'The name that the AI will use for the user internally. It is also used for the UI headings in the chat window.';
