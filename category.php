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
 * File : category.php
 * Page displaying the courses within a given category.
 */

require_once('../../config.php');
require_once("$CFG->dirroot/local/coursesshowcase/lib.php");

$categoryid = required_param('id', PARAM_INT);
$term = required_param('term', PARAM_INT);
$category = $DB->get_record('course_categories', array('id' => $categoryid));

// Header code.
$moodlefilename = '/local/coursesshowcase/category.php';
$sitecontext = context_system::instance();
$PAGE->set_context($sitecontext);
$PAGE->set_url($moodlefilename, array('id' => $categoryid));
$indexurl = new moodle_url('/', array('redirect' => 0));
$termurl = new moodle_url('/', array('redirect' => 0, 'term' => $term));
$title = get_string('sitehome');
$PAGE->set_title($title);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading($title);
$PAGE->navbar->add($title, $indexurl);
$PAGE->navbar->add(get_string('term', 'local_coursesshowcase')." $term", $termurl);
$PAGE->navbar->add($category->name);

$courses = $DB->get_records('course', array('category' => $categoryid), 'fullname');
$termcourses = local_coursesshowcase_termcourses($courses, $term);
$goodcohort = local_coursesshowcase_goodcohort();
$activecategory = $goodcohort || $category->idnumber;

echo $OUTPUT->header();
echo "<p><a href='$termurl'>".get_string('back')."</a></p>";

foreach ($termcourses as $termcourse) {

    echo local_coursesshowcase_tallcourse($termcourse, $term, $activecategory);
}
echo $OUTPUT->footer();

