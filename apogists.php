<?php
define('CLI_SCRIPT', true);
require_once('../../config.php');

$handle = fopen("/home/berrandonea/Apogistes.csv", "r");

if ($handle) {

    $role = $DB->get_record('role', array('shortname' => 'apogist'));
    $now = time();

    while (($buffer = fgets($handle, 4096)) !== false) {

        echo $buffer;
        $user = $DB->get_record('user', array('email' => trim($buffer)));
        if ($user) {

            $roleassignment = $DB->get_record('role_assignments', array('roleid' => $role->id, 'contextid' => 1,
                'userid' => $user->id));

            if (!$roleassignment) {

                $roleassignment = new stdClass();
                $roleassignment->roleid = $role->id;
                $roleassignment->contextid = 1;
                $roleassignment->userid = $user->id;
                $roleassignment->timemodified = $now;
                $roleassignment->modifierid = 3;
                $roleassignment->component = 'local_coursesshowcase';
                $roleassignment->id = $DB->insert_record('role_assignments', $roleassignment);
                echo "$user->firstname $user->lastname\n";
            }
        }
    }
    
    if (!feof($handle)) {

        echo "Erreur: fgets() a échoué\n";
    }
    fclose($handle);
}
