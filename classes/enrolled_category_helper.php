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
 * Category and grouping helpers for enrolled-course fallback data.
 *
 * @package   block_academic_dashboard_esse3
 * @copyright 2026 Università degli Studi di Ferrara - Unife
 * @author    Andrea Bertelli <andrea.bertelli@unife.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrolled_category_helper {
    /**
     * Resolve the full category tree path for a course category.
     *
     * @param int $categoryid
     * @return string
     */
    public function get_category_path(int $categoryid): string {
        $segments = $this->get_category_path_segments($categoryid);
        if (empty($segments)) {
            return '';
        }

        return implode(' / ', array_map(function ($segment) {
            return $segment['name'];
        }, $segments));
    }

    /**
     * Check whether a course category belongs to one of the configured roots.
     *
     * @param int $categoryid
     * @param int[] $allowedroots
     * @return bool
     */
    public function is_course_in_allowed_roots(int $categoryid, array $allowedroots): bool {
        if (empty($allowedroots)) {
            return true;
        }

        $pathids = $this->get_category_path_ids($categoryid);
        return !empty(array_intersect($allowedroots, $pathids));
    }

    /**
     * Resolve the primary group label for an enrolled fallback card.
     *
     * @param array $segments
     * @param int $categoryid
     * @param int $depth
     * @param string $groupmode
     * @return string
     */
    public function resolve_primary_enrolled_group(array $segments, int $categoryid, int $depth, string $groupmode): string {
        if ($depth > 0) {
            return $this->resolve_group_name_by_depth($segments, $depth);
        }

        return $this->resolve_enrolled_group_name($categoryid, $groupmode);
    }

    /**
     * Resolve the optional secondary group label for an enrolled fallback card.
     *
     * @param array $segments
     * @param int $categoryid
     * @param int $depth
     * @param string $groupmode
     * @param string $secondarygroupmode
     * @return string
     */
    public function resolve_secondary_enrolled_group(
        array $segments,
        int $categoryid,
        int $depth,
        string $groupmode,
        string $secondarygroupmode
    ): string {
        if ($depth > 0) {
            return $this->resolve_group_name_by_depth($segments, $depth);
        }

        return $this->resolve_secondary_group_name($categoryid, $groupmode, $secondarygroupmode);
    }

    /**
     * Resolve a depth-based label if the configured depth is valid.
     *
     * @param array $segments
     * @param int $depth
     * @return string
     */
    public function resolve_depth_based_group_name(array $segments, int $depth): string {
        if ($depth <= 0) {
            return '';
        }

        return $this->resolve_group_name_by_depth($segments, $depth);
    }

    /**
     * Build tags from remaining category levels below the configured structure depths.
     *
     * @param array $segments
     * @param int $useddepth
     * @return array
     */
    public function build_category_tags(array $segments, int $useddepth): array {
        if (empty($segments)) {
            return [];
        }

        $tags = [];
        foreach ($segments as $segment) {
            if ((int)$segment['depth'] <= $useddepth) {
                continue;
            }

            $tags[] = [
                'name' => $segment['name'],
            ];
        }

        return $tags;
    }

    /**
     * Build tags from the full category path.
     *
     * @param array $segments
     * @return array
     */
    public function build_full_category_tags(array $segments): array {
        if (empty($segments)) {
            return [];
        }

        $tags = [];
        foreach ($segments as $segment) {
            $tags[] = [
                'name' => $segment['name'],
            ];
        }

        return $tags;
    }

    /**
     * Resolve the group label for a course category according to settings.
     *
     * @param int $categoryid
     * @param string $mode
     * @return string
     */
    private function resolve_enrolled_group_name(int $categoryid, string $mode): string {
        switch ($mode) {
            case 'none':
                return get_string('enrolled', 'block_academic_dashboard_esse3');
            case 'parentcategory':
                return $this->get_parent_category_name($categoryid);
            case 'toproot':
                return $this->get_top_root_category_name($categoryid);
            case 'fullpath':
                return $this->get_category_path($categoryid);
            case 'directcategory':
            default:
                return $this->get_direct_category_name($categoryid);
        }
    }

    /**
     * Resolve optional secondary grouping label for a course category.
     *
     * @param int $categoryid
     * @param string $primarymode
     * @param string $secondarymode
     * @return string
     */
    private function resolve_secondary_group_name(int $categoryid, string $primarymode, string $secondarymode): string {
        if ($secondarymode === 'none' || $secondarymode === $primarymode) {
            return '';
        }

        return $this->resolve_enrolled_group_name($categoryid, $secondarymode);
    }

    /**
     * Resolve group label from a specific category depth.
     *
     * @param array $segments
     * @param int $depth
     * @return string
     */
    private function resolve_group_name_by_depth(array $segments, int $depth): string {
        $segment = $this->get_category_segment_by_depth($segments, $depth);
        if (empty($segment)) {
            return get_string('uncategorized', 'block_academic_dashboard_esse3');
        }

        return $segment['name'];
    }

    /**
     * Returns the direct category name assigned to the course.
     *
     * @param int $categoryid
     * @return string
     */
    private function get_direct_category_name(int $categoryid): string {
        $segments = $this->get_category_path_segments($categoryid);
        if (empty($segments)) {
            return get_string('uncategorized', 'block_academic_dashboard_esse3');
        }

        $segment = end($segments);
        return $segment['name'];
    }

    /**
     * Returns the direct parent category name for a course category.
     *
     * @param int $categoryid
     * @return string
     */
    private function get_parent_category_name(int $categoryid): string {
        $segments = $this->get_category_path_segments($categoryid);
        if (empty($segments)) {
            return get_string('uncategorized', 'block_academic_dashboard_esse3');
        }

        if (count($segments) > 1) {
            return $segments[count($segments) - 2]['name'];
        }

        return $segments[0]['name'];
    }

    /**
     * Returns the top-most root category name for a course category.
     *
     * @param int $categoryid
     * @return string
     */
    private function get_top_root_category_name(int $categoryid): string {
        $segments = $this->get_category_path_segments($categoryid);
        if (empty($segments)) {
            return get_string('uncategorized', 'block_academic_dashboard_esse3');
        }

        return $segments[0]['name'];
    }

    /**
     * Get ordered category ids from root to current category.
     *
     * @param int $categoryid
     * @return int[]
     */
    private function get_category_path_ids(int $categoryid): array {
        $segments = $this->get_category_path_segments($categoryid);
        if (empty($segments)) {
            return [];
        }

        return array_map(function ($segment) {
            return (int)$segment['id'];
        }, $segments);
    }

    /**
     * Get ordered category segments from root to current category.
     *
     * @param int $categoryid
     * @return array[]
     */
    public function get_category_path_segments(int $categoryid): array {
        global $DB;

        if ($categoryid <= 0) {
            return [];
        }

        $segments = [];
        $currentid = $categoryid;
        $guard = 0;
        while ($currentid > 0 && $guard < 50) {
            $guard++;
            $cat = $DB->get_record('course_categories', ['id' => $currentid], 'id, parent, name', IGNORE_MISSING);
            if (!$cat) {
                break;
            }
            array_unshift($segments, [
                'id' => (int)$cat->id,
                'name' => format_string($cat->name, true, ['context' => \context_system::instance()]),
            ]);
            $currentid = (int) $cat->parent;
        }

        foreach ($segments as $index => &$segment) {
            $segment['depth'] = $index + 1;
        }
        unset($segment);

        return $segments;
    }

    /**
     * Return a category segment at the requested 1-based depth.
     *
     * @param array $segments
     * @param int $depth
     * @return array|null
     */
    private function get_category_segment_by_depth(array $segments, int $depth): ?array {
        if ($depth <= 0 || empty($segments) || $depth > count($segments)) {
            return null;
        }

        return $segments[$depth - 1];
    }
}
