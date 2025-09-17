<?php
include '../Assets/Connection/Connection.php';
session_start();

if (!isset($_SESSION["uid"])) {
    header("Location: login.php");
    exit;
}

$loggedInUid = $_SESSION['uid'];     // logged-in user
$profileId   = intval($_GET['pid'] ?? 0); // profile being viewed

// ✅ Friends of logged-in user
$loggedInFriends = [];
$friendQuery = "
    SELECT u.user_id, u.user_name 
    FROM tbl_user u
    WHERE u.user_id IN (
        SELECT user_from_id FROM tbl_friends 
        WHERE user_to_id = '$loggedInUid' AND friends_status = 1
        UNION
        SELECT user_to_id FROM tbl_friends 
        WHERE user_from_id = '$loggedInUid' AND friends_status = 1
    )
    AND u.user_id != '$loggedInUid'
    ORDER BY u.user_name ASC
";
$friendResult = $con->query($friendQuery);
if ($friendResult) {
    $loggedInFriends = $friendResult->fetch_all(MYSQLI_ASSOC);
}

// ✅ Groups of logged-in user (created OR joined)
$loggedInGroups = [];
$groupRes = $con->query("
    SELECT DISTINCT g.group_id, g.group_name
    FROM tbl_group g
    LEFT JOIN tbl_groupmembers gm ON g.group_id = gm.group_id
    WHERE g.user_id = '$loggedInUid' 
       OR (gm.user_id = '$loggedInUid' AND gm.groupmembers_status = 1)
    ORDER BY g.group_name ASC
");
if ($groupRes) {
    $loggedInGroups = $groupRes->fetch_all(MYSQLI_ASSOC);
}

$uid = $loggedInUid; 
$message = "";

// Handle Follow (send request)
if (isset($_GET['fid'])) {
    $fid = mysqli_real_escape_string($con, $_GET['fid']);
    // Check if a request already exists to avoid duplicates
    $checkQry = "SELECT friends_id FROM tbl_friends WHERE user_from_id = '$uid' AND user_to_id = '$fid'";
    $checkRes = $con->query($checkQry);
    if ($checkRes->num_rows == 0) {
        $insQry = "INSERT INTO tbl_friends (user_from_id, user_to_id, friends_status) VALUES ('$uid', '$fid', 0)";
        if ($con->query($insQry)) {
            $message = "Follow request sent successfully.";
        } else {
            $message = "Failed to send follow request.";
        }
    }
    // Redirect to clean the URL
    header("Location: ViewProfile.php?pid=" . $fid);
    exit;
}

// Handle Unfollow or Cancel Request
if (isset($_GET['ufid'])) {
    $ufid = mysqli_real_escape_string($con, $_GET['ufid']);
    // This query is one-sided. For a true friendship model, you might need to delete where the user is either from or to.
    // For a follower model, this is correct.
    $delQry = "DELETE FROM tbl_friends WHERE (user_from_id = '$uid' AND user_to_id = '$ufid') OR (user_from_id = '$ufid' AND user_to_id = '$uid')";
    if ($con->query($delQry)) {
        $message = "You have unfollowed this user.";
    } else {
        $message = "Failed to unfollow.";
    }
    // Redirect to clean the URL
    header("Location: ViewProfile.php?pid=" . $ufid);
    exit;
}

// Determine which profile to display: the one from URL ('pid') or the logged-in user's
// Standardize on $profileId (safe int), default to logged-in if not set/valid
$profileId = isset($_GET['pid']) ? intval($_GET['pid']) : $loggedInUid;
if ($profileId <= 0) {
    header("Location: ../Guest/Login.php"); // Invalid ID, redirect
    exit;
}

// Fetch profile data
$selQry = "SELECT u.*, p.place_name, d.district_name,
              (SELECT friends_status FROM tbl_friends f WHERE (f.user_from_id = '$uid' AND f.user_to_id = u.user_id) LIMIT 1) as request_sent_status,
              (SELECT friends_status FROM tbl_friends f WHERE (f.user_from_id = u.user_id AND f.user_to_id = '$uid') LIMIT 1) as request_received_status
           FROM tbl_user u 
           LEFT JOIN tbl_place p ON u.place_id = p.place_id 
           LEFT JOIN tbl_district d ON p.district_id = d.district_id 
           WHERE u.user_id = '$profileId'";

$res = $con->query($selQry);
if ($res->num_rows == 0) {
    // User not found, redirect to a safe page
    header("Location: HomePage.php");
    exit;
}
$data = $res->fetch_assoc();


// Get counts for Posts and Friends
$postCountQry = "SELECT COUNT(*) as post_count FROM tbl_post WHERE user_id = '$profileId'";
$postCountRes = $con->query($postCountQry);
$postCount = $postCountRes->fetch_assoc()['post_count'];

$friendCountQry = "SELECT COUNT(*) as friend_count FROM tbl_friends WHERE (user_from_id = '$profileId' OR user_to_id = '$profileId') AND friends_status = 1";
$friendCountRes = $con->query($friendCountQry);
$friendCount = $friendCountRes->fetch_assoc()['friend_count'];


// Determine friendship status to show correct button
$friend_status = null;
$selFriendQry = "SELECT friends_status FROM tbl_friends WHERE (user_from_id = '$uid' AND user_to_id = '$profileId') OR (user_from_id = '$profileId' AND user_to_id = '$uid')";
$resFriend = $con->query($selFriendQry);
if($resFriend->num_rows > 0){
    $dataFriend = $resFriend->fetch_assoc();
    $friend_status = $dataFriend['friends_status'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['user_name']) ?>'s Profile | Nexo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --black: #0d0d0d;
            --white: #FFFFFF;
            --deep-forest-green: #013220;
            --light-green: #1a4b32;
            --gray-dark: #1a1a1a;
            --gray-medium: #333333;
            --gray-light: #4d4d4d;
            --border-color: #262626;
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
 
        /* Main Layout */
        .main-container {
            display: flex;
            max-width: 1200px;
            margin: 80px auto 0;
            padding: 20px;
            gap: 30px;
        }
        
        /* Sidebar Styles */
        .profile-sidebar {
            width: 320px;
            position: fixed;
            top: 100px;
            height: calc(100vh - 100px);
            overflow-y: auto; 
        }
        
        .profile-card {
            background-color: var(--gray-dark);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            padding: 20px;
            text-align: center;
        }
        
        .profile-picture {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid var(--deep-forest-green);
            object-fit: cover;
            margin: 0 auto 15px;
            display: block;
        }
        
        .profile-name {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .profile-bio {
            color: #b0b0b0;
            margin-bottom: 15px;
            line-height: 1.5;
            font-size: 14px;
        }
        
        .profile-location {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #a0a0a0;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .profile-stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
            padding: 15px 0;
            border-top: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
        }
        
        .profile-stat {
            text-align: center;
            cursor: pointer;
        }
        
        .stat-count {
            font-size: 20px;
            font-weight: 700;
        }
        
        .stat-label {
            font-size: 13px;
            color: #a0a0a0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .profile-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .profile-btn {
            padding: 10px 15px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-size: 15px;
            text-align: center;
            text-decoration: none;
            display: block;
            color: var(--white);
        }
        
        .follow-btn { background-color: var(--deep-forest-green); }
        .follow-btn:hover { background-color: var(--light-green); }
        
        .unfollow-btn { background-color: var(--gray-medium); }
        .unfollow-btn:hover { background-color: var(--gray-light); }
        
        .pending-btn, .edit-btn { background-color: var(--gray-medium); }
        .pending-btn:hover, .edit-btn:hover { background-color: var(--gray-light); }
        
        .profile-share-btn {
            background-color: transparent;
            border: 1px solid var(--border-color);
        }
        .profile-share-btn:hover { background-color: var(--gray-medium); }

        /* Posts Container */
        .posts-content {
            flex: 1;
            margin-left:350px;
        }
        
        .content-header {
            margin-bottom: 20px;
            font-size: 22px;
            color: var(--white);
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }

        .no-posts {
            background-color: var(--gray-dark);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            padding: 50px;
            text-align: center;
        }
        
        .no-posts i {
            font-size: 50px;
            margin-bottom: 20px;
            color: var(--deep-forest-green);
        }
        
        .no-posts p {
            margin-bottom: 25px;
            color: #b0b0b0;
            font-size: 16px;
        }
        
        .no-posts a {
            display: inline-block;
            padding: 12px 25px;
            background-color: var(--deep-forest-green);
            color: var(--white);
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        .no-posts a:hover { background-color: var(--light-green); }

        /* Alert Message */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-top: 20px;
            font-weight: 500;
            text-align: center;
        }
        .alert-success {
            background-color: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
            border: 1px solid #2ecc71;
        }
        .alert-error {
            background-color: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
            border: 1px solid #e74c3c;
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .main-container {
                flex-direction: column;
            }
            .profile-sidebar {
                width: 100%;
                position: static;
                height: auto;
                margin-bottom: 30px;
            }
        }

        @media (max-width: 768px) {
            .header-container { padding: 0 15px; }
            .main-container { padding: 15px; gap: 20px; margin-top: 70px; }
            .nav-links a { font-size: 18px; }
            .nav-links { gap: 15px; }
            .logo { font-size: 22px; }
        }

    </style>
</head>
<body>
    <?php include("Header.php") ?>
    <div class="main-container">
        <aside class="profile-sidebar">
            <div class="profile-card">
                <img src="../Assets/Files/UserDocs/<?php echo htmlspecialchars($data['user_photo']?:'default.avif') ?>" class="profile-picture" alt="Profile Picture">
                <h1 class="profile-name"><?php echo htmlspecialchars($data['user_name']) ?></h1>

                <?php if (!empty($data['user_bio'])): ?>
                    <p class="profile-bio"><?php echo htmlspecialchars($data['user_bio']) ?></p>
                <?php endif; ?>
                
                <div class="profile-location">
                    <i class="fas fa-map-marker-alt"></i>
                    <span><?php echo htmlspecialchars($data['place_name']) ?>, <?php echo htmlspecialchars($data['district_name']) ?></span>
                </div>
                
                <div class="profile-stats">
                    <div class="profile-stat" onclick="document.getElementById('posts').scrollIntoView({ behavior: 'smooth' });">
                        <div class="stat-count"><?php echo $postCount ?></div>
                        <div class="stat-label">Posts</div>
                    </div>
                    <div class="profile-stat" onclick="window.location='FriendsList.php?user_id=<?php echo $profileId ?>'">
                        <div class="stat-count"><?php echo $friendCount ?></div>
                        <div class="stat-label">Friends</div>
                    </div>
                </div>
                
                <div class="profile-actions">
                    <?php if ($profileId != $uid): ?>
                        <?php if ($friend_status === '1'): ?>
                            <a href="ViewProfile.php?ufid=<?php echo $profileId; ?>" class="profile-btn unfollow-btn">Unfollow</a>
                        <?php elseif ($friend_status === '0'): ?>
                            <button class="profile-btn pending-btn" disabled>Request Sent</button>
                             <a href="ViewProfile.php?ufid=<?php echo $profileId; ?>" class="profile-btn unfollow-btn" style="background-color:#5c2e2e">cancel Request</a>
                        <?php else: ?>
                            <a href="ViewProfile.php?fid=<?php echo $profileId; ?>" class="profile-btn follow-btn">Follow</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="EditProfile.php" class="profile-btn edit-btn">Edit Profile</a>
                    <?php endif; ?>
<button type="button" class="profile-btn profile-share-btn" onclick="document.getElementById('profileShareModal').style.display='block'">
    <i class="fas fa-share-alt"></i> Share Profile
</button>
                </div>
                
                <?php if (!empty($message)): ?>
                    <div class="alert <?php echo strpos(strtolower($message), 'fail') === false ? 'alert-success' : 'alert-error'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
            </div>
        </aside>
        
        <main class="posts-content">
            <h2 class="content-header" id="posts">Posts</h2>
            <div class="posts-container">
                <?php
                if ($postCount > 0) {
                    $profileUserId = $profileId;
                    include 'ViewPost.php'; // This file should handle fetching 
                } else {
                    echo '<div class="no-posts">';
                    echo '<i class="far fa-images"></i>';
                    if ($profileId == $uid) {
                        echo '<p>You haven\'t shared any posts yet.</p>';
                        echo '<a href="Post.php">Create Your First Post</a>';
                    } else {
                        echo '<p>This user hasn\'t posted anything yet.</p>';
                    }
                    echo '</div>';
                }
                ?>
            </div>
        </main>
    </div>
    
   <!-- Share Profile Modal -->
<div id="profileShareModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5);">
    <div style="background-color:var(--gray-dark); margin:10% auto; padding:20px; border:1px solid var(--border-color); border-radius:12px; width:80%; max-width:500px;">
        <h3>Share <?php echo htmlspecialchars($data['user_name']); ?>'s Profile</h3>
        <form action="ShareProfile.php" method="POST">
            <input type="hidden" name="profile_id" value="<?php echo $profileId; ?>">
            <!-- Friends -->
            <div>
                <h4>Your Friends</h4>
                <!-- DEBUG: Log friends array state -->
                <?php echo "<!-- DEBUG: Modal loggedInFriends count: " . count($loggedInFriends) . ", content: " . print_r($loggedInFriends, true) . " -->"; ?>
                <?php if (is_array($loggedInFriends) && count($loggedInFriends) > 0): ?>
                    <?php foreach ($loggedInFriends as $friend): ?>
                        <label style="display:block; padding:5px 0;">
                            <input type="checkbox" name="friends[]" value="<?php echo htmlspecialchars($friend['user_id']); ?>">
                            <?php echo htmlspecialchars($friend['user_name']); ?>
                        </label>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>You don't have any friends yet.</p>
                <?php endif; ?>
            </div>
            <!-- Groups -->
            <div style="margin-top:20px;">
                <h4>Your Groups</h4>
                <!-- DEBUG: Log groups array state -->
                <?php echo "<!-- DEBUG: Modal loggedInGroups count: " . count($loggedInGroups) . ", content: " . print_r($loggedInGroups, true) . " -->"; ?>
                <?php if (is_array($loggedInGroups) && count($loggedInGroups) > 0): ?>
                    <?php foreach ($loggedInGroups as $group): ?>
                        <label style="display:block; padding:5px 0;">
                            <input type="checkbox" name="groups[]" value="<?php echo htmlspecialchars($group['group_id']); ?>">
                            <?php echo htmlspecialchars($group['group_name']); ?>
                        </label>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>You don't have any groups yet.</p>
                <?php endif; ?>
            </div>
            <div style="margin-top:20px; display:flex; gap:10px; justify-content:flex-end;">
                <button type="submit" class="profile-btn follow-btn">Share</button>
                <button type="button" class="profile-btn unfollow-btn" onclick="document.getElementById('profileShareModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>


</body>
</html>