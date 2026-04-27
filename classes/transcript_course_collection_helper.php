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

/**
 * Helper for combining transcript and enrolled-course collections.
 *
 * @package   block_academic_dashboard_esse3
 * @copyright 2026 Università degli Studi di Ferrara - Unife
 * @author    Andrea Bertelli <andrea.bertelli@unife.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class transcript_course_collection_helper {
    /** @var callable */
    private $teacherresolver;

    /**
     * Constructor.
     *
     * @param callable $teacherresolver
     */
    public function __construct(callable $teacherresolver) {
        $this->teacherresolver = $teacherresolver;
    }

    /**
     * Build enrolled Moodle cards that are not already matched from transcript courses.
     *
     * @param int $userid
     * @param array $transcriptcourses
     * @return array
     */
    public function build_extra_enrolled_courses(int $userid, array $transcriptcourses): array {
        if ($userid <= 0) {
            return [];
        }

        $builder = new enrolled_courses_builder($this->teacherresolver);
        $courses = $builder->build_course_cards($userid);
        $matchedcourseids = $this->extract_matched_course_ids($transcriptcourses);

        if (empty($matchedcourseids)) {
            return $courses;
        }

        $matchedcourseids = array_fill_keys($matchedcourseids, true);
        return array_values(array_filter($courses, function ($course) use ($matchedcourseids) {
            return empty($matchedcourseids[(int)($course->moodlecourseid ?? 0)]);
        }));
    }

    /**
     * Attach source metadata used by the transcript template filters.
     *
     * @param array $courses
     * @param string $sourcekey
     * @return void
     */
    public function mark_course_source(array &$courses, string $sourcekey): void {
        foreach ($courses as &$course) {
            $course->sourcekey = $sourcekey;
            $course->issourcetranscript = ($sourcekey === 'transcript');
            $course->issourceenrolledextra = ($sourcekey === 'enrolledextra');
        }
        unset($course);
    }

    /**
     * Add source-filter metadata to the transcript template context.
     *
     * @param array $data
     * @return void
     */
    public function populate_source_filter_data(array &$data): void {
        $transcriptcount = 0;
        $enrolledextracount = 0;

        foreach ($data['courses'] as $course) {
            if (($course->sourcekey ?? '') === 'enrolledextra') {
                $enrolledextracount++;
            } else {
                $transcriptcount++;
            }
        }

        $data['transcriptcoursecount'] = $transcriptcount;
        $data['extracoursecount'] = $enrolledextracount;
        $data['hasoriginfilters'] = ($transcriptcount > 0 && $enrolledextracount > 0);
    }

    /**
     * Extract matched Moodle course ids from transcript-backed cards.
     *
     * @param array $transcriptcourses
     * @return array
     */
    private function extract_matched_course_ids(array $transcriptcourses): array {
        $matchedcourseids = [];
        foreach ($transcriptcourses as $course) {
            if (!empty($course->moodlecourseid)) {
                $matchedcourseids[] = (int)$course->moodlecourseid;
            }
        }

        return $matchedcourseids;
    }
}
