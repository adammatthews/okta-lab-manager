<?php
$title = "Add User";
$session = $auth0->getCredentials();
include 'includes/head.php';
  
if ($session === null) {
  // The user isn't logged in.
  header("Location: ".ROUTE_URL_INDEX);
  die();
}
?>

<?php 
$auth0_user = $management->users()->getAll(['q' => $session->user["sub"]]);
    
// Does the status code of the response indicate failure?
if ($auth0_user->getStatusCode() !== 200) {
    die("API request failed.");
}

// Decode the JSON response into a PHP array:
$auth0_user = json_decode($auth0_user->getBody()->__toString(), true, 512, JSON_THROW_ON_ERROR);

if (isset($_POST['submit'])){

  if(!isset($_REQUEST['active'])){
    $_REQUEST['active'] = "false";
  }
  
	$data = '{
    "profile": {
        "firstName": "'.$_REQUEST['firstName'].'",
        "lastName": "'.$_REQUEST['lastName'].'",
        "email": "'.$_REQUEST['email'].'",
        "login": "'.$_REQUEST['login'].'@'.$auth0_user[0]['user_metadata']["settings"]["email_domain"].'"      
    },
    "credentials": {
        "password" : { "value": "'.$_REQUEST['password'].'" }
    }
    }';

    //NOTE - this doesnt quite work - all users get added as active. 
    
    if(isset($_REQUEST['active'])){
        $result = newUser($data, $_REQUEST['active']);
      }
      else{
        $result = newUser($data, $_REQUEST['active']);
      }

    if(isset($result->id)){
    	echo '<div class="alert alert-success" role="alert">User with ID: '.$result->id.' has been successfully created! </div>';
      $mp->track("User Added", array("label" => "user-added"));
    }
    else{
    	echo '<div class="alert alert-danger" role="alert"><b>Failure to create</b>: '. $result->errorSummary.'<br>';
      if(isset($result->errorCauses[0])){
      echo '<b>Error Summary</b>: '.$result->errorCauses[0]->errorSummary.'<br>';
      }
      echo '<b>Error ID</b>: '.$result->errorId.'</div>';	
    }	
}

?>
 <div class="row">
    <div class="col">

      <form action="" method="POST" name="user">
  <div class="form-group">
	  <label for="firstName">First name</label>
	  <input type="text" id="firstName" class="form-control" required pattern="\w+" name="firstName" placeholder="John">
	  <label for="lastName">Last name</label>
	  <input type="text" id="lastName"  class="form-control" required pattern="\w+" name="lastName" placeholder="Appleseed">
	  <label for="email">Email</label>
	  <input type="text" id="email" class="form-control" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" name="email" placeholder="john.appleseed@apple.com">

<label for="login" class="form-label">Login</label>
    <div class="input-group has-validation">
      
      <input type="text" class="form-control" required id="login" aria-describedby="inputGroupPrepend" name="login">
      <span class="input-group-text" id="inputGroupPrepend">@<?php echo $_SESSION["selToken"]["user_domain"];//$auth0_user[0]['user_metadata']["settings"]["user_domain"]; ?></span>
      <div class="invalid-feedback">
        Please choose a username.
      </div>
    </div>  
  </div>
<!--   
  <div class="form-group">
  		<label for="mobilePhone">Mobile Phone</label>
		<input type="tel" id="mobilePhone" class="form-control" name="mobilePhone" required placeholder="01234 235 236">
  </div> -->

  <div class="form-group">
    <label for="password">Password</label>
    <input class="form-control" type="password" class="form-control" id="password" required name="password" placeholder="Password">
  </div>
<br>
  <button type="submit" class="btn btn-primary" name="submit">Submit</button>
</form>
    </div>
    <div class="col">
      Form to set up a brand new user in Okta. 
      <br>
      <button id="setText" class="btn btn-secondary">
        Generate Random Name
    </button>
    <script>
      // Use https://randomuser.me/ to generate a username. 
      // think about https://mojoaxel.github.io/bootstrap-select-country/index.html
      
        $("#setText").click(function(event) {
          var element = document.getElementById('country');
          var url = "https://randomuser.me/api/?nat=";
          var useUrl = url.concat(element.value);        
                var settings = {
                "url": useUrl,
                "method": "GET",
                "timeout": 0,
                dataType: 'json',
              };
              $.ajax(settings).done(function (response) {
                var fName = response.results[0].name.first;
                var lName = response.results[0].name.last; 
                var uName = fName.toLowerCase()+'.'+lName.toLowerCase();
                var uName = uName.replace(/\s/g, '');
                $('#firstName').val(fName);
                $('#lastName').val(lName);
                $('#email').val(uName+'@<?php echo $_SESSION["selToken"]["email_domain"];//$auth0_user[0]['user_metadata']["settings"]["email_domain"]; ?>');
                $('#login').val(uName);
              });
        });
    </script>

  <select class="form-select" aria-label="Default select example" id="country" style="width: 90px;">
    <option selected value="gb">GB</option>
    <option value="nl">NL</option>
    <option value="us">US</option>
    <option value="es">ES</option>
    <option value="de">DE</option>
  </select>
    </div>
  </div>

<?php include 'includes/footer.php';