<?php
include("../Assets/Connection/Connection.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>View Complaints</title>
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
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
    }
    
    .header p {
        font-size: 16px;
        opacity: 0.9;
    }
    
    .content {
        padding: 30px;
    }
    
    .controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .search-box {
        display: flex;
        align-items: center;
        background: #f9fafc;
        padding: 12px 15px;
        border-radius: 8px;
        width: 300px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        border: 1px solid #e9ecef;
    }
    
    .search-box input {
        border: none;
        background: transparent;
        padding: 5px 10px;
        width: 100%;
        font-size: 16px;
        outline: none;
    }
    
    .stats {
        display: flex;
        gap: 15px;
    }
    
    .stat-box {
        background: #f9fafc;
        padding: 12px 20px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        border: 1px solid #e9ecef;
    }
    
    .stat-box .number {
        font-size: 20px;
        font-weight: bold;
        color: #4b6cb7;
    }
    
    .stat-box .label {
        font-size: 14px;
        color: #666;
    }
    
    .table-container {
        overflow-x: auto;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        margin-top: 20px;
        background: white;
    }
    
    .data-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1000px;
    }
    
    .data-table th, .data-table td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #e9ecef;
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
    
    .user-photo {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #e9ecef;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 16px;
        border-radius: 6px;
        background-color: #4b6cb7;
        color: white;
        text-decoration: none;
        transition: var(--transition);
        font-weight: 500;
        gap: 6px;
    }
    
    .action-btn:hover {
        background-color: #3a5a9b;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .complaint-content {
        max-width: 250px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .complaint-content:hover {
        white-space: normal;
        overflow: visible;
        position: absolute;
        background: white;
        padding: 10px;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10;
        max-width: 400px;
    }
    
    .empty-row {
        text-align: center;
        color: #6c757d;
        font-style: italic;
        padding: 40px;
    }
    
    .empty-row i {
        font-size: 48px;
        margin-bottom: 15px;
        color: #dee2e6;
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
        
        .controls {
            flex-direction: column;
            align-items: stretch;
        }
        
        .search-box {
            width: 100%;
        }
        
        .stats {
            justify-content: space-around;
        }
        
        .data-table {
            font-size: 14px;
        }
        
        .data-table th, .data-table td {
            padding: 12px;
        }
        
        .content-area {
            padding: 20px;
        }
        
        .content {
            padding: 20px;
        }
    }

    @media (max-width: 576px) {
        .header {
            padding: 20px;
        }
        
        .header h1 {
            font-size: 24px;
        }
        
        .stat-box {
            padding: 10px 15px;
        }
        
        .stat-box .number {
            font-size: 18px;
        }
        
        .action-btn {
            padding: 8px 12px;
            font-size: 14px;
        }
        
        .user-photo {
            width: 50px;
            height: 50px;
        }
    }

    /* Animation */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .table-container {
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
            <div class="container">
                <div class="header">
                    <h1><i class="fas fa-exclamation-circle"></i> Complaint Management</h1>
                    <p>View and manage all user complaints in the system</p>
                </div>
                
                <div class="content">
                    <div class="controls">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="search" placeholder="Search complaints..." id="searchInput">
                        </div>
                        
                        <div class="stats">
                            <div class="stat-box">
                                <div class="number">
                                    <?php
                                    $countQry = "SELECT COUNT(*) as count FROM tbl_complaint";
                                    $countRes = $con->query($countQry);
                                    $countData = $countRes->fetch_assoc();
                                    echo $countData['count'];
                                    ?>
                                </div>
                                <div class="label">Total Complaints</div>
                            </div>
                            <div class="stat-box">
                                <div class="number">
                                    <?php
                                    $pendingQry = "SELECT COUNT(*) as count FROM tbl_complaint WHERE complaint_status = 0";
                                    $pendingRes = $con->query($pendingQry);
                                    $pendingData = $pendingRes->fetch_assoc();
                                    echo $pendingData['count'];
                                    ?>
                                </div>
                                <div class="label">Pending</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>SL NO.</th>
                                    <th>Photo</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Complaint Title</th>
                                    <th>Complaint Content</th>
                                    <th>Complaint Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $selQry="select * from tbl_complaint c inner join tbl_user u on c.user_id=u.user_id";
                                $res=$con->query($selQry);
                                $i=1;
                                
                                if($res && $res->num_rows > 0) {
                                    while($data=$res->fetch_assoc())
                                    {
                                ?>
                                <tr>
                                    <td><?php echo $i++ ?></td>
                                    <td>
                                        <img src="../Assets/Files/UserDocs/<?php echo ($data['user_photo']?:'default.avif')?>" 
                                             alt="User Photo" class="user-photo"
                                             onerror="this.src='../Assets/Files/UserDocs/default.avif'">
                                    </td>
                                    <td><?php echo $data['user_name'] ?></td>
                                    <td><?php echo $data['user_email'] ?></td>
                                    <td><?php echo $data['complaint_title'] ?></td>
                                    <td>
                                        <div class="complaint-content" title="<?php echo htmlspecialchars($data['complaint_content']); ?>">
                                            <?php echo $data['complaint_content'] ?>
                                        </div>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($data['complaint_date'])); ?></td>
                                    <td>
                                        <a href="Reply.php?rid=<?php echo $data['complaint_id'] ?>" target="_blank" class="action-btn">
                                            <i class="fas fa-reply"></i> Reply
                                        </a>
                                    </td>
                                </tr>
                                <?php
                                    }
                                } else {
                                ?>
                                <tr>
                                    <td colspan="8" class="empty-row">
                                        <i class="fas fa-inbox"></i>
                                        <br>
                                        No complaints found in the system.
                                    </td>
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
        // Simple search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const rows = document.querySelectorAll('.data-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
        // Handle image errors
        document.querySelectorAll('.user-photo').forEach(img => {
            img.addEventListener('error', function() {
                this.src = '../Assets/Files/UserDocs/default.avif';
            });
        });
        
        // Add enter key functionality for search
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const searchValue = encodeURIComponent(this.value);
                window.location.href = `?search=${searchValue}`;
            }
        });
    </script>
</body>
</html>