<?php
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');
class panopto_rename_form extends moodleform {

    protected $title = '';
    protected $description = '';

    /**
     * Defines a panopto provision form
     */
    public function definition() {

        global $DB;
        global $aserverarray;

        $mform = & $this->_form;
        $select_query = "id <> 1";
        $coursesraw = $DB->get_records_select('course', $select_query, null, 'id, shortname, fullname');
        $courses = array();
        if ($coursesraw) {
            foreach ($coursesraw as $course) {
                $courses[$course->id] = $course->shortname . ': ' . $course->fullname;
            }
        }
        asort($courses);

        $select = $mform->addElement('select', 'courses', get_string('renamefoldersselect', 'local_bath_panopto_folder_rename'), $courses);
        $select->setMultiple(true);
        $select->setSize(32);
        $mform->addHelpButton('courses', 'renamefoldersselect', 'local_bath_panopto_folder_rename');

        $this->add_action_buttons(true, get_string('rename_folders', 'local_bath_panopto_folder_rename'));
    }

}