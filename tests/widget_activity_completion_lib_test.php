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
 * Testcases for statistic
 *
 * @package    lytix_completions
 * @author     Guenther Moser
 * @copyright  2023 Educational Technologies, Graz, University of Technology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lytix_completions;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/mod/assign/externallib.php');
require_once($CFG->dirroot . '/mod/quiz/lib.php');
require_once($CFG->dirroot . '/lib/externallib.php');

use completion_info;
use context_course;
use external_api;
use externallib_advanced_testcase;
use local_lytix\helper\tests;
use stdClass;

/**
 * Activity completion tests.
 * @group learners_corner
 * @coversDefaultClass \lytix_completions\widget_activity_completion_lib
 */
class widget_activity_completion_lib_test extends externallib_advanced_testcase {
    /**
     * Course variable.
     * @var stdClass|null
     */
    private $course = null;

    /**
     * Sets up course for tests.
     */
    public function setUp(): void {
        $this->resetAfterTest(true);
        $this->setAdminUser();
        $now = new \DateTime('now');
        set_config('semester_start', $now->format('Y-m-d'), 'local_lytix');
        // Create course.
        $this->course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        // Add course to config list.
        set_config('course_list', $this->course->id, 'local_lytix');
        // Set platform.
        set_config('platform', 'course_dashboard', 'local_lytix');
    }

    /**
     * Execute activity_completion_get.
     * @return bool|float|int|mixed|string
     * @throws \coding_exception
     * @throws \invalid_parameter_exception
     * @throws \restricted_context_exception
     */
    public function executetask() {
        $context = context_course::instance($this->course->id);

        return widget_activity_completion_lib::activity_completion_get($context->id, $this->course->id);
    }

    /**
     * Create an assign instance.
     * @param int $duedate
     * @param int $allowsubmissionsfromdate
     * @return mixed
     * @throws \coding_exception
     */
    private function create_assign_instance($duedate = 0, $allowsubmissionsfromdate = 0) {
        $dg = $this->getDataGenerator();

        $generator = $dg->get_plugin_generator('mod_assign');
        $params['course'] = $this->course->id;
        $params['assignfeedback_file_enabled'] = 1;
        $params['assignfeedback_comments_enabled'] = 1;
        $params['duedate'] = $duedate;
        $params['completion'] = 2;
        $params['completionpass'] = 1;
        $params['allowsubmissionsfromdate'] = $allowsubmissionsfromdate;
        return $generator->create_instance($params);
    }

    // COMPLETION.

    /**
     * Completes an activity.
     * @param string $modulename
     * @param mixed $module
     * @param null|stdClass $user
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function complete_activity($modulename, $module, $user) {
        $cm = get_coursemodule_from_id($modulename, $module->cmid);
        $completion = new completion_info($this->course);
        $completion->update_state($cm, COMPLETION_COMPLETE, $user->id);
    }

    // Helper for USERS.

    /**
     * Test an empty course.
     * @covers ::activity_completion_get
     * @covers ::activity_completion_get_returns
     * @covers ::activity_completion_get_parameters
     * @covers \lytix_completions\cache\activity_completion::load_activity_completion
     * @covers \lytix_completions\cache\activity_completion::load_for_cache
     * @covers \lytix_completions\cache\activity_completion::get_activity_completion
     * @throws \coding_exception
     * @throws \invalid_parameter_exception
     * @throws \restricted_context_exception
     */
    public function test_empty_course() {
        $context = context_course::instance($this->course->id);
        $result = widget_activity_completion_lib::activity_completion_get($context->id, $this->course->id);
        try {
            external_api::clean_returnvalue(widget_activity_completion_lib::activity_completion_get_returns(), $result);
        } catch (\invalid_response_exception $e) {
            if ($e) {
                self::assertFalse(true, "invalid_responce_exception thorwn.");
            }
        }

        // Basic asserts.
        $this::assertEquals(5, count($result));

        $this->assertTrue(key_exists('Module', $result));
        $this->assertTrue(key_exists('Id', $result));
        $this->assertTrue(key_exists('Name', $result));
        $this->assertTrue(key_exists('Open', $result));
        $this->assertTrue(key_exists('Done', $result));
    }

    /**
     * Test one activity with no completion.
     * @covers ::activity_completion_get
     * @covers ::activity_completion_get_returns
     * @covers ::activity_completion_get_parameters
     * @covers \lytix_completions\cache\activity_completion::load_activity_completion
     * @covers \lytix_completions\cache\activity_completion::load_for_cache
     * @covers \lytix_completions\cache\activity_completion::get_activity_completion
     * @throws \coding_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \restricted_context_exception
     */
    public function test_one_activity() {
        $this->resetAfterTest(true);

        // Create a quiz.
        tests::create_quiz($this->course, 100);

        $result = $this->executetask();

        // Basic asserts.
        $this::assertEquals(5, count($result));

        $this->assertTrue(key_exists('Id', $result));
        $this->assertTrue(key_exists('Module', $result));
        $this->assertTrue(key_exists('Name', $result));
        $this->assertTrue(key_exists('Open', $result));
        $this->assertTrue(key_exists('Done', $result));

        // One activity.
        self::assertEquals(1, count($result['Module']));
        self::assertEquals(1, count($result['Id']));
        self::assertEquals(1, count($result['Name']));
        self::assertEquals(1, count($result['Open']));
        self::assertEquals(1, count($result['Done']));

        // No student has completed this activity.
        self::assertEquals(0, $result['Open'][0]);
        self::assertEquals(0, $result['Done'][0]);

        external_api::clean_returnvalue(widget_activity_completion_lib::activity_completion_get_returns(), $result);
    }

    /**
     * Test one activity with one completion.
     * @covers ::activity_completion_get
     * @covers ::activity_completion_get_returns
     * @covers ::activity_completion_get_parameters
     * @covers \lytix_completions\cache\activity_completion::load_activity_completion
     * @covers \lytix_completions\cache\activity_completion::load_for_cache
     * @covers \lytix_completions\cache\activity_completion::get_activity_completion
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \moodle_exception
     * @throws \restricted_context_exception
     */
    public function test_one_activity_completed() {
        $this->resetAfterTest(true);

        $teacher = tests::create_enrol_teacher($this->course);

        $student = tests::create_enrol_student($this->course, 'student1@example.com');
        self::assertNotNull($student);

        // Create a quiz.
        $quiz = tests::create_quiz($this->course, 100);
        // Create a numerical question.
        $quizobj = tests::create_quiz_question($this->course, $quiz, $teacher, 50);
        $timenow = time();
        // Start the attempt.
        $attempt = tests::create_quiz_attempt($quizobj, $student, $timenow, '3.14');
        // Finish the passing attempt.
        tests::finish_quiz_attempt($attempt, $timenow);

        $this->complete_activity('quiz', $quiz, $student);

        $result = $this->executetask();

        // Basic asserts.
        $this::assertEquals(5, count($result));
        $this->assertTrue(key_exists('Module', $result));
        $this->assertTrue(key_exists('Id', $result));
        $this->assertTrue(key_exists('Name', $result));
        $this->assertTrue(key_exists('Open', $result));
        $this->assertTrue(key_exists('Done', $result));

        // One compelted activity.
        self::assertEquals(1, count($result['Id']));
        self::assertEquals(1, count($result['Module']));
        self::assertEquals(1, count($result['Name']));
        self::assertEquals(1, count($result['Open']));
        self::assertEquals(1, count($result['Done']));

        // One student has completed this activity.
        self::assertEquals(0, $result['Open'][0]);
        self::assertEquals(1, $result['Done'][0]);

        external_api::clean_returnvalue(widget_activity_completion_lib::activity_completion_get_returns(), $result);
    }

    /**
     * Test one activity with two students (one completed and one not completed).
     * @covers ::activity_completion_get
     * @covers ::activity_completion_get_returns
     * @covers ::activity_completion_get_parameters
     * @covers \lytix_completions\cache\activity_completion::load_activity_completion
     * @covers \lytix_completions\cache\activity_completion::load_for_cache
     * @covers \lytix_completions\cache\activity_completion::get_activity_completion
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \moodle_exception
     * @throws \restricted_context_exception
     */
    public function test_one_activity_mixed() {
        $this->resetAfterTest(true);

        $teacher = tests::create_enrol_teacher($this->course);

        $passstudent = tests::create_enrol_student($this->course, 'pass@example.com');
        self::assertNotNull($passstudent);

        $failstudent = tests::create_enrol_student($this->course, 'fail@example.com');
        self::assertNotNull($failstudent);

        // Create a quiz.
        $quiz = tests::create_quiz($this->course, 100);
        // Create a numerical question.
        $quizobj = tests::create_quiz_question($this->course, $quiz, $teacher, 50);
        $timenow = time();
        // Start the passing attempt.
        $attempt = tests::create_quiz_attempt($quizobj, $passstudent, $timenow, '3.14');
        // Finish the passing attempt.
        tests::finish_quiz_attempt($attempt, $timenow);

        $this->complete_activity('quiz', $quiz, $passstudent);

        // Start the failing attempt.
        $attempt = tests::create_quiz_attempt($quizobj, $failstudent, $timenow, '0');
        // Finish the failing attempt.
        tests::finish_quiz_attempt($attempt, $timenow);

        $result = $this->executetask();

        // Basic asserts.
        $this::assertEquals(5, count($result));

        $this->assertTrue(key_exists('Module', $result));
        $this->assertTrue(key_exists('Id', $result));
        $this->assertTrue(key_exists('Name', $result));
        $this->assertTrue(key_exists('Open', $result));
        $this->assertTrue(key_exists('Done', $result));

        // One activity mixed.
        self::assertEquals(1, count($result['Module']));
        self::assertEquals(1, count($result['Id']));
        self::assertEquals(1, count($result['Name']));
        self::assertEquals(1, count($result['Open']));
        self::assertEquals(1, count($result['Done']));

        // One completed, one not.
        self::assertEquals(1, $result['Open'][0]);
        self::assertEquals(1, $result['Done'][0]);

        external_api::clean_returnvalue(widget_activity_completion_lib::activity_completion_get_returns(), $result);
    }

    /**
     * Test two activites with only one completed.
     * @covers ::activity_completion_get
     * @covers ::activity_completion_get_returns
     * @covers ::activity_completion_get_parameters
     * @covers \lytix_completions\cache\activity_completion::load_activity_completion
     * @covers \lytix_completions\cache\activity_completion::load_for_cache
     * @covers \lytix_completions\cache\activity_completion::get_activity_completion
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \moodle_exception
     * @throws \restricted_context_exception
     */
    public function test_two_activities_only_one_completed() {
        $this->resetAfterTest(true);

        $teacher = tests::create_enrol_teacher($this->course);

        $student = tests::create_enrol_student($this->course, 'pass@example.com');
        self::assertNotNull($student);

        // Create a quiz.
        $quiz = tests::create_quiz($this->course, 100);
        // Create a numerical question.
        $quizobj = tests::create_quiz_question($this->course, $quiz, $teacher, 50);
        $timenow = time();
        // Start the passing attempt.
        $attempt = tests::create_quiz_attempt($quizobj, $student, $timenow, '3.14');
        // Finish the passing attempt.
        tests::finish_quiz_attempt($attempt, $timenow);

        $this->complete_activity('quiz', $quiz, $student);

        // Create assign instance.
        $instance = $this->create_assign_instance();
        // Create an assignment.
        $assign = tests::create_assignment($this->course, $instance);
        self::assertNotNull($assign);

        $data = $this->executetask();

        // Basic asserts.
        $this::assertEquals(5, count($data));

        $this->assertTrue(key_exists('Module', $data));
        $this->assertTrue(key_exists('Id', $data));
        $this->assertTrue(key_exists('Name', $data));
        $this->assertTrue(key_exists('Open', $data));
        $this->assertTrue(key_exists('Done', $data));
        // Tow activities.
        self::assertEquals(2, count($data['Module']));
        self::assertEquals(2, count($data['Id']));
        self::assertEquals(2, count($data['Name']));
        self::assertEquals(2, count($data['Open']));
        self::assertEquals(2, count($data['Done']));

        // One student has completed this activity.
        self::assertEquals(0, $data['Open'][0]);
        self::assertEquals(1, $data['Done'][0]);

        // No student has completed this activity.
        self::assertEquals(1, $data['Open'][1]);
        self::assertEquals(0, $data['Done'][1]);

        external_api::clean_returnvalue(widget_activity_completion_lib::activity_completion_get_returns(), $data);
    }

    /**
     * Test twp activites with two students -> one activity completed by both students.
     * @covers ::activity_completion_get
     * @covers ::activity_completion_get_returns
     * @covers ::activity_completion_get_parameters
     * @covers \lytix_completions\cache\activity_completion::load_activity_completion
     * @covers \lytix_completions\cache\activity_completion::load_for_cache
     * @covers \lytix_completions\cache\activity_completion::get_activity_completion
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \moodle_exception
     * @throws \restricted_context_exception
     */
    public function test_more_students_completed() {
        $this->resetAfterTest(true);

        $teacher = tests::create_enrol_teacher($this->course);

        $student1 = tests::create_enrol_student($this->course, 'pass@example.com');
        self::assertNotNull($student1);

        $student2 = tests::create_enrol_student($this->course, 'fail@example.com');
        self::assertNotNull($student2);

        // Create a quiz.
        $quiz = tests::create_quiz($this->course, 100);
        // Create a numerical question.
        $quizobj = tests::create_quiz_question($this->course, $quiz, $teacher, 50);
        $timenow = time();

        // Start the passing attempt.
        $attempt = tests::create_quiz_attempt($quizobj, $student1, $timenow, '3.14');
        // Finish the passing attempt.
        tests::finish_quiz_attempt($attempt, $timenow);

        // Start the passing attempt.
        $attempt1 = tests::create_quiz_attempt($quizobj, $student2, $timenow, '3.14');
        // Finish the passing attempt.
        tests::finish_quiz_attempt($attempt1, $timenow);

        $this->complete_activity('quiz', $quiz, $student1);
        $this->complete_activity('quiz', $quiz, $student2);

        // Create assign instance.
        $instance = $this->create_assign_instance();
        // Create an assignment.
        $assign = tests::create_assignment($this->course, $instance);
        self::assertNotNull($assign);

        $this->complete_activity('assign', $instance, $student1);
        $this->complete_activity('assign', $instance, $student2);

        $result = $this->executetask();

        // Basic asserts.
        $this::assertEquals(5, count($result));

        $this->assertTrue(key_exists('Module', $result));
        $this->assertTrue(key_exists('Id', $result));
        $this->assertTrue(key_exists('Name', $result));
        $this->assertTrue(key_exists('Open', $result));
        $this->assertTrue(key_exists('Done', $result));
        // Tow activities.
        self::assertEquals(2, count($result['Module']));
        self::assertEquals(2, count($result['Id']));
        self::assertEquals(2, count($result['Name']));
        self::assertEquals(2, count($result['Open']));
        self::assertEquals(2, count($result['Done']));

        // Two students have completed this activity.
        self::assertEquals(0, $result['Open'][0]);
        self::assertEquals(2, $result['Done'][0]);

        // No student has completed this activity.
        self::assertEquals(0, $result['Open'][1]);
        self::assertEquals(2, $result['Done'][1]);

        external_api::clean_returnvalue(widget_activity_completion_lib::activity_completion_get_returns(), $result);
    }
}
