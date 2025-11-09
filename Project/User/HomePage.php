<?php
session_start();
include("../Assets/Connection/Connection.php");
include("Header.php");

if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit;
}
$sel="select * from tbl_user where user_id='".$_SESSION['uid']."'";
$rowuser=$con->query($sel);
$datauser=$rowuser->fetch_assoc();
// Get user details
$uid = mysqli_real_escape_string($con, $_SESSION['uid']);
$uname = $_SESSION['uname'];

// Get user friends for sharing
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


// Get posts for the feed
$post_sql = "SELECT p.*, u.user_name, u.user_photo,u.user_id,
             (SELECT COUNT(like_id) FROM tbl_like l WHERE l.post_id = p.post_id) AS like_count,
             (SELECT COUNT(like_id) FROM tbl_like l WHERE l.post_id = p.post_id AND l.user_id = '$uid') AS user_liked,
             (SELECT COUNT(comment_id) FROM tbl_comment c WHERE c.post_id = p.post_id) AS comment_count
             FROM tbl_post p 
             INNER JOIN tbl_user u ON p.user_id = u.user_id 
             ORDER BY p.post_date DESC";
$post_result = $con->query($post_sql);

if (isset($_POST['delete_post'])) {
    $postId = mysqli_real_escape_string($con, $_POST['delete_post_id']);
    $uid = $_SESSION['uid'];

    // Verify ownership
    $check = $con->query("SELECT post_photo FROM tbl_post WHERE post_id='$postId' AND user_id='$uid'");
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

// Get friend suggestions
$suggestion_sql = "SELECT u.* FROM tbl_user u 
                  WHERE u.user_status = 'active'
                  AND u.user_id != '$uid'
                  AND u.user_id NOT IN (
                      SELECT CASE 
                          WHEN user_from_id = '$uid' THEN user_to_id 
                          WHEN user_to_id = '$uid' THEN user_from_id 
                      END
                      FROM tbl_friends
                      WHERE user_from_id = '$uid' OR user_to_id = '$uid'
                  )
                  ORDER BY RAND() 
                  LIMIT 4";
$suggestion_result = $con->query($suggestion_sql);

// Get group suggestions
 $group_suggestion_sql = "SELECT * FROM tbl_group g 
                        WHERE g.group_id NOT IN 
                        (SELECT group_id FROM tbl_groupmembers WHERE user_id = '$uid')
                        ORDER BY RAND() LIMIT 2";
$group_suggestion_result = $con->query($group_suggestion_sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexo - Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Global Styles - Using only black, white, and deep forest green */
        :root {
            --black: #000000;
            --white: #FFFFFF;
            --deep-forest-green: #013220;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--black);
            color: var(--white);
        }

        .main-container {
            display: flex;
            max-width: 1200px;
            margin: 70px auto 0;
            padding: 20px;
            gap: 20px;
        }

        .left-sidebar {
            width: 25%;
            position: sticky;
            top: 70px;
            height: fit-content;
        }

        .right-sidebar {
            width: 25%;
            position: sticky;
            top: 70px;
            height: fit-content;
        }

        .content {
            flex: 1;
        }

        .welcome-card,
        .quick-links-card,
        .suggestions-card {
            background-color: var(--black);
            border-radius: 8px;
            border: 1px solid var(--white);
            padding: 15px;
            margin-bottom: 20px;
        }

        .welcome-card h2 {
            margin-bottom: 15px;
            color: var(--white);
        }

        .welcome-card p {
            color: var(--white);
            opacity: 0.8;
            margin-bottom: 10px;
        }

        .quick-link {
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 5px;
            transition: background-color 0.3s;
        }

        .quick-link:hover {
            background-color: var(--deep-forest-green);
        }

        .quick-link i {
            width: 30px;
            color: var(--white);
            opacity: 0.7;
            font-size: 18px;
        }

        .quick-link span {
            flex: 1;
            color: var(--white);
        }

        .quick-link a {
            text-decoration: none;
            color: inherit;
            display: block;
            width: 100%;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            overflow: auto;
        }

        .modal-content {
            background-color: var(--black);
            margin: 10% auto;
            padding: 0;
            border: 1px solid var(--white);
            width: 90%;
            max-width: 500px;
            border-radius: 8px;
            position: relative;
            top: 50%;
            transform: translateY(-50%);
        }

        .suggestions-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .suggestions-header h3 {
            color: var(--white);
            opacity: 0.8;
            font-size: 16px;
        }

        .suggestions-header a {
            color: var(--white);
            font-size: 14px;
            text-decoration: none;
        }

        .suggestion {
            display: flex;
            align-items: center;
            padding: 8px 0;
        }

        .suggestion-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            margin-right: 10px;
            overflow: hidden;
            border: 1px solid var(--deep-forest-green);
        }

        .suggestion-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .suggestion-info {
            flex: 1;
        }

        .suggestion-info h4 {
            font-size: 14px;
            color: var(--white);
        }

        .suggestion-info p {
            font-size: 12px;
            color: var(--white);
            opacity: 0.7;
        }

        .follow-btn {
            color: #3C99DC;
            font-weight: 600;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
        }

        .post-card {
            background-color: var(--black);
            border-radius: 8px;
            border: 1px solid var(--white);
            padding: 15px;
            margin-bottom: 20px;
        }

        .post-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .post-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            overflow: hidden;
            border: 1px solid var(--deep-forest-green);
        }

        .post-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .post-user {
            flex: 1;
        }

        .post-user h4 {
            font-size: 15px;
            color: var(--white);
        }

        .post-user p {
            font-size: 13px;
            color: var(--white);
            opacity: 0.7;
        }

        /* Post Options Menu */
        .post-options {
            position: relative;
            margin-left: auto;
        }

        .post-options-toggle {
            padding: 8px;
            border-radius: 50%;
            cursor: pointer;
            color: var(--white);
            opacity: 0.7;
            transition: all 0.3s;
        }

        .post-options-toggle:hover {
            background-color: var(--deep-forest-green);
            opacity: 1;
        }

        .post-options-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background-color: var(--black);
            border: 1px solid var(--white);
            border-radius: 8px;
            min-width: 180px;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .post-options-menu.show {
            display: block;
        }

        .post-option {
            padding: 10px 15px;
            display: flex;
            align-items: center;
            cursor: pointer;
            color: var(--white);
            transition: background-color 0.3s;
        }

        .post-option:hover {
            background-color: var(--deep-forest-green);
        }

        .post-option i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .post-option.report-btn i {
            color: #ff6b6b;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            overflow: auto;
        }

        .modal-content {
            background-color: var(--black);
            margin: 10% auto;
            padding: 0;
            border: 1px solid var(--white);
            width: 90%;
            max-width: 500px;
            border-radius: 8px;
            position: relative;
            top: 50%;
            transform: translateY(-50%);
        }

        .modal-header {
            padding: 15px;
            background-color: var(--deep-forest-green);
            color: white;
            border-radius: 8px 8px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.2rem;
        }

        .close-modal {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close-modal:hover {
            color: #ccc;
        }

        .modal-body {
            padding: 20px;
            color: var(--white);
        }

        .post-content {
            margin-bottom: 15px;
        }

        .post-content p {
            margin-bottom: 10px;
            color: var(--white);
        }

        .post-media {
            width: 100%;
            border-radius: 8px;
            margin-top: 10px;
            border: 1px solid var(--white);
        }

        .post-actions {
            display: flex;
            border-top: 1px solid var(--white);
            padding-top: 10px;
        }

        .post-action {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            border-radius: 5px;
            color: var(--white);
            opacity: 0.7;
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
        }

        .post-action:hover {
            opacity: 1;
            background-color: var(--deep-forest-green);
        }

        .post-action i {
            margin-right: 8px;
        }

        .liked {
            color: red;
            opacity: 1;
        }

        .post-input {
            display: block;
            width: 100%;
            padding: 10px;
            border-radius: 20px;
            background-color: var(--black);
            border: 1px solid var(--white);
            color: var(--white);
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .post-input:hover {
            background-color: var(--deep-forest-green);
        }

        @media (max-width: 992px) {
            .left-sidebar {
                display: none;
            }

            .right-sidebar {
                width: 35%;
            }
        }

        @media (max-width: 768px) {
            .right-sidebar {
                display: none;
            }

            .header-container {
                justify-content: space-around;
            }
        }
    </style>
</head>

<body>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Left Sidebar -->
        <div class="left-sidebar">
            <div class="welcome-card">
                <h2>Welcome
                    <?php echo htmlspecialchars($uname) ?>
                </h2>
                <p>What would you like to do today?</p>
            </div>

            <div class="quick-links-card">
                <div class="quick-link">
                    <i class="fas fa-user-circle"></i>
                    <span><a href="MyProfile.php">My Profile</a></span>
                </div>
                <div class="quick-link">
                    <i class="fas fa-search"></i>
                    <span><a href="UserSearch.php">Explore</a></span>
                </div>
                <div class="quick-link">
                    <i class="fas fa-user-plus"></i>
                    <span><a href="FollowRequests.php">Follow Requests</a></span>
                </div>
                <div class="quick-link">
                    <i class="fas fa-users"></i>
                    <span><a href="FriendsList.php">Friends</a></span>
                </div>
                <div class="quick-link">
                    <i class="fas fa-comments"></i>
                    <span><a href="ChatList.php">Chats</a></span>
                </div>

                <div class="quick-link">
                    <i class="fas fa-plus-square"></i>
                    <span><a href="Post.php">Post Something</a></span>
                </div>

                <div class="quick-link">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><a href="Complaint.php">File a Complaint</a></span>
                </div>
                <div class="quick-link">
                    <i class="fas fa-comment-alt"></i>
                    <span><a href="Feedback.php">Share Feedback</a></span>
                </div>
                <div class="quick-link">
                    <i class="fas fa-users"></i>
                    <span><a href="Groups.php">Groups</a></span>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content">
            <!-- Create Post -->
            <div class="post-card">
                <div class="post-header">
                    <div class="post-avatar">

                        <img src="../Assets/Files/UserDocs/<?php echo ($datauser['user_photo']?:'default.avif') ?>" alt="">

                    </div>
                    <div class="post-user">
                        <h4>
                            <?php echo htmlspecialchars($uname) ?>
                        </h4>
                    </div>
                </div>
                <div class="post-content">
                    <a href="Post.php" class="post-input">What's on your mind?</a>
                </div>
                <div class="post-actions">
                    <a href="Post.php" class="post-action">
                        <i class="fas fa-images" style="color: #45bd62;"></i>
                        <span>Photo/Video</span>
                    </a>
                    <a href="Post.php" class="post-action">
                        <i class="fas fa-smile" style="color: #f7b928;"></i>
                        <span>Feeling/Activity</span>
                    </a>
                </div>
            </div>

            <!-- Posts Feed -->
            <?php if ($post_result->num_rows > 0) {
                while ($post = $post_result->fetch_assoc()) {
                    $file = $post["post_photo"];
                    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    $file_path = '../Assets/Files/PostDocs/' . $file;
                    $post_id = $post['post_id'];
            ?>
            <div class="post-card">
                <div class="post-header">
                    <div class="post-avatar">
                        <a href="ViewProfile.php?pid=<?php echo $post['user_id']?>"><img
                                src="../Assets/Files/UserDocs/<?php echo htmlspecialchars($post['user_photo']?:'default.avif') ?>"
                                alt="<?php echo htmlspecialchars($post['user_name']) ?>"></a>
                    </div>
                    <div class="post-user">
                        <h4>
                            <?php echo htmlspecialchars($post['user_name']) ?>
                        </h4>
                        <p>
                            <?php echo date('M d, Y', strtotime($post["post_date"])) ?> · <i
                                class="fas fa-globe-americas"></i>
                        </p>
                    </div>
                    <div class="post-options">
                        <div class="post-options-toggle">
                            <i class="fas fa-ellipsis-v"></i>
                        </div>
                        <div class="post-options-menu">
    <?php if ($post['user_id'] == $_SESSION['uid']) { ?>
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
                    <p>
                        <?php echo htmlspecialchars($post["post_caption"]) ?>
                    </p>
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
                    <div class="post-action like-btn" data-post-id="<?php echo $post_id ?>"
                        data-liked="<?php echo $post['user_liked'] > 0 ? 'true' : 'false' ?>">
                        <i class="<?php echo $post['user_liked'] > 0 ? 'fas fa-heart liked' : 'far fa-heart' ?>"
                            id="icon-<?php echo $post_id ?>"></i>
                        <span id="count-<?php echo $post_id ?>">
                            <?php echo $post['like_count'] ?>
                        </span>
                    </div>
                    <a href="Comment.php?cid=<?php echo $post_id ?>" class="post-action">
                        <i class="far fa-comment"></i>
                        <span> <?php echo $post['comment_count'] ?> </span>
                    </a>
                    <a href="#" class="post-action share-btn" data-post-id="<?php echo $post_id ?>">
                    <i class="fas fa-share"></i> <span>Share</span>
                    </a>
                </div>
            </div>
            <?php } ?>
            <?php } else { ?>
            <div class="post-card">
                <p>No posts available. Be the first to post something!</p>
            </div>
            <?php } ?>
        </div>

        <!-- Right Sidebar -->
        <div class="right-sidebar">
            <div class="suggestions-card">
                <div class="suggestions-header">
                    <h3>People You May Know</h3>
                    <a href="UserSearch.php">See All</a>
                </div>

                <?php if ($suggestion_result->num_rows > 0) {
                    while ($suggestion = $suggestion_result->fetch_assoc()) { ?>
                <div class="suggestion">
                    <div class="suggestion-avatar">
                        <img src="../Assets/Files/UserDocs/<?php echo htmlspecialchars($suggestion['user_photo']?:'default.avif') ?>"
                            alt="<i class='far fa-user'></i>">
                    </div>
                    <div class="suggestion-info">
                        <h4>
                            <?php echo htmlspecialchars($suggestion['user_name']) ?>
                        </h4>
                        <p>New to Nexo</p>
                    </div>
                    <a href="UserSearch.php?fid=<?php echo $suggestion['user_id'] ?>" class="follow-btn">Add</a>
                </div>
                <?php } ?>
                <?php } else { ?>
                <p>No suggestions available</p>
                <?php } ?>
            </div>

            <div class="suggestions-card">
                <div class="suggestions-header">
                    <h3>Groups You May Like</h3>
                    <a href="Groups.php">See All</a>
                </div>

                <?php if ($group_suggestion_result->num_rows > 0) {
                    while ($group = $group_suggestion_result->fetch_assoc()) { ?>
                <div class="suggestion">
                    <div class="suggestion-avatar">
                        <img src="../Assets/Files/GroupDocs/<?php echo ($group['group_photo']?:'default.png') ?>" alt="">
                    </div>
                    <div class="suggestion-info">
                        <h4>
                            <?php echo htmlspecialchars($group['group_name']) ?>
                        </h4>
                        <p>
                            <?php echo htmlspecialchars($group['group_description']) ?>
                        </p>
                    </div>
                    <a href="Groups.php?join=<?php echo $group['group_id'] ?>" class="follow-btn">Join</a>
                </div>
                <?php } ?>
                <?php } else { ?>
                <p>No group suggestions available</p>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Report Modal -->
    <div id="reportModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Report Post</h2>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <form id="reportForm">
                    <input type="hidden" id="report_post_id" name="post_id">
                    <div style="margin-bottom:15px;">
                        <label style="display:block;margin-bottom:5px;">Reason for reporting:</label>
                        <select name="reason"
                            style="width:100%;padding:8px;border:1px solid var(--deep-forest-green);border-radius:4px;background-color:#111;color:var(--white);"
                            required>
                            <option value="">Select a reason</option>
                            <option value="spam">Spam or misleading</option>
                            <option value="harassment">Harassment or bullying</option>
                            <option value="hate_speech">Hate speech</option>
                            <option value="violence">Violence or harmful content</option>
                            <option value="nudity">Nudity or sexual content</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div style="margin-bottom:15px;">
                        <label style="display:block;margin-bottom:5px;">Additional details (optional):</label>
                        <textarea name="details"
                            style="width:100%;padding:8px;border:1px solid var(--deep-forest-green);border-radius:4px;min-height:100px;background-color:#111;color:var(--white);"></textarea>
                    </div>
                    <button type="submit"
                        style="background-color:var(--deep-forest-green);color:var(--white);border:none;padding:10px 15px;border-radius:4px;cursor:pointer;">Submit
                        Report</button>
                </form>
            </div>
        </div>
    </div>

    <!-- share modal -->
    <div id="shareModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Share Post</h2>
            <span class="close-modal" id="closeShareModal">&times;</span>
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

                <button type="submit" style="background:var(--deep-forest-green);color:white;padding:8px 15px;border:none;border-radius:4px;">Share</button>
            </form>
        </div>
    </div>
</div>


   <?php include("Footer.php");  ?>

    <script src="../Assets/JQ/JQuery.js"></script>
    <script>
        $(document).ready(function () {
            // Like functionality
            $('.like-btn').click(function () {
                const postId = $(this).data('post-id');

                $.ajax({
                    url: '../Assets/AjaxPages/AjaxLike.php?pid=' + postId,
                    type: 'GET',
                    success: function (response) {
                        const parts = response.split('|');
                        if (parts.length === 2) {
                            const status = parts[0];
                            const count = parts[1];

                            const icon = $('#icon-' + postId);
                            const countElement = $('#count-' + postId);

                            // Update count directly from backend
                            countElement.text(count);

                            // Update icon based on status
                            if (status === 'liked') {
                                icon.removeClass('far fa-heart').addClass('fas fa-heart liked');
                            } else if (status === 'disliked') {
                                icon.removeClass('fas fa-heart liked').addClass('far fa-heart');
                            }
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                    }
                });
            });

            // Search functionality
            $('.search-bar input').on('keyup', function (e) {
                if (e.key === 'Enter') {
                    const query = $(this).val();
                    if (query.trim() !== '') {
                        window.location.href = 'UserSearch.php?search=' + encodeURIComponent(query);
                    }
                }
            });
        });

        // 3-dot menu functionality
        $(document).on('click', '.post-options-toggle', function (e) {
            e.stopPropagation();
            // Close any other open menus
            $('.post-options-menu').removeClass('show');
            // Open this menu
            $(this).siblings('.post-options-menu').addClass('show');
        });

        // Close menu when clicking elsewhere
        $(document).click(function () {
            $('.post-options-menu').removeClass('show');
        });

        // Prevent menu from closing when clicking inside it
        $(document).on('click', '.post-options-menu', function (e) {
            e.stopPropagation();
        });

        // Report functionality from menu
        $(document).on('click', '.report-btn', function () {
            const postId = $(this).data('post-id');
            $('#report_post_id').val(postId);
            $('#reportModal').show();
            // Close the menu
            $('.post-options-menu').removeClass('show');
        });

        // Close modal when clicking X
        $('.close-modal').click(function () {
            $('#reportModal').hide();
        });

        // Close modal when clicking outside
        $(window).click(function (event) {
            if ($(event.target).is('#reportModal')) {
                $('#reportModal').hide();
            }
        });

        // Handle report form submission
        $('#reportForm').submit(function (e) {
            e.preventDefault();

            $.ajax({
                url: '../Assets/AjaxPages/AjaxReport.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function (response) {
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
                error: function () {
                    alert('An error occurred. Please try again.');
                }
            });
        });
        
        // Open modal when clicking share button
$(document).on('click', '.share-btn', function(e){
    e.preventDefault();
    var postId = $(this).data('post-id');
    $('#share_post_id').val(postId); // set hidden input
    $('#shareModal').show();
});

// Close modal
$('#closeShareModal').click(function(){
    $('#shareModal').hide();
});

// Close when clicking outside
$(window).click(function(e){
    if ($(e.target).is('#shareModal')) {
        $('#shareModal').hide();
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
        $('#shareModal').hide();
    });
});

</script>
</body>

</html>