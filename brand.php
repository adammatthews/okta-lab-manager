<?php
$title = "My Brands";
$session = $auth0->getCredentials();
include 'includes/head.php';

if ($session === null) {
  // The user isn't logged in.
  header("Location: ".ROUTE_URL_INDEX);
  die();
}

// The user is logged in.

if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['brandId']))
{
    func();
}
//Function to peform the actions when buttons are clicked in the Group management form. This checks which action we want to perform from the post header, runs the function to the API and then refreshes the page. 
function func()
{
	global $mp;
	$dbConfig = [
		"timeout" => false// deprecated! Set it to false!
	];

	if(isset($_POST['DeleteBrand'])){
		$id = $_POST['brandId'];
		$brandStore = new \SleekDB\Store('brands', __DIR__ . "/myDatabase", $dbConfig);
		$deleteBrand = $brandStore->deleteById($id);
		
  		if($deleteBrand){
				print('<div class="alert alert-success" role="alert">');
			  echo "Brand has been deleted";
		  }else{
			print('<div class="alert alert-failure" role="alert">');
			  echo "Brand was not deleted";
		  }
		  print('</div>');
	};
	
    if(isset($_POST['SetBrand'])){
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

	if(isset($_POST['resetBrand'])){
		$themeDataDefault = '{
			"primaryColorHex": "#1662dd",
			"secondaryColorHex": "#ebebed",
			"signInPageTouchPointVariant": "OKTA_DEFAULT",
			"endUserDashboardTouchPointVariant": "OKTA_DEFAULT",
			"errorPageTouchPointVariant": "OKTA_DEFAULT",
			"emailTemplateTouchPointVariant": "OKTA_DEFAULT"
		}';

		$brands = getBrands();
		$brandId = $brands[0]->id;
		$themes = getThemes($brandId);
		$themeId = $themes[0]->id;

		deleteLogo($brandId,$themeId);
		deleteBackground($brandId,$themeId);
		deleteFavicon($brandId,$themeId);
		updateTheme($brandId,$themeId,$themeDataDefault);

		print('<div class="alert alert-success" role="alert">');
  		print('Branding has been reset.');
		print('</div>');
		$mp->track("Branding Reset", array("label" => "branding-reset"));
	}
} // end func

$db =  __DIR__ . "/myDatabase";
$brandStore = new \SleekDB\Store('brands', __DIR__ . "/myDatabase", $dbConfig);
$allBrands = $brandStore->findBy(["userID", "=", $session->user["sub"]],["_id" => "asc"]);

$currentBrand = getBrands();
   if(isset($currentBrand->errorSummary)){
	 echo '<div class="alert alert-danger" role="alert">';
	 echo $currentBrand->errorSummary;
	 echo '. Your URL or Tenant Code is incorrect.';
	 echo '</div>';
   }else{
	   if(isset($currentBrand)){
		$cBrandId = $currentBrand[0]->id;
		$cTheme = getThemes($cBrandId);
	   }
	   else{
		echo '<div class="alert alert-danger" role="alert">';
		echo 'Your URL or Tenant Code might be incorrect. Brand Application likley to fail.';
		echo '</div>';
	   }
   }
?>

<div class="col">
<div class="row">

		<?php
		foreach($allBrands as $brand){
		?>

		<div class="col-md-12 col-lg-6 col-xl-4 ">
		<div class="card  mb-2 text-center card-brand <?php if(isset($brand["themeLogo"])){
			if(isset($currentBrand) && $cTheme[0]->logo == $brand["themeLogo"] ){
				print('card-bg-selected');
			}
		}?>">
		<div class="card-header">
		<?php echo $brand["Name"];?>
		</div>
		<div class="card-body">
			<img class="card-img-top img-card-bg" src="<?php echo $brand["Background"];?>" alt="Background">
			<img class="fishes" src="<?php echo $brand["Logo"];?>" alt="Logo">
			<!-- <p class="card-text">With supporting text below as a natural lead-in to additional content.</p> -->
		</div>
		<div class="card-footer text-muted">
		<span style="float: left; margin-left:10px;">
		<form action="" method="post">
				<input type="submit" name="SetBrand" value="Apply Branding" class="btn btn-primary" onclick="javascript:return confirm('Are you sure you want to set branding to '<?php print($brand["Name"]) ;?>'?')" />
				<input id="brandId" name="brandId" type="hidden" value="<?php print($brand["_id"]);?>">
			</form>
		</span>
		<span style="float: left;  margin-left:10px;">
			<form action="<?php echo ROUTE_URL_INDEX;?>/brandEdit" method="post">
				<input type="submit" name="SetBrand" value="Edit" class="btn btn-primary" />
				<input id="brandId" name="brandId" type="hidden" value="<?php print($brand["_id"]);?>">
			</form>
		</span>
		<span style="float: right;">
			<form action="" method="post">
				<input type="submit" name="DeleteBrand" value="Delete" class="btn btn-danger" onclick="javascript:return confirm('Are you sure you want to delete this brand?')"/>
				<input id="brandId" name="brandId" type="hidden" value="<?php print($brand["_id"]);?>">
			</form>
		</span>
		</div>
			<!-- Have this set when the form button is clicked for this card only -->
			<!-- <div class="overlay"> 
			<i class="fas fa-2x fa-sync-alt fa-spin"></i>
			</div> -->
		</div>
		
		</div>

		<?php
		print("</tr>");
		}
		?>
<!-- 		
		<div class="col-md-12 col-lg-6 col-xl-4 ">
		<div class="card card-bg-selected mb-2 text-center card-brand ">
		<div class="card-header">
			AM SERV OIE
		</div>
		<div class="card-body">
			<img class="card-img-top img-card-bg" src="https://custombrandportal-okta.s3.eu-west-2.amazonaws.com/background-oie.jpg" alt="Dist Photo 1">
			<img class="fishes" src="https://custombrandportal-okta.s3.eu-west-2.amazonaws.com/logo-oie.png" alt="Dist Photo 1">
		</div>
		<div class="card-footer text-muted">
			<a href="#" class="btn btn-primary">Apply</a>  <a href="#" class="btn btn-secondary">Edit</a> <span style="float: right;"><a href="#" class="btn btn-danger">Delete</a></span>
		</div>
		</div>
		</div> -->
			<div class="col-md-12 col-lg-6 col-xl-4">
				<div class="card mb-2 text-center">
				<div class="card-body">            
					<p class="card-text"><a href="<?php echo ROUTE_URL_INDEX;?>/brandAdd">Add Brand</a></p>
				</div>
				</div>

				<div class="col">
					<form action="" method="post">
						<input type="submit" name="resetBrand" value="Reset Branding" class="btn btn-warning" onclick="javascript:return confirm('Are you sure you want to reset branding for this tenant?')" />
						<input id="brandId" name="brandId" type="hidden" value="1000">
					</form>
				</div>

				</div>
		</div>
	</div>
<?php include 'includes/footer.php'; ?>