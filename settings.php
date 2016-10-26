<?php
defined('MOODLE_INTERNAL') || die;
if ($hassiteconfig) {
   // $settings = new admin_settingpage('local_bath_panopto_folder_rename', get_string('pluginname', 'local_bath_panopto_folder_rename'));
    //$ADMIN->add('localplugins', $settings);
    $ADMIN->add('root', new admin_category('bath_panopto_folder_rename',
        get_string('pluginname', 'local_bath_panopto_folder_rename')
    ));
    $settings = new admin_settingpage('local_bath_panopto_folder_rename', get_string('pluginname', 'local_bath_panopto_folder_rename'));
    $ADMIN->add('localplugins', $settings);
    $link = '<a href="' . $CFG->wwwroot . '/local/bath_panopto_folder_rename/rename_folder.php">' . get_string('rename_folders_link', 'local_bath_panopto_folder_rename') . '</a>';
    $settings->add(new admin_setting_heading('block_panopto_add_courses', '', $link));
    $settings->add(new admin_setting_configtext('local_bath_panopto_folder_rename/api_user', get_string('api_user', 'local_bath_panopto_folder_rename'), get_string('api_user_desc', 'local_bath_panopto_folder_rename'), ''));
    $settings->add(new admin_setting_configtext('local_bath_panopto_folder_rename/server_name', get_string('server_name', 'local_bath_panopto_folder_rename'), get_string('server_name_desc', 'local_bath_panopto_folder_rename'), ''));
    $settings->add(new admin_setting_configtext('local_bath_panopto_folder_rename/application_key', get_string('application_key', 'local_bath_panopto_folder_rename'), get_string('application_key_desc', 'local_bath_panopto_folder_rename'), ''));
    $settings->add(new admin_setting_configpasswordunmask('local_bath_panopto_folder_rename/password', get_string('password', 'local_bath_panopto_folder_rename'), '', ''));
}

