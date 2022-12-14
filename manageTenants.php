<?php
$title = "Tenant Management";
$session = $auth0->getCredentials();
include 'includes/head.php';
  
if ($session === null) {
  // The user isn't logged in.
  header("Location: ".ROUTE_URL_INDEX);
  die();
}

// The user is logged in.

// VALIDATE IF THE TOKEN WORKS OR NOT
if ($session !== null && !$session->accessTokenExpired) {
  // try {
  //   $check = getUsers(); 
  // } catch (Exception $e) {
  //   echo "Oh Dear";
  // }
 
  //  if(isset($check->errorSummary)){
	//  echo '<div class="alert alert-danger" role="alert">';
	//  echo $check->errorSummary;
	//  echo '. Your URL or Tenant Code is incorrect.';
	//  echo '</div>';
  //  }
 }

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

  if($_REQUEST['action_type'] == "set"){
  
  for($i = 0; $i < count($resp1[0]['user_metadata']["tokens"]); ++$i) {
    if($resp1[0]['user_metadata']["tokens"][$i]["id"] == $_REQUEST['cardID'] ){
      $resp1[0]['user_metadata']["tokens"][$i]["selected"] = 1;
    }else{
      $resp1[0]['user_metadata']["tokens"][$i]["selected"] = 0;
    }
  }

updateTokenArray($resp1[0]['user_metadata']["tokens"]);
$mp->track("Tenant Set", array("label" => "tenant-set"));
?>
<script type="text/javascript" >
        window.open('<?php echo "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" ?>', '_self');
</script>
<?php
  }

  if($_REQUEST['action_type'] == "update"){
    //echo $_REQUEST['URL'];
    for($i = 0; $i < count($resp1[0]['user_metadata']["tokens"]); ++$i) {
      if($resp1[0]['user_metadata']["tokens"][$i]["id"] == $_REQUEST['id'] ){
        $resp1[0]['user_metadata']["tokens"][$i]["URL"] = $_REQUEST['URL'];
        $resp1[0]['user_metadata']["tokens"][$i]["token"] = $_REQUEST['token'];
        $resp1[0]['user_metadata']["tokens"][$i]["email_domain"] = $_REQUEST['email_domain'];
        $resp1[0]['user_metadata']["tokens"][$i]["user_domain"] = $_REQUEST['user_domain'];
      }else{
        // $resp1[0]['user_metadata']["tokens"][$i]["selected"] = 0;
      }
    }

    updateTokenArray($resp1[0]['user_metadata']["tokens"]);
    $mp->track("Tenant Updated", array("label" => "tenant-update"));
    ?>
    <script type="text/javascript" >
            window.open('<?php echo "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" ?>', '_self');
    </script>
    <?php
    }

    if($_REQUEST['action_type'] == "delete"){ // Delete Token from account
      if(count($resp1[0]['user_metadata']["tokens"]) > 0){  // if we have more than one token then...
        
        for($i = 0; $i < count($resp1[0]['user_metadata']["tokens"]); ++$i) {
          if($resp1[0]['user_metadata']["tokens"][$i]["id"] == $_REQUEST['id'] ){
            unset($resp1[0]['user_metadata']["tokens"][$i]);
            $resp1[0]['user_metadata']["tokens"] = array_values($resp1[0]['user_metadata']["tokens"]);
          }else{
            // $resp1[0]['user_metadata']["tokens"][$i]["selected"] = 0;
          }
        }
        updateTokenArray($resp1[0]['user_metadata']["tokens"]);
        $mp->track("Tenant Deleted", array("label" => "tenant-delete"));
        ?>
        <script type="text/javascript" >
                window.open('<?php echo "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" ?>', '_self');
        </script>
        <?php
      }else{
        // we have only 1 token, dont delete it. Throw warning?
        echo '<div class="alert alert-danger" role="alert">';
        echo 'You only have one token, this cant be deleted. Please edit it if you want to remove data.';
        echo '</div>';
      }
     
   
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
      <div class="alert alert-warning" role="alert">
        For Lab/Demo tenant use only. 
      </div>
    </div>
</div>

<div class="row"><!-- Start Card Row -->

<?php 
  if (!empty($resp1)) {
    if(isset($resp[0]['user_metadata']["tokens"])){ // if we have tokens set
      // print_r($resp[0]['user_metadata']["tokens"]);
      foreach ($resp[0]['user_metadata']["tokens"] as $key => $token){ 
?>

<div class="col-sm-3">
  <div class="card card-outline <?php  if($token["selected"]){echo "card-primary";}?>">
    <div class="card-header">
      <h3 class="card-title"><?php echo $token["URL"];?></h3>
    </div>
    <div class="card-body">
    <form action="" method="POST" name="tenants">

    <button type="submit" name="action_type" value="set" class="btn btn-primary" <?php  if($token["selected"]){ echo "disabled";}?>>Set as Active Tenant</button>
        <input type="hidden" id="cardID" class="form-control"  name="cardID" value="<?php
          echo $token["id"];      
      ?>">

      </form>
      <button name="edit_tenant" value="<?php echo $key;?>" id="edit_tenant" class="btn btn-success" style="float:right">Edit Tenant</button>
    </div>
    <!-- <div class="card-footer">
      The footer of the card
    </div> -->
  </div>
</div>

<?php       }    
    }
    } ?>
<div class="col-sm-3">
<div class="card mb-2 text-center">
				<div class="card-body">            
        <a href="<?php echo ROUTE_URL_INDEX;?>/addTenant" class="btn btn-secondary" role="button" aria-pressed="true">Add Tenant</a>

				</div>
				</div>
  </div>
</div> <!-- End Card Row -->

<div class="row" id="editRow" style="display: none;">
<h2 id="EditTitle">Edit Tenant Details</h2>
<form action="" method="POST" name="tenants">
  <div class="form-group">
    <div class="form-group">
      
      <label for="URL">Tenant URL</label>
      <div class="input-group mb-3">
	    <input type="text" id="URL" class="form-control"  name="URL" value="">
      </div>

	    <label for="token">API Token</label>
      <div class="input-group mb-3">
	    <input type="text" id="token"  class="form-control" name="token" value="">
      </div>

      <label for="token">Email Domain - domain where emails can be recieved</label>
      <div class="input-group mb-3">
        <span class="input-group-text">@</span>
        <input type="text" id="email_domain"  class="form-control" name="email_domain" value="">
      </div>

      <label for="token">User Domain - for the user login</label>
      <div class="input-group mb-3">
      <span class="input-group-text">@</span>
        <input type="text" id="user_domain"  class="form-control" name="user_domain" value="">
      </div>

    </div>
  </div>
  <input type="hidden" id="id" name="id" value="">
  <button type="submit" name="action_type" value="update" class="btn btn-primary">Update Settings</button>
  <button type="submit" name="action_type" value="delete" class="btn btn-danger">Delete Tenant</button>
</form>

    </div>
    <div style="margin:10px"> <!-- Spacer --> </div>

<script type='text/javascript'>
<?php
$php_array = $_SESSION["tokens"];
$js_array = json_encode($php_array);
echo "var javascript_array = ". $js_array . ";\n";

?>
$(document).ready(function() {
    $("button[name='edit_tenant']").click(function(){
      // set focus to the edit area. 
      //editRow.style.display = (editRow.style.display == "block") ? "none": "block";
      editRow.style.display = "block";
      var top = document.getElementById("EditTitle").offsetTop;
      window.scrollTo(0, top);
      
      let idSel =  $(this).val();
      $('#URL').val(javascript_array[idSel]["URL"]);
      $('#id').val(javascript_array[idSel]["id"]);
      $('#OrigURL').val(javascript_array[idSel]["URL"]);
      $('#token').val(javascript_array[idSel]["token"]);
      $('#email_domain').val(javascript_array[idSel]["email_domain"]);
      $('#user_domain').val(javascript_array[idSel]["user_domain"]);

    }); 
});
</script>

<?php
include 'includes/footer.php';