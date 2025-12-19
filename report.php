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

require_once('../../config.php');
require_once($CFG->libdir . '/filelib.php');

defined('MOODLE_INTERNAL') || die();

require_login();

use mod_openaichat\openaichat;

global $COURSE, $DB;

// Get parameters.
$cmid = optional_param('cmid', null, PARAM_INT);
$modid = optional_param('modid', null, PARAM_INT);
if ($cmid) {
    $cm = get_coursemodule_from_id('openaichat', $cmid, 0, false, MUST_EXIST);
    $course = get_course($cm->course);
    require_login($course, true, $cm);
    $context = context_module::instance($cmid);
    $PAGE->set_cm($cm, $course);
    $url = new moodle_url('/mod/openaichat/report.php', ['cmid' => $cmid, 'modid' => $modid]);
} else {
    $context = context_system::instance();
    $url = new moodle_url('/mod/openaichat/report.php');
}

// Check permissions.
require_capability('mod/openaichat:seeopenailog', $context);

// Set up page.
$title = get_string('openailog', 'mod_openaichat');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title($title);
$PAGE->set_heading($title);

// Download handling.
$download = optional_param('download', '', PARAM_ALPHA);

// Create table.
$tablename = 'mod-openaichat-report';
$table = new flexible_table($tablename);
$table->is_downloading($download, $title . $tablename);
$cols = [
    'timestamp',
    'sessionid',
    'activity',
    'questions',
    'answers',
];
$headers = [
    get_string('time'),
    get_string('table:sessionid', 'mod_openaichat'),
    get_string('table:activity', 'mod_openaichat'),
    get_string('table:questions', 'mod_openaichat'),
    get_string('table:answers', 'mod_openaichat'),
];
$table->define_columns($cols);
$table->define_headers($headers);
$table->sortable(true);
$table->pageable(true);
$table->define_baseurl($url);
$table->is_downloadable(true);
$table->setup();

// Print the header.
if (!$table->is_downloading()) {
    echo $OUTPUT->header();
}

// Get records.
if (empty($modid)) {
    $records = $DB->get_records_select(
        'openaichat_chatlog',
        '',
        null,
        'timestamp DESC'
    );
} else {
    $records = $DB->get_records_select(
        'openaichat_chatlog',
        'modid = :modid',
        ['modid' => $modid],
        'timestamp DESC'
    );
}

// Add records.
foreach ($records as $record) {

    // Get cmid from modid.
    $cmid = openaichat::get_cmid_from_modid($record->modid);

    $activityname = $DB->get_record('openaichat', ['id' => $record->modid])->name;
    $activityurl = '<a href="' . $CFG->wwwroot . '/mod/openaichat/view.php?id=' . $cmid . '">' . $activityname . '</a>';
    $table->add_data([
        $record->timestamp ? userdate($record->timestamp) : '',
        $record->sesskey,
        $activityurl,
        $record->request,
        $record->response,
    ]);
}
$table->finish_output();

// Print the footer.
if (!$table->is_downloading()) {
    echo $OUTPUT->footer();
}
