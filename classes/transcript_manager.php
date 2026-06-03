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

namespace block_academic_dashboard_esse3;

use cache;
use moodle_url;
use context_course;

/**
 * Handle transcript data fetching, caching and preparation.
 *
 * @package   block_academic_dashboard_esse3
 * @copyright 2026 Università degli Studi di Ferrara - Unife
 * @author    Andrea Bertelli <andrea.bertelli@unife.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class transcript_manager {
    /**
     * Create the helper used to merge transcript and enrolled-course collections.
     *
     * @return transcript_course_collection_helper
     */
    private function get_course_collection_helper(): transcript_course_collection_helper {
        return new transcript_course_collection_helper(function (int $courseid): string {
            return $this->get_course_teachers($courseid);
        });
    }

    /**
     * Gets all data required for the transcript template.
     *
     * @param string $username Moodle/institutional username (used as Esse3 userId).
     * @param int $blockid
     * @param int $userid Moodle user id.
     * @return array
     */
    public function get_transcript_template_data($username, $blockid, $userid = 0) {
        $username = trim((string)$username);
        if ($username === '') {
            return $this->get_enrolled_courses_template_data((int)$userid, (int)$blockid);
        }

        $items = $this->get_cached_transcript($username);

        if (empty($items)) {
            // No careers found in Esse3 for this userId: the user is a teacher
            // or staff member, not an enrolled student. Fall back to enrolled-courses view.
            return $this->get_enrolled_courses_template_data((int)$userid, (int)$blockid);
        }

        $mapper = new \block_academic_dashboard_esse3\transcript_mapper();
        $data = $mapper->prepare_template_data($items, $blockid);

        global $DB;
        $this->check_moodle_courses($data['courses'], $DB);
        $helper = $this->get_course_collection_helper();
        $helper->mark_course_source($data['courses'], 'transcript');
        $extraenrolledcourses = $helper->build_extra_enrolled_courses((int)$userid, $data['courses']);
        if (!empty($extraenrolledcourses)) {
            $helper->mark_course_source($extraenrolledcourses, 'enrolledextra');
            $data['courses'] = array_merge($data['courses'], $extraenrolledcourses);
        }
        $this->remove_transcript_courses_without_teacher($data['courses']);
        $this->refresh_transcript_filter_data($data);
        $helper->populate_source_filter_data($data);
        $data['hascourses'] = !empty($data['courses']);
        $data['datasource'] = 'transcript';

        return $data;
    }

    /**
     * Gets transcript items from cache or fetches them from Esse3 if expired.
     *
     * @param string $username Moodle/institutional username (Esse3 userId).
     * @return array
     */
    private function get_cached_transcript($username) {
        $cache = cache::make('block_academic_dashboard_esse3', 'transcript');
        // Cache definition uses simple keys: keep alphanumeric/underscore only.
        $cachekey = 'userid_' . sha1((string)$username);
        $cacheddata = $cache->get($cachekey);

        // Manual TTL: 24 hours.
        $ttl = 24 * 3600;

        if ($cacheddata !== false) {
            if (isset($cacheddata['timestamp'], $cacheddata['data'])) {
                if (time() - $cacheddata['timestamp'] <= $ttl) {
                    return $cacheddata['data'];
                }
            }
            $cache->delete($cachekey);
        }

        $items = $this->fetch_transcript_from_esse3($username);
        if (!empty($items)) {
            $cache->set($cachekey, ['data' => $items, 'timestamp' => time()]);
        }
        return $items;
    }

    /**
     * Fetches transcript items from Esse3 API.
     *
     * @param string $username Moodle/institutional username (Esse3 userId).
     * @return array
     */
    private function fetch_transcript_from_esse3($username) {
        $esse3handler = new \block_academic_dashboard_esse3\local\esse3\esse3_handler();
        $careers = $esse3handler->get_careers_by_userid($username);

        if (empty($careers)) {
            return [];
        }

        $allitems = [];
        foreach ($careers as $career) {
            $allitems = array_merge($allitems, $this->process_career_transcript($career, $esse3handler));
        }

        return $allitems;
    }

    /**
     * Fetches and merges transcript and partitions for a single career.
     *
     * @param \stdClass $career The career object.
     * @param \block_academic_dashboard_esse3\local\esse3\esse3_handler $esse3handler
     * @return array
     */
    private function process_career_transcript($career, $esse3handler) {
        if (!isset($career->matId)) {
            return [];
        }

        $items = $esse3handler->get_transcript($career->matId);
        $partitions = $esse3handler->get_partitions($career->matId);

        $partitionmap = $this->get_partition_map($partitions);

        foreach ($items as $item) {
            $this->enhance_item_with_career_data($item, $career);
            $this->merge_partition_data($item, $partitionmap);
        }

        return $items;
    }

    /**
     * Indexes partitions by chaveAdContestualizzata.
     *
     * @param array $partitions
     * @return array
     */
    private function get_partition_map($partitions) {
        $partitionmap = [];
        foreach ($partitions as $part) {
            if (isset($part->chiaveAdContestualizzata)) {
                $ctx = $part->chiaveAdContestualizzata;
                $key = "{$ctx->aaOffId}-{$ctx->adCod}-{$ctx->cdsCod}";
                $partitionmap[$key] = $part;
            }
        }
        return $partitionmap;
    }

    /**
     * Attaches career information to a transcript item.
     *
     * @param \stdClass $item
     * @param \stdClass $career
     */
    private function enhance_item_with_career_data(&$item, $career) {
        $item->careerMatId = $career->matId;
        if (isset($career->cdsDes)) {
            $item->careerCdsDes = $career->cdsDes;
        }
    }

    /**
     * Merges partition info into a transcript item.
     *
     * @param stdClass $item
     * @param array $partitionmap
     */
    private function merge_partition_data(&$item, $partitionmap) {
        if (!isset($item->chiaveADContestualizzata)) {
            return;
        }

        $ctx = $item->chiaveADContestualizzata;
        $key = "{$ctx->aaOffId}-{$ctx->adCod}-{$ctx->cdsCod}";

        if (isset($partitionmap[$key])) {
            $part = $partitionmap[$key];
            if (isset($part->cognomeDocTit)) {
                $item->docenteDes = trim(($part->nomeDoctit ?? '') . ' ' . $part->cognomeDocTit);
            }
            if (isset($part->partEffDes)) {
                $item->semester = $part->partEffDes;
            }
        }
    }

    /**
     * Check if matching Moodle courses exist.
     *
     * @param array $courses
     * @param \moodle_database $db
     */
    private function check_moodle_courses(&$courses, $db) {
        foreach ($courses as &$course) {
            $this->get_moodle_course_info($course, $db);
            if (!empty($course->teacher)) {
                $course->teacher = \core_text::strtotitle($course->teacher);
            }
        }
    }

    /**
     * Remove transcript-backed courses that still have no reference teacher.
     *
     * @param array $courses
     * @return void
     */
    private function remove_transcript_courses_without_teacher(array &$courses): void {
        $courses = array_values(array_filter($courses, static function ($course): bool {
            if (($course->sourcekey ?? '') !== 'transcript') {
                return true;
            }

            return trim((string)($course->teacher ?? '')) !== '';
        }));
    }

    /**
     * Rebuild transcript year/status filters after hiding transcript courses.
     *
     * @param array $data
     * @return void
     */
    private function refresh_transcript_filter_data(array &$data): void {
        $uniqueyears = [];
        $uniquestatuses = [];

        foreach ($data['courses'] as $course) {
            if (($course->sourcekey ?? '') !== 'transcript') {
                continue;
            }

            if (!empty($course->courseYear)) {
                $uniqueyears[$course->courseYear] = $course->courseYear;
            }
            if (!empty($course->statusDes)) {
                $uniquestatuses[$course->statusDes] = $course->statusDes;
            }
        }

        ksort($uniqueyears);
        ksort($uniquestatuses);

        $data['years'] = array_map(static function ($value): array {
            return ['value' => $value];
        }, array_values($uniqueyears));
        $data['statuses'] = array_map(static function ($value): array {
            return ['value' => $value];
        }, array_values($uniquestatuses));
    }

    /**
     * Checks if a transcript item has a matching Moodle course.
     *
     * @param stdClass $course
     * @param \moodle_database $db
     */
    private function get_moodle_course_info(&$course, $db) {
        global $USER;

        if (empty($course->cdsId) || empty($course->adId) || empty($course->aaFreqId)) {
            $course->hasmoodlecourse = false;
            return;
        }

        $pattern = $course->cdsId . '-' . $course->adId . '-' . $course->aaFreqId;
        $found = $db->get_records_select(
            'course',
            $db->sql_like('idnumber', '?'),
            [$pattern . '%'],
            '',
            'id, fullname, shortname, idnumber',
            0,
            1
        );

        if (!empty($found)) {
            $moodlecourse = reset($found);
            $course->moodlecourseid = $moodlecourse->id;
            $course->moodlecoursename = $moodlecourse->fullname;
            $courseurl = new moodle_url('/course/view.php', ['id' => $moodlecourse->id]);
            $course->moodlecourseurl = $courseurl->out(false);

            $context = context_course::instance($moodlecourse->id, IGNORE_MISSING);
            if ($context) {
                $course->isenrolled = is_enrolled($context, $USER);
                if (empty($course->teacher)) {
                    $course->teacher = $this->get_course_teachers($moodlecourse->id);
                }
            } else {
                $course->isenrolled = false;
            }

            if (!$course->isenrolled) {
                $enrolurl = new moodle_url('/enrol/index.php', ['id' => $moodlecourse->id]);
                $course->enrolurl = $enrolurl->out(false);
            }
            $course->hasmoodlecourse = true;
        } else {
            $course->hasmoodlecourse = false;
        }
    }

    /**
     * Gets course teachers.
     *
     * @param int $courseid
     * @return string
     */
    private function get_course_teachers($courseid) {
        global $CFG;
        require_once($CFG->dirroot . '/course/lib.php');

        $teachers = [];
        $courserecord = get_course($courseid);
        if ($courserecord) {
            $courseelement = new \core_course_list_element($courserecord);
            $contacts = $courseelement->get_course_contacts();
            foreach ($contacts as $contact) {
                if (!empty($contact['username'])) {
                    $teachers[] = $contact['username'];
                }
            }
        }

        if (empty($teachers)) {
            return '';
        }

        $teachers = array_values(array_unique($teachers));
        return implode(', ', $teachers);
    }

    /**
     * Build transcript-like cards from enrolled Moodle courses.
     * Used as fallback when the ESSE3 user ID or careers are missing.
     *
     * @param int $userid
     * @param int $blockid
     * @return array
     */
    private function get_enrolled_courses_template_data(int $userid, int $blockid): array {
        $builder = new enrolled_courses_builder(function (int $courseid): string {
            return $this->get_course_teachers($courseid);
        });
        return $builder->build($userid, $blockid);
    }
}
