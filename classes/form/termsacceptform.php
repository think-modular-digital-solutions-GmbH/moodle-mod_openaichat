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
 * Contains the terms of use acceptance form
 *
 * @package    mod_openaichat
 * @copyright  2024 think modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

namespace mod_openaichat\form;

/**
 * Terms of use acceptance form
 *
 * @package    mod_openaichat
 */
class termsacceptform extends \moodleform {
    /**
     * Form definition
     */
    protected function definition() {
        global $PAGE;

        $this->_form->addElement('hidden', 'id', $PAGE->cm->id);
        $this->_form->setType('id', PARAM_INT);

        $this->_form->addElement('html', get_string('termsofuse', 'mod_openaichat'));

        $buttons = [];
        $buttons[] = $this->_form->createElement('submit', 'termsaccept', get_string('termsaccept', 'mod_openaichat'));
        $buttons[] = $this->_form->createElement('submit', 'termsdecline', get_string('termsdecline', 'mod_openaichat'));
        $this->_form->addGroup($buttons, 'termsbuttons', '', ' ', false);
    }
}
