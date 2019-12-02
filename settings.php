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
 * @copyright 2019 Laurent Guillet <laurent.guillet@u-cergy.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * File : settings.php
 * Settings file
 */

// Ensure the configurations for this site are set
if ($hassiteconfig){

    // Create the new settings page
    // - in a local plugin this is not defined as standard, so normal $settings->methods will throw an error as
    // $settings will be NULL
    $settings = new admin_settingpage('local_coursesshowcase', get_string('pluginname', 'local_coursesshowcase'));

    // Create
    $ADMIN->add('localplugins', $settings);

    // Add a setting field to the settings for this page
    $settings->add(new admin_setting_configtext(

        // This is the reference you will use to your configuration
        'local_coursesshowcase/currentterm',

        // This is the friendly title for the config, which will be displayed
        get_string('currentterm', 'local_coursesshowcase'),

        // This is helper text for this config field
        get_string('currenttermhelptext', 'local_coursesshowcase'),

        // This is the default value
        1,

        // This is the type of Parameter this config is
        PARAM_INT

    ));

    // Add a setting field to the settings for this page
    $settings->add(new admin_setting_configtext(

        // This is the reference you will use to your configuration
        'local_coursesshowcase/currenttermregistrationstart',

        // This is the friendly title for the config, which will be displayed
        get_string('currenttermregistrationstart', 'local_coursesshowcase'),

        // This is helper text for this config field
        get_string('currenttermregistrationstarthelptext', 'local_coursesshowcase'),

        // This is the default value
        0,

        // This is the type of Parameter this config is
        PARAM_INT

    ));

    // Add a setting field to the settings for this page
    $settings->add(new admin_setting_configtext(

        // This is the reference you will use to your configuration
        'local_coursesshowcase/currenttermregistrationend',

        // This is the friendly title for the config, which will be displayed
        get_string('currenttermregistrationend', 'local_coursesshowcase'),

        // This is helper text for this config field
        get_string('currenttermregistrationendhelptext', 'local_coursesshowcase'),

        // This is the default value
        0,

        // This is the type of Parameter this config is
        PARAM_INT

    ));
}