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

require_once($CFG->dirroot . '/enrol/manual/externallib.php');

global $DB;

$rolestudentid = $DB->get_record('role', array('shortname' => 'student'))->id;

$listassignmentsstudents = $DB->get_records('role_assignments', array('roleid' => $rolestudentid));

foreach ($listassignmentsstudents as $assignmentstudent) {

    $courseid = $DB->get_record('context', array('id' => $assignmentstudent->contextid))->instanceid;
    $studentid = $assignmentstudent->userid;

    enrol_manual_external::unenrol_users(array(
        array('userid' => $studentid, 'courseid' => $courseid, 'roleid' => $rolestudentid),));
}

