<?php
include("../Assets/Connection/Connection.php");
session_start();
include("Header.php");

if(isset($_POST['btn_submit']))
{
    $title=$_POST['txt_complaint_title'];
    $content=$_POST['txt_complaint_details'];
    
    $insQry="insert into tbl_complaint(complaint_title,complaint_content,complaint_date,user_id)values('".$title."','".$content."',curDate(),'".$_SESSION['uid']."')";
    if($con->query($insQry))
    {
        ?>
        <script>
        alert("Complaint Submitted");
        window.location="Complaint.php";
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
    <title>Complaint</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --bg-dark: #121212;
            --bg-card: #1e1e1e;
            --bg-hover: #2a2a2a;
            --primary-color: #4CAF50;
            --primary-hover: #3e8e41;
            --accent-color: #2196F3;
            --accent-hover: #0b7dda;
            --danger-color: #f44336;
            --danger-hover: #d32f2f;
            --text-primary: #e0e0e0;
            --text-secondary: #b0b0b0;
            --border-color: #333333;
            --border-radius: 8px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            --input-bg: #2d2d2d;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: var(--bg-dark);
            color: var(--text-primary);
            line-height: 1.6;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 100px auto;
        }
        
        .card {
            background-color: var(--bg-card);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
        }
        
        .heading {
            color: var(--primary-color);
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        th {
            background-color: var(--bg-hover);
            color: var(--text-primary);
            padding: 15px;
            text-align: left;
            font-weight: 500;
        }
        
        td {
            padding: 15px;
            text-align: left;
            color: var(--text-primary);
        }
        
        tr:hover {
            background-color: var(--bg-hover);
        }
        
        input[type="text"], textarea {
            width: 100%;
            padding: 12px;
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
            background-color: var(--input-bg);
            color: var(--text-primary);
            font-size: 16px;
        }
        
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        input[type="submit"] {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        input[type="submit"]:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
        }
        
        .status-pending {
            color: #FFC107;
        }
        
        .status-resolved {
            color: var(--primary-color);
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--text-secondary);
        }
        
        .empty-state i {
            font-size: 50px;
            margin-bottom: 20px;
            color: var(--border-color);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="heading"><i class="fas fa-exclamation-circle"></i> File a Complaint</h1>
        
        <div class="card">
            <form id="form1" name="form1" method="post" action="">
                <table>
                    <tr>
                        <td width="30%">Complaint Title</td>
                        <td>
                            <input type="text" name="txt_complaint_title" id="txt_complaint_title" required placeholder="Enter complaint title"/>
                        </td>
                    </tr>
                    
                    <tr>
                        <td>Complaint Details</td>
                        <td>
                            <textarea name="txt_complaint_details" id="txt_complaint_details" required placeholder="Describe your complaint in detail"></textarea>
                        </td>
                    </tr>
                    
                    <tr>
                        <td colspan="2" style="text-align: center;">
                            <input type="submit" name="btn_submit" id="btn_submit" value="Submit Complaint" />
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        
        <h1 class="heading"><i class="fas fa-history"></i> Complaint History</h1>
        
        <div class="card">
            <table>
                <tr>
                    <th>Complaint Title</th>
                    <th>Complaint Content</th>
                    <th>Complaint Reply</th>
                    <th>Status</th>
                    <th>Complaint Date</th>
                </tr>
                <?php
                $selQry="select * from tbl_complaint where user_id='".$_SESSION['uid']."'";
                $res=$con->query($selQry);
                if($res && $res->num_rows > 0) {
                    while($data=$res->fetch_assoc())
                    {
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($data['complaint_title']) ?></td>
                            <td><?php echo htmlspecialchars($data['complaint_content']) ?></td>
                            <td><?php echo htmlspecialchars($data['complaint_reply']) ?></td>
                            <td class="<?php echo $data['complaint_status'] == 0 ? 'status-pending' : 'status-resolved' ?>">
                                <?php echo $data['complaint_status'] == 0 ? "Pending" : "Resolved" ?>
                            </td>
                            <td><?php echo htmlspecialchars($data['complaint_date']) ?></td>
                        </tr>
                        <?php
                    }
                } else {
                    echo '<tr><td colspan="5" class="empty-state"><i class="fas fa-inbox"></i><h3>No Complaints Found</h3><p>You haven\'t filed any complaints yet.</p></td></tr>';
                }
                ?>
            </table>
        </div>
    </div>
</body>
</html>