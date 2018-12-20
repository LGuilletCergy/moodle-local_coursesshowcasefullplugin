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
 * Initially developped for :
 * Université de Cergy-Pontoise
 * 33, boulevard du Port
 * 95011 Cergy-Pontoise cedex
 * FRANCE
 *
 * Adds courses showcase on index page (requires additionnal HTML).
 *
 * @package   local_coursesshowcase
 * @copyright 2018 Brice Errandonea <brice.errandonea@u-cergy.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * File : coursedetails.php
 * Form page where teachers can give details about the course. These details will be shown in the showcase.
 */

require_once('../../config.php');
require_once('lib.php');
require_once('coursedetails_form.php');

$courseid = required_param('id', PARAM_INT);

// Access control.
$course = $DB->get_record('course', array('id' => $courseid));
require_login($course);
$coursecontext = context_course::instance($courseid);
require_capability('moodle/course:update', $coursecontext);

// Header code.
$moodlefilename = '/local/coursesshowcase/coursedetails.php';
$pageurl = new moodle_url($moodlefilename, array('id' => $courseid));
$title = "Détails sur $course->fullname";
$coursecontext = context_course::instance($courseid);
$PAGE->set_context($coursecontext);
$PAGE->set_course($course);
$PAGE->set_url($pageurl);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagelayout('standard');

// Prepare datas for the form.
$coursedetails = $DB->get_record('local_coursesshowcase_course', array('courseid' => $courseid));

if (!$coursedetails) {

    $coursedetails = new stdClass();
    $coursedetails->courseid = $courseid;
    $coursedetails->id = $DB->insert_record('local_coursesshowcase_course', $coursedetails);
}

// Form instanciation.
$customdatas = (array)$coursedetails;
$mform = new coursedetails_form($pageurl, $customdatas);

// Three possible states.
$courseurl = new moodle_url('/course/view.php', array('id' => $courseid));

if ($mform->is_cancelled()) {

    redirect($courseurl);
} else if ($submitteddata = $mform->get_data()) {

    $submitteddata->id = $coursedetails->id;
    $DB->update_record('local_coursesshowcase_course', $submitteddata);
    redirect($courseurl);
} else {
    
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}
