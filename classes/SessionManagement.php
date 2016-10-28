<?php
class PanoptoSessionManagementSoapClient extends SoapClient{

    //Namespace used for XML nodes for any root level variables or objects
    const ROOT_LEVEL_NAMESPACE = "http://tempuri.org/";

    //Namespace used for XML nodes for object members
    const OBJECT_MEMBER_NAMESPACE = "http://schemas.datacontract.org/2004/07/Panopto.Server.Services.PublicAPI.V40";
    const ARRAY_PAP = 'http://schemas.microsoft.com/2003/10/Serialization/Arrays';
    //Username of calling user.
    public $ApiUserKey;
    //Auth code generated for calling user.
    public $ApiUserAuthCode;
    //Name of Panopto server being called.
    public $Servername;
    //Password needed if provider does not have a bounce page.
    public $Password;

    // Older PHP SOAP clients fail to pass the SOAPAction header properly.
    // Store the current action so we can insert it in __doRequest.
    public $currentURI = 'http://tempuri.org/ISessionManagement/';
    public $currentaction;
    public $uri;
    private static $curloptions;
    public $wsLocation = '/Panopto/PublicAPI/4.6/SessionManagement.svc?wsdl' ;
    public function __construct($servername,$apiuseruserkey, $apiuserauthcode, $password) {
        global $CFG;

        $this->ApiUserKey = $apiuseruserkey;

        $this->ApiUserAuthCode = $apiuserauthcode;

        $this->Servername = $servername;

        $this->Password = $password;
        $locationuri = "https://". $this->Servername . $this->wsLocation;

        // Instantiate SoapClient in WSDL mode.
        //Set call timeout to 5 minutes.
        $soap_options = array();
        $soap_options['trace'] = true;
        $soap_options['cache_wsdl'] = WSDL_CACHE_NONE;
        if(!empty($CFG->proxyhost) && !empty($CFG->proxyport)){
            $soap_options['proxy_host']  = $CFG->proxyhost;
            $soap_options['proxy_port'] = $CFG->proxyport;
        }
        parent::__construct
        (
            $locationuri,
            $soap_options
        );
        if (empty(self::$curloptions)) {
            self::$curloptions = array(
                CURLOPT_PROXY => $CFG->proxyhost,
                CURLOPT_PROXYPORT => $CFG->proxyport,
                CURLOPT_PROXYTYPE => (($CFG->proxytype === 'HTTP') ? CURLPROXY_HTTP : CURLPROXY_SOCKS5),
                CURLOPT_PROXYUSERPWD => ((empty($CFG->proxypassword)) ? $CFG->proxyuser : "{$CFG->proxyuser}:{$CFG->proxypassword}"),
            );
        }

    }


    /**
     *  Helper method for making a call to the Panopto API.
     *  $methodname is the case sensitive name of the API method to be called
     *  $namedparams is an associative array of the member parameters (other than authenticationinfo )
     *   required by the API method being called. Keys should be the case sensitive names of the method's
     *   parameters as specified in the API documentation.
     *  $auth should only be set to false if the method does not require authentication info.
     */
    public function call_web_method($methodname, $namedparams = array(), $auth = true) {
        $params = array();

        // Include API user and auth code params unless $auth is set to false.
        if ($auth)
        {
            //Create SoapVars for AuthenticationInfo object members
            $authinfo = new stdClass();


            $authinfo->AuthCode = new SoapVar(
                $this->ApiUserAuthCode, //Data
                XSD_STRING, //Encoding
                null, //type_name should be left null
                null, //type_namespace should be left null
                null, //node_name should be left null
                self::OBJECT_MEMBER_NAMESPACE); //Node namespace should be set to proper namespace.

            //Add the password parameter if a password is provided
            if(!empty($this->Password))
            {
                $authinfo->Password = new SoapVar($this->Password, XSD_STRING, null, null, null, self::OBJECT_MEMBER_NAMESPACE);
            }

            $authinfo->AuthCode = new SoapVar($this->ApiUserAuthCode, XSD_STRING, null, null, null, self::OBJECT_MEMBER_NAMESPACE);


            $authinfo->UserKey = new SoapVar($this->ApiUserKey, XSD_STRING, null, null, null,self::OBJECT_MEMBER_NAMESPACE);

            //Create a container for storing all of the soap vars required for the request.
            $obj = array();

            //Add auth info to $obj container
            $obj['auth'] = new SoapVar($authinfo, SOAP_ENC_OBJECT, null, null, null, self::ROOT_LEVEL_NAMESPACE);


            //Add the soapvars from namedparams to the container using their key as their member name.
            foreach($namedparams as $key => $value)
            {
                $obj[$key] = $value;
            }

            //Create a soap param using the obj container
            $param = new SoapParam(new SoapVar($obj, SOAP_ENC_OBJECT), 'data');

            //Add the created soap param to an array to be passed to __soapCall
            $params = array($param);
        }

        //Update current action with the method being called.
        $this->currentaction = $this->currentURI.$methodname;
        // Make the SOAP call via SoapClient::__soapCall.
        return parent::__soapCall($methodname, $params);
    }

    /**
     * Sample function for calling an API method. This method will call the sessionmanagement method GetSessionsList.
     * Because this method calls a method from the SessionManagement API, it should only be called by a soap client
     * that has been initialized to SessionManagement.
     * Auth parameter will be created within the soap clients calling logic.
     * $request is a soap encoded ListSessionsRequest object
     * $searchQuery is an optional string containing an custom sql query
     */
    public function get_session_list($request, $searchQuery)
    {
        $requestvar = new SoapVar($request, SOAP_ENC_OBJECT, null, null, null, self::ROOT_LEVEL_NAMESPACE);
        $searchQueryVar = new SoapVar($searchQuery, XSD_STRING, null, null, null, self::ROOT_LEVEL_NAMESPACE);

            return self::call_web_method("GetSessionsList", array("request" => $requestvar, "searchQuery" => $searchQueryVar));
    }
    public function get_folder_by_id($folderIds){
        $requestvar = new SoapVar(array($folderIds), SOAP_ENC_OBJECT,null,null,null,self::ROOT_LEVEL_NAMESPACE);
        return self::call_web_method("GetFoldersById", array("folderIds" => $requestvar));
    }
    public function get_folder_list($request,$searchQuery){
        $requestvar = new SoapVar($request, XSD_ANYTYPE, null, null, null, self::ROOT_LEVEL_NAMESPACE);
        $searchQueryVar = new SoapVar($searchQuery, XSD_STRING, null, null, null, self::ROOT_LEVEL_NAMESPACE);
        return self::call_web_method("GetFoldersList", array("request" => $requestvar, "searchQuery" => $searchQueryVar));
    }
    public function update_folder_name($folderId,$newName){
        $folderIdVar = new SoapVar($folderId, XSD_STRING, null, null, null, self::ROOT_LEVEL_NAMESPACE);
        $newNameVar = new SoapVar($newName, XSD_STRING, null, null, null, self::ROOT_LEVEL_NAMESPACE);
        return self::call_web_method('UpdateFolderName',array('folderId'=> $folderIdVar,'name'=> $newNameVar));
    }

    /**
     * Override SOAP action to work around bug in older PHP SOAP versions.
     */
    public function __doRequest($request, $location, $action, $version, $oneway = null) {
        error_log(var_export($request,1));


        global $CFG;
        //Attempt to intitialize cURL session to make SOAP calls.
        $curl = curl_init($location);

        //Check cURL was initialized
        if ($curl !== false)
        {
            //Set standard cURL options
            $options = array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $request,
                CURLOPT_NOSIGNAL => true,
                CURLOPT_HTTPHEADER => array(sprintf('Content-Type: %s', $version == 2 ? 'application/soap+xml' : 'text/xml'), sprintf('SOAPAction: %s', $action)),
                CURLOPT_SSL_VERIFYPEER => false, //All of our SOAP calls must be made via ssl
                CURLOPT_TIMEOUT => 300 //Set call timeout in seconds
            );
            //Add curl options
            $options[CURLOPT_PROXY] = $CFG->proxyhost;
            $options[CURLOPT_PROXYPORT] = $CFG->proxyport;
            $options[CURLOPT_PROXYTYPE] = (($CFG->proxytype === 'HTTP') ? CURLPROXY_HTTP : CURLPROXY_SOCKS5);
            $option[CURLOPT_PROXYUSERPWD] = ((empty($CFG->proxypassword)) ? $CFG->proxyuser : "{$CFG->proxyuser}:{$CFG->proxypassword}");

            //$options = array_merge(self::$curloptions, $options); // Add proxy options to curl request.
            //Attempt to set the options for the cURL call
            if (curl_setopt_array($curl, $options) !== false)
            {
                //Make call using cURL (including timeout settings)
                $response = curl_exec($curl);
                //If cURL throws an error, log it
                if (curl_errno($curl) !== 0)
                {
                    error_log(curl_error($curl));
                }
            }
            else
            {
                //A cURL option could not be set.
                throw new Exception('Failed setting cURL options.');
            }
        }
        else
        {
            //cURL was not initialized properly.
            throw new Exception("Couldn't initialize cURL to make SOAP calls");
        }

        //Close cURL session.
        curl_close($curl);

        //Return the SOAP response
        return $response;
        //return parent::__doRequest($request, $location, $this->currentaction, $version);
    }

}