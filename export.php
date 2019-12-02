
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
require_once("$CFG->dirroot/blocks/mytermcourses/lib.php");

// Style for category titles.
$bgcolor = '#b731472';

// A modifier pour rendre le plugin 100% indépendant de fordson.

if ($CFG->theme == 'fordson') {

    $fordsonconfig = get_config('theme_fordson');

    if ($fordsonconfig->brandprimary) {

        $bgcolor = $fordsonconfig->brandprimary;
    }
}

$style = "font-weight:bold;padding:5px;padding-left:10px;color:white;background-color:$bgcolor;width:100%";

$courses = enrol_get_my_courses('summary, summaryformat', 'idnumber ASC');

if ($courses) {

    // User's course categories.
    $categoriesid = array();
    foreach ($courses as $course) {

        if (!in_array($course->category, $categoriesid)) {
            array_push($categoriesid, $course->category);
        }
    }

    sort($categoriesid);

    // Display categories and courses.
    echo "<div width='200px'>";
    foreach ($categoriesid as $categoryid) {

        $category = $DB->get_record('course_categories', array('id' => $categoryid));
        echo "<p style='$style'>$category->name</p>";
        echo '<div style="overflow:auto">';
        foreach ($courses as $course) {

            if ($course->category == $category->id) {

                //~ echo block_mytermcourses_displaycourse($course);
                echo "<a href='$CFG->wwwroot/course/view.php?id=$course->id'>$course->fullname</a>";
            }
        }

        echo '</div>';
        echo '<br><br>';
    }

    echo "</div>";
}

