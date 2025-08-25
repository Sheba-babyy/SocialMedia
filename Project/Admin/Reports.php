<?php
include("../Assets/Connection/Connection.php");
session_start();

// Check admin login
if (!isset($_SESSION['aid'])) {
    header("Location: login.php");
    exit;
}

$gid = isset($_GET['gid']) ? intval($_GET['gid']) : 0;

$groupName = "";
if ($gid > 0) {
    $groupFilter = " AND gr.group_id=$gid ";
    $gnameQry = $con->query("SELECT group_name FROM tbl_group WHERE group_id=$gid");
    if ($gnameQry && $gnameQry->num_rows > 0) {
        $groupName = $gnameQry->fetch_assoc()['group_name'];
    }
} else {
    $groupFilter = "";
}

$qry = "
    SELECT gr.*, u.user_name, g.group_name
    FROM tbl_group_reports gr
    INNER JOIN tbl_user u ON gr.user_id = u.user_id
    INNER JOIN tbl_group g ON gr.group_id = g.group_id
    WHERE (gr.report_status='Pending') $groupFilter
    ORDER BY gr.report_id DESC
";

//  Handle Post Deletion
if (isset($_GET['delete_post'])) {
    $postId = intval($_GET['delete_post']);

    // Delete file if exists
    $postQry = $con->query("SELECT post_photo FROM tbl_post WHERE post_id='$postId'");
    if ($postQry && $postRow = $postQry->fetch_assoc()) {
        $filePath = "../Assets/Files/PostDocs/" . $postRow['post_photo'];
        if (!empty($postRow['post_photo']) && file_exists($filePath)) {
            unlink($filePath);
        }
    }

    $con->query("DELETE FROM tbl_post WHERE post_id='$postId'");
    $con->query("UPDATE tbl_reports SET report_status='Resolved - Deleted' WHERE post_id='$postId'");

    header("Location: Reports.php?msg=PostDeleted");
    exit;
}

//  Handle Group Deletion
if (isset($_GET['delete_group'])) {
    $groupId = intval($_GET['delete_group']);
    $con->query("DELETE FROM tbl_group WHERE group_id='$groupId'");
    $con->query("UPDATE tbl_group_reports SET report_status='Resolved - Deleted' WHERE group_id='$groupId'");
    header("Location: Reports.php?msg=GroupDeleted");
    exit;
}

//  Handle Post Report Dismiss
if (isset($_GET['dismiss_post'])) {
    $postId = intval($_GET['dismiss_post']);
    $con->query("UPDATE tbl_reports SET report_status='Resolved - Dismissed' WHERE post_id='$postId'");
    header("Location: Reports.php?msg=PostDismissed");
    exit;
}

//  Handle Group Report Dismiss
if (isset($_GET['dismiss_group'])) {
    $groupId = intval($_GET['dismiss_group']);
    $con->query("UPDATE tbl_group_reports SET report_status='Resolved - Dismissed' WHERE group_id='$groupId'");
    header("Location: Reports.php?msg=GroupDismissed");
    exit;
}

//  Handle Reviewed
if (isset($_GET['review_post'])) {
    $postId = intval($_GET['review_post']);
    $con->query("UPDATE tbl_reports SET report_status='Reviewed' WHERE post_id='$postId'");
    header("Location: Reports.php?msg=PostReviewed");
    exit;
}
if (isset($_GET['review_group'])) {
    $groupId = intval($_GET['review_group']);
    $con->query("UPDATE tbl_group_reports SET report_status='Reviewed' WHERE group_id='$groupId'");
    header("Location: Reports.php?msg=GroupReviewed");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --info: #4895ef;
            --warning: #f72585;
            --danger: #dc3545;
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
        }

        /* Header */
        .page-header {
            background: white;
            padding: 20px 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 28px;
            color: var(--dark);
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-header p {
            color: var(--gray);
            font-size: 16px;
        }

        /* Message notification */
        .message {
            padding: 12px 20px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            font-weight: 500;
            text-align: center;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        /* Section headers */
        .section-header {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
            padding: 15px 20px;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            margin-bottom: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Tables */
        .table-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            margin-bottom: 40px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        th {
            background-color: #f8f9fa;
            color: var(--dark);
            font-weight: 600;
            position: sticky;
            top: 0;
        }

        tr:nth-child(even) {
            background-color: #f9fafc;
        }

        tr:hover {
            background-color: #f1f5f9;
        }

        /* Action buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: var(--transition);
            gap: 6px;
            margin-right: 8px;
            margin-bottom: 5px;
        }

        .view-btn {
            background-color: rgba(67, 97, 238, 0.15);
            color: var(--primary);
        }

        .view-btn:hover {
            background-color: var(--primary);
            color: white;
        }

        .remove-btn {
            background-color: rgba(220, 53, 69, 0.15);
            color: var(--danger);
        }

        .remove-btn:hover {
            background-color: var(--danger);
            color: white;
        }

        .dismiss-btn {
            background-color: rgba(108, 117, 125, 0.15);
            color: var(--gray);
        }

        .dismiss-btn:hover {
            background-color: var(--gray);
            color: white;
        }

        .review-btn {
            background-color: rgba(255, 193, 7, 0.15);
            color: #ffc107;
        }

        .review-btn:hover {
            background-color: #ffc107;
            color: white;
        }

        /* Post images */
        .post-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #e9ecef;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--gray);
        }

        .empty-state i {
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
            
            .content-area {
                padding: 20px;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
            
            th, td {
                padding: 12px;
            }
            
            .btn {
                margin-right: 5px;
                margin-bottom: 5px;
            }
        }

        @media (max-width: 576px) {
            .page-header {
                padding: 15px 20px;
            }
            
            .page-header h1 {
                font-size: 24px;
            }
            
            .section-header {
                padding: 12px 15px;
                font-size: 18px;
            }
            
            th, td {
                padding: 10px;
                font-size: 14px;
            }
            
            .btn {
                padding: 6px 12px;
                font-size: 12px;
            }
            
            .post-image {
                width: 60px;
                height: 60px;
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
            <!-- Header -->
            <div class="page-header">
                <h1><i class="fas fa-flag"></i> Reports Dashboard</h1>
                <p>Manage reported content and groups</p>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
                </div>
            <?php endif; ?>

            <!-- Reported Posts -->
            <div class="table-container">
                <h2 class="section-header"><i class="fas fa-image"></i> Reported Posts</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Report ID</th>
                            <th>Reported By</th>
                            <th>Post</th>
                            <th>Reason</th>
                            <th>Details</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $postReports = $con->query("
                            SELECT r.*, u.user_name, u.user_id, p.post_caption, p.post_photo
                            FROM tbl_reports r
                            JOIN tbl_user u ON r.user_id = u.user_id
                            JOIN tbl_post p ON r.post_id = p.post_id
                            WHERE r.report_status = 'Pending'
                            ORDER BY r.report_date DESC
                        ");

                        if ($postReports->num_rows > 0) {
                            while ($row = $postReports->fetch_assoc()) {
                                echo "<tr>
                                        <td>{$row['report_id']}</td>
                                        <td>{$row['user_name']}</td>
                                        <td>
                                            <div>{$row['post_caption']}</div>";
                                if (!empty($row['post_photo'])) {
                                    echo "<img src='../Assets/Files/PostDocs/{$row['post_photo']}' class='post-image'>";
                                }
                                echo "</td>
                                        <td>{$row['reason']}</td>
                                        <td>{$row['details']}</td>
                                        <td>" . date('M j, Y', strtotime($row['report_date'])) . "</td>
                                        <td>
                                            <a href='UserProfile.php?uid={$row['user_id']}' class='btn view-btn' title='View User'>
                                                <i class='fas fa-user'></i> View
                                            </a>
                                            <a href='Reports.php?delete_post={$row['post_id']}' class='btn remove-btn' onclick='return confirm(\"Delete this post?\")' title='Remove Post'>
                                                <i class='fas fa-trash'></i> Remove
                                            </a>
                                            <a href='Reports.php?dismiss_post={$row['post_id']}' class='btn dismiss-btn' onclick='return confirm(\"Dismiss this report?\")' title='Dismiss Report'>
                                                <i class='fas fa-times'></i> Dismiss
                                            </a>
                                            <a href='Reports.php?review_post={$row['post_id']}' class='btn review-btn' title='Mark as Reviewed'>
                                                <i class='fas fa-check'></i> Reviewed
                                            </a>
                                        </td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr>
                                    <td colspan='7' class='empty-state'>
                                        <i class='fas fa-check-circle'></i>
                                        <div>No reported posts</div>
                                    </td>
                                  </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Reported Groups -->
            <div class="table-container">
                <?php if ($gid > 0 && $groupName): ?>
    <div class="message success">
        Showing reports for group <b><?= htmlspecialchars($groupName) ?></b>
    </div>
               <?php else: ?> 
                <h2 class="section-header"><i class="fas fa-users"></i> Reported Groups</h2>
                <?php endif;?>
                <table>
                    <thead>
                        <tr>
                            <th>Report ID</th>
                            <th>Reported By</th>
                            <th>Group</th>
                            <th>Reason</th>
                            <th>Details</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $groupReports = $con->query($qry);

                        if ($groupReports->num_rows > 0) {
                            while ($row = $groupReports->fetch_assoc()) {
                                echo "<tr>
                                        <td>{$row['report_id']}</td>
                                        <td>{$row['user_name']}</td>
                                        <td>{$row['group_name']}</td>
                                        <td>{$row['reason']}</td>
                                        <td>{$row['details']}</td>
                                        <td>" . date('M j, Y', strtotime($row['report_date'])) . "</td>
                                        <td>
                                            <a href='UserProfile.php?uid={$row['user_id']}' class='btn view-btn' title='View User'>
                                                <i class='fas fa-user'></i> View
                                            </a>
                                            <a href='Reports.php?delete_group={$row['group_id']}' class='btn remove-btn' onclick='return confirm(\"Delete this group?\")' title='Remove Group'>
                                                <i class='fas fa-trash'></i> Remove
                                            </a>
                                            <a href='Reports.php?dismiss_group={$row['group_id']}' class='btn dismiss-btn' onclick='return confirm(\"Dismiss this report?\")' title='Dismiss Report'>
                                                <i class='fas fa-times'></i> Dismiss
                                            </a>
                                            <a href='Reports.php?review_group={$row['group_id']}' class='btn review-btn' title='Mark as Reviewed'>
                                                <i class='fas fa-check'></i> Reviewed
                                            </a>
                                        </td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr>
                                    <td colspan='7' class='empty-state'>
                                        <i class='fas fa-check-circle'></i>
                                        <div>No reported groups</div>
                                    </td>
                                  </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
          // Auto-hide message after 5 seconds
            const message = document.querySelector('.message');
            if (message) {
                setTimeout(() => {
                    message.style.opacity = '0';
                    message.style.transition = 'opacity 0.5s ease';
                    setTimeout(() => {
                        message.style.display = 'none';
                    }, 500);
                }, 5000);
            }
    </script>
</body>
</html>
