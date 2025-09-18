<?php
include("../Assets/Connection/Connection.php");
session_start();
include("Header.php");

if(isset($_POST['btn_change']))
{
	$old=$_POST['txt_oldpassword'];
	$new=$_POST['txt_newpassword'];
	$repass=$_POST['txt_repassword'];
	$hashedPassword = password_hash($new, PASSWORD_DEFAULT); // Hash password
	
	$selQry="select user_password from tbl_user where user_id='".$_SESSION['uid']."'";
	$res=$con->query($selQry);
	$data=$res->fetch_assoc();
	if($old == $data['user_password'])
	{
	if($new == $repass)
	{
		$upQry="update tbl_user set user_password='".$hashedPassword."' where user_id='".$_SESSION['uid']."'";
	   if($con->query($upQry))
	   {
		?>
        <script>
		alert("Password updated");
		window.location="MyProfile.php";
		</script>
        <?php
	   }
    }
	else
	{
		?>
        <script>
		alert("Invalid new password");
		window.location="ChangePassword.php";
		</script>
		<?php
	}
	}
	else
	{
		?>
    <script>
		alert("Your old password is incorrect");
		window.location="ChangePassword.php";
		</script>
		<?php
	}
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Change Password - Nexo</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@400;500;600;700&display=swap');
    
    :root {
        --dark-bg: #0A0A0A;
        --card-bg: rgba(255, 255, 255, 0.08);
        --accent-red: #E53935;
        --text-light: #F8F8F8;
        --text-subtle: #AFAFAF;
        --border-color: rgba(255, 255, 255, 0.15);
    }

    body {
        font-family: 'Montserrat', sans-serif;
        background-color: var(--dark-bg);
        color: var(--text-light);
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 20px;
        overflow-x: hidden;
    }

    .form-container {
        margin-top:70px;
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border-radius: 20px;
        width: 100%;
        max-width: 500px;
        padding: 40px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        animation: fadeIn 0.8s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .form-header {
        margin-bottom: 30px;
        text-align: center;
    }
    
    .form-header h1 {
        font-family: 'Playfair Display', serif;
        font-size: 2.2rem;
        margin-bottom: 10px;
        color: var(--text-light);
    }
    
    .form-header p {
        color: var(--text-subtle);
        font-size: 1rem;
    }

    .form-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 15px;
    }
    
    .form-table td {
        padding: 10px;
        border: none;
    }
    
    .form-table tr td:first-child {
        width: 35%;
        font-weight: 500;
        padding-right: 10px;
        text-align: right;
        color: var(--text-light);
    }
    
    .form-table tr td:last-child {
        width: 65%;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 14px;
        background-color: rgba(0, 0, 0, 0.2);
        color: var(--text-light);
        transition: all 0.3s;
    }
    
    .form-control:focus {
        outline: none;
        border-color: var(--accent-red);
        box-shadow: 0 0 0 0.2rem rgba(229, 57, 53, 0.25);
    }

    /* Password field styling */
    .password-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .password-wrapper input {
        width: 100%;
        padding-right: 40px;
    }

    .password-wrapper i {
        position: absolute;
        right: 12px;
        cursor: pointer;
        color: var(--text-subtle);
        font-size: 18px;
        transition: color 0.2s;
    }

    .password-wrapper i:hover {
        color: var(--accent-red);
    }

    /* Hide default password reveal icons */
    input[type="password"]::-ms-reveal,
    input[type="password"]::-ms-clear {
        display: none;
    }

    input[type="password"]::-webkit-credentials-auto-fill-button,
    input[type="password"]::-webkit-clear-button,
    input[type="password"]::-webkit-inner-spin-button {
        display: none !important;
    }

    .button-group {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        justify-content: center;
    }
    
    .btn {
        padding: 12px 30px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s;
        border: none;
    }
    
    .btn-primary {
        background-color: var(--accent-red);
        color: var(--text-light);
    }
    
    .btn-primary:hover {
        opacity: 0.9;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(229, 57, 53, 0.4);
    }
    
    .btn-secondary {
        background-color: transparent;
        color: var(--text-light);
        border: 1px solid var(--border-color);
    }
    
    .btn-secondary:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }
</style>
</head>

<body>
<div class="form-container">
    <div class="form-header">
        <h1>Change Password</h1>
        <p>Secure your account with a new password</p>
    </div>
    
    <form id="form1" name="form1" method="post" action="">
        <table class="form-table">
            <tr>
                <td>Old Password</td>
                <td>
                    <div class="password-wrapper">
                        <input type="password" class="form-control" name="txt_oldpassword" id="txt_oldpassword" 
                               placeholder="Enter Old Password" 
                               pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" 
                               title="Must contain at least one number, uppercase, lowercase letter, and 8+ characters" 
                               required />
                        <i class="fa-regular fa-eye" id="toggleOldPassword"></i>
                    </div>
                </td>
            </tr>
            <tr>
                <td>New Password</td>
                <td>
                    <div class="password-wrapper">
                        <input type="password" class="form-control" name="txt_newpassword" id="txt_newpassword" 
                               placeholder="Enter New Password" 
                               pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" 
                               title="Must contain at least one number, uppercase, lowercase letter, and 8+ characters" 
                               required />
                        <i class="fa-regular fa-eye" id="toggleNewPassword"></i>
                    </div>
                </td>
            </tr>
            <tr>
                <td>Re-Type Password</td>
                <td>
                    <div class="password-wrapper">
                        <input type="password" class="form-control" name="txt_repassword" id="txt_repassword" 
                               placeholder="Re-Type Password" 
                               required />
                        <i class="fa-regular fa-eye" id="toggleRePassword"></i>
                    </div>
                </td>
            </tr>
        </table>
        <div class="button-group">
            <input type="submit" class="btn btn-primary" name="btn_change" id="btn_change" value="Change Password" />
            <input type="reset" class="btn btn-secondary" name="btn_cancel" id="btn_cancel" value="Cancel" />
        </div>
    </form>
</div>
</body>
</html>