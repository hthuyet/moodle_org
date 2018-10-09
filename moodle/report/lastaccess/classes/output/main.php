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
 * Class containing data for my overview block.
 *
 * @package    block_myoverview
 * @copyright  2017 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace report_lastaccess\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;
use core_completion\progress;

require_once($CFG->dirroot . '/blocks/myoverview/lib.php');
require_once($CFG->libdir . '/completionlib.php');

/**
 * Class containing data for my overview block.
 *
 * @copyright  2017 Simey Lameze <simey@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class main implements renderable, templatable {
    
    protected $categories = [];
    
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
        global $USER;
        
        $categories = [];
        
        foreach ($this->categories as $category) {
            $categories[$category->id] = $category;
        }

        $coursesview = new category_view($categories);
        $nocoursesurl = $output->image_url('courses', 'block_myoverview')->out();
        $noeventsurl = $output->image_url('activities', 'block_myoverview')->out();

        $rtn = [
            'midnight' => usergetmidnight(time()),
            'categoryview' => $coursesview->export_for_template($output),
            'urls' => [
                'nocourses' => $nocoursesurl,
                'noevents' => $noeventsurl
            ],
            'viewingtimeline' => false,
            'viewingcourses' => true
        ];
        //var_dump($rtn);die;
        return $rtn;
    }
}
