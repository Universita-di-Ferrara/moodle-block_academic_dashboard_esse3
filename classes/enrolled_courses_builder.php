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

use moodle_url;

/**
 * Build enrolled-course fallback data for the block template.
 *
 * @package   block_academic_dashboard_esse3
 * @copyright 2026 Università degli Studi di Ferrara - Unife
 * @author    Andrea Bertelli <andrea.bertelli@unife.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrolled_courses_builder {
    /** @var callable */
    private $teacherresolver;
    /** @var enrolled_category_helper */
    private $categoryhelper;
    /** @var enrolled_grouping_settings */
    private $settingshelper;

    /**
     * Constructor.
     *
     * @param callable $teacherresolver
     */
    public function __construct(callable $teacherresolver) {
        $this->teacherresolver = $teacherresolver;
        $this->categoryhelper = new enrolled_category_helper();
        $this->settingshelper = new enrolled_grouping_settings();
    }

    /**
     * Build transcript-like cards from enrolled Moodle courses.
     *
     * @param int $userid
     * @param int $blockid
     * @return array
     */
    public function build(int $userid, int $blockid): array {
        $options = $this->settingshelper->get_enrolled_display_options();
        $courses = $this->build_enrolled_course_cards($userid, $options);
        if (empty($courses)) {
            return [];
        }

        $this->sort_courses($courses, $options['sortdirection']);
        $hasgroups = $this->has_enrolled_groups($options);

        return [
            'courses' => $courses,
            'groupedcategories' => $this->build_enrolled_grouped_categories($courses, $options, $hasgroups),
            'hasgroups' => $hasgroups,
            'hascourses' => true,
            'years' => [],
            'statuses' => [],
            'blockid' => $blockid,
            'datasource' => 'enrolled',
        ];
    }

    /**
     * Build transcript-like cards from all Moodle courses the user is enrolled in.
     *
     * @param int $userid
     * @return array
     */
    public function build_course_cards(int $userid): array {
        $options = $this->settingshelper->get_enrolled_display_options();
        return $this->build_enrolled_course_cards($userid, $options);
    }

    /**
     * Build transcript-like cards from all Moodle courses the user is enrolled in.
     *
     * @param int $userid
     * @param array $options
     * @return array
     */
    private function build_enrolled_course_cards(int $userid, array $options): array {
        global $CFG;
        require_once($CFG->dirroot . '/lib/enrollib.php');

        if ($userid <= 0) {
            return [];
        }

        $enrolledcourses = enrol_get_all_users_courses($userid, true, 'id, fullname, shortname, idnumber, category');
        if (empty($enrolledcourses)) {
            return [];
        }

        $courses = [];
        foreach ($enrolledcourses as $moodlecourse) {
            $course = $this->build_enrolled_course_card($moodlecourse, $options);
            if ($course !== null) {
                $courses[] = $course;
            }
        }

        return $courses;
    }

    /**
     * Build a transcript-like course card from an enrolled Moodle course.
     *
     * @param \stdClass $moodlecourse
     * @param array $options
     * @return \stdClass|null
     */
    private function build_enrolled_course_card(\stdClass $moodlecourse, array $options): ?\stdClass {
        if ((int)$moodlecourse->id === SITEID) {
            return null;
        }

        if (
            !$this->categoryhelper->is_course_in_allowed_roots(
                (int)$moodlecourse->category,
                $options['allowedroots']
            )
        ) {
            return null;
        }

        $categoryid = (int)$moodlecourse->category;
        $categorysegments = $this->categoryhelper->get_category_path_segments($categoryid);
        $effectivedepths = $this->get_effective_depth_configuration(
            $categorysegments,
            $options['groupdepth'],
            $options['secondarygroupdepth'],
            $options['sortdepth']
        );

        $course = $this->create_enrolled_course_base($moodlecourse, $categoryid);
        $course->categorytags = $this->categoryhelper->build_category_tags(
            $categorysegments,
            max($effectivedepths['groupdepth'], $effectivedepths['secondarygroupdepth'])
        );
        $course->hascategorytags = !empty($course->categorytags);
        $course->fullcategorytags = $this->categoryhelper->build_full_category_tags($categorysegments);
        $course->hasfullcategorytags = !empty($course->fullcategorytags);
        $course->searchtext = $this->build_enrolled_course_searchtext($course);
        $course->sortlabel = $this->categoryhelper->resolve_depth_based_group_name(
            $categorysegments,
            $effectivedepths['sortdepth']
        );
        $course->categorygroup = $this->categoryhelper->resolve_primary_enrolled_group(
            $categorysegments,
            $categoryid,
            $effectivedepths['groupdepth'],
            $options['groupmode']
        );
        $course->secondarycategorygroup = $this->categoryhelper->resolve_secondary_enrolled_group(
            $categorysegments,
            $categoryid,
            $effectivedepths['secondarygroupdepth'],
            $options['groupmode'],
            $options['secondarygroupmode']
        );

        return $course;
    }

    /**
     * Create the base view model shared by all enrolled fallback cards.
     *
     * @param \stdClass $moodlecourse
     * @param int $categoryid
     * @return \stdClass
     */
    private function create_enrolled_course_base(\stdClass $moodlecourse, int $categoryid): \stdClass {
        $teacherresolver = $this->teacherresolver;
        $course = new \stdClass();
        $course->adDes = $moodlecourse->fullname;
        $course->adCod = $moodlecourse->idnumber ?: $moodlecourse->shortname;
        $course->adsceId = '';
        $course->careerMatId = '';
        $course->courseYear = '';
        $course->statusDes = get_string('enrolled', 'block_academic_dashboard_esse3');
        $course->status = 'E';
        $course->weight = '';
        $course->careerCdsDes = '';
        $course->cdsDes = $this->categoryhelper->get_category_path($categoryid);
        $course->cdsCod = '';
        $course->adId = '';
        $course->cdsId = '';
        $course->hassyllabus = false;
        $course->teacher = $teacherresolver((int)$moodlecourse->id);
        $course->semester = '';
        $course->passed = false;
        $course->hasmoodlecourse = true;
        $course->isenrolled = true;

        $courseurl = new moodle_url('/course/view.php', ['id' => $moodlecourse->id]);
        $course->moodlecourseurl = $courseurl->out(false);
        $course->moodlecourseid = (int)$moodlecourse->id;
        $course->moodlecoursename = $moodlecourse->fullname;

        return $course;
    }

    /**
     * Build searchable text for an enrolled fallback card.
     *
     * @param \stdClass $course
     * @return string
     */
    private function build_enrolled_course_searchtext(\stdClass $course): string {
        $tagsearch = '';
        if (!empty($course->categorytags)) {
            $tagsearch = ' ' . implode(' ', array_map(function ($tag) {
                return $tag['name'];
            }, $course->categorytags));
        }

        $searchcontent = $course->adDes . ' ' . $course->adCod . ' ' . $course->teacher . ' ' . $course->cdsDes . $tagsearch;
        return strtolower(trim($searchcontent));
    }

    /**
     * Normalize configured depth settings against the actual category path.
     *
     * @param array $segments
     * @param int $groupdepth
     * @param int $secondarygroupdepth
     * @param int $sortdepth
     * @return array
     */
    private function get_effective_depth_configuration(
        array $segments,
        int $groupdepth,
        int $secondarygroupdepth,
        int $sortdepth
    ): array {
        $maxdepth = count($segments);

        $groupdepth = $this->normalize_depth_setting($groupdepth, $maxdepth);
        $secondarygroupdepth = $this->normalize_depth_setting($secondarygroupdepth, $maxdepth);
        $sortdepth = $this->normalize_depth_setting($sortdepth, $maxdepth);

        if ($groupdepth > 0 && $secondarygroupdepth > 0 && $secondarygroupdepth <= $groupdepth) {
            $secondarygroupdepth = 0;
        }

        return [
            'groupdepth' => $groupdepth,
            'secondarygroupdepth' => $secondarygroupdepth,
            'sortdepth' => $sortdepth,
        ];
    }

    /**
     * Normalize a depth setting against the available category path depth.
     *
     * @param int $depth
     * @param int $maxdepth
     * @return int
     */
    private function normalize_depth_setting(int $depth, int $maxdepth): int {
        if ($depth <= 0 || $depth > $maxdepth) {
            return 0;
        }

        return $depth;
    }

    /**
     * Determine whether enrolled fallback cards should be grouped.
     *
     * @param array $options
     * @return bool
     */
    private function has_enrolled_groups(array $options): bool {
        return ($options['groupdepth'] > 0) || ($options['groupmode'] !== 'none');
    }

    /**
     * Build grouped categories for enrolled fallback cards.
     *
     * @param array $courses
     * @param array $options
     * @param bool $hasgroups
     * @return array
     */
    private function build_enrolled_grouped_categories(array $courses, array $options, bool $hasgroups): array {
        if (!$hasgroups) {
            return [];
        }

        $groupedmap = [];
        foreach ($courses as $course) {
            $groupname = $course->categorygroup;
            if (!isset($groupedmap[$groupname])) {
                $groupedmap[$groupname] = [];
            }
            $groupedmap[$groupname][] = $course;
        }

        $this->sort_map_by_keys($groupedmap);

        $groupedcategories = [];
        foreach ($groupedmap as $groupname => $groupcourses) {
            $subgroups = $this->build_visible_enrolled_subgroups($groupcourses, $groupname, $options);
            $groupedcategories[] = [
                'groupname' => $groupname,
                'groupid' => 'enrolled-group-' . substr(sha1((string)$groupname), 0, 12),
                'courses' => array_values($groupcourses),
                'hassubgroups' => !empty($subgroups),
                'subgroups' => $subgroups,
            ];
        }

        return $groupedcategories;
    }

    /**
     * Build visible subgroups for an enrolled category group.
     *
     * @param array $groupcourses
     * @param string $groupname
     * @param array $options
     * @return array
     */
    private function build_visible_enrolled_subgroups(array $groupcourses, string $groupname, array $options): array {
        if (!$this->should_build_enrolled_subgroups($options)) {
            return [];
        }

        $subgroups = $this->build_enrolled_subgroups($groupcourses, $groupname);
        if (count($subgroups) <= 1) {
            return [];
        }

        return $subgroups;
    }

    /**
     * Determine whether second-level grouping is enabled for enrolled cards.
     *
     * @param array $options
     * @return bool
     */
    private function should_build_enrolled_subgroups(array $options): bool {
        return $options['secondarygroupdepth'] > 0 || $options['secondarygroupmode'] !== 'none';
    }

    /**
     * Build second-level group structures for enrolled fallback cards.
     *
     * @param array $groupcourses
     * @param string $groupname
     * @return array
     */
    private function build_enrolled_subgroups(array $groupcourses, string $groupname): array {
        $subgroupmap = [];
        foreach ($groupcourses as $course) {
            $subgroupname = $course->secondarycategorygroup ?: $groupname;
            if (!isset($subgroupmap[$subgroupname])) {
                $subgroupmap[$subgroupname] = [];
            }
            $subgroupmap[$subgroupname][] = $course;
        }

        if (count($subgroupmap) <= 1) {
            return [];
        }

        $this->sort_map_by_keys($subgroupmap);

        $subgroups = [];
        $isfirst = true;
        foreach ($subgroupmap as $subgroupname => $subgroupcourses) {
            $subgrouphash = sha1($groupname . '|' . $subgroupname);
            $subgroups[] = [
                'subgroupname' => $subgroupname,
                'subgroupid' => 'enrolled-subgroup-' . substr($subgrouphash, 0, 12),
                'subgrouppaneid' => 'enrolled-subgroup-pane-' . substr($subgrouphash, 0, 12),
                'subgroupbuttonid' => 'enrolled-subgroup-button-' . substr($subgrouphash, 0, 12),
                'isactive' => $isfirst,
                'courses' => array_values($subgroupcourses),
            ];
            $isfirst = false;
        }

        return $subgroups;
    }

    /**
     * Sort enrolled courses with optional depth-based label and course-title fallback.
     *
     * @param array $courses
     * @param string $direction
     * @return void
     */
    private function sort_courses(array &$courses, string $direction): void {
        usort($courses, function ($a, $b) use ($direction) {
            $leftlabel = (string)($a->sortlabel ?? '');
            $rightlabel = (string)($b->sortlabel ?? '');

            if ($leftlabel !== '' || $rightlabel !== '') {
                $comparison = strcasecmp($leftlabel, $rightlabel);
                if ($comparison !== 0) {
                    return ($direction === 'desc') ? -$comparison : $comparison;
                }
            }

            $comparison = strcasecmp((string)$a->adDes, (string)$b->adDes);
            return ($direction === 'desc') ? -$comparison : $comparison;
        });
    }

    /**
     * Sort a string-keyed map alphabetically by key.
     *
     * @param array $map
     * @return void
     */
    private function sort_map_by_keys(array &$map): void {
        uksort($map, function ($left, $right) {
            return strcasecmp((string)$left, (string)$right);
        });
    }
}
