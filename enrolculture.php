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
 * File : enrolculture.php
 * Import enrolments from Service Culture
 */

define('CLI_SCRIPT', true);
require_once('../../config.php');
require_once('lib.php');

$fichiercsv = fopen('Culture.csv', 'r');

$nblines = 0;
if ($fichiercsv == FALSE) {

    echo "Impossible d'ouvrir le fichier CSV<br>";
} else {

    $studentrole = $DB->get_record('role', array('shortname' => 'student'));
    while (($data = fgetcsv($fichiercsv, 200, ";")) !== FALSE) {

        $line = explode(',', $data[0]);
        if (is_numeric($line[0])) {

            print_object($line);
            $user = $DB->get_record('user', array('idnumber' => $line[0]));
            $course = $DB->get_record('course', array('idnumber' => $line[1]));

            if ($user && $course) {

                echo "$user->firstname $user->lastname : $course->fullname\n";
                $coursecontext = $DB->get_record('context', array('contextlevel' => 50, 'instanceid' => $course->id));
                $selfenrolmethod = $DB->get_record('enrol', array('enrol' => 'self', 'courseid' => $course->id));

                if ($selfenrolmethod && $coursecontext) {

                    local_coursesshowcase_enrolstudent($user->id, $selfenrolmethod, $coursecontext, true);
                    $nblines++;
                }
            }
        }
    }
    echo "$nblines lignes\n";
}
