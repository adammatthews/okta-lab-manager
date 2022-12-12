<?php
$title = "Brand - Add";
$session = $auth0->getCredentials();
include 'includes/head.php';

if ($session === null) {
  // The user isn't logged in.
  header("Location: ".ROUTE_URL_INDEX);
  die();
}

// The user is logged in.

//if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['brandName']))
if($_SERVER['REQUEST_METHOD'] == "POST")
{
    func();
    //upload();
}
//Function to peform the actions when buttons are clicked in the Group management form. This checks which action we want to perform from the post header, runs the function to the API and then refreshes the page. 
function func()
{
	global $session;
  global $mp;
	$dbConfig = [
		"timeout" => false// deprecated! Set it to false!
	  ];
	$brandStore = new \SleekDB\Store('brands', __DIR__ . "/myDatabase", $dbConfig);

	$brand = [ 
		"Name" => $_POST['brandName'],
		"Logo"  =>  $_POST['brandLogo'],
		"Background"  =>  $_POST['brandBackground'],
    "Favicon"  =>  $_POST['brandFavicon'],
		"primaryColorHex"  =>  $_POST['brandPrimaryColorHex'],
		"secondaryColorHex"  =>  $_POST['brandSecondaryColorHex'],
		"signInPageTouchPointVariant"  =>  $_POST['signInPageTouchPointVariant'],
		"endUserDashboardTouchPointVariant"  =>  $_POST['endUserDashboardTouchPointVariant'],
		"errorPageTouchPointVariant"  =>  $_POST['errorPageTouchPointVariant'],
		"emailTemplateTouchPointVariant"  =>  $_POST['emailTemplateTouchPointVariant'], 
    	"userID" => $session->user["sub"]
	];

  $results = $brandStore->insert($brand);

  print('<div class="alert alert-success" role="alert">');
  print('New brand "'.$results["Name"].'" has been successfully added. <a href="'.ROUTE_URL_INDEX.'/brand">Go back to apply to your tenant.</a>');
  print('</div>');
  $mp->track("Branding Added", array("label" => "branding-add"));
}

function upload()
{
  global $s3Client;



if($_SERVER["REQUEST_METHOD"] == "POST"){
  // Check if file was uploaded without errors
if(isset($_FILES["anyfile"]) && $_FILES["anyfile"]["error"] == 0){
  $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
  $filename = $_FILES["anyfile"]["name"];
  $filetype = $_FILES["anyfile"]["type"];
  $filesize = $_FILES["anyfile"]["size"];
  // Validate file extension
  $ext = pathinfo($filename, PATHINFO_EXTENSION);
  if(!array_key_exists($ext, $allowed)) die("Error: Please select a valid file format.");
  // Validate file size - 10MB maximum
  $maxsize = 10 * 1024 * 1024;
  if($filesize > $maxsize) die("Error: File size is larger than the allowed limit.");
  // Validate type of the file
  if(in_array($filetype, $allowed)){
  // Check whether file exists before uploading it
  if(file_exists("upload/" . $filename)){
  echo $filename . " is already exists.";
  } else{
  if(move_uploaded_file($_FILES["anyfile"]["tmp_name"], "upload/" . $filename)){
  $bucket = 'am-serv-branding';
  $file_Path = __DIR__ . '/upload/'. $filename;
  $key = basename($file_Path);
  try {
  $result = $s3Client->putObject([
  'Bucket' => $bucket,
  'Key'    => $key,
  'Body'   => fopen($file_Path, 'r'),
  'ACL'    => 'public-read', // make file 'public'
  ]);
  echo "Image uploaded successfully. Image path is: ". $result->get('ObjectURL');
  } catch (Aws\S3\Exception\S3Exception $e) {
  echo "There was an error uploading the file.\n";
  echo $e->getMessage();
  }
  echo "Your file was uploaded successfully.";
  }else{
  echo "File is not uploaded";
  }
  } 
  } else{
  echo "Error: There was a problem uploading your file. Please try again."; 
  }
  } else{
  echo "Error: " . $_FILES["anyfile"]["error"];
  }
  }

  
}

?>

<!-- Secure Content -->

<form action="" method="post">
  <div class="form-group">
    <label for="brandName">Brand Name</label>
    <input type="text" class="form-control" id="brandName" name="brandName">
	<label for="brandLogo">Brand Logo URL (E.g: https://custombrandportal-okta.s3.eu-west-2.amazonaws.com/logo-oie.png)</label>
    <input type="text" class="form-control" id="brandLogo" name="brandLogo">
	<label for="brandBackground">Brand Background URL (E.g: https://custombrandportal-okta.s3.eu-west-2.amazonaws.com/background-oie.jpg)</label>
    <input type="text" class="form-control" id="brandBackground" name="brandBackground">
  <label for="brandFavicon">Brand Favicon URL (E.g: https://custombrandportal-okta.s3.eu-west-2.amazonaws.com/amserv-favicon.png)</label>
    <input type="text" class="form-control" id="brandFavicon" name="brandFavicon">
	<label for="brandPrimaryColorHex">Primary Color Hex</label>
    <input type="text" class="form-control" id="brandPrimaryColorHex" name="brandPrimaryColorHex" value="#1662dd" data-coloris>
    <br>
	<label for="brandSecondaryColorHex">Secondary Color Hex</label>
    <input type="text" class="form-control" id="brandSecondaryColorHex" name="brandSecondaryColorHex" value="#ebebed" data-coloris>	
  </div>
  <div class="form-group">
  	<label for="signInPageTouchPointVariant">signInPageTouchPointVariant</label>
    <select class="form-control" id="signInPageTouchPointVariant" name="signInPageTouchPointVariant">
      <option value="OKTA_DEFAULT">OKTA_DEFAULT</option>
      <option value="BACKGROUND_IMAGE" selected="selected">BACKGROUND_IMAGE</option>
    </select>

	<label for="endUserDashboardTouchPointVariant">endUserDashboardTouchPointVariant</label>
    <select class="form-control" id="endUserDashboardTouchPointVariant" name="endUserDashboardTouchPointVariant">
      <option value="OKTA_DEFAULT" selected="selected">OKTA_DEFAULT</option>
      <option value="BACKGROUND_IMAGE">LOGO_ON_FULL_WHITE_BACKGROUND</option>
    </select>

	<label for="errorPageTouchPointVariant">errorPageTouchPointVariant</label>
    <select class="form-control" id="errorPageTouchPointVariant" name="errorPageTouchPointVariant">
      <option value="OKTA_DEFAULT">OKTA_DEFAULT</option>
      <option value="BACKGROUND_IMAGE" selected="selected">BACKGROUND_IMAGE</option>
    </select>

	<label for="emailTemplateTouchPointVariant">emailTemplateTouchPointVariant</label>
    <select class="form-control" id="emailTemplateTouchPointVariant" name="emailTemplateTouchPointVariant">
      <option value="OKTA_DEFAULT" selected="selected">OKTA_DEFAULT</option>
      <option value="BACKGROUND_IMAGE">FULL_THEME</option>
    </select>
    </div>
    <div class="form-group">
    <button type="submit" class="btn btn-primary">Add Brand</button>
    </div>

</form>
<!-- 
<form action="" method="post" enctype="multipart/form-data">
        <h2>PHP Upload File</h2>
        <label for="file_name">Filename:</label>
        <input type="file" name="anyfile" id="anyfile">
        <input type="submit" name="submit" value="Upload">
        <p><strong>Note:</strong> Only .jpg, .jpeg, .gif, .png formats allowed to a max size of 5 MB.</p>
    </form> -->

<?php include 'includes/footer.php'; ?>