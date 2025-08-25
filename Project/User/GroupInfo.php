<?php
include '../Assets/Connection/Connection.php';
session_start();
include("Header.php");

if (!isset($_SESSION['uid']) || !isset($_GET['id'])) {
    header("Location: login.php");
    exit();
}

$uid = mysqli_real_escape_string($con, $_SESSION['uid']);
$group_id = mysqli_real_escape_string($con, $_GET['id']);

// Fetch group details and check membership
$selGroup = "SELECT * FROM tbl_group WHERE group_id = '$group_id'";
$groupResult = $con->query($selGroup);
if (!$groupResult || $groupResult->num_rows == 0) {
    echo "<script>alert('Group does not exist'); window.location='Groups.php';</script>";
    exit;
}
$groupData = $groupResult->fetch_assoc();

// Check if user is group owner
$isOwner = ($groupData['user_id'] == $uid);

// Check if user is an admin (Assuming an admin table exists)
$isAdmin = false;
$adminQry = "SELECT * FROM tbl_admin WHERE admin_id = '$uid'";
if ($adminRes = $con->query($adminQry)) {
    $isAdmin = $adminRes->num_rows > 0;
}

// Check if user is a member (status = 1)
$memberCheck = "SELECT * FROM tbl_groupmembers WHERE user_id = '$uid' AND group_id = '$group_id' AND groupmembers_status = 1";
$memberResult = $con->query($memberCheck);
if ($memberResult->num_rows == 0 && !$isOwner) {
    echo "<script>alert('You are not a member of this group'); window.location='Groups.php';</script>";
    exit;
}

// Handle LEAVE GROUP action
if (isset($_GET['action']) && $_GET['action'] == 'leave' && !$isOwner) {
    $delQry = "DELETE FROM tbl_groupmembers WHERE user_id = '$uid' AND group_id = '$group_id'";
    if ($con->query($delQry)) {
        echo "<script>alert('You have left the group.'); window.location='Groups.php';</script>";
    } else {
        echo "<script>alert('Error leaving the group.'); window.location='GroupInfo.php?id=$group_id';</script>";
    }
    exit;
}

// Handle EDIT GROUP form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_submit']) && $isOwner) {
    $group_name = mysqli_real_escape_string($con, $_POST['group_name']);
    $group_description = mysqli_real_escape_string($con, $_POST['group_description']);

    $updateQry = "UPDATE tbl_group SET group_name = '$group_name', group_description = '$group_description' WHERE group_id = '$group_id'";

    if (isset($_FILES['group_photo']) && $_FILES['group_photo']['error'] == 0) {
        $photo = $_FILES['group_photo'];
        $photo_name = uniqid() . '_' . $photo['name'];
        $photo_path = '../Assets/Files/GroupDocs/' . $photo_name;

        if (move_uploaded_file($photo['tmp_name'], $photo_path)) {
            $updateQry = "UPDATE tbl_group SET group_name = '$group_name', group_description = '$group_description', group_photo = '$photo_name' WHERE group_id = '$group_id'";
        }
    }

    if ($con->query($updateQry)) {
        echo "<script>alert('Group updated successfully!'); window.location='GroupInfo.php?id=$group_id';</script>";
    } else {
        echo "<script>alert('Failed to update group.'); window.location='GroupInfo.php?id=$group_id';</script>";
    }
    exit;
}

// Handle REPORT GROUP form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['report_submit'])) {
    $reason = mysqli_real_escape_string($con, $_POST['reason']);
    $details = mysqli_real_escape_string($con, $_POST['details']);

    // Check if user has already reported this group (assuming tbl_group_reports exists)
    $checkQry = "SELECT * FROM tbl_group_reports WHERE group_id = '$group_id' AND user_id = '$uid'";
    $checkResult = $con->query($checkQry);

    if ($checkResult->num_rows > 0) {
        echo "<script>alert('You have already reported this group.'); window.location='GroupInfo.php?id=$group_id';</script>";
    } else {
        $insertQry = "INSERT INTO tbl_group_reports (group_id, user_id, reason, details) VALUES ('$group_id', '$uid', '$reason', '$details')";
        if ($con->query($insertQry)) {
            echo "<script>alert('Group reported successfully!'); window.location='GroupInfo.php?id=$group_id';</script>";
        } else {
            echo "<script>alert('Failed to report group.'); window.location='GroupInfo.php?id=$group_id';</script>";
        }
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($groupData['group_name']); ?> Info</title>
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
            --accent-yellow: #ffba00;
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
            max-width: 600px;
            margin: 80px auto;
        }

        .glass-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            text-align: center;
        }

        .group-photo-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 25px;
        }

        .group-photo {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 5px 15px rgba(229, 57, 53, 0.3);
        }

        .photo-edit-overlay {
            position: absolute;
            bottom: 0;
            right: 0;
            background-color: var(--accent-red);
            color: var(--text-light);
            border-radius: 50%;
            padding: 10px;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(229, 57, 53, 0.4);
            transition: all 0.3s ease;
        }

        .photo-edit-overlay:hover {
            transform: scale(1.1);
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 700;
            margin: 0 0 15px;
            color: var(--text-light);
        }

        .group-description {
            color: var(--text-subtle);
            font-size: 1rem;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .action-list {
            list-style: none;
            padding: 0;
            margin-top: 30px;
        }

        .action-item {
            padding: 18px 0;
            border-bottom: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .action-item:last-child {
            border-bottom: none;
        }

        .action-item:hover {
            background-color: var(--hover-bg);
            transform: translateX(5px);
        }

        .action-item a, .action-item button {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: var(--text-light);
            font-size: 1.1rem;
            font-weight: 500;
            background: none;
            border: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
            padding: 0 15px;
        }

        .action-item i {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
            margin-right: 15px;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }

        .action-item:hover i {
            transform: scale(1.1);
        }

        .action-item.edit i { color: var(--accent-blue); }
        .action-item.members i { color: var(--accent-green); }
        .action-item.requests i { color: var(--accent-yellow); }
        .action-item.leave i { color: var(--accent-red); }
        .action-item.report i { color: var(--accent-red); }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            justify-content: center;
            align-items: center;
            z-index: 1000;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            backdrop-filter: blur(15px);
            padding: 30px;
            border-radius: 20px;
            max-width: 450px;
            width: 90%;
            position: relative;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
        }

        .close-modal {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 28px;
            cursor: pointer;
            color: var(--text-subtle);
            transition: color 0.3s ease;
        }

        .close-modal:hover {
            color: var(--accent-red);
        }

        .modal-content h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            margin-bottom: 25px;
            color: var(--text-light);
            text-align: center;
        }

        .form-group {
            margin-bottom: 25px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--text-light);
        }

        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 14px;
            background-color: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            font-size: 1rem;
            color: var(--text-light);
            transition: border-color 0.3s ease;
        }
        #report_reason option{
            color:black;
        }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
            outline: none;
            border-color: var(--accent-red);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .photo-upload-label {
            display: block;
            width: 150px;
            height: 150px;
            margin: 0 auto 25px;
            position: relative;
            cursor: pointer;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .photo-upload-label:hover {
            border-color: var(--accent-red);
            transform: scale(1.05);
        }

        .round-photo-preview {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            border-radius: 50%;
            position: relative;
            z-index: 1;
        }

        .submit-btn {
            background-color: var(--accent-red);
            color: var(--text-light);
            border: none;
            padding: 15px 25px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
            font-size: 1.1rem;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(229, 57, 53, 0.4);
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .glass-card {
                padding: 25px;
            }
            
            h1 {
                font-size: 1.8rem;
            }
            
            .action-item a, .action-item button {
                font-size: 1rem;
            }
            
            .modal-content {
                padding: 25px;
            }
        }

        @media (max-width: 480px) {
            .group-photo-container {
                width: 120px;
                height: 120px;
            }
            
            .photo-upload-label {
                width: 120px;
                height: 120px;
            }
            
            h1 {
                font-size: 1.6rem;
            }
            
            .action-item i {
                width: 35px;
                height: 35px;
                font-size: 1.1rem;
                margin-right: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="glass-card">
            <div class="group-photo-container">
                <img src="../Assets/Files/GroupDocs/<?php echo htmlspecialchars($groupData['group_photo']); ?>" alt="Group Photo" class="group-photo" onerror="this.src='https://via.placeholder.com/150/0A0A0A/AFAFAF?text=Group'">
                <?php if ($isOwner) { ?>
                    <button class="photo-edit-overlay" onclick="openModal('editModal')">
                        <i class="fas fa-camera"></i>
                    </button>
                <?php } ?>
            </div>
            <h1><?php echo htmlspecialchars($groupData['group_name']); ?></h1>
            <p class="group-description"><?php echo htmlspecialchars($groupData['group_description'] ?: "No description available."); ?></p>

            <ul class="action-list">
                <?php if ($isOwner || $isAdmin) { ?>
                    <li class="action-item requests">
                        <a href="GroupRequests.php?grid=<?php echo $group_id; ?>">
                            <i class="fas fa-user-plus"></i>
                            <span>Group Requests</span>
                        </a>
                    </li>
                <?php } ?>
                <li class="action-item members">
                    <a href="GroupMembersList.php?gmlid=<?php echo $group_id; ?>">
                        <i class="fas fa-users"></i>
                        <span>Members List</span>
                        </a>
                </li>
                <?php if ($isOwner) { ?>
                    <li class="action-item edit">
                        <button onclick="openModal('editModal')">
                            <i class="fas fa-edit"></i>
                            <span>Edit Group</span>
                        </button>
                    </li>
                <?php } ?>
                <li class="action-item leave">
                    <a href="GroupInfo.php?id=<?php echo $group_id; ?>&action=leave" onclick="return confirm('Are you sure you want to leave this group?');">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Leave Group</span>
                    </a>
                </li>
                <li class="action-item report">
                    <button onclick="openModal('reportModal')">
                        <i class="fas fa-flag"></i>
                        <span>Report Group</span>
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('editModal')">&times;</span>
            <h2>Edit Group Details</h2>
            <form action="GroupInfo.php?id=<?php echo $group_id; ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                
                <div class="form-group">
                    <label for="group_photo_file" class="photo-upload-label">
                        <img id="photo-preview" src="../Assets/Files/GroupDocs/<?php echo htmlspecialchars($groupData['group_photo']); ?>" alt="Group Photo" class="round-photo-preview" onerror="this.src='https://via.placeholder.com/150/0A0A0A/AFAFAF?text=New+Photo'">
                    </label>
                    <input type="file" id="group_photo_file" name="group_photo" accept="image/*" style="display: none;">
                </div>
                <div class="form-group">
                    <label for="group_name">Group Name</label>
                    <input type="text" id="group_name" name="group_name" value="<?php echo htmlspecialchars($groupData['group_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="group_description">Description</label>
                    <textarea id="group_description" name="group_description"><?php echo htmlspecialchars($groupData['group_description']); ?></textarea>
                </div>
                
                <button type="submit" name="edit_submit" class="submit-btn">Save Changes</button>
            </form>
        </div>
    </div>
    
    <div id="reportModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('reportModal')">&times;</span>
            <h2>Report Group</h2>
            <form action="GroupInfo.php?id=<?php echo $group_id; ?>" method="POST">
                <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                <div class="form-group">
                    <label for="report_reason">Reason for reporting:</label>
                    <select name="reason" id="report_reason" required>
                        <option value="">Select a reason</option>
                        <option value="spam">Spam or misleading</option>
                        <option value="hate_speech">Hate speech or harassment</option>
                        <option value="violence">Violence or dangerous content</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="report_details">Additional details (optional):</label>
                    <textarea name="details" id="report_details"></textarea>
                </div>
                <button type="submit" name="report_submit" class="submit-btn">Submit Report</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        // Photo preview functionality
        document.getElementById('group_photo_file').addEventListener('change', function(e) {
            const preview = document.getElementById('photo-preview');
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        // Make photo upload label clickable
        document.querySelector('.photo-upload-label').addEventListener('click', function() {
            document.getElementById('group_photo_file').click();
        });
    </script>
</body>
</html>