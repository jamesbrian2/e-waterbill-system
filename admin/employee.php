<?php include ('includes/config.php'); 
if(strlen($_SESSION['user'])==0)
{	
header('location:index2.php');
}
else{


    $_SESSION['sidebarname'] = 'Employee';

    if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['employee'])){
$fname = $_POST['firstname'];
$lname= $_POST['lastname'];
$mname = $_POST['middlename'];
$mobile= $_POST['mobilenumber'];
$address= $_POST['address'];
$dob= $_POST['dob'];
$position= $_POST['position'];
$email= $_POST['email'];
$pass= $_POST['password'];


 if($_FILES['img']['error'] == UPLOAD_ERR_OK ){
  $filename = basename($_FILES['img']['name']);
  $uploadPath = 'img/' .$filename;

  $hashedpassword = password_hash($password,PASSWORD_BCRYPT);

  
  if (move_uploaded_file($_FILES['img']['tmp_name'], $uploadPath)) {
    try {
        $dbh->beginTransaction(); // Begin transaction

        // Insert into users table
        $query2 = $dbh->prepare("INSERT INTO users (usertype, username, password) VALUES (:usertype, :username, :password)");
        $query2->bindParam(':usertype', $position, PDO::PARAM_STR);
        $query2->bindParam(':username', $email, PDO::PARAM_STR);
        $query2->bindParam(':password', $hashedpassword, PDO::PARAM_STR);
        $query2->execute();

        // Get the last inserted ID from users table
        $user_id = $dbh->lastInsertId();

        // Insert into employee table
        $query = $dbh->prepare("INSERT INTO employee (user_id,position, firstname, lastname, middlename, mobile_number,dob, address, picture) VALUES (:user_id,:position,:fname, :lname, :mname, :mobile,:dob, :address, :picture)");
        $query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $query->bindParam(':position', $position, PDO::PARAM_STR);
        $query->bindParam(':fname', $fname, PDO::PARAM_STR);
        $query->bindParam(':lname', $lname, PDO::PARAM_STR);
        $query->bindParam(':mname', $mname, PDO::PARAM_STR);
        $query->bindParam(':mobile', $mobile, PDO::PARAM_STR);
        $query->bindParam(':address', $address, PDO::PARAM_STR);
        $query->bindParam(':dob', $dob, PDO::PARAM_STR);
        $query->bindParam(':picture', $filename, PDO::PARAM_STR);
        $query->execute();

        $dbh->commit(); // Commit transaction

   $success = "Employee Added" ;
    header("refresh:1; url=employee.php");
        // echo "<script type='text/javascript'>alert('Added Successfully'); window.location.href = 'employee.php';</script>";
    } catch (PDOException $ex) {
        $dbh->rollBack(); // Rollback transaction in case of error
        echo $ex->getMessage();
        exit;
    }
} else {
    echo "Could not move the uploaded file";
}
} else {
echo "File upload error";
}
    }


    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {

      $id = isset($_POST['id']) ? $_POST['id'] : null;
      $fname = $_POST['firstname'];
      $lname = $_POST['lastname'];
      $mname = $_POST['middlename'];
      $mobile = $_POST['mobilenumber'];
      $dob = $_POST['dob'];
      $address = $_POST['address'];
      $position = $_POST['position'];
      $email = $_POST['email'];
      $pass = $_POST['password'];
  
      function handleImageUpload($imageKey, $existingImage) {
          if (isset($_FILES[$imageKey]) && $_FILES[$imageKey]['error'] == UPLOAD_ERR_OK) {
              $filename = basename($_FILES[$imageKey]['name']);
              $uploadPath = 'img/' . $filename;
              if (move_uploaded_file($_FILES[$imageKey]['tmp_name'], $uploadPath)) {
                  return $filename;
              }
          }
          return $existingImage;  // If no new upload, return existing filename
      }
   
      $fetchQuery = $dbh->prepare("SELECT picture FROM employee WHERE user_id = :id");
      $fetchQuery->bindParam(':id', $id, PDO::PARAM_INT);
      $fetchQuery->execute();
      $currentData = $fetchQuery->fetch(PDO::FETCH_OBJ);
      if ($currentData) {
          // Handle uploads for each image
          $imgMain = handleImageUpload('img', $currentData->picture);
          try {
              // Update employee details
              $query = $dbh->prepare("UPDATE employee SET firstname = :fname, middlename = :mname, lastname = :lname, address = :address,dob=:dob, mobile_number = :mobile, position = :position, picture = :picture WHERE user_id = :id");
              $query->bindParam(':fname', $fname, PDO::PARAM_STR);
              $query->bindParam(':mname', $mname, PDO::PARAM_STR);
              $query->bindParam(':lname', $lname, PDO::PARAM_STR);
              $query->bindParam(':address', $address, PDO::PARAM_STR);
              $query->bindParam(':mobile', $mobile, PDO::PARAM_INT);
              $query->bindParam(':dob', $dob, PDO::PARAM_STR);
              $query->bindParam(':position', $position, PDO::PARAM_INT);
              $query->bindParam(':picture', $imgMain, PDO::PARAM_STR);
              $query->bindParam(':id', $id, PDO::PARAM_INT);
              $query->execute();
  
              // Update user credentials
              $hashedpassword = password_hash($pass, PASSWORD_BCRYPT);
              $query2 = $dbh->prepare("UPDATE users SET username = :username, password = :password, usertype = :position WHERE id = :id");
              $query2->bindParam(':username', $email, PDO::PARAM_STR);
              $query2->bindParam(':password', $hashedpassword, PDO::PARAM_STR);
              $query2->bindParam(':position', $position, PDO::PARAM_INT);
              $query2->bindParam(':id', $id, PDO::PARAM_INT);
              $query2->execute();
  
              // Check if both queries were successful
              if ($query && $query2) {
                 $success= "Employee Updated";
                  header("refresh:1; url=employee.php");
              } else {
                  echo "<script type='text/javascript'>alert('Error: Please Try Again Later'); </script>";
              }
          } catch (PDOException $ex) {
              echo "Error: " . $ex->getMessage();
              exit;
          }
      } else {
          echo "No record found for the given user ID.";
          exit;
      }
  }
  

        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])){

          $user_id=$_POST['id'];
           
          $delete="DELETE FROM employee WHERE user_id=:id";
          $deleteemployee=$dbh->prepare($delete);
          $deleteemployee->bindParam(':id',$user_id,PDO::PARAM_STR);
          $delete2="DELETE FROM users WHERE id=:id";
          $deleteemployee2=$dbh->prepare($delete2);
          $deleteemployee2->bindParam(':id',$user_id,PDO::PARAM_STR);

          try {
            $deleteemployee->execute();
            $deleteemployee2->execute();
            $success= "Employee Deleted";
            header("refresh:1; url=employee.php");
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
                            <th>ID</th>
                            <th>FullName</th>
                            <th>Email Address</th>
                            <th>Contact Number</th>
                            <th>Date Of Birth</th>
                            <th>Address</th>
                            <th>Registration Date</th>
                            <th>Position</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                      <?php 
                      $sql ="SELECT e.*, 
                      CONCAT(e.firstname, ' ', e.middlename, ' ', e.lastname) AS fullname, 
                      u.label AS position, 
                      u.id AS position_id, 
                      us.username AS username 
               FROM employee e 
               LEFT JOIN usertype u ON e.position = u.id 
               LEFT JOIN users us ON e.user_id = us.id";
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
                            <td><?php echo htmlentities($list->user_id); ?></td>
                            <td><?php echo htmlentities($list->fullname) ?></td>
                            <td><?php echo $list->username?></td>
                            <td><?php echo htmlentities($list->mobile_number) ?></td>
                            <td><?php echo  $dob_format ?></td>
                            <td><?php echo htmlentities($list->address) ?></td>
                            <td><?php echo  $registration_date ?></td>
                            <td><?php echo htmlentities($list->position) ?></td>
                            <td><button type="button" class="btn text-primary fs-4 border-0 m-0 p-1 updateModalBtn" title="Update" data-bs-toggle="modal" data-bs-target="#updateModal" data-employeeId="<?php echo $list->user_id ?>"><i class='bx bx-calendar-edit'></i></button>
                            <button type="button" class="btn text-danger fs-4 border-0 p-1 m-0" title="Delete"  data-bs-toggle="modal" data-bs-target="#deleteModal-<?php echo htmlentities($list->user_id);?>"  ><i class='bx bx-trash'></i></button>
                          </td>
                        </tr>

<!-- delete  Modal -->
<div class="modal fade" id="deleteModal-<?php echo htmlentities($list->user_id);?>" tabindex="-1"  aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </button>
                </div>
                <div class="modal-body">
                <div class="text-center">
                  <form action="<?=$_SERVER['PHP_SELF']?>" method="POST"> 
                <input type="hidden" name="id" value="<?php echo $list->user_id ?>">
                    <img src="img/employeex.svg" alt="Profile Picture" width="150px" height="150px">
                    <h3 class="confirm">Are you sure you want to delete this employee?</h3>
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
                  
<!-- Update Employee Modal -->
<div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header pt-2 mx-3">
        <h5 class="modal-title w-100 text-center" id="exampleModalLabel">Update Employee</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body px-4 mt-2">
        <form id="updateForm" action="<?=$_SERVER['PHP_SELF']?>" method="POST" enctype="multipart/form-data">
          <!-- Form fields will be populated here by JavaScript -->
        </form>
      </div>
    </div>
  </div>
</div>
              
<!-- update Modal -->

                        <?php 
                      } ?>
                        <!-- Add more rows as needed -->
                    </tbody>
                </table>

                
                	<!-- add Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
<div class="modal-dialog modal-custom">
    <div class="modal-content p-3">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Add New Employee</h1>
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
    <input type="text" name="address" id="address" class="form-control" placeholder="Address" aria-label="First name" autocomplete="given-name" required>
  </div>
        </div>

    
      <div class ="row pb-2">
      <div class="col">

    <input type="text" name="email" id="email" class="form-control" placeholder="Email Address" aria-label="First name" autocomplete="given-name" required>
  </div>
</div>
  <div class ="row pb-2">
  <div class="col">
    <input type="password" name="password" id="password" class="form-control" placeholder="Password" aria-label="First name" autocomplete="given-name" required>
  </div>
</div>
<div class ="row pb-2">
<div class="col">
        <select
                id="position"
                name="position"
                aria-label="Position"
                class="form-select form-select-sm"
                required="required"
              >
              <option selected>Position</option>
  <option value="2">Reader</option>
  <option value="1">Encoder</option>
              </select>
          </div>
</div>
<div class ="row pb-1">
<div class="col">
                                 <label for="img">Picture</label>
  									<input type="file" id="img" name="img" label="Picture" class="form-control form-control-sm rounded-0" required>
								</div>
                </div>
      <div class="modal-footer ">
      <button type="button" class="btn btn-secondary" id="cancelBtn" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="employee" class="btn btn-primary" id="confirmBtn">Confirm</button>
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
            $('#myTable2').DataTable();

            $(document).on("click", ".updateModalBtn", function() {
    var employeeId = $(this).attr("data-employeeId");

    $.ajax({
        url: 'crud.php',
        type: 'POST',
        data: {employeeId: employeeId },
        dataType:'json',
        success: function(response) {
      
            $("#updateForm").html(`
                <div class="row px-2 m-0 ">
                    <div class="d-flex justify-content-center">
                        <img src="img/${response.picture}" width="80px" height="80px" class="rounded-circle mx-auto d-block p-0 m-0 border" alt="Profile Picture">
                    </div>
                    <p class="text-center m-0 p-0">Profile</p>
                </div>
                <br>
                <div class="row pb-2">
                    <div class="col">
                        <input type="hidden" name="id" id="id" class="form-control" value="${response.user_id}" required>
                        <input type="text" name="firstname" id="name" class="form-control" placeholder="First Name" value="${response.firstname}" required>
                    </div>
                </div>
                <div class="row pb-2">
                    <div class="col">
                        <input type="text" name="middlename" id="MiddleName" class="form-control" placeholder="Middle Name" value="${response.middlename}" required>
                    </div>
                </div>
                <div class="row pb-2">
                    <div class="col">
                        <input type="text" name="lastname" id="LastName" class="form-control" placeholder="Last Name" value="${response.lastname}" required>
                    </div>
                </div>
                <div class="row pb-2">
                    <div class="col">
                        <input type="text" name="mobilenumber" id="mobilenumber" class="form-control" placeholder="Mobile Number" value="${response.mobile_number}" required>
                    </div>
                </div>
                <div class="row pb-2">
                    <div class="col">
                        <input type="date" name="dob" id="dob" class="form-control" placeholder="Date of Birth" value="${response.dob}" required>
                    </div>
                </div>
                <div class="row pb-2">
                    <div class="col">
                        <input type="text" name="address" id="address" class="form-control" placeholder="Address" value="${response.address}" required>
                    </div>
                </div>
                <div class="row pb-2">
                    <div class="col">
                        <input type="text" name="email" id="email" class="form-control" placeholder="Email Address" value="${response.username}" required>
                    </div>
                </div>
                <div class="row pb-2">
                    <div class="col">
                        <input type="password" name="password" id="password" class="form-control" placeholder="New Password">
                    </div>
                </div>
                <div class="row pb-2">
                    <div class="col">
                        <select id="position" name="position" class="form-select form-select-sm" required>
                        <option value="1" ${response.position == '1' ? 'selected' : ''}>Encoder</option>
    <option value="2" ${response.position == '2' ? 'selected' : ''}>Reader</option>
                        </select>
                    </div>
                </div>
                <div class="row pb-3">
                    <div class="col">
                        <label for="img">Picture</label>
                        <input type="file" id="img" name="img" class="form-control form-control-sm rounded-0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update" class="btn btn-primary">Update</button>
                </div>
            `);
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
