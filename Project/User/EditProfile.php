<?php
include("../Assets/Connection/Connection.php");
session_start();
include("Header.php");

$selQry="select * from tbl_user where user_id='".$_SESSION['uid']."'";
$res=$con->query($selQry);
$data=$res->fetch_assoc();

if(isset($_POST['btn_update']))
{
    $name=$_POST['txt_name'];
    $email=$_POST['txt_email'];
    $contact=$_POST['txt_contact'];
    
    // Handle photo upload
    if(!empty($_FILES['file_photo']['name'])) {
        $photo = $_FILES['file_photo']['name'];
        $temp = $_FILES['file_photo']['tmp_name'];
        move_uploaded_file($temp,"../Assets/Files/UserDocs/".$photo);
        $photo_update = ", user_photo='".$photo."'";
    } else {
        $photo_update = "";
    }
    
    $upQry="update tbl_user set user_name='".$name."',user_email='".$email."',user_contact='".$contact."'".$photo_update." where user_id='".$_SESSION['uid']."'";
    if($con->query($upQry))
    {
        ?>
        <script>
        alert("Profile updated");
        window.location="MyProfile.php";
        </script>
        <?php
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Edit Profile</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    body {
        background-color: #000000;
        color: #FFFFFF;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        margin: 50px;
        padding: 20px;
        line-height: 1.6;
    }
    
    .form-container {
        background-color: #000000;
        border: 2px solid #013220;
        border-radius: 12px;
        padding: 30px;
        width: 100%;
        max-width: 500px;
        box-shadow: 0 10px 30px rgba(1, 50, 32, 0.2);
    }
    
    .photo-upload-container {
        text-align: center;
        margin-bottom: 25px;
    }
    
    .round-photo {
        width: 160px;
        height: 160px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #013220;
        display: block;
        margin: 0 auto 15px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(1, 50, 32, 0.3);
    }
    
    .round-photo:hover {
        transform: scale(1.02);
        box-shadow: 0 6px 20px rgba(1, 50, 32, 0.4);
    }
    
    .edit-icon {
        position: absolute;
        bottom: 15px;
        right: calc(50% - 65px);
        background: #013220;
        color: white;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }
    
    .edit-icon:hover {
        background: #024a2a;
        transform: scale(1.1);
    }
    
    .edit-icon i {
        font-size: 16px;
    }
    
    .file-input {
        display: none;
    }
    
    table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        margin: 0 auto;
    }
    
    td {
        padding: 15px;
        border: 1px solid #013220;
    }
    
    tr:first-child td {
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }
    
    tr:last-child td {
        border-bottom-left-radius: 8px;
        border-bottom-right-radius: 8px;
    }
    
    input[type="text"],
    input[type="email"] {
        width: 100%;
        padding: 12px 15px;
        background-color: rgba(1, 50, 32, 0.1);
        color: #FFFFFF;
        border: 1px solid #013220;
        border-radius: 6px;
        font-size: 15px;
        transition: all 0.3s ease;
    }
    
    input[type="text"]:focus,
    input[type="email"]:focus {
        outline: none;
        border-color: #024a2a;
        box-shadow: 0 0 0 2px rgba(1, 50, 32, 0.3);
    }
    
    input[type="submit"] {
        background-color: #013220;
        color: #FFFFFF;
        padding: 12px 25px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 600;
        transition: all 0.3s ease;
        width: 100%;
    }
    
    input[type="submit"]:hover {
        background-color: #024a2a;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(1, 50, 32, 0.4);
    }
    
    .form-footer {
        text-align: center;
        margin-top: 20px;
    }
    
    .form-footer a {
        color: #FFFFFF;
        text-decoration: none;
        font-size: 14px;
        opacity: 0.8;
        transition: opacity 0.3s;
    }
    
    .form-footer a:hover {
        opacity: 1;
        text-decoration: underline;
    }
    
    @media (max-width: 600px) {
        .form-container {
            padding: 20px;
        }
        
        td {
            padding: 12px;
        }
    }
</style>
</head>

<body>
<div class="form-container">
    <form id="form1" name="form1" method="post" action="" enctype="multipart/form-data">
        <div class="photo-upload-container">
            <div style="position: relative; display: inline-block;">
                <img src="../Assets/Files/UserDocs/<?php echo ($data['user_photo']?:'default.avif') ?>" class="round-photo" alt="Profile Photo" id="profilePreview">
                <label for="file_photo" class="edit-icon">
                    <i class="fas fa-pencil-alt"></i>
                </label>
                <input type="file" name="file_photo" id="file_photo" class="file-input" accept="image/*">
            </div>
        </div>

        <table width="100%" border="1" align="center">
            <tr>
                <td>Name</td>
                <td>
                    <label for="txt_name"></label>
                    <input type="text" name="txt_name" id="txt_name" 
                           value="<?php echo $data['user_name']?>" 
                           title="Name Allows Only Alphabets, Spaces and First Letter Must Be Capital Letter" 
                           pattern="^[A-Z]+[a-zA-Z ]*$" required>
                </td>
            </tr>
            
            <tr>
                <td>Email</td>
                <td>
                    <label for="txt_email"></label>
                    <input type="email" name="txt_email" id="txt_email" 
                           value="<?php echo $data['user_email']?>" required>
                </td>
            </tr>
                
            <tr>
                <td>Contact</td>
                <td>
                    <label for="txt_contact"></label>
                    <input type="text" name="txt_contact" id="txt_contact" 
                           value="<?php echo $data['user_contact']?>" 
                           pattern="[7-9]{1}[0-9]{9}" 
                           title="Phone number with 7-9 and remaining 9 digits with 0-9" required>
                </td>
            </tr>
            
            <tr>
                <td colspan="2">
                    <div align="center">
                        <input type="submit" name="btn_update" id="btn_update" value="Update Profile" />
                    </div>
                </td>
            </tr>
        </table>
        
        <div style="text-align: center; margin: 20px 0;">
       <a href="ChangePassword.php" style="color: #10b582; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
        <i class="fas fa-lock"></i> Change Password
        </a>
       </div>
        
        <div class="form-footer">
            <a href="MyProfile.php"><i class="fas fa-arrow-left"></i> Back to Profile</a>
        </div>
    </form>
</div>

<script>
// Optional: Add preview functionality when new photo is selected
document.getElementById('file_photo').addEventListener('change', function(e) {
    if (e.target.files.length > 0) {
        const reader = new FileReader();
        reader.onload = function(event) {
            document.querySelector('.round-photo').src = event.target.result;
        };
        reader.readAsDataURL(e.target.files[0]);
    }
});
</script>

</body>
</html>