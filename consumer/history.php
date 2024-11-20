<?php
 include ('includes/config.php'); 
 if(strlen($_SESSION['user'])==0)
 {	
 header('location:index.php');
 }
 else{
    $currentMonthYear = date("Y-m");
    $_SESSION['sidebarname']= 'History';
    if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['paid'])){
        $encoderid=$_SESSION['user'];
        $paids="paid";
        $id=$_POST['id'];
        $paid="UPDATE bill set payment=:paid,encoder_id=:encoder WHERE id=:id";
        $paidconsumers=$dbh->prepare($paid);
        $paidconsumers->bindParam(':id',$id,PDO::PARAM_INT);
        $paidconsumers->bindParam(':encoder',$encoderid,PDO::PARAM_INT);
        $paidconsumers->bindParam(':paid',$paids,PDO::PARAM_STR);

        try {
          $paidconsumers->execute();
          $success= "Consumer Paid";
          header("refresh:1; url=readinglists.php");
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
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
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
    <div class="d-flex justify-content-between mb-3 px-3">
        <h5>List</h5>
        <form action="download_bill_history.php" method="POST" id="filterForm">
            <input type="hidden" name="id" id="id" value="<?php echo $_SESSION['user']; ?>"> 
            <button type="submit" class="border-0 bg-primary rounded px-3 py-1" title="download" name="print" id="print"><i class="bx bx-download fs-5 pt-1 text-white"></i></button>
        </form>
</div>
                <table id="myTable" class="table table-hover table-striped table-bordered">
                    <thead>
                        <tr>
                            <!-- <th>Action</th>  -->
                            <th>Consumption</th>
                            <th>Total Amount</th>
                            <th>Reading Date</th>
                            <th>Due Date</th>
                        </tr>
                    </thead>
                    <tbody>
                      <?php 
             
                $id=$_SESSION['user'];
                $sql = "SELECT * from bill LEFT JOIN  consumers ON  bill.serial_number=consumers.serial_no
                WHERE consumers.user_id =  $id
                ORDER BY bill.reading_date";
                $employee = $dbh->prepare($sql);
                $employee->execute();
                $results = $employee->fetchAll(PDO::FETCH_OBJ);

            $reading_format="";
            $due_format="";
                   foreach($results as $list){ 
                    if($list->reading_date){
                    $reading = new DateTime($list->reading_date);
                        $reading_format = $reading->format('F j, Y');

                        $reading2 = new DateTime($list->due_date);
                        $due_format = $reading2->format('F j, Y');
                    }
                    ?>
                        <tr>
                        <!-- <td>
                        <?php if($list->payment == 'pending') { ?>
    <button type="button" class="btn text-primary fs-4 border-0 m-0 p-1 w-100 h-100" title="Paid" data-bs-toggle="modal" data-bs-target="#paideModal-<?php echo $list->id; ?>">
        <i class='bx bx-receipt'></i>
    </button>
<?php } else { ?>
    <button type="button" class="btn text-success fs-4 border-0 m-0 p-1 w-100 h-100">
        <i class='bx bx-check'></i>
    </button>
<?php } ?>
                          </td> -->
                            <td><?php echo htmlentities($list->consumption); ?>m³</td>
                            <td>₱<?php echo htmlentities($list->total_amount); ?></td>
                            <td><?php echo htmlentities($reading_format); ?></td>
                            <td><?php echo htmlentities($due_format); ?></td>
                        </tr>

                        <!-- update pig Modal -->
<!-- paid Modal -->
<div class="modal fade" id="paideModal-<?php echo $list->id ?>" tabindex="-1"  aria-labelledby="paidModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </button>
                </div>
                <div class="modal-body">
                <div class="text-center">
                  <form action="<?=$_SERVER['PHP_SELF']?>" method="POST"> 
                <input type="hidden" name="id" value="<?php echo $list->bill_id ?>">
                    <img src="img/employeex.svg" alt="Profile Picture" width="150px" height="150px">
                    <h3 class="confirm">Confirm payment of consumer <?php echo $list->serial_no ?> </h3>
                  </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit"  name="paid" class="btn btn-primary">Confirm</button>
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

  });

  </script>
     <script src="script.js"> </script>
     </body>
</html>