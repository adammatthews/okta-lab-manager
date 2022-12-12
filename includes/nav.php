 <!-- Navbar -->
 <?php 
 if ($session != null) {?>
 <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="<?php echo ROUTE_URL_INDEX;?>" class="nav-link">Home</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="<?php echo ROUTE_URL_INDEX;?>/about" class="nav-link">About</a>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- Navbar Search -->
          <li class="nav-item">
          <a class="nav-link" href="<?php echo $baseUrl;?>" target="_blank"> Tenant: <?php echo $baseUrl;?> </a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href=""><i class="nav-icon fas fa-solid fa-retweet"></i></a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="<?php echo ROUTE_URL_LOGOUT;?>">Logout</a>
          </li>            

    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?php echo ROUTE_URL_INDEX;?>" class="brand-link">&nbsp;&nbsp;
      <!-- <img src="dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8"> -->
      <i class="fas fa-solid fa-dice-d6"></i>      
      <span class="brand-text font-weight-light"><?php echo $lab_name;?></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="<?php echo $session->user["picture"];?>" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block"><?php echo $session->user["name"];?></a>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
               <li class="nav-header">BRANDING</li>
               <li class="nav-item">
                <a href="<?php echo ROUTE_URL_INDEX;?>/brand" class="nav-link">
                  <i class="nav-icon fas fa-building"></i>
                  <p>
                    My Brands
                    <span class="badge badge-info right"><?php
                    $dbConfig = [
                      "timeout" => false// deprecated! Set it to false!
                    ];

                    $db =  dirname(__DIR__,1) . "/myDatabase";
                    $brandStore = new \SleekDB\Store('brands', $db, $dbConfig);
                    $myBrands = $brandStore->findBy(["userID", "=", $session->user["sub"]],["_id" => "asc"]);
                    $sharedBrands = $brandStore->findBy(["userID", "=", "shared"],["_id" => "asc"]);
                    echo count($myBrands);
                    $mp->people->set($session->user["sub"], array(
                      '$brand_count'       => count($myBrands),                
                  ), $ip = 0, $ignore_time = true);
                    
                    ?>
                    </span>
                  </p>
                </a>
              </li>

              <li class="nav-item">
                <a href="<?php echo ROUTE_URL_INDEX;?>/sharedBrands" class="nav-link">
                  <i class="nav-icon fas fa-share"></i>
                  <p>
                    Shared Brands
                    <span class="badge badge-info right"><?php echo count($sharedBrands);?></span>
                  </p>
                </a>
              </li>

         <li class="nav-item">
           <a href="<?php echo ROUTE_URL_INDEX;?>/brandAdd" class="nav-link">
             <i class="nav-icon fas fa-plus"></i>
             <p>
               Add Brand
             </p>
            </a>
          </li>

          <li class="nav-header">USERS</li>
          <li class="nav-item">
            <a href="<?php echo ROUTE_URL_INDEX;?>/userList" class="nav-link">
              <i class="nav-icon fas fa-solid fa-users"></i>
              <p>
                Users List
              </p>
            </a>
          </li>

          <li class="nav-item">
            <a href="<?php echo ROUTE_URL_INDEX;?>/addUser" class="nav-link">
              <i class="nav-icon fas fa-solid fa-user-plus"></i>
              <p>
                Add User
              </p>
            </a>
          </li>

          <li class="nav-header">TENANTS</li>
          <li class="nav-item">
            <a href="<?php echo ROUTE_URL_INDEX;?>/manageTenants" class="nav-link">
              <i class="nav-icon fas fa-solid fa-cubes"></i>
              <p>
                Manage Tenants
                <span class="badge badge-info right">
                  <?php
                  if(isset($resp[0]['user_metadata']["tokens"])){ // if we have tokens set
                    $count = count($resp[0]['user_metadata']["tokens"]);
                    echo $count;
                    $mp->people->set($session->user["sub"], array(
                      '$tenant_count'       => $count,                
                  ), $ip = 0, $ignore_time = true);
                  }?></span>
              </p>
            </a>
          </li>

          <li class="nav-item">
            <a href="<?php echo ROUTE_URL_INDEX;?>/addTenant" class="nav-link">
              <i class="nav-icon fas fa-solid fa-cube"></i>
              <p>
                Add Tenant
              </p>
            </a>
          </li>

          <?php
  if($is_admin){
  ?>
          <li class="nav-header">ADMIN</li>
          <li class="nav-item">
            <a href="<?php echo ROUTE_URL_INDEX;?>/adminHome" class="nav-link">
              <i class="nav-icon fas fa-solid fa-users"></i>
              <p>
                Manage
              </p>
            </a>
          </li>
<?php 
 } // end admin
?>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0"><?php echo $title;?></h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?php echo ROUTE_URL_INDEX;?>">Home</a></li>
              <li class="breadcrumb-item active"><?php echo $title;?></li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->
    <?php }?>
    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        <div class="row">