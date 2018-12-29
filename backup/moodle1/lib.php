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
 * Backup handler for Moodle 1.x mchoicefalse2 questions
 *
 * @package    qtype_mchoicefalse2
 * @copyright  2011 David Mudrak <david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * mchoicefalse2 question type conversion handler
 *
 * @copyright  2011 David Mudrak <david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class moodle1_qtype_mchoicefalse2_handler extends moodle1_qtype_handler {

    /**
     * Return the subpaths within a question
     *
     * @return array
     */
    public function get_question_subpaths() {
        return array(
            'ANSWERS/ANSWER',
            'mchoicefalse2',
        );
    }

    /**
     * Appends the mchoicefalse2 specific information to the question
     *
     * @param array $data the question data
     * @param array $raw unused
     */
    public function process_question(array $data, array $raw) {

        // Convert and write the answers first.
        if (isset($data['answers'])) {
            $this->write_answers($data['answers'], $this->pluginname);
        }

        // Convert and write the mchoicefalse2.
        if (!isset($data['mchoicefalse2'])) {
            // This should never happen, but it can do if the 1.9 site contained
            // corrupt data.
            $data['mchoicefalse2'] = array(array(
                'shuffleanswers'                 => 1,
                'correctfeedback'                => '',
                'correctfeedbackformat'          => FORMAT_HTML,
                'incorrectfeedback'              => '',
                'incorrectfeedbackformat'        => FORMAT_HTML,
                'answernumbering'                => 'abc',
                'shownumcorrect'                => 0
            ));
        }
        $this->write_mchoicefalse2($data['mchoicefalse2'], $data['oldqtextformat']);
    }

    /**
     * Converts the mchoicefalse2 info and writes it into question XML
     *
     * @param array $mchoicefalse2s the grouped structure
     * @param int $oldqtextformat - (see moodle1_question_bank_handler::process_question())
     */
    protected function write_mchoicefalse2(array $mchoicefalse2s, $oldqtextformat) {
        global $CFG;

        // The grouped array is supposed to have just one element - let us use foreach anyway
        // just to be sure we do not loose anything.
        foreach ($mchoicefalse2s as $mchoicefalse2) {
            // Append an artificial 'id' attribute (is not included in moodle.xml).
            $mchoicefalse2['id'] = $this->converter->get_nextid();

            // Replay the upgrade step 2009021801.
            $mchoicefalse2['correctfeedbackformat']               = 0;
            $mchoicefalse2['incorrectfeedbackformat']             = 0;

            if ($CFG->texteditors !== 'textarea' and $oldqtextformat == FORMAT_MOODLE) {
                $mchoicefalse2['correctfeedback']         = text_to_html($mchoicefalse2['correctfeedback'], false, false, true);
                $mchoicefalse2['correctfeedbackformat']   = FORMAT_HTML;
                $mchoicefalse2['incorrectfeedback']       = text_to_html($mchoicefalse2['incorrectfeedback'], false, false, true);
                $mchoicefalse2['incorrectfeedbackformat'] = FORMAT_HTML;
            } else {
                $mchoicefalse2['correctfeedbackformat']   = $oldqtextformat;
                $mchoicefalse2['incorrectfeedbackformat'] = $oldqtextformat;
            }

            $this->write_xml('mchoicefalse2', $mchoicefalse2, array('/mchoicefalse2/id'));
        }
    }
}
