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
 * Languages configuration for the block_academic_dashboard_esse3 plugin.
 *
 * @package   block_academic_dashboard_esse3
 * @copyright 2026 Università degli Studi di Ferrara - Unife
 * @author    Andrea Bertelli <andrea.bertelli@unife.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['academic_dashboard_esse3:addinstance'] = 'Add a new Academic Dashboard (Esse3) block';
$string['academic_dashboard_esse3:myaddinstance'] = 'Add a new Academic Dashboard (Esse3) block to the My Moodle page';
$string['accentcolor'] = 'Accent Color';
$string['accentcolor_desc'] = 'Hex code for the accent color used in badges and highlights (e.g., #0073e6)';
$string['ajax_error'] = 'AJAX error: {$a}';
$string['all_statuses'] = 'All statuses';
$string['all_years'] = 'All years';
$string['apitoken'] = 'API Token';
$string['apitoken_desc'] = 'Basic Auth Token for the Esse3 API';
$string['apiurl'] = 'Esse3 API URL';
$string['apiurl_desc'] = 'Base URL for the Esse3 API (e.g., https://<your_domain>/e3rest/api/)';
$string['blocktitle'] = 'Academic Dashboard';
$string['cachedef_transcript'] = 'Student career and transcript data fetched from Esse3';
$string['coursenotavailable'] = 'Course not available on Moodle';
$string['enrol'] = 'Enrol';
$string['enrolled'] = 'Enrolled';
$string['enrolled_not_in_transcript'] = 'Enrolled outside transcript';
$string['enrolled_not_in_transcript_short'] = 'Outside transcript';
$string['error_loading_syllabus'] = 'Error loading syllabus';
$string['filter_by'] = 'Filter by';
$string['gotocourse'] = 'Go to course';
$string['grid_view'] = 'Grid';
$string['groupdepth'] = 'Enrolled courses primary group depth';
$string['groupdepth_desc'] = '1-based category depth used for the first grouping level. Leave empty to fall back to the legacy grouping mode setting.';
$string['groupmode'] = 'Enrolled courses grouping';
$string['groupmode:directcategory'] = 'Direct category';
$string['groupmode:fullpath'] = 'Full category path';
$string['groupmode:none'] = 'No grouping';
$string['groupmode:parentcategory'] = 'Parent category';
$string['groupmode:toproot'] = 'Top root category';
$string['groupmode_desc'] = 'How enrolled courses should be grouped when transcript data is not available.';
$string['in_transcript'] = 'Courses in transcript';
$string['list_view'] = 'List';
$string['no_results'] = 'No courses matching the filters.';
$string['no_syllabus'] = 'No syllabus available.';
$string['nocourses'] = 'No courses found in the transcript.';
$string['parentcourse'] = 'Parent course';
$string['pluginname'] = 'Academic Dashboard (Esse3)';
$string['privacy:invalidmatid'] = 'The requested ESSE3 career is not available for the current user.';
$string['privacy:metadata:esse3'] = 'The Academic Dashboard (Esse3) block sends personal data to the external ESSE3 student information system in order to retrieve transcript and syllabus information for the current user.';
$string['privacy:metadata:esse3:careerid'] = 'The ESSE3 career identifier associated with the current user.';
$string['privacy:metadata:esse3:courseid'] = 'The ESSE3 course or syllabus identifier requested for the current user.';
$string['privacy:metadata:esse3:syllabusdata'] = 'Syllabus information returned by ESSE3 for the current user.';
$string['privacy:metadata:esse3:transcriptdata'] = 'Transcript and academic career information returned by ESSE3 for the current user.';
$string['privacy:metadata:esse3:userid'] = 'The institutional user ID (Moodle username or configured field) sent to Esse3 to identify the current user.';
$string['privacy:missinguserid'] = 'The current user does not have a configured ESSE3 user ID.';
$string['rootcategories'] = 'Allowed root categories';
$string['rootcategories_desc'] = 'Comma-separated list of Moodle course category IDs used to scope enrolled-course fallback. Leave empty to include all categories.';
$string['search'] = 'Search course...';
$string['secondarygroupdepth'] = 'Enrolled courses secondary group depth';
$string['secondarygroupdepth_desc'] = 'Optional 1-based category depth used inside the first group. Leave empty to fall back to the legacy secondary grouping mode setting.';
$string['secondarygroupmode'] = 'Enrolled courses secondary grouping';
$string['secondarygroupmode_desc'] = 'Optional second-level grouping applied inside the first group when transcript data is not available.';
$string['sortcategorydepth'] = 'Enrolled courses sort depth';
$string['sortcategorydepth_desc'] = 'Optional 1-based category depth used to sort enrolled courses. Leave empty to sort by course title.';
$string['sortdirection'] = 'Enrolled courses sort direction';
$string['sortdirection:asc'] = 'Ascending';
$string['sortdirection:desc'] = 'Descending';
$string['sortdirection_desc'] = 'Sorting direction used for enrolled courses and their groups.';
$string['syllabus'] = 'Syllabus';
$string['syllabus_assessment'] = 'Assessment Methods';
$string['syllabus_content'] = 'Contents';
$string['syllabus_loading'] = 'Loading syllabus...';
$string['syllabus_methods'] = 'Teaching Methods';
$string['syllabus_objectives'] = 'Learning Objectives';
$string['syllabus_prerequisites'] = 'Prerequisites';
$string['syllabus_textbooks'] = 'Reference Textbooks';
$string['teacher'] = 'Teacher';
$string['uncategorized'] = 'Uncategorized';
$string['userfield'] = 'Esse3 User ID Field';
$string['userfield_desc'] = 'The Moodle user field whose value is sent to Esse3 as the userId to retrieve the student transcript. Supports standard fields (e.g. username, idnumber) and custom profile fields. Defaults to "username" if left empty.';
