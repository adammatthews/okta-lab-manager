<?php
$title = "Add Tenant";
$session = $auth0->getCredentials();
include 'includes/head.php';
  
if ($session === null) {
  // The user isn't logged in.
  header("Location: ".ROUTE_URL_INDEX);
  die();
}

// The user is logged in.

if($_SERVER['REQUEST_METHOD'] == "POST")
{
    func();
}

//Function to peform the actions when buttons are clicked in the Group management form. This checks which action we want to perform from the post header, runs the function to the API and then refreshes the page. 
function func()
{
  global $management;
  global $session;
  global $mp;

  $resp1 = $management->users()->getAll(['q' => $session->user["sub"]]);
            
    // Does the status code of the response indicate failure?
    if ($resp1->getStatusCode() !== 200) {
        die("API request failed.");
    }

    // Decode the JSON response into a PHP array:
    $resp1 = json_decode($resp1->getBody()->__toString(), true, 512, JSON_THROW_ON_ERROR);
    

  if($_REQUEST['action_type'] == "add"){

    $last_token = end($resp1[0]['user_metadata']["tokens"]);
    $new_id = $last_token["id"]+1; //build an ID based on the ID of the last token +1

    $array = [
      "id" => $new_id,
      "URL" => $_REQUEST['URL'],
      "token" => $_REQUEST['token'],
      "email_domain" => $_REQUEST['email_domain'],
      "user_domain" => $_REQUEST['user_domain'],
      "selected" => 0
  ];  
    array_push($resp1[0]['user_metadata']["tokens"],$array);

$strUpdate = '{
  "user_metadata": {
      "tokens": '.json_encode($resp1[0]['user_metadata']["tokens"]).'
  }
}';

$json_meta = json_decode($strUpdate, true);
$update_resp = $management->users()->update($session->user["sub"], $json_meta);

print('<div class="alert alert-success" role="alert">');
print('New tenant token for "'. $_REQUEST['URL'].'" has been successfully added. <a href="'.ROUTE_URL_INDEX.'/manageTenants">Go back.</a>');
$mp->track("Tenant Added", array("label" => "tenant-added"));
print('</div>');
  }
}
?>

<?php 
  // Code to retrieve the data from the user_metadata for the logged in user. 
        $resp1 = $management->users()->getAll(['q' => $session->user["sub"]]);
            
        // Does the status code of the response indicate failure?
        if ($resp1->getStatusCode() !== 200) {
            die("API request failed.");
        }

        // Decode the JSON response into a PHP array:
        $resp1 = json_decode($resp1->getBody()->__toString(), true, 512, JSON_THROW_ON_ERROR);

        $_SESSION["tokens"] = $resp[0]['user_metadata']["tokens"];

      ?>

<div class="row">
    <div class="col">

  <form action="" method="POST" name="tenants">
  <div class="form-group">
    <div class="form-group">     
      <label for="URL">Tenant URL</label>
      <div class="input-group mb-3">
	    <input type="text" id="URL" class="form-control"  name="URL" value="Tenant URL">
      </div>

	    <label for="token">API Token</label>
      <div class="input-group mb-3">
	    <input type="text" id="token"  class="form-control" name="token" value="Token">
      </div>

      <label for="token">Email Domain - domain where emails can be recieved</label>
      <div class="input-group mb-3">
        <span class="input-group-text">@</span>
        <input type="text" id="email_domain"  class="form-control" name="email_domain" value="domain.com">
      </div>


    <label for="token">User Domain - used for the user login</label>
    <div class="input-group mb-3">
    <span class="input-group-text">@</span>
	    <input type="text" id="user_domain"  class="form-control" name="user_domain" value="domain.com">
    </div>
    </div>
  </div>

  <button type="submit" name="action_type" value="add" class="btn btn-primary">Add Token</button>

</form>
      </div></div>
<?php
include 'includes/footer.php';