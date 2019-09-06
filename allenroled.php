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
 * File : allenroled.php
 * Lists all enroled users.
 */

require_once('../../config.php');
require_once('lib.php');

// Access control.
require_login();
$sitecontext = context_system::instance();
require_capability('local/coursesshowcase:manage', $sitecontext);

// Header code.
$moodlefilename = '/local/coursesshowcase/allenroled.php';
$pageurl = new moodle_url($moodlefilename, array('id' => $courseid));
$title = "Tous les inscrits";
$PAGE->set_context($sitecontext);
$PAGE->set_url($pageurl);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagelayout('standard');

$OUTPUT->header();

// LAURENTHACKED. Utilisation du timestamp de config.php .

$sql = "SELECT ra.id, ra.userid, ctx.instanceid AS courseid FROM `mdl_role_assignments` ra,"
        . " mdl_context ctx, mdl_user u WHERE ra.roleid = 5 AND ctx.id = ra.contextid ORDER BY ra.userid AND"
        . " ra.timemodified > $CFG->currenttermregistrationstart";

$roleassignments = $DB->get_records('role_assignments', array('roleid' => 5), 'userid');

$previoususerid = 0;
$previouscourseid = 0;

foreach ($roleassignments as $roleassignment) {

    $user = $DB->get_record('user', array('id' => $roleassignment->userid));
    $goodcohort = false;

    if ($user->id) {

        // Peut-être à LAURENTHACK pour ne pas hardcoder les ids des cohortes à tester.
        //  C'est la même chose que dans lib.php avec un $user différent. Peut-être à fusionner.
        // Un peu LAURENTHACK pour ne pas donner tous les accès aux LSH au semestre 1.

        $cohortmember4 = $DB->get_record('cohort_members', array('userid' => $user->id, 'cohortid' => 393));

        if ($cohortmember4 && $CFG->currentterm == 2) {

            $goodcohort = true;
        }

        $cohortmember5 = $DB->get_record('cohort_members', array('userid' => $user->id, 'cohortid' => 394));

        if ($cohortmember5) {

            $goodcohort = true;
        }
    }

    $context = $DB->get_record('context', array('id' => $roleassignment->contextid));
    $course = $DB->get_record('course', array('id' => $context->instanceid));
    $selfenrolmethod = $DB->get_record('enrol', array('courseid' => $course->id, 'enrol' => 'self'));
    $userenrolment = $DB->get_record('user_enrolments', array('enrolid' => $selfenrolmethod->id, 'userid' => $user->id));

    if (!$userenrolment) {

        $previouscourseid = $course->id;
        continue;
    }

    $category = $DB->get_record('course_categories', array('id' => $course->category));
    echo "$user->firstname | $user->lastname | $user->email | $course->fullname | $category->name";
    $cohortmembers = $DB->get_records('cohort_members', array('userid' => $user->id));

    if ($cohortmembers) {

        echo " | ";
        foreach ($cohortmembers as $cohortmember) {

            $cohort = $DB->get_record('cohort', array('id' => $cohortmember->cohortid));
            echo "$cohort->name ";
        }
    }

    if (!$category->idnumber && !$goodcohort) {

        echo " | <span style='color:red'>Mauvaise composante !</span>";
        $DB->delete_records('user_enrolments', array('id' => $userenrolment->id));
        $DB->delete_records('role_assignments', array('id' => $roleassignment->id));
        echo " | <span style='color:green'>Désinscrit !</span>";
    }

    if (($previoususerid == $user->id) && ($previouscourseid != $previouscourse->id) && ($category->id != 14)) {

        echo " | <span style='color:red'>Plusieurs inscriptions !</span>";
    }

    $previoususerid = $user->id;
    $previouscourseid = $course->id;
    echo '<br>';
}

$OUTPUT->footer();
