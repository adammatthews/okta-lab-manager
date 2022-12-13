<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
// Import the Composer Autoloader to make the SDK classes accessible:
require 'vendor/autoload.php';

use Aws\S3\S3Client;

// get the Mixpanel class instance with your project token
$mp = Mixpanel::getInstance("aa3c4bde1f50b8dfd21b4028a3ab5983", array("host" => "api-eu.mixpanel.com"));

// Load our environment variables from the .env file:
(Dotenv\Dotenv::createImmutable(__DIR__))->load();

// Now instantiate the Auth0 class with our configuration:
$auth0 = new \Auth0\SDK\Auth0([
    'domain' => $_ENV['AUTH0_DOMAIN'],
    'clientId' => $_ENV['AUTH0_CLIENT_ID'],
    'clientSecret' => $_ENV['AUTH0_CLIENT_SECRET'],
    'cookieSecret' => $_ENV['AUTH0_COOKIE_SECRET']
]);

//Instantiate an Amazon S3 client.
$s3Client = new S3Client([
  'version' => 'latest',
  'region'  => 'eu-west-2',
  'credentials' => [
  'key'    => $_ENV['AWS_KEY'],
  'secret' => $_ENV['AWS_SECRET']
  ]
  ]);

// Import our router library:
use Steampixel\Route;

// Define route constants:
define('ROUTE_URL_INDEX', rtrim($_ENV['AUTH0_BASE_URL'], '/'));
define('ROUTE_URL_LOGIN', ROUTE_URL_INDEX . '/login');
define('ROUTE_URL_CALLBACK', ROUTE_URL_INDEX . '/callback');
define('ROUTE_URL_LOGOUT', ROUTE_URL_INDEX . '/logout');

if (isset($env['AUTH0_MANAGEMENT_API_TOKEN'])) {
  $auth0->configuration()->setManagementToken($env['AUTH0_MANAGEMENT_API_TOKEN']);
}

// Create a configured instance of the `Auth0\SDK\API\Management` class, based on the configuration we setup the SDK ($auth0) using.
// If no AUTH0_MANAGEMENT_API_TOKEN is configured, this will automatically perform a client credentials exchange to generate one for you, so long as a client secret is configured.
$management = $auth0->management();

$session = $auth0->getCredentials();

// Handle Admin Area
if(isset($session)){ // get User Roles
  // Add Mixpanel user details
  $mp->people->set($session->user["sub"], array(
    '$name'       => $session->user["name"],
    '$email'        => $session->user["email"],
), $ip = 0, $ignore_time = true);

  $roles = $management->roles()->getUsers("rol_VYVt6zujxcCKkIle"); // Role ID for Admin users
  $roles = json_decode($roles->getBody()->__toString(), true, 512, JSON_THROW_ON_ERROR);  
  $is_admin = array_search($session->user["sub"], array_column($roles, 'user_id'));

  if($is_admin){
   // Admin Area Routes
    Route::add('/adminHome', function() use ($auth0) {
      global $management;
      global $is_admin;
      global $mp;
        include 'adminHome.php';
      });
  }else{

  }
}



Route::add('/', function() use ($auth0) {
  global $management;
  global $is_admin;
  global $mp;
    include 'home.php';
  });

  Route::add('/login', function() use ($auth0) {
    // It's a good idea to reset user sessions each time they go to login to avoid "invalid state" errors, should they hit network issues or other problems that interrupt a previous login process:
    $auth0->clear();
    // Finally, set up the local application session, and redirect the user to the Auth0 Universal Login Page to authenticate.
    header("Location: " . $auth0->login(ROUTE_URL_CALLBACK));
    exit;
});

Route::add('/callback', function() use ($auth0) {
  global $mp;
    // Have the SDK complete the authentication flow:
    $auth0->exchange(ROUTE_URL_CALLBACK);
    $mp->track("login");
    // Finally, redirect our end user back to the / index route, to display their user profile:
    header("Location: " . ROUTE_URL_INDEX);
    exit;
});

Route::add('/logout', function() use ($auth0) {
  global $mp;
    // Clear the user's local session with our app, then redirect them to the Auth0 logout endpoint to clear their Auth0 session.
    session_destroy();
    $mp->track("logout");
    header("Location: " . $auth0->logout(ROUTE_URL_INDEX));
    exit;
});


Route::add('/about', function() use ($auth0) {
  global $management;
  global $is_admin;
  global $mp;
    include 'about.php';
  });


Route::add('/brand', function() use ($auth0) {
  global $management;
  global $baseUrl;
  global $apiKey;
  global $mp;
  global $is_admin;
    include 'brand.php';
  });
// Post route example
Route::add('/brand', function() use ($auth0) {
  global $management;
    global $session;
    global $baseUrl;
    global $apiKey;
    global $mp;
    global $is_admin;
    include 'brand.php';
  }, 'post');


  Route::add('/sharedBrands', function() use ($auth0) {
    global $management;
    global $baseUrl;
    global $apiKey;
    global $mp;
    global $is_admin;
      include 'sharedBrands.php';
    });
  // Post route example
  Route::add('/sharedBrands', function() use ($auth0) {
    global $management;
      global $session;
      global $baseUrl;
      global $apiKey;
      global $mp;
      global $is_admin;
      include 'sharedBrands.php';
    }, 'post');

  Route::add('/brandAdd', function() use ($auth0) {
    global $management;
    global $is_admin;
    global $mp;
    include 'brandAdd.php';
  });
  // Post route example
  Route::add('/brandAdd', function() use ($auth0) {
    global $management;
    global $session;
    global $baseUrl;
    global $apiKey;
    global $s3Client;
    global $mp;
    global $is_admin;
    include 'brandAdd.php';
    //print_r($_POST);
  }, 'post');


  Route::add('/brandEdit', function() use ($auth0) {
    global $management;
    global $is_admin;
    global $mp;
    include 'brandEdit.php';
  });
  // Post route example
  Route::add('/brandEdit', function() use ($auth0) {
    global $management;
    global $session;
    global $baseUrl;
    global $apiKey;
    global $mp;
    global $is_admin;
    include 'brandEdit.php';
    //print_r($_POST);
  }, 'post');

  // Add User
  Route::add('/addUser', function() use ($auth0) {
    global $management;
    global $auth_user;
    global $mp;
    global $is_admin;
    include 'addUser.php';
  });
  // Post route example
  Route::add('/addUser', function() use ($auth0) {
    global $management;
    global $auth_user;
    global $session;
    global $baseUrl;
    global $apiKey;
    global $is_admin;
    global $mp;
    include 'addUser.php';
    //print_r($_POST);
  }, 'post');

  // User List
  Route::add('/userList', function() use ($auth0) {
    global $management;
    global $session;
    global $baseUrl;
    global $apiKey;
    global $is_admin;
    global $mp;
    include 'userList.php';
  });
  // Post route example
  Route::add('/userList', function() use ($auth0) {
    global $management; 
    global $session;
    global $baseUrl;
    global $apiKey;
    global $is_admin;
    global $mp;
    include 'userList.php';
    //print_r($_POST);
  }, 'post');


    // Manage Tenants
    Route::add('/manageTenants', function() use ($auth0) {
      global $management;
      global $session;
      global $baseUrl;
      global $apiKey;
      global $is_admin;
      global $mp;
      include 'manageTenants.php';
    });
    // Post route example
    Route::add('/manageTenants', function() use ($auth0) {
      global $management; 
      global $session;
      global $baseUrl;
      global $apiKey;
      global $is_admin;
      global $mp;
      include 'manageTenants.php';
      //print_r($_POST);
    }, 'post');

    // Manage Tenants
    Route::add('/addTenant', function() use ($auth0) {
      global $management;
      global $session;
      global $baseUrl;
      global $apiKey;
      global $is_admin;
      global $mp;
      include 'addTenant.php';
    });
    // Post route example
    Route::add('/addTenant', function() use ($auth0) {
      global $management; 
      global $session;
      global $baseUrl;
      global $apiKey;
      global $is_admin;
      global $mp;
      include 'addTenant.php';
      //print_r($_POST);
    }, 'post');


    // Manage Tenants
    Route::add('/getUsers', function() use ($auth0) {
      global $management;
      global $session;
      global $baseUrl;
      global $apiKey;
      global $is_admin;
      global $mp;

      include 'includes/api.php';
        
      if(isset($session)){ //i.e. if we're logged in - use the mgmt API to grab our user_metadata
      $usersList = getUsers();
  
      if(isset($usersList->errorSummary)){
          echo '<div class="alert alert-danger" role="alert">';
          echo $usersList->errorSummary;
          echo '. Your URL or Tenant Code is incorrect.';
          echo '</div>';
        }else{
          echo '[';
          foreach($usersList as $user) {
              $profile = $user->profile;
              $groups = Usergroups($user->id);
              $out = Flatten($groups); //get the usergroups into a format we can output as str. 
              if(!next($usersList)) {
                // This is the last $element
                echo '{ "ID": "'.$user->id.'", "Username": "'.$profile->login.'", "First": "'.$profile->firstName.'", "Last": "'.$profile->lastName.'", "LastLogin": "'.$user->lastLogin.'", "Status": "'.$user->status.'", "Source": "'.$user->credentials->provider->type.'", "Groups": "'.rtrim($out, ",").'"}';
              }else{
                echo '{ "ID": "'.$user->id.'", "Username": "'.$profile->login.'", "First": "'.$profile->firstName.'", "Last": "'.$profile->lastName.'", "LastLogin": "'.$user->lastLogin.'", "Status": "'.$user->status.'", "Source": "'.$user->credentials->provider->type.'", "Groups": "'.rtrim($out, ",").'"},';              }              
          }
          echo ']';
        }
      }else{
        echo "No Auth";
      }

    });
    // Pos


  // This tells our router that we've finished configuring our routes, and we're ready to begin routing incoming HTTP requests:
Route::run($_ENV['APP_BASE']);