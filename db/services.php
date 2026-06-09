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
 * Web services definition for the block_academic_dashboard_esse3 plugin.
 *
 * @package    block_academic_dashboard_esse3
 * @copyright  2024 Università degli Studi di Ferrara - Unife
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'block_academic_dashboard_esse3_get_syllabus' => [
        'classname'   => 'block_academic_dashboard_esse3\external\get_syllabus',
        'methodname'  => 'execute',
        'description' => 'Get course syllabus from Esse3',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities' => 'block/academic_dashboard_esse3:view',
    ],
];
