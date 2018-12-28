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
 * Backup handler for Moodle 1.x okimultiplechoicefalse2 questions
 *
 * @package    qtype_okimultiplechoicefalse2
 * @copyright  2011 David Mudrak <david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * okimultiplechoicefalse2 question type conversion handler
 *
 * @copyright  2011 David Mudrak <david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class moodle1_qtype_okimultiplechoicefalse2_handler extends moodle1_qtype_handler {

    /**
     * Return the subpaths within a question
     *
     * @return array
     */
    public function get_question_subpaths() {
        return array(
            'ANSWERS/ANSWER',
            'okimultiplechoicefalse2',
        );
    }

    /**
     * Appends the okimultiplechoicefalse2 specific information to the question
     *
     * @param array $data the question data
     * @param array $raw unused
     */
    public function process_question(array $data, array $raw) {

        // Convert and write the answers first.
        if (isset($data['answers'])) {
            $this->write_answers($data['answers'], $this->pluginname);
        }

        // Convert and write the okimultiplechoicefalse2.
        if (!isset($data['okimultiplechoicefalse2'])) {
            // This should never happen, but it can do if the 1.9 site contained
            // corrupt data.
            $data['okimultiplechoicefalse2'] = array(array(
                'shuffleanswers'                 => 1,
                'correctfeedback'                => '',
                'correctfeedbackformat'          => FORMAT_HTML,
                'incorrectfeedback'              => '',
                'incorrectfeedbackformat'        => FORMAT_HTML,
                'answernumbering'                => 'abc',
                'shownumcorrect'                => 0
            ));
        }
        $this->write_okimultiplechoicefalse2($data['okimultiplechoicefalse2'], $data['oldqtextformat']);
    }

    /**
     * Converts the okimultiplechoicefalse2 info and writes it into question XML
     *
     * @param array $okimultiplechoicefalse2s the grouped structure
     * @param int $oldqtextformat - (see moodle1_question_bank_handler::process_question())
     */
    protected function write_okimultiplechoicefalse2(array $okimultiplechoicefalse2s, $oldqtextformat) {
        global $CFG;

        // The grouped array is supposed to have just one element - let us use foreach anyway
        // just to be sure we do not loose anything.
        foreach ($okimultiplechoicefalse2s as $okimultiplechoicefalse2) {
            // Append an artificial 'id' attribute (is not included in moodle.xml).
            $okimultiplechoicefalse2['id'] = $this->converter->get_nextid();

            // Replay the upgrade step 2009021801.
            $okimultiplechoicefalse2['correctfeedbackformat']               = 0;
            $okimultiplechoicefalse2['incorrectfeedbackformat']             = 0;

            if ($CFG->texteditors !== 'textarea' and $oldqtextformat == FORMAT_MOODLE) {
                $okimultiplechoicefalse2['correctfeedback']         = text_to_html($okimultiplechoicefalse2['correctfeedback'], false, false, true);
                $okimultiplechoicefalse2['correctfeedbackformat']   = FORMAT_HTML;
                $okimultiplechoicefalse2['incorrectfeedback']       = text_to_html($okimultiplechoicefalse2['incorrectfeedback'], false, false, true);
                $okimultiplechoicefalse2['incorrectfeedbackformat'] = FORMAT_HTML;
            } else {
                $okimultiplechoicefalse2['correctfeedbackformat']   = $oldqtextformat;
                $okimultiplechoicefalse2['incorrectfeedbackformat'] = $oldqtextformat;
            }

            $this->write_xml('okimultiplechoicefalse2', $okimultiplechoicefalse2, array('/okimultiplechoicefalse2/id'));
        }
    }
}
