<?php

require_once ('rename_form.php');
require_once('classes/SessionManagement.php');
//require_once('classes/RemoteRecorderManagement.php');
require ('lib.php');

require_login();
global $courses;
global $PAGE,$DB;
$returnurl = optional_param('return_url', $CFG->wwwroot . '/admin/settings.php?section=localsettingbath_panopto_folder_rename', PARAM_LOCALURL);
$returnurl = '';
$context = context_system::instance();
$PAGE->set_context($context);
$urlparams['return_url'] = $returnurl;
$PAGE->set_url('/local/bath_panopto_folder_rename/rename_folder.php', $urlparams);
$PAGE->set_pagelayout('base');

$mform = new panopto_rename_form($PAGE->url);
if ($mform->is_cancelled()) {
    redirect(new moodle_url($returnurl));
} else {

    $title = get_string('rename_folders', 'local_bath_panopto_folder_rename');
    $PAGE->navbar->add($title, new moodle_url($PAGE->url));
    $data = $mform->get_data();
    if($data){
        $courses = $data->courses;
    }
    echo $OUTPUT->header();
    if($courses){
        $config =  get_config_vars();
        $AuthCode = generate_auth_code();
        $sessionManagementClient = new PanoptoSessionManagementSoapClient($config->server_name, $config->api_user, $AuthCode, $config->password);
        $sessionManagementClient ->__setLocation("https://". $config->server_name . "/Panopto/PublicAPI/4.6/SessionManagement.svc");
        foreach ($courses as $courseid) {
            if (empty($courseid)) {
                continue;
            }
            $course_shortName = $DB->get_field('course', 'shortname', array('id' => $courseid));
            $course_LongName = $DB->get_field('course', 'fullname', array('id' => $courseid));
            $newName = construct_folder_name($course_LongName,$course_shortName);
             //For that course id, get the panopto folder id
            $panoptoid = get_panopto_folder_for_course($courseid);
            if(!empty($panoptoid)){
                //Update folder

                 try{
                    echo "updating folder name for <b>$newName</b>";
                    $sessionManagementClient->update_folder_name($panoptoid,$newName);
                    echo "..done";
                }
                catch (\Exception $e){
                    echo "\n".$e->getMessage();
                }

            }

        }
    }
    else{
        $mform->display();
    }

    echo $OUTPUT->footer();
}