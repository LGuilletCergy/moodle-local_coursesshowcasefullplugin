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
 * File : lib.php
 * Library functions
 */

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->dirroot/course/lib.php");

?>
<script>
function changeterm(wantedterm) {

    termdivs = document.getElementsByClassName('termdiv');
    nbtermdivs = termdivs.length;
    for (i = 0; i < nbtermdivs; i++) {

        if (i + 1 == wantedterm) {

            termdivs[i].style.display = 'block';
        } else {

            termdivs[i].style.display = 'none';
        }
    }
}
</script>
<style>
.coursecard, .categorycard {

    background-color: whitesmoke;
}
<!--
background-image: url('<?php echo $CFG->wwwroot; ?>/theme/image.php/fordson/theme/1530172244/noimg');
-->



.imageframe {
    <!--
    background-image: url('http://www.catalunyaexperience.fr/wp-content/uploads/2014/09/25052005-Cadaque%CC%81s-5-%C2%A9-Miguel-A%CC%81ngel-A%CC%81lvarez.jpg');
    -->
    background-color: white;
    text-align: center;
}
</style>
<?php

function local_coursesshowcase_goodcohort() {

    global $DB, $USER;
    $sitecontext = context_system::instance();

    if (has_capability('local/coursesshowcase:seeallcourses', $sitecontext)) {

        return true;
    }

    $goodcohort = false;
    if ($USER->id) {

        // Peut-être à LAURENTHACK pour ne pas hardcoder les ids des cohortes à tester.
        // C'est proche de ce qu'il y a dans allenroled donc peut-être à fusionner ?
        // Différences sur le $USER utilisé.

        $cohortmember4 = $DB->get_record('cohort_members', array('userid' => $USER->id, 'cohortid' => 393));
        if ($cohortmember4) {

            $goodcohort = true;
        }

        $cohortmember5 = $DB->get_record('cohort_members', array('userid' => $USER->id, 'cohortid' => 394));
        if ($cohortmember5) {

            $goodcohort = true;
        }
    }
    return $goodcohort;
}



/**
 * Calls the plugin during any page load.
 * @param settings_navigation $nav
 * @param context $context
 */
function local_coursesshowcase_extend_settings_navigation(settings_navigation $nav, context $context) {

    global $CFG, $COURSE, $DB, $PAGE, $USER;

    if ($COURSE->id > 1) {

        $branch = $nav->get('courseadmin');
        if ($branch) {

            $params = array('id' => $COURSE->id);
            $manageurl = new moodle_url('/local/coursesshowcase/coursedetails.php', $params);
            $managetext = get_string('coursedetails', 'local_coursesshowcase');
            $branch->add($managetext, $manageurl, $nav::TYPE_CONTAINER, null, null, null);
        }
    }

    $pagepath = $PAGE->url->get_path();
    if ($pagepath == '/') {

        // LAURENTHACKED. Utilisation du currentterm dans config.php.

        $PAGE->set_pagelayout('standard');
        $PAGE->set_heading(get_string('choosecourses', 'local_coursesshowcase'));
        $term = optional_param('term', $CFG->currentterm, PARAM_INT);
        $categorystyle = "";
        $categories = $DB->get_records('course_categories', array(), 'name');

        //L'utilisateur est-il dans une des cohortes du projet ? (UFR LSH et UFR ST)
        $goodcohort = local_coursesshowcase_goodcohort();
        echo '<div style="display:none" id="hiddenindexcontent">';
        $sitecontext = context_system::instance();
        if (has_capability('local/coursesshowcase:cohortenrolments', $sitecontext)) {

            echo "<p style='font-weight:bold'>Récapitulatif des choix</p>";
            //~ echo "Choisissez une composante :";
            echo "<table>";
            echo "<tr>";
            $composantes = array('1' => 'UFR Droit',
             '2' => 'UFR Eco-Gestion',
             '3' => 'UFR LEI',
             '4' => 'UFR LSH',
             '5' => 'UFR ST',
             '7' => 'IUT',
             'A' => 'ESPE',
             'B' => 'Sciences Po');
            foreach ($composantes as $code => $composante) {

                $cohortsurl = "$CFG->wwwroot/local/coursesshowcase/cohortenrolments.php?code=$code";
                echo "<td><a href='$cohortsurl'><button class='btn btn-secondary'>$composante</button></a> &nbsp; </td>";
                //~ if ($code == '4') {
                        //~ echo '</tr><tr>';
                //~ }
            }

            echo "</tr>";
            echo "</table>";
            echo "<p style='font-weight:bold'>------------------------</p>";
        }

        echo "<form id='termform' action='index.php'>";
        $yearterms = array(1, 2);
        foreach ($yearterms as $yearterm) {

            echo "<input type='radio' id='filterterm$yearterm' name='term' value='$yearterm'"
                    . " onclick='changeterm($yearterm)'";
            if ($term == $yearterm) {

                echo ' checked';
            }

            echo "> ".get_string('term', 'local_coursesshowcase').' '.$yearterm;
            echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        }

        echo "<input type='hidden' name='redirect' value='0'>";
        echo "</form>";
        reset($yearterms);
        foreach ($yearterms as $yearterm) {

            if ($yearterm == $term) {

                $display = 'block';
            } else {

                $display = 'none';
            }
            echo "<div id='termdiv$yearterm' class='termdiv' style='display:$display'>";

            if ($USER->id) {

                $studentassignments = $DB->get_records('role_assignments', array('userid' => $USER->id, 'roleid' => 5));
                if ($studentassignments) {

                    echo get_string('youenroledin', 'local_coursesshowcase').'<br>';
                } else {

                    echo get_string('noueyet', 'local_coursesshowcase').'<br>';
                }
            }

            echo '<br>';
            echo get_string('firstin', 'local_coursesshowcase').'<br><br>';
            if (!$goodcohort) {

                echo get_string('goodcohort', 'local_coursesshowcase').'<br><br>';
            }
            foreach ($categories as $category) {

                echo local_coursesshowcase_display_category($category, $yearterm, $goodcohort);
            }
            echo "</div>";
        }

        reset($categories);

        echo '</div>';
    }

    // Si l'utilisateur n'est inscrit à aucune UE, on le redirige vers la page "Choisir une UE".
    if ($pagepath == '/my/index.php') {

        $userenrolments = $DB->get_records('user_enrolments', array('userid' => $USER->id));
        if (!$userenrolments) {
            $chooseurl = new moodle_url('/index.php', array('redirect' => 0));
            redirect($chooseurl);
        }
    }
}

function local_coursesshowcase_termcourses($courses, $term) {

    global $DB;
    $termcourses = array();

    foreach ($courses as $course) {

        $coursedata = $DB->get_record('local_coursesshowcase_course', array('courseid' => $course->id));
        if ($coursedata) {

            if ($coursedata->oddterm && ($term == 1)) {

                $termcourses[] = $course;
            }
            if ($coursedata->eventerm && ($term == 2)) {

               $termcourses[] = $course;
            }
        }
    }
    return $termcourses;
}

function local_coursesshowcase_courseterms($coursedata) {

    $courseterms = '';
    if ($coursedata->oddterm) {

	    $courseterms .= '1';
    }
    if ($coursedata->oddterm && $coursedata->eventerm) {

	    $courseterms .= ' '.get_string('and', 'local_coursesshowcase').' ';
    }
    if ($coursedata->eventerm) {

	    $courseterms .= '2';
    }
    return $courseterms;
}

function local_coursesshowcase_display_category($category, $term, $goodcohort) {

    global $CFG, $DB;
    $width = '250px';
    $height = '150px';
    $style = 'border:1px solid gray;margin:10px;float:left;padding:10px;border-radius:5px;overflow:hidden';
    $activecategory = $goodcohort || $category->idnumber;
    //~ if ($category->idnumber) {
        //~ $categoryurl = $category->idnumber;
    //~ } else {
        $categoryurl = $CFG->wwwroot.'/local/coursesshowcase/category.php?id='.$category->id.'&term='.$term;
    //~ }

    $courses = $DB->get_records('course', array('category' => $category->id, 'visible' => 1), 'fullname');
    $termcourses = local_coursesshowcase_termcourses($courses, $term);
    if (!$termcourses) {
        return '';
    }
    $html = '';
    if ($activecategory) {

        $html .= "<a style='font-weight:bold;font-size:16' href='$categoryurl'>";
    }
    $html .= "<div style='width:$width;height:$height;$style' class='categorycard'>";
    $html .= "<div style='overflow:hidden'>";
    $html .= "<p>$category->name";
    $html .= "</p>";
    $imageheight = '70px';
    $imagewidth = '150px';
    $html .= "<div style='text-align:center;width:100%;height:$imageheight;margin:10px'>";
    foreach ($courses as $course) {

        $courseimage = local_coursesshowcase_courseimage($course, $imagewidth, $imageheight);

        if ($courseimage) {

            $html .= $courseimage;
            break;
        }
    }
    $html .= "</div>";
    $nbtermcourses = count($termcourses);
    $html .= "<p style='text-align:center;font-weight:normal;color:black;font-size:12px'>$nbtermcourses UE";
    $html .= "</p>";
    $html .= "</div>";
    $html .= "</div>";

    if ($activecategory) {
        $html .= "</a>";
    }
    return $html;
}

function local_coursesshowcase_display_course($course, $term) {

    global $CFG, $DB;
    if (!$course->visible) {

        return '';
    }

    $coursedata = $DB->get_record('local_coursesshowcase_course', array('courseid' => $course->id));
    $width = '280px';
    $height = '180px';
    $backgroundurl = $CFG->wwwroot.'/theme/image.php/fordson/theme/1530172244/noimg';
    $style = "border:1px solid gray;margin:10px;padding:10px;float:left;border-radius:5px;overflow:hidden";
    $courseurl = $CFG->wwwroot.'/local/coursesshowcase/course.php?id='.$course->id.'&term='.$term;
    $html = "<a style='font-weight:bold;font-size:16' href='$courseurl'><div style='width:$width;height:$height;$style'"
            . " class='coursecard'>";
    $html .= "<div style='overflow:hidden'>";
    $html .= "<p class='coursename' id='coursename$course->id'>$course->shortname</p>";
    $html .= "<div width='100%' class='imageframe'>";
    $courseimage = local_coursesshowcase_courseimage($course, '100%', '90px');

    if ($courseimage) {

        $html .= $courseimage;
    }
    $html .= "</div>";

    //~ $namelength = strlen($course->shortname);
    //~ $imageheight = '120px';
    //~ $imagewidth = '200px';
    //~ if ($namelength > 36) {
    //
            //~ $imageheight = '100px';
    //~ }
    //~ if ($namelength > 70) {
    //
            //~ $imageheight = '80px';
    //~ }
    //~ $html .= "<div style='text-align:center;width:$imagewidth;height:$imageheight;margin:10px'>";
    //~ $courseimage = local_coursesshowcase_courseimage($course, $imagewidth, $imageheight);
    //~ if ($courseimage) {
    //
        //~ $html .= $courseimage;
    //~ }
    //~ $html .= "</div>";
    //~ if ($coursedata) {
    //
        //~ $courseterms = local_coursesshowcase_courseterms($coursedata);
        //~ if (is_numeric($courseterms)) {
        //
                //~ $s = '';
        //~ } else {
        //
                //~ $s = 's';
        //~ }
        //~ $html .= "<p style='text-align:center;font-weight:normal;color:black;font-size:12px'>Semestre".$s."
        // $courseterms</p>";
    //~ }

    $html .= "</div>";
    $html .= "</div></a>";
    return $html;
}

function local_coursesshowcase_tallcourse($course, $term, $activecategory) {

    global $CFG, $DB;
    if (!$course->visible) {

        return '';
    }
    $coursedata = $DB->get_record('local_coursesshowcase_course', array('courseid' => $course->id));
    $category = $DB->get_record('course_categories', array('id' => $course->category));
    $needsredirection = local_coursesshowcase_needsredirection($category);
    $width = '280px';
    $height = '310px';
    $imageheight = '150px';
    $titleheight = '90px';
    $backgroundurl = $CFG->wwwroot.'/theme/image.php/fordson/theme/1530172244/noimg';
    $style = "border:1px solid gray;margin:10px;float:left;border-radius:5px;overflow:hidden";

    if ($needsredirection) {

        $courseurl = $category->idnumber;
    } else {

        $courseurl = $CFG->wwwroot.'/local/coursesshowcase/course.php?id='.$course->id.'&term='.$term;
    }

    $html = '';

    if ($activecategory) {

        $html .= "<a style='font-weight:bold;font-size:16' href='$courseurl'>";
    }

    $html .= "<div style='width:$width;height:$height;$style'>";

    // Image.
    $html .= "<div style='width:$width;height:$imageheight;text-align:center' class='coursecard'>";
    $courseimage = local_coursesshowcase_courseimage($course, '100%', $imageheight);

    if ($courseimage) {

        $html .= $courseimage;
    }
    $html .= "</div>";
    // Title.
    $html .= "<div style='padding:10px;height:$titleheight'>";
    $html .= "<p class='coursename' id='coursename$course->id'>$course->shortname</p>";
    $html .= "</div>";
    // Infos.
    $coursecontext = context_course::instance($coursedata->courseid);
    $remainingplaces = local_coursesshowcase_freerooms($coursecontext, $coursedata);
    $html .= "<div style='padding:10px;color:black;font-size:13px;font-weight:normal'>";
    $html .= "<table width='100%'><tr>";

    if (!$needsredirection) {

        $html .= "<td style='text-align:left;width:50%'>$coursedata->level $coursedata->training</td>";
    }
    if (!$needsredirection && $category->idnumber != "Culture"/*|| !$remainingplaces*/) {

        $html .= "<td style='text-align:right;width:50%'>".local_coursesshowcase_numbercolor($remainingplaces).""
                . " places restantes</td>";
    }

    $html .= "</tr></table>";
    $html .= "</div>";
    $html .= "</div>";

    if ($activecategory) {

        $html .= "</a>";
    }
    return $html;
}

function local_coursesshowcase_needsredirection($category) {

    if ($category->idnumber) {

        $prefix = substr($category->idnumber, 0, 4);
        if ($prefix == 'http') {

            return true;
        } else {

            return false;
        }
    } else {

        return false;
    }
}

function local_coursesshowcase_courseimage($courserecord, $imagewidth, $imageheight) {

    global $CFG;
    $course = new course_in_list($courserecord);
    $content = '';
    $contentimages = $contentfiles = '';

    foreach ($course->get_course_overviewfiles() as $file) {

        $isimage = $file->is_valid_image();
        $url = file_encode_url("$CFG->wwwroot/pluginfile.php",
                '/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
                $file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage);

        if ($isimage) {

            $contentimages .= html_writer::tag('div',
            html_writer::empty_tag('img', array('src' => $url, 'style' =>
                "max-width:$imagewidth;max-height:$imageheight")), array('class' => 'courseimage'));
        } else {

            $image = $this->output->pix_icon(file_file_icon($file, 24), $file->get_filename(), 'moodle');
            $filename = html_writer::tag('span', $image, array('class' => 'fp-icon')).
                    html_writer::tag('span', $file->get_filename(), array('class' => 'fp-filename'));
            $contentfiles .= html_writer::tag('span',
                    html_writer::link($url, $filename),
                    array('class' => 'coursefile fp-filename-icon'));
        }
    }

    $content .= $contentimages. $contentfiles;
    return $content;
}

function local_coursesshowcase_coursecontacts($courserecord, $size) {

    global $DB;
    $course = new course_in_list($courserecord);
    $content = "<div style='font-size:$size;margin:2'>";

    if ($course->has_course_contacts()) {

        $coursecontacts = $course->get_course_contacts();
        $courseteachers = array();

        foreach ($coursecontacts as $coursecontact) {

            if ($coursecontact['role']->shortname == 'editingteacher') {
                $courseteachers[] = $coursecontact;
            }
        }
        $nbteachers = count($courseteachers);

        if ($nbteachers > 1) {

            $s = 's';
        } else {

            $s = '';
        }
        $content .= '<span style="font-weight:bold">'.$coursecontact['rolename'].$s.' :</span>';
        $content .= '<ul>';
        $numteacher = 1;

        foreach ($courseteachers as $courseteacher) {

            $user = $DB->get_record('user', array('id' => $courseteacher['user']->id));
            /*$name = html_writer::link(new moodle_url('/user/view.php',
                                                     array('id' => $courseteacher['user']->id, 'course' => SITEID)),
                                                     $courseteacher['username']);*/
            $name = $user->firstname.' '.$user->lastname;
            if ($numteacher == 5) {

                $content .= '<li>etc.</li>';
                break;
            } else {

                $content .= "<li>$name</li>";
                $numteacher++;
            }
        }

        $content .= '</ul>';
    }

    $content .= '</div>';
    return $content;
}

function local_coursesshowcase_canenrol($courseid) {

    global $DB, $USER;
    $coursecohorts = $DB->get_records('local_coursesshowcase_cohort', array('courseid' => $courseid));

    if (!$coursecohorts) {

        return true;
    }
    foreach ($coursecohorts as $coursecohort) {

        $cohortmember = $DB->record_exists('cohort_members',
                array('cohortid' => $coursecohort->cohortid, 'userid' => $USER->id));

        if ($cohortmember) {

            return true;
        }
    }
    return false;
}

function local_coursesshowcase_enrolstudent($userid, $selfenrolmethod, $coursecontext, $needsredirection) {

    global $DB, $USER;
    $now = time();

    // Activate self-enrol method if it's not already.
    if ($selfenrolmethod->status) {

        $selfenrolmethod->status = 0;
        $DB->update_record('enrol', $selfenrolmethod);
    }

    $enrolment = $DB->get_record('user_enrolments', array('userid' => $userid, 'enrolid' => $selfenrolmethod->id));
    if (!$enrolment) {

        $enrolment = new stdClass();
        $enrolment->enrolid = $selfenrolmethod->id;
        $enrolment->userid = $userid;
        $enrolment->timestart = $now;
        $enrolment->timecreated = $now;
        $enrolment->timemodified = $now;
        $enrolment->modifierid = $USER->id;
        $DB->insert_record('user_enrolments', $enrolment);
    }

    $roleassignment = $DB->get_record('role_assignments',
            array('contextid' => $coursecontext->id, 'userid' => $userid));

    if (!$roleassignment) {

        $roleassignment = new stdClass();
        $roleassignment->roleid = 5;
        $roleassignment->contextid = $coursecontext->id;
        $roleassignment->userid = $userid;
        $roleassignment->timemodified = $now;
        $roleassignment->modifierid = $USER->id;
        $DB->insert_record('role_assignments', $roleassignment);
    }

    if (!$needsredirection) {
        $headers = 'From: noreply@ue-libres.u-cergy.fr' . "\r\n" .'MIME-Version: 1.0' . "\r\n".
        'Reply-To: noreply@ue-libres.u-cergy.fr' . "\r\n" .'Content-type: text/html; charset=utf-8' . "\r\n".
        'X-Mailer: PHP/' . phpversion();
        $to = $USER->email;
        $course = $DB->get_record('course', array('id' => $coursecontext->instanceid));
        $subject = "Choix de l'UE libre $course->fullname";
        $message = "<br><h3>UE libres - Université de Cergy-Pontoise</h3>";
        $message .= "<p>Bonjour,<br>Votre choix de l'UE libre $course->fullname est enregistré.</p>";
        $message .= "<p>Ce choix n'a pas valeur d'inscription définitive. En particulier,"
                . " il sera annulé si vous en formulez un autre, sur cette plateforme ou ailleurs.</p>";
        $message .= "<p>L'équipe UE libres</p>";
        $message .= "<p>PS : Ceci est un message automatique. Merci de ne pas y répondre.</p>";
        mail($to, $subject, $message, $headers);
    }
}

function local_coursesshowcase_numbercolor($number) {

    if ($number > 5) {

        $color = 'green';
    } else if ($number > 0) {

        $color = 'orange';
    } else {

        $color = 'red';
    }

    $output = "<span style='font-weight:bold;color:$color'>$number</span>";
    return $output;
}

function local_coursesshowcase_freerooms($coursecontext, $coursedata) {

    // LAURENTHACKED. Utilisation du timestamp depuis config.php.

    global $DB, $CFG;

    $sql = "SELECT DISTINCT userid FROM {role_assignments} WHERE roleid = 5 AND"
            . " contextid = $coursecontext->id AND timemodified > $CFG->currenttermregistrationstart";
    $students = $DB->get_records_sql($sql);

    $nbstudents = count($students);
    //~ $nbstudents = $DB->count_records('role_assignments', array('roleid' => 5, 'contextid' => $coursecontext->id));
    $remainingplaces = $coursedata->capacity - $nbstudents;

    if ($remainingplaces < 0) {

        $remainingplaces = 0;
    }
    return $remainingplaces;
}

/**
 * Unenrol this user from all the courses where he is self-enroled, except Engagement UEs.
 * @param int $userid
 */
function local_coursesshowcase_unenrol($userid) {

    // LAURENTHACKED. Utilisation du timestamp depuis config.php.

    global $DB, $CFG;
    $userenrolments = $DB->get_records('user_enrolments', array('userid' => $userid));
    foreach ($userenrolments as $userenrolment) {
        if ($userenrolment->timecreated < $CFG->currenttermregistrationstart) {
            continue;
        }
        $enrolmethod = $DB->get_record('enrol', array('id' => $userenrolment->enrolid));

        if ($enrolmethod->enrol == 'self') {

            $course = $DB->get_record('course', array('id' => $enrolmethod->courseid));

            if ($course->category != 14) { //14 : Engagement étudiant

                $context = $DB->get_record('context', array('contextlevel' => 50, 'instanceid' => $course->id));
                $DB->delete_records('role_assignments', array('contextid' => $context->id, 'userid' => $userid));
                $DB->delete_records('user_enrolments', array('id' => $userenrolment->id));
            }
        }
    }

    //~ $studentassignments = $DB->get_records('role_assignments', array('userid' => $userid, 'roleid' => 5));
    //~ foreach ($studentassignments as $studentassignment) {
        //~ $context = $DB->get_record('context', array('id' => $studentassignment->contextid));
        //~ $courseid = $context->instanceid;
        //~ $selfenrolmethod = $DB->get_record('enrol', array('enrol' => 'self', 'courseid' => $courseid));
        //~ $DB->delete_records('user_enrolments', array('enrolid' => $selfenrolmethod->id, 'userid' => $userid));
        //~ $DB->delete_records('role_assignments', array('id' => $studentassignment->id));
    //~ }
}

/**
 * Lists the courses from which this user will be unenrolled if (s)he enrols in a new one.
 */
function local_coursesshowcase_previouscourses() {

    // LAURENTHACKED. Utilisation du timestamp depuis config.php.

    global $DB, $USER, $CFG;
    $previouscourses = array();
    $userenrolments = $DB->get_records('user_enrolments', array('userid' => $USER->id));

    foreach ($userenrolments as $userenrolment) {
        if ($userenrolment->timecreated < $CFG->currenttermregistrationstart) {
            continue;
        }
        $enrolmethod = $DB->get_record('enrol', array('id' => $userenrolment->enrolid));

        if ($enrolmethod->enrol == 'self') {

            $course = $DB->get_record('course', array('id' => $enrolmethod->courseid));

            if ($course->category != 14) { //14 : Engagement étudiant.

                $previouscourses[] = $course;
            }
        }
    }
    return $previouscourses;
}
