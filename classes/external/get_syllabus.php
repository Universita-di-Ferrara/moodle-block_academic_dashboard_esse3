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
 * External function for fetching course syllabus from Esse3.
 *
 * @package    block_academic_dashboard_esse3
 * @copyright  2026 Università degli Studi di Ferrara - Unife
 * @author     Andrea Bertelli <andrea.bertelli@unife.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_academic_dashboard_esse3\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
use moodle_exception;
use block_academic_dashboard_esse3\local\esse3\esse3_handler;
use block_academic_dashboard_esse3\local\matricola_resolver;

/**
 * Class get_syllabus
 *
 * External function to get syllabus data.
 */
class get_syllabus extends external_api {
    /**
     * Parameters for execute()
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'matId' => new external_value(PARAM_INT, 'Student career ID', VALUE_REQUIRED),
            'adsceId' => new external_value(PARAM_INT, 'Course sequence ID', VALUE_REQUIRED),
        ]);
    }

    /**
     * Fetches the syllabus from Esse3.
     *
     * @param int $matid
     * @param int $adsceid
     * @return array
     */
    public static function execute($matid, $adsceid) {
        global $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'matId' => $matid,
            'adsceId' => $adsceid,
        ]);

        $context = \context_system::instance();
        self::validate_context($context);

        $matricola = matricola_resolver::resolve_for_user($USER);
        if ($matricola === '') {
            throw new moodle_exception('privacy:missingmatricola', 'block_academic_dashboard_esse3');
        }

        $handler = new esse3_handler();
        $careers = $handler->get_careers_by_matricola($matricola);

        $allowedmatids = array_map(static function ($career): int {
            return isset($career->matId) ? (int)$career->matId : 0;
        }, $careers);

        if (!in_array((int)$params['matId'], $allowedmatids, true)) {
            throw new moodle_exception('privacy:invalidmatid', 'block_academic_dashboard_esse3');
        }

        $syllabus = $handler->get_syllabus($params['matId'], $params['adsceId']);

        if ($syllabus === false) {
            return [
                'status' => 'error',
                'message' => 'Failed to fetch syllabus from Esse3',
                'data' => [],
            ];
        }

        return [
            'status' => 'success',
            'message' => '',
            'data' => $syllabus,
        ];
    }

    /**
     * Return type for execute()
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_ALPHANUM, 'Status (success or error)'),
            'message' => new external_value(PARAM_TEXT, 'Error message if any'),
            'data' => new external_multiple_structure(
                new external_single_structure([
                    'adsceId' => new external_value(PARAM_INT, 'ID sequence', VALUE_OPTIONAL),
                    'contenuti' => new external_value(PARAM_RAW, 'Contents', VALUE_OPTIONAL),
                    'metodiDidattici' => new external_value(PARAM_RAW, 'Teaching methods', VALUE_OPTIONAL),
                    'modalitaVerificaApprendimento' => new external_value(PARAM_RAW, 'Assessment methods', VALUE_OPTIONAL),
                    'obiettiviFormativi' => new external_value(PARAM_RAW, 'Learning objectives', VALUE_OPTIONAL),
                    'prerequisiti' => new external_value(PARAM_RAW, 'Prerequisites', VALUE_OPTIONAL),
                    'testiRiferimento' => new external_value(PARAM_RAW, 'Reference textbooks', VALUE_OPTIONAL),
                ]),
                'Syllabus entries'
            ),
        ]);
    }
}
