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
 * Block definition class for the block_academic_dashboard_esse3 plugin.
 *
 * Displays the student's Esse3 study plan (transcript) and allows
 * self-enrollment into corresponding Moodle courses.
 *
 * @package   block_academic_dashboard_esse3
 * @copyright 2026 Università degli Studi di Ferrara - Unife
 * @author    Andrea Bertelli <andrea.bertelli@unife.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_academic_dashboard_esse3 extends block_base {
    /**
     * Normalizes a hex color string.
     *
     * @param string $color
     * @return string
     */
    private function normalize_hex_color(string $color): string {
        $color = trim($color);
        if ($color === '') {
            return '#0056b3';
        }

        if ($color[0] !== '#') {
            $color = '#' . $color;
        }

        if (preg_match('/^#([a-f0-9]{3})$/i', $color, $matches)) {
            $digits = strtolower($matches[1]);
            return '#' . $digits[0] . $digits[0] . $digits[1] . $digits[1] . $digits[2] . $digits[2];
        }

        if (preg_match('/^#([a-f0-9]{6})$/i', $color)) {
            return strtolower($color);
        }

        return '#0056b3';
    }

    /**
     * Returns a readable text color for the provided accent color.
     *
     * @param string $hexcolor
     * @return string
     */
    private function get_contrast_text_color(string $hexcolor): string {
        $hexcolor = ltrim($this->normalize_hex_color($hexcolor), '#');

        $red = hexdec(substr($hexcolor, 0, 2));
        $green = hexdec(substr($hexcolor, 2, 2));
        $blue = hexdec(substr($hexcolor, 4, 2));

        $luminance = (($red * 299) + ($green * 587) + ($blue * 114)) / 1000;

        return ($luminance >= 150) ? '#1f2933' : '#ffffff';
    }

    /**
     * Initialises the block.
     *
     * @return void
     */
    public function init() {
        $this->title = get_string('blocktitle', 'block_academic_dashboard_esse3');
    }

    /**
     * Gets the block contents.
     *
     * @return stdClass|string The block HTML.
     */
    public function get_content() {
        global $OUTPUT, $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';
        $this->content->text = '';

        $esse3userid = \block_academic_dashboard_esse3\local\user_identifier_resolver::resolve_for_user($USER);

        $manager = new \block_academic_dashboard_esse3\transcript_manager();
        $data = $manager->get_transcript_template_data($esse3userid, $this->instance->id, (int)$USER->id);

        if (empty($data)) {
            // Keep block truly empty so Moodle can hide it when no transcript courses are available.
            return $this->content;
        }

        $template = 'block_academic_dashboard_esse3/content';
        if (!empty($data['datasource']) && $data['datasource'] === 'enrolled') {
            $template = 'block_academic_dashboard_esse3/enrolled_content';
            $this->page->requires->js_call_amd(
                'block_academic_dashboard_esse3/enrolled',
                'init',
                [['uniqueid' => $this->instance->id]]
            );
        } else {
            // Load JS module only for transcript view (filters + syllabus modal).
            $this->page->requires->js_call_amd(
                'block_academic_dashboard_esse3/transcript',
                'init',
                [['uniqueid' => $this->instance->id]]
            );
        }

        // Fetch accent color from settings and pass it to template.
        $accentcolor = $this->normalize_hex_color(
            (string)(get_config('block_academic_dashboard_esse3', 'accentcolor') ?: '#0056b3')
        );
        $data['accentcolor'] = $accentcolor;
        $data['accenttextcolor'] = $this->get_contrast_text_color($accentcolor);

        $this->content->text = $OUTPUT->render_from_template($template, $data);

        return $this->content;
    }

    /**
     * Defines in which pages this block can be added.
     *
     * @return array of the pages where the block can be added.
     */
    public function applicable_formats() {
        return [
            'admin' => false,
            'site-index' => true,
            'course-view' => false,
            'mod' => false,
            'my' => true,
        ];
    }

    /**
     * Allow the block to have configuration.
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }
}
