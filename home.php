<?php
$title = "Lab Management - Home";
$session = $auth0->getCredentials();
include 'includes/head.php';
  
if ($session === null) {
  // The user isn't logged in.
  
?>

<style type="text/css">
.navbar {
  display: none; !override;
}
main {
  margin-left: auto;
  margin-right: auto;
  text-align: center;
  width: 100%;
  padding: 10px;

}
</style>

  <main class="px-3">
    <h1>Okta Lab Management</h1>
    <p class="lead">Simple service to manage multiple branding schemes for your Okta tenant. Designed for Lab and Demo use only</p>
    <p class="lead">Click to sign up/sign in with Auth0</p>
    <p class="lead">
      <a href="<?php echo ROUTE_URL_LOGIN;?>" class="btn btn-lg btn-secondary">Log In</a>
    </p>
  </main>

<?php
  return;
}

// The user is logged in.
?>

<p>This service allows you to manage multiple different brands for your Okta tenant and set them with a single click.</p>
<p>This tool also allows for a quick way to add new users to your tenant with random names.</p>

<div class="px-4 py-5 my-5 text-center">
    <!-- <h2 class="display-5 fw-bold">Centered hero</h2> -->
    <div class="col-lg-6 mx-auto">
      <p class="lead mb-4">Get started by heading to Branding and adding a brand!</p>
      <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
        <a href="<?php echo ROUTE_URL_INDEX;?>/brand"><button type="button" class="btn btn-primary btn-lg px-4 gap-3">Get Started</button></a>
      </div>
    </div>
  </div>
<?php

include 'includes/footer.php';