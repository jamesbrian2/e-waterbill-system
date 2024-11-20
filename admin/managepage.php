<?php
include('includes/config.php'); 
if (strlen($_SESSION['user']) == 0) {	
    header('location:index2.php');
} else {
    $_SESSION['sidebarname'] = 'Manage Page';

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
        $email = $_POST['email'];
        $contact = $_POST['contact'];

        $query = $dbh->prepare("UPDATE contacts SET email = :email, contact = :contact  ");
        $query->bindParam(':contact', $contact, PDO::PARAM_STR);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->execute();

      if ($query) {
                 $success= "Contact Information Updated";
                  header("refresh:1; url=managepage.php");
              } else {
                  echo "<script type='text/javascript'>alert('Error: Please Try Again Later'); </script>";
              }
    }

    $sql = "SELECT * FROM contacts LIMIT 1"; 
    $query3 = $dbh->prepare($sql);
    $query3->execute();
    $contact = $query3->fetch(PDO::FETCH_OBJ);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brgy. Panadtaran Water Works</title>
    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <link href='../boxicons/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="js/swal.js"></script>
</head>
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
            <div class="container-fluid">
                <form action="<?=$_SERVER['PHP_SELF']?>" method="POST">
                    <div class="row rounded bg-white m-4 p-4">
                        <h4>Contact Details</h4>
                      
                        <div class="col-md-6 mb-3">
                            <label for="exampleFormControlInput1" class="form-label">Email address</label>
                            <input type="email" class="form-control" name="email" id="exampleFormControlInput1" placeholder="name@example.com" value="<?php echo $contact->email; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="exampleFormControlInput2" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" name="contact" id="exampleFormControlInput2" placeholder="Mobile Number" value="<?php echo $contact->contact; ?>">
                        </div>
                        <div class="col-md-12 mb-3">
                            <button type="submit" class="btn btn-primary w-100" name="update">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
        <?php include('includes/footer.php'); ?>
    </section>
</body>
<script src="script.js"> </script>
</html>
