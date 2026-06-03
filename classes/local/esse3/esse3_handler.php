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
 * Esse3 API handler for the block_academic_dashboard_esse3 plugin.
 *
 * Handles communication with the Esse3 REST API for student career and transcript data.
 *
 * @package    block_academic_dashboard_esse3
 * @copyright  2026 Università degli Studi di Ferrara - Unife
 * @author     Andrea Bertelli <andrea.bertelli@unife.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_academic_dashboard_esse3\local\esse3;

/**
 * Class esse3_handler
 *
 * Handles communication with the Esse3 REST API for student data.
 */
class esse3_handler {
    /** @var string|null The base URL for the Esse3 API. */
    private $urlws = null;

    /** @var string|null The authentication token. */
    private $token = null;

    /**
     * Constructor. Initializes API configuration from block_academic_dashboard_esse3 settings.
     */
    public function __construct() {
        $this->urlws = get_config('block_academic_dashboard_esse3', 'apiurl');
        $this->token = get_config('block_academic_dashboard_esse3', 'apitoken');
    }

    /**
     * Performs a GET request to the Esse3 API.
     *
     * @param string $url The full URL for the request.
     * @return string|false The response content or false on failure.
     */
    private function request_get($url) {
        if (empty($this->urlws) || empty($this->token)) {
            return false;
        }

        $options = [
            'http' => [
                'header' => "Content-type: application/json\r\n" .
                    "accept: application/json\r\n" .
                    "authorization: Basic $this->token\r\n" .
                    "X-Esse3-permit-invalid-jsessionid: true\r\n",
                'method' => 'GET',
                'ignore_errors' => true,
            ],
        ];

        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);

        if ($result === false) {
            return false;
        }

        return $result;
    }

    /**
     * Gets careers for a user based on their institutional userId.
     *
     * Uses the userId query parameter, which is unique per person in Esse3
     * and avoids collisions with non-unique student identifiers.
     *
     * @param string $userid The institutional user ID resolved from Moodle settings.
     * @return array|false List of careers/libretti with matId, or false on request failure.
     */
    public function get_careers_by_userid($userid) {
        if (!$this->urlws) {
            return false;
        }

        $url = $this->urlws . 'carriere-service-v1/carriere?userId=' . urlencode($userid);
        $response = $this->request_get($url);
        if (!$response) {
            return false;
        }

        $decoded = json_decode($response);
        if (!is_array($decoded)) {
            return false;
        }

        // Keep only active entries if staStuCod is present.
        $active = [];
        foreach ($decoded as $entry) {
            if (!isset($entry->staStuCod) || $entry->staStuCod !== 'X') {
                $active[] = $entry;
            }
        }

        return $active;
    }

    /**
     * Gets the transcript items for a given career.
     *
     * @param int $matid The career ID (matId).
     * @return array List of transcript items (courses).
     */
    public function get_transcript($matid) {
        if (!$this->urlws) {
            return [];
        }

        $url = $this->urlws . "libretto-service-v2/libretti/{$matid}/righe";
        $response = $this->request_get($url);

        if (!$response) {
            return [];
        }

        $decoded = json_decode($response);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Gets the partitions (teacher and semester info) for a given career.
     *
     * @param int $matid The career ID (matId).
     * @return array List of partition items.
     */
    public function get_partitions($matid) {
        if (!$this->urlws) {
            return [];
        }

        $url = $this->urlws . "libretto-service-v2/libretti/{$matid}/partizioni";
        $response = $this->request_get($url);

        if (!$response) {
            return [];
        }

        $decoded = json_decode($response);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Gets the syllabus for a specific course (AD).
     *
     * @param int $matid The student's career ID.
     * @param int $adsceid The course sequence ID (adsceId).
     * @return array|false The syllabus data or false on failure.
     */
    public function get_syllabus($matid, $adsceid) {
        if (!$this->urlws) {
            return false;
        }

        $url = $this->urlws . "libretto-service-v2/libretti/{$matid}/righe/{$adsceid}/syllabus/AD";
        $response = $this->request_get($url);

        if (!$response) {
            return false;
        }

        $decoded = json_decode($response);
        return is_array($decoded) ? $decoded : false;
    }
}
