<?php
$title = "Admin Panel";
$session = $auth0->getCredentials();
include 'includes/head.php';
  
if ($session === null) {
  // The user isn't logged in.
  header("Location: ".ROUTE_URL_INDEX);
  die();
}

// The user is logged in.
?>

<?php

$role = $management->roles()->getUsers("rol_VYVt6zujxcCKkIle");
$role_resp = json_decode($role->getBody()->__toString(), true, 512, JSON_THROW_ON_ERROR);

// print_r($role_resp);
// echo $session->user["sub"];


$key = array_search($session->user["sub"], array_column($role_resp, 'user_id'));

var_dump($key);


if($key){
  echo "Admin";
}
  ?>

<p>This service allows you to manage multiple different brands for your Okta tenant and set them with a single click.</p>
<p>This tool also allows for a quick way to add new users to your tenant with random names.</p>

<b>Version</b> 
      <?php

$output=null;
$retval=null;
exec('git log -1 | grep ^commit | cut -d " " -f 2', $output, $retval);
// print_r($output);
$output = substr($output[0], 0, 7);
echo $output;
      ?>
<?php

include 'includes/footer.php';