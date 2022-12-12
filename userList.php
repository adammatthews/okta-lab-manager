<?php
$title = "User List";
$session = $auth0->getCredentials();
include 'includes/head.php';

if ($session === null) {
  // The user isn't logged in.
  header("Location: ".ROUTE_URL_INDEX);
  die();
}

// The user is logged in.

if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['userId']))
{
    func();
}
//Function to peform the actions when buttons are clicked in the Group management form. This checks which action we want to perform from the post header, runs the function to the API and then refreshes the page. 
function func()
{
    if(isset($_POST['RemoveAdmin'])){
        print_r(removeFromGroup($_POST['userId'],"Admin"));
        echo "<meta http-equiv='refresh' content='0'>";
    }  
    if(isset($_POST['AddAdmin'])){
        print_r(addToGroup($_POST['userId'],"Admin"));
        echo "<meta http-equiv='refresh' content='0'>";
    }

    if(isset($_POST['RemoveSF'])){
        print_r(removeFromGroup($_POST['userId'],"SalesforceUsers"));
        echo "<meta http-equiv='refresh' content='0'>";
    }  
    if(isset($_POST['AddSF'])){
        print_r(addToGroup($_POST['userId'],"SalesforceUsers"));
        echo "<meta http-equiv='refresh' content='0'>";
    }
}
?>

<!-- Secure Content -->

<?php 
    $users = getUsers();
    if(isset($users->errorSummary)){
        echo '<div class="alert alert-danger" role="alert">';
        echo $users->errorSummary;
        echo '. Your URL or Tenant Code is incorrect.';
        echo '</div>';
      }else{ // if we have a working users call. 
        ?>

      <div id="myGrid" class="ag-theme-alpine" style="height: 600px; width:100%;"></div>

      <script type="text/javascript" charset="utf-8">
        // specify the columns
          const defaultColDef = {
              resizable: true,
          };

        const columnDefs = [
          { field: "ID" },
          { field: "Username", sortable: true, filter: true},
          { field: "First"},
          { field: "Last"},
          { field: "LastLogin"},
          { field: "Status", sortable: true, filter: true},
          { field: "Source", sortable: true, filter: true},
          { field: "Groups" },
        ];
      
            // let the grid know which columns and what data to use
            const gridOptions = {
              columnDefs: columnDefs,
              defaultColDef: defaultColDef,
          //rowData: rowData
        };

        // lookup the container we want the Grid to use
        const eGridDiv = document.querySelector('#myGrid');
        // create the grid passing in the div to use together with the columns & data we want to use
        new agGrid.Grid(eGridDiv, gridOptions);

          // fetch the row data to use and one ready provide it to the Grid via the Grid API
        fetch('<?php echo ROUTE_URL_INDEX;?>/getUsers')
            .then(response => response.json())
            .then(data => {
                gridOptions.api.setRowData(data);
            });
      </script>
<?php

    } // end of good users call
?>
  </tbody>
</table>

<?php include 'includes/footer.php';