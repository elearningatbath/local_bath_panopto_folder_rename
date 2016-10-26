<?php
require_once 'SessionManagement.php';
class PanoptoRemoteRecorderManagementClient extends PanoptoSessionManagementSoapClient  {
    public $currentURI = 'http://tempuri.org/IRemoteRecorderManagement/';
    public function __construct($servername,$apiuseruserkey, $apiuserauthcode, $password) {

        $this->ApiUserKey = $apiuseruserkey;

        $this->ApiUserAuthCode = $apiuserauthcode;

        $this->Servername = $servername;

        $this->Password = $password;
        $this->wsLocation = "/Panopto/PublicAPI/4.6/RemoteRecorderManagement.svc?wsdl";
        // Instantiate SoapClient in WSDL mode.
        //Set call timeout to 5 minutes.
        parent::__construct
        (
            $servername,$apiuseruserkey,$apiuserauthcode,$password
        );

    }
    public function get_remote_recorders($pagination,$searchQuery){
        $paginationvar = new SoapVar($pagination, XSD_ANYTYPE, null, null, null, self::ROOT_LEVEL_NAMESPACE);
        $searchQueryVar = new SoapVar($searchQuery, XSD_STRING, null, null, null, self::ROOT_LEVEL_NAMESPACE);

        return $this->call_web_method("ListRecorders",array('pagination'=> $paginationvar));
    }
}