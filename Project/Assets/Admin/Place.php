<?php
include("../Assets/Connection/Connection.php");
$id="";
if(isset($_POST["btn_save"]))
{
	$id=$_POST['txt_id'];
	$disid=$_POST["seldistrict"];
	$placename=$_POST["txtplace"];
	if($id=="")
	{
	$ins="insert into tbl_place(district_id,place_name)values('".$disid."','".$placename."')";
	if($con->query($ins))
	{
		?>
        <script>
		alert("Record inserted");
		window.location="Place.php";
		</script>
        <?php
	}
	}
	else
	{
		$upqry="update tbl_place set district_id='".$disid."',place_name='".$placename."' where place_id='".$id."'";
		if($con->query($upqry))
		{
			?>
			<script>
			alert("Record Updated");
			window.location="District.php";
			</script>
			<?php
		}
	}
}
if(isset($_GET['did']))
{
	$delqry="delete from tbl_place where place_id='".$_GET['did']."'";
	if($con->query($delqry))
	{
		?>
        <script>
		alert("Deleted");
		window.location="Place.php";
		</script>
        <?php
	}
}
$disid="";
$placeid="";
$placename="";

if(isset($_GET['eid']))
{
	$sel="select * from tbl_place where place_id='".$_GET['eid']."'";
	$res=$con->query($sel);
	$data=$res->fetch_assoc();
	$disid=$data['district_id'];
	$placeid=$data['place_id'];
	$placename=$data['place_name'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Management System</title>
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
            padding: 30px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(to right, #4b6cb7, #182848);
            color: white;
            padding: 25px;
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
            background: #f9fafc;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 40px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }
        
        .form-title {
            font-size: 22px;
            color: #4b6cb7;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e6ef;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        @media (min-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #444;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: var(--transition);
        }
        
        .form-group input:focus,
        .form-group select:focus {
            border-color: #4b6cb7;
            box-shadow: 0 0 0 3px rgba(75, 108, 183, 0.2);
            outline: none;
        }
        
        .button-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
        }
        
        .btn {
            padding: 14px 25px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            min-width: 120px;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-save {
            background: linear-gradient(to right, #4b6cb7, #182848);
            color: white;
        }
        
        .btn-cancel {
            background: #6c757d;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .table-container {
            overflow-x: auto;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th, .data-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e0e6ef;
        }
        
        .data-table th {
            background-color: #4b6cb7;
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
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
            color: #6c757d;
            font-style: italic;
            padding: 20px;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar-space {
                width: 80px;
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
            
            .container {
                border-radius: 8px;
            }
            
            .content {
                padding: 20px;
            }
            
            .form-section {
                padding: 20px;
            }
            
            .data-table {
                font-size: 14px;
            }
            
            .action-link {
                padding: 6px 10px;
                font-size: 12px;
                margin-bottom: 5px;
                display: inline-flex;
            }
            
            .button-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            .content-area {
                padding: 20px;
            }
        }

        @media (max-width: 576px) {
            .action-cell {
                display: flex;
                flex-direction: column;
                gap: 8px;
            }
            
            .action-link {
                margin-right: 0;
                justify-content: center;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-section, .table-container {
            animation: fadeIn 0.5s ease;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="sidebar-space">
<?php include 'Sidebar.php'?>
        </div>
        
        <!-- Content area -->
        <div class="content-area">
            <div class="container">
                <div class="header">
                    <h1><i class="fas fa-map-marker-alt"></i> Place Management</h1>
                    <p>Add, edit, and manage places in the system</p>
                </div>
                
                <div class="content">
                    <div class="form-section">
                        <h2 class="form-title"><i class="fas fa-plus-circle"></i> Place Form</h2>
                        <form method="post" action="Place.php">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="seldistrict"><i class="fas fa-globe"></i> District</label>
                                    <select name="seldistrict" id="seldistrict" required>
                                        <option value="">-- Select District --</option>
                                        <?php
                                        $sel = "select * from tbl_district";
                                        $res = $con->query($sel);
                                        while($data = $res->fetch_assoc()) {
                                        ?>
                                        <option
                                        <?php if($disid == $data['district_id']) {
                                            echo "selected";   
                                        } ?> 
                                        value="<?php echo $data['district_id']?>"><?php echo $data['district_name']?>
                                        </option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="place"><i class="fas fa-map-pin"></i> Place Name</label>
                                    <input name="txt_id" type="hidden" value="<?php echo $placeid ?>"/>
                                    <input required type="text" name="txtplace" id="place" 
                                           value="<?php echo $placename ?>" placeholder="Enter place name"/>
                                </div>
                            </div>
                            
                            <div class="button-group">
                                <button type="submit" name="btn_save" class="btn btn-save">
                                    <i class="fas fa-save"></i> Save</button>
                                <button type="reset" name="btn_cancel" class="btn btn-cancel">Cancel</button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="table-container">
                        <h2 class="form-title"><i class="fas fa-list"></i> Place List</h2>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>SL.NO</th>
                                    <th>District</th>
                                    <th>Place</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sel = "select * from tbl_place p
                                inner join tbl_district d
                                on p.district_id = d.district_id";
                                $res = $con->query($sel);
                                $i = 1;
                                
                                if ($res->num_rows > 0) {
                                    while($data = $res->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td><?php echo $i ?></td>
                                    <td><?php echo $data['district_name']?></td>
                                    <td><?php echo $data['place_name']?></td>
                                    <td class="action-cell">
                                        <a href="Place.php?did=<?php echo $data['place_id'];?>" class="action-link delete-link"
                                           onclick="return confirm('Are you sure you want to delete this place?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                        <a href="Place.php?eid=<?php echo $data['place_id'];?>" class="action-link edit-link">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                                <?php
                                        $i = $i + 1;
                                    }
                                } else {
                                ?>
                                <tr>
                                    <td colspan="4" class="empty-row">No places found. Please add some places.</td>
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
        // Simple animation for form elements
        document.addEventListener('DOMContentLoaded', function() {
            const formInputs = document.querySelectorAll('input, select');
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