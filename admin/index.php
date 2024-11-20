
<?php
include 'includes/config.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Check if it's a login or sign-up request
  if (isset($_POST["login"])) {
    // Process login form data
    $username = $_POST["username"];
    $password = $_POST["password"];

    $sql = "SELECT  user.*, ut.label as usertypes FROM users as user LEFT JOIN usertype ut ON user.usertype = ut.id  WHERE user.username = :username";
    $query = $dbh->prepare($sql);
    $query->bindParam(':username', $username, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetch(PDO::FETCH_ASSOC);
    
    if ($results && ($password == $results['password'])) {
        // Login successful
        $_SESSION['login'] = $_POST['username'];
        $_SESSION['user'] = $results['id'];
        $_SESSION['usertype'] = $results['usertypes'];
    
        header("Location:dashboard.php");
        exit();
    } else {
        // Login failed
        $loginError = "Invalid login credentials";
    }
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
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <link rel="stylesheet" href="login.css" />
    <title>Login/SignUp</title>
  </head>
  <body>
    <div class="<?php echo $containerClass; ?>">
      <div class="forms-container">
        <div class="signin-signup">
          <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"  method="post" class="sign-in-form">
            <h2 class="title">Sign in</h2>
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

            <input type="submit" value="Login" name="login" class="btn solid" />
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
          <img src="img/login.svg" class="image" alt="sing-up" />
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

function validatePassword() {
    var password = document.getElementById("signin-password").value;
    var confirmPassword = document.getElementById("confirm-password").value;
    var confirmPasswordFields = document.getElementById("confirm-password");
    var confirmPasswordField = document.getElementById("confirm");

    if (password !== confirmPassword) {
        alert("Password does not match");
        confirmPasswordFields.value = "";
        confirmPasswordField.style.border = "2px solid red"; // Highlight the field
        confirmPasswordFields.focus(); // Put focus back on the field
        return false; // Prevent form submission
    }

    return true; // Allow form submission
}
document.getElementById('dob').addEventListener('focus', function (e) {
    e.target.type = 'date';
    e.target.removeEventListener('focus', arguments.callee);
});
    </script>
   
  </body>
</html>