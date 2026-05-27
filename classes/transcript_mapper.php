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
use stdClass;

/**
 * Maps Esse3 items to course display objects and prepares template data.
 *
 * @package   block_academic_dashboard_esse3
 * @copyright 2026 Università degli Studi di Ferrara - Unife
 * @author    Andrea Bertelli <andrea.bertelli@unife.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class transcript_mapper {
    /**
     * Prepares data for the Mustache template.
     *
     * @param array $items Esse3 transcript items.
     * @param int $blockid
     * @return array
     */
    public function prepare_template_data($items, $blockid) {
        $items = $this->apply_parent_child_grouping($items);

        $courses = [];
        $uniqueyears = [];
        $uniquestatuses = [];

        foreach ($items as $item) {
            $course = $this->map_item_to_course($item);
            if (!empty($course->courseYear)) {
                $uniqueyears[$course->courseYear] = $course->courseYear;
            }
            if (!empty($course->statusDes)) {
                $uniquestatuses[$course->statusDes] = $course->statusDes;
            }
            $courses[] = $course;
        }

        $this->sort_courses($courses);
        $filters = $this->prepare_filter_lists($uniqueyears, $uniquestatuses);

        return [
            'courses' => $courses,
            'hascourses' => !empty($courses),
            'years' => $filters['years'],
            'statuses' => $filters['statuses'],
            'blockid' => $blockid,
        ];
    }

    /**
     * Hide aggregate parent teachings and annotate child teachings with their parent title.
     *
     * Esse3 exposes aggregate exams without an explicit parent field. Child rows can be identified when
     * raggEsaTipo is ESA and ragId points to the adsceId of another row in the transcript.
     *
     * @param array $items Esse3 transcript items.
     * @return array Visible transcript items.
     */
    private function apply_parent_child_grouping(array $items): array {
        $itemsbyadsceid = [];
        foreach ($items as $item) {
            if (!empty($item->adsceId)) {
                $itemsbyadsceid[(string)$item->adsceId] = $item;
            }
        }

        $hiddenparentids = [];
        foreach ($items as $item) {
            if (!$this->is_aggregate_child($item)) {
                continue;
            }

            $parentid = (string)$item->ragId;
            if (!isset($itemsbyadsceid[$parentid])) {
                continue;
            }

            $parentitem = $itemsbyadsceid[$parentid];
            $item->parentAdDes = $parentitem->adDes ?? '';
            $item->parentAdsceId = $parentitem->adsceId ?? '';
            $item->hasParentCourse = !empty($item->parentAdDes);
            $hiddenparentids[$parentid] = true;
        }

        $visibleitems = [];
        foreach ($items as $item) {
            $adsceid = !empty($item->adsceId) ? (string)$item->adsceId : '';
            if ($adsceid !== '' && isset($hiddenparentids[$adsceid])) {
                continue;
            }

            if (empty($item->hasParentCourse)) {
                $item->parentAdDes = '';
                $item->parentAdsceId = '';
                $item->hasParentCourse = false;
            }

            $visibleitems[] = $item;
        }

        return $visibleitems;
    }

    /**
     * Maps a raw Esse3 item into a standardized course display object.
     *
     * @param stdClass $item
     * @return stdClass
     */
    private function map_item_to_course($item) {
        $course = new stdClass();
        $course->adDes = $item->adDes ?? '';
        $course->adCod = $item->adCod ?? '';
        $course->adsceId = $item->adsceId ?? '';
        $course->parentAdDes = $item->parentAdDes ?? '';
        $course->parentAdsceId = $item->parentAdsceId ?? '';
        $course->hasparentcourse = !empty($course->parentAdDes);
        $course->careerMatId = $item->careerMatId ?? '';
        $course->courseYear = $item->annoCorso ?? '';
        $course->statusDes = $item->statoDes ?? '';
        $course->status = $item->stato->value ?? $item->stato ?? '';
        $course->weight = $item->peso ?? '';
        $course->careerCdsDes = $item->careerCdsDes ?? '';
        $course->aaFreqId = $item->aaFreqId ?? '';

        $course->hassyllabus = false;
        if (isset($item->chiaveADContestualizzata)) {
            $ctx = $item->chiaveADContestualizzata;
            $course->cdsDes = $ctx->cdsDes ?? '';
            $course->cdsCod = $ctx->cdsCod ?? '';
            $course->adId = $ctx->adId ?? '';
            $course->cdsId = $ctx->cdsId ?? '';
            if (!empty($course->adId) && !empty($course->cdsId)) {
                $course->hassyllabus = true;
            }
        }

        $course->teacher = $item->docenteDes ?? '';
        if (empty($course->teacher) && isset($item->presidenteCognome)) {
            $course->teacher = trim(($item->presidenteNome ?? '') . ' ' . $item->presidenteCognome);
        }
        $course->semester = $item->semester ?? '';

        $this->set_course_exam_status($course, $item);

        return $course;
    }

    /**
     * Returns whether an Esse3 item is a child of an aggregate exam.
     *
     * @param stdClass $item
     * @return bool
     */
    private function is_aggregate_child(stdClass $item): bool {
        $aggregationtype = '';
        if (isset($item->raggEsaTipo)) {
            if (is_object($item->raggEsaTipo) && isset($item->raggEsaTipo->value)) {
                $aggregationtype = (string)$item->raggEsaTipo->value;
            } else {
                $aggregationtype = (string)$item->raggEsaTipo;
            }
        }

        $parentid = $item->ragId ?? '';
        $adsceid = $item->adsceId ?? '';

        if ($aggregationtype !== 'ESA' || empty($parentid) || empty($adsceid)) {
            return false;
        }

        return (string)$parentid !== (string)$adsceid;
    }

    /**
     * Sets exam-related status on a course object.
     *
     * @param stdClass $course
     * @param stdClass $item
     */
    private function set_course_exam_status(&$course, $item) {
        $course->passed = false;

        // Check for 'esito' object or flat fields.
        $supesa = $item->esito->supEsaFlg ?? $item->supEsaFlg ?? 0;
        $voto = $item->esito->voto ?? $item->voto ?? 0;
        $lode = $item->esito->lodeFlg ?? $item->lodeFlg ?? 0;
        $giudizio = $item->esito->tipoGiudDes ?? $item->tipoGiudDes ?? '';

        // An exam is passed if supesa is 1, OR if status is 'S' (Superata), OR if there is a grade > 0.
        if ($supesa == 1 || $course->status === 'S' || $voto > 0) {
            $course->passed = true;
            if ($voto > 0) {
                $course->grade = $voto;
                if ($lode == 1) {
                    $course->grade .= 'L';
                }
            } else if (!empty($giudizio)) {
                $course->grade = $giudizio;
            }
        }
    }

    /**
     * Sorts courses.
     *
     * @param array $courses
     */
    private function sort_courses(&$courses) {
        usort($courses, function ($a, $b) {
            if ($a->courseYear != $b->courseYear) {
                return $a->courseYear <=> $b->courseYear;
            }
            if ($a->semester != $b->semester) {
                return strcasecmp($a->semester, $b->semester);
            }
            return strcasecmp($a->adDes, $b->adDes);
        });
    }

    /**
     * Formats filter lists for the template.
     *
     * @param array $uniqueyears
     * @param array $uniquestatuses
     * @return array
     */
    private function prepare_filter_lists($uniqueyears, $uniquestatuses) {
        ksort($uniqueyears);
        ksort($uniquestatuses);

        $years = [];
        foreach ($uniqueyears as $y) {
            $years[] = ['value' => $y];
        }

        $statuses = [];
        foreach ($uniquestatuses as $s) {
            $statuses[] = ['value' => $s];
        }

        return ['years' => $years, 'statuses' => $statuses];
    }
}
