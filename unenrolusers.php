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
 * Create cohorts and add ways to manage them for teachers.
 *
 * @package   local_coursesshowacase
 * @copyright 2019 Laurent Guillet <laurent.guillet@u-cergy.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * File : unenrolusers.php
 * Unenrol all students from courses
 */

define('CLI_SCRIPT', true);
require_once( __DIR__.'/../../config.php');

require_once($CFG->dirroot . '/cohort/lib.php');
require_once($CFG->dirroot . '/lib/enrollib.php');

global $DB;

// Il y a des inscriptions auto, des inscriptions manuelles et des inscriptions par cohorte.

// D'abord les cohortes.

$cohortplugin = enrol_get_plugin('cohort');

$listenrolcohorts = $DB->get_records('enrol', array('enrol' => 'cohort'));

foreach ($listenrolcohorts as $enrolcohort) {

    $cohortplugin->delete_instance($enrolcohort);
}

// Puis les inscriptions auto et manuelle.

$selfplugin = enrol_get_plugin('self');
$manualplugin = enrol_get_plugin('manual');

$rolestudentid = $DB->get_record('role', array('shortname' => 'student'))->id;

$listselfenrolments = $DB->get_records('enrol', array('enrol' => 'self'));

foreach ($listselfenrolments as $selfenrolment) {

    $context = $DB->get_record('context', array('contextlevel' => CONTEXT_COURSE,
        'instanceid' => $selfenrolment->courseid));

    // Récupérer tous les étudiants inscrits aux cours

    $liststudentsassignments = $DB->get_records('role_assignments',
            array('roleid' => $rolestudentid, 'contextid' => $context->id));

    foreach ($liststudentsassignments as $studentassignment) {

        $sql = "SELECT * FROM {role_assignments} WHERE"
                . " roleid != $rolestudentid AND contextid = $context->id AND userid = $liststudentsassignments->userid";

        // Si il n'est pas qu'étudiant, ne lui retirer que le rôle étudiant, sinon le désinscrire.

        if ($DB->record_exists_sql($sql)) {

            $DB->delete_records('role_assignments',
                    array('roleid' => $rolestudentid, 'contextid' => $context->id,
                        'userid' => $liststudentsassignments->userid));
        } else {

            $selfplugin->unenrol_user($selfenrolment, $liststudentsassignments->userid);
        }
    }
}

$listmanualenrolments = $DB->get_records('enrol', array('enrol' => 'manual'));

foreach ($listmanualenrolments as $manualenrolment) {

    $context = $DB->get_record('context', array('contextlevel' => CONTEXT_COURSE,
        'instanceid' => $manualenrolment->courseid));

    // Récupérer tous les étudiants inscrits aux cours

    $liststudentsassignments = $DB->get_records('role_assignments',
            array('roleid' => $rolestudentid, 'contextid' => $context->id));

    foreach ($liststudentsassignments as $studentassignment) {

        $sql = "SELECT * FROM {role_assignments} WHERE"
                . " roleid != $rolestudentid AND contextid = $context->id AND userid = $liststudentsassignments->userid";

        // Si il n'est pas qu'étudiant, ne lui retirer que le rôle étudiant, sinon le désinscrire.

        if ($DB->record_exists_sql($sql)) {

            $DB->delete_records('role_assignments',
                    array('roleid' => $rolestudentid, 'contextid' => $context->id,
                        'userid' => $liststudentsassignments->userid));
        } else {

            $manualplugin->unenrol_user($manualenrolment, $liststudentsassignments->userid);
        }
    }
}