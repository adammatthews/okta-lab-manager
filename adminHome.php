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

//var_dump($key);
// if($key){
//   echo "Admin";
// }

$db =  __DIR__ . "/myDatabase";
$brandStore = new \SleekDB\Store('brands', __DIR__ . "/myDatabase", $dbConfig);

$resp = $management->users()->getAll();
    
// Does the status code of the response indicate failure?
if ($resp->getStatusCode() !== 200) {
    die("API request failed.");
}

// Decode the JSON response into a PHP array:
$resp = json_decode($resp->getBody()->__toString(), true, 512, JSON_THROW_ON_ERROR);
//print("<pre>".print_r($resp,true)."</pre>");
  ?>

<table class="table">
<thead>
  <tr>
    <th scope="col">Email</th>
    <th scope="col">Brand Count</th>
    <th scope="col">Tokens Count</th>
    <th scope="col">Last Login</th>
    <th scope="col">Login Count</th>
  </tr>
</thead>
<tbody>
  <?php 
  foreach($resp as $user){
    $tokens =  $user["user_metadata"];
    echo "<tr>";
    echo "<th scope='row'>".$user["email"]."</th>";
    $userBrands = $brandStore->findBy(["userID", "=", $user["user_id"]],["_id" => "asc"]);
    echo "<td>".count($userBrands)."</td>";
    echo "<td>".count($tokens["tokens"])."</td>";
    echo "<td>".$user["last_login"]."</td>";
    echo "<td>".$user["logins_count"]."</td>";
    echo "</tr>";
  }
  ?>
</tbody>
</table>


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