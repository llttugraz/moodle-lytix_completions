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
 * This is a one-line short description of the file.
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    lytix_completions
 * @author     Guenther Moser <moser@tugraz.at>
 * @copyright  2021 Educational Technologies, Graz, University of Technology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lytix_completions;

use context_course;
use lytix_completions\cache\activity_completion;

/**
 * Activity Completion Lib.
 */
class widget_activity_completion_lib extends \external_api {
    /**
     * Check parameters.
     * @return \external_function_parameters
     */
    public static function activity_completion_get_parameters() {
        return new \external_function_parameters(
            [
                'contextid' => new \external_value(PARAM_INT, 'Context Id', VALUE_REQUIRED),
                'courseid' => new \external_value(PARAM_INT, 'Course Id', VALUE_REQUIRED),
            ]
        );
    }

    /**
     * Checks return values.
     * @return \external_single_structure
     */
    public static function activity_completion_get_returns() {
        return new \external_single_structure(
            [
                'Module' => new \external_multiple_structure(
                    new \external_value(PARAM_TEXT, 'type of the module', VALUE_REQUIRED)
                ),
                'Id' => new \external_multiple_structure(
                    new \external_value(PARAM_INT, 'id of the module', VALUE_REQUIRED)
                ),
                'Name' => new \external_multiple_structure(
                    new \external_value(PARAM_TEXT, 'name of the module', VALUE_REQUIRED)
                ),
                'Open' => new \external_multiple_structure(
                    new \external_value(PARAM_INT, 'number of student not completed the module', VALUE_REQUIRED)
                ),
                'Done' => new \external_multiple_structure(
                    new \external_value(PARAM_INT, 'number of student completed the module', VALUE_REQUIRED)
                )
            ]
        );
    }

    /**
     * Gets activity completion.
     * @param int $contextid
     * @param int $courseid
     * @return bool|float|int|mixed|string
     * @throws \coding_exception
     * @throws \invalid_parameter_exception
     * @throws \restricted_context_exception
     */
    public static function activity_completion_get($contextid, $courseid) {
        $params = self::validate_parameters(self::activity_completion_get_parameters(), [
            'contextid' => $contextid,
            'courseid' => $courseid
        ]);

        // We always must call validate_context in a webservice.
        $context = \context::instance_by_id($params['contextid'], MUST_EXIST);
        self::validate_context($context);

        return activity_completion::load_activity_completion($courseid);
    }
}
