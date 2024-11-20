<?php
include('includes/config.php'); 
include 'sitio.php';
if(strlen($_SESSION['user']) == 0) {	
    header('location:index2.php');
} else {



    function sendSMS($phone, $total, $reading_calc) {

        $txtmessage = "Your water usage is $reading_calc cubic meters. Please have $total pesos ready for payment.Visit us at http://192.168.1.106:8080/e-waterbill-system/index.php for more detail";
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


    // Rest of your code
    $currentMonthYear = date("Y-m");
    $_SESSION['sidebarname'] = 'Reading';
    $sitio = getsitio($dbh);
    $sitioname = getsitioname($dbh);
    $filterDate = isset($_POST['filterdate']) ? $_POST['filterdate'] : date("Y-m");
    
    if ($filterDate) {
        bill($dbh, $filterDate);  
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
        $reader = $_SESSION['user'];
        $serial = $_POST['serial'];
        $reading_date = $_POST['reading_date'];
        $new = new DateTime($reading_date);
        $new->modify('+21 days');
        $due = $new->format('Y-m-d');
        $reading = $_POST['reading'];
        $previousreading = floatval($_POST['previous']); 
        
        $reading_calc = ($reading - $previousreading);
        $total = $reading_calc >= 21 ? 16 * $reading_calc : ($reading_calc >= 10 && $reading_calc <= 20 ? 15 * $reading_calc : 150);

        $sql1 = $dbh->prepare("SELECT mobile_number FROM consumers WHERE serial_no = :serial");
        $sql1->bindParam(':serial', $serial, PDO::PARAM_INT);
        $sql1->execute();
        $result = $sql1->fetch(PDO::FETCH_OBJ);
        $phone = $result->mobile_number;
        $txtmessage = "Your Reading is $total ";
        sendSMS($phone,$total,$reading_calc);

        $status = "billed";

        try {
            $query = $dbh->prepare("INSERT INTO bill SET reader_id = :reader_id, serial_number = :serial_number, reading_date= :reading_date, due_date = :due_date, reading = :reading, consumption = :cons, total_amount = :total_amount, status = :status");
            $query->bindParam(':reader_id', $reader, PDO::PARAM_INT);
            $query->bindParam(':serial_number', $serial, PDO::PARAM_INT);
            $query->bindParam(':reading_date', $reading_date, PDO::PARAM_STR);
            $query->bindParam(':due_date', $due, PDO::PARAM_STR);
            // $query->bindParam(':previouslabel', $previousreading, PDO::PARAM_STR);
            $query->bindParam(':reading', $reading, PDO::PARAM_INT);
            $query->bindParam(':cons', $reading_calc, PDO::PARAM_INT);
            $query->bindParam(':total_amount', $total, PDO::PARAM_INT);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->execute();

            if ($query) {
                $success = "Success";
                header("refresh:1; url=reading.php");
            } else {
                $error = "Error";
                header("refresh:1; url=reading.php");
            }
        } catch (PDOException $ex) {
            echo "Error: " . $ex->getMessage();
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
                            <th>Meter-ID</th>
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
                sitio.sitio_name 
         FROM consumers 
         LEFT JOIN sitio ON consumers.sitio = sitio.id
         LEFT JOIN bill ON bill.serial_number = consumers.serial_no 
                         AND DATE_FORMAT(bill.reading_date, '%Y-%m') = :currentDate
         WHERE (bill.serial_number IS NULL 
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

                   foreach($results as $list){ 
                    $currentMonthYear = $list->current_date;
                    ?>
                        <tr>
                        <td><button type="button" class="btn text-primary fs-4 border-0 m-0 p-1 w-100 h-100" id="updateModalBtn" title="Update" data-bs-toggle="modal" data-bs-target="#updateModal" data-serial="<?php echo $list->serial_no ?>" data-consumer="<?php echo $list->id ?>"><i class='bx bx-receipt'></i></button>
                          </td>
                            <td><?php echo htmlentities($list->serial_no); ?></td>
                            <td><?php echo htmlentities($list->fullname) ?></td>
                            <td><?php echo $list->sitio_name?></td>
                            <td><?php echo htmlentities($list->mobile_number) ?></td>
                        </tr>

         
<!-- Update  Modal -->
<div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header pt-3 mx-2 ">
        <h5 class="modal-title w-100 text-center" id="exampleModalLabel">Issue Water Bill</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body px-3 m-0 ">
        <form id="updateForm" action="<?=$_SERVER['PHP_SELF']?>" method="POST" enctype="multipart/form-data">
          <!-- Form fields will be populated here by JavaScript -->
        </form>
      </div>
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
            var sitioInTable = data[3]; 
            if (selectedSitio === "" || sitioInTable.includes(selectedSitio)) {
                return true;
            }
            return false;
        }
    );
    table.draw();
    $.fn.dataTable.ext.search.pop();
});
    

$(document).on("click", "#updateModalBtn", function() {
    var consumer = $(this).attr("data-consumer");
    var serial = $(this).attr("data-serial");

    $.ajax({
        url: 'crud.php',
        type: 'POST',
        data: { consumer: consumer, serial: serial, date: new Date().toISOString().split('T')[0] }, // Ensure the date format matches what your PHP expects
        dataType: 'json',
        success: function(response) {
            $.ajax({
                url: 'crud.php', 
                type: 'POST',
                data: { sitio_id: response.sitio }, 
                success: function(sitioOptions) {
                    $("#updateForm").html(`
                        <div class="col-12 pb-1 form-floating">
                            <h5>Meter Number: ${response.serial_number}</h5>
                        </div>
                        <div class="row pb-2 p-0 m-0 g-1"> 
                            <div class="col-5 form-floating">
                                <input type="hidden" name="serial" id="serial" value="${response.serial_number}" required>
                                <input type="text" name="firstname" id="name" class="form-control" placeholder="First Name" value="${response.firstname}" required>
                                <label for="name">First Name</label>
                            </div>
                            <div class="col-2 form-floating">
                                <input type="text" name="middlename" id="MiddleName" class="form-control" placeholder="Middle Name" value="${response.middle_initial}" required>
                                <label for="MiddleName">M.I</label>
                            </div>
                            <div class="col-5 form-floating">
                                <input type="text" name="lastname" id="LastName" class="form-control" placeholder="Last Name" value="${response.lastname}" required>
                                <label for="LastName">Last Name</label>
                            </div>
                        </div>
                        <div class="row pb-2 p-0 m-0 g-1"> 
                        <div class="col-6 pb-2 form-floating">
                            <input type="text" name="mobilenumber" id="mobilenumber" class="form-control" placeholder="Mobile Number" value="${response.mobile_number}" required>
                            <label for="mobilenumber">Mobile Number</label>
                        </div>
                        <div class="col-6 pb-0 form-floating">
                                <select id="sitio" name="sitio" class="form-control" required="required" disabled>
                                    ${sitioOptions}
                                </select>
                                <label for="sitio">Sitio:</label>
                            </div>
                            </div>
                            <div class="col-sm-12 pb-2 form-floating">
<input type="text" id="previous_display" class="form-control" label="${response.previouslabel}" value="${response.previous != null ? response.previous : 0}">
    <input type="hidden" name="previous" id="previous" value="${response.previous}">
    <label for="previous_display">Previous Consumption:</label>
</div>
                       
                        <div class="col-sm-12 pb-2 form-floating">
                                <input type="date" name="reading_date" id="reading_date" class="form-control" value="${response.date}" required>
                                <label for="reading_date">Date:</label>
                            </div>
                            <div class="col-12 pb-2 form-floating">
                                <input type="number" name="reading" id="reading" class="form-control" placeholder="Reading" required>
                                <label for="reading">Consumption(m³)</label>
                            </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="update" class="btn btn-primary">Save</button>
                        </div>
                    `);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log("AJAX error: " + textStatus + ' : ' + errorThrown);
                }
            });
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log("AJAX error: " + textStatus + ' : ' + errorThrown);
        }
    });
});

  });

  document.getElementById('updateForm').addEventListener('submit', function(event) {
    // Get the values of the current reading and the previous reading
    var reading = parseFloat(document.getElementById('reading').value);
    var previous = parseFloat(document.getElementById('previous').value);

    // Check if the current reading is less than or equal to the previous reading
    if (reading <= previous) {
        // Show an alert and prevent the form from submitting
        alert('Error: Current reading is less than or equal to the previous reading.');
        event.preventDefault();  // This stops the form submission
    }
});

  </script>
     <script src="script.js"> </script>
     </body>
</html>