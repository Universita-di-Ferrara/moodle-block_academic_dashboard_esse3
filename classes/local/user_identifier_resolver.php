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
 * Resolves Moodle user values used by Esse3 lookups.
 *
 * @package   block_academic_dashboard_esse3
 * @copyright 2026 Università degli Studi di Ferrara - Unife
 * @author    Andrea Bertelli <andrea.bertelli@unife.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_identifier_resolver {
    /**
     * Resolve the Esse3 matricola from the configured Moodle user field.
     *
     * @param \stdClass $user
     * @return string
     */
    public static function resolve_matricola_for_user(\stdClass $user): string {
        $fieldname = (string)get_config('block_academic_dashboard_esse3', 'matricolafield');

        if ($fieldname === '') {
            $fieldname = 'idnumber';
        }

        return self::resolve_field_for_user($user, $fieldname);
    }

    /**
     * Checks if the user email belongs to the configured student domain.
     *
     * If no domain is configured, the check is disabled and the user is treated
     * as potentially student to preserve the existing ESSE3 flow.
     *
     * @param \stdClass $user
     * @return bool
     */
    public static function is_student(\stdClass $user): bool {
        $domains = self::get_student_domains();
        if (empty($domains)) {
            return true;
        }

        $email = trim((string)($user->email ?? ''));
        if ($email === '' || strpos($email, '@') === false) {
            return false;
        }

        $emaildomain = strtolower(substr(strrchr($email, '@'), 1));
        return in_array($emaildomain, $domains, true);
    }

    /**
     * Resolve a configured Moodle user field.
     *
     * @param \stdClass $user
     * @param string $fieldname
     * @return string
     */
    private static function resolve_field_for_user(\stdClass $user, string $fieldname): string {
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

        return '';
    }

    /**
     * Gets normalized student domains from plugin settings.
     *
     * @return array
     */
    private static function get_student_domains(): array {
        $configured = (string)get_config('block_academic_dashboard_esse3', 'studentdomain');
        $parts = preg_split('/[\s,;]+/', $configured);
        $domains = [];

        foreach ($parts as $part) {
            $domain = strtolower(trim((string)$part));
            $domain = ltrim($domain, '@');
            if ($domain !== '') {
                $domains[$domain] = $domain;
            }
        }

        return array_values($domains);
    }
}
