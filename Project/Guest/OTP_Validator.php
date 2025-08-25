<?php
include("../Assets/Connection/Connection.php");
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../Assets/phpMail/src/Exception.php';
require '../Assets/phpMail/src/PHPMailer.php';
require '../Assets/phpMail/src/SMTP.php';

function generateOTP($length = 6) {
    $digits = '0123456789';
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= $digits[rand(0, strlen($digits) - 1)];
    }
    return $otp;
}

function otpEmail($email,$otp){
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'nexo51102@gmail.com'; // Your gmail
    $mail->Password = 'ooem ajgf wtbv aqtr'; // Your gmail app password
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
  
    $mail->setFrom('nexo51102@gmail.com'); // Your gmail
  
    $mail->addAddress($email);
  
    $mail->isHTML(true);
    $message = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your OTP Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: #fff;
            border-radius: 5px;
            padding: 20px;
            max-width: 600px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            font-size: 24px;
            margin-bottom: 20px;
        }
        .footer {
            font-size: 12px;
            color: #999;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            Your OTP Code
        </div>
        <p>Hello,</p>
        <p>Here is your One-Time Password (OTP) for verification:</p>
        <h2 style="font-size: 36px; color: #333;">' . $otp . '</h2>
        <p>This OTP is valid for the next 5 minutes. Please use it to complete your verification process.</p>
        <p>If you did not request this OTP, please ignore this email or contact support if you have concerns.</p>
        <p>Best regards,<br>Company Name</p>
        <div class="footer">
            This is an automated message. Please do not reply.
        </div>
    </div>
</body>
</html>
';
    $mail->Subject = "Reset your password";  //Your Subject goes here
    $mail->Body = $message; //Mail Body goes here
  if($mail->send())
  {
    return true;
  }
  else
  {
   return false;
  }
}

if(isset($_POST['btn_submit'])){
    if($_SESSION['otp']==$_POST['txt_otp']){
        
       ?>
       <script>
        alert('OTP Validated')
        window.location="ResetPassword.php"
        </script>
       <?php
    }
    else{
        ?>
        <script>
            alert('OTP Incorrect')
            </script>
        <?php
    }
}

// Handle OTP resend request
if(isset($_POST['resend_otp'])) {
    if(isset($_SESSION['user_email'])) {
        // Generate new OTP
        $new_otp = generateOTP();
        $_SESSION['otp'] = $new_otp;
        
        // Send the new OTP via email
        if(otpEmail($_SESSION['user_email'], $new_otp)) {
            $resend_success = "New OTP has been sent to your email.";
        } else {
            $resend_error = "Failed to send OTP. Please try again.";
        }
    } else {
        $resend_error = "Session expired. Please restart the password reset process.";
    }
}

// Make sure we have the user's email in session
if(!isset($_SESSION['user_email']) && isset($_SESSION['ruid'])) {
    // Fetch user email from database based on user_id
    $selQry = "SELECT user_email FROM tbl_user WHERE user_id = ".$_SESSION['ruid'];
    $result = $con->query($selQry);
    if($userData = $result->fetch_assoc()) {
        $_SESSION['user_email'] = $userData['user_email'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validate OTP - Nexo</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');
        
        :root {
            --dark-bg: #0A0A0A;
            --card-bg: rgba(255, 255, 255, 0.08);
            --accent-blue: #007bff;
            --button-hover: #0056b3;
            --text-light: #F8F8F8;
            --text-subtle: #AFAFAF;
            --border-color: rgba(255, 255, 255, 0.15);
            --input-bg: rgba(255, 255, 255, 0.05);
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-light);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .header {
            background-color: rgba(10, 10, 10, 0.95);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-bottom: 1px solid var(--border-color);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logo {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-light);
            text-decoration: none;
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
        }

        .main-container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-grow: 1;
            padding: 40px 20px;
        }

        .form-container {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        .form-title {
            margin-bottom: 15px;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-light);
        }

        .form-description {
            margin-bottom: 30px;
            color: var(--text-subtle);
            line-height: 1.6;
        }

        .otp-input-container {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .otp-input {
            width: 50px;
            height: 60px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 600;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background: var(--input-bg);
            color: var(--text-light);
            transition: all 0.3s ease;
        }

        .otp-input:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }

        .btn-validate {
            width: 100%;
            padding: 14px;
            background-color: var(--accent-blue);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-validate:hover {
            background-color: var(--button-hover);
            transform: translateY(-2px);
        }

        .btn-validate:active {
            transform: translateY(0);
        }

        .resend-otp {
            margin-top: 25px;
            color: var(--text-subtle);
        }

        .resend-otp a {
            color: var(--accent-blue);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .resend-otp a:hover {
            text-decoration: underline;
        }

        .back-link {
            text-align: center;
            margin-top: 25px;
        }

        .back-link a {
            color: var(--accent-blue);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.2);
            border: 1px solid #28a745;
            color: #28a745;
        }

        .alert-error {
            background-color: rgba(220, 53, 69, 0.2);
            border: 1px solid #dc3545;
            color: #dc3545;
        }

        /* Responsive design */
        @media (max-width: 576px) {
            .form-container {
                padding: 30px 20px;
            }
            
            .header-container {
                padding: 15px 20px;
            }
            
            .otp-input-container {
                gap: 10px;
            }
            
            .otp-input {
                width: 45px;
                height: 55px;
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-container">
            <a href="HomePage.php" class="logo">Nexo</a>
        </div>
    </header>

    <div class="main-container">
        <div class="form-container">
            <h2 class="form-title">Enter Verification Code</h2>
            <p class="form-description">We've sent a 6-digit code to your email. Please enter it below to verify your account.</p>

            <?php if(isset($resend_success)): ?>
                <div class="alert alert-success"><?php echo $resend_success; ?></div>
            <?php endif; ?>
            
            <?php if(isset($resend_error)): ?>
                <div class="alert alert-error"><?php echo $resend_error; ?></div>
            <?php endif; ?>
            
            <form id="otpForm" method="POST" action="">
                <div class="otp-input-container">
                    <input type="text" class="otp-input" id="digit1" maxlength="1" oninput="moveToNext(1)" autofocus>
                    <input type="text" class="otp-input" id="digit2" maxlength="1" oninput="moveToNext(2)">
                    <input type="text" class="otp-input" id="digit3" maxlength="1" oninput="moveToNext(3)">
                    <input type="text" class="otp-input" id="digit4" maxlength="1" oninput="moveToNext(4)">
                    <input type="text" class="otp-input" id="digit5" maxlength="1" oninput="moveToNext(5)">
                    <input type="text" class="otp-input" id="digit6" maxlength="1" oninput="moveToNext(6)">
                </div>
                
                <input type="hidden" name="txt_otp" id="fullOtp">
                
                <button type="submit" name="btn_submit" class="btn-validate">Validate OTP</button>
            </form>
            
            <form method="POST" action="" id="resendForm">
                <input type="hidden" name="resend_otp" value="1">
            </form>
            <div class="resend-otp">
                Didn't receive the code? <a href="#" id="resendLink">Resend OTP</a>
            </div>
            
            <div class="back-link">
                <a href="../Guest/Login.php">Back to Login</a>
            </div>
        </div>
    </div>

    <script>
        // Function to move to next input field
        function moveToNext(current) {
            const currentInput = document.getElementById(`digit${current}`);
            const nextInput = document.getElementById(`digit${current + 1}`);
            
            if (currentInput.value.length === 1 && nextInput) {
                nextInput.focus();
            }
            
            updateFullOtp();
        }
        
        // Function to update the hidden input with full OTP
        function updateFullOtp() {
            let fullOtp = '';
            for (let i = 1; i <= 6; i++) {
                fullOtp += document.getElementById(`digit${i}`).value;
            }
            document.getElementById('fullOtp').value = fullOtp;
        }
        
        // Auto-focus first input on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('digit1').focus();
        });
        
        // Allow pasting OTP
        document.getElementById('digit1').addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedData = e.clipboardData.getData('text');
            if (pastedData.length === 6 && /^\d+$/.test(pastedData)) {
                for (let i = 0; i < 6; i++) {
                    document.getElementById(`digit${i+1}`).value = pastedData[i];
                }
                updateFullOtp();
                document.getElementById('digit6').focus();
            }
        });

        // Resend OTP functionality
        const resendLink = document.getElementById('resendLink');
        resendLink.addEventListener('click', function(e) {
            e.preventDefault();
            
            // If link is disabled, do nothing
            if (this.classList.contains('disabled')) {
                return;
            }
            
            // Submit the resend form
            document.getElementById('resendForm').submit();
        });
    </script>
</body>
</html>