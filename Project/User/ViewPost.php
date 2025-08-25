<?php
include '../Assets/Connection/Connection.php';

$uid = mysqli_real_escape_string($con, $_SESSION['uid']);

$sql = "SELECT p.*, u.user_name, u.user_photo,u.user_id,
             (SELECT COUNT(like_id) FROM tbl_like l WHERE l.post_id = p.post_id) AS like_count,
             (SELECT COUNT(like_id) FROM tbl_like l WHERE l.post_id = p.post_id AND l.user_id = '$uid') AS user_liked,
             (SELECT COUNT(comment_id) FROM tbl_comment c WHERE c.post_id = p.post_id) AS comment_count
             FROM tbl_post p 
             INNER JOIN tbl_user u ON p.user_id = u.user_id 
             WHERE p.user_id = '$uid'
             ORDER BY p.post_date DESC";
$result = $con->query($sql);

if (isset($_POST['delete_post'])) {
    $postId = mysqli_real_escape_string($con, $_POST['delete_post_id']);
    $loggedInUid = $_SESSION['uid'];

    // Verify ownership
    $check = $con->query("SELECT post_photo FROM tbl_post WHERE post_id='$postId' AND user_id='$loggedInUid'");
    if ($check && $check->num_rows > 0) {
        $row = $check->fetch_assoc();

        // Delete file if exists
        if (!empty($row['post_photo'])) {
            $filePath = "../Assets/Files/PostDocs/" . $row['post_photo'];
            if (file_exists($filePath) && is_file($filePath)) {
                unlink($filePath);
            }
        }

        // Delete record
        $con->query("DELETE FROM tbl_post WHERE post_id='$postId' AND user_id='$uid'");
    }

    // Refresh feed
    header("Location: Home.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Posts | Nexo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
    :root {
        /* Color Scheme */
        --black: #000000;
        --white: #FFFFFF;
        --deep-forest: #013220;
        --forest-green: #1a4b32;
        --light-forest: #2c6e49;
        
        /* Dark Mode Variables */
        --bg-dark: #121212;
        --card-dark: #1e1e1e;
        --text-dark: #e0e0e0;
        --border-dark: #333333;
    }
    
    /* Base Styles */
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }
    
    body {
        background-color: var(--bg-dark);
        color: var(--text-dark);
        font-family: 'Segoe UI', system-ui, sans-serif;
        line-height: 1.6;
    }
    
    /* Container */
    .container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }
    
    /* Posts */
    .post-card {
        background-color: var(--card-dark);
        border-radius: 12px;
        border: 1px solid var(--border-dark);
        margin-bottom: 25px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: transform 0.2s ease;
    }
    
    .post-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }
    
    /* Header */
    .post-header {
        display: flex;
        align-items: center;
        padding: 16px;
        border-bottom: 1px solid var(--border-dark);
        position: relative;
    }
    
    .post-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        margin-right: 12px;
        overflow: hidden;
        border: 2px solid var(--forest-green);
        flex-shrink: 0;
    }
    
    .post-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .post-user {
        flex-grow: 1;
        min-width: 0;
    }
    
    .post-user h4 {
        color: var(--white);
        font-weight: 600;
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .post-user p {
        color: var(--text-dark);
        opacity: 0.7;
        font-size: 0.85rem;
    }
    
    /* Content */
    .post-content {
        padding: 16px;
    }
    
    .post-text {
        color: var(--text-dark);
        margin-bottom: 12px;
        line-height: 1.5;
        word-wrap: break-word;
    }
    
    .post-media {
        width: 100%;
        border-radius: 8px;
        margin-top: 12px;
        max-height: 500px;
        object-fit: contain;
        border: 1px solid var(--border-dark);
        display: block;
    }
    
    /* Actions */
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
    
    /* Stats */
    .post-stats {
        display: flex;
        justify-content: space-between;
        padding: 12px 16px;
        color: var(--text-dark);
        opacity: 0.8;
        font-size: 0.9rem;
        border-top: 1px solid var(--border-dark);
    }
    
    /* Options Menu */
    .post-options {
        position: relative;
    }
    
    .post-options-toggle {
        padding: 8px;
        border-radius: 50%;
        cursor: pointer;
        color: var(--text-dark);
        transition: all 0.2s ease;
        background: none;
        border: none;
    }
    
    .post-options-toggle:hover {
        background-color: rgba(26, 75, 50, 0.2);
    }
    
    .post-options-menu {
        position: absolute;
        right: 0;
        top: 100%;
        background-color: var(--card-dark);
        border: 1px solid var(--border-dark);
        border-radius: 8px;
        min-width: 180px;
        z-index: 100;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        display: none;
    }
    
    .post-options-menu.show {
        display: block;
    }
    
    .post-option {
        padding: 12px 16px;
        display: flex;
        align-items: center;
        cursor: pointer;
        color: var(--text-dark);
        transition: background-color 0.2s ease;
        text-decoration: none;
        background: none;
        border: none;
        width: 100%;
        text-align: left;
        font-family: inherit;
    }
    
    .post-option:hover {
        background-color: rgba(26, 75, 50, 0.2);
    }
    
    .post-option i {
        margin-right: 10px;
        width: 20px;
        text-align: center;
    }
    
    /* Modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.7);
    }
    
    .modal-content {
        background-color: var(--card-dark);
        margin: 10% auto;
        padding: 24px;
        border-radius: 12px;
        width: 90%;
        max-width: 500px;
        border: 1px solid var(--forest-green);
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .modal-header h2 {
        color: var(--white);
    }
    
    .close-modal {
        color: var(--text-dark);
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        transition: color 0.2s ease;
        background: none;
        border: none;
    }
    
    .close-modal:hover {
        color: var(--white);
    }
    
    /* Form Elements */
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: var(--text-dark);
    }
    
    .form-group select, 
    .form-group textarea {
        width: 100%;
        padding: 12px;
        background-color: var(--bg-dark);
        border: 1px solid var(--border-dark);
        border-radius: 6px;
        color: var(--text-dark);
        font-size: 1rem;
    }
    
    .form-group textarea {
        min-height: 120px;
        resize: vertical;
    }
    
    .submit-btn {
        background-color: var(--forest-green);
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        width: 100%;
        transition: background-color 0.2s ease;
    }
    
    .submit-btn:hover {
        background-color: var(--light-forest);
    }
    
    /* No Posts */
    .no-posts {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-dark);
        opacity: 0.7;
    }
    
    .no-posts i {
        font-size: 48px;
        margin-bottom: 20px;
        color: var(--forest-green);
    }
    
    /* Responsive Adjustments */
    @media (max-width: 600px) {
        .container {
            padding: 10px;
        }
        
        .post-avatar {
            width: 40px;
            height: 40px;
        }
        
        .post-header {
            padding: 12px;
        }
        
        .post-content {
            padding: 12px;
        }
    }
</style>
</head>
<body>
    <div class="container">
        <?php if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $file = $row["post_photo"];
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $file_path = '../Assets/Files/PostDocs/' . $file;
                $post_id = $row['post_id'];
        ?>
        <div class="post-card">
            <div class="post-header">
                <div class="post-avatar">
                    <img src="../Assets/Files/UserDocs/<?php echo htmlspecialchars($row['user_photo']) ?>" alt="<?php echo htmlspecialchars($row['user_name']) ?>">
                </div>
                <div class="post-user">
                    <h4><?php echo htmlspecialchars($row['user_name']) ?></h4>
                    <p><?php echo date('M d, Y • h:i A', strtotime($row['post_date'])) ?></p>
                </div>
                <div class="post-options">
                    <button class="post-options-toggle">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <div class="post-options-menu">
              <?php if ($row['user_id'] == $loggedInUid) { ?>
        <form method="post" action="" onsubmit="return confirm('Are you sure you want to delete this post?');">
            <input type="hidden" name="delete_post_id" value="<?php echo $post_id; ?>">
            <button type="submit" name="delete_post" class="post-option" style="background:none;border:none;width:100%;text-align:left;">
                <i class="fas fa-trash"></i>
                <span>Delete Post</span>
            </button>
        </form>
    <?php } else { ?>
        <div class="post-option report-btn" data-post-id="<?php echo $post_id ?>">
            <i class="fas fa-flag"></i>
            <span>Report Post</span>
        </div>
    <?php } ?>
</div>
                </div>
            </div>
            
            <div class="post-content">
                <p class="post-text"><?php echo htmlspecialchars($row['post_caption']) ?></p>
                <?php if (!empty($file)) { ?>
                    <?php if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) { ?>
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
                <button class="post-action like-btn" data-post-id="<?php echo $post_id ?>" data-liked="<?php echo $row['user_liked'] ? 'true' : 'false' ?>">
                    <i class="<?php echo ($row['user_liked'] > 0) ? 'fas fa-heart liked' : 'far fa-heart'; ?>" id="icon-<?php echo $post_id ?>"></i>
                    <span id="like-count-<?php echo $post_id ?>"><?php echo $row['like_count'] ?></span>
                </button>
                <a href="Comment.php?cid=<?php echo $post_id ?>" class="post-action">
                    <i class="far fa-comment"></i>
                    <span><?php echo $row['comment_count'] ?></span>
                </a>
                <button class="post-action share-btn" data-post-id="<?php echo $post_id ?>">
                    <i class="fas fa-share"></i>
                    <span>Share</span>
                </button>
            </div>
        </div>
        <?php } ?>
        <?php } else { ?>
            <div class="no-posts">
                <i class="far fa-newspaper"></i>
                <p>No posts available</p>
            </div>
        <?php } ?>
    </div>

    <div id="reportModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Report Post</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="reportForm">
                    <input type="hidden" id="report_post_id" name="post_id">
                    <div class="form-group">
                        <label for="reason">Reason for reporting:</label>
                        <select name="reason" id="reason" required>
                            <option value="">Select a reason</option>
                            <option value="spam">Spam or misleading</option>
                            <option value="harassment">Harassment or bullying</option>
                            <option value="hate_speech">Hate speech</option>
                            <option value="violence">Violence or harmful content</option>
                            <option value="nudity">Nudity or sexual content</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="details">Additional details (optional):</label>
                        <textarea name="details" id="details"></textarea>
                    </div>
                    <button type="submit" class="submit-btn">Submit Report</button>
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

    // Share functionality
    $(document).on('click', '.share-btn', function() {
        const postId = $(this).data('post-id');
        const postUrl = window.location.origin + window.location.pathname.replace('view-post-like.php', '') + 'Comment.php?cid=' + postId;
        
        if (navigator.share) {
            navigator.share({
                title: 'Check out this post',
                url: postUrl
            }).catch(console.error);
        } else {
            navigator.clipboard.writeText(postUrl).then(() => {
                alert('Link copied to clipboard!');
            });
        }
    });

    // Post options menu toggle
    $(document).on('click', '.post-options-toggle', function(e) {
        e.stopPropagation();
        // Close other menus first
        $('.post-options-menu.show').not($(this).siblings('.post-options-menu')).removeClass('show');
        // Toggle this menu
        $(this).siblings('.post-options-menu').toggleClass('show');
    });

    // Close menu when clicking elsewhere
    $(document).click(function() {
        $('.post-options-menu').removeClass('show');
    });

    // Prevent menu from closing when clicking inside it
    $(document).on('click', '.post-options-menu', function(e) {
        e.stopPropagation();
    });

    // Report functionality
    $(document).on('click', '.report-btn', function() {
        const postId = $(this).data('post-id');
        $('#report_post_id').val(postId);
        $('#reportModal').show();
        $('.post-options-menu').removeClass('show');
    });

    // Close modal
    $('.close-modal').click(function() {
        $('#reportModal').hide();
    });

    // Close modal when clicking outside
    $(window).click(function(event) {
        if ($(event.target).is('#reportModal')) {
            $('#reportModal').hide();
        }
    });

    // Handle report form submission
    $('#reportForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '../Assets/AjaxPages/AjaxReport.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response === 'success') {
                    alert('Thank you for reporting. We will review this content.');
                    $('#reportModal').hide();
                } else if (response === 'already_reported') {
                    alert('You have already reported this post.');
                    $('#reportModal').hide();
                } else {
                    alert('Failed to submit report. Please try again.');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });
});
</script>
</body>
</html>