<?php

require('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/report/lastaccess/index_form.php');

$systemcontext = context_system::instance();

$url = new moodle_url('/report/lastaccess/index.php');

//Check permisson
require_capability('report/lastaccess:view', $systemcontext);

//Get string from language
$strtitle = get_string("title", "report_lastaccess");

//Set up page object
$PAGE->set_url($url);
$PAGE->set_context($systemcontext);
$PAGE->set_title($strtitle);
$PAGE->set_pagelayout('category');
//$PAGE->set_pagelayout('report');
$PAGE->set_heading($strtitle);


$sql = "SELECT t1.* FROM {course_categories} AS t1 LEFT JOIN {course_categories} AS t2 ON t1.id = t2.parent WHERE t2.id IS NULL";
$categories = $DB->get_records_sql($sql, array());
//var_dump($categories);die;




//Get the course
$sql = "SELECT id, fullname FROM {course} WHERE visible=:visible AND id!=:siteid ORDER BY fullname";
$courses = $DB->get_records_sql_menu($sql, array("visible" => 1, "siteid" => SITEID));

//Load up the form
$mform = new lastaccess_form('', array("courses" => $courses));


//if ($users = $DB->get_records_sql($sql, $params)) {
//    //Table setup
//    $table = new html_table();
//    $table->head = array($strcourse, $strname, $strlastaccess, $stragency, $strgrade);
//    foreach ($users as $u) {
//        //If the finalgrade is returned, roud it to the  nearest value else assign finalgrade = 0
//        if ($u->finalgrade) {
//            $finalgrade = round($u->finalgrade, $CFG->grade_decimalpoints);
//        } else {
//            $finalgrade = 0;
//        }
//        //Display table data
//        $table->data[] = array($u->fullname, fullname($u), userdate($u->time), $u->agency, $finalgrade);
//    }
//}


echo $OUTPUT->header();
echo $OUTPUT->heading($strtitle);

//echo "Hello word..";

//echo $mform->display();

$renderable = new \report_lastaccess\output\main($categories);
$renderer = $PAGE->get_renderer('report_lastaccess');

echo $OUTPUT->render($renderable);

echo $OUTPUT->footer();
