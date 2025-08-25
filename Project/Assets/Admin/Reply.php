<?php
include("../Assets/Connection/Connection.php");

if(isset($_POST['btn_reply'])) {
    // Handle reply without resolving
    $upQry = "update tbl_complaint set complaint_reply='".$_POST['txt_reply']."', complaint_status=2 where complaint_id='".$_GET['rid']."'";
    if($con->query($upQry)) {
        echo "<script>alert('Reply sent'); window.location='Reply.php?rid=".$_GET['rid']."';</script>";
    }
}

if(isset($_POST['btn_resolve'])) {
    // Handle reply and mark as resolved
    $upQry = "update tbl_complaint set complaint_reply='".$_POST['txt_reply']."', complaint_status=1 where complaint_id='".$_GET['rid']."'";
    if($con->query($upQry)) {
        echo "<script>alert('Complaint resolved'); window.location='Reply.php?rid=".$_GET['rid']."';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaint Management</title>
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
            max-width: 800px;
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
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .content {
            padding: 30px;
        }
        
        .complaint-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid #4b6cb7;
        }
        
        .complaint-info h3 {
            color: #4b6cb7;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 10px;
        }
        
        .info-label {
            font-weight: 600;
            width: 120px;
            color: #495057;
        }
        
        .info-value {
            flex: 1;
            color: #212529;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-replied {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .status-resolved {
            background-color: #d4edda;
            color: #155724;
        }
        
        .form-section {
            margin-bottom: 25px;
        }
        
        .form-section h3 {
            color: #4b6cb7;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
        }
        
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 16px;
            resize: vertical;
            min-height: 120px;
            transition: var(--transition);
        }
        
        textarea:focus {
            outline: none;
            border-color: #4b6cb7;
            box-shadow: 0 0 0 2px rgba(75, 108, 183, 0.2);
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
        }
        
        .btn {
            padding: 12px 20px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-reply {
            background: linear-gradient(to right, #4b6cb7, #182848);
            color: white;
        }
        
        .btn-reply:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .btn-resolve {
            background: linear-gradient(to right, #28a745, #20c997);
            color: white;
        }
        
        .btn-resolve:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 6px;
            text-align: center;
            font-weight: 500;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .info-note {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 6px;
            font-size: 14px;
            color: #495057;
            border-left: 4px solid #4b6cb7;
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
            
            .action-buttons {
                flex-direction: column;
            }
            
            .info-row {
                flex-direction: column;
                margin-bottom: 15px;
            }
            
            .info-label {
                width: 100%;
                margin-bottom: 5px;
            }
            
            .content-area {
                padding: 20px;
            }
            
            .content {
                padding: 20px;
            }
        }

        @media (max-width: 576px) {
            .container {
                border-radius: 8px;
            }
            
            .header {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 20px;
            }
            
            .complaint-info {
                padding: 15px;
            }
            
            .btn {
                padding: 10px 15px;
                font-size: 14px;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .complaint-info, .form-section {
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
                    <h1><i class="fas fa-headset"></i> Complaint Management</h1>
                    <p>Respond to user complaints and track resolution status</p>
                </div>
                
                <div class="content">
                    <?php
                    // Fetch complaint details for display
                    if(isset($_GET['rid'])) {
                        $complaint_id = $_GET['rid'];
                        $complaintQry = "SELECT * FROM tbl_complaint c INNER JOIN tbl_user u ON c.user_id=u.user_id WHERE complaint_id='$complaint_id'";
                        $complaintRes = $con->query($complaintQry);
                        
                        if($complaintRes && $complaintRes->num_rows > 0) {
                            $complaintData = $complaintRes->fetch_assoc();
                    ?>
                    <div class="complaint-info">
                        <h3><i class="fas fa-info-circle"></i> Complaint Details</h3>
                        <div class="info-row">
                            <div class="info-label">From:</div>
                            <div class="info-value"><?php echo $complaintData['user_name']; ?> (<?php echo $complaintData['user_email']; ?>)</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Subject:</div>
                            <div class="info-value"><?php echo $complaintData['complaint_title']; ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Message:</div>
                            <div class="info-value"><?php echo $complaintData['complaint_content']; ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Date:</div>
                            <div class="info-value"><?php echo $complaintData['complaint_date']; ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Status:</div>
                            <div class="info-value">
                                <span class="status-badge <?php 
                                    if($complaintData['complaint_status'] == 0) echo 'status-pending'; 
                                    else if($complaintData['complaint_status'] == 1) echo 'status-resolved';
                                    else echo 'status-replied';
                                ?>">
                                    <i class="fas <?php 
                                        if($complaintData['complaint_status'] == 0) echo 'fa-clock'; 
                                        else if($complaintData['complaint_status'] == 1) echo 'fa-check-circle';
                                        else echo 'fa-reply';
                                    ?>"></i>
                                    <?php 
                                    if($complaintData['complaint_status'] == 0) echo 'Pending'; 
                                    else if($complaintData['complaint_status'] == 1) echo 'Resolved';
                                    else echo 'Replied';
                                    ?>
                                </span>
                            </div>
                        </div>
                        <?php if(!empty($complaintData['complaint_reply'])): ?>
                        <div class="info-row">
                            <div class="info-label">Previous Reply:</div>
                            <div class="info-value"><?php echo $complaintData['complaint_reply']; ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php
                        }
                    }
                    ?>
                    
                    <div class="form-section">
                        <h3><i class="fas fa-reply"></i> Response</h3>
                        <form id="responseForm" method="post" action="">
                            <div class="form-group">
                                <label for="txt_reply">Your Response:</label>
                                <textarea name="txt_reply" id="txt_reply" required placeholder="Type your response to the user here..."></textarea>
                            </div>
                            
                            <div class="action-buttons">
                                <button type="submit" name="btn_reply" class="btn btn-reply">
                                    <i class="fas fa-paper-plane"></i> Send Reply
                                </button>
                                <button type="submit" name="btn_resolve" class="btn btn-resolve">
                                    <i class="fas fa-check-circle"></i> Mark as Resolved
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="info-note">
                        <p><strong>Note:</strong> Sending a reply will update the user complaint as "Replied". Marking as resolved will close the complaint with status "Resolved".</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-resize textarea as user types
        const textarea = document.getElementById('txt_reply');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
        // Handle form submission for different actions
        document.getElementById('responseForm').addEventListener('submit', function(e) {
            const submitBtn = e.submitter;
            
            if(submitBtn.name === 'btn_resolve') {
                if(!confirm("Are you sure you want to mark this complaint as resolved? This will close the complaint.")) {
                    e.preventDefault();
                }
            }
        });
    </script>
</body>
</html>