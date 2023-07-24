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
 *
 *  *
 * @package    lytix_completions
 * @category   task
 * @author     Guenther Moser
 * @copyright  2021 Educational Technologies, Graz, University of Technology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lytix_completions\task;
use lytix_completions\cache\activity_completion;
use lytix_completions\cache\cache_reset;
// Important to get libraries here, else we get a conflict with the unit-tests.
// Note that we do NOT need to use global $CFG.
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/course/lib.php');

/**
 * Refresh KFG Cache.
 */
class refresh_completions_cache extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('cron_refresh_lytix_completions_cache', 'lytix_completions');
    }

    /**
     * Refresh cache.
     * @throws \dml_exception
     */
    public function execute() {
        if (get_config('local_lytix', 'platform') == 'course_dashboard') {
            $courseids = explode(',', get_config('local_lytix', 'course_list'));
            foreach ($courseids as $courseid) {
                if (!$courseid) {
                    continue;
                }
                if (cache_reset::reset_cache((int)$courseid)) {
                    echo "There was an error deleting the cache for course $courseid.";
                }
                if (!activity_completion::load_activity_completion((int)$courseid)) {
                    echo "There was an error creating the caches for course $courseid.";
                }
            }
        }
    }
}
