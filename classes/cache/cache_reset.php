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
 * @author     Günther Moser
 * @copyright  2021 Educational Technologies, Graz, University of Technology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lytix_completions\cache;

/**
 * Cache Reset.
 */
class cache_reset {
    /**
     * Resets activity completion cache.
     * @param int $courseid
     * @throws \coding_exception
     */
    public static function reset_cache($courseid) {
        // Reset Statistic.
        $cache = \cache::make('lytix_completions', 'activity_completion');
        if ($cache->get($courseid)) {
            return $cache->delete($courseid);
        }
        return true;
    }
}
