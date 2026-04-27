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
 * Read grouping-related settings for enrolled-course fallback data.
 *
 * @package   block_academic_dashboard_esse3
 * @copyright 2026 Università degli Studi di Ferrara - Unife
 * @author    Andrea Bertelli <andrea.bertelli@unife.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrolled_grouping_settings {
    /**
     * Gather enrolled-course display options from plugin settings.
     *
     * @return array
     */
    public function get_enrolled_display_options(): array {
        $groupmode = $this->get_enrolled_group_mode();
        $groupdepth = $this->get_enrolled_group_depth();

        return [
            'allowedroots' => $this->get_allowed_root_category_ids(),
            'groupdepth' => $groupdepth,
            'secondarygroupdepth' => $this->get_enrolled_secondary_group_depth($groupdepth),
            'sortdepth' => $this->get_enrolled_sort_depth(),
            'sortdirection' => $this->get_enrolled_sort_direction(),
            'groupmode' => $groupmode,
            'secondarygroupmode' => $this->get_secondary_enrolled_group_mode($groupmode),
        ];
    }

    /**
     * Parse allowed root category ids from plugin settings.
     *
     * @return int[]
     */
    private function get_allowed_root_category_ids(): array {
        $raw = (string) (get_config('block_academic_dashboard_esse3', 'rootcategories') ?: '');
        if (trim($raw) === '') {
            return [];
        }

        $parts = preg_split('/[\s,;]+/', $raw);
        $ids = [];
        foreach ($parts as $part) {
            $id = (int) trim((string) $part);
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }

    /**
     * Get grouping strategy from plugin settings.
     *
     * @return string
     */
    private function get_enrolled_group_mode(): string {
        $mode = (string) (get_config('block_academic_dashboard_esse3', 'groupmode') ?: 'directcategory');
        $allowed = ['directcategory', 'parentcategory', 'toproot', 'fullpath', 'none'];
        if (!in_array($mode, $allowed, true)) {
            return 'directcategory';
        }

        return $mode;
    }

    /**
     * Get optional second-level grouping strategy from plugin settings.
     *
     * @param string $primarymode
     * @return string
     */
    private function get_secondary_enrolled_group_mode(string $primarymode): string {
        $mode = (string) (get_config('block_academic_dashboard_esse3', 'secondarygroupmode') ?: 'none');
        $allowed = ['directcategory', 'parentcategory', 'toproot', 'fullpath', 'none'];
        if (!in_array($mode, $allowed, true)) {
            return 'none';
        }

        if ($mode === 'none' || $mode === $primarymode) {
            return 'none';
        }

        return $mode;
    }

    /**
     * Get primary grouping depth for enrolled courses.
     *
     * @return int
     */
    private function get_enrolled_group_depth(): int {
        $depth = (int)(get_config('block_academic_dashboard_esse3', 'groupdepth') ?: 0);
        return max(0, $depth);
    }

    /**
     * Get secondary grouping depth for enrolled courses.
     *
     * @param int $primarydepth
     * @return int
     */
    private function get_enrolled_secondary_group_depth(int $primarydepth): int {
        $depth = (int)(get_config('block_academic_dashboard_esse3', 'secondarygroupdepth') ?: 0);
        $depth = max(0, $depth);
        if ($depth === 0 || $depth === $primarydepth || ($primarydepth > 0 && $depth < $primarydepth)) {
            return 0;
        }

        return $depth;
    }

    /**
     * Get category depth used for enrolled-course sorting.
     *
     * @return int
     */
    private function get_enrolled_sort_depth(): int {
        $depth = (int)(get_config('block_academic_dashboard_esse3', 'sortcategorydepth') ?: 0);
        return max(0, $depth);
    }

    /**
     * Get sorting direction for enrolled courses.
     *
     * @return string
     */
    private function get_enrolled_sort_direction(): string {
        $direction = (string)(get_config('block_academic_dashboard_esse3', 'sortdirection') ?: 'asc');
        return ($direction === 'desc') ? 'desc' : 'asc';
    }
}
