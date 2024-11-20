<?php
include 'includes/config.php';

if (isset($_POST["login"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $sql = "SELECT id, username, password,status FROM users WHERE username = :username";
    $query = $dbh->prepare($sql);
    $query->bindParam(':username', $username, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetch(PDO::FETCH_ASSOC);

  
    if ($results && password_verify($password, $results['password'])) {
        $meter=$dbh->prepare("SELECT serial_no FROM consumers WHERE user_id = :id");
        $meter->bindParam(':id',$id,PDO::PARAM_INT);
        $meter->execute();
        $meterresult=$meter->fetch(PDO::FETCH_ASSOC);


        $_SESSION['meter_id'] = $results['serial_no']; 
        $_SESSION['login'] = $username; 
        $_SESSION['user'] = $results['id']; 
        header("Location: dashboard.php");
    } else {
        $loginError = "Invalid login credentials"; 
}
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script
      src="https://kit.fontawesome.com/64d58efce2.js"
      crossorigin="anonymous"
    ></script>
    <link rel="shortcut icon" href="img/favicon.ico">
    <link rel="stylesheet" href="../admin/login.css" />
    <script src="signin/js/swal.js"></script>
    <title>Login</title>
  </head>
  <body>
    <div class="container">
      <div class="forms-container">
        <div class="signin-signup">
          <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"  method="POST" class="sign-in-form">
            <h2 class="title">Welcome</h2>
            <br>
            <div class="input-field">
            <i class="fas fa-user"></i>
              <input type="text" name="username" placeholder="Username" required />
            </div>
            <div class="input-field">
              <i class="fas fa-lock"></i>
              <input type="password" id="login-password" name="password" placeholder="Password" required />
  <span class="toggle-password" onclick="togglePasswordVisibility('login-password')">
    <i id="login-password-eye-icon" class="fas fa-eye-slash"></i>
  </span>
</div>

            <?php if (isset($loginError)): ?>
    <p class="error"><?php echo $loginError; ?></p>
  <?php endif; ?>
            <button type="submit"  name="login" class="btn solid">Login</button>
            </form>

          <br>
          <br>
        </div>
      </div>
      <div class="panels-container">
        <div class="panel left-panel">
          <div class="content">
            <h3>Brgy. Panadtaran Water Works</h3>
          </div>
          <img src="../admin/img/login.svg" class="image" alt="sing-up" />
        </div>
      
      </div>
    </div>
    <script>
      function togglePasswordVisibility(inputId) {
  var passwordInput = document.getElementById(inputId);
  var eyeIcon = document.getElementById(inputId + "-eye-icon");

  if (passwordInput.type === "password") {
    passwordInput.type = "text";
    eyeIcon.classList.remove("fa-eye-slash");
    eyeIcon.classList.add("fa-eye");
  } else {
    passwordInput.type = "password";
    eyeIcon.classList.remove("fa-eye");
    eyeIcon.classList.add("fa-eye-slash");
  }
}



    </script>
 
  </body>
</html>