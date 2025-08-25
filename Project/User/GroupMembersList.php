<?php
include '../Assets/Connection/Connection.php';
session_start();
include("Header.php");

if (!isset($_SESSION['uid']) || !isset($_GET['gmlid'])) {
    header("Location: login.php");
    exit();
}

$uid = mysqli_real_escape_string($con, $_SESSION['uid']);
$group_id = mysqli_real_escape_string($con, $_GET['gmlid']);

// Check if user is group owner
$ownerQry = "SELECT * FROM tbl_group WHERE group_id = '$group_id' AND user_id = '$uid'";
$isOwner = $con->query($ownerQry)->num_rows > 0;

//check if user is admin
$isAdmin = false;
$adminQry = "SELECT * FROM tbl_admin WHERE admin_id = '$uid'";
if ($adminRes = $con->query($adminQry)) {
    $isAdmin = $adminRes->num_rows > 0;
}

// Handle member remove action
if (isset($_GET['rid']) && ($isOwner || $isAdmin)) {
    $rid = mysqli_real_escape_string($con, $_GET['rid']);
    $delQry = "DELETE FROM tbl_groupmembers WHERE groupmembers_id = '$rid' AND user_id != '$uid'";
    if ($con->query($delQry)) {
        ?>
        <script>
            alert("Member removed");
            window.location = "GroupMembersList.php?gmlid=<?php echo $group_id;?>";
        </script>
        <?php
    } else {
        ?>
        <script>
            alert("Error removing member");
            window.location = "GroupMembersList.php?gmlid=<?php echo $group_id; ?>";
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
    <title>Group Members List</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-dark: #0d1b1e;
            --primary-green: #005c4b;
            --accent-green: #008069;
            --light-text: #ffffff;
            --dark-text: #111111;
            --light-bg: #f8f9fa;
            --border-color: #2d3e40;
            --danger-red: #e74c3c;
        }
        
        body {
            font-family: 'Segoe UI', Helvetica, Arial, sans-serif;
            margin: 120px;
            padding: 0;
            background-color: var(--primary-dark);
            color: var(--light-text);
        }
        
        .member-list {
            max-width: 600px;
            margin: 20px auto;
            background: var(--primary-dark);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border: 1px solid var(--border-color);
        }
        
        .member-item {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .member-item:hover {
            background-color: rgba(0, 92, 75, 0.1);
        }
        
        .member-item:last-child {
            border-bottom: none;
        }
        
        .member-photo {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background-color: var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            overflow: hidden;
            flex-shrink: 0;
        }
        
        .member-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .member-name {
            flex: 1;
            font-size: 15px;
            font-weight: 500;
            color: var(--light-text);
        }
        
        .action-menu-container {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(13, 27, 30, 0.9);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .action-menu {
            background-color: var(--primary-dark);
            border-radius: 12px;
            width: 300px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0,0,0,0.3);
            animation: fadeIn 0.25s ease-out;
            border: 1px solid var(--border-color);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .menu-item {
            padding: 14px 20px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.2s;
        }
        
        .menu-item:hover {
            background-color: rgba(0, 92, 75, 0.2);
        }
        
        .menu-item:last-child {
            border-bottom: none;
        }
        
        .menu-item i {
            margin-right: 15px;
            color: var(--accent-green);
            font-size: 18px;
            width: 20px;
            text-align: center;
        }
        
        .menu-item.red i {
            color: var(--danger-red);
        }
        
        .menu-item.red {
            color: var(--danger-red);
        }
        
        .menu-title {
            padding: 16px;
            text-align: center;
            font-weight: 600;
            font-size: 16px;
            color: var(--light-text);
            border-bottom: 1px solid var(--border-color);
            background-color: var(--primary-green);
        }
        
        .no-members {
            padding: 20px;
            text-align: center;
            color: #8696a0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="member-list">
        <?php
        $selQry = "SELECT u.user_id, u.user_name, u.user_photo,'member' as role 
                   FROM tbl_groupmembers gm 
                   INNER JOIN tbl_user u ON gm.user_id = u.user_id 
                   WHERE gm.group_id = '$group_id' AND gm.groupmembers_status = '1' 
                   union 
                   select u.user_id, u.user_name, u.user_photo,'owner' as role from tbl_group g INNER JOIN tbl_user u ON g.user_id = u.user_id
                   WHERE g.group_id = '$group_id'";
        $res = $con->query($selQry);
        
        if ($res && $res->num_rows > 0) {
            while ($data = $res->fetch_assoc()) {
                $memberId = isset($data['groupmembers_id']) ? $data['groupmembers_id'] : 0;
                ?>
                <div class="member-item" 
                onclick="if('<?php echo $data['user_id']; ?>' !== '<?php echo $uid; ?>') 
                  showActionMenu('<?php echo $data['user_id']; ?>', '<?php echo htmlspecialchars($data['user_name']); ?>', '<?php echo $memberId ?>', <?php echo ($isOwner || $isAdmin) ? 'true' : 'false'; ?>)">

                    <div class="member-photo">
                        <?php if (!empty($data['user_photo'])) { ?>
                            <img src="../Assets/Files/UserDocs/<?php echo ($data['user_photo']?:'default.avif') ?>" onerror="this.style.display='none'; this.parentNode.innerHTML='<i class=\'fas fa-user\'></i>'">
                        <?php } else { ?>
                            <i class="fas fa-user" style="color: #a0a0a0;"></i>
                        <?php } ?>
                    </div>
                    <div class="member-name"><?php echo htmlspecialchars($data['user_name']); ?>
                    <?php if ($data['role'] === 'owner') echo "<span style='color: #0f0; font-size: 12px;'> (Owner)</span>"; ?>
                </div>
                </div>
                <?php
            }
        } else {
            echo "<div class='no-members'>No members found in this group</div>";
        }
        ?>
    </div>
    
    <div class="action-menu-container" id="actionMenuContainer">
        <div class="action-menu">
            <div class="menu-title" id="menuTitle">Member Options</div>
            <div class="menu-item" onclick="messageMember()">
                <i class="fas fa-comment-dots"></i>
                <span id="messageText">Message</span>
            </div>
            <div class="menu-item" onclick="viewProfile()">
                <i class="fas fa-user-circle"></i>
                <span id="viewText">View</span>
            </div>
            <div class="menu-item red" onclick="removeMember()" id="removeMenuItem" style="display: none;">
                <i class="fas fa-user"></i>
                <span id="removeText">Remove</span>
            </div>
        </div>
    </div>

    <script>
        let currentUserId = '';
        let currentUserName = '';
        let currentMemberId = '';
        
        function showActionMenu(userId, userName, memberId, isAdminOrOwner) {
            currentUserId = userId;
            currentUserName = userName;
            currentMemberId = memberId;
            
            // Update menu items with user's name
            document.getElementById('menuTitle').textContent = userName;
            document.getElementById('messageText').textContent = `Message ${userName}`;
            document.getElementById('viewText').textContent = `View ${userName}`;
            document.getElementById('removeText').textContent = `Remove ${userName}`;
            
            // Show/hide remove option based on admin status
            document.getElementById('removeMenuItem').style.display = isAdminOrOwner ? 'flex' : 'none';
            
            document.getElementById('actionMenuContainer').style.display = 'flex';
        }
        
        function hideActionMenu() {
            document.getElementById('actionMenuContainer').style.display = 'none';
        }
        
        function messageMember() {
            window.location.href = `Chat.php?id=${currentUserId}`;
        }
        
        function viewProfile() {
            window.location.href = `ViewProfile.php?pid=${currentUserId}`;
        }
        
        function removeMember() {
            if (confirm(`Are you sure you want to remove ${currentUserName} from this group?`)) {
                window.location.href = `GroupMembersList.php?gmlid=<?php echo $group_id; ?>&rid=${currentMemberId}`;
            }
        }
        
        // Close menu when clicking outside
        document.getElementById('actionMenuContainer').addEventListener('click', function(e) {
            if (e.target === this) {
                hideActionMenu();
            }
        });
    </script>
</body>
</html>