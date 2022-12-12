<?php
$title = "About";
$session = $auth0->getCredentials();
include 'includes/head.php';
  
if ($session === null) {
  // The user isn't logged in.
  return;
}

// The user is logged in.
?>

<h3>What is this tool for?</h3>
<p>This tool is intended to help manage various aspects of your Okta demo labs.</p>
<h3>Branding</h3>
<p>You can manage multiple brand schemes for your various Okta labs. Add your different brandings in "Manage Brands" and you can switch between these on you various tenants.</p>
<h4>Image Storage</h4>
<p>As of March 2022, you will need to still upload your brand logo, background and favicon on some publically available storage and use the URL. I recommend adding these to a public S3 bucket, but anywhere publically available will be OK.</p>
<p>When you add your brand, it will preview your images so you will know instantly if the images are available.</p>
<h3>User Management</h3>
<p>You can also add users to your lab with random localised names, no more John Wick and Joe Bloggs!</p>
<p>Just select your localisation, and click "Generate Random Name". Enter your password.</p>
<p>&nbsp;</p>
<p>This tool as built by Adam Matthews, SE at Okta. Feel free to contact <a href="mailto:adam.matthews@okta.com ">adam.matthews@okta.com.</a> Or on Okta Slack</p>
<?php
include 'includes/footer.php';