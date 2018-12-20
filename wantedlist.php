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
 * File : wantedlist.php
 * Page where teachers can list the students who wanted to enrol in their course and couldn't.
 */

require_once('../../config.php');
require_once('lib.php');

$courseid = required_param('id', PARAM_INT);

// Access control.
$course = $DB->get_record('course', array('id' => $courseid));
require_login($course);
$coursecontext = context_course::instance($courseid);
require_capability('moodle/course:update', $coursecontext);

// Header code.
$moodlefilename = '/local/coursesshowcase/wantedlist.php';
$pageurl = new moodle_url($moodlefilename, array('id' => $courseid));
$title = get_string('wantedlist', 'block_coursesshowcase');
$coursecontext = context_course::instance($courseid);
$PAGE->set_context($coursecontext);
$PAGE->set_course($course);
$PAGE->set_url($pageurl);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagelayout('standard');

$wantingstudents = $DB->get_records('local_coursesshowcase_wanted', array('courseid' => $courseid), 'timecreated');

echo $OUTPUT->header();
//~ echo "<p style='text-align:center;font-weight:bold;color:green'>Cette page est encore en développement.
// Elle sera prête pour l'ouverture de la plateforme le 15 septembre.</p>";

echo "<table>";
echo "<tr>";
echo "<th>".get_string('date')."</th>";
echo "<th>&nbsp;&nbsp;</th>";
echo "<th>".get_string('firstname')."</th>";
echo "<th>&nbsp;&nbsp;</th>";
echo "<th>".get_string('lastname')."</th>";
echo "<th>&nbsp;&nbsp;</th>";
echo "<th>".get_string('idnumber')."</th>";
echo "<th>&nbsp;&nbsp;</th>";
echo "<th>".get_string('email')."</th>";
echo "<th>&nbsp;&nbsp;</th>";
echo "<th>".get_string('cohort', 'cohort')."</th>";
echo "</tr>";

foreach ($wantingstudents as $wantingstudent) {

    $user = $DB->get_record('user', array('id' => $wantingstudent->userid));
    $cohortmembers = $DB->get_records('cohort_members', array('userid' => $user->id));
    echo "<tr>";
    echo "<td>".date('d/m/Y à H:i:s', $wantingstudent->timecreated)."</td>";
    echo "<td>&nbsp;&nbsp;</td>";
    echo "<td>$user->firstname</td>";
    echo "<td>&nbsp;&nbsp;</td>";
    echo "<td>$user->lastname</td>";
    echo "<td>&nbsp;&nbsp;</td>";
    echo "<td>$user->idnumber</td>";
    echo "<td>&nbsp;&nbsp;</td>";
    echo "<td>$user->email</td>";
    echo "<td>&nbsp;&nbsp;</td>";
    echo "<td>";

    foreach ($cohortmembers as $cohortmember) {

        $cohort = $DB->get_record('cohort', array('id' => $cohortmember->cohortid));
        echo $cohort->name."&nbsp;";
    }
    echo "</td>";
    echo "</tr>";
}
echo "</table>";
echo $OUTPUT->footer();
