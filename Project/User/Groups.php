<?php
include '../Assets/Connection/Connection.php';
session_start();

if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}

$uid = mysqli_real_escape_string($con, $_SESSION['uid']);

// Handle join action
if (isset($_GET['gid'])) {
    $gid = mysqli_real_escape_string($con, $_GET['gid']);
    $checkQry = "SELECT * FROM tbl_groupmembers WHERE user_id = '$uid' AND group_id = '$gid'";
    $checkRes = $con->query($checkQry);
    if ($checkRes && $checkRes->num_rows > 0) {
        ?>
        <script>
            alert("You are already a member or have a pending request.");
            window.location = "Groups.php";
        </script>
        <?php
    } else {
        $insQry = "INSERT INTO tbl_groupmembers(user_id, group_id) VALUES('$uid', '$gid')";
        if ($con->query($insQry)) {
            ?>
            <script>
                alert("Request sent successfully.");
                window.location = "Groups.php";
            </script>
            <?php
        } else {
            ?>
            <script>
                alert("Failed to join group.");
                window.location = "Groups.php";
            </script>
            <?php
        }
    }
}

// Handle leave group action
if (isset($_GET['lid'])) {
    $lid = mysqli_real_escape_string($con, $_GET['lid']);
    $delQry = "DELETE FROM tbl_groupmembers WHERE user_id = '$uid' AND group_id = '$lid' AND groupmembers_status = 1";
    if ($con->query($delQry)) {
        ?>
            <script>
                alert("Left group successfully");
                window.location = "Groups.php";
            </script>
            <?php
        } else {
            ?>
            <script>
                alert("Error leaving group");
                window.location = "Groups.php";
            </script>
            <?php
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Groups | Social Network</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #1877f2;
            --primary-hover: #166fe5;
            --success: #42b72a;
            --success-hover: #36a420;
            --danger: #f02849;
            --danger-hover: #d61f3a;
            --warning: #ffba00;
            --dark: #18191a;
            --dark-card: #242526;
            --dark-border: #3e4042;
            --dark-hover: #3a3b3c;
            --text-primary: #e4e6eb;
            --text-secondary: #b0b3b8;
            --card-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            --border-radius: 10px;
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: var(--dark);
            color: var(--text-primary);
            line-height: 1.6;
            padding: 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }
        
        .main-content {
            background-color: var(--dark-card);
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            padding: 25px;
            margin-top: 20px;
        }
        
        .groups {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--dark-border);
        }
        
        .groups h1 {
            font-size: 28px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text-primary);
        }
        
        .groups i {
            color: var(--primary);
            font-size: 1.2em;
        }
        
        .create-group-btn {
            background-color: var(--success);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            padding: 10px 20px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        .create-group-btn:hover {
            background-color: var(--success-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }
        
        .groups-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .group-card {
            background-color: var(--dark-card);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            border: 1px solid var(--dark-border);
            position: relative;
        }
        
        .group-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        /* Option 1: Photo as full cover */
        .group-cover-container {
            height: 160px;
            position: relative;
            overflow: hidden;
        }
        
        .group-cover-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }
        
        /* Option 2: Circular avatar positioned on cover */
        .group-avatar-container {
            position: absolute;
            bottom: -40px;
            left: 20px;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid var(--dark-card);
            background-color: var(--dark-hover);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            z-index: 2;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        .group-avatar {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        /* Content area */
        .group-info {
            padding: 30px 20px 20px;
        }
        
        /* If using Option 1 (full cover photo), adjust padding */
        .group-card.cover-style .group-info {
            padding-top: 20px;
        }
        
        /* If using Option 2 (avatar on cover), adjust padding */
        .group-card.avatar-style .group-info {
            padding-top: 70px;
        }
        
        .group-name {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .group-members {
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .group-members i {
            font-size: 12px;
        }
        
        .group-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .group-btn {
            padding: 10px;
            border-radius: var(--border-radius);
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 14px;
        }
        
        .join-btn {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        .join-btn:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }
        
        .leave-btn {
            background-color: var(--danger);
            color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        .leave-btn:hover {
            background-color: var(--danger-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }
        
        .view-btn {
            background-color: transparent;
            color: var(--primary);
            border: 1px solid var(--dark-border);
        }
        
        .view-btn:hover {
            background-color: var(--dark-hover);
            border-color: var(--primary);
        }
        
        .pending-status {
            color: var(--warning);
            font-size: 14px;
            text-align: center;
            padding: 10px;
            font-style: italic;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background-color: rgba(255, 186, 0, 0.1);
            border-radius: var(--border-radius);
        }
        
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 50px;
            color: var(--text-secondary);
        }
        
        .empty-icon {
            font-size: 60px;
            margin-bottom: 20px;
            color: var(--dark-border);
            opacity: 0.7;
        }
        
        .empty-state h3 {
            font-size: 22px;
            margin-bottom: 10px;
            color: var(--text-primary);
        }
        
        .empty-state p {
            font-size: 15px;
            max-width: 400px;
            margin: 0 auto;
        }
        
        @media (max-width: 768px) {
            .groups-grid {
                grid-template-columns: 1fr;
            }
            
            .groups {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .main-content {
                padding: 20px 15px;
            }
            
            .group-card {
                max-width: 100%;
            }
            
            .group-avatar-container {
                width: 80px;
                height: 80px;
                bottom: -30px;
            }
        }
        
        /* Animation for new group cards */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .group-card {
            animation: fadeIn 0.5s ease-out forwards;
        }
        
        /* Add delay for each card */
        .group-card:nth-child(1) { animation-delay: 0.1s; }
        .group-card:nth-child(2) { animation-delay: 0.2s; }
        .group-card:nth-child(3) { animation-delay: 0.3s; }
        .group-card:nth-child(4) { animation-delay: 0.4s; }
        .group-card:nth-child(5) { animation-delay: 0.5s; }
    </style>
</head>
<body>
    <?php include("Header.php") ?>
    <div class="container">
        <main class="main-content">
            <div class="groups">
                <h1><i class="fas fa-users"></i> Groups</h1>
                <button class="create-group-btn" onclick="window.location.href='GroupCreate.php'">
                Create Group
                </button>
            </div>
            
            <div class="groups-grid">
                <?php
                $selQry = "SELECT * FROM tbl_group";
                $res = $con->query($selQry);
                if ($res) {
                    if ($res->num_rows > 0) {
                        while ($data = $res->fetch_assoc()) {
                            $group_id = mysqli_real_escape_string($con, $data['group_id']);
                            $isOwner = ($data['user_id'] == $uid);
                            
                            // Get member count for this group
                            $memberCountQry = "SELECT COUNT(*) as count FROM tbl_groupmembers WHERE group_id = '$group_id' AND groupmembers_status = 1";
                            $memberCountRes = $con->query($memberCountQry);
                            $memberCount = $memberCountRes ? $memberCountRes->fetch_assoc()['count']+1 : 1;
                            ?>
                            
                            <div class="group-card cover-style">
                                <div class="group-cover-container">
                                    <img src="../Assets/Files/GroupDocs/<?php echo htmlspecialchars($data['group_photo']?:'default.png')?>" 
                                         class="group-cover-photo" 
                                         alt="<?php echo htmlspecialchars($data['group_name']); ?>"
                                         onerror="this.src='../Assets/Files/default.png'">
                                </div>
                                <div class="group-info">
                                    <h3 class="group-name"><?php echo htmlspecialchars($data['group_name']); ?></h3>
                                    <div class="group-members">
                                        <i class="fas fa-users"></i>
                                        <?php echo number_format($memberCount) . ' member' . ($memberCount != 1 ? 's' : ''); ?>
                                    </div>
                                    
                                    <div class="group-actions">
                                        <?php
                                        if (!$isOwner) {
                                            $checkQry = "SELECT * FROM tbl_groupmembers WHERE user_id = '$uid' AND group_id = '$group_id'";
                                            $checkRes = $con->query($checkQry);
                                            if ($checkRes && $checkRes->num_rows == 0) {
                                                echo "<button onclick=\"window.location.href='Groups.php?gid=$group_id'\" class='group-btn join-btn'>
                                                    <i class='fas fa-user-plus'></i> Join Group
                                                </button>";
                                            } elseif ($checkRes && $checkRes->num_rows > 0) {
                                                $memberData = $checkRes->fetch_assoc();
                                                if ($memberData['groupmembers_status'] == 1) {
                                                    echo "<button onclick=\"window.location.href='Groups.php?lid=$group_id'\" class='group-btn leave-btn'>
                                                        <i class='fas fa-user'></i> Leave Group
                                                    </button>";
                                                } else {
                                                    echo "<div class='pending-status'>
                                                        <i class='fas fa-clock'></i> Request Pending
                                                    </div>";
                                                }
                                            }
                                        }
                                        $memQry = "SELECT * FROM tbl_groupmembers WHERE group_id = '$group_id' AND user_id = '$uid' AND groupmembers_status = 1";
                                        $memRes = $con->query($memQry);
                                        if (($memRes && $memRes->num_rows > 0) || $isOwner) {
                                            echo "<button onclick=\"window.location.href='GroupChat.php?id=$group_id'\" class='group-btn view-btn'>
                                                <i class='fas fa-comments'></i> View Group
                                            </button>";
                                        }
                                        ?>
                                    </div>
                                </div>
                                </div>                           
                            <?php
                        }
                    } else {
                        echo '<div class="empty-state">
                            <i class="fas fa-users-slash empty-icon"></i>
                            <h3>No Groups Found</h3>
                            <p>There are no groups available at the moment. Be the first to create one!</p>
                        </div>';
                    }
                } else {
                    echo '<div class="empty-state">
                        <i class="fas fa-exclamation-triangle empty-icon"></i>
                        <h3>Error Loading Groups</h3>
                        <p>There was an issue fetching groups. Please try again later.</p>
                    </div>';
                }
                ?>
            </div>
        </main>
    </div>
    <?php include("Footer.php") ?>
    <script>
        // Add smooth transitions for button clicks
        document.querySelectorAll('.group-btn').forEach(button => {
            button.addEventListener('click', function() {
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });
        });
        
        // Add hover effect for group cards
        document.querySelectorAll('.group-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                const cover = this.querySelector('.group-cover-photo');
                if (cover) {
                    cover.style.transform = 'scale(1.05)';
                }
            });
            card.addEventListener('mouseleave', function() {
                const cover = this.querySelector('.group-cover-photo');
                if (cover) {
                    cover.style.transform = '';
                }
            });
        });
    </script>
</body>
</html>