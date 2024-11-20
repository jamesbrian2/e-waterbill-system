<?php
 include ('includes/config.php'); 
 include 'sitio.php';
 if(strlen($_SESSION['user'])==0)
 {	
 header('location:index2.php');
 }
 else{
    $_SESSION['sidebarname']= 'Encoder';
    $sitio=getsitio($dbh);
      
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['connect'])) {
      $encoderid = $_SESSION['user']; 
      $paids = "paid";
      $active = 'active';
      $id = $_POST['id'];
  
      $paid = "UPDATE bill SET payment = :paid, encoder_id = :encoder WHERE serial_number = :id AND payment = 'pending'";
      $paidconsumers = $dbh->prepare($paid);
      $paidconsumers->bindParam(':id', $id, PDO::PARAM_INT);        
      $paidconsumers->bindParam(':encoder', $encoderid, PDO::PARAM_INT); 
      $paidconsumers->bindParam(':paid', $paids, PDO::PARAM_STR);     
  
      $paid1 = "UPDATE consumers SET warning = :active WHERE serial_no = :id";
      $paidconsumer1 = $dbh->prepare($paid1);
      $paidconsumer1->bindParam(':id', $id, PDO::PARAM_INT);        
      $paidconsumer1->bindParam(':active', $active, PDO::PARAM_STR); 
      try {
          $paidconsumers->execute();
          $paidconsumer1->execute();
          $success = "Consumer Reconnected";
          header("refresh:1; url=terminated.php");  
      } catch (PDOException $ex) {
          echo "Error: " . $ex->getMessage();
      }
  }
  


      if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])){
        $id=$_POST['id'];
        $delete="DELETE FROM consumers WHERE serial_no=:id";
        $deleteconsumers=$dbh->prepare($delete);
        $deleteconsumers->bindParam(':id',$id,PDO::PARAM_STR);
        try {
          $deleteconsumers->execute();
          $success= "Consumer Deleted";
          header("refresh:1; url=terminated.php");
} catch (PDOException $ex) {
        }  catch (PDOException $ex) {
          echo $ex->getMessage();
          exit;
        }
      } 

}?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brgy.Panadtaran Water Works</title>
    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <link href='../boxicons/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">

  <!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<!-- Then load Bootstrap and its dependencies -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>

<!-- DataTables JS should be loaded after jQuery -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script src="js/swal.js"></script>
</head>
<body>
    	<!-- SIDEBAR -->
	<?php include('includes/sidebar.php');?>
	<!-- SIDEBAR -->

	<!-- CONTENT -->
	<section id="content">
		<!-- NAVBAR -->
		<?php include('includes/header.php');?>
		<!-- NAVBAR -->
		<!-- MAIN -->
        <main>
            <div class="container mt-3 rounded  shadow-sm bg-white p-2">
            <div class="table-responsive p-2">
        <!-- Select Sitio Centered -->
        <!-- <div class="col-sm-4 d-flex justify-content-center">
        <label for="sitio" class="col-form-label pr-2">Sitio:</label>
            <select id="sitio" name="sitio" class="form-select form-select-sm" required="required" >
                <?php echo $sitioname; ?>
            </select>
        </div> -->
    </div>

             

                <table id="myTable" class="table table-hover table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Meter#</th>
                            <th>Pending Balance</th>
                            <th>FullName</th>
                            <th>Sitio</th>
                            <th>Contact Number</th>
                            <th>Disconnection Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                      <?php 
              function bill($dbh) {
                $sql = "SELECT consumers.*, 
                CONCAT(firstname, ' ', middlename, ' ', lastname) AS fullname, 
                sitio.sitio_name, terminate.disconnect as disconnect,bill.consumption,bill.total_amount,bill.id as bill_id, SUM(bill.payment) as payment
                FROM consumers 
                LEFT JOIN sitio ON consumers.sitio = sitio.id
                LEFT JOIN bill ON bill.serial_number = consumers.serial_no 
                LEFT JOIN terminate ON terminate.serial_no = consumers.serial_no 
                WHERE consumers.warning = 'terminated' 
                GROUP BY consumers.serial_no";

                $employee = $dbh->prepare($sql);
            
                $employee->execute();
                $results = $employee->fetchAll(PDO::FETCH_OBJ);
            
                foreach ($results as &$result) {
                }
                return $results;
                
            }
            $results = bill($dbh);
            foreach ($results as $list) {
                if (isset($list->disconnect)) {
                    $disconnect = new DateTime($list->disconnect);
                    $disconect_date = $disconnect->format('F j, Y');
                }
                    ?>
                        <tr>
                        
                            <td><?php echo htmlentities($list->serial_no); ?></td>
                            <td>₱<?php echo htmlentities($list->total_amount); ?></td>
                            <td><?php echo htmlentities($list->fullname) ?></td>
                            <td><?php echo $list->sitio_name?></td>
                            <td><?php echo htmlentities($list->mobile_number) ?></td>
                            <td><?php echo htmlentities($disconect_date) ?></td>
                            <td><button type="button" class="btn text-success fs-4 border-0 p-1 m-0"  title="Continue Disconnection" data-bs-toggle="modal" data-bs-target="#connectModal-<?php echo $list->serial_no ?>"><i class='bx bx-shuffle'></i></button>
                        <button type="button" class="btn text-danger fs-4 border-0 p-1 m-0"  title="Cancel Disconnection" data-bs-toggle="modal" data-bs-target="#disconnectModal-<?php echo $list->serial_no ?>"> <i class='bx bx-trash'></i></button></td>
                        </tr>

<!-- paid Modal -->
<div class="modal fade" id="connectModal-<?php echo $list->serial_no?>" tabindex="-1"  aria-labelledby="paidModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </button>
                </div>
                <div class="modal-body">
                <div class="text-center">
                  <form action="<?=$_SERVER['PHP_SELF']?>" method="POST"> 
                <input type="hidden" name="id" value="<?php echo $list->serial_no ?>">
                    <img src="img/employeex.svg" alt="Profile Picture" width="150px" height="150px">
                    <h3 class="confirm">Connect Consumer <?php echo $list->serial_no ?> again?</h3>
                  </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit"  name="connect" class="btn btn-primary">Confirm</button>
                </div>
                </form>
            </div>
    
        </div>
    </div>

<!-- delete Modal -->

    <div class="modal fade" id="disconnectModal-<?php echo $list->serial_no ?>" tabindex="-1"  aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </button>
                </div>
                <div class="modal-body">
                <div class="text-center">
                  <form action="<?=$_SERVER['PHP_SELF']?>" method="POST"> 
                <input type="hidden" name="id" value="<?php echo $list->serial_no ?>">
                    <img src="img/employeex.svg" alt="Profile Picture" width="150px" height="150px">
                    <h3 class="confirm">Are you sure you want to delete this consumer? </h3>
                  </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit"  name="delete" class="btn btn-danger" >Confirm</button>
                </div>
                </form>
            </div>
    
        </div>
    </div>
                        <?php 
                      } ?>
                        <!-- Add more rows as needed -->
                    </tbody>
                </table>
              
            </div>
            </div>
          
        </main>
        <?php include('includes/footer.php');?>
     </section>
    <script> 
$(document).ready(function() {
      $('#myTable').DataTable();
      var table = $('#myTable').DataTable();
$('#sitio').on('change', function() {
    var selectedSitio = $('#sitio').val(); 
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            var sitioInTable = data[5]; 
            if (selectedSitio === "" || sitioInTable.includes(selectedSitio)) {
                return true;
            }
            return false;
        }
    );
    table.draw();
    $.fn.dataTable.ext.search.pop();
});
  });

  </script>
     <script src="script.js"> </script>
     </body>
</html>