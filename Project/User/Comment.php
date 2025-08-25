<?php
session_start();
include '../Assets/Connection/Connection.php';

if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit;
}

$uid = mysqli_real_escape_string($con, $_SESSION['uid']);
$post_id = isset($_GET['cid']) ? mysqli_real_escape_string($con, $_GET['cid']) : null;

if (!$post_id) {
    header("Location: ViewPost.php");
    exit;
}

// Add comment
if (isset($_POST['btn_enter'])) {
    $content = mysqli_real_escape_string($con, $_POST['txt_comment']);
    $insQry = "INSERT INTO tbl_comment(comment_content, comment_date, user_id, post_id) 
               VALUES ('$content', NOW(), '$uid', '$post_id')";
    if ($con->query($insQry)) {
        ?>
        <script>
            window.location="Comment.php?cid=<?php echo $post_id ?>";
        </script>
        <?php
    }
}

// Delete comment
if (isset($_GET['did'])) {
    $did = mysqli_real_escape_string($con, $_GET['did']);
    $delQry = "DELETE FROM tbl_comment WHERE comment_id='$did' AND user_id='$uid'";
    if ($con->query($delQry)) {
        ?>
        <script>
            alert("Comment Deleted");
            window.location="Comment.php?cid=<?php echo $post_id ?>";
        </script>
        <?php
    }
}

// Fetch post details
$selPost = "SELECT p.*, u.user_name,u.user_photo,
                   (SELECT COUNT(like_id) FROM tbl_like l WHERE l.post_id = p.post_id) AS like_count,
                   (SELECT COUNT(like_id) FROM tbl_like l WHERE l.post_id = p.post_id AND l.user_id = '$uid') AS user_liked
            FROM tbl_post p 
            INNER JOIN tbl_user u ON p.user_id = u.user_id 
            WHERE p.post_id = '$post_id'";
$postResult = $con->query($selPost);
if ($postResult->num_rows == 0) {
    header("Location: ViewPost.php");
    exit;
}
$postData = $postResult->fetch_assoc();

// Fetch comments
$selComments = "SELECT c.*, u.user_name, u.user_photo 
                FROM tbl_comment c 
                INNER JOIN tbl_user u ON c.user_id = u.user_id 
                WHERE c.post_id = '$post_id' 
                ORDER BY c.comment_date DESC";
$commentResult = $con->query($selComments);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments | Nexo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@400;500;600;700&display=swap');
    
    :root {
        --dark-bg: #0A0A0A;
        --card-bg: rgba(255, 255, 255, 0.08);
        --accent-red: #E53935;
        --accent-green: #00c853;
        --text-light: #F8F8F8;
        --text-subtle: #AFAFAF;
        --border-color: rgba(255, 255, 255, 0.15);
        --hover-bg: rgba(255, 255, 255, 0.05);
    }

    body {
        font-family: 'Montserrat', sans-serif;
        background-color: var(--dark-bg);
        color: var(--text-light);
        margin: 0;
        padding: 20px;
        min-height: 100vh;
    }

    .container {
        max-width: 800px;
        margin: 100px auto;
    }

    .post-card {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border-radius: 20px;
        padding: 0;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        overflow: hidden;
        margin-bottom: 30px;
    }

    .post-header {
        display: flex;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
    }

    .post-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        margin-right: 15px;
        overflow: hidden;
        border: 2px solid var(--accent-red);
        flex-shrink: 0;
    }

    .post-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .post-user h4 {
        color: var(--text-light);
        font-weight: 600;
        margin: 0;
        font-size: 18px;
    }

    .post-user p {
        color: var(--text-subtle);
        opacity: 0.8;
        font-size: 14px;
        margin: 4px 0 0;
    }

    .post-content {
        padding: 20px;
    }

    .post-text {
        color: var(--text-light);
        margin-bottom: 15px;
        line-height: 1.6;
        font-size: 16px;
    }

    .post-media {
        width: 100%;
        border-radius: 12px;
        margin-top: 15px;
        max-height: 500px;
        object-fit: contain;
        border: 1px solid var(--border-color);
    }

    .post-actions {
        display: flex;
        padding: 10px 20px;
        border-top: 1px solid var(--border-color);
    }

    .post-action {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 12px;
        color: var(--text-light);
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
        cursor: pointer;
        background: none;
        border: none;
        font-family: inherit;
        font-size: 14px;
    }

    .post-action:hover {
        color: var(--accent-red);
        background-color: var(--hover-bg);
    }

    .post-action i {
        margin-right: 8px;
        font-size: 18px;
    }

    .liked {
        color: var(--accent-red) !important;
    }

    .comment-form {
        padding: 20px;
        border-top: 1px solid var(--border-color);
    }

    .comment-input {
        display: flex;
        gap: 12px;
    }

    .comment-input input[type="text"] {
        flex: 1;
        padding: 14px;
        background-color: rgba(0, 0, 0, 0.2);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        color: var(--text-light);
        font-size: 16px;
        transition: border-color 0.3s ease;
    }

    .comment-input input[type="text"]:focus {
        outline: none;
        border-color: var(--accent-red);
    }

    .comment-input input[type="submit"] {
        background-color: var(--accent-red);
        color: var(--text-light);
        border: none;
        padding: 14px 24px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        font-family: 'Montserrat', sans-serif;
    }

    .comment-input input[type="submit"]:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(229, 57, 53, 0.4);
    }

    .comments-container {
        margin-top: 20px;
    }

    .comment {
        display: flex;
        gap: 15px;
        padding: 15px 20px;
        border-bottom: 1px solid var(--border-color);
        transition: background-color 0.3s ease;
    }

    .comment:hover {
        background-color: var(--hover-bg);
    }

    .comment-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        overflow: hidden;
        flex-shrink: 0;
        border: 2px solid var(--accent-green);
    }

    .comment-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .comment-content {
        flex: 1;
    }

    .comment-header {
        display: flex;
        align-items: center;
        margin-bottom: 6px;
    }

    .comment-user {
        font-weight: 600;
        margin-right: 10px;
        color: var(--text-light);
        font-size: 16px;
    }

    .comment-date {
        color: var(--text-subtle);
        opacity: 0.8;
        font-size: 14px;
    }

    .comment-text {
        color: var(--text-light);
        line-height: 1.5;
        font-size: 15px;
    }

    .comment-actions {
        margin-top: 10px;
    }

    .delete-comment {
        color: var(--accent-red);
        text-decoration: none;
        font-size: 14px;
        transition: opacity 0.3s ease;
    }

    .delete-comment:hover {
        opacity: 0.8;
    }

    .no-comments {
        text-align: center;
        padding: 50px 20px;
        color: var(--text-subtle);
    }

    .no-comments i {
        font-size: 50px;
        margin-bottom: 20px;
        color: var(--accent-red);
    }

    .no-comments p {
        font-size: 16px;
        margin: 0;
    }

    @media (max-width: 768px) {
        .container {
            padding: 0 10px;
        }
        
        .post-header {
            padding: 15px;
        }
        
        .post-avatar {
            width: 45px;
            height: 45px;
        }
        
        .post-content {
            padding: 15px;
        }
        
        .post-actions {
            padding: 8px 15px;
        }
        
        .post-action {
            padding: 10px;
            font-size: 13px;
        }
        
        .post-action i {
            font-size: 16px;
        }
        
        .comment-form {
            padding: 15px;
        }
        
        .comment {
            padding: 12px 15px;
        }
        
        .comment-avatar {
            width: 40px;
            height: 40px;
        }
    }

    @media (max-width: 576px) {
        body {
            padding: 15px;
        }
        
        .post-header {
            flex-direction: column;
            text-align: center;
        }
        
        .post-avatar {
            margin-right: 0;
            margin-bottom: 10px;
        }
        
        .post-actions {
            flex-direction: column;
            gap: 5px;
        }
        
        .comment {
            flex-direction: column;
            text-align: center;
        }
        
        .comment-avatar {
            margin: 0 auto;
        }
        
        .comment-header {
            justify-content: center;
        }
        
        .comment-input {
            flex-direction: column;
        }
    }
</style>
</head>
<body>
    <?php include("Header.php")?>
    <div class="container">
        <div class="post-card">
            <div class="post-header">
                <div class="post-avatar">
                    <img src="../Assets/Files/UserDocs/<?php echo $postData['user_photo'] ?>" alt="User">
                </div>
                <div class="post-user">
                    <h4><?php echo htmlspecialchars($postData['user_name']) ?></h4>
                    <p><?php echo date('M d, Y • h:i A', strtotime($postData['post_date'])) ?></p>
                </div>
            </div>
            
            <div class="post-content">
                <p class="post-text"><?php echo htmlspecialchars($postData['post_caption']) ?></p>
                <?php
                $file = $postData["post_photo"];
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $file_path = '../Assets/Files/PostDocs/' . $file;
                if (!empty($file)) { 
                    if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) { ?>
                        <img src="<?php echo htmlspecialchars($file_path) ?>" alt="Post Image" class="post-media">
                    <?php } elseif (in_array($extension, ['mp4', 'webm', 'ogg'])) { ?>
                        <video controls class="post-media">
                            <source src="<?php echo htmlspecialchars($file_path) ?>" type="video/<?php echo $extension ?>">
                            Your browser does not support the video tag.
                        </video>
                    <?php } ?>
                <?php } ?>
            </div>
            
            <div class="post-actions">
                <button class="post-action like-btn" data-post-id="<?php echo $post_id ?>" data-liked="<?php echo $postData['user_liked'] ? 'true' : 'false' ?>">
    <i class="<?php echo $postData['user_liked'] ? 'fas fa-heart liked' : 'far fa-heart' ?>" id="icon-<?php echo $post_id ?>"></i>
    <span id="count-<?php echo $post_id ?>"><?php echo $postData['like_count'] ?></span>
</button>
                <button class="post-action">
                    <i class="far fa-comment"></i>
                    <span>Comment</span>
                </button>
                <button class="post-action">
                    <i class="fas fa-share"></i>
                    <span>Share</span>
                </button>
            </div>
            
            <form method="post" class="comment-form">
                <div class="comment-input">
                    <input type="text" name="txt_comment" id="txt_comment" placeholder="Write a comment..." required>
                    <input type="submit" name="btn_enter" id="btn_enter" value="Post">
                </div>
            </form>
        </div>
        
        <div class="comments-container">
            <?php if ($commentResult->num_rows > 0) { ?>
                <?php while ($commentData = $commentResult->fetch_assoc()) { ?>
                    <div class="comment">
                        <div class="comment-avatar">
                            <img src="../Assets/Files/UserDocs/<?php echo htmlspecialchars($commentData['user_photo']) ?>" alt="User">
                        </div>
                        <div class="comment-content">
                            <div class="comment-header">
                                <span class="comment-user">@<?php echo htmlspecialchars($commentData['user_name']) ?></span>
                                <span class="comment-date"><?php echo date('M d, Y', strtotime($commentData['comment_date'])) ?></span>
                            </div>
                            <p class="comment-text"><?php echo htmlspecialchars($commentData['comment_content']) ?></p>
                            <?php if ($commentData['user_id'] == $uid) { ?>
                                <div class="comment-actions">
                                    <a href="Comment.php?did=<?php echo $commentData['comment_id'] ?>&cid=<?php echo $post_id ?>" class="delete-comment">Delete</a>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="no-comments">
                    <i class="far fa-comment-dots"></i>
                    <p>No comments yet</p>
                </div>
            <?php } ?>
        </div>
    </div>

    <script src="../Assets/JQ/JQuery.js"></script>
    <script>
      $(document).on('click', '.like-btn', function() {
    const btn = $(this);
    const postId = btn.data('post-id');
    const isLiked = btn.data('liked') === 'true';
    
    $.get(`../Assets/AjaxPages/AjaxLike.php?pid=${postId}&action=${isLiked ? 'dislike' : 'like'}`)
     .then(response => {
         const [status, count] = response.split('|');
         const icon = $(`#icon-${postId}`);
         
         // Update icon
         if (status === 'liked') {
             icon.removeClass('far fa-heart').addClass('fas fa-heart liked');
             btn.data('liked', 'true');
         } else {
             icon.removeClass('fas fa-heart liked').addClass('far fa-heart');
             btn.data('liked', 'false');
         }
         
         // Update count
         $(`#count-${postId}`).text(count);
     })
     .catch(() => alert('Failed to update like'));
});
    </script>
</body>
</html>