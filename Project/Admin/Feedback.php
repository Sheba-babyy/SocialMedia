<?php
include '../Assets/Connection/Connection.php';
session_start();

// Check admin login
if (!isset($_SESSION['aid'])) {
    header("Location: login.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $feedbackId = intval($_GET['delete']);
    $con->query("DELETE FROM tbl_feedback WHERE feedback_id='$feedbackId'");
    header("Location: Feedback.php?msg=Deleted");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Dashboard</title>
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
        }

        /* Header */
        .page-header {
            background: white;
            padding: 20px 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h1 {
            font-size: 28px;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
        }

        /* Message notification */
        .msg {
            background: #d4edda;
            color: #155724;
            padding: 12px 20px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
            text-align: center;
        }

        /* Feedback table */
        .feedback-table-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }

        .feedback-table {
            width: 100%;
            border-collapse: collapse;
        }

        .feedback-table th, .feedback-table td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .feedback-table th {
            background-color: var(--primary);
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
        }

        .feedback-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .feedback-table tr:hover {
            background-color: #f1f5f9;
        }

        .user-photo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e9ecef;
        }

        .action-cell {
            white-space: nowrap;
        }

        .delete-btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            background: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: var(--transition);
            gap: 6px;
        }

        .delete-btn:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

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

        /* Stats cards */
        .stats-container {
            display: grid;
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
                display: none;
            }
            
            .page-header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .feedback-table {
                font-size: 14px;
            }
            
            .feedback-table th, .feedback-table td {
                padding: 12px;
            }
            
            .content-area {
                padding: 20px;
            }
        }

        @media (max-width: 576px) {
            .feedback-table {
                display: block;
                overflow-x: auto;
            }
            
            .user-photo {
                width: 40px;
                height: 40px;
            }
            
            .action-cell {
                display: flex;
                flex-direction: column;
                gap: 8px;
            }
            
            .delete-btn {
                justify-content: center;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .feedback-table-container {
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
                <h1><i class="fas fa-comments"></i> Feedback Dashboard</h1>
                <div class="user-info">
                    <div class="user-avatar">A</div>
                    <div>
                        <div style="font-weight: 500;">Administrator</div>
                        <div style="font-size: 13px; color: var(--gray);">Admin Account</div>
                    </div>
                </div>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <div class="msg">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['msg']) ?>
                </div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-value">
                        <?php
                        $countQuery = "SELECT COUNT(*) as total FROM tbl_feedback";
                        $countResult = $con->query($countQuery);
                        echo $countResult->fetch_assoc()['total'];
                        ?>
                    </div>
                    <div class="stat-label">Total Feedback</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">
                        <?php
                        $today = date('Y-m-d');
                        $todayQuery = "SELECT COUNT(*) as total FROM tbl_feedback WHERE DATE(feedback_date) = '$today'";
                        $todayResult = $con->query($todayQuery);
                        echo $todayResult->fetch_assoc()['total'];
                        ?>
                    </div>
                    <div class="stat-label">Today's Feedback</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">
                        <?php
                        $userQuery = "SELECT COUNT(DISTINCT user_id) as total FROM tbl_feedback";
                        $userResult = $con->query($userQuery);
                        echo $userResult->fetch_assoc()['total'];
                        ?>
                    </div>
                    <div class="stat-label">Users Feedback</div>
                </div>
            </div>

            <!-- Feedback Table -->
            <div class="feedback-table-container">
                <table class="feedback-table">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>User Name</th>
                            <th>Feedback</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $res = $con->query("SELECT f.*, u.user_name, u.user_photo 
                                            FROM tbl_feedback f
                                            JOIN tbl_user u ON f.user_id=u.user_id
                                            ORDER BY f.feedback_date DESC");
                        if ($res->num_rows > 0) {
                            while($data = $res->fetch_assoc()) {
                               $photo = (!empty($data['user_photo']) && file_exists("../Assets/Files/UserDocs/{$data['user_photo']}"))
    ? $data['user_photo']
    : 'default.avif';

                               echo "<tr>
    <td><img src='../Assets/Files/UserDocs/$photo' class='user-photo' alt='{$data['user_name']}'></td>
    <td>{$data['user_name']}</td>
    <td>{$data['feedback_content']}</td>
    <td>" . date('M j, Y g:i A', strtotime($data['feedback_date'])) . "</td>
    <td class='action-cell'>
        <a href='Feedback.php?delete={$data['feedback_id']}' class='delete-btn' onclick='return confirm(\"Delete this feedback?\")'>
            <i class='fas fa-trash'></i> Delete
        </a>
    </td>
</tr>";
                            }
                        } else {
                            echo "<tr>
                                    <td colspan='5' class='empty-state'>
                                        <i class='fas fa-comment-slash'></i>
                                        <div>No feedback found.</div>
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
            const message = document.querySelector('.msg');
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