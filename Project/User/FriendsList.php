<?php
include '../Assets/Connection/Connection.php';
session_start();
include("Header.php");


if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}

$uid = mysqli_real_escape_string($con, $_SESSION['uid']);

// If viewing another user's friends (pid from ViewProfile)
$profile_id = isset($_GET['user_id']) ? mysqli_real_escape_string($con, $_GET['user_id']) : $uid;
$is_own_profile = ($profile_id == $uid);

// Handle unfollow action
if (isset($_GET['ufid'])) {
    $ufid = mysqli_real_escape_string($con, $_GET['ufid']);
    
    // More secure query - ensures user can only unfollow their own relationships
    $delQry = "DELETE FROM tbl_friends WHERE friends_id = '$ufid' 
               AND ((user_from_id = '$uid' AND friends_status = 1) 
               OR (user_to_id = '$uid' AND friends_status = 1))";
    
    if ($con->query($delQry)) {
        if ($con->affected_rows > 0) {
            ?>
            <script>
                alert("Unfollowed successfully");
                window.location = "FriendsList.php";
            </script>
            <?php
        } else {
            ?>
            <script>
                alert("No friendship found to unfollow or you don't have permission");
                window.location = "FriendsList.php";
            </script>
            <?php
        }
    } else {
        ?>
        <script>
            alert("Error unfollowing: <?php echo addslashes($con->error); ?>");
            window.location = "FriendsList.php";
        </script>
        <?php
    }
    exit(); // Prevent further execution
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friends List - Nexo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@400;500;600;700&display=swap');
        
        :root {
            --dark-bg: #0A0A0A;
            --card-bg: rgba(255, 255, 255, 0.08);
            --accent-red: #E53935;
            --accent-green: #00c853;
            --accent-blue: #2196F3;
            --text-light: #F8F8F8;
            --text-subtle: #AFAFAF;
            --border-color: rgba(255, 255, 255, 0.15);
            --hover-bg: rgba(255, 255, 255, 0.05);
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-light);
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 900px;
            margin: 80px auto;
        }

        .heading {
            margin-bottom: 30px;
            text-align: center;
        }

        .heading h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: var(--text-light);
            margin-bottom: 10px;
        }

        .heading p {
            color: var(--text-subtle);
            font-size: 1rem;
        }

        .glass-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 12px;
            padding: 5px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .friends-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .friend-item {
            display: flex;
            align-items: center;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .friend-item:hover {
            background-color: var(--hover-bg);
            transform: translateY(-2px);
        }

        .user-avatar {
            margin-left:10px;
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--accent-red);
            margin-right: 20px;
            flex-shrink: 0;
            cursor: pointer;
        }

        .user-info {
            flex-grow: 1;
            cursor: pointer;
        }

        .user-name {
            font-weight: 600;
            font-size: 18px;
            margin-bottom: 5px;
            color: var(--text-light);
        }

        .friend-since {
            font-size: 14px;
            color: var(--text-subtle);
        }

        .action-buttons {
            display: flex;
            gap: 12px;
        }

        .btn-unfollow {
            background-color: var(--accent-red);
            color: var(--text-light);
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-unfollow:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(229, 57, 53, 0.4);
        }

        .btn-chat {
            background-color: var(--accent-blue);
            color: var(--text-light);
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-chat:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(33, 150, 243, 0.4);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-subtle);
        }

        .empty-icon {
            font-size: 60px;
            margin-bottom: 20px;
            color: var(--accent-red);
        }

        .empty-state h3 {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            margin-bottom: 10px;
            color: var(--text-light);
        }

        .empty-state p {
            font-size: 16px;
        }

        @media (max-width: 768px) {
            .friend-item {
                flex-direction: column;
                text-align: center;
                padding: 15px;
            }
            
            .user-avatar {
                margin-right: 0;
                margin-bottom: 15px;
            }
            
            .action-buttons {
                margin-top: 15px;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="heading">
            <h1><i class="fas fa-user-friends me-2"></i>
                <?php echo $is_own_profile ? 'My Friends List' : 'Friends List'; ?>
            </h1>
            <p>
                <?php echo $is_own_profile ? 'People you\'re connected with' : 'People connected with this user'; ?>
            </p>
        </div>
        
        <div class="glass-card">
            <?php
            $selQry = "
            SELECT f.friends_id, u.user_id, u.user_name, u.user_photo
            FROM tbl_friends f
            JOIN tbl_user u 
              ON ( (f.user_from_id = '$profile_id' AND f.user_to_id = u.user_id)
                OR (f.user_to_id = '$profile_id' AND f.user_from_id = u.user_id) )
            WHERE f.friends_status = 1
            ORDER BY u.user_name ASC";

            $res = $con->query($selQry);
            
            if ($res && $res->num_rows > 0) {
                echo '<div class="friends-list">';
                
                while ($data = $res->fetch_assoc()) {
                    ?>
                    <div class="friend-item">
                        <img src="../Assets/Files/UserDocs/<?php echo htmlspecialchars($data['user_photo']?:'default.avif') ?>" 
                             class="user-avatar" 
                             alt="<?php echo htmlspecialchars($data['user_name']); ?>"
                             onclick="window.location='ViewProfile.php?pid=<?= $data['user_id']; ?>'">
                        <div class="user-info" onclick="window.location='ViewProfile.php?pid=<?= $data['user_id']; ?>'">
                            <div class="user-name"><?php echo htmlspecialchars($data['user_name']); ?></div>
                        </div>
                        <?php if ($is_own_profile) { ?>
                            <div class="action-buttons">
                                <a href="FriendsList.php?ufid=<?php echo $data['friends_id']; ?>" class="btn-unfollow">
                                    <i class="fas fa-user-times"></i> Unfollow
                                </a>
                                <a href="Chat.php?id=<?php echo $data['user_id']; ?>" class="btn-chat">
                                    <i class="fas fa-comment-dots"></i> Chat
                                </a>
                            </div>
                        <?php } ?>
                    </div>
                    <?php
                }
                
                echo '</div>';
            } else {
                ?>
                <div class="empty-state">
                    <i class="fas fa-user-friends empty-icon"></i>
                    <h3>No Friends Yet</h3>
                    <p>
                        <?php echo $is_own_profile 
                            ? 'You haven\'t connected with anyone yet. Start exploring to find friends!' 
                            : 'This user hasn\'t connected with anyone yet.'; ?>
                    </p>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</body>
</html>