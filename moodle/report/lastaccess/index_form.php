<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/adminlib.php');

class lastaccess_form extends moodleform {

    protected function definition() {
        global $DB;
        $mform = & $this->_form;
        //Get the couse passed to the form
        $options = array();
        $options[0] = get_string("choose");
        $options += $this->_customdata['courses'];

        $mform->addElement('select', 'course', get_string("course"), $options, 'align="center"');
        $mform->setType("course", PARAM_ALPHANUMEXT);
        $mform->addElement('date_selector', 'lastaccesseddate', get_string("from"), 'align="center"');
        $mform->setType("lastaccesseddate", PARAM_INT);
        $mform->addElement('date_selector', 'currentdate', get_string("to"), 'align="center"');
        $mform->setType("currentdate", PARAM_INT);
        $mform->addElement('submit', 'save', get_string("display", "report_lastaccess"), 'align="right"');
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        //Add
        if ($data['course'] == '0') {
            $errors['course'] = get_string("error_invalid_course");
        }
        if ($data['currentdate'] > time(date("d-m-Y"))) {
            $errors['currentdate'] = get_string("error_invalid_currentdate");
        }
        return $errors;
    }

}
