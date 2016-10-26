<?php
/*
*Function to create an api auth code for use when calling methods from the Panopto API.
*/
require_once('classes/SessionManagement.php');

function get_config_vars(){
    $config_vars = get_config('local_bath_panopto_folder_rename');
    return $config_vars;
}
function generate_auth_code() {
    $config = get_config_vars();
    $payload = $config->api_user . "@" . $config->server_name;
    $signedpayload = $payload . "|" . $config->application_key;
    $authcode = strtoupper(sha1($signedpayload));
    return $authcode;
}
function get_panopto_folder_for_course($courseid){
    global $DB;
    $panopto_id = null;
    if(!empty($courseid)){
        $panopto_id = $DB->get_field('block_panopto_foldermap','panopto_id',['moodleid'=>$courseid]);
    }
    return $panopto_id;
}
function local_bath_panopto_folder_rename_scheduled_task() {
    global $DB, $CFG;
    $panoptocourses = $DB->get_records('block_panopto_foldermap');
    $config = get_config_vars();
    $AuthCode = generate_auth_code();
    $sessionManagementClient = new PanoptoSessionManagementSoapClient($config->server_name, $config->api_user, $AuthCode, $config->password);
    $sessionManagementClient ->__setLocation("https://". $config->server_name . "/Panopto/PublicAPI/4.6/SessionManagement.svc");
    foreach ($panoptocourses as $course) {
        $moodlecourse = $DB->get_record('course', array('id' => $course->moodleid));
        if (!$moodlecourse) {
            continue;
        }
        $folder_name = construct_folder_name($moodlecourse->fullname, $moodlecourse->shortname);
        if (!empty($folder_name)) {
            $panoptoid = get_panopto_folder_for_course($moodlecourse->id);
            if (!empty($panoptoid)) {
                try{
                    echo "updating folder name for $folder_name ($panoptoid)";
                    $sessionManagementClient->update_folder_name($panoptoid,$folder_name);
                    echo "..done";
                }
                catch (\Exception $e){
                    echo "\n".$e->getMessage();
                }
            }
        }

    }
}
function construct_folder_name($coursefullname,$courseshortname){
    return $courseshortname.":".$coursefullname;
}

function  Create_ListSessionsRequest_Object($endDate, $folderId, $remoteRecorderId, $sortBy, $sortIncreasing, $startDate)
{

    //Create empty object to store member data
    $listSessionsRequest = new stdClass();
    $listSessionsRequest->EndDate = new SoapVar($endDate, XSD_STRING, null, null, null, PanoptoSessionManagementSoapClient::OBJECT_MEMBER_NAMESPACE);
    $listSessionsRequest->FolderId = new SoapVar($folderId, XSD_STRING, null, null, null, PanoptoSessionManagementSoapClient::OBJECT_MEMBER_NAMESPACE);
    $listSessionsRequest->RemoteRecorderId = new SoapVar($remoteRecorderId, XSD_STRING, null, null, null, PanoptoSessionManagementSoapClient::OBJECT_MEMBER_NAMESPACE);
    $listSessionsRequest->SortBy = new SoapVar($sortBy, XSD_STRING, null, null, null, PanoptoSessionManagementSoapClient::OBJECT_MEMBER_NAMESPACE);
    $listSessionsRequest->SortIncreasing = new SoapVar($sortIncreasing, XSD_BOOLEAN, null, null, null, PanoptoSessionManagementSoapClient::OBJECT_MEMBER_NAMESPACE);
    $listSessionsRequest->StartDate = new SoapVar($startDate, XSD_STRING, null, null, null, PanoptoSessionManagementSoapClient::OBJECT_MEMBER_NAMESPACE);
    return $listSessionsRequest;
}
function Create_ListFolderRequest_Object($pagination,$parentFolderId,$publicOnly,$sortBy,$sortIncreasing,$WildcardSearchNameOnly){
    $folderListRequest = new stdClass();
    $folderListRequest->Pagination = new SoapVar($pagination,SOAP_ENC_OBJECT, null, null, null, PanoptoSessionManagementSoapClient::OBJECT_MEMBER_NAMESPACE);
    $folderListRequest->ParentFolderId = new SoapVar($parentFolderId,XSD_STRING);
    $folderListRequest->PublicOnly= new SoapVar($publicOnly,XSD_STRING);
    $folderListRequest->SortBy= new SoapVar($sortBy,XSD_STRING);
    $folderListRequest->SortIncreasing= new SoapVar($sortIncreasing,XSD_STRING);
    $folderListRequest->WildcardSearchNameOnly=new SoapVar($WildcardSearchNameOnly,XSD_STRING);
    return $folderListRequest;

}
function Create_RemoteRecorder_Object($devices,$id,$machineIp,$name,$settingsUrl,$state){
    $remoteRecorder = new stdClass();
    $remoteRecorder->Devices = new SoapVar($devices,XSD_ANYTYPE);
    $remoteRecorder->Id = new SoapVar($id,XSD_STRING);
    $remoteRecorder->MachineIP = new SoapVar($machineIp,XSD_STRING);
    $remoteRecorder->Name = new SoapVar($name,XSD_STRING);
    $remoteRecorder->SettingsUrl = new SoapVar($settingsUrl,XSD_STRING);
    $remoteRecorder->State = new SoapVar($state,XSD_ANYTYPE);
    return $remoteRecorder;

}

function Create_Pagination_Object($maxNumberResults, $pageNumber)
{

    //Create empty object to store member data
    $pagination = new stdClass();
    $pagination->MaxNumberResults = $maxNumberResults;
    $pagination->PageNumber = $pageNumber;
    return $pagination;
}