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
 * Completion plugin for lytix
 *
 * @package    lytix_completions
 * @author     Viktoria Wieser
 * @copyright  2021 Educational Technologies, Graz, University of Technology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Lytix activity completion';

$string['privacy:metadata'] = 'This plugin does not store any data.';
$string['cachedef_activity_completion'] = 'Cache for the completions subplugin';

// Task.
$string['cron_refresh_lytix_completions_cache'] = "Refresh caches for lytix subplugin completions.";

$string['core'] = "Navigation";
$string['forum'] = "Forum";
$string['grade'] = "Grade";
$string['submission'] = "Submission";
$string['resource'] = "Resource";
$string['quiz'] = "Quiz";
$string['video'] = "Video";
$string['bbb'] = "BigBlueButton";
$string['label'] = "Label";
$string['feedback'] = "Feedback";
$string['assign'] = "Assignment";

$string['activity_statistic'] = 'Activity Completion';
$string['statistic_label_activity'] = 'Activity';
$string['statistic_label_done'] = 'Completed Attempts';
$string['statistic_label_done_abbr'] = 'Compl.';
$string['statistic_label_open'] = 'Open';
$string['statistic_label_total'] = 'Number of Students';
$string['statistic_label_total_abbr'] = 'Total';
$string['statistic_label_name'] = 'Name';
$string['statistic_label_export'] = 'Export CSV';
$string['Alle_abgeschlossen'] = 'All completed:';
$string['statistic_entries_missing'] = 'No activities found…';
$string['Certificates'] = 'Certificates generated:';

// KFG, Activity Completion.
$string['completion_chart_done'] = 'Done';
$string['completion_chart_open'] = 'Open';
$string['completion_filter'] = 'visible Activities';

$string['participants_left'] = ' Participants left';

$string['time'] = 'Time';
$string['clicks'] = 'Clicks';

$string['section_heading_overview'] = 'Overview';
$string['section_heading_details'] = 'Details';

// Privacy.
$string['privacy:nullproviderreason'] = 'This plugin has no database to store user information. It only uses APIs in mod_assign to help with displaying the grading interface.';
