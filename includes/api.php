<?php 
/**********************************
     Functions for OKTA REST API.
     Author: Adam Matthews adam@adammatthews.co.uk
     Date: December 2020
     Updated: March 2022
***********************************/

$lab_name = $_ENV['LAB_NAME'];

// First Login Metadata for Auth0 account
$meta = '{
    "user_metadata": {
        "tokens": [{
                "URL": "https://demo.okta.com",
                "token": "00ka8487T5JRqOxzeP8JtdF9ywL6F-XxvWhOI2MbaC", 
                "selected" : 1, 
                "email_domain": "atko.email", 
                "user_domain": "atko.domain"
            }
          ], 
          "settings": {
          }
    }
  }';

  
if(isset($session)){ //i.e. if we're logged in - use the mgmt API to grab our user_metadata
    $resp = $management->users()->getAll(['q' => $session->user["sub"]]);
    
    // Does the status code of the response indicate failure?
    if ($resp->getStatusCode() !== 200) {
        die("API request failed.");
    }

    // Decode the JSON response into a PHP array:
    $resp = json_decode($resp->getBody()->__toString(), true, 512, JSON_THROW_ON_ERROR);

    if (!empty($resp)) {
        if(isset($resp[0]['user_metadata']["tokens"])){ // if we have tokens set
            foreach ($resp[0]['user_metadata']["tokens"] as $token){
                if(isset($token["selected"]) && $token["selected"]){ // set the selected token
                    $apiKey = $token["token"];
                    $baseUrl = $token["URL"];
                    $_SESSION["selToken"] = $token;
                }
                if(!isset($token["selected"])){
                    $_SESSION["selToken"] = $resp[0]['user_metadata']["tokens"][0];
                    $apiKey = $resp[0]['user_metadata']["tokens"][0]["token"];
                    $baseUrl = $resp[0]['user_metadata']["tokens"][0]["URL"];
                }
            }
        }
        else{
            echo "NO METADATA";
            $baseUrl = "NOT SET";
            // SET A BASELINE TOKEN
            $json_meta = json_decode($meta, true);
            $update_resp = $management->users()->update($session->user["sub"], $json_meta);

            header("Location: ".ROUTE_URL_INDEX."/manageTenants"); // Send you to manage your tenant on first login.
            die();
        }

    }

}

// Generic function for the Okta REST API Call
    function Okta ($url, $method = "GET", $data = "", $key = "", $base = "") {
        global $baseUrl;
        global $apiKey;

        // if a key and base url have not been passed into the Call, set it with the defaults from the selected user_metadata token. 
        if(empty($key)){
            $key = $apiKey;
        }
        if(empty($base)){
            $base = $baseUrl;
        }

        $headers = array(
            'Authorization: SSWS ' . $key,
            'Accept: application/json',
            'Content-Type: application/json'      
        );

        $curl = curl_init();

        $curl_url = $base.$url;

        curl_setopt($curl, CURLOPT_URL, $curl_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
       
        if ($method == "POST") {      
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        }      
        if ($method == "GET") {      
            curl_setopt($curl, CURLOPT_HTTPGET, 1);
        }
        //written to support the user group updating (Put and Delete)
        if ($method == "PUT") {      
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        }      
        if ($method == "DELETE") {      
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
        }     

        // Run the call when we have some data. 
        if (!empty($data)) {                            
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        if (($output = curl_exec($curl)) === FALSE) {
            die("Curl Failed: " . curl_error($curl));
        }
        
        curl_close($curl);

        return json_decode($output);
    }

// Obtains the users user groups so we can list them. Returns an array.
    function Usergroups ($sub) {
        $groups = Okta ("/api/v1/users/".$sub."/groups", "GET"); // get the user groups for the user 'sub'
        $stack = array();

        foreach($groups as $group) { // iterate the groups
            foreach ($group as $item){
              if(!is_object($item)) { continue; } // if we dont have a proper object, forget it
                if (isset($item->name)) {
                    array_push($stack, $item->name); // add the user group to the array
                }
            }
        }
        return $stack;
    }

//Function to flatten an array. Used in conjunction with 'Usergroups' to provide data to output into a table. 
    function flatten(array $array) {
      $str = "";
      foreach ($array as $key => $value) {
            $str = $str.$value.",";
      }
      return $str;
    }

// Create new User with Password
    function newUser($data, $active){
        if($active){
            // echo "this is: TRUE";
            return Okta ("/api/v1/users?activate=true", "POST",$data); //Returns full array  back of user data
        }else{
            // echo "this is: FALSE";
            return Okta ("/api/v1/users?activate=false", "POST",$data); //Returns full array  back of user data
        }
    }

// Create new Activated User with Password -- $sendEmail - Bool - Sends a deactivation email to the administrator if true. Default value is false.
    function deleteUser($uid){
        return Okta ("/api/v1/users/".$uid."?sendEmail=false", "DELETE"); //Returns full array  back of user data        
    }

// Function to return all users (active and deprovisioned)
    function getUsers(){
        return Okta ('/api/v1/users?filter=status+eq+%22ACTIVE%22+or+status+eq+%22DEPROVISIONED%22+or+status+eq+%22STAGED%22', "GET");
    }

// Returns a user object from the Okta API with a given ID
    function getUser($uid){
        return Okta ("/api/v1/users/".$uid, "GET");
    }

//// BRANDS

// Returns the Brands
function getBrands(){
    return Okta ("/api/v1/brands", "GET");
}

// Returns the Brands
function getThemes($brandid){
    return Okta ("/api/v1/brands/".$brandid."/themes", "GET");
}

// Returns the Brands
function updateTheme($brandid, $themeid, $data){
    // echo $data;
    return Okta ("/api/v1/brands/".$brandid."/themes/".$themeid, "PUT", $data);
}

//Delete theme logo
function deleteLogo($brandid, $themeid){
    return Okta ("/api/v1/brands/".$brandid."/themes/".$themeid."/logo", "DELETE");
}
//Delete theme background
function deleteBackground($brandid, $themeid){
    return Okta ("/api/v1/brands/".$brandid."/themes/".$themeid."/background-image", "DELETE");
}
//Delete theme logo
function deleteFavicon($brandid, $themeid){
    return Okta ("/api/v1/brands/".$brandid."/themes/".$themeid."/favicon", "DELETE");
}

//Manually done here to get around the CURLFILE crap
function uploadLogo($brandid,$themeid,$file) {
    global $baseUrl;
    global $apiKey;

    if(!empty($file)){ // Allow for no logo file
        $curl = curl_init();

        $url = $baseUrl."/api/v1/brands/".$brandid."/themes/".$themeid."/logo";
        
        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('file'=> new CURLFILE($file)),
        CURLOPT_HTTPHEADER => array(
            'Authorization: SSWS '.$apiKey
            //'Cookie: JSESSIONID=2733FB55F60136813A9B47711E6699F5'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
}

//Manually done here to get around the CURLFILE crap
function uploadBackground($brandid,$themeid,$file) {
    global $baseUrl;
    global $apiKey;

    if(!empty($file)){ // Allow for no background
        $curl = curl_init();

        $url = $baseUrl."/api/v1/brands/".$brandid."/themes/".$themeid."/background-image";
        
        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('file'=> new CURLFILE($file)),
        CURLOPT_HTTPHEADER => array(
            'Authorization: SSWS '.$apiKey
            //'Cookie: JSESSIONID=2733FB55F60136813A9B47711E6699F5'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
}

function uploadFavicon($brandid,$themeid,$file) {
    global $baseUrl;
    global $apiKey;

    if(!empty($file)){ // Allow for no favicon
        $curl = curl_init();

        $url = $baseUrl."/api/v1/brands/".$brandid."/themes/".$themeid."/favicon";
        
        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('file'=> new CURLFILE($file)),
        CURLOPT_HTTPHEADER => array(
            'Authorization: SSWS '.$apiKey
            //'Cookie: JSESSIONID=2733FB55F60136813A9B47711E6699F5'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }else{
        // we have no favicon, so we should delete it. 
        $curl = curl_init();

        $url = $baseUrl."/api/v1/brands/".$brandid."/themes/".$themeid."/favicon";
        
        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'DELETE',
        CURLOPT_HTTPHEADER => array(
            'Authorization: SSWS '.$apiKey
            //'Cookie: JSESSIONID=2733FB55F60136813A9B47711E6699F5'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
}