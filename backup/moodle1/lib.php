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
 * Backup handler for Moodle 1.x mchoiceftwo questions
 *
 * @package    qtype_mchoiceftwo
 * @copyright  2011 David Mudrak <david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * mchoiceftwo question type conversion handler
 *
 * @copyright  2011 David Mudrak <david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class moodle1_qtype_mchoiceftwo_handler extends moodle1_qtype_handler {

    /**
     * Return the subpaths within a question
     *
     * @return array
     */
    public function get_question_subpaths() {
        return array(
            'ANSWERS/ANSWER',
            'mchoiceftwo',
        );
    }

    /**
     * Appends the mchoiceftwo specific information to the question
     *
     * @param array $data the question data
     * @param array $raw unused
     */
    public function process_question(array $data, array $raw) {

        // Convert and write the answers first.
        if (isset($data['answers'])) {
            $this->write_answers($data['answers'], $this->pluginname);
        }

        // Convert and write the mchoiceftwo.
        if (!isset($data['mchoiceftwo'])) {
            // This should never happen, but it can do if the 1.9 site contained
            // corrupt data.
            $data['mchoiceftwo'] = array(array(
                'shuffleanswers'                 => 1,
                'correctfeedback'                => '',
                'correctfeedbackformat'          => FORMAT_HTML,
                'incorrectfeedback'              => '',
                'incorrectfeedbackformat'        => FORMAT_HTML,
                'answernumbering'                => 'abc',
                'shownumcorrect'                => 0
            ));
        }
        $this->write_mchoiceftwo($data['mchoiceftwo'], $data['oldqtextformat']);
    }

    /**
     * Converts the mchoiceftwo info and writes it into question XML
     *
     * @param array $mchoiceftwos the grouped structure
     * @param int $oldqtextformat - (see moodle1_question_bank_handler::process_question())
     */
    protected function write_mchoiceftwo(array $mchoiceftwos, $oldqtextformat) {
        global $CFG;

        // The grouped array is supposed to have just one element - let us use foreach anyway
        // just to be sure we do not loose anything.
        foreach ($mchoiceftwos as $mchoiceftwo) {
            // Append an artificial 'id' attribute (is not included in moodle.xml).
            $mchoiceftwo['id'] = $this->converter->get_nextid();

            // Replay the upgrade step 2009021801.
            $mchoiceftwo['correctfeedbackformat']               = 0;
            $mchoiceftwo['incorrectfeedbackformat']             = 0;

            if ($CFG->texteditors !== 'textarea' and $oldqtextformat == FORMAT_MOODLE) {
                $mchoiceftwo['correctfeedback']         = text_to_html($mchoiceftwo['correctfeedback'], false, false, true);
                $mchoiceftwo['correctfeedbackformat']   = FORMAT_HTML;
                $mchoiceftwo['incorrectfeedback']       = text_to_html($mchoiceftwo['incorrectfeedback'], false, false, true);
                $mchoiceftwo['incorrectfeedbackformat'] = FORMAT_HTML;
            } else {
                $mchoiceftwo['correctfeedbackformat']   = $oldqtextformat;
                $mchoiceftwo['incorrectfeedbackformat'] = $oldqtextformat;
            }

            $this->write_xml('mchoiceftwo', $mchoiceftwo, array('/mchoiceftwo/id'));
        }
    }
}
