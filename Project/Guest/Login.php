<?php
include ("../Assets/Connection/Connection.php");
session_start();

if(isset($_POST['btn_login']))
{
    $email = $_POST['txt_email'];
    $password = $_POST['txt_password'];

    //user login
    $selUser="select * from tbl_user where user_email='".$email."' and user_status='active'";
    $UserRes=$con->query($selUser);
    $UserData=$UserRes->fetch_assoc();
    
    //verifier login
    $selVerifier="select * from tbl_verifier where verifier_email='".$email."'";
    $VerifierRes=$con->query($selVerifier);
    $VerifierData = $VerifierRes->fetch_assoc();
    
    //admin login
    $selAdmin="select * from tbl_admin where admin_email='".$email."'";
    $AdminRes=$con->query($selAdmin);
    $AdminData = $AdminRes->fetch_assoc();
    
    //checking if it's admin, user, verifier 
    if($UserData && password_verify($password, $UserData['user_password']))
    {
        $_SESSION['uid'] = $UserData['user_id'];
        $_SESSION['uname'] = $UserData['user_name'];
        header("location:../User/HomePage.php");
        exit;
    }
    else if($VerifierData && password_verify($password, $VerifierData['verifier_password']))
    {
        $_SESSION['vid'] = $VerifierData['verifier_id'];
        $_SESSION['vname'] = $VerifierData['verifier_name'];
        header("location:../Verifier/HomePage.php");
    }
    else if($AdminData && password_verify($password, $AdminData['admin_password']))
    {
        $_SESSION['aid'] = $AdminData['admin_id'];
        $_SESSION['aname'] = $AdminData['admin_name'];
        header("location:../Admin/HomePage.php");
    }
    else
    {
        ?>
        <script>
        alert("Invalid");
        window.location="Login.php";
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
    <title>Nexo - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
    <style>
        /* Re-using styles from the first code block for consistency */
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@400;500;600;700&display=swap');
        
        :root {
            --dark-bg: #0A0A0A; /* Deeper, richer black */
            --card-bg: rgba(255, 255, 255, 0.08); /* More transparent for better blur */
            --accent-red: #E53935; /* Brighter, more vibrant red */
            --text-light: #F8F8F8;
            --text-subtle: #AFAFAF;
            --border-color: rgba(255, 255, 255, 0.15);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--text-light);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            line-height: 1.6;
            padding: 20px;
        }
        
        a {
            text-decoration: none;
            color: inherit;
        }

        .login-container {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            backdrop-filter: blur(15px); /* Stronger blur effect */
            -webkit-backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--accent-red);
            display: block;
            text-align: center;
            margin-bottom: 20px;
        }

        .login-header {
            margin-bottom: 30px;
            text-align: center;
        }
        
        .login-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            color: var(--text-light);
            margin-bottom: 8px;
        }

        .login-header p {
            color: var(--text-subtle);
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 25px;
            width: 100%;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-subtle);
            font-weight: 500;
            font-size: 14px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background-color: transparent; /* Make input transparent */
            color: var(--text-light);
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--accent-red);
            box-shadow: 0 0 0 3px rgba(229, 57, 53, 0.3);
        }
        
        .input-wrapper {
            position: relative;
        }
        
        /* Hide browser’s built-in password reveal */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none;
        }
        input[type="password"]::-webkit-contacts-auto-fill-button,
        input[type="password"]::-webkit-credentials-auto-fill-button {
            display: none !important;
        }
        
        .toggle-password {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--accent-red);
            font-size: 18px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .toggle-password.visible {
            opacity: 1;
        }
        
        .forgot-password {
            display: block;
            text-align: right;
            margin-bottom: 20px;
            color: var(--accent-red);
            font-size: 14px;
            transition: color 0.3s ease;
        }
        
        .forgot-password:hover {
            color: #ff6347; /* Lighter red on hover */
        }
        
        .btn-login {
            width: 100%;
            padding: 12px;
            background-color: var(--accent-red);
            color: var(--text-light);
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            margin-bottom: 25px;
        }
        
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(229, 57, 53, 0.4);
        }
        
        .signup-link {
            text-align: center;
            color: var(--text-subtle);
            font-size: 14px;
        }
        
        .signup-link a {
            color: var(--accent-red);
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .signup-link a:hover {
            color: #ff6347; /* Lighter red on hover */
        }
        
        .animation-container {
            margin-top: 20px;
            width: 250px;
            height: 250px;
        }
        
        @media (max-width: 768px) {
            .login-container {
                padding: 30px 20px;
                margin: 20px;
            }
            
            .animation-container {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <a href="#" class="logo">Nexo</a>
        
        <div class="login-header">
            <h2>Welcome Back</h2>
            <p>Please enter your credentials to log in</p>
        </div>
        
        <form id="form1" name="form1" method="post" action="">
            <div class="form-group">
                <label for="txt_email">Email</label>
                <input type="email" name="txt_email" id="txt_email" class="form-control" placeholder="yourname@example.com" required />
            </div>
            
            <div class="form-group">
                <label for="txt_password">Password</label>
                <div class="input-wrapper">
                    <input type="password" name="txt_password" id="txt_password" class="form-control" placeholder="••••••••" required />
                    <i class="fa-regular fa-eye toggle-password" id="togglePassword" style="display:none;"></i>
                </div>
            </div>
            <a href="ForgotPassword.php" class="forgot-password">Forgot password?</a>

            <input type="submit" name="btn_login" id="btn_login" value="Login" class="btn-login" />
            
            <div class="signup-link">
                Don't have an account? <a href="NewUser.php">Sign up</a>
            </div>
        </form>
        
        <div class="animation-container">
            <lottie-player src="https://assets2.lottiefiles.com/packages/lf20_khtt8ejx.json" background="transparent" speed="1" loop autoplay></lottie-player>
        </div>
    </div>

<script>
const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('txt_password');

// Show/hide icon with fade
passwordInput.addEventListener('input', () => {
    if (passwordInput.value.length > 0) {
        togglePassword.style.display = 'block';
        togglePassword.classList.add('visible');
    } else {
        togglePassword.classList.remove('visible');
        setTimeout(() => { 
            if (!passwordInput.value.length) togglePassword.style.display = 'none';
        }, 300); // wait for fade out
    }
});

// Toggle password visibility
togglePassword.addEventListener('click', () => {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);

    // Switch between eye (hidden) and eye-slash (visible) in OUTLINE style
    togglePassword.classList.toggle('fa-eye');
    togglePassword.classList.toggle('fa-eye-slash');
});
</script>

</body>
</html>