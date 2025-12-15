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
 * Prints an instance of mod_openaichat.
 *
 * @package    mod_openaichat
 * @copyright  2024 think modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

use context_module;
use mod_openaichat\openaichat;

// Get the parameters.
$id = optional_param('id', 0, PARAM_INT);
$instanceid = optional_param('o', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('openaichat', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('openaichat', ['id' => $cm->instance], '*', MUST_EXIST);
} else {
    $moduleinstance = $DB->get_record('openaichat', ['id' => $instanceid], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $moduleinstance->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('openaichat', $moduleinstance->id, $course->id, false, MUST_EXIST);
}

// Permissions.
require_login($course, true, $cm);
$modulecontext = context_module::instance($cm->id);
require_capability('mod/openaichat:view', $modulecontext);

// Set up the page.
$PAGE->set_url('/mod/openaichat/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

// Create event.
$event = \mod_openaichat\event\course_module_viewed::create([
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext,
]);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('openaichat', $moduleinstance);
$event->trigger();

// Check user consent.
openaichat::termsofuse();

// Output the page.
echo $OUTPUT->header();
echo openaichat::render();
echo $OUTPUT->footer();
