<?php
session_start();
include("../Assets/Connection/Connection.php");
include("Header.php");

// Check login
if (!isset($_SESSION['uid'])) {
    echo "Please log in to view this post.";
    exit;
}

$uid = $_SESSION['uid'];

// Get post ID from query string
if (!isset($_GET['pid'])) {
    echo "No post selected.";
    exit;
}

$post_id = intval($_GET['pid']);

// Fetch post and user details
$stmt = $con->prepare("
    SELECT p.*, u.user_name, u.user_photo 
    FROM tbl_post p
    INNER JOIN tbl_user u ON p.user_id = u.user_id
    WHERE p.post_id = ?
");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Post not found.";
    exit;
}

$post = $result->fetch_assoc();
$file = $post['post_photo'];
$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
$file_path = '../Assets/Files/PostDocs/' . $file;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Shared Post | Nexo</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
body {
    font-family: 'Segoe UI', sans-serif;
    background-color: #121212;
    color: #e0e0e0;
    margin: 0px;
    padding: 20px;
}
.container {
    max-width: 800px;
    margin: auto;
    margin-top:80px;
}
.post-card {
    background-color: #1e1e1e;
    border: 1px solid #333;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
}
.post-header {
    display: flex;
    align-items: center;
    margin-bottom: 16px;
}
.post-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    overflow: hidden;
    margin-right: 12px;
    border: 2px solid #1a4b32;
}
.post-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.post-user h4 {
    margin: 0;
    font-size: 1.1rem;
    color: #fff;
}
.post-user p {
    margin: 2px 0 0 0;
    font-size: 0.85rem;
    opacity: 0.7;
}
.post-content p {
    margin-bottom: 12px;
}
.post-media {
    width: 100%;
    max-height: 500px;
    border-radius: 8px;
    margin-top: 12px;
    object-fit: contain;
}
.shared-label {
    display: inline-block;
    background-color: #1a4b32;
    color: #fff;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.85rem;
    margin-bottom: 12px;
}
</style>
</head>
<body>

<div class="container">
    <div class="post-card">
        <div class="shared-label">Shared Post</div>

        <div class="post-header">
            <div class="post-avatar">
                <img src="../Assets/Files/UserDocs/<?php echo htmlspecialchars($post['user_photo'] ?: 'default.avif'); ?>" alt="<?php echo htmlspecialchars($post['user_name']); ?>">
            </div>
            <div class="post-user">
                <h4><?php echo htmlspecialchars($post['user_name']); ?></h4>
                <p><?php echo date('M d, Y • h:i A', strtotime($post['post_date'])); ?></p>
            </div>
        </div>

        <div class="post-content">
            <p><?php echo htmlspecialchars($post['post_caption']); ?></p>

            <?php if (!empty($file)) : ?>
                <?php if (in_array($extension, ['jpg','jpeg','png','gif','webp'])) : ?>
                    <img src="<?php echo htmlspecialchars($file_path); ?>" class="post-media" alt="Post Image">
                <?php elseif (in_array($extension, ['mp4','webm','ogg'])) : ?>
                    <video controls class="post-media">
                        <source src="<?php echo htmlspecialchars($file_path); ?>" type="video/<?php echo $extension; ?>">
                        Your browser does not support the video tag.
                    </video>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
