<?php
include("../Assets/Connection/Connection.php");
$admid="";
$admname="";
$admemail="";
$admpass="";
if(isset($_POST['btn_submit']))
{
	$admname=$_POST["txt_name"];
  $admemail=$_POST["txt_email"];
  $admpass=$_POST["txt_password"];
	$hashedPassword = password_hash($admpass, PASSWORD_DEFAULT); // Hash password

	if($admid == "")
	{
	$insqry="insert into tbl_admin(admin_name,admin_email,admin_password)values('".$admname."','".$admemail."','".$hashedPassword."')";
	if($con->query($insqry))
	{
		?>
        <script>
		alert("record saved");
		window.location="AdminReg.php";
		</script>
        <?php
	}
   }
   else
   {
	   $upqry="update tbl_admin set admin_name='".$admname."',admin_email='".$admemail."',admin_password='".$hashedPassword."' where admin_id='".$_POST['txt_id']."'";
	   if($con->query($upqry))
	   {
		?>
        <script>
		alert("Record updated");
		window.location="AdminReg.php";
		</script>
        <?php
	  }
   }
}
if(isset($_GET['did']))
{
	$delqry="delete from tbl_admin where admin_id='".$_GET['did']."'";
	if($con->query($delqry))
	{
		?>
        <script>
		alert("Record deleted");
		window.location="AdminReg.php";
		</script>
        <?php
	}
}

if(isset($_GET['eid']))
{
	$sel="select * from tbl_admin where admin_id='".$_GET['eid']."'";
	$res=$con->query($sel);
	$data=$res->fetch_assoc();
	$admid=$data['admin_id'];
	$admname=$data['admin_name'];
    $admemail=$data['admin_email'];
	$admpass=$data['admin_password'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee; 
            --success: #4cc9f0;
            --info: #4895ef;
            --warning: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --sidebar-width: 260px;
            --border-radius: 10px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fb;
            color: #495057;
            line-height: 1.6;
            display: flex;
            min-height: 100vh;
        }

        /* Main container with sidebar space */
        .main-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* Sidebar space (empty space for the sidebar) */
        .sidebar-space {
            width: var(--sidebar-width);
            min-height: 100vh;
            flex-shrink: 0;
            background: transparent;
        }

        /* Content area */
        .content-area {
            flex: 1;
            padding: 20px;
            /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(to right, #4b6cb7, #182848);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .content {
            padding: 30px;
        }
        
        .form-section {
            margin-bottom: 40px;
        }
        
        .form-title {
            font-size: 22px;
            color: #4b6cb7;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .input-group {
            margin-bottom: 20px;
        }
        
        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        
        .input-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: var(--transition);
        }
        
        .input-group input:focus {
            border-color: #4b6cb7;
            box-shadow: 0 0 0 3px rgba(75, 108, 183, 0.2);
            outline: none;
        }
        
        .password-container {
            position: relative;
        }
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
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #777;
        }
        
        .btn-submit {
            background: linear-gradient(to right, #4b6cb7, #182848);
            color: white;
            border: none;
            padding: 14px 25px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            display: block;
            margin: 20px auto 0;
            min-width: 150px;
        }
        
        .btn-submit:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .table-section {
            overflow-x: auto;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .data-table th, .data-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .data-table th {
            background-color: #4b6cb7;
            color: white;
            font-weight: 600;
        }
        
        .data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .data-table tr:hover {
            background-color: #f1f1f1;
        }
        
        .action-links a {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            margin-right: 8px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: var(--transition);
            gap: 6px;
        }
        
        .edit-link {
            background-color: rgba(255, 193, 7, 0.15);
            color: #ffc107;
        }
        
        .edit-link:hover {
            background-color: #ffc107;
            color: white;
        }
        
        .delete-link {
            background-color: rgba(220, 53, 69, 0.15);
            color: #dc3545;
        }
        
        .delete-link:hover {
            background-color: #dc3545;
            color: white;
        }
        
        .alert {
            padding: 12px 20px;
            margin: 20px 0;
            border-radius: 6px;
            font-weight: 500;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .pattern-hint {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            font-style: italic;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar-space {
                width: 80px;
            }
            
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
            }
            
            .sidebar-space {
                width: 100%;
                min-height: auto;
                display: none;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .container {
                border-radius: 8px;
            }
            
            .content {
                padding: 20px;
            }
            
            .content-area {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="sidebar-space">
            <?php include 'Sidebar.php' ?>
        </div>
        
        <!-- Content area -->
        <div class="content-area">
            <div class="container">
                <div class="header">
                    <h1><i class="fas fa-user-cog"></i> Admin Registration</h1>
                    <p>Manage administrator accounts for your system</p>
                </div>
                
                <div class="content">
                    <div class="form-section">
                        <h2 class="form-title">Admin Details</h2>
                        <form method="post" action="">
                            <div class="form-grid">
                                <div class="input-group">
                                    <label for="name"><i class="fas fa-user"></i> Full Name</label>
                                    <input type="hidden" name="txt_id" value="<?php echo $admid ?>" />
                                    <input type="text" name="txt_name" id="name" value="<?php echo $admname ?>" 
                                           required title="Name Allows Only Alphabets,Spaces and First Letter Must Be Capital Letter" 
                                           pattern="^[A-Z]+[a-zA-Z ]*$" placeholder="Enter admin full name"/>
                                    <p class="pattern-hint">Must start with a capital letter and contain only letters and spaces</p>
                                </div>
                                
                                <div class="input-group">
                                    <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                                    <input type="email" name="txt_email" id="email" value="<?php echo $admemail ?>" 
                                           required placeholder="Enter admin email address"/>
                                </div>
                                
                                <div class="input-group">
                                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                                    <div class="password-container">
                                        <input type="password" name="txt_password" id="password" value="<?php echo $admpass ?>" 
                                               required style="padding-right: 40px;" 
                                               pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" 
                                               title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters"
                                               placeholder="Enter a strong password"/>
                                        <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                                    </div>
                                    <p class="pattern-hint">Must contain uppercase, lowercase, number, and be at least 8 characters long</p>
                                </div>
                            </div>
                            
                            <button type="submit" name="btn_submit" class="btn-submit">
                                <i class="fas fa-paper-plane"></i> Submit
                            </button>
                        </form>
                    </div>
                    
                    <div class="table-section">
                        <h2 class="form-title">Registered Admins</h2>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Admin ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Password Hash</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sel = "select * from tbl_admin";
                                $res = $con->query($sel);
                                if ($res->num_rows > 0) {
                                    while($data = $res->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td><?php echo $data['admin_id'] ?></td>
                                    <td><?php echo $data['admin_name'] ?></td>
                                    <td><?php echo $data['admin_email'] ?></td>
                                    <td title="<?php echo $data['admin_password'] ?>">
                                        <?php echo substr($data['admin_password'], 0, 20) . '...' ?>
                                    </td>
                                    <td class="action-links">
                                        <a href="AdminReg.php?did=<?php echo $data['admin_id']; ?>" class="delete-link" 
                                           onclick="return confirm('Are you sure you want to delete this admin?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                        <a href="AdminReg.php?eid=<?php echo $data['admin_id']; ?>" class="edit-link">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                                <?php
                                    }
                                } else {
                                ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 20px; color: var(--gray);">No admin records found</td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
        
        // Form validation
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const nameInput = document.getElementById('name');
            const passwordInput = document.getElementById('password');
            
            // Name validation
            const namePattern = /^[A-Z]+[a-zA-Z ]*$/;
            if (!namePattern.test(nameInput.value)) {
                alert('Name must start with a capital letter and contain only letters and spaces.');
                nameInput.focus();
                e.preventDefault();
                return false;
            }
            
            // Password validation
            const passwordPattern = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/;
            if (!passwordPattern.test(passwordInput.value)) {
                alert('Password must contain at least one number, one uppercase and lowercase letter, and be at least 8 characters long.');
                passwordInput.focus();
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>