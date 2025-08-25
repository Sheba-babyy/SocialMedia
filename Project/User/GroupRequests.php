<?php
include '../Assets/Connection/Connection.php';
session_start();
include("Header.php");

if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}

$uid = mysqli_real_escape_string($con, $_SESSION['uid']);

// Handle accept action
if (isset($_GET['aid'])) {
    $aid = mysqli_real_escape_string($con, $_GET['aid']);
    $upQry = "UPDATE tbl_groupmembers gm 
              INNER JOIN tbl_group g ON gm.group_id = g.group_id 
              SET gm.groupmembers_status = 1 
              WHERE gm.groupmembers_id = '$aid' AND g.user_id = '$uid'";
    if ($con->query($upQry)) {
        ?>
        <script>
            alert("Request Accepted");
            window.location = "GroupRequests.php";
        </script>
        <?php
    } else {
        ?>
        <script>
            alert("Error accepting request");
            window.location = "GroupRequests.php";
        </script>
        <?php
    }
}

// Handle decline action
if (isset($_GET['did'])) {
    $did = mysqli_real_escape_string($con, $_GET['did']);
    $delQry = "DELETE gm FROM tbl_groupmembers gm 
               INNER JOIN tbl_group g ON gm.group_id = g.group_id 
               WHERE gm.groupmembers_id = '$did' AND g.user_id = '$uid'";
    if ($con->query($delQry)) {
        ?>
        <script>
            alert("Request Declined");
            window.location = "GroupRequests.php";
        </script>
        <?php
    } else {
        ?>
        <script>
            alert("Error declining request");
            window.location = "GroupRequests.php";
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
    <title>Group Requests</title>
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
            margin:80px auto;
        }

        .heading {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 30px;
            color: var(--text-light);
            text-align: center;
        }

        .glass-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .request-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .request-item:hover {
            background-color: var(--hover-bg);
            transform: translateY(-2px);
        }

        .request-item:last-child {
            border-bottom: none;
        }

        .user-photo {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 20px;
            border: 2px solid var(--accent-red);
            flex-shrink: 0;
        }

        .user-info {
            flex: 1;
        }

        .user-name {
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 8px;
            color: var(--text-light);
        }

        .request-time {
            font-size: 0.9rem;
            color: var(--text-subtle);
        }

        .action-buttons {
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-accept {
            background-color: var(--accent-green);
            color: var(--text-light);
        }

        .btn-accept:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 200, 83, 0.4);
        }

        .btn-decline {
            background-color: var(--accent-red);
            color: var(--text-light);
        }

        .btn-decline:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(229, 57, 53, 0.4);
        }

        .empty-state {
            padding: 60px 30px;
            text-align: center;
            color: var(--text-subtle);
            font-size: 1.1rem;
        }

        .empty-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            color: var(--accent-red);
        }

        .error-message {
            padding: 60px 30px;
            text-align: center;
            color: var(--accent-red);
            font-size: 1.1rem;
        }

        .error-icon {
            font-size: 3rem;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .request-item {
                flex-direction: column;
                text-align: center;
                padding: 20px;
            }
            
            .user-photo {
                margin-right: 0;
                margin-bottom: 15px;
            }
            
            .action-buttons {
                margin-top: 15px;
                justify-content: center;
            }
            
            .heading {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 480px) {
            .action-buttons {
                flex-direction: column;
                width: 100%;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .user-photo {
                width: 60px;
                height: 60px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="heading">Group Requests</div>
        
        <div class="glass-card">
            <?php
            $selQry = "SELECT gm.*, u.user_name, u.user_photo,u.user_id
                       FROM tbl_groupmembers gm 
                       INNER JOIN tbl_user u ON gm.user_id = u.user_id 
                       INNER JOIN tbl_group g ON gm.group_id = g.group_id 
                       WHERE gm.groupmembers_status = 0 AND g.user_id = '$uid'";
            $res = $con->query($selQry);
            
            if ($res) {
                if ($res->num_rows > 0) {
                    while ($data = $res->fetch_assoc()) {
                        $photoSrc = !empty($data['user_photo']) ? 
                                   "../Assets/Files/UserDocs/".$data['user_photo'] : 
                                   "../Assets/Files/UserDocs/default.avif";
                        ?>
                        
                        <div class="request-item">
                            <a href="ViewProfile.php?pid=<?php echo $data['user_id']?>">
                            <img src="<?php echo $photoSrc ?>" class="user-photo" alt="Profile Photo" onerror="this.src='../Assets/Files/UserDocs/default.avif'">  </a>
                            
                            <div class="user-info">
                                <div class="user-name"><?php echo htmlspecialchars($data['user_name']); ?></div>
                                <div class="request-time">Request to join your group</div>
                            </div>
                            
                            <div class="action-buttons">
                                <a href="GroupRequests.php?aid=<?php echo $data['groupmembers_id']; ?>" class="btn btn-accept">
                                    Accept
                                </a>
                                <a href="GroupRequests.php?did=<?php echo $data['groupmembers_id']; ?>" class="btn btn-decline">
                                     Decline
                                </a>
                            </div>
                        </div> </a>
                        <?php
                    }
                } else {
                    echo '<div class="empty-state">
                            <i class="fas fa-user-slash empty-icon"></i>
                            <div>No pending requests</div>
                          </div>';
                }
            } else {
                echo '<div class="error-message">
                        <i class="fas fa-exclamation-triangle error-icon"></i>
                        <div>Error fetching requests</div>
                      </div>';
            }
            ?>
        </div>
    </div>
</body>
</html>