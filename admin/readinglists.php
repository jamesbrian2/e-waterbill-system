<?php
 include ('includes/config.php'); 
 include 'sitio.php';
 if(strlen($_SESSION['user'])==0)
 {	
 header('location:index2.php');
 }
 else{

 
    $currentMonthYear = date("Y-m");
    $_SESSION['sidebarname']= 'Encoder';
    $sitio=getsitio($dbh);
    $sitioname=getsitioname($dbh);
    $filterDate = isset($_POST['filterdate']) ? $_POST['filterdate'] :  date("Y-m") ;

    if($filterDate){
        bill($dbh, $filterDate);  
    }
   
 
    function sendSMS($phone, $total, $reading_calc) {
        $txtmessage = "Your water usage $reading_calc cubic meters having a $total pesos has been successfully paid.Visit us at http://192.168.1.106:8080/e-waterbill-system/index.php for more details";
        $phone = "+63" . substr($phone, 1, 10);
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
         $result = json_decode($response, true);
}



    if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['paid'])){
        $encoderid=$_SESSION['user'];
        $paids="paid";
        $id=$_POST['id'];
        $phone=$_POST['phone'];
        $total=$_POST['total'];
        $reading_calc=$_POST['consumption'];
        $paid="UPDATE bill set payment=:paid,encoder_id=:encoder WHERE id=:id";
        $paidconsumers=$dbh->prepare($paid);
        $paidconsumers->bindParam(':id',$id,PDO::PARAM_INT);
        $paidconsumers->bindParam(':encoder',$encoderid,PDO::PARAM_INT);
        $paidconsumers->bindParam(':paid',$paids,PDO::PARAM_STR);

        $paid1="INSERT INTO payments (bill_id) VALUES(:bill_id)";
        $paidconsumers1=$dbh->prepare($paid1);
        $paidconsumers1->bindParam(':bill_id',$id,PDO::PARAM_INT);

        try {
            $paidconsumers1->execute();
          $paidconsumers->execute();
        sendSMS($phone,$total,$reading_calc);
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
            <form action="<?=$_SERVER['PHP_SELF']?>" method="POST" id="filterForm">
    <div class="row d-flex justify-content-between mb-2">
        <!-- Date Input on the Left -->
        <div class="col-sm-4 d-flex align-items-center">
            <label for="filterdate" class="col-form-label pr-2">Date:</label>
            <input type="month" name="filterdate" id="filterdate" class="form-control" value="<?php echo $filterDate ?>" onchange="document.getElementById('filterForm').submit();">
        </div>
        </form>
        <!-- Select Sitio Centered -->
        <div class="col-sm-4 d-flex justify-content-center">
        <label for="sitio" class="col-form-label pr-2">Sitio:</label>
            <select id="sitio" name="sitio" class="form-select form-select-sm" required="required" >
                <?php echo $sitioname; ?>
            </select>
        </div>
    </div>

             

                <table id="myTable" class="table table-hover table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Action</th> 
                            <th>Meter#</th>
                            <th>Consumption</th>
                            <th>Total Amount</th>
                            <th>Reading Date</th>
                            <th>Due Date</th>
                            <th>FullName</th>
                            <th>Sitio</th>
                            <th>Contact Number</th>
                        </tr>
                    </thead>
                    <tbody>
                      <?php 
              function bill($dbh, $filterDate) {
                $currentDate = $filterDate;
            
                $sql = "SELECT consumers.*, 
                CONCAT(firstname, ' ', middlename, ' ', lastname) AS fullname, 
                sitio.sitio_name ,bill.consumption,bill.total_amount,bill.id as bill_id,bill.payment as payment,bill.reading_date as reading,bill.due_date as due
         FROM consumers 
         LEFT JOIN sitio ON consumers.sitio = sitio.id
         LEFT JOIN bill ON bill.serial_number = consumers.serial_no 
                         AND DATE_FORMAT(bill.reading_date, '%Y-%m') = :currentDate
         WHERE (bill.status = 'billed' 
                OR DATE_FORMAT(bill.reading_date, '%Y-%m') != :currentDate)
           AND DATE_FORMAT(consumers.registration_date, '%Y-%m') <= :currentDate 
         
         GROUP BY consumers.serial_no";
            
                
                $employee = $dbh->prepare($sql);
                $employee->bindParam(':currentDate', $currentDate, PDO::PARAM_STR);
            
                $employee->execute();
                $results = $employee->fetchAll(PDO::FETCH_OBJ);
            
                foreach ($results as &$result) {
                    $result->current_date = $currentDate;
                }
                return $results;
                
            }
            $results = bill($dbh, $filterDate);

            $reading_format="";
            $due_format="";
                   foreach($results as $list){ 
                    if($list->reading){
                    $reading = new DateTime($list->reading);
                    $due = new DateTime($list->due);
                        $reading_format = $reading->format('F j, Y');
                        $due_format = $due->format('F j, Y');
                    }
                    $currentMonthYear = $list->current_date;
                    ?>
                        <tr>
                        <td>
                        <?php if($list->payment == 'pending') { ?>
    <button type="button" class="btn text-primary fs-4 border-0 m-0 p-1 w-100 h-100" title="Paid" data-bs-toggle="modal" data-bs-target="#paideModal-<?php echo $list->id; ?>">
        <i class='bx bx-receipt'></i>
    </button>
<?php } else { ?>
    <button type="button" class="btn text-success fs-4 border-0 m-0 p-1 w-100 h-100">
        <i class='bx bx-check'></i>
    </button>
<?php } ?>
                          </td>
                            <td><?php echo htmlentities($list->serial_no); ?></td>
                            <td><?php echo htmlentities($list->consumption); ?>m³</td>
                            <td>₱<?php echo htmlentities($list->total_amount); ?></td>
                            <td><?php echo htmlentities($reading_format); ?></td>
                            <td><?php echo htmlentities($due_format); ?></td>
                            <td><?php echo htmlentities($list->fullname) ?></td>
                            <td><?php echo $list->sitio_name?></td>
                            <td><?php echo htmlentities($list->mobile_number) ?></td>
                        </tr>

      
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
                <input type="hidden" name="phone" value="<?php echo $list->mobile_number ?>">
                <input type="hidden" name="total" value="<?php echo $list->total_amount ?>">
                <input type="hidden" name="consumption" value="<?php echo $list->consumption ?>">
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