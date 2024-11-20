<?php 

include 'admin/includes/config.php';

// Check if the session has a user_verification_id
if (isset($_SESSION['user_verification_id'])) {
    $userVerificationID = $_SESSION['user_verification_id'];
    $meter = $_SESSION['meter'];
    $email = $_SESSION['email'];
} else {
    echo "
    <script>
        alert('No verification session found.');
        window.location.href = 'signin.php'; // Redirect to a proper page if needed
    </script>
    ";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <link rel="shortcut icon" href="assets/favicon.ico">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@500&display=swap');

        * {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: url("assets/images/dark.jpg");
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            padding:20px;
            height: 100vh;
        }

        .verification-form {
            backdrop-filter: blur(10px);
            color: #fff;
            padding: 40px;
            width: 400px;
            border-radius: 10px;
            background-color: rgba(0, 0, 0, 0.7);
        }

        .verification-form input {
            margin: 10px 0;
        }

        /* Styling for the 4 input fields */
        .code-inputs {
            display: flex;
            justify-content: space-between;
        }

        .code-input {
            width: 60px;
            height: 60px;
            font-size: 2rem;
            text-align: center;
            margin: 5px;
        }
    </style>
</head>
<body>
    
    <div class="main">

        <!-- Email Verification Area -->
        <div class="verification-container">
            <div class="verification-form">
                <h2 class="text-center">Email Verification</h2>
                <p class="text-center">Please enter the 4-digit verification code sent to your email.</p>
                
                <!-- Form to verify the user -->
                <form action="check.php" method="POST" id="verificationForm">
                    <!-- Hidden field to pass user verification ID -->
                    <input type="hidden" name="user_verification_id" value="<?= htmlspecialchars($userVerificationID); ?>">
                    <input type="hidden" name="meter" value="<?= htmlspecialchars($meter); ?>">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($email); ?>">
                    
                    <!-- 4 separate input fields for the verification code -->
                    <div class="code-inputs">
                        <input type="text" class="form-control code-input" maxlength="1" id="code1" oninput="moveNext(this, 'code2')" >
                        <input type="text" class="form-control code-input" maxlength="1" id="code2" oninput="moveNext(this, 'code3')" >
                        <input type="text" class="form-control code-input" maxlength="1" id="code3" oninput="moveNext(this, 'code4')" >
                        <input type="text" class="form-control code-input" maxlength="1" id="code4" >
                    </div>

                    <!-- Hidden field to store concatenated verification code -->
                    <input type="hidden" id="verification_code" name="verification_code">

                    <!-- Verify Button -->
                    <button type="submit" class="btn btn-secondary login-btn form-control mt-4" name="verify">Verify</button>
                    
                    <!-- Resend OTP Button -->
                    <button type="submit" class="btn btn-secondary login-btn form-control mt-2" name="resend_otp">Resend OTP</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>

    <script>
        // Function to move to the next input automatically
        function moveNext(currentInput, nextInputId) {
            if (currentInput.value.length === 1) {
                document.getElementById(nextInputId).focus();
            }
        }

        // Concatenate the values of the 4 inputs and submit the form
        document.getElementById('verificationForm').addEventListener('submit', function(event) {
            var code1 = document.getElementById('code1').value;
            var code2 = document.getElementById('code2').value;
            var code3 = document.getElementById('code3').value;
            var code4 = document.getElementById('code4').value;

            // Concatenate the codes into the hidden input field
            var verificationCode = code1 + code2 + code3 + code4;
            document.getElementById('verification_code').value = verificationCode;
        });
    </script>

</body>
</html>
