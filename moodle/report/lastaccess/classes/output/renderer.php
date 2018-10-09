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
 * Renderer class for report_competency
 *
 * @package    report_competency
 * @copyright  2015 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_lastaccess\output;

defined('MOODLE_INTERNAL') || die;

use plugin_renderer_base;
use renderable;

/**
 * Renderer class for competency breakdown report
 *
 * @package    report_competency
 * @copyright  2015 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    public function render_main(main $main) {
        return $this->render_from_template('report_lastaccess/main', $main->export_for_template($this));
    }
    
    /**
     * Defer to template.
     *
     * @param report $page
     * @return string html for the page
     */
//    public function render_lastaccess(report $page) {
//        $data = $page->export_for_template($this);
//        return parent::render_from_template('report_lastaccess/report', $data);
//    }
    
    public function render_indexpage(renderer_base $output) {
        global $USER;

        $courses = enrol_get_my_courses('*');
        
        var_dump($courses);die;
        $coursesprogress = [];

        foreach ($courses as $course) {

            $completion = new \completion_info($course);

            // First, let's make sure completion is enabled.
            if (!$completion->is_enabled()) {
                continue;
            }

            $percentage = progress::get_course_progress_percentage($course);
            if (!is_null($percentage)) {
                $percentage = floor($percentage);
            }

            $coursesprogress[$course->id]['completed'] = $completion->is_course_complete($USER->id);
            $coursesprogress[$course->id]['progress'] = $percentage;
        }

        $coursesview = new category_view($courses, $coursesprogress);
        $nocoursesurl = $output->image_url('courses', 'block_myoverview')->out();
        $noeventsurl = $output->image_url('activities', 'block_myoverview')->out();

        return [
            'midnight' => usergetmidnight(time()),
            'coursesview' => $coursesview->export_for_template($output),
            'urls' => [
                'nocourses' => $nocoursesurl,
                'noevents' => $noeventsurl
            ],
            'viewingcourses' => true
        ];
    }
}
