<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './admin/PHPMailer/src/Exception.php';
require './admin/PHPMailer/src/PHPMailer.php';
require './admin/PHPMailer/src/SMTP.php';

include 'admin/includes/config.php';

$mode = $_GET['mode'] ?? '';
$containerClass = ($mode === 'signup') ? 'container' : '';

$signupError = "";

if (isset($_POST['signup'])) {
    try {
        $meternumber = $_POST['meternumber'];
        $mobilenumber = $_POST['mobilenumber'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT); 
        $dbh->beginTransaction();

        $stmt = $dbh->prepare("SELECT `mobile_number`, `serial_no` FROM `consumers` 
        WHERE `mobile_number` = :mobile_number 
        AND `serial_no` = :serial_no 
        AND (`user_id` = 0 OR `user_id` IS NULL)");
        $stmt->execute([
            'serial_no' => $meternumber,
            'mobile_number' => $mobilenumber
        ]);
        $accExist = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!empty($accExist)) {
            $verificationCode = rand(1000, 9999);
            $meter=$accExist['serial_no'];
            $insertStmt = $dbh->prepare("INSERT INTO `users` (`username`, `password`) VALUES (:username, :password)");
            $insertStmt->bindParam(':username', $email, PDO::PARAM_STR);
            $insertStmt->bindParam(':password', $password, PDO::PARAM_STR);
            $insertStmt->execute();

            $lastInsertId = $dbh->lastInsertId();

            $insertStmt1 = $dbh->prepare("INSERT INTO `otp` (`consumer_id`, `otp`) VALUES (:consumer_id, :verification_code)");
            $insertStmt1->bindParam(':consumer_id', $lastInsertId, PDO::PARAM_INT);
            $insertStmt1->bindParam(':verification_code', $verificationCode, PDO::PARAM_INT);
            $insertStmt1->execute();

            $mail = new PHPMailer(true);

            $mail->isSMTP(); 
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true; 
            $mail->Username   = 'primebea11@gmail.com';
            $mail->Password   = 'eudjclfackegutph'; 
            $mail->Port       = 465;     
            $mail->SMTPSecure = 'ssl';

            $mail->setFrom('primebea11@gmail.com', 'Brgy.Panadtaran Water Works');
            $mail->addAddress($email);   
            $mail->addReplyTo('primebea11@gmail.com', 'Brgy.Panadtaran Water Works');

            $mail->isHTML(true);  
            $mail->Subject = 'Verification Code';
            $mail->Body    = 'Your verification code is: ' . $verificationCode;

            $mail->send();

            $_SESSION['user_verification_id'] = $lastInsertId;
            $_SESSION['meter'] = $meter;
            $_SESSION['email'] = $email;
            echo "
            <script>
                alert('Check your email for verification code.');
                window.location.href = 'otp.php';
            </script>
            ";

            $dbh->commit();
        } elseif($accExist) {
            echo "
            <script>
                alert('User Already Exists');
                window.location.href = 'signin.php';
            </script>
            ";
        }
        else {
            echo "
            <script>
                alert('User not Found');
                window.location.href = 'signin.php';
            </script>
            ";
        }
    } catch (PDOException $e) {
        $dbh->rollBack();
        echo "Error: " . $e->getMessage();
    }
}

if (isset($_POST['verify'])) {

    try {
        $meter = $_POST['meter'];
        $userVerificationID = $_POST['user_verification_id'];
        $verificationCode = $_POST['verification_code'];
    
        $stmt = $dbh->prepare("SELECT `otp` FROM `otp` WHERE `consumer_id` = :user_verification_id");
        $stmt->execute([
            'user_verification_id' => $userVerificationID,
        ]);
        $codeExist = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($codeExist && $codeExist['otp'] == $verificationCode) {
            $insertStmt = $dbh->prepare("UPDATE `consumers` SET `user_id` = :user WHERE serial_no = :meter");
            $insertStmt->bindParam(':user',  $userVerificationID, PDO::PARAM_INT);
            $insertStmt->bindParam(':meter',  $meter, PDO::PARAM_INT);
            $insertStmt->execute();
       
              $insertStmt1 = $dbh->prepare("UPDATE `users` SET `status` = :status WHERE id = :user");
              $insertStmt1->bindParam(':user',  $userVerificationID , PDO::PARAM_INT);
              $insertStmt1->bindValue(':status', 'active', PDO::PARAM_STR);
              $insertStmt1->execute();
 


            session_destroy();
            echo "
            <script>
                alert('Registered Successfully.');
                window.location.href = 'consumer/index.php';
            </script>
            ";
        } else {
            $dbh->prepare("DELETE FROM `users` WHERE `id` = :user_verification_id")->execute([
                'user_verification_id' => $userVerificationID
            ]);

            echo "
            <script>
                alert('Incorrect Verification Code.Please try again.');
                window.location.href = 'otp.php';
            </script>
            ";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
if (isset($_POST['resend_otp'])) {
    try {
        $userVerificationID = $_POST['user_verification_id'];
        $email = $_POST['email'];
    
        if (!empty($userVerificationID)) {
            $verificationCode = rand(1000, 9999);
            $mail = new PHPMailer(true);

            $mail->isSMTP(); 
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true; 
            $mail->Username   = 'primebea11@gmail.com';
            $mail->Password   = 'eudjclfackegutph'; 
            $mail->Port       = 465;     
            $mail->SMTPSecure = 'ssl';

            $mail->setFrom('primebea11@gmail.com', 'Brgy.Panadtaran Water Works');
            $mail->addAddress($email);   
            $mail->addReplyTo('primebea11@gmail.com', 'Brgy.Panadtaran Water Works');

            $mail->isHTML(true);  
            $mail->Subject = 'Verification Code';
            $mail->Body    = 'Your verification code is: ' . $verificationCode;

            $mail->send();

            $insertStmt = $dbh->prepare("UPDATE `otp` SET `otp` = :code WHERE consumer_id = :user");
            $insertStmt->bindParam(':user',  $userVerificationID, PDO::PARAM_INT);
            $insertStmt->bindParam(':code',  $verificationCode, PDO::PARAM_INT);
            $insertStmt->execute();
            echo "
            <script>
                alert('OTP Sent Successfully.');
                window.location.href = 'otp.php';
            </script>
            ";
        } else {
            $dbh->prepare("DELETE FROM `otp` WHERE `consumer_id` = :user_verification_id")->execute([
                'user_verification_id' => $userVerificationID
            ]);
            echo "
            <script>
                alert('Error. Register Again.');
                window.location.href = 'signin.php';
            </script>
            ";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>