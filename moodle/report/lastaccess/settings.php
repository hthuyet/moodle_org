<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/adminlib.php');

$ADMIN->add('reports', new admin_externalpage('reportlastaccess', get_string('pluginname', 'report_lastaccess'),
    "$CFG->wwwroot/report/lastaccess/index.php"));

#No report setting
$settings = null;