
<?php
include 'admin/includes/config.php';
$mode = $_GET['mode'] ?? '';
// Check if the mode is set to 'signup' and add the 'sign-up-mode' class to the container accordingly
if ($mode === 'signup') {
    $containerClass = 'container ';
} else {
    $containerClass = '';
}
?>
<script>
function checkAvailability() {
$("#loaderIcon").show();
jQuery.ajax({
url: "check_availability.php",
data:'emailid='+$("#emailid").val(),
type: "POST",
success:function(data){
$("#user-availability-status").html(data);
$("#loaderIcon").hide();
},
error:function (){}
});
}
</script>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script
      src="https://kit.fontawesome.com/64d58efce2.js"
      crossorigin="anonymous"
    ></script>
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico">
    <link rel="stylesheet" href="style.css"/>
    
    <title>Login/SignUp</title>
  </head>
  <body>
    <div class="container sign-up-mode">
      <div class="forms-container">
        <div class="signin-signup">
        
          <form action="check.php" method="post" class="sign-up-form" onsubmit="return validatePassword();">
            <h2 class="title">Sign up</h2>
            <div class="input-field">
              <i class="fas fa-mobile"></i>
              <input type="text" name="meternumber" placeholder="Meter Number"required/>
            </div>
            <div class="input-field">
              <i class="fas fa-mobile"></i>
              <input type="text" name="mobilenumber" placeholder="Mobile Number"required/>
            </div>
            <div class="input-field">
              <i class="fas fa-envelope"></i>
              <input type="email" name="email" id="emailid" onBlur="checkAvailability()" placeholder="Email Address"required>
       
            </div>   
            <div class="input-field">
              <i class="fas fa-lock"></i>
              <input type="password" id="signin-password" name="password" placeholder="Password" required />
  <span class="toggle-password" onclick="togglePasswordVisibility('signin-password')">
    <i id="signin-password-eye-icon" class="fas fa-eye-slash"></i>
  </span>
</div>
           
            <div class="input-field" id="confirm">
              <i class="fas fa-lock"></i>
              <input type="password" id="confirm-password" name="confirmPassword" placeholder="Confirm Password" required />
  <span class="toggle-password" onclick="togglePasswordVisibility('confirm-password')">
    <i id="confirm-password-eye-icon" class="fas fa-eye-slash"></i>
  </span>
</div>
            
       
            <input type="submit" class="btn" name="signup" value="Signup" onclick="return validatePassword();"/>
          </form>



          <br>
          <br>
        </div>
      </div>

      <div class="panels-container">
        <div class="panel left-panel">
          <div class="content">
            <h3></h3>
            <p>
     
            </p>
            <button class="btn transparent" id="sign-up-btn">
            </button>
          </div>
        </div>
        <div class="panel right-panel">
          <div class="content">
            <h3>Panadtaran Waterworks</h3>
         
          </div>
          <img src="admin/img/login.svg" class="image" alt="sign in" />
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
    
    <script src="signin/js/jquery.min.js"></script>
<script src="signin/js/bootstrap.min.js"></script> 
<script src="signin/js/interface.js"></script> 
    <script src="app.js"></script>
  </body>
</html>