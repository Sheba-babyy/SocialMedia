<?php
include '../Assets/Connection/Connection.php';
session_start();
$loggedInUid = $_SESSION['uid'];

if(!isset($_SESSION["uid"])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION["uid"];
$profile_id = isset($_GET['id']) ? $_GET['id'] : $uid;

// Get profile data
$profileQry = "SELECT u.*, p.place_name, d.district_name 
              FROM tbl_user u
              JOIN tbl_place p ON u.place_id = p.place_id
              JOIN tbl_district d ON p.district_id = d.district_id
              WHERE u.user_id = $profile_id";
$profileData = $con->query($profileQry)->fetch_assoc();

// Check if viewing own profile
$is_own_profile = ($uid == $profile_id);

// Get counts
$postCountQry = "SELECT COUNT(*) as count FROM tbl_post WHERE user_id = $profile_id";
$postCount = $con->query($postCountQry)->fetch_assoc()['count'];

$friendCountQry = "SELECT COUNT(*) as count FROM tbl_friends 
                  WHERE (user_from_id = $profile_id OR user_to_id = $profile_id) 
                  AND friends_status = 1";
$friendCount = $con->query($friendCountQry)->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=htmlspecialchars($profileData['user_name'])?>'s Profile | Nexo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    :root {
        --black: #000000;
        --white: #FFFFFF;
        --deep-forest: #013220;
        --forest-green: #1a4b32;
        --light-forest: #2c6e49;
        --bg-dark: #121212;
        --card-dark: #1e1e1e;
        --text-dark: #e0e0e0;
        --border-dark: #333333;
        --accent: #10b582;
    }
    
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
        min-height: 100vh; 
    }
    
    .profile-container {
        display: flex;
        max-width: 1200px;
        margin: 80px auto 0;
        padding: 20px;
        gap: 30px;
    }
    
    /* Profile sidebar */
    .profile-sidebar {
        width: 300px;
        position: fixed;
        top: 100px;
        height: fit-content;
        overflow-y: auto; 
    }
    
    .profile-card {
        background-color: var(--card-dark);
        border-radius: 12px;
        border: 1px solid var(--border-dark);
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .profile-header {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        margin-bottom: 20px;
    }
    
    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid white;
        margin-bottom: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    .profile-name {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .profile-bio {
        color: var(--text-dark);
        opacity: 0.8;
        margin-bottom: 15px;
    }
    
    .profile-stats {
        display: flex;
        justify-content: space-around;
        margin-top: 20px;
    }
    
    .stat-item {
        text-align: center;
        cursor: pointer;
        transition: transform 0.2s;
    }
    
    .stat-item:hover {
        transform: translateY(-2px);
    }
    
    .stat-number {
        font-size: 1.2rem;
        font-weight: 600;
    }
    
    .stat-label {
        font-size: 0.85rem;
        opacity: 0.7;
    }
    
    /* Profile details */
    .profile-details {
        margin-top: 20px;
    }
    
    .detail-item {
        margin-bottom: 15px;
    }
    
    .detail-label {
        font-weight: 600;
        margin-bottom: 5px;
        color: #E53935;
    }
    
    .location-item {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 15px;
        color: var(--text-dark);
        opacity: 0.8;
    }
    
    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 20px;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        border: none;
        font-family: inherit;
        gap: 8px;
        font-size: 0.95rem;
    }
    
    .btn-primary {
        background-color: var(--forest-green);
        color: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .btn-primary:hover {
        background-color: var(--light-forest);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    
    .btn-outline {
        background: transparent;
        color: var(--text-dark);
        border: 1px solid var(--border-dark);
    }
    
    .btn-outline:hover {
        background-color: rgba(26, 75, 50, 0.2);
    }
    
    .btn-accent {
        background-color: var(--accent);
        color: white;
    }
    
    .btn-accent:hover {
        background-color: #0e9e74;
    }
    
    .btn-block {
        width: 100%;
    }
    
    /* Action buttons container */
    .profile-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 20px;
    }
    
    /* Posts section */
    .posts-section {
        flex-grow: 1;
        margin-left:350px;
    }
    
    .section-title {
        font-size: 1.3rem;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border-dark);
    }
    
    /* Create post card */
    .create-post-card {
        background-color: var(--card-dark);
        border-radius: 12px;
        border: 1px solid var(--border-dark);
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .create-post-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }
    
    .create-post-header {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .create-post-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        margin-right: 12px;
        object-fit: cover;
        border: 2px solid var(--forest-green);
    }
    
    .create-post-prompt {
        font-weight: 500;
        color: var(--text-dark);
        opacity: 0.9;
    }
    
    /* No posts */
    .no-posts {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-dark);
        opacity: 0.7;
        background-color: var(--card-dark);
        border-radius: 12px;
        border: 1px solid var(--border-dark);
    }
    
    .no-posts i {
        font-size: 48px;
        margin-bottom: 20px;
        color: var(--forest-green);
    }
    
    .create-post-link {
        color: var(--forest-green);
        text-decoration: none;
        font-weight: 600;
        margin-top: 15px;
        display: inline-block;
        padding: 10px 20px;
        border-radius: 6px;
        background-color: rgba(26, 75, 50, 0.1);
        transition: background-color 0.2s;
    }
    
    .create-post-link:hover {
        text-decoration: none;
        background-color: rgba(26, 75, 50, 0.2);
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .profile-container {
            flex-direction: column;
        }
        
        .profile-sidebar {
            width: 100%;
            position: static;
        }
        
        .btn {
            padding: 10px 15px;
            font-size: 0.9rem;
        }
    }
    </style>
</head>
<body>
    <?php include("Header.php") ?>
    <div class="profile-container">

        <!-- Left sidebar with profile info -->
        <div class="profile-sidebar">
            <div class="profile-card">
                <div class="profile-header">
                    <img src="../Assets/Files/UserDocs/<?=htmlspecialchars($profileData['user_photo'])?>" 
                         alt="Profile Photo" class="profile-avatar">
                    <h2 class="profile-name"><?=htmlspecialchars($profileData['user_name'])?></h2>
                    <p class="profile-bio"><?=htmlspecialchars($profileData['user_bio'] ?? 'No bio yet')?></p>
                </div>
                
                <div class="profile-stats">
                    <div class="stat-item" onclick="window.location='#posts'">
                        <div class="stat-number"><?=$postCount?></div>
                        <div class="stat-label">Posts</div>
                    </div>
                    <div class="stat-item" onclick="window.location='FriendsList.php?user_id=<?=$profile_id?>'">
                        <div class="stat-number"><?=$friendCount?></div>
                        <div class="stat-label">Friends</div>
                    </div>
                </div>
                
                <div class="profile-details">
                    <div class="detail-item">
                        <div class="detail-label">Date of Birth</div>
                        <div><?=date('F j, Y', strtotime($profileData['user_dob']))?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Location</div>
                        <div class="location-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?=htmlspecialchars($profileData['place_name'])?>, <?=htmlspecialchars($profileData['district_name'])?></span>
                        </div>
                    </div>
                </div>
                
                <div class="profile-actions">
                    <?php if($is_own_profile): ?>
                        <button class="btn btn-primary btn-block" onclick="window.location.href='EditProfile.php'">
                            <i class="fas fa-edit"></i> Edit Profile
                        </button>
                    <?php else: ?>
                        <button class="btn btn-primary btn-block" onclick="sendFriendRequest(<?=$profile_id?>)">
                            <i class="fas fa-user-plus"></i> Add Friend
                        </button>
                    <?php endif; ?>
                    
                    <!-- Share Profile Button - styled like Edit Profile -->
                    <button class="btn btn-outline btn-block" onclick="shareProfile()">
                        <i class="fas fa-share-alt"></i> Share Profile
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Right section with posts -->
        <div class="posts-section" id="posts">
            <h2 class="section-title">Posts</h2>
            
            <?php if($is_own_profile): ?>
                <!-- Enhanced Create Post Card -->
                <div class="create-post-card" onclick="window.location.href='Post.php'">
                    <div class="create-post-header">
                        <img src="../Assets/Files/UserDocs/<?=htmlspecialchars($profileData['user_photo'])?>" 
                             class="create-post-avatar" alt="Your profile">
                        <div class="create-post-prompt">What's on your mind?</div>
                    </div>
                    <button class="btn btn-accent btn-block">
                        <i class="fas fa-plus"></i> Create Post
                    </button>
                </div>
            <?php endif; ?>
            
            <?php 
            // Include ViewPost.php 
            $_GET['user_id'] = $profile_id; // Pass the user ID to ViewPost.php
            include 'ViewPost.php'; 
            ?>

        </div>
    </div>


    <script src="../Assets/JQ/JQuery.js"></script>
    <script>
    function sendFriendRequest(userId) {
        $.ajax({
            url: '../Assets/AjaxPages/AjaxFriendRequest.php',
            type: 'POST',
            data: { user_to_id: userId },
            success: function(response) {
                if(response === 'sent') {
                    alert('Friend request sent!');
                } else if(response === 'already_sent') {
                    alert('Friend request already sent');
                } else if(response === 'already_friends') {
                    alert('You are already friends');
                } else {
                    alert('Error sending friend request');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    }
    
    function shareProfile() {
        const profileLink = window.location.href;
        if(navigator.share) {
            navigator.share({
                title: '<?=htmlspecialchars($profileData['user_name'])?>\'s Profile',
                url: profileLink,
                text: 'Check out this profile on Nexo'
            }).catch(console.error);
        } else {
            navigator.clipboard.writeText(profileLink).then(() => {
                alert('Profile link copied to clipboard!');
            });
        }
    }
    </script>
</body>
</html>