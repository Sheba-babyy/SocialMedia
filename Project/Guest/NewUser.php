<?php
include("../Assets/Connection/Connection.php");
if(isset($_POST['btn_submit']))
{
    $name = $_POST['txt_name'];
    $gender = $_POST['rbtn_gender'];
    $email = $_POST['txt_email'];
    $contact = $_POST['txt_contact'];
    $dob = $_POST['txt_date'];
    $dis = $_POST['sel_district'];
    $place = $_POST['sel_place'];
    $pass = $_POST['txt_password'];
    $repass = $_POST['txt_repassword'];
    $photo = $_FILES['file_photo']['name'];
    $temp = $_FILES['file_photo']['tmp_name'];
    move_uploaded_file($temp,"../Assets/Files/UserDocs/".$photo);
    
    $hashedPassword = password_hash($pass, PASSWORD_DEFAULT);
    
    $insqry="insert into tbl_user(user_name,user_gender,user_status,user_dob,user_contact,user_email,user_password,place_id,user_photo)values('".$name."','".$gender."','active','".$dob."','".$contact."','".$email."','".$hashedPassword."','".$place."','".$photo."')";
    if($con->query($insqry))
    {
        ?>
        <script>
        alert("Account created successfully!");
        window.location="Login.php";
        </script>
        <?php
    }
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Sign Up - Nexo</title>
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
        /* background-color: var(--dark-bg); */
        background: url("../../Docs/img/signin.png") no-repeat center center fixed;
        background-size: cover;
        color: var(--text-light);
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 20px;
        overflow-x: hidden;
    }

    .form-container {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border-radius: 20px;
        width: 100%;
        max-width: 600px;
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
        font-size: 2.5rem;
        margin-bottom: 10px;
        color: var(--text-light);
    }
    
    .form-header p {
        color: var(--text-subtle);
        font-size: 1rem;
    }
    
    .profile-upload {
        margin: 0 auto 25px;
        position: relative;
        width: 100px;
        height: 100px;
    }
    
    .profile-preview {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 2px solid var(--accent-red);
        background-color: rgba(0, 0, 0, 0.3);
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
        margin: 0 auto;
    }
    
    .profile-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: none;
    }
    
    .profile-preview i {
        font-size: 36px;
        color: var(--text-subtle);
    }
    
    .upload-btn {
        position: absolute;
        bottom: 0;
        right: 0;
        background-color: var(--accent-red);
        color: var(--text-light);
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        transition: transform 0.2s ease;
    }
    
    .upload-btn:hover {
        transform: scale(1.1);
    }
    
    .upload-btn i {
        font-size: 14px;
    }
    
    #file_photo {
        display: none;
    }
    
    .form-group {
        margin-bottom: 20px;
        text-align: left;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: var(--text-light);
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
    
    .radio-group {
        display: flex;
        gap: 20px;
    }
    
    .radio-option {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-light);
    }
    
    input[type="radio"] {
        accent-color: var(--accent-red);
    }

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
        display: none;
        transition: color 0.2s;
    }

    .password-wrapper i:hover {
        color: var(--accent-red);
    }

    /* Hide default password icons */
    input[type="password"]::-ms-reveal,
    input[type="password"]::-ms-clear {
        display: none;
    }

    input[type="password"]::-webkit-credentials-auto-fill-button,
    input[type="password"]::-webkit-clear-button,
    input[type="password"]::-webkit-inner-spin-button {
        display: none !important;
    }

    select.form-control {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23AFAFAF'%3e%3cpath d='M7 10l5 5 5-5z'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 15px center;
        background-size: 15px;
    }

    .btn-group {
        display: flex;
        gap: 15px;
        margin-top: 30px;
    }
    
    .btn {
        flex: 1;
        padding: 12px;
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
        <h1>Create Your Account</h1>
        <p>Join Nexo to connect with friends and share your moments</p>
    </div>
    
    <div class="profile-upload">
        <div class="profile-preview" id="profilePreview">
            <i class="fas fa-user"></i>
            <img id="previewImage" src="#" alt="Profile preview">
        </div>
        <label class="upload-btn" for="file_photo">
            <i class="fas fa-camera"></i>
        </label>
        <input type="file" name="file_photo" id="file_photo" accept="image/*"/>
    </div>
    
    <form action="" method="post" enctype="multipart/form-data" name="form1" id="form1" onSubmit="return validateForm()">
        <div class="form-group">
            <label for="txt_name">Full Name</label>
            <input type="text" class="form-control" name="txt_name" id="txt_name" placeholder="Enter Your Name" required title="Name Allows Only Alphabets,Spaces and First Letter Must Be Capital Letter" pattern="^[A-Z]+[a-zA-Z ]*$"/>
        </div>
        
        <div class="form-group">
            <label for="txt_email">Email</label>
            <input type="email" class="form-control" name="txt_email" id="txt_email" placeholder="Enter Your Email" required/>
        </div>
        
        <div class="form-group">
            <label for="txt_contact">Contact</label>
            <input type="text" class="form-control" name="txt_contact" id="txt_contact" placeholder="Enter Your Contact" required pattern="[7-9]{1}[0-9]{9}" title="Phone number with 7-9 and remaing 9 digit with 0-9"/>
        </div>
        
        <div class="form-group">
            <label for="txt_bio">Bio</label>
            <input type="text" class="form-control" name="txt_bio" id="txt_bio" placeholder="Tell us about yourself"/>
        </div>
        
        <div class="form-group">
            <label>Gender</label>
            <div class="radio-group">
                <div class="radio-option">
                    <input type="radio" name="rbtn_gender" id="rbtn_gender_m" value="M" required/>
                    <label for="rbtn_gender_m">Male</label>
                </div>
                <div class="radio-option">
                    <input type="radio" name="rbtn_gender" id="rbtn_gender_f" value="F"/>
                    <label for="rbtn_gender_f">Female</label>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label for="txt_date">Date of Birth</label>
            <input type="date" class="form-control" name="txt_date" id="txt_date" required/>
        </div>
        
        <div class="form-group">
            <label for="sel_district">District</label>
            <select class="form-control" name="sel_district" id="sel_district" onChange="getPlace(this.value)" required>
                <option value="">----Select District----</option>
                <?php
                $sel="select * from tbl_district";
                $res=$con->query($sel);
                while($data=$res->fetch_assoc()) {
                ?>
                <option value="<?php echo $data['district_id']?>"><?php echo $data['district_name'] ?></option>
                <?php } ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="sel_place">Place</label>
            <select class="form-control" name="sel_place" id="sel_place" required>
                <option value="">----Select Place----</option>
            </select>
        </div>
        
        <div class="form-group password-group">
            <label for="txt_password">Password</label>
            <div class="password-wrapper">
                <input type="password" class="form-control" name="txt_password" id="txt_password" placeholder="Enter Your Password" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters"/>
                <i class="fa-regular fa-eye" id="togglePassword"></i>
            </div>
        </div>

        <div class="form-group password-group">
            <label for="txt_repassword">Confirm Password</label>
            <div class="password-wrapper">
                <input type="password" class="form-control" name="txt_repassword" id="txt_repassword" placeholder="Confirm Your Password" required/>
                <i class="fa-regular fa-eye" id="toggleRePassword"></i>
            </div>
        </div>
        
        <div class="btn-group">
            <input type="submit" class="btn btn-primary" name="btn_submit" id="submit" value="Create Account" />
            <input type="reset" class="btn btn-secondary" name="btn_cancel" id="cancel" value="Cancel" />
        </div>
        
    </form>
</div>

<script>

function setupPasswordToggle(inputId, toggleId) {
    const passwordInput = document.getElementById(inputId);
    const toggleIcon = document.getElementById(toggleId);

    // Show icon only when user types
    passwordInput.addEventListener("input", function() {
        toggleIcon.style.display = this.value.length > 0 ? "block" : "none";
    });

   // Toggle visibility
    toggleIcon.addEventListener("click", function() {
    const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
    passwordInput.setAttribute("type", type);

    if (type === "text") {
        this.classList.remove("fa-eye");
        this.classList.add("fa-eye-slash");
    } else {
        this.classList.remove("fa-eye-slash");
        this.classList.add("fa-eye");
    }
});
}

// Apply to both password fields
setupPasswordToggle("txt_password", "togglePassword");
setupPasswordToggle("txt_repassword", "toggleRePassword");


function validateForm() {
    var password = document.getElementById("txt_password").value;
    var repassword = document.getElementById("txt_repassword").value;

    if (password !== repassword) {
        alert("Passwords do not match.");
        document.getElementById("txt_repassword").focus();
        return false;
    }
    return true;
}

// Profile image preview
document.getElementById('file_photo').addEventListener('change', function(e) {
    const preview = document.getElementById('previewImage');
    const previewContainer = document.getElementById('profilePreview');
    const icon = previewContainer.querySelector('i');
    
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            icon.style.display = 'none';
        }
        
        reader.readAsDataURL(file);
    }
});
</script>

<script src="../Assets/JQ/JQuery.js"></script> 
<script>
    function getPlace(did) {
        $.ajax({
            url:"../Assets/AjaxPages/AjaxPlace.php?did="+did,
            success: function(html){
                $("#sel_place").html(html);
            }
        });
    }
</script>
</body>
</html>