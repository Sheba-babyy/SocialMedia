<?php
include "../Assets/Connection/Connection.php";
session_start();
include("Header.php");

if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}

$uid = mysqli_real_escape_string($con, $_SESSION['uid']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chats</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        margin: 100px auto;
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

    .card {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border-radius: 20px;
        padding: 0;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        overflow: hidden;
    }

    .chat-list {
        display: flex;
        flex-direction: column;
    }

    .chat-item {
        display: flex;
        align-items: center;
        padding: 10px;
        border-bottom: 1px solid var(--border-color);
        transition: background-color 0.3s ease;
        text-decoration: none;
        color: inherit;
    }

    .chat-item:last-child {
        border-bottom: none;
    }

    .chat-item:hover {
        background-color: var(--hover-bg);
    }

    .user-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--accent-red);
        margin-left: 20px;
        margin-right:15px;
        flex-shrink: 0;
    }

    .user-info {
        flex-grow: 1;
        min-width: 0;
    }

    .user-name {
        font-weight: 600;
        font-size: 18px;
        margin-bottom: 5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: var(--text-light);
    }

    .user-status {
        font-size: 14px;
        color: var(--text-subtle);
    }

    .btn-chat {
        background-color: var(--accent-green);
        color: var(--text-light);
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        flex-shrink: 0;
        margin-right: 20px;
        border: none;
        text-decoration: none;
    }

    .btn-chat:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 200, 83, 0.4);
        background-color: #00b34a;
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
        .container{
            margin-top:180px;
        }
        .heading h1 {
            font-size: 2rem;
        }
        
        .chat-item {
            padding: 15px;
            align-items:center;
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            margin-right: 15px;
        }
        
        .btn-chat {
            padding: 8px 15px;
            font-size: 13px;
            margin-left: 10px;
        }
        
        .empty-state {
            padding: 40px 15px;
        }
        
        .empty-icon {
            font-size: 50px;
        }
        
        .empty-state h3 {
            font-size: 20px;
        }
    }

    @media (max-width: 576px) {
        body {
            padding: 15px;
            align-items:center;
        }
        
        .heading h1 {
            font-size: 1.8rem;
        }
        
        .chat-item {
            flex-direction: row;
            align-items: center;
            padding: 10px 10px;
        }
        
        .user-avatar {
            margin-right: 0;
            margin-bottom: 10px;
        }
        
        .user-info {
            margin-bottom: 10px;
            text-align: center;
        }
        
        .btn-chat {
            margin-left: 0;
        }
    }
</style>
</head>
<body>
    <div class="container">
        <h1 class="heading"><i class="fas fa-comment-alt"></i> Your Conversations</h1>
        
        <div class="card">
            <div class="chat-list">
                <?php
                $selQry = "SELECT f.*, u.user_name, u.user_photo, u.user_id 
                           FROM tbl_friends f 
                           INNER JOIN tbl_user u ON (f.user_from_id = u.user_id OR f.user_to_id = u.user_id) 
                           WHERE (f.user_from_id = '$uid' OR f.user_to_id = '$uid') 
                           AND u.user_id != '$uid' AND f.friends_status = 1";
                $res = $con->query($selQry);
                
                if ($res && $res->num_rows > 0) {
                    while ($data = $res->fetch_assoc()) {
                        ?>
                        <a href="Chat.php?id=<?php echo $data['user_id']; ?>" class="chat-item">
                            <img src="../Assets/Files/UserDocs/<?php echo htmlspecialchars($data['user_photo']?:'default.avif') ?>" 
                                 class="user-avatar" 
                                 alt="<?php echo htmlspecialchars($data['user_name']); ?>">
                            <div class="user-info">
                                <div class="user-name"><?php echo htmlspecialchars($data['user_name']); ?></div>
                            </div>
                            <div class="btn-chat">
                                <i class="fas fa-paper-plane"></i> Message
                            </div>
                        </a>
                        <?php
                    }
                } else {
                    echo '<div class="empty-state">
                            <i class="fas fa-comment-slash empty-icon"></i>
                            <h3>No conversations yet</h3>
                            <p>Start chatting with your friends</p>
                          </div>';
                }
                ?>
            </div>
        </div>
    </div>
        <script>
        // Simple animation for page elements
        document.addEventListener('DOMContentLoaded', function() {
            const chatItems = document.querySelectorAll('.chat-item');
            
            chatItems.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    item.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, 100 + (index * 100));
            });
        });
    </script>
</body>
</html>