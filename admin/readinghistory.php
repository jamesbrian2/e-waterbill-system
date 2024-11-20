<?php
 include ('includes/config.php'); 
 include 'sitio.php';
 if(strlen($_SESSION['user'])==0)
 {	
 header('location:index2.php');
 }
 else{
    $currentMonthYear = date("Y-m");
    $_SESSION['sidebarname']= 'Reading History';
    $sitio=getsitio($dbh);
    $sitioname=getsitioname($dbh);
    $filterDate = isset($_POST['filterdate']) ? $_POST['filterdate'] :  date("Y-m") ;
    // $sitio = isset($_POST['sitio']) ? $_POST['sitio'] : null;
    if($filterDate){
        bill($dbh, $filterDate);  
    }
   
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
      $reader = $_SESSION['user'];
      $serial = $_POST['serial'];
      $bill_id = $_POST['bill_id'];
      $reading_date = $_POST['reading_date'];
      $new = new DateTime($reading_date);
      $new->modify('+21 days');
      $due = $new->format('Y-m-d');
      $reading = $_POST['reading'];
      $previousreading = floatval($_POST['previous']); 
      $reading_calc = ($reading-$previousreading);
        if($reading_calc >= 21){
            $total = 16 * $reading_calc;
        }
        elseif($reading_calc >= 10 && $reading_calc <=20 ){
            $total = 15 * $reading_calc;
        }else{
            $total = 150;
        }

      $status = "billed";
    

          try {
            $query = $dbh->prepare("UPDATE bill 
            SET 
                serial_number = :serial_number, 
                reading_date = :reading_date, 
                due_date = :due_date, 
                reading = :reading, 
                consumption = :cons, 
                total_amount = :total_amount, 
                status = :status 
            WHERE id = :bill_id");
        
        $query->bindParam(':serial_number', $serial, PDO::PARAM_INT);
        $query->bindParam(':reading_date', $reading_date, PDO::PARAM_STR);
        $query->bindParam(':due_date', $due, PDO::PARAM_STR);
        $query->bindParam(':reading', $reading, PDO::PARAM_INT);
        $query->bindParam(':bill_id', $bill_id, PDO::PARAM_INT);
        $query->bindParam(':cons', $reading_calc, PDO::PARAM_INT);
        $query->bindParam(':total_amount', $total, PDO::PARAM_INT);
        $query->bindParam(':status', $status, PDO::PARAM_STR);
              $query->execute();
              if ($query) {
                 $success= "Success";
                  header("refresh:1; url=readinghistory.php");
              }
               else {
                 $error= "Error";
                  header("refresh:1; url=readinghistory.php");
              }
          } catch (PDOException $ex) {
              echo "Error: " . $ex->getMessage();
              exit;
          }
      }

      if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])){

        $id=$_POST['id'];
         
        $delete="DELETE FROM bill WHERE id=:id";
        $deletebill=$dbh->prepare($delete);
        $deletebill->bindParam(':id',$id,PDO::PARAM_STR);
        try {
          $deletebill->execute();
          $success= "Bill Deleted";
          header("refresh:1; url=readinghistory.php");
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
                        <th>Meter-ID</th>
                            <th>Reader</th> 
                            <th>Consumer</th>
                            <th>Total Reading</th>
                            <th>Consumption</th>
                            <th>Total Amount</th>
                            <th>Sitio</th>
                            <th>Contact Number</th> 
                             <th>Status</th>
                            <th>Action</th> 
                        </tr>
                    </thead>
                    <tbody>
                      <?php 
              function bill($dbh, $filterDate) {
                $currentDate = $filterDate;
            
                $sql = "SELECT consumers.*, consumers.id AS id,
                CONCAT(consumers.firstname, ' ', consumers.middlename, ' ', consumers.lastname) AS fullname,
                CONCAT(employee.firstname, ' ', employee.middlename, ' ', employee.lastname) AS readername, 
                sitio.sitio_name,
                upper(bill.status) AS status,
                bill.id as bill_id,
                bill.consumption as consumption,
                bill.reading as reading, 
                bill.total_amount as payment
         FROM consumers 
         LEFT JOIN sitio ON consumers.sitio = sitio.id
         LEFT JOIN bill ON bill.serial_number = consumers.serial_no 
         LEFT JOIN employee ON bill.reader_id = employee.user_id 
         WHERE DATE_FORMAT(bill.reading_date, '%Y-%m') = :currentDate
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
                        <td><?php echo htmlentities($list->serial_no); ?></td>
                          <td><?php echo htmlentities($list->readername) ?></td>
                          <td><?php echo htmlentities($list->fullname) ?></td>
                            <td><?php echo htmlentities($list->reading) ?>m³</td>
                            <th><?php echo htmlentities($list->consumption) ?>m³</th>
                            <th>₱<?php echo htmlentities($list->payment) ?></th>
                      
                            <td><?php echo $list->sitio_name?></td>
                            <td><?php echo htmlentities($list->mobile_number) ?></td>
                            <td>  <p class="<?php echo ($list->status == "BILLED") ? "text-success" : "text-warning"; ?>">
        <?php echo htmlentities($list->status); ?>
    </p></td>
                            <td><button type="button" class="btn text-primary fs-4 border-0 m-0 p-1 updateModalBtn" id="updateModalBtn" title="Update" data-bs-toggle="modal" data-bs-target="#updateModal" data-serial="<?php echo $list->id ?>" data-consumer="<?php echo $list->id ?>" data-bill="<?php echo $list->bill_id ?>"><i class='bx bx-calendar-edit'></i></button>
                            <button type="button" class="btn text-danger fs-4 border-0 p-1 m-0" title="Delete"  data-bs-toggle="modal" data-bs-target="#deleteModal-<?php echo htmlentities($list->bill_id);?>"  ><i class='bx bx-trash'></i></button>
                        </td>
                        </tr>

         
<!-- Update Employee Modal -->
<div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header pt-3 mx-2 ">
        <h5 class="modal-title w-100 text-center" id="exampleModalLabel">Edit Water Bill</h5>
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

<!-- delete  Modal -->
<div class="modal fade" id="deleteModal-<?php echo htmlentities($list->bill_id);?>" tabindex="-1"  aria-labelledby="cancelModalLabel" aria-hidden="true">
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
                    <h3 class="confirm">Are you sure you want to delete this bill record?</h3>
                  </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit"  name="delete" class="btn btn-danger" name="delete">Confirm</button>
                </div>
                </form>
            </div>
    
        </div>
    </div>

<!-- delete  Modal -->

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
    var bill = $(this).attr("data-bill");

    $.ajax({
        url: 'crud.php',
        type: 'POST',
        data: { bill:bill,consumers: consumer, serials: serial }, // Ensure the date format matches what your PHP expects
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
                                <input type="hidden" name="bill_id" id="bill_id" value="${response.bill_id}" required>
                                <input type="text" name="firstname" id="name" class="form-control" placeholder="First Name" value="${response.bill}" required>
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
    <input type="text" id="previous_display" class="form-control" value="${response.previouslabel}" disabled>
    <input type="hidden" name="previous" id="previous" value="${response.previous}">
    <label for="previous_display">Previous Consumption:</label>
</div>
                       
                        <div class="col-sm-12 pb-2 form-floating">
                                <input type="date" name="reading_date" id="reading_date" class="form-control" value="${response.reading_date}">
                                <label for="reading_date">Date:</label>
                            </div>
                            <div class="col-12 pb-2 form-floating">
                                <input type="number" name="reading" id="reading" class="form-control" placeholder="Reading" value="${response.consumption}">
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