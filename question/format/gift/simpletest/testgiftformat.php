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
 * Unit tests for the Moodle GIFT format.
 *
 * @package qformat_xml
 * @copyright 2010 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/format.php');
require_once($CFG->dirroot . '/question/format/gift/format.php');


/**
 * Unit tests for the GIFT import/export format.
 *
 * @copyright 2010 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qformat_gift_test extends UnitTestCase {
    public function assert_same_gift($expectedtext, $text) {
        $this->assertEqual(str_replace("\r\n", "\n", $expectedtext),
                str_replace("\r\n", "\n", $text));
    }

    public function test_import_essay() {
        $gift = '
// essay
::Q8:: How are you? {}';
        $lines = preg_split('/[\\n\\r]/', str_replace("\r\n", "\n", $gift));

        $importer = new qformat_gift();
        $q = $importer->readquestion($lines);

        $expectedq = (object) array(
            'name' => 'Q8',
            'questiontext' => 'How are you?',
            'questiontextformat' => FORMAT_MOODLE,
            'generalfeedback' => '',
            'generalfeedbackformat' => FORMAT_MOODLE,
            'qtype' => 'essay',
            'defaultgrade' => 1,
            'penalty' => 0.1,
            'length' => 1,
            'feedback' => array(
                'text' => '',
                'format' => FORMAT_MOODLE,
                'files' => array(),
            ),
        );

        $this->assert(new CheckSpecifiedFieldsExpectation($expectedq), $q);
    }

    public function test_export_essay() {
        $qdata = (object) array(
            'id' => 666 ,
            'name' => 'Q8',
            'questiontext' => 'How are you?',
            'questiontextformat' => FORMAT_MOODLE,
            'generalfeedback' => '',
            'generalfeedbackformat' => FORMAT_MOODLE,
            'defaultgrade' => 1,
            'penalty' => 0.1,
            'length' => 1,
            'qtype' => 'essay',
            'options' => (object) array(
                'answers' => array(
                    123 => (object) array(
                        'id' => 123,
                        'answer' => 666,
                        'answerformat' => FORMAT_MOODLE,
                        'fraction' => 0,
                        'feedback' => '',
                        'feedbackformat' => FORMAT_MOODLE,
                    ),
                ),
            ),
        );

        $exporter = new qformat_gift();
        $gift = $exporter->writequestion($qdata);

        $expectedgift = "// question: 666  name: Q8
::Q8::How are you?{}

";

        $this->assert_same_gift($expectedgift, $gift);
    }

    public function test_import_match() {
        $gift = '
// question: 2  name: Moodle activities
::Moodle activities::[html]Match the <b>activity</b> to the description.{
    =[html]An activity supporting asynchronous discussions. -> Forum
    =[moodle]A teacher asks a question and specifies a choice of multiple responses. -> Choice
    =[plain]A bank of record entries which participants can add to. -> Database
    =[markdown]A collection of web pages that anyone can add to or edit. -> Wiki
    = -> Chat
}';
        $lines = preg_split('/[\\n\\r]/', str_replace("\r\n", "\n", $gift));

        $importer = new qformat_gift();
        $q = $importer->readquestion($lines);

        $expectedq = (object) array(
            'name' => 'Moodle activities',
            'questiontext' => 'Match the <b>activity</b> to the description.',
            'questiontextformat' => FORMAT_HTML,
            'generalfeedback' => '',
            'generalfeedbackformat' => FORMAT_HTML,
            'qtype' => 'match',
            'defaultgrade' => 1,
            'penalty' => 0.1,
            'length' => 1,
            'shuffleanswers' => '1',
            'subquestions' => array(
                0 => array(
                    'text' => 'An activity supporting asynchronous discussions.',
                    'format' => FORMAT_HTML,
                    'files' => array(),
                ),
                1 => array(
                    'text' => 'A teacher asks a question and specifies a choice of multiple responses.',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                2 => array(
                    'text' => 'A bank of record entries which participants can add to.',
                    'format' => FORMAT_PLAIN,
                    'files' => array(),
                ),
                3 => array(
                    'text' => 'A collection of web pages that anyone can add to or edit.',
                    'format' => FORMAT_MARKDOWN,
                    'files' => array(),
                ),
                4 => array(
                    'text' => '',
                    'format' => FORMAT_HTML,
                    'files' => array(),
                ),
            ),
            'subanswers' => array(
                0 => 'Forum',
                1 => 'Choice',
                2 => 'Database',
                3 => 'Wiki',
                4 => 'Chat',
            ),
        );

        // Repeated test for better failure messages.
        $this->assertEqual($expectedq->subquestions, $q->subquestions);
        $this->assert(new CheckSpecifiedFieldsExpectation($expectedq), $q);
    }

    public function test_export_match() {
        $qdata = (object) array(
            'id' => 666 ,
            'name' => 'Moodle activities',
            'questiontext' => 'Match the <b>activity</b> to the description.',
            'questiontextformat' => FORMAT_HTML,
            'generalfeedback' => '',
            'generalfeedbackformat' => FORMAT_HTML,
            'defaultgrade' => 1,
            'penalty' => 0.1,
            'length' => 1,
            'qtype' => 'match',
            'options' => (object) array(
                'id' => 123,
                'question' => 666,
                'shuffleanswers' => 1,
                'subquestions' => array(
                    42 => (object) array(
                        'id' => 1234,
                        'code' => 12341234,
                        'question' => 666,
                        'questiontext' => 'An activity supporting asynchronous discussions.',
                        'questiontextformat' => FORMAT_HTML,
                        'answertext' => 'Forum',
                    ),
                    43 => (object) array(
                        'id' => 1234,
                        'code' => 12341234,
                        'question' => 666,
                        'questiontext' => 'A teacher asks a question and specifies a choice of multiple responses.',
                        'questiontextformat' => FORMAT_MOODLE,
                        'answertext' => 'Choice',
                    ),
                    44 => (object) array(
                        'id' => 1234,
                        'code' => 12341234,
                        'question' => 666,
                        'questiontext' => 'A bank of record entries which participants can add to.',
                        'questiontextformat' => FORMAT_PLAIN,
                        'answertext' => 'Database',
                    ),
                    45 => (object) array(
                        'id' => 1234,
                        'code' => 12341234,
                        'question' => 666,
                        'questiontext' => 'A collection of web pages that anyone can add to or edit.',
                        'questiontextformat' => FORMAT_MARKDOWN,
                        'answertext' => 'Wiki',
                    ),
                    46 => (object) array(
                        'id' => 1234,
                        'code' => 12341234,
                        'question' => 666,
                        'questiontext' => '',
                        'questiontextformat' => FORMAT_MARKDOWN,
                        'answertext' => 'Chat',
                    ),
                ),
            ),
        );

        $exporter = new qformat_gift();
        $gift = $exporter->writequestion($qdata);

        $expectedgift = "// question: 666  name: Moodle activities
::Moodle activities::[html]Match the <b>activity</b> to the description.{
\t=An activity supporting asynchronous discussions. -> Forum
\t=[moodle]A teacher asks a question and specifies a choice of multiple responses. -> Choice
\t=[plain]A bank of record entries which participants can add to. -> Database
\t=[markdown]A collection of web pages that anyone can add to or edit. -> Wiki
\t= -> Chat
}

";

        $this->assert_same_gift($expectedgift, $gift);
    }

    public function test_import_multichoice() {
        $gift = "
// multiple choice with specified feedback for right and wrong answers
::Q2:: What's between orange and green in the spectrum?
{
    =yellow # right; good!
    ~red # [html]wrong, it's yellow
    ~[plain]blue # wrong, it's yellow
}";
        $lines = preg_split('/[\\n\\r]/', str_replace("\r\n", "\n", $gift));

        $importer = new qformat_gift();
        $q = $importer->readquestion($lines);

        $expectedq = (object) array(
            'name' => 'Q2',
            'questiontext' => "What's between orange and green in the spectrum?",
            'questiontextformat' => FORMAT_MOODLE,
            'generalfeedback' => '',
            'generalfeedbackformat' => FORMAT_MOODLE,
            'qtype' => 'multichoice',
            'defaultgrade' => 1,
            'penalty' => 0.1,
            'length' => 1,
            'single' => 1,
            'shuffleanswers' => '1',
            'answernumbering' => 'abc',
            'correctfeedback' => array(
                'text' => '',
                'format' => FORMAT_MOODLE,
                'files' => array(),
            ),
            'partiallycorrectfeedback' => array(
                'text' => '',
                'format' => FORMAT_MOODLE,
                'files' => array(),
            ),
            'incorrectfeedback' => array(
                'text' => '',
                'format' => FORMAT_MOODLE,
                'files' => array(),
            ),
            'answer' => array(
                0 => array(
                    'text' => 'yellow',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                1 => array(
                    'text' => 'red',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                2 => array(
                    'text' => 'blue',
                    'format' => FORMAT_PLAIN,
                    'files' => array(),
                ),
            ),
            'fraction' => array(1, 0, 0),
            'feedback' => array(
                0 => array(
                    'text' => 'right; good!',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                1 => array(
                    'text' => "wrong, it's yellow",
                    'format' => FORMAT_HTML,
                    'files' => array(),
                ),
                2 => array(
                    'text' => "wrong, it's yellow",
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
            ),
        );

        // Repeated test for better failure messages.
        $this->assertEqual($expectedq->answer, $q->answer);
        $this->assertEqual($expectedq->feedback, $q->feedback);
        $this->assert(new CheckSpecifiedFieldsExpectation($expectedq), $q);
    }

    public function test_import_multichoice_multi() {
        $gift = "
// multiple choice, multiple response with specified feedback for right and wrong answers
::colours:: What's between orange and green in the spectrum?
{
    ~%50%yellow # right; good!
    ~%-100%red # [html]wrong
    ~%50%off-beige # right; good!
    ~%-100%[plain]blue # wrong
}";
        $lines = preg_split('/[\\n\\r]/', str_replace("\r\n", "\n", $gift));

        $importer = new qformat_gift();
        $q = $importer->readquestion($lines);

        $expectedq = (object) array(
            'name' => 'colours',
            'questiontext' => "What's between orange and green in the spectrum?",
            'questiontextformat' => FORMAT_MOODLE,
            'generalfeedback' => '',
            'generalfeedbackformat' => FORMAT_MOODLE,
            'qtype' => 'multichoice',
            'defaultgrade' => 1,
            'penalty' => 0.1,
            'length' => 1,
            'single' => 0,
            'shuffleanswers' => '1',
            'answernumbering' => 'abc',
            'correctfeedback' => array(
                'text' => '',
                'format' => FORMAT_MOODLE,
                'files' => array(),
            ),
            'partiallycorrectfeedback' => array(
                'text' => '',
                'format' => FORMAT_MOODLE,
                'files' => array(),
            ),
            'incorrectfeedback' => array(
                'text' => '',
                'format' => FORMAT_MOODLE,
                'files' => array(),
            ),
            'answer' => array(
                0 => array(
                    'text' => 'yellow',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                1 => array(
                    'text' => 'red',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                2 => array(
                    'text' => 'off-beige',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                3 => array(
                    'text' => 'blue',
                    'format' => FORMAT_PLAIN,
                    'files' => array(),
                ),
            ),
            'fraction' => array(0.5, -1, 0.5, -1),
            'feedback' => array(
                0 => array(
                    'text' => 'right; good!',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                1 => array(
                    'text' => "wrong",
                    'format' => FORMAT_HTML,
                    'files' => array(),
                ),
                2 => array(
                    'text' => "right; good!",
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                3 => array(
                    'text' => "wrong",
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
            ),
        );

        // Repeated test for better failure messages.
        $this->assertEqual($expectedq->answer, $q->answer);
        $this->assertEqual($expectedq->feedback, $q->feedback);
        $this->assert(new CheckSpecifiedFieldsExpectation($expectedq), $q);
    }

    public function test_export_multichoice() {
        $qdata = (object) array(
            'id' => 666 ,
            'name' => 'Q8',
            'questiontext' => "What's between orange and green in the spectrum?",
            'questiontextformat' => FORMAT_MOODLE,
            'generalfeedback' => '',
            'generalfeedbackformat' => FORMAT_MOODLE,
            'defaultgrade' => 1,
            'penalty' => 0.1,
            'length' => 1,
            'qtype' => 'multichoice',
            'options' => (object) array(
                'single' => 1,
                'shuffleanswers' => '1',
                'answernumbering' => 'abc',
                'correctfeedback' => '',
                'correctfeedbackformat' => FORMAT_MOODLE,
                'partiallycorrectfeedback' => '',
                'partiallycorrectfeedbackformat' => FORMAT_MOODLE,
                'incorrectfeedback' => '',
                'incorrectfeedbackformat' => FORMAT_MOODLE,
                'answers' => array(
                    123 => (object) array(
                        'id' => 123,
                        'answer' => 'yellow',
                        'answerformat' => FORMAT_MOODLE,
                        'fraction' => 1,
                        'feedback' => 'right; good!',
                        'feedbackformat' => FORMAT_MOODLE,
                    ),
                    124 => (object) array(
                        'id' => 124,
                        'answer' => 'red',
                        'answerformat' => FORMAT_MOODLE,
                        'fraction' => 0,
                        'feedback' => "wrong, it's yellow",
                        'feedbackformat' => FORMAT_HTML,
                    ),
                    125 => (object) array(
                        'id' => 125,
                        'answer' => 'blue',
                        'answerformat' => FORMAT_PLAIN,
                        'fraction' => 0,
                        'feedback' => "wrong, it's yellow",
                        'feedbackformat' => FORMAT_MOODLE,
                    ),
                ),
            ),
        );

        $exporter = new qformat_gift();
        $gift = $exporter->writequestion($qdata);

        $expectedgift = "// question: 666  name: Q8
::Q8::What's between orange and green in the spectrum?{
\t=yellow#right; good!
\t~red#[html]wrong, it's yellow
\t~[plain]blue#wrong, it's yellow
}

";

        $this->assert_same_gift($expectedgift, $gift);
    }

    public function test_import_numerical() {
        $gift = "
// math range question
::Q5:: What is a number from 1 to 5? {#3:2~#Completely wrong}";
        $lines = preg_split('/[\\n\\r]/', str_replace("\r\n", "\n", $gift));

        $importer = new qformat_gift();
        $q = $importer->readquestion($lines);

        $expectedq = (object) array(
            'name' => 'Q5',
            'questiontext' => "What is a number from 1 to 5?",
            'questiontextformat' => FORMAT_MOODLE,
            'generalfeedback' => '',
            'generalfeedbackformat' => FORMAT_MOODLE,
            'qtype' => 'numerical',
            'defaultgrade' => 1,
            'penalty' => 0.1,
            'length' => 1,
            'answer' => array(
                '3',
                '*',
            ),
            'fraction' => array(1, 0),
            'feedback' => array(
                0 => array(
                    'text' => '',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                1 => array(
                    'text' => "Completely wrong",
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
            ),
            'tolerance' => array(2, 0),
        );

        // Repeated test for better failure messages.
        $this->assertEqual($expectedq->answer, $q->answer);
        $this->assertEqual($expectedq->fraction, $q->fraction);
        $this->assertEqual($expectedq->feedback, $q->feedback);
        $this->assert(new CheckSpecifiedFieldsExpectation($expectedq), $q);
    }

    public function test_export_numerical() {
        $qdata = (object) array(
            'id' => 666 ,
            'name' => 'Q5',
            'questiontext' => "What is a number from 1 to 5?",
            'questiontextformat' => FORMAT_MOODLE,
            'generalfeedback' => '',
            'generalfeedbackformat' => FORMAT_MOODLE,
            'defaultgrade' => 1,
            'penalty' => 1,
            'length' => 1,
            'qtype' => 'numerical',
            'options' => (object) array(
                'id' => 123,
                'question' => 666,
                'instructions' => '',
                'instructionsformat' => FORMAT_MOODLE,
                'showunits' => 0,
                'unitsleft' => 0,
                'showunits' => 2,
                'unitgradingtype' => 0,
                'unitpenalty' => 0,
                'answers' => array(
                    1 => (object) array(
                        'id' => 123,
                        'answer' => '3',
                        'answerformat' => 0,
                        'fraction' => 1,
                        'tolerance' => 2,
                        'feedback' => '',
                        'feedbackformat' => FORMAT_MOODLE,
                    ),
                    2 => (object) array(
                        'id' => 124,
                        'answer' => '*',
                        'answerformat' => 0,
                        'fraction' => 0,
                        'tolerance' => 0,
                        'feedback' => "Completely wrong",
                        'feedbackformat' => FORMAT_MOODLE,
                    ),
                ),
            ),
        );

        $exporter = new qformat_gift();
        $gift = $exporter->writequestion($qdata);

        $expectedgift = "// question: 666  name: Q5
::Q5::What is a number from 1 to 5?{#
\t=%100%3:2#
\t~#Completely wrong
}

";

        $this->assert_same_gift($expectedgift, $gift);
    }

    public function test_import_shortanswer() {
        $gift = "
// question: 666  name: Shortanswer
::Shortanswer::Which is the best animal?{
    =Frog#Good!
    =%50%Cat#What is it with Moodlers and cats?
    =%0%*#Completely wrong
}";
        $lines = preg_split('/[\\n\\r]/', str_replace("\r\n", "\n", $gift));

        $importer = new qformat_gift();
        $q = $importer->readquestion($lines);

        $expectedq = (object) array(
            'name' => 'Shortanswer',
            'questiontext' => "Which is the best animal?",
            'questiontextformat' => FORMAT_MOODLE,
            'generalfeedback' => '',
            'generalfeedbackformat' => FORMAT_MOODLE,
            'qtype' => 'shortanswer',
            'defaultgrade' => 1,
            'penalty' => 0.1,
            'length' => 1,
            'answer' => array(
                'Frog',
                'Cat',
                '*',
            ),
            'fraction' => array(1, 0.5, 0),
            'feedback' => array(
                0 => array(
                    'text' => 'Good!',
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                1 => array(
                    'text' => "What is it with Moodlers and cats?",
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
                2 => array(
                    'text' => "Completely wrong",
                    'format' => FORMAT_MOODLE,
                    'files' => array(),
                ),
            ),
        );

        // Repeated test for better failure messages.
        $this->assertEqual($expectedq->answer, $q->answer);
        $this->assertEqual($expectedq->fraction, $q->fraction);
        $this->assertEqual($expectedq->feedback, $q->feedback);
        $this->assert(new CheckSpecifiedFieldsExpectation($expectedq), $q);
    }

    public function test_export_shortanswer() {
        $qdata = (object) array(
            'id' => 666 ,
            'name' => 'Shortanswer',
            'questiontext' => "Which is the best animal?",
            'questiontextformat' => FORMAT_MOODLE,
            'generalfeedback' => '',
            'generalfeedbackformat' => FORMAT_MOODLE,
            'defaultgrade' => 1,
            'penalty' => 1,
            'length' => 1,
            'qtype' => 'shortanswer',
            'options' => (object) array(
                'id' => 123,
                'question' => 666,
                'usecase' => 1,
                'answers' => array(
                    1 => (object) array(
                        'id' => 1,
                        'answer' => 'Frog',
                        'answerformat' => 0,
                        'fraction' => 1,
                        'feedback' => 'Good!',
                        'feedbackformat' => FORMAT_MOODLE,
                    ),
                    2 => (object) array(
                        'id' => 2,
                        'answer' => 'Cat',
                        'answerformat' => 0,
                        'fraction' => 0.5,
                        'feedback' => "What is it with Moodlers and cats?",
                        'feedbackformat' => FORMAT_MOODLE,
                    ),
                    3 => (object) array(
                        'id' => 3,
                        'answer' => '*',
                        'answerformat' => 0,
                        'fraction' => 0,
                        'feedback' => "Completely wrong",
                        'feedbackformat' => FORMAT_MOODLE,
                    ),
                ),
            ),
        );

        $exporter = new qformat_gift();
        $gift = $exporter->writequestion($qdata);

        $expectedgift = "// question: 666  name: Shortanswer
::Shortanswer::Which is the best animal?{
\t=%100%Frog#Good!
\t=%50%Cat#What is it with Moodlers and cats?
\t=%0%*#Completely wrong
}

";

        $this->assert_same_gift($expectedgift, $gift);
    }

    public function test_import_truefalse() {
        $gift = "
// true/false
::Q1:: 42 is the Absolute Answer to everything.{
FALSE#42 is the Ultimate Answer.#You gave the right answer.}";
        $lines = preg_split('/[\\n\\r]/', str_replace("\r\n", "\n", $gift));

        $importer = new qformat_gift();
        $q = $importer->readquestion($lines);

        $expectedq = (object) array(
            'name' => 'Q1',
            'questiontext' => "42 is the Absolute Answer to everything.",
            'questiontextformat' => FORMAT_MOODLE,
            'generalfeedback' => '',
            'generalfeedbackformat' => FORMAT_MOODLE,
            'qtype' => 'truefalse',
            'defaultgrade' => 1,
            'penalty' => 1,
            'length' => 1,
            'correctanswer' => 0,
            'feedbacktrue' => array(
                'text' => '42 is the Ultimate Answer.',
                'format' => FORMAT_MOODLE,
                'files' => array(),
            ),
            'feedbackfalse' => array(
                'text' => 'You gave the right answer.',
                'format' => FORMAT_MOODLE,
                'files' => array(),
            ),
        );

        $this->assert(new CheckSpecifiedFieldsExpectation($expectedq), $q);
    }

    public function test_import_truefalse_true_answer1() {
        $gift = "// name 0-11
::2-08 TSL::TSL is blablabla.{T}";
        $lines = preg_split('/[\\n\\r]/', str_replace("\r\n", "\n", $gift));

        $importer = new qformat_gift();
        $q = $importer->readquestion($lines);

        $expectedq = (object) array(
            'name' => '2-08 TSL',
            'questiontext' => "TSL is blablabla.",
            'questiontextformat' => FORMAT_MOODLE,
            'generalfeedback' => '',
            'generalfeedbackformat' => FORMAT_MOODLE,
            'qtype' => 'truefalse',
            'defaultgrade' => 1,
            'penalty' => 1,
            'length' => 1,
            'correctanswer' => 1,
            'feedbacktrue' => array(
                'text' => '',
                'format' => FORMAT_MOODLE,
                'files' => array(),
            ),
            'feedbackfalse' => array(
                'text' => '',
                'format' => FORMAT_MOODLE,
                'files' => array(),
            ),
        );

        $this->assert(new CheckSpecifiedFieldsExpectation($expectedq), $q);
    }

    public function test_import_truefalse_true_answer2() {
        $gift = "// name 0-11
::2-08 TSL::TSL is blablabla.{TRUE}";
        $lines = preg_split('/[\\n\\r]/', str_replace("\r\n", "\n", $gift));

        $importer = new qformat_gift();
        $q = $importer->readquestion($lines);

        $expectedq = (object) array(
            'name' => '2-08 TSL',
            'questiontext' => "TSL is blablabla.",
            'questiontextformat' => FORMAT_MOODLE,
            'generalfeedback' => '',
            'generalfeedbackformat' => FORMAT_MOODLE,
            'qtype' => 'truefalse',
            'defaultgrade' => 1,
            'penalty' => 1,
            'length' => 1,
            'correctanswer' => 1,
            'feedbacktrue' => array(
                'text' => '',
                'format' => FORMAT_MOODLE,
                'files' => array(),
            ),
            'feedbackfalse' => array(
                'text' => '',
                'format' => FORMAT_MOODLE,
                'files' => array(),
            ),
        );

        $this->assert(new CheckSpecifiedFieldsExpectation($expectedq), $q);
    }

    public function test_export_truefalse() {
        $qdata = (object) array(
            'id' => 666 ,
            'name' => 'Q1',
            'questiontext' => "42 is the Absolute Answer to everything.",
            'questiontextformat' => FORMAT_MOODLE,
            'generalfeedback' => '',
            'generalfeedbackformat' => FORMAT_MOODLE,
            'defaultgrade' => 1,
            'penalty' => 1,
            'length' => 1,
            'qtype' => 'truefalse',
            'options' => (object) array(
                'id' => 123,
                'question' => 666,
                'trueanswer' => 1,
                'falseanswer' => 2,
                'answers' => array(
                    1 => (object) array(
                        'id' => 123,
                        'answer' => 'True',
                        'answerformat' => 0,
                        'fraction' => 1,
                        'feedback' => 'You gave the right answer.',
                        'feedbackformat' => FORMAT_MOODLE,
                    ),
                    2 => (object) array(
                        'id' => 124,
                        'answer' => 'False',
                        'answerformat' => 0,
                        'fraction' => 0,
                        'feedback' => "42 is the Ultimate Answer.",
                        'feedbackformat' => FORMAT_HTML,
                    ),
                ),
            ),
        );

        $exporter = new qformat_gift();
        $gift = $exporter->writequestion($qdata);

        $expectedgift = "// question: 666  name: Q1
::Q1::42 is the Absolute Answer to everything.{TRUE#[html]42 is the Ultimate Answer.#You gave the right answer.}

";

        $this->assert_same_gift($expectedgift, $gift);
    }
}
