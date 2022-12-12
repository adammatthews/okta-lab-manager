<?php
$title = "Edit Brand";
$session = $auth0->getCredentials();
include 'includes/head.php';

if ($session === null) {
  // The user isn't logged in.
  header("Location: ".ROUTE_URL_INDEX);
  die();
}

// If we have post data and brand name is present
if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['brandName']))
{
    func();
}
function func()
{
	global $session;
	global $mp;
	$dbConfig = [
		"timeout" => false// deprecated! Set it to false!
	  ];
	$brandStore = new \SleekDB\Store('brands', __DIR__ . "/myDatabase", $dbConfig);
	

	//if(isset($_POST["brandShared"])) {
	if(isset($_POST["setShared"])) {
		$userID = "shared";
	}
	else{
		$userID = $session->user["sub"];
	}

	$data = [ 
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
		"userID" => $userID
	];

	$brandUpdate = $brandStore->updateById($_POST["brandId"], $data);
	

	if($_REQUEST['action_type'] == "update_and_apply"){ // if we've selected upate and apply
		$id = $_POST['brandId'];

		$brandID = $_POST["brandId"]; 
		$brandStore = new \SleekDB\Store('brands', __DIR__ . "/myDatabase", $dbConfig);
		$brandDetails = $brandStore->findById($brandID);

		$logo = $brandDetails["Logo"];
		$background = $brandDetails["Background"];
		$favicon = $brandDetails["Favicon"];

		$brands = getBrands();
 
		if(isset($brands->errorSummary)){
			echo '<div class="alert alert-danger" role="alert">';
			//echo $check->errorSummary;
			echo ' Your URL or Tenant Code is incorrect. <a href="'.ROUTE_URL_INDEX.'/manageTenants">Click here to fix.</a>';
			echo '</div>';
			die();
		}
		$brandId = $brands[0]->id;
		$themes = getThemes($brandId);
		$themeId = $themes[0]->id;

		$themeData = '{
			"primaryColorHex": "'.$brandDetails["primaryColorHex"].'",
			"secondaryColorHex": "'.$brandDetails["primaryColorHex"].'",
			"signInPageTouchPointVariant": "'.$brandDetails["signInPageTouchPointVariant"].'",
			"endUserDashboardTouchPointVariant": "'.$brandDetails["endUserDashboardTouchPointVariant"].'",
			"errorPageTouchPointVariant": "'.$brandDetails["errorPageTouchPointVariant"].'",
			"emailTemplateTouchPointVariant": "'.$brandDetails["emailTemplateTouchPointVariant"].'"
		}';

		updateTheme($brandId,$themeId,$themeData);

		//Upload a Logo
		uploadLogo($brandId,$themeId,$logo);
		uploadBackground($brandId,$themeId,$background);
		uploadFavicon($brandId,$themeId,$favicon);
		// UPDATE FIELD IN THE BRAND TO IDENTIFY WHATS SET
		$currentBrand = getBrands();
		$cBrandId = $currentBrand[0]->id;
		$cTheme = getThemes($cBrandId);

		$updateLogo = [
			"themeLogo" => $cTheme[0]->logo
		];

		$brandUpdate = $brandStore->updateById($_POST["brandId"], $updateLogo); // end update. 

		print('<div class="alert alert-success" role="alert">');
  		print('Branding has been set to "'.$brandDetails["Name"].'".');
		print('</div>');
		$mp->track("Branding Applied", array("label" => "apply-branding"));
    }  

	print('<div class="alert alert-success" role="alert">');
	print('Brand "'.$brandUpdate["Name"].'" has been successfully updated. <a href="'.ROUTE_URL_INDEX.'/brand">Go back to apply to your tenant.</a>');
	print('</div>');
}

?>

<!-- Secure Content -->

<?php
$brandID = $_POST["brandId"]; 
$brandStore = new \SleekDB\Store('brands', __DIR__ . "/myDatabase", $dbConfig);
$editBrand = $brandStore->findById($brandID);
?>
<div class="row align-items-start">
    <div class="col">
<form action="" method="post">
  <div class="form-group">
  <div class="mb-3">
    <label for="brandName">Brand Name</label>
    <input type="text" class="form-control" id="brandName" name="brandName" value="<?php echo $editBrand["Name"];?>">
   </div>
   <div class="mb-3">
	<label for="brandLogo">Brand Logo URL</label>
    <input type="text" class="form-control" id="brandLogo" name="brandLogo" value="<?php echo $editBrand["Logo"];?>">
	</div>
   <div class="mb-3">
	<label for="brandBackground">Brand Background URL</label>
    <input type="text" class="form-control" id="brandBackground" name="brandBackground" value="<?php echo $editBrand["Background"];?>">
	</div>
   <div class="mb-3">
	<label for="brandFavicon">Brand Favicon URL</label>
    <input type="text" class="form-control" id="brandFavicon" name="brandFavicon" value="<?php echo $editBrand["Favicon"];?>">
	</div>
   <div class="mb-3">
	<label for="brandPrimaryColorHex">Primary Color</label><br>
    <input type="text" class="form-control" id="brandPrimaryColorHex" name="brandPrimaryColorHex" value="<?php echo $editBrand["primaryColorHex"];?>" data-coloris>
	</div>
   <div class="mb-3">
	<label for="brandSecondaryColorHex">Secondary Color </label><br>
    <input type="text" class="form-control" id="brandSecondaryColorHex" name="brandSecondaryColorHex" value="<?php echo $editBrand["secondaryColorHex"];?>" data-coloris>	
</div>
	<!-- <label for="brandPrimaryColorHex" class="form-label">Color picker</label>
<input type="color" class="form-control form-control-color" name="brandPrimaryColorHex"  id="brandPrimaryColorHex" value="<?php echo $editBrand["primaryColorHex"];?>" title="Choose your color">
   -->

</div>
  <div class="form-group">

   <div class="mb-3">
  	<label for="signInPageTouchPointVariant">signInPageTouchPointVariant</label>
    <select class="form-control" id="signInPageTouchPointVariant" name="signInPageTouchPointVariant">
      <option value="OKTA_DEFAULT"<?=$editBrand['signInPageTouchPointVariant'] == 'OKTA_DEFAULT' ? ' selected="selected"' : '';?>>OKTA_DEFAULT</option>
      <option value="BACKGROUND_IMAGE"<?=$editBrand['signInPageTouchPointVariant'] == 'BACKGROUND_IMAGE' ? ' selected="selected"' : '';?>>BACKGROUND_IMAGE</option>
    </select>
	</div>

   <div class="mb-3">	   
	<label for="endUserDashboardTouchPointVariant">endUserDashboardTouchPointVariant</label>
    <select class="form-control" id="endUserDashboardTouchPointVariant" name="endUserDashboardTouchPointVariant">
      <option value="OKTA_DEFAULT"<?=$editBrand['endUserDashboardTouchPointVariant'] == 'OKTA_DEFAULT' ? ' selected="selected"' : '';?>>OKTA_DEFAULT</option>
      <option value="LOGO_ON_FULL_WHITE_BACKGROUND"<?=$editBrand['endUserDashboardTouchPointVariant'] == 'LOGO_ON_FULL_WHITE_BACKGROUND' ? ' selected="selected"' : '';?>>LOGO_ON_FULL_WHITE_BACKGROUND</option>
    </select>
 </div>

   <div class="mb-3">
	<label for="errorPageTouchPointVariant">errorPageTouchPointVariant</label>
    <select class="form-control" id="errorPageTouchPointVariant" name="errorPageTouchPointVariant">
      <option value="OKTA_DEFAULT"<?=$editBrand['errorPageTouchPointVariant'] == 'OKTA_DEFAULT' ? ' selected="selected"' : '';?>>OKTA_DEFAULT</option>
      <option value="BACKGROUND_IMAGE"<?=$editBrand['errorPageTouchPointVariant'] == 'BACKGROUND_IMAGE' ? ' selected="selected"' : '';?>>BACKGROUND_IMAGE</option>
    </select>
 </div>

   <div class="mb-3">
	<label for="emailTemplateTouchPointVariant">emailTemplateTouchPointVariant</label>
    <select class="form-control" id="emailTemplateTouchPointVariant" name="emailTemplateTouchPointVariant">
      <option value="OKTA_DEFAULT"<?=$editBrand['emailTemplateTouchPointVariant'] == 'OKTA_DEFAULT' ? ' selected="selected"' : '';?>>OKTA_DEFAULT</option>
      <option value="FULL_THEME"<?=$editBrand['emailTemplateTouchPointVariant'] == 'FULL_THEME' ? ' selected="selected"' : '';?>>FULL_THEME</option>
    </select>
	</div>

   <div class="mb-3">
	<input id="brandId" name="brandId" type="hidden" value="<?php print($_POST["brandId"]);?>">
	<?php if(isset($_POST["brandShared"])){?>
	<input id="brandShared" name="brandShared" type="hidden" value="1">
	<?php } ?>
	</div>

	<?php if($is_admin){?>
	<div class="mb-3">
	<input class="form-check-input" type="checkbox" value="" id="setShared" name="setShared" <?php if(isset($_POST["brandShared"])){echo "checked";}?>>
  	<label class="form-check-label" for="setShared">
    	Set as Shared Brand
  	</label>
	</div>
	<?php } ?>

</div>
	<button type="submit" name="action_type" value="update_only" class="btn btn-primary">Update Brand</button>
	<button type="submit" name="action_type" value="update_and_apply" class="btn btn-primary">Update & Apply Brand</button>
	
</form>

</div>

<div class="col">
<div class="field">
	<label class="field-label">Logo</label><br>
	<img src="<?php echo $editBrand["Logo"];?>" class="img-thumbnail" width="300" />
</div>
<div class="field">
	<label>Background</label><br>
	<img src="<?php echo $editBrand["Background"];?>" class="img-thumbnail" width="500" />
</div>
<div class="field">
	<label>Favicon</label><br>
	<img src="<?php echo $editBrand["Favicon"];?>" class="img-thumbnail" width="100" />
</div>

</div>

</div>

<?php include 'includes/footer.php'; ?>