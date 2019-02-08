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
 * File : cohortenrolments.php
 * Lists enrolments for each members of a given cohort.
 */

require_once('../../config.php');
//~ require_once('lib.php');

$cohortid = optional_param('id', 0, PARAM_INT); // Cohorte test : 131
$export = optional_param('export', 0, PARAM_INT);
$code = optional_param('code', '', PARAM_ALPHANUM);
$params = array('id' => $cohortid, 'export' => $export, 'code' => $code);

// Access control.
require_login();
$sitecontext = context_system::instance();
require_capability('local/coursesshowcase:cohortenrolments', $sitecontext);

// Header code.
$moodlefilename = '/local/coursesshowcase/cohortenrolments.php';
$pageurl = new moodle_url($moodlefilename, $params);
$title = "Choix des étudiants d'une cohorte";
$PAGE->set_context($sitecontext);
$PAGE->set_url($pageurl);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add($title, $pageurl);

if (!$export) {

    echo $OUTPUT->header();

    //~ $composantes = array('1' => 'UFR Droit',
                         //~ '2' => 'UFR Eco-Gestion',
                         //~ '3' => 'UFR LEI',
                         //~ '4' => 'UFR LSH',
                         //~ '5' => 'UFR ST',
                         //~ '7' => 'IUT',
                         //~ 'A' => 'ESPE',
                         //~ 'B' => 'Sciences Po');

    echo "<p>";
    echo '<form enctype="multipart/form-data" action="cohortenrolments.php" method="post">';
    echo "<select name='id'>";
    $vetsql = "SELECT id, name FROM {cohort} WHERE idnumber LIKE '$CFG->yearprefix-$code%' AND"
            . " idnumber NOT LIKE '%-%-%' ORDER BY name";

    $vets = $DB->get_records_sql($vetsql);

    foreach ($vets as $vet) {

        echo "<option value='$vet->id'";

        if ($cohortid && ($cohortid == $vet->id)) {

            echo " selected ";
        }
        echo ">$vet->name</option>";
    }

    echo "</select> ";
    echo "<input type='hidden' name='code' value='$code' />";
    echo "<input type='submit' value='OK' />";
    echo '</form>';
    echo "</p>";
}

if ($cohortid) {

    $cohortuserlines = local_coursesshowcase_cohortenrolments($cohortid, $code, $export);
}

//--------------------

function local_coursesshowcase_cohortuserlines($cohort, $code) {

    global $DB;
    $sql = "SELECT u.*
            FROM {cohort_members} cm, {user} u
            WHERE cm.cohortid = $cohort->id AND u.id = cm.userid
            ORDER BY u.lastname, u.firstname";

    $users = $DB->get_recordset_sql($sql);
    $userlines = array();
    foreach ($users as $user) {

        $studentassignments = $DB->get_records('role_assignments', array('userid' => $user->id, 'roleid' => 5), 'contextid');
        $user->enroledin = '';
        $previouscourseid = 0;
        foreach ($studentassignments as $studentassignment) {
			if ($studentassignment->timemodified < 1546766069) {
			    continue;
			}
//~ global $USER; if ($USER->username == 'berrando') print_object($studentassignment);

            $context = $DB->get_record('context', array('id' => $studentassignment->contextid));
            $course = $DB->get_record('course', array('id' => $context->instanceid));
            $category = $DB->get_record('course_categories', array('id' => $course->category));
        /*  $needsredirection = local_coursesshowcase_needsredirection2($category);
	        if ($needsredirection) {
                continue;
	        }*/
            if ($course->id != $previouscourseid) {

                $user->enroledin .= "$course->fullname  ";

                if ($course->idnumber) {

                    $user->enroledin .= "($course->idnumber) ";
                }
            }
            $previouscourseid = $course->id;
        }

        $studentwishes = $DB->get_records('local_coursesshowcase_wanted', array('userid' => $user->id), 'courseid');
        $user->wanted = '';
        $previouswishcourseid = 0;

        foreach ($studentwishes as $studentwish) {
			if ($studentwish->timecreated < 1546766069) {
			    continue;
			}
            $wishcourse = $DB->get_record('course', array('id' => $studentwish->courseid));

            if ($wishcourse->id != $previouswishcourseid) {

                $user->wanted .= "$wishcourse->fullname ";
            }

            $previouswishcourseid = $course->id;
        }
        $userline = array($code, $cohort->name, $user->firstname, $user->lastname, $user->idnumber,
            $user->email, $user->enroledin, $user->wanted);
        //~ $userline = array($user->firstname, $user->lastname, '', $user->email, $user->enroledin, $user->wanted);
        $userlines[] = $userline;
    }

    return $userlines;
}


function local_coursesshowcase_cohortenrolments($cohortid, $code, $export) {

    global $DB;
    $cohort = $DB->get_record('cohort', array('id' => $cohortid));
    $userlines = local_coursesshowcase_cohortuserlines($cohort, $code);
    $columntitles = array('Composante', 'VET', 'Prénom', 'Nom', 'Numéro étudiant', 'Courriel',
        'A choisi l\'UE libre', 'Aurait voulu l\'UE libre');

    if ($export) {

        local_coursesshowcase_cohortcsv($cohort, $userlines, $columntitles);
    } else {

        local_coursesshowcase_cohorthtml($cohort, $userlines, $columntitles, $code);
    }

    return $userlines;
}

/**
 * Apply utf8_decode to all the cells of an array.
 * @param array of strings $array
 * @return array of strings
 */
function local_coursesshowcase_utf8($array) {

    $decodedarray = array();
    foreach ($array as $cell) {

        $decodedarray[] = utf8_decode($cell);
    }
    return $decodedarray;
}

function local_coursesshowcase_cohortcsv($cohort, $userlines, $columntitles) {

    global $CFG;
    require_once($CFG->libdir . '/csvlib.class.php');
    $csvexporter = new csv_export_writer('semicolon');
    $title = "Choix pour $cohort->name";
    $csvexporter->set_filename($title);
    $csvexporter->add_data(local_coursesshowcase_utf8(array($title)));
    $columntitles = local_coursesshowcase_utf8($columntitles);
    $csvexporter->add_data($columntitles);

    foreach ($userlines as $userline) {

        $userline = local_coursesshowcase_utf8($userline);
        $csvexporter->add_data($userline);
    }

    $csvexporter->download_file();
    exit;
}

function local_coursesshowcase_cohorthtml($cohort, $userlines, $columntitles, $code) {

    global $OUTPUT;
    echo "<h2>$cohort->name</h2>";
    echo "<br><table>";
    echo "<tr>";

    foreach ($columntitles as $columntitle) {

        echo "<th>$columntitle</th>";
    }

    echo "</tr>";
    foreach ($userlines as $userline) {

        echo '<tr>';
 	foreach ($userline as $cell) {

	    echo "<td>$cell</td>";
	}
	echo '</tr>';
    }

    echo "</table><br>";

    echo '<form enctype="multipart/form-data" action="cohortenrolments.php" method="post">
            <fieldset>';
    echo "      <input name='id' type='hidden' value='$cohort->id' />";
    echo '      <input name="export" type="hidden" value="1" />';
    echo "      <input name='code' type='hidden' value='$code' />";
    echo '      <p style="text-align: center;"><input type="submit" value="Exporter vers un fichier CSV"/></p>
            </fieldset>
          </form>';
    echo $OUTPUT->footer();
}

function local_coursesshowcase_needsredirection2($category) {
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
