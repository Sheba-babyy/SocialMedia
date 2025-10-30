<?php
include("../Assets/Connection/Connection.php");
session_start();

// --- (Optional) Admin authentication check ---
if (!isset($_SESSION['aid'])) {
    header("Location: login.php");
    exit;
}

// --- Handle Group Actions (Block / Delete) ---
if (isset($_GET['block_id'])) {
    $gid = intval($_GET['block_id']);
    $con->query("UPDATE tbl_group SET group_status='blocked' WHERE group_id=$gid");
    header("Location: ManageGroups.php");
    exit;
}
if (isset($_GET['unblock_id'])) {
    $gid = intval($_GET['unblock_id']);
    $con->query("UPDATE tbl_group SET group_status='active' WHERE group_id=$gid");
    header("Location: ManageGroups.php");
    exit;
}
if (isset($_GET['delete_id'])) {
    $gid = intval($_GET['delete_id']);
    $con->query("DELETE FROM tbl_group WHERE group_id=$gid");
    header("Location: ManageGroups.php");
    exit;
}
// --- Pagination ---
$limit = 10; // groups per page
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// --- Search ---
$search = isset($_GET['search']) ? $con->real_escape_string($_GET['search']) : "";
$searchFilter = $search ? " AND g.group_name LIKE '%$search%'" : "";

$countQry = "
    SELECT COUNT(*) AS total 
    FROM tbl_group g 
    INNER JOIN tbl_user u ON g.user_id = u.user_id
    WHERE 1 $searchFilter
";
$totalGroups = $con->query($countQry)->fetch_assoc()['total'];
$totalPages = ceil($totalGroups / $limit);

$qry = "
    SELECT g.*, u.user_name,
        (SELECT COUNT(*) FROM tbl_groupmembers gm WHERE gm.group_id = g.group_id) AS member_count,
        (SELECT COUNT(*) FROM tbl_group_reports gr WHERE gr.group_id = g.group_id) AS report_count
    FROM tbl_group g
    INNER JOIN tbl_user u ON g.user_id = u.user_id
    WHERE 1 $searchFilter
    ORDER BY g.group_id DESC
    LIMIT $offset, $limit
";
$result = $con->query($qry);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Groups</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7eb 100%);
            min-height: 100vh;
            padding: 0;
            margin: 0;
        }
        
        .main-content {
            margin-left: 280px;
            padding: 0px;
            width: calc(100% - 280px);
            transition: all 0.3s ease;
        }
        
        @media (max-width: 1024px) {
            .main-content {
                padding: 1.5rem;
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 70px;
                width: calc(100% - 70px);
                padding: 1rem;
            }
        }
        
        /* Glassmorphism Card Styling */
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            overflow: hidden;
        }
        
        .table-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .table-container {
            overflow-x: auto;
            border-radius: 0 0 20px 20px;
        }
        
        .table-modern {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
        }
        
        .table-modern th {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 1.25rem 1.5rem;
            text-align: left;
            font-weight: 600;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table-modern td {
            padding: 1.5rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .table-modern tr:hover td {
            background-color: #f8fafc;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .table-modern tr:last-child td {
            border-bottom: none;
        }
        
        /* Enhanced Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn-sm {
            padding: 0.6rem 1.2rem;
            font-size: 0.8rem;
            border-radius: 10px;
        }
        
        .btn-blue {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }
        
        .btn-blue:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        }
        
        .btn-green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        .btn-green:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }
        
        .btn-yellow {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }
        
        .btn-yellow:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        }
        
        .btn-red {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }
        
        .btn-red:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        }
        
        /* Enhanced Badge Styles */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.6rem 1.2rem;
            border-radius: 25px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .badge-success {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            color: #166534;
            border: 2px solid #bbf7d0;
        }
        
        .badge-danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
            border: 2px solid #fecaca;
        }
        
        /* Search and Filter Styles */
        .search-container {
            position: relative;
        }
        
        .search-input {
            padding: 1rem 1rem 1rem 3.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            width: 350px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            transform: translateY(-1px);
        }
        
        .search-icon {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 1.2rem;
        }
        
        /* Pagination Styles */
        .pagination {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .page-btn {
            padding: 0.75rem 1.25rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            color: #64748b;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            background: white;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }
        
        .page-btn:hover {
            border-color: #3b82f6;
            color: #3b82f6;
            background: #f8fafc;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }
        
        .page-info {
            color: #64748b;
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        /* Action Cell Styles */
        .action-cell {
            white-space: nowrap;
        }
        
        .action-cell .btn {
            margin-right: 0.75rem;
            margin-bottom: 0.5rem;
        }
        
        .action-cell .btn:last-child {
            margin-right: 0;
        }
        
        /* Empty State Styles */
        .empty-state {
            padding: 4rem 2rem;
            text-align: center;
            color: #64748b;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            opacity: 0.3;
        }
        
        /* Header Styles */
        .page-header {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }
        
        /* Responsive adjustments */
        @media (max-width: 1024px) {
            .search-input {
                width: 280px;
            }
            
            .page-header {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 768px) {
            .search-input {
                width: 100%;
                margin-bottom: 1rem;
            }
            
            .table-modern th,
            .table-modern td {
                padding: 1rem;
            }
            
            .action-cell {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }
            
            .action-cell .btn {
                margin-right: 0;
                width: 100%;
                justify-content: center;
            }
            
            .page-header {
                font-size: 1.75rem;
            }
        }
        
        /* Animation for table rows */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .table-modern tr {
            animation: fadeIn 0.5s ease-out;
        }
        
        .table-modern tr:nth-child(even) {
            animation-delay: 0.1s;
        }
        
    </style>
</head>
<body class="min-h-screen">
    <!-- Sidebar Space -->
    <?php include 'Sidebar.php'?>                

    <!-- Main Content Area -->
    <div class="main-content">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-8 gap-6">
            <div>
                <h1 class="page-header">Manage Groups</h1>
                <p class="text-gray-600 text-lg">Monitor and manage all community groups</p>
            </div>
            <form method="get" class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
                <div class="search-container">
                    <i class='bx bx-search search-icon'></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Search groups..."
                           class="search-input">
                </div>
                <button type="submit" class="btn btn-blue">
                    <i class='bx bx-search-alt mr-2'></i> Search
                </button>
                <?php if ($search) { ?>
                    <a href="ManageGroups.php" class="btn btn-red">
                        <i class='bx bx-x mr-2'></i> Clear
                    </a>
                <?php } ?>
            </form>
        </div>

        <div class="card">
            <div class="table-header">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-semibold">Groups Management</h2>
                        <p class="text-blue-100 text-sm opacity-90 mt-1">Total: <?= $totalGroups ?> groups found</p>
                    </div>
                </div>
            </div>
            
            <div class="table-container">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th class="text-center">SL NO</th>
                            <th>Group Name</th>
                            <th>Created By</th>
                            <th class="text-center">Members</th>
                            <th class="text-center">Reports</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        $i = $offset + 1;
                        while ($row = $result->fetch_assoc()) {
                            ?>
                            <tr>
                                <td class="text-center font-semibold text-gray-600 text-lg"><?= $i++ ?></td>
                                <td class="font-semibold text-gray-800 text-lg"><?= htmlspecialchars($row['group_name']) ?></td>
                                <td class="text-gray-700"><?= htmlspecialchars($row['user_name']) ?></td>
                                <td class="text-center font-mono text-blue-600 font-bold text-lg"><?= $row['member_count'] ?></td>
                                <td class="text-center">
                                    <?php if ($row['report_count'] > 0) { ?>
                                        <a href="Reports.php?gid=<?= $row['group_id'] ?>" 
                                           class="inline-flex items-center justify-center px-4 py-2 bg-red-50 text-red-700 rounded-full text-sm font-semibold hover:bg-red-100 transition-all duration-300 transform hover:scale-105">
                                           <i class='bx bx-flag mr-2'></i> <?= $row['report_count'] ?>
                                        </a>
                                    <?php } else { ?>
                                        <span class="text-gray-400 text-sm">-</span>
                                    <?php } ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($row['group_status'] == 'blocked') { ?>
                                        <span class="badge badge-danger">
                                            <i class='bx bx-block mr-2'></i> Blocked
                                        </span>
                                    <?php } else { ?>
                                        <span class="badge badge-success">
                                            <i class='bx bx-check-circle mr-2'></i> Active
                                        </span>
                                    <?php } ?>
                                </td>
                                <td class="text-center action-cell">
                                    <a href="Reports.php?gid=<?= $row['group_id'] ?>" class="btn btn-blue btn-sm">
                                        <i class='bx bx-show mr-2'></i> View
                                    </a>
                                    <?php if ($row['group_status'] == 'blocked') { ?>
                                        <a href="?unblock_id=<?= $row['group_id'] ?>" class="btn btn-green btn-sm">
                                            <i class='bx bx-check-circle mr-2'></i> Unblock
                                        </a>
                                    <?php } else { ?>
                                        <a href="?block_id=<?= $row['group_id'] ?>" class="btn btn-yellow btn-sm">
                                            <i class='bx bx-block mr-2'></i> Block
                                        </a>
                                    <?php } ?>
                                    <a href="?delete_id=<?= $row['group_id'] ?>" onclick="return confirm('Are you sure you want to delete this group?\n\nThis action will permanently remove the group and all its data. This cannot be undone.')" class="btn btn-red btn-sm">
                                        <i class='bx bx-trash mr-2'></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr>
                                <td colspan='7' class='empty-state'>
                                    <i class='bx bx-group'></i>
                                    <p class='text-xl font-semibold text-gray-600 mb-2'>No groups found</p>
                                    <p class='text-gray-500'>" . 
                                    ($search ? "Try adjusting your search criteria" : "There are no groups in the system yet") . 
                                    "</p>
                                </td>
                              </tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($totalPages > 1) { ?>
            <div class="px-8 py-6 border-t border-gray-100 bg-gray-50/50">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="page-info">
                        Page <?= $page ?> of <?= $totalPages ?> • <?= $totalGroups ?> groups total
                    </div>
                    <div class="pagination">
                        <?php if ($page > 1) { ?>
                            <a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>" class="page-btn">
                                <i class='bx bx-chevron-left mr-2'></i> Previous
                            </a>
                        <?php } ?>
                        
                        <?php 
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $startPage + 4);
                        
                        for ($p = $startPage; $p <= $endPage; $p++) {
                            $isActive = $p == $page;
                            echo "<a href='?page=$p&search=" . urlencode($search) . "' 
                                  class='page-btn " . ($isActive ? 'bg-blue-500 text-white border-blue-500 shadow-lg' : '') . "'>$p</a>";
                        }
                        ?>
                        
                        <?php if ($page < $totalPages) { ?>
                            <a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>" class="page-btn">
                                Next <i class='bx bx-chevron-right ml-2'></i>
                            </a>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>

    <script>
            // Add smooth animations
            const tableRows = document.querySelectorAll('.table-modern tr');
            tableRows.forEach((row, index) => {
                row.style.animationDelay = `${index * 0.05}s`;
            });
    </script>
</body>
</html>