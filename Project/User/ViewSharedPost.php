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

// Get user friends for sharing (always logged-in user)
$friends_result = $con->query("
    SELECT u.user_id, u.user_name 
    FROM tbl_user u
    INNER JOIN tbl_friends f 
        ON (f.user_from_id = u.user_id OR f.user_to_id = u.user_id)
    WHERE f.friends_status = '1' 
        AND '$uid' IN (f.user_from_id, f.user_to_id)
        AND u.user_id != '$uid'
    ORDER BY u.user_name ASC
");
$friends = $friends_result->fetch_all(MYSQLI_ASSOC);

// Get groups the user has joined for sharing
$groups_result = $con->query("
    SELECT g.group_id, g.group_name
    FROM tbl_group g
    LEFT JOIN tbl_groupmembers gm 
        ON g.group_id = gm.group_id AND gm.user_id = '$uid' AND gm.groupmembers_status = 1
    WHERE g.user_id = '$uid' OR gm.user_id = '$uid'
");
$groups = $groups_result->fetch_all(MYSQLI_ASSOC);

// Get post ID from query string
if (!isset($_GET['pid'])) {
    echo "No post selected.";
    exit;
}

$post_id = intval($_GET['pid']);

// Fetch post and user details
$stmt = $con->prepare("
    SELECT p.*, u.user_name, u.user_photo,u.user_id,
             (SELECT COUNT(like_id) FROM tbl_like l WHERE l.post_id = p.post_id) AS like_count,
             (SELECT COUNT(like_id) FROM tbl_like l WHERE l.post_id = p.post_id AND l.user_id = '$uid') AS user_liked,
             (SELECT COUNT(comment_id) FROM tbl_comment c WHERE c.post_id = p.post_id) AS comment_count
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
.post-actions {
        display: flex;
        padding: 8px 16px;
        border-top: 1px solid var(--border-dark);
    }
    
    .post-action {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px;
        color: var(--text-dark);
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s ease;
        cursor: pointer;
        border: none;
        background: none;
        font-family: inherit;
        font-size: inherit;
    }
    
    .post-action:hover {
        color: var(--white);
        background-color: rgba(26, 75, 50, 0.2);
    }
    
    .post-action i {
        margin-right: 8px;
        font-size: 1.1rem;
    }
    
    /* Like Button */
    .liked {
        color: red;
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
.modal {
    display: none; /* Hidden by default */
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #1e1e1e;
    margin: 10% auto;
    padding: 20px;
    border: 1px solid #333;
    border-radius: 10px;
    width: 400px;
    color: #fff;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.close-modal {
    cursor: pointer;
    font-size: 20px;
    color: #aaa;
}

.close-modal:hover {
    color: white;
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
        <!-- ✅ Post Actions -->
        <div class="post-actions">
            <button type="button" 
        class="post-action like-btn" 
        data-post-id="<?php echo $post_id ?>" 
        data-liked="<?php echo $post['user_liked'] ? 'true' : 'false' ?>">
    <i class="<?php echo ($post['user_liked'] > 0) ? 'fas fa-heart liked' : 'far fa-heart'; ?>" 
       id="icon-<?php echo $post_id ?>"></i>
    <span id="like-count-<?php echo $post_id ?>"><?php echo $post['like_count'] ?></span>
</button>
            <a href="Comment.php?cid=<?php echo $post_id ?>" class="post-action">
                <i class="far fa-comment"></i>
                <span><?php echo $post['comment_count'] ?></span>
            </a>
            <button type="button" class="post-action share-btn" data-post-id="<?php echo $post_id ?>">
    <i class="fas fa-share"></i> <span>Share</span>
</button>
        </div>
    </div>
</div>

 <!-- share modal -->
<div id="postShareModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Share Post</h2>
            <span class="close-modal" id="closePostShareModal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="shareForm">
                <input type="hidden" name="original_post_id" id="share_post_id" value="">

                <!-- Friends -->
                <label>Select Friends:</label>
                <select name="friends[]" multiple id="share_friends" style="width:100%;padding:5px;">
                    <?php if(!empty($friends)){ foreach($friends as $f){ ?>
                        <option value="<?= $f['user_id'] ?>"><?= htmlspecialchars($f['user_name']) ?></option>
                    <?php }} ?>
                </select><br><br>

                <!-- Groups -->
                <label>Select Groups:</label>
                <select name="groups[]" multiple id="share_groups" style="width:100%;padding:5px;">
                    <?php if(!empty($groups)){ foreach($groups as $g){ ?>
                        <option value="<?= $g['group_id'] ?>"><?= htmlspecialchars($g['group_name']) ?></option>
                    <?php }} ?>
                </select><br><br>

                <button type="submit" style="background:var(--deep-forest);color:white;padding:8px 15px;border:none;border-radius:4px;">Share</button>
            </form>
        </div>
    </div>
</div>

<script src="../Assets/JQ/JQuery.js"></script>
<script>
$(document).ready(function() {
    // Like functionality
    $('.like-btn').click(function() {
        const postId = $(this).data('post-id');
        const countElement = $('#like-count-' + postId);
        const icon = $('#icon-' + postId);

        $.ajax({
            url: '../Assets/AjaxPages/AjaxLike.php',
            type: 'GET',
            data: { pid: postId },
            success: function(response) {
                const parts = response.split('|');
                if (parts.length === 2) {
                    const status = parts[0];
                    const count = parts[1];
                    
                    countElement.text(count);

                    if (status === 'liked') {
                        icon.removeClass('far fa-heart').addClass('fas fa-heart liked');
                    } else if (status === 'disliked') {
                        icon.removeClass('fas fa-heart liked').addClass('far fa-heart');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
            }
        });
    });

// Open modal when clicking share button
$(document).on('click', '.share-btn', function(e){
    e.preventDefault();
    console.log("Share button clicked ✅"); 
    var postId = $(this).data('post-id');
    $('#share_post_id').val(postId); // set hidden input
    $('#postShareModal').show();
});

// Close modal
$('#closePostShareModal').click(function(){
    $('#postShareModal').hide();
});

// Close when clicking outside
$(window).click(function(e){
    if ($(e.target).is('#postShareModal')) {
        $('#postShareModal').hide();
    }
});

// AJAX form submission
$('#shareForm').submit(function(e){
    e.preventDefault();
    var formData = new FormData(this);
    fetch('SharePost.php', { method:'POST', body: formData })
    .then(res => res.text())
    .then(data => {
        alert(data);
        $('#postShareModal').hide(); 
    });
});

</script>
</body>
</html>
