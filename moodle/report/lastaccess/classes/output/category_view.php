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
 * Class containing data for learning plan template competencies page
 *
 * @package    report_competency
 * @copyright  2015 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_lastaccess\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;
use core_course\external\course_summary_exporter;

/**
 * Class containing data for learning plan template competencies page
 *
 * @copyright  2015 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class category_view implements renderable, templatable {

    protected $categories = [];

    /**
     * Construct this renderable.
     *
     * @param int $courseid The course id
     * @param int $userid The user id
     */
    public function __construct($categories) {
        $this->categories = $categories;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $CFG;
        global $DB;

        require_once($CFG->dirroot . '/course/lib.php');
        require_once($CFG->dirroot . '/lib/coursecatlib.php');

        require_once($CFG->dirroot . '/lib/weblib.php');
        //require_once($CFG->libdir . '/adminlib.php');
        // Build courses view data structure.
        $coursesview = [
          'hascategories' => !empty($this->categories)
        ];

        $sql = "SELECT c.* FROM {course} AS c LEFT JOIN {course_format_options} AS o ON c.id = o.courseid "
            . "WHERE c.category=:category AND c.visible=:visible AND c.id!=:siteid AND c.`format`=:format AND o.`name`=:name AND o.`value`=:value";
        $array = array(
          "category" => 0,
          "visible" => 1,
          "siteid" => SITEID,
          "format" => "singleactivity",
          "name" => "activitytype",
          "value" => "quiz"
        );

        $courses_quiz = null;
        foreach ($this->categories as &$cate) {
            $cate->url_cource = $CFG->wwwroot . '/course/index.php?categoryid=' . $cate->id;
            
            $courses_quiz = null;
            //Lay thong tin bai cource_thi
            $array["category"] = $cate->id;
            $courses = $DB->get_records_sql($sql, $array);
            if ($courses) {
                foreach ($courses as &$co) {
                    $course_module = $this->getCourseModules($co->id);
                    if ($course_module != null) {
                        $co->url = $CFG->wwwroot . '/mod/quiz/view.php?id=' . $course_module->id;
                        $courses_quiz = $co;
                        break;
                    }
                }
            }

            if ($courses_quiz != null) {
                $cate->courses_quiz = $courses_quiz;
                $cate->has_courses_quiz = true;
            } else {
                $cate->courses_quiz = null;
                $cate->has_courses_quiz = false;
            }

            $coursesview['categories'][] = $cate;
        }

        return $coursesview;
    }

    public function getCourseModules($course, $module = 16) {
        global $DB;
        $sql = "SELECT c.* FROM {course_modules} AS c WHERE c.course=:course AND c.`module`=:module";
        $array = array(
          "course" => $course,
          "module" => $module
        );
        $courses = $DB->get_records_sql($sql, $array);

        $rtn = null;
        foreach ($courses as &$co) {
            $rtn = $co;
        }
        return $rtn;
    }

}
