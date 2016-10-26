<?php
namespace local_bath_panopto_folder_rename\task;
defined('MOODLE_INTERNAL') || die();

class rename_folder extends \core\task\scheduled_task{
    public function get_name(){
        return get_string('pluginname','local_bath_panopto_folder_rename');
    }
    public function execute() {
        global $CFG,$DB;
        require_once($CFG->dirroot . '/local/bath_panopto_folder_rename/lib.php');
        $task_lastruntime = parent::get_last_run_time();
        local_bath_panopto_folder_rename_scheduled_task();
    }
}