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

namespace block_academic_dashboard_esse3\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\metadata\provider as metadata_provider;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\core_userlist_provider;
use core_privacy\local\request\plugin\provider as plugin_provider;
use core_privacy\local\request\userlist;

/**
 * Privacy provider for block_academic_dashboard_esse3.
 *
 * @package   block_academic_dashboard_esse3
 * @copyright 2026 Università degli Studi di Ferrara - Unife
 * @author    Andrea Bertelli <andrea.bertelli@unife.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements core_userlist_provider, metadata_provider, plugin_provider {
    /**
     * Describe the user data processed by this plugin.
     *
     * @param collection $collection The collection to add metadata to.
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->link_external_location('Esse3', [
            'matricola' => 'privacy:metadata:esse3:matricola',
            'careerid' => 'privacy:metadata:esse3:careerid',
            'courseid' => 'privacy:metadata:esse3:courseid',
            'transcriptdata' => 'privacy:metadata:esse3:transcriptdata',
            'syllabusdata' => 'privacy:metadata:esse3:syllabusdata',
        ], 'privacy:metadata:esse3');

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * This plugin does not store plugin-owned personal data in Moodle contexts.
     *
     * @param int $userid The user to search.
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        return new contextlist();
    }

    /**
     * Get the list of users who have data within a context.
     *
     * This plugin does not store plugin-owned personal data in Moodle contexts.
     *
     * @param userlist $userlist The userlist containing the list of users.
     * @return void
     */
    public static function get_users_in_context(userlist $userlist): void {
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * Data is processed via the external ESSE3 service and is not stored by the plugin.
     *
     * @param approved_contextlist $contextlist The approved contexts.
     * @return void
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * Data is processed via the external ESSE3 service and is not stored by the plugin.
     *
     * @param \context $context The context to delete for.
     * @return void
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
    }

    /**
     * Delete multiple users within a single context.
     *
     * Data is processed via the external ESSE3 service and is not stored by the plugin.
     *
     * @param approved_userlist $userlist The approved userlist.
     * @return void
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * Data is processed via the external ESSE3 service and is not stored by the plugin.
     *
     * @param approved_contextlist $contextlist The approved contexts.
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
    }
}
