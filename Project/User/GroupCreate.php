<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include '../Assets/Connection/Connection.php';

if(isset($_POST['btn_create']))
{
    $grp_name = mysqli_real_escape_string($con, $_POST['txt_grp_name']);
    $grp_description = mysqli_real_escape_string($con, $_POST['txt_description']);
    
    // Handle optional photo
    if(!empty($_FILES['file_grp_photo']['name'])) {
        $grp_photo = time()."_".basename($_FILES['file_grp_photo']['name']);
        $temp = $_FILES['file_grp_photo']['tmp_name'];
        move_uploaded_file($temp,"../Assets/Files/GroupDocs/".$grp_photo);
    } else {
        $grp_photo = "default.png"; // <-- give a default image
    }

    $insQry="INSERT INTO tbl_group(group_name,group_description,group_photo,user_id,group_status) 
             VALUES('$grp_name','$grp_description','$grp_photo','".$_SESSION['uid']."','active')";
    if($con->query($insQry)) {
    echo "<script>alert('Group Created'); window.location='Groups.php';</script>";
} else {
    echo "SQL Error: " . $con->error . "<br>";
    echo "Query: " . $insQry;
}

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Group | Social Network</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #1877f2;
            --primary-hover: #166fe5;
            --success: #42b72a;
            --success-hover: #36a420;
            --dark: #18191a;
            --dark-card: #242526;
            --dark-border: #3e4042;
            --dark-hover: #3a3b3c;
            --text-primary: #e4e6eb;
            --text-secondary: #b0b3b8;
            --card-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            --border-radius: 10px;
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: var(--dark);
            color: var(--text-primary);
            line-height: 1.6;
            padding: 0;
        }
        
        .container {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .create-card {
            background-color: var(--dark-card);
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            padding: 30px;
            margin-top: 20px;
            border: 1px solid var(--dark-border);
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--dark-border);
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            color: var(--text-primary);
        }
        
        .header i {
            color: var(--primary);
            font-size: 1.2em;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-primary);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border-radius: var(--border-radius);
            border: 1px solid var(--dark-border);
            background-color: var(--dark-card);
            color: var(--text-primary);
            font-size: 15px;
            transition: var(--transition);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(24, 119, 242, 0.2);
        }
        
        .form-control::placeholder {
            color: var(--text-secondary);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        /* Circular Avatar Upload Styles */
        .avatar-upload {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .avatar-edit {
            position: relative;
            margin-bottom: 20px;
        }
        
        .avatar-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 4px solid var(--dark-border);
            background-color: var(--dark-hover);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            transition: var(--transition);
        }
        
        .avatar-preview:hover {
            border-color: var(--primary);
        }
        
        .avatar-preview i {
            font-size: 60px;
            color: var(--text-secondary);
        }
        
        .avatar-preview-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
        }
        
        .avatar-upload input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .upload-label {
            display: block;
            text-align: center;
            color: var(--primary);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .upload-label:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }
        
        .btn-submit {
            background-color: var(--success);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            padding: 12px 25px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: block;
            width: 100%;
            font-size: 16px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        .btn-submit:hover {
            background-color: var(--success-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }
        
        .btn-submit:active {
            transform: translateY(0);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .create-card {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .avatar-preview {
                width: 120px;
                height: 120px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="create-card">
            <div class="header">
                <h1><i class="fas fa-users"></i> Create New Group</h1>
            </div>
            
            <form action="" method="post" enctype="multipart/form-data">
                <!-- Circular Avatar Upload -->
                <div class="avatar-upload">
                    <div class="avatar-edit">
                        <div class="avatar-preview">
                            <i class="fas fa-users"></i>
                            <img id="avatar-preview-image" class="avatar-preview-image" src="#" alt="Group Photo Preview" />
                        </div>
                        <input type="file" name="file_grp_photo" id="file_grp_photo" accept="image/*" />
                    </div>
                    <label for="file_grp_photo" class="upload-label">Choose Group Photo</label>
                </div>
                
                <div class="form-group">
                    <label for="txt_grp_name">Group Name</label>
                    <input type="text" name="txt_grp_name" id="txt_grp_name" class="form-control" placeholder="Enter group name" required />
                </div>
                
                <div class="form-group">
                    <label for="txt_description">Group Description</label>
                    <textarea name="txt_description" id="txt_description" class="form-control" placeholder="What's this group about?"></textarea>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="btn_create" class="btn-submit">
                        <i class="fas fa-plus-circle"></i> Create Group
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Avatar preview functionality
        document.getElementById('file_grp_photo').addEventListener('change', function(e) {
            const preview = document.getElementById('avatar-preview-image');
            const icon = document.querySelector('.avatar-preview i');
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.style.display = 'block';
                    preview.src = e.target.result;
                    icon.style.display = 'none';
                }
                
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
                preview.src = '#';
                icon.style.display = 'block';
            }
        });
        
        // Form submission animation
        document.querySelector('form').addEventListener('submit', function(e) {
            const btn = document.getElementById('btn_create');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
        });
    </script>
</body>
</html>