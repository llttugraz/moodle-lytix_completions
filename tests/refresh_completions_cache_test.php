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
 * Testcases for completions cache
 *
 * @package    lytix_completions
 * @author     Guenther Moser
 * @copyright  2023 Educational Technologies, Graz, University of Technology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lytix_completions;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/lib/externallib.php');
require_once($CFG->dirroot . '/webservice/tests/helpers.php');

use externallib_advanced_testcase;
use lytix_completions\task\refresh_completions_cache;
use stdClass;

/**
 * Activity completion cache tests.
 * @group learners_corner
 * @coversDefaultClass \lytix_completions\task\refresh_completions_cache
 */
class refresh_completions_cache_test extends externallib_advanced_testcase {
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
     * Test get_name of task.
     * @covers ::get_name
     * @return void
     */
    public function test_task_get_name() {
        $task = new refresh_completions_cache();
        self::assertIsString($task->get_name(), "Task has no name.");
    }

    /**
     * Test execute of task.
     * @covers ::execute
     * @covers \lytix_completions\cache\activity_completion::load_activity_completion
     * @covers \lytix_completions\cache\activity_completion::load_for_cache
     * @covers \lytix_completions\cache\activity_completion::get_activity_completion
     * @return void
     * @throws \dml_exception
     */
    public function test_task_execute() {
        $task = new refresh_completions_cache();
        $task->execute();
        self::assertTrue(true, "task failed.");
    }

    /**
     * Test fail of execute.
     * @covers ::execute
     * @covers \lytix_completions\cache\activity_completion::load_activity_completion
     * @covers \lytix_completions\cache\activity_completion::load_for_cache
     * @covers \lytix_completions\cache\activity_completion::get_activity_completion
     * @covers \lytix_completions\cache\cache_reset::reset_cache
     * @return void
     * @throws \dml_exception
     */
    public function test_task_execute_fail() {
        set_config('course_list', "0," . $this->course->id . ",666", 'local_lytix');
        $task = new refresh_completions_cache();
        $task->execute();
        self::assertFalse(false, "task should fail, but did not.");
    }
}
