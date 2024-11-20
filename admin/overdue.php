<?php
include ('includes/config.php'); 
include 'sitio.php';
if (strlen($_SESSION['user']) == 0) {	
    header('location:index2.php');
} else {
    $currentMonthYear = date("Y-m");
    $_SESSION['sidebarname'] = 'Overdue and Warnings';
    $sitio = getsitio($dbh);

    function sendSMS($dueinfo) {
        $due = $dueinfo['due'];
        $team = $dueinfo['team'];
        $mobile_number = $dueinfo['mobile_number'];
        $fullname = $dueinfo['fullname'];

        $txtmessage = "Dear $fullname, your water bill is due. Please settle your payment by $due to avoid disconnection. Disconnection is scheduled for $team. For questions, please contact us. Thank you!";
        
        $phone = "+63" . substr($mobile_number, 1, 10);
        $message = [
            "secret" => "ebc469d67a471a72e0c07704f375a761ad2b7217", 
            "mode" => "devices",
            "device" => "00000000-0000-0000-30ac-62a95f7eb748",
            "sim" => 1,
            "priority" => 1,
            "phone" => $phone,
            "message" => $txtmessage
        ];

        $cURL = curl_init("https://sms.teamssprogram.com/api/send/sms");
        curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cURL, CURLOPT_POSTFIELDS, $message);
        $response = curl_exec($cURL);
        curl_close($cURL);
        
        return json_decode($response, true);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['due'])) {
        $due = $_POST['due'];
        $team = $_POST['team'];
        $serial = $_POST['serial'];

        try {
            $query = $dbh->prepare("INSERT INTO warnings (serial_no, due_date, disconnect) VALUES (:serial_no, :due_date, :disconnect)");
            $query->bindParam(':serial_no', $serial, PDO::PARAM_STR);
            $query->bindParam(':due_date', $due, PDO::PARAM_STR);
            $query->bindParam(':disconnect', $team, PDO::PARAM_STR);
            $query->execute();

            $query2 = $dbh->prepare("UPDATE consumers SET warning = 'sent' WHERE serial_no = :serial_no");
            $query2->bindParam(':serial_no', $serial, PDO::PARAM_STR);
            $query2->execute();

            $success = "Disconnection notice Saved";
            header("refresh:1; url=overdue.php");

            if ($query) {
                $sql = "SELECT mobile_number, CONCAT(firstname, '', middlename, '', lastname) AS fullname FROM consumers WHERE serial_no = :sn";
                $query = $dbh->prepare($sql);
                $query->bindParam(':sn', $serial, PDO::PARAM_STR);
                $query->execute();
                $results = $query->fetch(PDO::FETCH_OBJ);

                $dueinfo = [
                    'mobile_number' => $results->mobile_number,
                    'fullname' => $results->fullname,
                    'team' => $team,
                    'due' => $due
                ];

                sendSMS($dueinfo);
            }
        } catch (PDOException $ex) {
            echo $ex->getMessage();
            exit;
        }
    }


    if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel'])){
        $serial = $_POST['serial'];
        $delete="DELETE FROM warnings WHERE serial_no=:id";
        $deleteconsumers=$dbh->prepare($delete);
        $deleteconsumers->bindParam(':id',$serial,PDO::PARAM_STR);

        $cancel="UPDATE consumers SET warning='none' WHERE serial_no=:id";
        $cancel1=$dbh->prepare($cancel);
        $cancel1->bindParam(':id',$serial,PDO::PARAM_STR);

        try {
          $deleteconsumers->execute();
          $cancel1->execute();
          $success= "Disconnection Cancelled";
          header("refresh:1; url=overdue.php");
} catch (PDOException $ex) {
        }  catch (PDOException $ex) {
          echo $ex->getMessage();
          exit;
        }
      } 

      if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['continue'])){
        $serial = $_POST['serial'];
        $delete="DELETE FROM warnings WHERE serial_no=:id";
        $deleteconsumers=$dbh->prepare($delete);
        $deleteconsumers->bindParam(':id',$serial,PDO::PARAM_STR);

        $continue="INSERT INTO terminate (serial_no) VALUES (:id)";
        $continueconsumers=$dbh->prepare($continue);
        $continueconsumers->bindParam(':id',$serial,PDO::PARAM_STR);

        $cancel="UPDATE consumers SET warning='terminated' WHERE serial_no=:id";
        $cancel1=$dbh->prepare($cancel);
        $cancel1->bindParam(':id',$serial,PDO::PARAM_STR);

        try {
          $deleteconsumers->execute();
          $continueconsumers->execute();
          $cancel1->execute();
          $success= "Disconnection Successful";
          header("refresh:1; url=overdue.php");
} catch (PDOException $ex) {
        }  catch (PDOException $ex) {
          echo $ex->getMessage();
          exit;
        }
      } 
}
?>


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
<style>
  .custom-badge {
    left: 190px !important; /* Adjust the value to move the badge further right */
}
.custom-badges {
    left: 157px !important; /* Adjust the value to move the badge further right */
}
</style>
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
        <div class="accordion mt-4" id="accordionPanelsStayOpenExample">
  <div class="accordion-item">
    <h2 class="accordion-header">
    <?php 
$sql ="SELECT count(id) as due from bill WHERE due_date < CURRENT_DATE() ";
$query = $dbh -> prepare($sql);
$query->execute();
$results=$query->fetch(PDO::FETCH_OBJ);
$due=$results->due;
?>
      <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
        Overdue Consumers
        <span class="position-absolute top-1 start-50 translate-middle badge rounded-pill bg-danger custom-badge <?php echo ($due>0)? $due:"visually-hidden" ?>">
 <?php echo $due ?>
    <span class="visually-hidden">unread messages</span>
  </span>
      </button>
    </h2>

    <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show">
    <div class="container mt-3 rounded  shadow-sm bg-white p-2">
            <div class="table-responsive p-2">
            <form action="<?=$_SERVER['PHP_SELF']?>" method="POST" id="filterForm">
                <table id="myTable" class="table table-hover table-striped table-bordered">
                    <thead>
                        <tr>
                          
                            <th>Meter-ID</th>
                            <th>FullName</th>
                            <th>Sitio</th>
                            <th>Contact Number</th>
                            <th>Reading Date</th>
                            <th>Due Date</th>
                            <th>Total Balance</th>
                            <th>Action</th> 
                        </tr>
                    </thead>
                    <tbody>
                      <?php 
             
             $currentMonthYear = date("Y-m-d");
             $sql = "SELECT bill.*, 
                     CONCAT(consumers.firstname, ' ', consumers.middlename, ' ', consumers.lastname) AS fullname, 
                     sitio.sitio_name,consumers.mobile_number as mobile_number,bill.serial_number AS serial_number,
                     SUM(bill.total_amount) AS balance
                 FROM bill
                 LEFT JOIN consumers ON bill.serial_number = consumers.serial_no AND bill.payment = 'pending'
                 LEFT JOIN sitio ON consumers.sitio = sitio.id
                 WHERE consumers.warning = 'active' OR consumers.warning = 'pending'
                 GROUP BY bill.serial_number
                 HAVING balance > 2000 OR MAX(bill.due_date) < :currentDate 
             ";
            
                
                $employee = $dbh->prepare($sql);
                $employee->bindParam(':currentDate', $currentMonthYear, PDO::PARAM_STR);
                $employee->execute();
                $results = $employee->fetchAll(PDO::FETCH_OBJ);
            
                   foreach($results as $list){ 
                  
                    ?>
                        <tr>
                       
                            <td><?php echo htmlentities($list->serial_number); ?></td>
                            <td><?php echo htmlentities($list->fullname) ?></td>
                            <td><?php echo $list->sitio_name?></td>
                            <td><?php echo htmlentities($list->mobile_number ) ?></td>
                            <td><?php echo htmlentities($list->reading_date) ?></td>
                            <td><?php echo htmlentities($list->due_date ) ?></td>
                            <td class="<?php echo ($list->balance >= 2000)?'text-danger': '' ?>">₱<?php echo number_format($list-> balance,2) ?></td>
                            <td><button type="button" class="btn text-primary fs-4 border-0 m-0 p-1 w-100 h-100" id="updateModalBtn" title="Issue Notice" data-bs-toggle="modal" data-bs-target="#confirmModal" data-serial="<?php echo $list->serial_no ?>" data-consumer="<?php echo $list->id ?>"><i class='bx bx-receipt'></i></button>
                          </td>
                        </tr>

                     
                	<!-- add Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
<div class="modal-dialog modal-custom">
    <div class="modal-content p-3">
      <div class="modal-header">
      <?php 
$sql1 ="SELECT id from warnings";
$query1 = $dbh -> prepare($sql1);
$query1->execute();
$results1=$query1->fetchAll(PDO::FETCH_OBJ);
$warning=$query1->rowCount();
?>

        <h1 class="modal-title fs-5" id="exampleModalLabel">Issue Disconnection Notice</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
     
      </div>
      <div class="modal-body p-0 mt-1">
         
      <form  action="<?=$_SERVER['PHP_SELF']?>" method="POST">
                            <div class="col-sm-12 pb-2 form-floating">
    <input type="hidden" name="serial" id="serial" value="<?php echo $list->serial_number ?>">
</div>
<div class="col-sm-12 pb-2 form-floating">
                                <input type="int" id="meter" class="form-control" placeholder="Meter#" value="<?php echo $list->serial_number ?>" disabled>
                                <label for="meter">Meter Number </label>
                            </div>
                        <div class="col-sm-12 pb-2 form-floating">
                                <input type="date" name="due" id="due" class="form-control">
                                <label for="due">Due Date:</label>
                            </div>
                            <div class="col-sm-12 pb-2 form-floating">
                                <input type="date" name="team" id="team" class="form-control" >
                                <label for="team">Disconection Team Date:</label>
                            </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="issue" class="btn btn-primary">Save</button>
                        </div>
      </form>
                </div>
				</div>
        </div>
        </div>


<!-- add Modal -->

                        <?php 
                      } ?>
                        <!-- Add more rows as needed -->
                    </tbody>
                </table>
              
            </div>
            </div>

    </div>

  </div>
  <div class="accordion-item">
    <h2 class="accordion-header">
      <?php 
$sql1 ="SELECT count(id) as warning from warnings";
$query1 = $dbh -> prepare($sql1);
$query1->execute();
$results1=$query1->fetch(PDO::FETCH_OBJ);
$warning=$results1->warning;
?>
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseTwo" aria-expanded="false" aria-controls="panelsStayOpen-collapseTwo">
     Issued Warnings
     <span class="position-absolute top-1 start-50 translate-middle badge rounded-pill bg-danger custom-badges <?php echo ($warning>0)? $warning:"visually-hidden" ?>">
 <?php echo $warning ?>
    <span class="visually-hidden">unread messages</span>
  </span>
      </button>
    
    </h2>
    <div id="panelsStayOpen-collapseTwo" class="accordion-collapse collapse">
      <div class="accordion-body">
      <div class="container mt-3 rounded  shadow-sm bg-white p-2">
            <div class="table-responsive p-2">
            <form action="<?=$_SERVER['PHP_SELF']?>" method="POST" id="filterForm">
                <table id="myTable1" class="table table-hover table-striped table-bordered">
                    <thead>
                        <tr>
                          
                            <th>Meter-ID</th>
                            <th>FullName</th>
                            <th>Issued</th>
                            <th>Due</th>
                            <th>Disconnection</th>
                            <th>Sitio</th>
                            <th>Contact Number</th>
                            <th>Total Balance</th>
                            <th>Action #</th> 
                        </tr>
                    </thead>
                    <tbody>
                      <?php 
             
             $currentMonthYear = date("Y-m-d");
             $sql = "SELECT bill.*, 
                     CONCAT(consumers.firstname, ' ', consumers.middlename, ' ', consumers.lastname) AS fullname, 
                     sitio.sitio_name,consumers.mobile_number as mobile_number,bill.serial_number AS serial_number,
                     warnings.due_date AS due,warnings.disconnect AS team,warnings.date_issued AS issued,
                     SUM(bill.total_amount) AS balance
                 FROM bill
                 LEFT JOIN consumers ON bill.serial_number = consumers.serial_no AND bill.payment = 'pending'
                 LEFT JOIN sitio ON consumers.sitio = sitio.id
                 LEFT JOIN warnings ON warnings.serial_no = bill.serial_number
                 WHERE consumers.warning = 'sent'
                 GROUP BY bill.serial_number
             ";
            
                $employee = $dbh->prepare($sql);
                $employee->execute();
                $results = $employee->fetchAll(PDO::FETCH_OBJ);
                $due_format = "";
                $team_format ="";
                $issued_format ="";
                   foreach($results as $list){ 
                    if($list->due){
                        $due = new DateTime($list->due);
                            $due_format = $due->format('F j, Y');
                        }
                        if($list->team){
                            $team = new DateTime($list->team);
                                $team_format = $team->format('F j, Y');
                            }
                            if($list->issued){
                                $issued = new DateTime($list->issued);
                                    $issued_format = $issued->format('F j, Y');
                                }
                  
                    ?>
                        <tr>
                       
                            <td><?php echo htmlentities($list->serial_number); ?></td>
                            <td><?php echo htmlentities($list->fullname) ?></td>
                            <td><?php echo htmlentities($issued_format) ?></td>
                            <td><?php echo htmlentities($due_format) ?></td>
                            <td><?php echo htmlentities($team_format) ?></td>
                            <td><?php echo $list->sitio_name?></td>
                            <td><?php echo htmlentities($list->mobile_number ) ?></td>
                            <td class="<?php echo ($list->balance >= 2000)?'text-danger': '' ?>">₱<?php echo number_format($list-> balance,2) ?></td>
                            <td><button type="button" class="btn text-primary fs-4 border-0 p-1 m-0" id="connectModal" title="Cancel Disconnection" data-bs-toggle="modal" data-bs-target="#connectModal-<?php echo $list->serial_number ?>"> <i class='bx bx-receipt'></i></button><button type="button" class="btn text-danger fs-4 border-0 p-1 m-0" id="disconnectModal" title="Continue Disconnection" data-bs-toggle="modal" data-bs-target="#disconnectModal-<?php echo $list->serial_number ?>" ><i class='bx bx-cut'></i></button>
                        
                          </td>
                        </tr>

                     
                	<!-- disconnect Modal -->
<div class="modal fade" id="disconnectModal-<?php echo $list->serial_number ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
<div class="modal-dialog modal-custom">
    <div class="modal-content p-3">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Disconnection</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0 mt-1">
         
      <form  action="<?=$_SERVER['PHP_SELF']?>" method="POST">
                            <div class="col-sm-12 pb-2 form-floating">
    <input type="hidden" name="serial" id="serial" value="<?php echo $list->serial_number ?>">
</div>
<div class="text-center">
                    <img src="img/employeex.svg" alt="Profile Picture" width="150px" height="150px">
                    <h3 class="confirm">Are you sure you want to continue the Disconnection? </h3>
                  </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="continue" class="btn btn-danger">Disconnect</button>
                        </div>
      </form>
                </div>
				</div>
        </div>
        </div>
<!-- disconnect Modal -->

              	<!-- connect Modal -->
                  <div class="modal fade" id="connectModal-<?php echo $list->serial_number?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
<div class="modal-dialog modal-custom">
    <div class="modal-content p-3">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Cancel Disconnection</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0 mt-1">
         
      <form  action="<?=$_SERVER['PHP_SELF']?>" method="POST">
      <div class="col-sm-12 pb-2 form-floating">
    <input type="hidden" name="serial" id="serial" value="<?php echo $list->serial_number ?>">
</div>
      <div class="text-center">
                    <img src="img/employeex.svg" alt="Profile Picture" width="150px" height="150px">
                    <h3 class="confirm">Are you sure you want to cancel the Disconnection? </h3>
                  </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="cancel" class="btn btn-primary">Confirm</button>
                        </div>
      </form>
                </div>
				</div>
        </div>
        </div>


<!-- connect Modal -->
                        <?php 
                      } ?>
                        <!-- Add more rows as needed -->
                    </tbody>
                </table>
              
            </div>
            </div>
      </div>
    </div>
  </div>
</div>
          
        </main>
        <?php include('includes/footer.php');?>
     </section>
    <script> 
$(document).ready(function() {
      $('#myTable').DataTable();
      $('#myTable1').DataTable();
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


