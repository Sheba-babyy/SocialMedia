<?php
include("../Assets/Connection/Connection.php");
session_start();

if (!isset($_SESSION["uid"])) {
    header("Location: login.php");
    exit;
}

$uid = mysqli_real_escape_string($con, $_SESSION["uid"]);
$recipientId = isset($_GET["id"]) && is_numeric($_GET["id"]) ? mysqli_real_escape_string($con, $_GET["id"]) : null;

// Check if recipient exists
$selUser = "SELECT * FROM tbl_user WHERE user_id = '$recipientId'";
$userResult = $con->query($selUser);
if (!$userResult || $userResult->num_rows == 0) {
    echo "<script>alert('User does not exist'); window.location='ChatList.php';</script>";
    exit;
}
$row = $userResult->fetch_assoc();

// Check if users are friends
$friendCheck = "SELECT * FROM tbl_friends 
                WHERE ((user_from_id = '$uid' AND user_to_id = '$recipientId') 
                OR (user_from_id = '$recipientId' AND user_to_id = '$uid')) 
                AND friends_status = 1";
$friendResult = $con->query($friendCheck);
if ($friendResult->num_rows == 0) {
    echo "<script>alert('You are not friends with this user'); window.location='ChatList.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with <?php echo htmlspecialchars($row["user_name"]) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
        /* Body & Container */
        body {
            background-color: #121212;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            color: #e0e0e0;
        }

        .chat-container {
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 900px;
            height: 90vh;
            background: #1e1e1e;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.4);
            margin: 0 auto; /* Ensures centering */
        }

        /* Header */
        .chat-header {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            background: #b71c1c; /* Dark red */
            color: #fff;
            gap: 15px;
        }

        .chat-header img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
            border: 2px solid #fff;
        }

        .chat-header .user-info {
            flex: 1;
        }

        .chat-header .user-info h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 500;
            padding-top: 10px;
        }

        .chat-header .options {
            cursor: pointer;
            font-size: 20px;
            color: #fff;
        }

        /* Chat body */
        .chat-body {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            background-color: #1e1e1e;
        }

        .chat-body::-webkit-scrollbar {
            width: 6px;
        }

        .chat-body::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.2);
            border-radius: 3px;
        }

        /* Messages */
        .message {
            max-width: 70%;
            padding: 8px 12px;
            border-radius: 12px;
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 4px;
            word-break: break-word;
            transition: all 0.2s ease;
        }

        .message.sent {
            background: #2d2d2d; /* Changed from red to dark gray */
            align-self: flex-end;
            margin-left: auto;
            border-radius: 12px;
            color: #fff;
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        }

        .message.received {
            background: #2d2d2d;
            align-self: flex-start;
            border-radius: 12px;
            box-shadow: 0 1px 0.5px rgba(0,0,0,0.3);
            color: #e0e0e0;
        }

        /* Sender info */
        .sender-info {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #aaa;
        }

        .sender-photo, .user-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            object-fit: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #333;
        }

        .user-icon i {
            font-size: 12px;
            color: #b71c1c; /* Red accent */
        }

        .sender-name {
            font-weight: 500;
            font-size: 12px;
            color: #b71c1c; /* Red accent */
        }

        /* Message content & time */
        .message-content {
            font-size: 14px;
            line-height: 1.4;
        }

        .message-time {
            font-size: 11px;
            color: #aaa;
            align-self: flex-end;
        }

        /* Delete button */
        .delete-btn {
            display: none;
            position: absolute;
            top: 4px;
            right: 4px;
            font-size: 14px;
            color: #ff6b6b; /* Light red */
            cursor: pointer;
        }

        .message.sent:hover .delete-btn {
            display: block;
        }

        
        .file-preview {
            margin-top: 6px;
            max-width: 200px;
        }

        .file-preview img {
            max-width: 100%;
            border-radius: 5px; 
            border: 1px solid #444; 
        }
        
        .file-preview img:hover {
            transform: scale(1.03); 
        }
        
        .file-preview video {
            max-width: 200px;
            border-radius: 5px; 
            border: 1px solid #444;
        }
        
        /* Shared profile cards - Reduced border thickness */
        .shared-profile-card {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            background: #2d2d2d;
            border-radius: 8px; /* Reduced from 10px */
            cursor: pointer;
            gap: 10px;
            max-width: 250px;
            transition: all 0.2s ease;
            border: 1px solid #444; /* Added subtle border */
        }
        
        .shared-profile-card:hover {
            background: #333;
            border-color: #555;
        }
        
        .shared-profile-card img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #444; /* Added subtle border */
        }
        
        .shared-profile-info {
            display: flex;
            flex-direction: column;
        }
        
        .shared-profile-info strong {
            font-size: 14px;
            color: #e0e0e0;
        }
        
        .shared-profile-info span {
            font-size: 12px;
            color: #aaa;
        }
        
        /* ===== Shared Post Card - Reduced border thickness ===== */
        .shared-post-card {
            display: flex;
            flex-direction: column;
            gap: 6px;
            max-width: 250px;
            background: #2d2d2d;
            padding: 10px;
            border-radius: 8px; /* Reduced from 10px */
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid #444; /* Added subtle border */
        }
        
        .shared-post-card:hover {
            background: #333;
            border-color: #555;
        }
        
        .shared-post-card img,
        .shared-post-card video {
            width: 100%;
            border-radius: 6px; /* Reduced from 10px */
            border: 1px solid #444; /* Added subtle border */
        }
        
        .shared-post-caption {
            font-size: 13px;
            color: #e0e0e0;
        }
        
        .shared-post-link {
            font-size: 12px;
            color: #b71c1c; /* Red accent */
            font-weight: 500;
        }

        /* Date divider */
        .date-divider {
            text-align: center;
            font-size: 12px;
            color: #aaa;
            margin: 10px 0;
            padding: 4px 8px;
            border-radius: 6px; /* Reduced from 6px */
            background: rgba(45,45,45,0.8);
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
            border: 1px solid #444; /* Added subtle border */
        }

        /* Footer */
        .chat-footer {
            padding: 10px 20px;
            background: #1a1a1a;
            display: flex;
            align-items: center;
            gap: 10px;
            border-top: 1px solid #333;
        }

        .message-input {
            flex: 1;
            padding: 10px 15px;
            border: none;
            border-radius: 20px;
            background: #2d2d2d;
            font-size: 14px;
            outline: none;
            color: #e0e0e0;
        }

        .message-input::placeholder {
            color: #888;
        }

        .send-btn, .attach-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 20px;
            color: #b71c1c; /* Red accent */
        }

        .send-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Modals */
        .user-modal {
            display: none;
            position: fixed;
            top:0;
            left:0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .user-content {
            background: #1e1e1e;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            position: relative;
            max-width: 400px;
            color: #e0e0e0;
            border: 1px solid #444; /* Added subtle border */
        }

        .user-content img {
            width: 200px;
            height: 200px;
            border-radius: 8px; /* Reduced from 10px */
            object-fit: cover;
            border: 1px solid #444; /* Added subtle border */
        }

        .user-content p {
            margin: 10px 0;
            font-size: 14px;
            color: #e0e0e0;
        }

        .close-user {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            cursor: pointer;
            color: #aaa;
        }

        /* Options menu */
        .options-menu {
            display: none;
            position: absolute;
            right: 20px;
            top: 60px;
            background: #2d2d2d;
            border-radius: 6px; /* Reduced from 8px */
            box-shadow: 0 2px 8px rgba(0,0,0,0.4);
            z-index: 100;
            border: 1px solid #444; /* Added subtle border */
        }

        .options-menu a {
            display: block;
            padding: 10px 20px;
            color: #e0e0e0;
            text-decoration: none;
            font-size: 14px;
        }

        .options-menu a:hover {
            background: #333;
            color: #b71c1c; /* Red accent */
        }

        /* Loader */
        .loader {
            display: none;
            position: fixed;
            top:0;
            left:0;
            width: 100%;
            height: 100%;
            background: rgba(30,30,30,0.8);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .loader::after {
            content: '';
            width: 40px;
            height: 40px;
            border: 3px solid #b71c1c; /* Reduced from 4px */
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 600px) {
            .chat-container {
                height: 100vh;
                border-radius: 0;
                width: 100%;
            }

            .message {
                max-width: 80%;
            }
        }
    </style>
</head>
<body>
    <div class="loader" id="loader"></div>
    <div class="user-modal" id="userModal">
        <div class="user-content">
            <i class="fas fa-times close-user" onclick="closeUserModal()"></i>
            <img src="../Assets/Files/UserDocs/<?php echo htmlspecialchars($row["user_photo"]?:'default.avif') ?>" alt="User Photo">
            <h3><?php echo htmlspecialchars($row["user_name"]) ?></h3>
        </div>
    </div>
    <div class="chat-container">
        <div class="chat-header">
            <img src="../Assets/Files/UserDocs/<?php echo htmlspecialchars($row["user_photo"]?:'default.avif') ?>" alt="User Photo" onclick="openUserModal()">
            <div class="user-info">
                <h3><?php echo htmlspecialchars($row["user_name"]) ?></h3>
                <input type="hidden" id="recipientId" value="<?php echo $recipientId ?>">
            </div>
            <i class="fas fa-ellipsis-v options" onclick="toggleOptions()"></i>
        </div>
        <div class="options-menu" id="optionsMenu">
            <a href="ViewProfile.php?id=<?php echo $recipientId; ?>"><i class="fas fa-user"></i> View Profile</a>
            <a href="#" onclick="clearChat()"><i class="fas fa-trash"></i> Clear Chat</a>
        </div>
        <div class="chat-body" id="chatBody"></div>
        <div class="chat-footer">
            <label for="fileInput">
                <i class="fas fa-paperclip attach-btn"></i>
            </label>
        <input type="file" id="fileInput" name="file" 
       accept="image/*,video/*,.pdf,.doc,.docx,.zip" 
       style="display: none;" onchange="previewFile()">
            <input type="text" class="message-input" id="messageInput" placeholder="Type a message..." autocomplete="off">
            <button class="send-btn" id="sendBtn" onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
            <div id="filePreview" class="file-preview"></div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let isSending = false;
        let isInitialLoad = true;
        let autoScroll = true;

        // Add event listener for Enter key when the page loads
document.addEventListener('DOMContentLoaded', function() {
    const messageInput = document.getElementById("messageInput");
    
    // Add Enter key event listener to message input
    messageInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault(); // Prevent default form submission behavior
            sendMessage();
        }
    });
});

        function sendMessage() {
            if (isSending) return;
            isSending = true;
            const sendBtn = document.getElementById("sendBtn");
            sendBtn.disabled = true;

            const message = document.getElementById("messageInput").value.trim();
            const fileInput = document.getElementById("fileInput");
            const file = fileInput.files[0];
            const recipientId = document.getElementById("recipientId").value;

            if (!message && !file) {
                isSending = false;
                sendBtn.disabled = false;
                return;
            }

            if (message.length > 60) {
                alert("Message length should be less than 60 characters");
                isSending = false;
                sendBtn.disabled = false;
                return;
            }

            const formData = new FormData();
            formData.append("msg", message);
            formData.append("uid", recipientId);
            if (file) formData.append("file", file);

            $.ajax({
                url: "../Assets/AjaxPages/UAjaxChat.php",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: () => document.getElementById("loader").style.display = "flex",
                success: (response) => {
                    if (response.trim() === "Message sent") {
                        document.getElementById("messageInput").value = "";
                        document.getElementById("fileInput").value = "";
                        document.getElementById("filePreview").innerHTML = "";
                        loadMessages();
                    }
                },
                error: () => {
                    console.log("Failed to send message");
                },
                complete: () => {
                    isSending = false;
                    sendBtn.disabled = false;
                    document.getElementById("loader").style.display = "none";
                    document.getElementById("messageInput").value = "";
                    document.getElementById("fileInput").value = "";
                    document.getElementById("filePreview").innerHTML = "";
                }
            });
            // Send message on Enter key
            messageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    sendMessage();
                }
            });
        }

        function deleteMessage(chatId) {
            if (confirm("Are you sure you want to delete this message?")) {
                $.ajax({
                    url: `../Assets/AjaxPages/UAjaxChat.php?action=delete&chat_id=${chatId}&uid=${document.getElementById("recipientId").value}`,
                    success: () => loadMessages()
                });
            }
        }

        function clearChat() {
            const recipientId = document.getElementById("recipientId").value;
            if (confirm("Are you sure you want to clear all your messages?")) {
                document.getElementById("loader").style.display = "flex";
                $.ajax({
                    url: `../Assets/AjaxPages/UAjaxChat.php?action=clear&uid=${recipientId}`,
                    success: () => {
                        alert("Your messages cleared");
                        loadMessages();
                    },
                    complete: () => document.getElementById("loader").style.display = "none"
                });
            }
        }

    function previewFile() {
    const fileInput = document.getElementById('fileInput');
    const file = fileInput.files[0];
    const preview = document.getElementById('filePreview'); // ✅ fixed ID

    if (!file) {
        preview.innerHTML = "";
        return;
    }

    const fileType = file.type;
    preview.innerHTML = ""; // clear old preview

    if (fileType.startsWith("image/")) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" style="max-width:150px; border-radius:8px;">`;
        };
        reader.readAsDataURL(file);

    } else if (fileType.startsWith("video/")) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <video controls style="max-width:150px; border-radius:8px;">
                    <source src="${e.target.result}" type="${fileType}">
                </video>`;
        };
        reader.readAsDataURL(file);

    } else if (fileType === "application/pdf") {
        preview.innerHTML = `<div style="padding:5px; border:1px solid #ccc; border-radius:5px;">
                                📕 PDF Selected: ${file.name}
                             </div>`;

    } else if (fileType.includes("msword") || fileType.includes("officedocument")) {
        preview.innerHTML = `<div style="padding:5px; border:1px solid #ccc; border-radius:5px;">
                                📘 Document Selected: ${file.name}
                             </div>`;

    } else if (fileType === "application/zip" || fileType === "application/x-zip-compressed") {
        preview.innerHTML = `<div style="padding:5px; border:1px solid #ccc; border-radius:5px;">
                                📦 ZIP File Selected: ${file.name}
                             </div>`;

    } else {
        preview.innerHTML = `<div style="padding:5px; border:1px solid #ccc; border-radius:5px;">
                                📁 File Selected: ${file.name}
                             </div>`;
    }
}

        function toggleOptions() {
            const menu = document.getElementById("optionsMenu");
            menu.style.display = menu.style.display === "none" ? "block" : "none";
        }

        function openUserModal() {
            document.getElementById("userModal").style.display = "flex";
        }

        function closeUserModal() {
            document.getElementById("userModal").style.display = "none";
        }

        // Auto-load messages
loadMessages();
setInterval(loadMessages, 1000);

// Close options menu when clicking outside
document.addEventListener("click", (e) => {
    const optionsMenu = document.getElementById("optionsMenu");
    if (!e.target.closest(".options") && !e.target.closest(".options-menu")) {
        optionsMenu.style.display = "none";
    }
});

// ✅ Track user scroll to control auto-scroll
const chatBody = document.getElementById("chatBody");
chatBody.addEventListener("scroll", () => {
    autoScroll = chatBody.scrollTop + chatBody.clientHeight >= chatBody.scrollHeight - 50;
});

function loadMessages() {
    const chatBody = document.getElementById("chatBody");
    const recipientId = document.getElementById("recipientId").value;

    fetch(`../Assets/AjaxPages/ChatLoad.php?id=${recipientId}`)
        .then(response => response.text())
        .then(data => {
            // If content hasn't changed, do nothing
            if (chatBody.dataset.lastData === data) return;

            // Save new version
            chatBody.dataset.lastData = data;

            // Save scroll info before update
            const oldScrollTop = chatBody.scrollTop;
            const oldScrollHeight = chatBody.scrollHeight;

            chatBody.innerHTML = data;

            const newScrollHeight = chatBody.scrollHeight;

            if (autoScroll) {
                // Stick to bottom if user was already at bottom
                chatBody.scrollTop = chatBody.scrollHeight;
            } else {
                // Restore previous position while new messages load
                chatBody.scrollTop = oldScrollTop + (chatBody.scrollHeight - oldScrollHeight);
            }
        })
        .catch(err => console.error("Load messages failed:", err));
}


    </script>
</body>
</html>