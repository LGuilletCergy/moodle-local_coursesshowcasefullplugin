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
 * File : coursedetails_form.php
 * Form class definition.
 */

if (!defined('MOODLE_INTERNAL')) {

    die('Direct access to this script is forbidden.');
}
require_once($CFG->libdir.'/formslib.php');

class coursedetails_form extends moodleform {

    public function definition() {

        global $DB;
        $mform =& $this->_form;

        $mform->addElement('header', 'generalhdr', get_string('general'));
        // Nombre maximum d'étudiants
        $mform->addElement('text', 'capacity', get_string('capacity', 'local_coursesshowcase'));
        $mform->setType('capacity', PARAM_INT);
        // Organisé par
        $holderoptions = array();
        $holders = $DB->get_records('local_coursesshowcase_holder', array(), 'name');

        foreach ($holders as $holder) {

            $holderoptions[$holder->id] = $holder->name;
        }

        $mform->addElement('select', 'holderid', get_string('organizer', 'local_coursesshowcase'), $holderoptions);

        // Niveau
        $mform->addElement('text', 'level', get_string('level', 'local_coursesshowcase'));
        $mform->setType('level', PARAM_TEXT);

        // Filière recommandée
        $mform->addElement('text', 'training', get_string('besttrainings', 'local_coursesshowcase')." *");
        $mform->setType('training', PARAM_TEXT);
        $mform->addElement('static', 'avoid', '* : '.get_string('avoid', 'local_coursesshowcase').'.',
                get_string('ueforall', 'local_coursesshowcase'));

        // Nombre d'ECTS
        $mform->addElement('text', 'ects', get_string('ects', 'local_coursesshowcase'),
                get_string('zeroifdepends', 'local_coursesshowcase'));
        $mform->setType('ects', PARAM_INT);
        $mform->addElement('advcheckbox', 'leisure', get_string('leisure', 'local_coursesshowcase'));
        $mform->addElement('advcheckbox', 'competition', get_string('competition', 'local_coursesshowcase'));

        $mform->addElement('header', 'wherewhen', get_string('wherewhen', 'local_coursesshowcase'));

        // Semestre 1
        $mform->addElement('advcheckbox', 'oddterm', get_string('oddterm', 'local_coursesshowcase'));

        // Semestre 2
        $mform->addElement('advcheckbox', 'eventerm', get_string('eventerm', 'local_coursesshowcase'));

        // Nombre d'heures
        $mform->addElement('text', 'nbhours', get_string('nbhours', 'local_coursesshowcase'));
        $mform->setType('nbhours', PARAM_INT);

        // Nombre d'heures par semaine
        $mform->addElement('text', 'hoursperweek', get_string('nbhoursperweek', 'local_coursesshowcase'));
        $mform->setType('hoursperweek', PARAM_INT);

        // Cours en présentiel
        $mform->addElement('advcheckbox', 'presence', get_string('presence', 'local_coursesshowcase'));

        // Cours à distance
        $mform->addElement('advcheckbox', 'distance', get_string('distance', 'local_coursesshowcase'));

        // Lieu
        $mform->addElement('textarea', 'place', get_string('place', 'local_coursesshowcase'),
                'wrap="virtual" rows="5" cols="30"');
        $mform->setType('place', PARAM_TEXT);

        $mform->addElement('header', 'evaluation', get_string('evaluation', 'local_coursesshowcase'));

        // Contrôle continu
        $mform->addElement('text', 'evalcc', get_string('evalcc', 'local_coursesshowcase'));
        $mform->setType('evalcc', PARAM_TEXT);

        // Contrôle terminal
        $mform->addElement('text', 'evalct', get_string('evalct', 'local_coursesshowcase'));
        $mform->setType('evalct', PARAM_TEXT);

        // Autre
        $mform->addElement('text', 'evalother', get_string('evalother', 'local_coursesshowcase'));
        $mform->setType('evalother', PARAM_TEXT);

        $mform->addElement('hidden', 'courseid', $this->_customdata['courseid']);
        $mform->setType('courseid', PARAM_INT);

        foreach ($this->_customdata as $key => $value) {
            $mform->setDefault($key, $value);
        }

        $this->add_action_buttons();
    }
}
