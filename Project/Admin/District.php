<?php
include ("../Assets/Connection/Connection.php");
$id="";
$districtname="";
if(isset($_POST['btnsubmit']))
{
	$districtname=$_POST["txtdistrict"];
	$id=$_POST['txt_id'];
	
	if($id=="")
	{
		$insqry="insert into tbl_district(district_name)values('".$districtname."')";
		if($con->query($insqry))
		{
			?>
			<script>
			window.location="District.php";
			</script>
			<?php
		}
	}
	else
	{
		$upqry="update tbl_district set district_name='".$districtname."' where district_id='".$id."'";
		if($con->query($upqry))
		{
			?>
			<script>
			window.location="District.php";
			</script>
			<?php
		}
	}
}


if(isset($_GET['did']))
{
	$delqry="delete from tbl_district where district_id='".$_GET['did']."'";
	if($con->query($delqry))
	{
		?>
        <script>
		alert("Deleted");
		window.location="District.php";
		</script>
        <?php
	}
}
$disid="";
$disname="";
if(isset($_GET['eid']))
{
	$sel="select * from tbl_district where district_id='".$_GET['eid']."'";
	$res=$con->query($sel);
	$data=$res->fetch_assoc();
	
	$disid=$data['district_id'];
	$disname=$data['district_name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>District Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
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
        }

        /* Content area */
        .content-area {
            flex: 1;
            padding-left: 25px;
        }

        /* Header */
        .admin-header {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
            padding: 20px 30px;
            /* border-radius: var(--border-radius); */
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-header h1 {
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .admin-header .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        /* Breadcrumb */
        .breadcrumb {
            padding: 15px 20px;
            background: #f8f9fa;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--gray);
            font-size: 14px;
        }

        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
        }

        /* Content */
        .admin-content {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--box-shadow);
        }

        /* Stats */
        .stats-container {
            display: flex;
            justify-content:flex-end;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--box-shadow);
            text-align: center;
            border-left: 4px solid var(--primary);
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 8px;
        }

        .stat-label {
            color: var(--gray);
            font-size: 14px;
        }

        /* Form Section */
        .form-section {
            background: #f9fafc;
            padding: 25px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .form-title {
            font-size: 20px;
            color: var(--dark);
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .btn-submit {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            flex-direction:center;
            align-items: center;
            gap: 8px;
        }

        .btn-submit:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        /* Table Section */
        .table-section {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .table-header {
            padding: 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-title {
            font-size: 20px;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th, .data-table td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .data-table th {
            background-color: #f8f9fa;
            color: var(--dark);
            font-weight: 600;
        }

        .data-table tr:nth-child(even) {
            background-color: #f9fafc;
        }

        .data-table tr:hover {
            background-color: #f1f5f9;
        }

        .action-cell {
            white-space: nowrap;
        }

        .action-link {
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

        .empty-row {
            text-align: center;
            color: var(--gray);
            font-style: italic;
            padding: 30px;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar-space {
                width: 80px;
            }
            
            .stats-container {
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
                display: none; /* Hide sidebar space on mobile */
            }
            
            .admin-header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .data-table {
                font-size: 14px;
            }
            
            .action-cell {
                display: flex;
                flex-direction: column;
                gap: 8px;
            }
            
            .action-link {
                margin-right: 0;
                justify-content: center;
            }
            
            .content-area {
                padding: 15px;
            }
            
            .admin-content {
                padding: 20px;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-section, .table-section {
            animation: fadeIn 0.5s ease;
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
            <!-- Header -->
            <div class="admin-header">
                <h1><i class="fas fa-map-marked-alt"></i> District Management</h1>
                <div class="user-info">
                    <div class="user-avatar">A</div>
                    <div>
                        <div style="font-weight: 500;">Administrator</div>
                        <div style="font-size: 13px; opacity: 0.8;">Admin Account</div>
                    </div>
                </div>
            </div>

            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <a href="HomePage.php?page=home"><i class="fas fa-home"></i> Dashboard</a>
                <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
                <span>District Management</span>
            </div>

            <!-- Content -->
            <div class="admin-content">

                <!-- Form Section -->
                <div class="form-section">
                    <h2 class="form-title"><i class="fas fa-plus-circle"></i> District Form</h2>
                    <form method="post" action="">
                        <div class="form-group">
                            <label for="txtdistrict" class="form-label">
                                <i class="fas fa-city"></i> District Name
                            </label>
                            <input name="txt_id" type="hidden" value="<?php echo $disid ?>"/>
                            <input required type="text" name="txtdistrict" id="txtdistrict" 
                                   value="<?php echo $disname ?>" placeholder="Enter district name" class="form-control"/>
                        </div>
                        
                        <button type="submit" name="btnsubmit" class="btn-submit">
                            <i class="fas fa-paper-plane"></i> Submit
                        </button>
                    </form>
                </div>
                
                <!-- Table Section -->
                <div class="table-section">
                    <div class="table-header">
                        <h2 class="table-title"><i class="fas fa-list"></i> District List</h2>
                        <div style="color: var(--gray); font-size: 14px;">
                            <?php
                            $countQuery = "SELECT COUNT(*) as total FROM tbl_district";
                            $countResult = $con->query($countQuery);
                            echo $countResult->fetch_assoc()['total'] . ' districts found';
                            ?>
                        </div>
                    </div>
                    
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>District ID</th>
                                <th>District Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sel = "select * from tbl_district";
                            $res = $con->query($sel);
                            if ($res->num_rows > 0) {
                                while($data = $res->fetch_assoc()) {
                            ?>
                            <tr>
                                <td><?php echo $data['district_id']  ?></td>
                                <td><?php echo $data['district_name'] ?></td>
                                <td class="action-cell">
                                    <a href="District.php?did=<?php echo $data['district_id'];?>" class="action-link delete-link"
                                       onclick="return confirm('Are you sure you want to delete this district?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                    <a href="District.php?eid=<?php echo $data['district_id'];?>" class="action-link edit-link">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </td>
                            </tr>
                            <?php
                                }
                            } else {
                            ?>
                            <tr>
                                <td colspan="3" class="empty-row">No districts found. Please add some districts.</td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Simple animation for form elements
        document.addEventListener('DOMContentLoaded', function() {
            const formInputs = document.querySelectorAll('.form-control');
            formInputs.forEach(input => {
                input.addEventListener('focus', () => {
                    input.parentElement.style.transform = 'translateY(-5px)';
                    input.parentElement.style.transition = 'transform 0.3s ease';
                });
                
                input.addEventListener('blur', () => {
                    input.parentElement.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>