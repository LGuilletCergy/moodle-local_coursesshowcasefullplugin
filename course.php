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
 * File : course.php
 * Page showcasing a given course.
 */

require_once('../../config.php');
require_once("$CFG->dirroot/local/coursesshowcase/lib.php");

$courseid = required_param('id', PARAM_INT);
$term = optional_param('term', 1, PARAM_INT);
$askenrol = optional_param('enrol', 0, PARAM_INT);
$wanted = optional_param('wanted', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);
$course = $DB->get_record('course', array('id' => $courseid));
$category = $DB->get_record('course_categories', array('id' => $course->category));
$coursedata = $DB->get_record('local_coursesshowcase_course', array('courseid' => $courseid));

// Header code.
$moodlefilename = '/local/coursesshowcase/course.php';
$sitecontext = context_system::instance();
$PAGE->set_context($sitecontext);
$PAGE->set_url($moodlefilename, array('id' => $courseid, 'term' => $term, 'enrol' => $askenrol,
                                      'wanted' => $wanted, 'confirm' => $confirm));
$indexurl = new moodle_url('/', array('redirect' => 0));
$categoryurl = new moodle_url('/local/coursesshowcase/category.php', array('id' => $category->id, 'term' => $term));
$courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
$title = get_string('sitehome');
$PAGE->set_title($title);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading($title);
$PAGE->navbar->add($title, $indexurl);
$PAGE->navbar->add($category->name, $categoryurl);
$PAGE->navbar->add($course->fullname);

$coursecontext = context_course::instance($courseid);
$remainingplaces = local_coursesshowcase_freerooms($coursecontext, $coursedata);
$imageheight = '300px';
$imagewidth = '300px';

if ($USER->id) {

    $isenroled = $DB->record_exists('role_assignments',
            array('contextid' => $coursecontext->id, 'userid' => $USER->id));
    $previouscourses = local_coursesshowcase_previouscourses();
    //~ $previouscourses = $DB->get_records('role_assignments', array('roleid' => 5, 'userid' => $USER->id));
} else {

    $isenroled = false;
    $previouscourses = null;
}

$canenrol = local_coursesshowcase_canenrol($courseid);

echo $OUTPUT->header();

echo "<p><a href='$categoryurl'>".get_string('back')."</a></p>";
echo "<h1>$course->fullname</h1><br>";
$courseimage = local_coursesshowcase_courseimage($course, $imagewidth, $imageheight);

if ($courseimage) {

    echo "<div style='float:right'>$courseimage</div>";
}

$courseterms = local_coursesshowcase_courseterms($coursedata);
$needsredirection = local_coursesshowcase_needsredirection($category);

if ($needsredirection) {

    //~ header("Location: $category->idnumber");
    echo "<script type='text/javascript'>document.location.replace('$category->idnumber')</script>";
    exit;
}

if (!$needsredirection) {

    echo "<p><span style='font-weight:bold'>".get_string('taughtonterms', 'local_coursesshowcase')." :"
            . " </span>$courseterms</p>";

    if ($coursedata->capacity && $category->idnumber != "Culture") {

        echo "<p><span style='font-weight:bold'>".get_string('capacity', 'local_coursesshowcase')." :"
                . " </span>$coursedata->capacity étudiant(e)s</p>";
        echo "<p><span style='font-weight:bold'>".get_string('freerooms', 'local_coursesshowcase')." : </span>";
        echo local_coursesshowcase_numbercolor($remainingplaces);
        echo "</p>";
    }

    if ($coursedata->nbhours) {

        echo "<p><span style='font-weight:bold'>".get_string('nbhours', 'local_coursesshowcase')." :"
                . " </span>$coursedata->nbhours";
        if ($coursedata->hoursperweek) {
                echo " ($coursedata->hoursperweek ".get_string('hoursperweek', 'local_coursesshowcase').")";
        }
        echo "</p>";
    }

    if ($coursedata->place) {

        echo "<p><span style='font-weight:bold'>".get_string('place', 'local_coursesshowcase')." :"
                . " </span>$coursedata->place</p>";
    }
    if (!$coursedata->ects) {

        $coursedata->ects = get_string('dependsonfaculty', 'local_coursesshowcase');
    }
    echo "<p><span style='font-weight:bold'>".get_string('ects', 'local_coursesshowcase')." :"
            . " </span>$coursedata->ects</p>";

    echo local_coursesshowcase_coursecontacts($course, 18);

    echo "<p><span style='font-weight:bold'>".get_string('evaluation', 'local_coursesshowcase')." : </span>";

    if ($coursedata->evalcc && ($coursedata->evalcc != 'Non')) {

        echo get_string('evalcc', 'local_coursesshowcase');

        if ($coursedata->evalcc != 'Oui') {

            echo " : $coursedata->evalcc";
        }
        echo ' &nbsp; &nbsp; ';
    }

    if ($coursedata->evalct && ($coursedata->evalct != 'Non')) {

        echo get_string('evalct', 'local_coursesshowcase');
        if ($coursedata->evalct != 'Oui') {

            echo " : $coursedata->evalct";
        }
        echo ' &nbsp; &nbsp; ';
    }

    if ($coursedata->evalother && ($coursedata->evalother != 'Non')) {

        if ($coursedata->evalother != 'Oui') {

            echo "$coursedata->evalother</p>";
        } else {

            echo get_string('evalother', 'local_coursesshowcase');
        }
    }
    echo "</p>";

    if ($coursedata->level) {

        echo "<p><span style='font-weight:bold'>".get_string('level', 'local_coursesshowcase')." :"
                . " </span>$coursedata->level</p>";
    }
    if ($coursedata->training) {

        echo "<p><span style='font-weight:bold'>".get_string('besttrainings', 'local_coursesshowcase')." :"
                . " </span>$coursedata->training</p>";
        echo "<p style='color:red;text-align:justify'>".get_string('ifnotraining', 'local_coursesshowcase')."</p>";

    } else if  (($coursedata->level != 'Tous niveaux')) {

        echo "<p style='color:red;text-align:justify'>".get_string('ifnolevel', 'local_coursesshowcase')."</p>";
    }
    if ($coursedata->leisure) {

        echo "<p style='font-weight:bold;color:green'>".get_string('leisure', 'local_coursesshowcase')."</p>";
    }
    if ($coursedata->competition) {

        echo "<p style='font-weight:bold;color:red'>".get_string('competition', 'local_coursesshowcase')."</p>";
    }
}

echo "<h2>".get_string('description')."</h2>";
echo "<p style='text-align:justify'>".$course->summary."</p>";

if ($coursedata->holderid) {

    $holder = $DB->get_record('local_coursesshowcase_holder', array('id' => $coursedata->holderid));
    echo "<h2>".get_string('organizer', 'local_coursesshowcase')."</h2>";
    echo "<h3>$holder->name</h3>";

    if ($holder->address) {

        echo "$holder->address<br>";
    }
    if ($holder->phone) {

        echo "$holder->phone<br>";
    }
    if ($holder->contactmail) {

        echo "$holder->contactmail<br>";
    }
}

if ($category->idnumber != "Culture") {

    echo '<p id="showcasebottom"></p>';

    $goodcohort = local_coursesshowcase_goodcohort();
    if (!$goodcohort && !$category->idnumber) {
        echo "<p style='font-weight:bold;color:red;text-align:center'>";
        echo get_string('badcohort', 'local_coursesshowcase');
        echo "</p>";
    } else if ($coursedata->oddterm) { // La condition devra être changée lors du passage au nouveau premier semestre.

        echo "<p style='text-align:center;color:red;font-weight:bold'>".get_string('notoneventerm',
                'local_coursesshowcase')."</p>";
    } else if ($isenroled) {

        echo "<p style='text-align:center'><span style='font-weight:bold;color:green'>".get_string('youenroled'
                , 'local_coursesshowcase')."</span>";
        echo "&nbsp;&nbsp;<a href='$CFG->wwwroot/course/view.php?id=$courseid'><button class='btn btn-primary'>";
        echo get_string('gotocourse', 'local_coursesshowcase')."</button></a></p>";
    } else if ($remainingplaces) {

        if ($askenrol) {

            require_login();
            $selfenrolmethod = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'self'));
            if (!$canenrol || !$selfenrolmethod) {
                echo "<p style='font-weight:bold;color:red;text-align:center'>";
                echo get_string('cantenrol', 'local_coursesshowcase');
                echo "</p>";
            } else if ($confirm) {

                if ($course->category != 14) { //14 : Engagement étudiant.

                    local_coursesshowcase_unenrol($USER->id);
                }

                local_coursesshowcase_enrolstudent($USER->id, $selfenrolmethod, $coursecontext, $needsredirection);

                if ($needsredirection) {

                    echo "<script type='text/javascript'>document.location.replace('$category->idnumber')</script>";
                } else {

                    $showcaseurl = new moodle_url($moodlefilename, array('id' => $courseid, 'term' => $term));
                    redirect($showcaseurl);
                }
            } else {

                echo "<p style='text-align:center'>";
                if ($previouscourses && $category->id != 14) { //14: Engagement étudiant
                    echo "<span style='font-weight:bold;color:orange'>".get_string('reallyenrolunenrol',
                            'local_coursesshowcase')."</span><br>";
                    foreach ($previouscourses as $previouscourse) {
                        echo "$previouscourse->fullname<br>";
                    }
                } else {
                    echo "<span style='font-weight:bold;color:orange'>".get_string('reallyenrol',
                            'local_coursesshowcase')."</span>";
                }

                echo "<br><a href='course.php?id=$courseid&term=$term&enrol=1&confirm=1#showcasebottom'>"
                        . "<button class='btn btn-success'>".get_string('yes')."</button></a>";
                echo "&nbsp;&nbsp;<a href='course.php?id=$courseid&term=$term'>".get_string('cancel')."</a>";
                echo "</p>";
            }
        } else {
            //Bouton "Je choisis cette UE".
            $sitecontext = context_system::instance();

            if (has_capability('local/coursesshowcase:openchoices', $sitecontext)) {
                //A retirer pour ouvrir les choix
                echo "<p style='text-align:center'><a href='course.php?id=$courseid&term=$term&enrol=1"
                        . "#showcasebottom'><button class='btn btn-success'>".
                        get_string('ienrol', 'local_coursesshowcase')."</button></a></p>";
            }  //A retirer pour ouvrir les choix
        }
    } else {

        // Il n'y a plus de place.
        if ($wanted) {

            //L'utilisateur vient de cliquer sur "J'aurais voulu choisir cette UE".
            require_login();
            $latedemand = new stdClass();
            $latedemand->courseid = $courseid;
            $latedemand->userid = $USER->id;
            $latedemand->timecreated = time();
            $latedemand->id = $DB->insert_record('local_coursesshowcase_wanted', $latedemand);
            $recordeddemand = true;
        } else if ($USER->id) {

            $recordeddemand = $DB->record_exists('local_coursesshowcase_wanted',
                    array('courseid' => $courseid, 'userid' => $USER->id));
        } else {

            $recordeddemand = false;
        }
        if ($recordeddemand) {

            echo "<p style='text-align:center'><span style='font-weight:bold'>".get_string('recordedwish',
                    'local_coursesshowcase')."</span>";
        } else {

            if (has_capability('local/coursesshowcase:openchoices', $sitecontext)) {  //A retirer pour ouvrir les choix

                // Bouton "J'aurais voulu choisir cette UE".
                echo '<p>'.get_string('noroomleft', 'local_coursesshowcase').'</p>';
                echo "<p style='text-align:center'><a href='course.php?id=$courseid&term=$term&wanted=1#"
                        . "showcasebottom'>";
                echo "<button class='btn btn-danger'>".get_string('wantedtoenrol',
                        'local_coursesshowcase')."</button></a></p>";
            }  //A retirer pour ouvrir les choix
        }
    }
} else {

    echo "<p style='text-align:center'>".get_string('contactculture', 'local_coursesshowcase')."</p>";
}
echo $OUTPUT->footer();
