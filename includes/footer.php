        </div>
        <!-- /.row -->
      </div>
      <!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- Main Footer -->
  <footer class="main-footer">
    <div class="col">
    <strong>Copyright &copy; <?php echo date("Y"); ?> <a href="https://am-serv.co.uk">AM SERV</a>.</strong>
    All rights reserved.
    <span >
    Managing 
    <?php 
    	$dbConfig = [
        "timeout" => false// deprecated! Set it to false!
      ];
      $brandStore = new \SleekDB\Store('brands', dirname(__DIR__, 1) . "/myDatabase", $dbConfig);
      echo $brandStore->count();

    ?> Brands!</span>
    <div class="float-right d-none d-sm-inline-block">
     
    </div>
  </footer>
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE -->
<script src="dist/js/adminlte.js"></script>

<!-- OPTIONAL SCRIPTS -->
<script src="plugins/chart.js/Chart.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="includes/coloris.js"></script>

</body>
</html>
