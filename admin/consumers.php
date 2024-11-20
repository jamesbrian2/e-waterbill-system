<?php include ('includes/config.php');
 include 'sitio.php';
if(strlen($_SESSION['user'])==0)
{	
header('location:index2.php');
}
else{

  $meter = $dbh->prepare("SELECT MAX(serial_no)  AS meter_no FROM consumers");
  $meter ->execute();
  $meter_result = $meter->fetch(PDO::FETCH_OBJ);
  $meter_no = $meter_result->meter_no ?? 0;

  $meter = $meter_no + 1;

    $sitio = getsitio($dbh);

    $_SESSION['sidebarname'] = 'Consumer';


    function meter($dbh, $serial_no) {
      $query = "SELECT serial_no FROM consumers WHERE serial_no = :serial_no";
      $query2 = $dbh->prepare($query);
      $query2->bindParam(':serial_no', $serial_no, PDO::PARAM_STR);
      $query2->execute();
      $result = $query2->fetch(PDO::FETCH_OBJ);
      return $result ? true : false;
  }

  function meterupdate($id, $serial_no) {
    global $dbh; 
    $query = "SELECT id , serial_no FROM consumers WHERE serial_no = :serial_no";
    $query2 = $dbh->prepare($query);
    $query2->bindParam(':serial_no', $serial_no, PDO::PARAM_STR);
    $query2->execute();
    $result = $query2->fetchAll(PDO::FETCH_OBJ);
if($result){
    foreach($result as $item){
      if($item->id == $id){
        return $result = false;
      }
      else{
        return $result = true;
      }
    }
  }
  else{
    return $result = false;
  }
   
}

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['consumer'])) {

      $fname = $_POST['firstname'];
      $lname = $_POST['lastname'];
      $mname = $_POST['middlename'];
      $mobile = $_POST['mobilenumber'];
      $dob = $_POST['dob'];
      $serial_no =  $meter ;
      $sitio = $_POST['sitio'];

      $verifymeter = meter($dbh, $serial_no);
  
      try {
          if (!$verifymeter) {
              // Insert into consumers table
              $query = $dbh->prepare("INSERT INTO consumers (firstname, lastname, middlename, mobile_number, dob, sitio, serial_no) 
                                      VALUES (:fname, :lname, :mname, :mobile, :dob, :sitio, :serial_no)");
              $query->bindParam(':fname', $fname, PDO::PARAM_STR);
              $query->bindParam(':lname', $lname, PDO::PARAM_STR);
              $query->bindParam(':mname', $mname, PDO::PARAM_STR);
              $query->bindParam(':mobile', $mobile, PDO::PARAM_STR);
              $query->bindParam(':sitio', $sitio, PDO::PARAM_INT);
              $query->bindParam(':dob', $dob, PDO::PARAM_STR);
              $query->bindParam(':serial_no', $serial_no, PDO::PARAM_STR);
              $query->execute();
  
              $success = "Consumer Added";
              header("refresh:1; url=consumers.php");
          } else {
              $error = "Meter Number Already Exists";
              header("refresh:1; url=consumers.php");
          }
      } catch (PDOException $ex) {
          echo $ex->getMessage();
          exit;
      }
  }







    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
      $id = isset($_POST['id']) ? $_POST['id'] : null;
      $fname = $_POST['firstname'];
      $lname = $_POST['lastname'];
      $mname = $_POST['middlename'];
      $mobile = $_POST['mobilenumber'];
      $sitio = $_POST['sitio'];
      $dob = $_POST['dob'];
      $serial_no = $_POST['serial_number'];
              // Update consumers details
              $query = $dbh->prepare("UPDATE consumers SET firstname = :fname, middlename = :mname, lastname = :lname, sitio = :sitio, mobile_number = :mobile,dob = :dob, serial_no = :serial_no WHERE id = :id");
              $query->bindParam(':fname', $fname, PDO::PARAM_STR);
              $query->bindParam(':mname', $mname, PDO::PARAM_STR);
              $query->bindParam(':lname', $lname, PDO::PARAM_STR);
              $query->bindParam(':sitio', $sitio, PDO::PARAM_STR);
              $query->bindParam(':mobile', $mobile, PDO::PARAM_STR);
              $query->bindParam(':dob', $dob, PDO::PARAM_STR);
              $query->bindParam(':serial_no', $serial_no, PDO::PARAM_STR);
              $query->bindParam(':id', $id, PDO::PARAM_INT);
              $query->execute();
              if($query){
                $success= "Consumer Updated";
                  header("refresh:1; url=consumers.php");
              }else{
                  $error= "Error";
                  header("refresh:1; url=consumers.php");
                 }
      }



        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])){
          $id=$_POST['id'];
          $delete="DELETE FROM consumers WHERE id=:id";
          $deleteconsumers=$dbh->prepare($delete);
          $deleteconsumers->bindParam(':id',$id,PDO::PARAM_STR);


          try {
            $deleteconsumers->execute();
            $success= "Consumer Deleted";
            header("refresh:1; url=consumers.php");
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
<style>
    
        .modal-header,
 .modal-footer {
  border: none !important;
}
.modal-content .modal-body {
	padding:0px 50px 0px 50px;
    border: none; /* This will remove the border around the entire modal content */
}
</style>
<body>
    <!-- SIDEBAR -->
    <?php include('includes/sidebar.php'); ?>
    <!-- SIDEBAR -->

    <!-- CONTENT -->
    <section id="content">
        <!-- NAVBAR -->
        <?php include('includes/header.php'); ?>
        <!-- NAVBAR -->
        <!-- MAIN -->
        <main>
            <div class="container mt-3 rounded  shadow-sm bg-white p-2">
            <div class="table-responsive p-2">
                <div class="d-flex justify-content-between">
                <h4>List</h4>
                <button type="button" class="btn btn-primary rounded-4" data-bs-toggle="modal" data-bs-target="#confirmModal"
    class="openModalBtn"><i class='bx bx-user-plus'></i>Add</button>
                <h4></h4>
                </div>
                <table id="myTable" class="table table-hover table-striped table-bordered">
                    <thead>
                        <tr>
                        <th>Meter #</th>
                            <th>FullName</th>
                            <th>Contact Number</th>
                            <th>Date Of Birth</th>
                            <th>Sitio</th>
                            <th>Meter Number</th>
                            <th>Registration Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                      <?php 
                      $sql ="SELECT *, consumers.id AS id,
                      CONCAT(firstname, ' ',middlename, ' ', lastname) AS fullname, sitio.sitio_name AS sitio_name 
        FROM consumers 
        LEFT JOIN sitio ON consumers.sitio = sitio.id";
                      $employee=$dbh->prepare($sql);
                      $employee->execute();
                      $results= $employee->fetchAll(PDO::FETCH_OBJ);

                   foreach($results as $list){ 
                    $registration_date = "";
                    if (!empty($list->registration_date)) {
                        $registration = new DateTime($list->registration_date);
                        $registration_date = $registration->format('F j, Y');
                    }
                    $dob_format = "";
                    if (!empty($list->dob)) {
                        $dob = new DateTime($list->dob);
                        $dob_format = $dob->format('F j, Y');
                    }
                    ?>
                    
                        <tr>
                        <td><?php echo htmlentities($list->serial_no) ?></td>
                            <td><?php echo htmlentities($list->fullname) ?></td>
                            <td><?php echo htmlentities($list->mobile_number) ?></td>
                            <td><?php echo  $dob_format ?></td>
                            <td><?php echo htmlentities($list->sitio_name) ?></td>
                            <td><?php echo htmlentities($list->serial_no) ?></td>
                            <td><?php echo  $registration_date ?></td>
                            <td><button type="button" class="btn text-primary fs-4 border-0 m-0 p-1" id="updateModalBtn" title="Update" data-bs-toggle="modal" data-bs-target="#updateModal" data-consumer="<?php echo $list->id ?>"><i class='bx bx-calendar-edit'></i></button>
                            <button type="button" class="btn text-danger fs-4 border-0 p-1 m-0" title="Delete"  data-bs-toggle="modal" data-bs-target="#deleteModal-<?php echo htmlentities($list->id);?>"  ><i class='bx bx-trash'></i></button>
                          </td>
                        </tr>

<!-- delete Modal -->
<div class="modal fade" id="deleteModal-<?php echo htmlentities($list->id);?>" tabindex="-1"  aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </button>
                </div>
                <div class="modal-body">
                <div class="text-center">
                  <form action="<?=$_SERVER['PHP_SELF']?>" method="POST"> 
                <input type="hidden" name="id" value="<?php echo $list->id ?>">
                    <img src="img/employeex.svg" alt="Profile Picture" width="150px" height="150px">
                    <h3 class="confirm">Are you sure you want to delete this consumer? </h3>
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

<!-- Update  Modal -->
<div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header pt-2 mx-3">
        <h5 class="modal-title w-100 text-center" id="exampleModalLabel">Update Consumer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body px-4 m-2">
        <form id="updateForm" action="<?=$_SERVER['PHP_SELF']?>" method="POST">
        </form>
      </div>
    </div>
  </div>
</div>
              
<!-- update Modal -->

                        <?php 
                      } ?>
                    </tbody>
                </table>

                	<!-- add Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
<div class="modal-dialog modal-custom">
    <div class="modal-content p-3">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Add New Consumer</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0 mt-1">
         
      <form  action="<?=$_SERVER['PHP_SELF']?>" method="POST" enctype="multipart/form-data">
      <div class="row pb-2">
  <div class="col">
    <input type="text" name="firstname" id="name" class="form-control" placeholder="First Name" aria-label="First name" autocomplete="given-name" required>
  </div>
</div>
<div class="row pb-2">
        <div class="col">
    <input type="text" name="middlename" id="MiddleName" class="form-control" placeholder="Middle Name" aria-label="First name" autocomplete="given-name" required>
  </div>
        </div>

      <div class ="row pb-2">
      <div class="col">
    <input type="text" name="lastname" id="LastName" class="form-control" placeholder="Last Name" aria-label="First name" autocomplete="given-name" required>
  </div>
        </div>


      <div class ="row pb-2">
 
      <div class="col">

    <input type="text" name="mobilenumber" id="mobilenumber" class="form-control" placeholder="Mobile Number" aria-label="First name" autocomplete="given-name" required>
  </div>
        </div>

        <div class ="row pb-2">
      <div class="col">
      <input type="text" name="dob" id="dob" class="form-control" placeholder="Date of Birth" aria-label="Date of Birth" autocomplete="given-name" required>
  </div>
        </div>
      <div class ="row pb-2">
      <div class="col">
      <select
              id="sitio"
              name="sitio"
              class="form-select form-select-sm"
              required="required"
            >
              <?php echo $sitio; ?>
            </select>
  </div>
        </div>

    
      <div class ="row pb-2">
      <div class="col">

    <input type="text" name="serial_number" id="serial_number" class="form-control" placeholder="Meter Number" aria-label="First name" autocomplete="given-name" value="<?php echo $meter?>" disabled>
  </div>
</div>
<!-- <div class ="row pb-1">
<div class="col">
                                 <label for="img">Picture</label>
  									<input type="file" id="img" name="img" label="Picture" class="form-control form-control-sm rounded-0" required>
								</div>
                </div> -->
      <div class="modal-footer ">
      <button type="button" class="btn btn-secondary" id="cancelBtn" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="consumer" class="btn btn-primary" id="confirmBtn">Confirm</button>
      </div>
      </form>
                </div>
				</div>
        </div>
        </div>


<!-- add Modal -->


            </div>
            </div>
          
        </main>
        <?php include('includes/footer.php');?>
    </section>

    <!-- SCRIPTS -->
  
    <script>
      
        $(document).ready(function() {
            $('#myTable').DataTable();
            $(document).on("click", "#updateModalBtn", function() {
    var consumer = $(this).attr("data-consumer");

    $.ajax({
        url: 'crud.php',
        type: 'POST',
        data: { consumeraccount: consumer },
        dataType: 'json',
        success: function(response) {
            $.ajax({
                url: 'crud.php', 
                type: 'POST',
                data: { sitio_id: response.sitio }, 
                success: function(sitioOptions) {
                    $("#updateForm").html(`
                        <div class="row px-2 m-0 ">
                            <p class="text-center m-0 p-0">Profile</p>
                        </div>
                        <br>
                        <div class="col-12 pb-2 form-floating">
                            <input type="hidden" name="id" id="id" value="${response.id}" required>
                            <input type="text" name="firstname" id="name" class="form-control" placeholder="First Name" value="${response.firstname}" required>
                            <label for="name" class="mr-2">First Name</label>
                        </div>
                        <div class="col-12 pb-2 form-floating">
                            <input type="text" name="middlename" id="MiddleName" class="form-control" placeholder="Middle Name" value="${response.middlename}" required>
                            <label for="MiddleName">Middle Name</label>
                        </div>
                        <div class="col-12 pb-2 form-floating">
                            <input type="text" name="lastname" id="LastName" class="form-control" placeholder="Last Name" value="${response.lastname}" required>
                            <label for="LastName">Last Name</label>
                        </div>
                        <div class="col-12 pb-2 form-floating">
                            <input type="text" name="mobilenumber" id="mobilenumber" class="form-control" placeholder="Mobile Number" value="${response.mobile_number}" required>
                            <label for="mobilenumber">Mobile Number</label>
                        </div>
                        <div class="col-12 pb-2 form-floating">
                            <input type="date" name="dob" id="dob" class="form-control" placeholder="Date of Birth" value="${response.dob}" required>
                            <label for="dob">Date of Birth</label>
                        </div>
                        <div class="col-12 pb-2 form-floating">
                            <select id="sitio" name="sitio" class="form-control" required="required">
                                ${sitioOptions} 
                            </select>
                            <label for="sitio">Sitio</label>
                        </div>
                        <div class="col-12 pb-2 form-floating">
                            <input type="text" name="serial_number" id="serial_number" class="form-control" placeholder="Meter Number" value="${response.serial_number}" required>
                            <label for="serial_number">Meter Number</label>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="update" class="btn btn-primary">Update</button>
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
            console.log("Response text: " + jqXHR.responseText);
        }
    });
});

document.getElementById('dob').addEventListener('focus', function (e) {
    e.target.type = 'date';
    e.target.removeEventListener('focus', arguments.callee);
});

        });

    </script>
    <script src="script.js"></script>
</body>
</html>
