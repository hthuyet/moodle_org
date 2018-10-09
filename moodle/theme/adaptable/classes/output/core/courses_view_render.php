<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace theme_adaptable\output\core;

defined('MOODLE_INTERNAL') || die();

include_once($CFG->dirroot . "/blocks/myoverview/classes/output/courses_view.php");

class theme_adaptable_courses_view extends courses_view {
    public function export_for_template(renderer_base $output) {
        global $CFG;
        require_once($CFG->dirroot.'/course/lib.php');
        require_once($CFG->dirroot.'/lib/coursecatlib.php');

        die("@#432");
        // Build courses view data structure.
        $coursesview = [
            'hascourses' => !empty($this->courses)
        ];

        // How many courses we have per status?
        $coursesbystatus = ['past' => 0, 'inprogress' => 0, 'future' => 0];
        
        //var_dump($this->courses);die;
        foreach ($this->courses as $course) {
            $courseid = $course->id;
            $context = \context_course::instance($courseid);
            $exporter = new course_summary_exporter($course, [
                'context' => $context
            ]);
            $exportedcourse = $exporter->export($output);
            // Convert summary to plain text.
            $exportedcourse->summary = content_to_text($exportedcourse->summary, $exportedcourse->summaryformat);

            $course = new \course_in_list($course);
            foreach ($course->get_course_overviewfiles() as $file) {
                $isimage = $file->is_valid_image();
                if ($isimage) {
                    $url = file_encode_url("$CFG->wwwroot/pluginfile.php",
                        '/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
                        $file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage);
                    $exportedcourse->courseimage = $url;
                    $exportedcourse->classes = 'courseimage';
                    break;
                }
            }

            $exportedcourse->color = $this->coursecolor($course->id);

            if (!isset($exportedcourse->courseimage)) {
                $pattern = new \core_geopattern();
                $pattern->setColor($exportedcourse->color);
                $pattern->patternbyid($courseid);
                $exportedcourse->classes = 'coursepattern';
                $exportedcourse->courseimage = $pattern->datauri();
            }

            // Include course visibility.
            $exportedcourse->visible = (bool)$course->visible;

            $courseprogress = null;

            $classified = course_classify_for_timeline($course);

            if (isset($this->coursesprogress[$courseid])) {
                $courseprogress = $this->coursesprogress[$courseid]['progress'];
                $exportedcourse->hasprogress = !is_null($courseprogress);
                $exportedcourse->progress = $courseprogress;
            }

            if ($classified == COURSE_TIMELINE_PAST) {
                // Courses that have already ended.
                $pastpages = floor($coursesbystatus['past'] / $this::COURSES_PER_PAGE);

                $coursesview['past']['pages'][$pastpages]['courses'][] = $exportedcourse;
                $coursesview['past']['pages'][$pastpages]['active'] = ($pastpages == 0 ? true : false);
                $coursesview['past']['pages'][$pastpages]['page'] = $pastpages + 1;
                $coursesview['past']['haspages'] = true;
                $coursesbystatus['past']++;
            } else if ($classified == COURSE_TIMELINE_FUTURE) {
                // Courses that have not started yet.
                $futurepages = floor($coursesbystatus['future'] / $this::COURSES_PER_PAGE);

                $coursesview['future']['pages'][$futurepages]['courses'][] = $exportedcourse;
                $coursesview['future']['pages'][$futurepages]['active'] = ($futurepages == 0 ? true : false);
                $coursesview['future']['pages'][$futurepages]['page'] = $futurepages + 1;
                $coursesview['future']['haspages'] = true;
                $coursesbystatus['future']++;
            } else {
                // Courses still in progress. Either their end date is not set, or the end date is not yet past the current date.
                $inprogresspages = floor($coursesbystatus['inprogress'] / $this::COURSES_PER_PAGE);

                $coursesview['inprogress']['pages'][$inprogresspages]['courses'][] = $exportedcourse;
                $coursesview['inprogress']['pages'][$inprogresspages]['active'] = ($inprogresspages == 0 ? true : false);
                $coursesview['inprogress']['pages'][$inprogresspages]['page'] = $inprogresspages + 1;
                $coursesview['inprogress']['haspages'] = true;
                $coursesbystatus['inprogress']++;
            }
        }

        
        //var_dump($coursesview);die;
        // Build courses view paging bar structure.
        foreach ($coursesbystatus as $status => $total) {
            $quantpages = ceil($total / $this::COURSES_PER_PAGE);

            if ($quantpages) {
                $coursesview[$status]['pagingbar']['disabled'] = ($quantpages <= 1);
                $coursesview[$status]['pagingbar']['pagecount'] = $quantpages;
                $coursesview[$status]['pagingbar']['first'] = ['page' => '&laquo;', 'url' => '#'];
                $coursesview[$status]['pagingbar']['last'] = ['page' => '&raquo;', 'url' => '#'];
                for ($page = 0; $page < $quantpages; $page++) {
                    $coursesview[$status]['pagingbar']['pages'][$page] = [
                        'number' => $page + 1,
                        'page' => $page + 1,
                        'url' => '#',
                        'active' => ($page == 0 ? true : false)
                    ];
                }
            }
        }

        //var_dump($coursesview);die;
        return $coursesview;
    }
}
