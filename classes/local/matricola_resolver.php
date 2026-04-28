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

namespace block_academic_dashboard_esse3\local;

/**
 * Resolves the configured matricola value for a Moodle user.
 *
 * @package   block_academic_dashboard_esse3
 * @copyright 2026 Università degli Studi di Ferrara - Unife
 * @author    Andrea Bertelli <andrea.bertelli@unife.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class matricola_resolver {
    /**
     * Resolve the configured matricola value from standard or custom profile fields.
     *
     * @param \stdClass $user
     * @return string
     */
    public static function resolve_for_user(\stdClass $user): string {
        $fieldname = (string)get_config('block_academic_dashboard_esse3', 'matricolafield');

        if ($fieldname !== '' && isset($user->$fieldname)) {
            return trim((string)$user->$fieldname);
        }

        if (
            $fieldname !== '' &&
            !empty($user->profile) &&
            is_array($user->profile) &&
            array_key_exists($fieldname, $user->profile)
        ) {
            return trim((string)$user->profile[$fieldname]);
        }

        return '';
    }
}
