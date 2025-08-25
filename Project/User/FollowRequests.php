<?php
include '../Assets/Connection/Connection.php';
session_start();

// Ensure user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}

$uid = mysqli_real_escape_string($con, $_SESSION['uid']);

// Handle accept action
if (isset($_GET['aid'])) {
    $aid = mysqli_real_escape_string($con, $_GET['aid']);
    $upQry = "UPDATE tbl_friends SET friends_status = 1 WHERE friends_id = '$aid' AND user_to_id = '$uid'";
    if ($con->query($upQry)) {
        ?>
        <script>
            alert("Followed");
            window.location = "FollowRequests.php";
        </script>
        <?php
    } else {
        ?>
        <script>
            alert("Error accepting request");
            window.location = "FollowRequests.php";
        </script>
        <?php
    }
}

// Handle decline action
if (isset($_GET['did'])) {
    $did = mysqli_real_escape_string($con, $_GET['did']);
    $delQry = "DELETE FROM tbl_friends WHERE friends_id = '$did' AND user_to_id = '$uid'";
    if ($con->query($delQry)) {
        ?>
        <script>
            alert("Declined");
            window.location = "FollowRequests.php";
        </script>
        <?php
    } else {
        ?>
        <script>
            alert("Error declining request");
            window.location = "FollowRequests.php";
        </script>
        <?php
    }
}

// // Check for duplicate requests in the database
// $checkDuplicatesQuery = "SELECT f1.friends_id 
//                          FROM tbl_friends f1 
//                          INNER JOIN tbl_friends f2 
//                          ON f1.user_from_id = f2.user_from_id 
//                          AND f1.user_to_id = f2.user_to_id 
//                          WHERE f1.friends_id != f2.friends_id 
//                          AND f1.user_to_id = '$uid' 
//                          AND f1.friends_status = 0";

// $duplicateResult = $con->query($checkDuplicatesQuery);

// if ($duplicateResult && $duplicateResult->num_rows > 0) {
//     // Clean up duplicate requests
//     while ($duplicate = $duplicateResult->fetch_assoc()) {
//         $cleanupQuery = "DELETE FROM tbl_friends WHERE friends_id = '{$duplicate['friends_id']}'";
//         $con->query($cleanupQuery);
//     }
// }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Follow Requests - Nexo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
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
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
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
            margin-bottom: 30px;
        }

        .request-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .request-item {
            display: flex;
            align-items: center;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .request-item:hover {
            background-color: var(--hover-bg);
            transform: translateY(-2px);
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--accent-red);
            margin-right: 20px;
            margin-left: 10px;
            flex-shrink: 0;
        }

        .user-info {
            flex-grow: 1;
        }

        .user-name {
            font-weight: 600;
            font-size: 18px;
            margin-bottom: 5px;
            color: var(--text-light);
        }

        .request-date {
            font-size: 14px;
            color: var(--text-subtle);
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-accept {
            background-color: var(--accent-green);
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

        .btn-accept:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 200, 83, 0.4);
        }

        .btn-decline {
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

        .btn-decline:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(229, 57, 53, 0.4);
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
            .request-item {
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
    <?php include("Header.php") ?>
    <div class="container">

        <div class="heading">
            <h1><i class="fas fa-user-friends me-2"></i>Follow Requests</h1>
            <p>Manage your incoming follow requests</p>
        </div>
        
        <div class="glass-card">
            <?php
            $selQry = "SELECT f.*, u.user_name, u.user_photo 
                       FROM tbl_friends f 
                       INNER JOIN tbl_user u ON f.user_from_id = u.user_id 
                       WHERE f.user_to_id = '$uid' AND f.friends_status = 0";
            $res = $con->query($selQry);
            
            if ($res && $res->num_rows > 0) {
                echo '<div class="request-list">';
                
                while ($data = $res->fetch_assoc()) {
                    ?>
                    <div class="request-item">
                        <img src="../Assets/Files/UserDocs/<?php echo htmlspecialchars($data['user_photo']); ?>" 
                             class="user-avatar" 
                             alt="<?php echo htmlspecialchars($data['user_name']); ?>">
                        <div class="user-info">
                            <div class="user-name"><?php echo htmlspecialchars($data['user_name']); ?></div>
                        </div>
                        <div class="action-buttons">
                            <a href="FollowRequests.php?aid=<?php echo $data['friends_id']; ?>" class="btn-accept">
                                Accept
                            </a>
                            <a href="FollowRequests.php?did=<?php echo $data['friends_id']; ?>" class="btn-decline">
                                 Decline
                            </a>
                        </div>
                    </div>
                    <?php
                }
                
                echo '</div>';
            } else {
                ?>
                <div class="empty-state">
                    <i class="fas fa-user-slash empty-icon"></i>
                    <h3>No Pending Requests</h3>
                    <p>You don't have any follow requests at this time.</p>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</body>
</html>