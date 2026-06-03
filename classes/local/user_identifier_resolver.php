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
 * Resolves the Esse3 userId value for a Moodle user.
 *
 * @package   block_academic_dashboard_esse3
 * @copyright 2026 Università degli Studi di Ferrara - Unife
 * @author    Andrea Bertelli <andrea.bertelli@unife.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_identifier_resolver {
    /**
     * Resolve the Esse3 userId from the configured Moodle user field.
     *
     * Looks up the field name stored in the 'userfield' plugin setting and reads
     * its value from the standard user object or from custom profile fields.
     * Falls back to the Moodle username if no specific field is configured.
     *
     * @param \stdClass $user
     * @return string
     */
    public static function resolve_for_user(\stdClass $user): string {
        $fieldname = (string)get_config('block_academic_dashboard_esse3', 'userfield');

        if ($fieldname === '') {
            $fieldname = 'username';
        }

        if (isset($user->$fieldname)) {
            return trim((string)$user->$fieldname);
        }

        if (
            !empty($user->profile) &&
            is_array($user->profile) &&
            array_key_exists($fieldname, $user->profile)
        ) {
            return trim((string)$user->profile[$fieldname]);
        }

        return trim((string)($user->username ?? ''));
    }
}
