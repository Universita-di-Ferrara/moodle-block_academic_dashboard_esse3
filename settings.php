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
 * Settings for the block_academic_dashboard_esse3 plugin.
 *
 * @package   block_academic_dashboard_esse3
 * @copyright 2026 Università degli Studi di Ferrara - Unife
 * @author    Andrea Bertelli <andrea.bertelli@unife.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    // API URL.
    $settings->add(new admin_setting_configtext(
        'block_academic_dashboard_esse3/apiurl',
        get_string('apiurl', 'block_academic_dashboard_esse3'),
        get_string('apiurl_desc', 'block_academic_dashboard_esse3'),
        'https://<your_domain>/e3rest/api/',
        PARAM_URL
    ));

    // API Token.
    $settings->add(new admin_setting_configpasswordunmask(
        'block_academic_dashboard_esse3/apitoken',
        get_string('apitoken', 'block_academic_dashboard_esse3'),
        get_string('apitoken_desc', 'block_academic_dashboard_esse3'),
        '',
        PARAM_TEXT
    ));

    // Student email domain.
    $settings->add(new admin_setting_configtext(
        'block_academic_dashboard_esse3/studentdomain',
        get_string('studentdomain', 'block_academic_dashboard_esse3'),
        get_string('studentdomain_desc', 'block_academic_dashboard_esse3'),
        '',
        PARAM_TEXT
    ));

    // Esse3 matricola field.
    $settings->add(new admin_setting_configtext(
        'block_academic_dashboard_esse3/matricolafield',
        get_string('matricolafield', 'block_academic_dashboard_esse3'),
        get_string('matricolafield_desc', 'block_academic_dashboard_esse3'),
        get_config('block_academic_dashboard_esse3', 'matricolafield') ?: 'idnumber',
        PARAM_ALPHANUMEXT
    ));

    // Root categories used to scope enrolled-course fallback.
    $settings->add(new admin_setting_configtextarea(
        'block_academic_dashboard_esse3/rootcategories',
        get_string('rootcategories', 'block_academic_dashboard_esse3'),
        get_string('rootcategories_desc', 'block_academic_dashboard_esse3'),
        ''
    ));

    // Primary grouping depth for enrolled-course fallback.
    $settings->add(new admin_setting_configtext(
        'block_academic_dashboard_esse3/groupdepth',
        get_string('groupdepth', 'block_academic_dashboard_esse3'),
        get_string('groupdepth_desc', 'block_academic_dashboard_esse3'),
        '',
        PARAM_INT
    ));

    // Secondary grouping depth for enrolled-course fallback.
    $settings->add(new admin_setting_configtext(
        'block_academic_dashboard_esse3/secondarygroupdepth',
        get_string('secondarygroupdepth', 'block_academic_dashboard_esse3'),
        get_string('secondarygroupdepth_desc', 'block_academic_dashboard_esse3'),
        '',
        PARAM_INT
    ));

    // Category depth used for enrolled-course sorting.
    $settings->add(new admin_setting_configtext(
        'block_academic_dashboard_esse3/sortcategorydepth',
        get_string('sortcategorydepth', 'block_academic_dashboard_esse3'),
        get_string('sortcategorydepth_desc', 'block_academic_dashboard_esse3'),
        '',
        PARAM_INT
    ));

    // Sorting direction for enrolled-course sorting.
    $settings->add(new admin_setting_configselect(
        'block_academic_dashboard_esse3/sortdirection',
        get_string('sortdirection', 'block_academic_dashboard_esse3'),
        get_string('sortdirection_desc', 'block_academic_dashboard_esse3'),
        'asc',
        [
            'asc' => get_string('sortdirection:asc', 'block_academic_dashboard_esse3'),
            'desc' => get_string('sortdirection:desc', 'block_academic_dashboard_esse3'),
        ]
    ));

    // Grouping strategy for enrolled-course fallback.
    $settings->add(new admin_setting_configselect(
        'block_academic_dashboard_esse3/groupmode',
        get_string('groupmode', 'block_academic_dashboard_esse3'),
        get_string('groupmode_desc', 'block_academic_dashboard_esse3'),
        'directcategory',
        [
            'directcategory' => get_string('groupmode:directcategory', 'block_academic_dashboard_esse3'),
            'parentcategory' => get_string('groupmode:parentcategory', 'block_academic_dashboard_esse3'),
            'toproot' => get_string('groupmode:toproot', 'block_academic_dashboard_esse3'),
            'fullpath' => get_string('groupmode:fullpath', 'block_academic_dashboard_esse3'),
            'none' => get_string('groupmode:none', 'block_academic_dashboard_esse3'),
        ]
    ));

    // Optional second-level grouping strategy.
    $settings->add(new admin_setting_configselect(
        'block_academic_dashboard_esse3/secondarygroupmode',
        get_string('secondarygroupmode', 'block_academic_dashboard_esse3'),
        get_string('secondarygroupmode_desc', 'block_academic_dashboard_esse3'),
        'none',
        [
            'none' => get_string('groupmode:none', 'block_academic_dashboard_esse3'),
            'directcategory' => get_string('groupmode:directcategory', 'block_academic_dashboard_esse3'),
            'parentcategory' => get_string('groupmode:parentcategory', 'block_academic_dashboard_esse3'),
            'toproot' => get_string('groupmode:toproot', 'block_academic_dashboard_esse3'),
            'fullpath' => get_string('groupmode:fullpath', 'block_academic_dashboard_esse3'),
        ]
    ));

    // Accent Color.
    $settings->add(new admin_setting_configcolourpicker(
        'block_academic_dashboard_esse3/accentcolor',
        get_string('accentcolor', 'block_academic_dashboard_esse3'),
        get_string('accentcolor_desc', 'block_academic_dashboard_esse3'),
        '#0056b3'
    ));
}
