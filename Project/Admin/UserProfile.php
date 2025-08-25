<?php
include("../Assets/Connection/Connection.php");
session_start();

//  Allow only admin
if (!isset($_SESSION['aid'])) {
    header("Location: ../login.php");
    exit;
}
$admin_id = $_SESSION['aid'];

//  Get target user
if (!isset($_GET['pid'])) {
    echo "No user specified.";
    exit;
}
$pid = mysqli_real_escape_string($con, $_GET['pid']);

// --- Actions ---
$message = "";
if (isset($_GET['action'])) {
    if ($_GET['action'] == "ban") {
        $con->query("UPDATE tbl_user SET user_status='banned' WHERE user_id='$pid'");
        $message = "User has been banned.";
    } elseif ($_GET['action'] == "delete") {
        $con->query("DELETE FROM tbl_user WHERE user_id='$pid'");
        $message = "User has been deleted.";
    } elseif ($_GET['action'] == "unban") {
        $con->query("UPDATE tbl_user SET user_status='active' WHERE user_id='$pid'");
        $message = "User has been unbanned.";
    }
}

// Fetch user after any action
$userQry = "SELECT u.*, p.place_name, d.district_name 
            FROM tbl_user u 
            INNER JOIN tbl_place p ON u.place_id=p.place_id 
            INNER JOIN tbl_district d ON p.district_id=d.district_id 
            WHERE u.user_id='$pid'";
$userRes = $con->query($userQry);
if (!$userRes || $userRes->num_rows == 0) {
    echo "User not found.";
    exit;
}
$user = $userRes->fetch_assoc();

// Get all posts by this user
$postQry = "SELECT * FROM tbl_post WHERE user_id='$pid' ORDER BY post_id DESC";
$postRes = $con->query($postQry);

// Handle post deletion by admin
if (isset($_GET['delete_post'])) {
    $postId = (int) $_GET['delete_post'];

    // Optional: delete file from server if it exists
    $postQry = $con->query("SELECT post_photo FROM tbl_post WHERE post_id='$postId'");
    if ($postQry && $postRow = $postQry->fetch_assoc()) {
        $filePath = "../Assets/Files/PostDocs/" . $postRow['post_photo'];
        if (file_exists($filePath) && is_file($filePath)) {
            unlink($filePath); // remove file
        }
    }

    // Delete post record
    $con->query("DELETE FROM tbl_post WHERE post_id='$postId'");

    header("Location: UserProfile.php?pid=$pid");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - User Profile</title>
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
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .profile-container {
            width: 100%;
            max-width: 900px;
            background: white;
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        /* Message */
        .message {
            padding: 15px 20px;
            background: #d4edda;
            color: #155724;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
            border: 1px solid #c3e6cb;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Profile header */
        .profile-header {
            display: flex;
            align-items: center;
            gap: 25px;
            margin-bottom: 30px;
            padding-bottom: 25px;
            border-bottom: 1px solid #e9ecef;
        }

        .profile-header img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #e9ecef;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .profile-info h2 {
            font-size: 28px;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .profile-info p {
            color: var(--gray);
            margin-bottom: 8px;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .status-active {
            background: rgba(40, 167, 69, 0.15);
            color: #28a745;
        }

        .status-banned {
            background: rgba(220, 53, 69, 0.15);
            color: #dc3545;
        }

        /* Details section */
        .details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .detail-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: var(--border-radius);
            border-left: 4px solid var(--primary);
        }

        .detail-card h3 {
            color: var(--dark);
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .detail-item {
            margin-bottom: 12px;
            display: flex;
        }

        .detail-label {
            font-weight: 600;
            min-width: 100px;
            color: var(--dark);
        }

        .detail-value {
            color: var(--gray);
            flex: 1;
        }

        /* Actions */
        .actions {
            display: flex;
            gap: 15px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 12px 24px;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
            border: none;
            cursor: pointer;
        }

        .ban-btn {
            background: var(--danger);
            color: white;
        }

        .ban-btn:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        .unban-btn {
            background: var(--primary);
            color: white;
        }

        .unban-btn:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }

        .delete-btn {
            background: var(--gray);
            color: white;
        }

        .delete-btn:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        /* Posts section */
        .posts-section {
            margin-top: 40px;
        }

        .section-header {
            font-size: 22px;
            color: var(--dark);
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .post {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
        }

        .post:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .post-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .post-id {
            font-weight: 600;
            color: var(--dark);
        }

        .post-content {
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .post-media {
            margin-top: 15px;
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        .post-media img {
            max-width: 100%;
            border-radius: var(--border-radius);
        }

        .post-media video {
            max-width: 100%;
            border-radius: var(--border-radius);
        }

        .post-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 15px;
        }

        .remove-post-btn {
            padding: 8px 16px;
            background: var(--danger);
            color: white;
            text-decoration: none;
            border-radius: var(--border-radius);
            font-size: 14px;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .remove-post-btn:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        .empty-posts {
            text-align: center;
            padding: 40px;
            color: var(--gray);
        }

        .empty-posts i {
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
            
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .action-btn {
                justify-content: center;
            }
            
            .details {
                grid-template-columns: 1fr;
            }
            
            .content-area {
                padding: 20px;
            }
        }

        @media (max-width: 576px) {
            .profile-container {
                padding: 20px;
            }
            
            .profile-header img {
                width: 100px;
                height: 100px;
            }
            
            .profile-info h2 {
                font-size: 24px;
            }
            
            .detail-item {
                flex-direction: column;
                gap: 5px;
            }
            
            .detail-label {
                min-width: auto;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .profile-container {
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
            <div class="profile-container">
                <?php if ($message): ?>
                    <div class="message">
                        <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <div class="profile-header">
                    <img src="../Assets/Files/UserDocs/<?php echo ($user['user_photo']?:'default.avif') ?>" 
                         onerror="this.src='../Assets/Files/UserDocs/default.avif'">
                    <div class="profile-info">
                        <h2><?php echo htmlspecialchars($user['user_name']); ?></h2>
                        <p><?php echo htmlspecialchars($user['user_email']); ?></p>
                        <span class="status-badge <?php echo $user['user_status'] === 'active' ? 'status-active' : 'status-banned'; ?>">
                            <i class="fas <?php echo $user['user_status'] === 'active' ? 'fa-check-circle' : 'fa-ban'; ?>"></i>
                            <?php echo ucfirst($user['user_status']); ?>
                        </span>
                    </div>
                </div>
                
                <div class="details">
                    <div class="detail-card">
                        <h3><i class="fas fa-user"></i> Personal Info</h3>
                        <div class="detail-item">
                            <span class="detail-label">Bio:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($user['user_bio'])?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">DOB:</span>
                            <span class="detail-value"><?php echo date("M j, Y", strtotime($user['user_dob'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Gender:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($user['user_gender']); ?></span>
                        </div>
                    </div>
                    
                    <div class="detail-card">
                        <h3><i class="fas fa-address-card"></i> Contact Info</h3>
                        <div class="detail-item">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($user['user_email']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Contact:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($user['user_contact']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Location:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($user['place_name']) . ', ' . htmlspecialchars($user['district_name']); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="actions">
                    <?php if ($user['user_status'] === 'active') { ?>
                        <a href="UserProfile.php?pid=<?php echo (int)$user['user_id']; ?>&action=ban"
                           class="action-btn ban-btn"
                           onclick="return confirm('Ban this user?');">
                            <i class="fas fa-ban"></i> Ban User
                        </a>
                    <?php } else { ?>
                        <a href="UserProfile.php?pid=<?php echo (int)$user['user_id']; ?>&action=unban"
                           class="action-btn unban-btn"
                           onclick="return confirm('Unban this user?');">
                            <i class="fas fa-check-circle"></i> Unban User
                        </a>
                    <?php } ?>
                    <a href="UserProfile.php?pid=<?php echo (int)$user['user_id']; ?>&action=delete"
                       class="action-btn delete-btn"
                       onclick="return confirm('Delete this user permanently?');">
                        <i class="fas fa-trash"></i> Delete User
                    </a>
                </div>

                <!-- User Posts Section -->
                <div class="posts-section">
                    <h3 class="section-header"><i class="fas fa-images"></i> User Posts</h3>
                    <?php if ($postRes->num_rows > 0) { 
                        while ($post = $postRes->fetch_assoc()) { 
                            $file = $post['post_photo']; 
                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION)); // detect file type
                    ?>
                            <div class="post">
                                <div class="post-header">
                                    <span class="post-id">Post #<?php echo $post['post_id']; ?></span>
                                </div>
                                <div class="post-content">
                                    <?php echo nl2br(htmlspecialchars($post['post_caption'])); ?>
                                </div>

                                <?php if (!empty($file)) { 
                                    // Check if file is image
                                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) { ?>
                                        <div class="post-media">
                                            <img src="../Assets/Files/PostDocs/<?php echo $file; ?>" 
                                                 alt="Post Image"
                                                 onerror="this.style.display='none'">
                                        </div>
                                    <?php } 
                                    // Check if file is video
                                    elseif (in_array($ext, ['mp4', 'webm', 'ogg'])) { ?>
                                        <div class="post-media">
                                            <video controls width="100%">
                                                <source src="../Assets/Files/PostDocs/<?php echo $file; ?>" type="video/<?php echo $ext; ?>">
                                                Your browser does not support the video tag.
                                            </video>
                                        </div>
                                    <?php } ?>
                                <?php } ?>
                                
                                <div class="post-actions">
                                    <a href="UserProfile.php?pid=<?php echo $pid; ?>&delete_post=<?php echo (int)$post['post_id']; ?>"
                                       class="remove-post-btn"
                                       onclick="return confirm('Are you sure you want to remove this post?');">
                                        <i class="fas fa-trash"></i> Remove Post
                                    </a>
                                </div>
                            </div>
                    <?php } 
                    } else { ?>
                        <div class="empty-posts">
                            <i class="fas fa-image"></i>
                            <p>No posts from this user.</p>
                        </div>
                    <?php } ?>
                </div>
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