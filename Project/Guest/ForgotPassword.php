<?php
session_start();
include("../Assets/Connection/Connection.php");
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
    ?>
<script>
    alert("Email Send");
    window.location="OTP_validator.php";
</script>
    <?php
  }
  else
  {
    ?>
<script>
    alert("Email Failed")
</script>
    <?php
  }
}

if(isset($_POST['btn_submit'])){
    $email=$_POST['txt_email'];
    $selUser="select * from tbl_user where user_email='".$email."'";	
	$resUser=$con->query($selUser);
    $otp = generateOTP();
    $_SESSION['otp'] = $otp;
    if($userData=$resUser->fetch_assoc())
	{
		$_SESSION['ruid'] = $userData['user_id'];
		otpEmail($email,$otp);
	}
	else{
	?>
    	<script>
		alert("Account Doesn't Exists")
		</script>
    <?php	
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Nexo</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');
        
        :root {
            --dark-bg: #0A0A0A;
            --card-bg: rgba(255, 255, 255, 0.08);
            --accent-red: #E53935;
            --text-light: #F8F8F8;
            --text-subtle: #AFAFAF;
            --border-color: rgba(255, 255, 255, 0.15);
            --hover-bg: rgba(255, 255, 255, 0.05);
            --success-green: #4CAF50;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: url("../../Docs/img/forgot.jpg") no-repeat center center fixed;
            background-size: cover;
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
        }

        .form-title {
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-light);
        }

        .form-description {
            text-align: center;
            margin-bottom: 30px;
            color: var(--text-subtle);
            line-height: 1.6;
        }

        .input-group {
            margin-bottom: 25px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-light);
        }

        .input-group input {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-light);
            font-size: 1rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .input-group input:focus {
            outline: none;
            border-color: var(--accent-red);
            box-shadow: 0 0 0 2px rgba(229, 57, 53, 0.2);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background-color: var(--accent-red);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            background-color: #d32f2f;
            transform: translateY(-2px);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .back-to-login {
            text-align: center;
            margin-top: 25px;
        }

        .back-to-login a {
            color: var(--accent-red);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .back-to-login a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .alert-success {
            background-color: rgba(76, 175, 80, 0.2);
            border: 1px solid var(--success-green);
            color: var(--success-green);
        }

        .alert-error {
            background-color: rgba(229, 57, 53, 0.2);
            border: 1px solid var(--accent-red);
            color: var(--accent-red);
        }

        /* Responsive design */
        @media (max-width: 576px) {
            .form-container {
                padding: 30px 20px;
            }
            
            .header-container {
                padding: 15px 20px;
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
            <h2 class="form-title">Reset Your Password</h2>
            <p class="form-description">Enter your email address and we'll send you an OTP to reset your password.</p>
            
            <form action="" method="post">
                <div class="input-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="txt_email" id="email" required>
                </div>
                
                <button type="submit" name="btn_submit" class="btn-submit">Send OTP</button>
            </form>
            
            <div class="back-to-login">
                <a href="Login.php">Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>