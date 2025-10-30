<?php
include("../Assets/Connection/Connection.php");

// --- Pagination Setup ---
$limit = 10; // how many rows per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// --- Search Setup ---
$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$where = "";
if ($search !== "") {
    // Search in name, email, phone
    $safeSearch = mysqli_real_escape_string($con, $search);
    $where = "WHERE u.user_name LIKE '%$safeSearch%' 
              OR u.user_email LIKE '%$safeSearch%' 
              OR u.user_contact LIKE '%$safeSearch%'";
}

// Count total users
$countQry="select count(*) as total from tbl_user";
$countRes = $con->query("$countQry");
$totalRows = $countRes->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management System</title>
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
            padding-left: 25px;
        }

        .container {
            width: 100%;
            max-width: 1400px;
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
            cursor: pointer;
        }
        
        .data-table th:hover {
            background-color: #3a5a9b;
        }
        
        .data-table tr:nth-child(even) {
            background-color: #f9fafc;
        }
        
        .data-table tr:hover {
            background-color: #f1f5f9;
            cursor: pointer;
        }
        
        .user-photo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e9ecef;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .action-cell {
            white-space: nowrap;
        }
        
        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            text-decoration: none;
            margin: 0 5px;
            transition: var(--transition);
        }
        
        .view-btn {
            background-color: #4b6cb7;
            color: white;
        }
        
        .edit-btn {
            background-color: #ffc107;
            color: #333;
        }
        
        .delete-btn {
            background-color: #dc3545;
            color: white;
        }
        
        .action-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 3px 8px rgba(0,0,0,0.2);
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 25px;
            gap: 8px;
        }
        
        .pagination-btn {
            padding: 10px 15px;
            border-radius: 6px;
            background: #f9fafc;
            border: 1px solid #e9ecef;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            color: #495057;
        }
        
        .pagination-btn.active {
            background: #4b6cb7;
            color: white;
            border-color: #4b6cb7;
        }
        
        .pagination-btn:hover:not(.active) {
            background: #e9ecef;
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

        .gender-male {
            color: #4b6cb7;
        }
        
        .gender-female {
            color: #e83e8c;
        }
        
        .gender-other {
            color: #6f42c1;
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
            text-align: center;
            flex-direction: column;
                gap: 15px;
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
            
            .pagination {
                flex-wrap: wrap;
            }
            
            .pagination-btn {
                padding: 8px 12px;
                font-size: 14px;
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
<?php include 'Sidebar.php'?>
    </div>
        
        <!-- Content area -->
        <div class="content-area">
            <div class="container">
                <div class="header">
                    <h1><i class="fas fa-users"></i> User Management</h1>
                    <p>View and manage all registered users in the system</p>
                </div>
                
                <div class="content">
                    <div class="controls">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="search" name="search" placeholder="Search users..." id="searchInput" value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        
                        <div class="stats">
                            <div class="stat-box">
                                <div class="number"><?php echo $totalRows; ?></div>
                                <div class="label">Total Users</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>SL.NO</th>
                                    <th>Photo</th>
                                    <th>Name</th>
                                    <th>Bio</th>
                                    <th>Date of Birth</th>
                                    <th>Email</th>
                                    <th>Contact</th>
                                    <th>Gender</th>
                                    <th>District</th>
                                    <th>Place</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $selQry = "select * from tbl_user u inner join tbl_place p on u.place_id=p.place_id inner join tbl_district d on p.district_id=d.district_id $where limit $limit offset $offset";
                                $res = $con->query($selQry);
                                $i = $offset + 1;
                                
                                if ($res && $res->num_rows > 0) {
                                    while($data = $res->fetch_assoc()) {
                                        // Determine gender class for styling
                                        $genderClass = '';
                                        if ($data['user_gender'] == 'Male') $genderClass = 'gender-male';
                                        if ($data['user_gender'] == 'Female') $genderClass = 'gender-female';
                                        if ($data['user_gender'] == 'Other') $genderClass = 'gender-other';
                                ?>
                                <tr onclick="window.location.href='../Admin/UserProfile.php?pid=<?php echo $data['user_id'] ?>'">
                                    <td><?php echo $i++; ?></td>
                                    <td>
                                        <img src="../Assets/Files/UserDocs/<?php echo ($data['user_photo']?:'default.avif')?>" 
                                             alt="User Photo" class="user-photo"
                                             onerror="this.src='../Assets/Files/UserDocs/default.avif'">
                                    </td>
                                    <td><?php echo $data['user_name'] ?></td>
                                    <td><?php echo strlen($data['user_bio']) > 50 ? substr($data['user_bio'], 0, 50) . '...' : $data['user_bio']; ?></td>
                                    <td><?php echo date('M j, Y', strtotime($data['user_dob'])); ?></td>
                                    <td><?php echo $data['user_email'] ?></td>
                                    <td><?php echo $data['user_contact'] ?></td>
                                    <td class="<?php echo $genderClass; ?>">
                                        <i class="fas 
                                            <?php 
                                            if ($data['user_gender'] == 'Male') echo 'fa-mars'; 
                                            else if ($data['user_gender'] == 'Female') echo 'fa-venus';
                                            else echo 'fa-genderless';
                                            ?>">
                                        </i> 
                                        <?php echo $data['user_gender'] ?>
                                    </td>
                                    <td><?php echo $data['district_name'] ?></td>
                                    <td><?php echo $data['place_name'] ?></td>
                                    <td><?php echo $data['user_status'] ?></td>
                                </tr> 
                                <?php
                                    }
                                } else {
                                ?>
                                <tr>
                                    <td colspan="11" class="empty-row">
                                        <i class="fas fa-user-slash"></i>
                                        <br>
                                        No users found in the system.
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>" class="pagination-btn">
                                <i class="fas fa-chevron-left"></i> Prev
                            </a>
                        <?php endif; ?>

                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <a href="?page=<?php echo $p; ?>&search=<?php echo urlencode($search); ?>" 
                               class="pagination-btn <?php if ($p == $page) echo 'active'; ?>">
                                <?php echo $p; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>" class="pagination-btn">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
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