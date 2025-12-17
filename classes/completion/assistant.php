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
 * Class providing completions for assistant API
 *
 * @package    mod_openaichat
 * @copyright  2024 think modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_openaichat\completion;

use mod_openaichat\completion;
use mod_openaichat\openaichat;

/**
 * Class providing completions for assistant API
 *
 * @package    mod_openaichat
 */
class assistant extends \mod_openaichat\completion {
    /** @var string the thread id */
    private $threadid;

    /**
     * Constructor
     *
     * @param string $model The model to use
     * @param string $message The user message
     * @param array $history The message history
     * @param object $modsettings The module settings
     * @param string|null $threadid The thread id, or NULL to create a new thread
     */
    public function __construct($model, $message, $history, $modsettings, $threadid) {
        parent::__construct($model, $message, $history, $modsettings);

        // If threadid is NULL, create a new thread.
        if (!$threadid) {
            $threadid = $this->create_thread();
        }
        $this->threadid = $threadid;
    }

    /**
     * Given everything we know after constructing the parent,
     * create a completion by constructing the prompt and making the api call
     * @return JSON: The API response from OpenAI
     */
    public function create_completion($context) {
        if (!$this->user_has_questions_left()) {
            return [
                'id' => null,
                'message' => get_string('noquestionsleft', 'mod_openaichat'),
                'threadid' => $this->threadid,
            ];
        }

        $messageid = $this->add_message_to_thread();

        if (!$this->wait_for_message($messageid)) {
            return [
                'id' => null,
                'message' => 'User message could not be persisted to thread.',
                'threadid' => $this->threadid,
            ];
        }

        return $this->run();
    }

    /**
     * Create a new thread in OpenAI
     * @return string The thread ID
     */
    private function create_thread(): string {
        $headers = ['OpenAI-Beta: assistants=v2'];
        $url = 'https://api.openai.com/v1/threads';

        // IMPORTANT: pass an empty payload to force POST in api_call().
        $response = openaichat::api_call($url, $this->modid, (object)[], $headers);

        if (!is_object($response)) {
            throw new \moodle_exception('OpenAI thread creation failed: empty response.');
        }

        if (!empty($response->error)) {
            $msg = $response->error->message ?? 'Unknown error';
            throw new \moodle_exception('OpenAI thread creation failed: ' . $msg);
        }

        if (empty($response->id)) {
            throw new \moodle_exception('OpenAI thread creation failed: missing thread id.');
        }

        return $response->id;
    }

    /**
     * Add the user message to the thread
     * @return string The message ID
     */
    private function add_message_to_thread(): string {
        $headers = ['OpenAI-Beta: assistants=v2'];

        $payload = (object) [
            'role' => 'user',
            'content' => $this->message,
        ];

        $response = openaichat::api_call(
            "https://api.openai.com/v1/threads/{$this->threadid}/messages",
            $this->modid,
            $payload,
            $headers
        );

        if (!is_object($response)) {
            throw new \moodle_exception('OpenAI message creation failed: empty response.');
        }

        if (!empty($response->error)) {
            $msg = $response->error->message ?? 'Unknown error';
            throw new \moodle_exception('OpenAI message creation failed: ' . $msg);
        }

        if (empty($response->id)) {
            throw new \moodle_exception('OpenAI message creation failed: missing message id.');
        }

        return $response->id;
    }

    /**
     * Wait for a message to appear in the thread
     * @param string $messageid The message ID to wait for
     * @param int $timeout The timeout in seconds
     * @return bool True if the message was found, false if timed out
     */
    private function wait_for_message(string $messageid, int $timeout = 5): bool {
        $headers = ['OpenAI-Beta: assistants=v2'];
        $start = microtime(true);

        do {
            $messages = openaichat::api_call(
                "https://api.openai.com/v1/threads/{$this->threadid}/messages",
                $this->modid,
                null,
                $headers
            );

            foreach ($messages->data as $message) {
                if ($message->id === $messageid) {
                    return true;
                }
            }

            usleep(300_000);
        } while (microtime(true) - $start < $timeout);

        return false;
    }

    /**
     * Run the assistant and fetch the response
     * @return array The assistant response details
     */
    private function run(): array {
        $headers = ['OpenAI-Beta: assistants=v2'];

        $response = openaichat::api_call(
            "https://api.openai.com/v1/threads/{$this->threadid}/runs",
            $this->modid,
            [
                'assistant_id' => $this->assistant,
                'instructions' => $this->instructions ?: 'You are a helpful assistant.',
            ],
            $headers
        );

        if (!empty($response->error)) {
            return [
                'id' => null,
                'message' => $response->error->message,
                'threadid' => $this->threadid,
            ];
        }

        $runid = $response->id;

        $start = microtime(true);
        $timeout = 20;

        do {
            $status = $this->get_run_status($runid);

            if ($status === 'completed') {
                break;
            }

            if (in_array($status, ['failed', 'cancelled', 'expired'], true)) {
                return [
                    'id' => null,
                    'message' => "Run failed with status: {$status}",
                    'threadid' => $this->threadid,
                ];
            }

            if (microtime(true) - $start > $timeout) {
                return [
                    'id' => null,
                    'message' => 'Run timed out.',
                    'threadid' => $this->threadid,
                ];
            }

            usleep(500_000);
        } while (true);

        return $this->fetch_assistant_message();
    }

    /**
     * Get the status of a run
     * @param string $runid The run ID
     * @return string The run status
     */
    private function get_run_status(string $runid): string {
        $headers = ['OpenAI-Beta: assistants=v2'];

        $response = openaichat::api_call(
            "https://api.openai.com/v1/threads/{$this->threadid}/runs/{$runid}",
            $this->modid,
            null,
            $headers
        );

        return $response->status ?? 'unknown';
    }

    /**
     * Fetch the assistant's message from the thread
     * @return array The assistant message details
     */
    private function fetch_assistant_message(): array {
        $headers = ['OpenAI-Beta: assistants=v2'];

        $messages = openaichat::api_call(
            "https://api.openai.com/v1/threads/{$this->threadid}/messages",
            $this->modid,
            null,
            $headers
        );

        foreach ($messages->data as $message) {
            if ($message->role !== 'assistant') {
                continue;
            }

            $text = '';

            foreach ($message->content as $block) {
                if ($block->type === 'text') {
                    $text .= $block->text->value;
                }
            }

            if ($text !== '') {
                return [
                    'id' => $message->id,
                    'message' => $text,
                    'threadid' => $this->threadid,
                ];
            }
        }

        return [
            'id' => null,
            'message' => 'No assistant message found.',
            'threadid' => $this->threadid,
        ];
    }
}
