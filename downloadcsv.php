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
 * File : downloadcsv.php
 * CSV list of enrolments for each members of a given cohort.
 */

require_once('../../config.php');
require_once("$CFG->libdir/csvlib.class.php");
//~ require_once('lib.php');

$csv = optional_param('csv', 0, PARAM_TEXT);

if (confirm_sesskey()) {

    $csvarray = explode('£µ£', $csv);
    //~ print_object($csvarray);
    $csvexporter = new csv_export_writer('semicolon');
    $title = 'cohortenrolments';
    $csvexporter->set_filename($title);
    $csvexporter->add_data($title);
    //~ $decodedfiltersline = report_exportlist_utf8($filtersline);
    //~ $csvexporter->add_data($decodedfiltersline);
    //~ $decodedcolumntitles = report_exportlist_utf8($columntitles);
    //~ $csvexporter->add_data($decodedcolumntitles);

    foreach ($csvarray as $userline) {

        //~ $userline = utf8_decode($userline);
        //~ echo "$userline<br>";
        $csvexporter->add_data($userline);
    }

    //~ print_object($csvexporter);
    $csvexporter->download_file();
    exit;
}

/**
 * Apply utf8_decode to all the cells of an array.
 * @param array of strings $array
 * @return array of strings
 */
function local_coursesshowcase_utf8($userline) {

    $decodedarray = array();
    foreach ($array as $cell) {
        
        $decodedarray[] = utf8_decode($cell);
    }
    return $decodedarray;
}


