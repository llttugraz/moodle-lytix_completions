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
 * A course overview and filter plugin
 *
 * @package    lytix_completions
 * @author     Güntgher Moser
 * @copyright  2021 Educational Technologies, Graz, University of Technology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lytix_completions\cache;

use cache_definition;
use context_course;
use core\plugininfo\filter;
use dml_exception;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/completionlib.php');

/**
 * Activity completion cache.
 */
class activity_completion implements \cache_data_source {
    /**
     * Instance.
     *
     * @var null
     */
    protected static $instance = null;

    /**
     * Gets instance for cache.
     *
     * @param cache_definition $definition
     * @return activity_completion|object|null
     */
    public static function get_instance_for_cache(cache_definition $definition) {
        if (is_null(self::$instance)) {
            self::$instance = new activity_completion();
        }
        return self::$instance;
    }

    /**
     * Necessary function.
     *
     * @param int|string $key
     * @return array|mixed
     */
    public function load_for_cache($key) {
        return self::get_activity_completion($key);
    }

    /**
     * Necessary function.
     *
     * @param array $keys
     * @return array
     */
    public function load_many_for_cache(array $keys) {
        $courses = [];
        foreach ($keys as $key) {
            if ($course = $this->load_for_cache($key)) {
                $courses[$course->id] = $course;
            }
        }
        return $courses;
    }

    /**
     * Necessary function.
     *
     * @param int $courseid
     * @return bool|float|int|mixed|string
     * @throws \coding_exception
     */
    public static function load_activity_completion($courseid) {
        $cache = \cache::make('lytix_completions', 'activity_completion');
        return $cache->get($courseid);
    }

    /**
     * Gets activity completion cache.
     *
     * @param int $courseid
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    private static function get_activity_completion($courseid) {
        global $CFG, $DB;

        try {
            $course = get_course($courseid);
        } catch (dml_exception $e) {
            return [$e->getMessage()];
        }

        $coursecontext = context_course::instance($courseid);
        $studentroleid = $DB->get_record('role', ['shortname' => 'student'], '*')->id; // Must exist.
        $students = get_role_users($studentroleid, $coursecontext);

        $modules = [];
        $ids = [];
        $names = [];
        $open = [];
        $done = [];
        $completioninfo = new \completion_info($course);

        $quizzes = get_coursemodules_in_course('quiz', $courseid);
        $feedbacks = get_coursemodules_in_course('feedback', $courseid);
        $resources = get_coursemodules_in_course('resource', $courseid);
        $assigns = get_coursemodules_in_course('assign', $courseid);

        $cmodules = array_merge($quizzes, $feedbacks, $resources, $assigns);

        // Iterate m = moules.
        foreach ($cmodules as $module) {
            $completetotal = 0;
            if ($completioninfo->is_enabled($module) != COMPLETION_TRACKING_NONE) {
                // Iterate u = users.
                foreach ($students as $student) {
                    $completiondata = $completioninfo->get_data($module, true, $student->id);
                    if ($completiondata->completionstate == COMPLETION_COMPLETE
                        || $completiondata->completionstate == COMPLETION_COMPLETE_PASS) {
                        $completetotal++;
                    }
                }
                $modules[] = (string)$module->modname;
                $ids[] = $module->id;
                $names[] = format_string($module->name);
                $open[] = count($students) - $completetotal;
                $done[] = $completetotal;
            }
        }
        $data['Module'] = $modules;
        $data['Id'] = $ids;
        $data['Name'] = $names;
        $data['Open'] = $open;
        $data['Done'] = $done;

        return $data;
    }
}
