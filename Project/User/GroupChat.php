<?php
include("../Assets/Connection/Connection.php");
session_start();

if (!isset($_SESSION["uid"])) {
    header("Location: login.php");
    exit;
}

$uid = mysqli_real_escape_string($con, $_SESSION["uid"]);
$groupId = isset($_GET["id"]) && is_numeric($_GET["id"]) ? mysqli_real_escape_string($con, $_GET["id"]) : null;

// Check if group exists
$selGroup = "SELECT * FROM tbl_group WHERE group_id = '$groupId'";
$groupResult = $con->query($selGroup);
if (!$groupResult || $groupResult->num_rows == 0) {
    echo "<script>alert('Group does not exist'); window.location='Groups.php';</script>";
    exit;
}
$row = $groupResult->fetch_assoc();

// Check if user is the owner of the group
$isOwnerQry = "SELECT * FROM tbl_group WHERE group_id = '$groupId' AND user_id = '$uid'";
$isOwnerRes = $con->query($isOwnerQry);

if ($isOwnerRes && $isOwnerRes->num_rows > 0) {
    //  User is the owner → allow access
} else {
    //  Otherwise, must be an approved member
    $memberCheck = "SELECT * FROM tbl_groupmembers 
                    WHERE user_id = '$uid' 
                    AND group_id = '$groupId' 
                    AND groupmembers_status = 1";
    $memberResult = $con->query($memberCheck);

    if ($memberResult->num_rows == 0) {
        echo "<script>alert('You are not a member of this group'); window.location='Groups.php';</script>";
        exit;
    }
}

// Check if user is admin
$isAdmin = false;
$adminQry = "SELECT * FROM tbl_admin WHERE admin_id = '$uid'";
$adminRes = $con->query($adminQry);
if ($adminRes && $adminRes->num_rows > 0) {
    $isAdmin = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat in <?php echo htmlspecialchars($row["group_name"]) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
     <style>
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
            width: 100%;
            max-width: 900px;
            background: #1e1e1e;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.4);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 90vh;
            position: relative;
        }

        .chat-header {
            background: #b71c1c; /* Dark red */
            color: white;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .chat-header img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
        }

        .chat-header .group-info {
            flex: 1;
        }

        .chat-header .group-info h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 500;
            padding-top: 20px;
        }

        .chat-header .group-info a {
            color: white;
            text-decoration: none;
            margin-right: 10px;
            font-size: 14px;
        }

        .chat-header .options {
            cursor: pointer;
            font-size: 20px;
            color: white;
        }

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
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }

        .message {
            max-width: 70%;
            padding: 8px 12px;
            border-radius: 8px;
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .message.sent {
            background: #2d2d2d; /* Dark gray instead of red */
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
            color: #e0e0e0;
            box-shadow: 0 1px 0.5px rgba(0,0,0,0.3);
        }

        .sender-info {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #aaa;
        }

        .sender-photo, .user-icon {
            width: 24px;
            height: 24px;
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

        .message-content {
            word-break: break-word;
            font-size: 14px;
            line-height: 1.4;
        }

        .message-time {
            font-size: 11px;
            color: #aaa;
            align-self: flex-end;
        }

        .message .delete-btn {
            display: none;
            position: absolute;
            top: 5px;
            right: 5px;
            cursor: pointer;
            color: #ff6b6b; /* Light red */
            font-size: 14px;
        }

        .message.sent:hover .delete-btn {
            display: block;
        }

        .file-preview {
            margin-top: 8px;
            max-width: 200px;
        }

        .file-preview img {
            max-width: 100%;
            border-radius: 6px;
            border: 1px solid #444;
        }
        .file-preview img:hover {
            transform: scale(1.03); /* Reduced from 1.05 */
        }
        
        .file-preview video {
            max-width: 200px;
            border-radius: 6px; /* Reduced from 10px */
            border: 1px solid #444; /* Added subtle border */
        }
        .date-divider {
            text-align: center;
            color: #aaa;
            font-size: 12px;
            margin: 15px 0;
            background: rgba(45, 45, 45, 0.8);
            padding: 5px;
            border-radius: 5px;
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
            border: 1px solid #444;
        }

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

        .group-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .group-content {
            background: #1e1e1e;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            position: relative;
            max-width: 400px;
            color: #e0e0e0;
            border: 1px solid #444;
        }

        .group-icon {
            font-size: 100px;
            color: #b71c1c; /* Red accent */
            margin: 20px 0;
        }

        .group-icon-fallback {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100px;
            height: 100px;
            background: #333;
            border-radius: 50%;
            margin: 20px auto;
            color: #b71c1c; /* Red accent */
            font-size: 50px;
        }

        .image-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 200px;
        }

        .group-content img {
            width: 200px;
            height: 200px;
            border-radius: 10px;
            object-fit: cover;
            border: 1px solid #444;
        }

        .group-content p {
            margin: 10px 0;
            font-size: 14px;
            color: #e0e0e0;
        }

        .close-group {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            cursor: pointer;
            color: #aaa;
        }

        .options-menu {
            display: none;
            position: absolute;
            right: 20px;
            top: 60px;
            background: #2d2d2d;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
            z-index: 100;
            border: 1px solid #444;
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

        .loader {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(30, 30, 30, 0.8);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .loader::after {
            content: '';
            width: 40px;
            height: 40px;
            border: 3px solid #b71c1c; /* Red accent */
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .shared-profile-card {
            display: flex;
            align-items: center;
            border: 1px solid #444;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            background: #2d2d2d;
            max-width: 250px;
            transition: background 0.2s;
        }

        .shared-profile-card:hover {
            background: #333;
        }

        .shared-profile-photo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
            border: 1px solid #444;
        }

        .shared-profile-info {
            display: flex;
            flex-direction: column;
        }

        .shared-profile-name {
            font-weight: bold;
            font-size: 14px;
            color: #e0e0e0;
        }

        .shared-profile-text {
            font-size: 12px;
            color: #aaa;
        }

        /* ===== Shared Post Card ===== */
        .shared-post-card {
            display: flex;
            flex-direction: column;
            gap: 6px;
            max-width: 250px;
            background: #2d2d2d;
            padding: 10px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid #444;
        }

        .shared-post-card:hover {
            background: #333;
        }

        .shared-post-card img,
        .shared-post-card video {
            width: 100%;
            border-radius: 6px;
            border: 1px solid #444;
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

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 600px) {
            .chat-container {
                height: 100vh;
                border-radius: 0;
            }

            .message {
                max-width: 80%;
            }
        }
    </style>
</head>
<body>
    <div class="loader" id="loader"></div>
    <div class="group-modal" id="groupModal">
        <div class="group-content">
            <i class="fas fa-times close-group" onclick="closeGroupModal()"></i>
            <div class="image-container">
            <?php if(empty($row["group_photo"])): ?>
                <i class="fas fa-users group-icon"></i>
            <?php else: ?>
                <img src="../Assets/Files/GroupDocs/<?php echo htmlspecialchars($row["group_photo"]?:'default.png') ?>" 
                     alt="Group Icon" 
                     onerror="this.onerror=null;this.className='group-icon-fallback';this.innerHTML='<i class=\'fas fa-users\'></i>';">
            <?php endif; ?>
            </div>
            <h3><?php echo htmlspecialchars($row["group_name"]) ?></h3>
            <p><?php echo htmlspecialchars($row["group_description"] ?: "No description available") ?></p>
        </div>
    </div>
    <div class="chat-container">
        <div class="chat-header">
            <img src="../Assets/Files/GroupDocs/<?php echo htmlspecialchars($row["group_photo"]?:'default.png') ?>" alt="Group Photo" onclick="openGroupModal()">
            <div class="group-info">
               <a href="GroupInfo.php?id=<?php echo $groupId ?>"> <h3><?php echo htmlspecialchars($row["group_name"]) ?></h3>
                <input type="hidden" id="groupId" value="<?php echo $groupId ?>"></a>
                <!-- <a href="GroupMembersList.php?gmlid=<?php echo $groupId; ?>"><i class="fas fa-users"></i> Group Members</a>
                <?php if ($isAdmin) { ?>
                    <a href="GroupRequests.php?grid=<?php echo $groupId; ?>"><i class="fas fa-user-plus"></i> Group Requests</a>
                <?php } ?> -->
            </div>
            <i class="fas fa-ellipsis-v options" onclick="toggleOptions()"></i>
        </div>
        <div class="options-menu" id="optionsMenu">
            <a href="#" onclick="clearChat()"><i class="fas fa-broom"></i> Clear Your Messages</a>
        </div>

        <div class="chat-body" id="chatBody"></div>
        <div class="chat-footer">
            <label for="fileInput">
                <i class="fas fa-paperclip attach-btn"></i>
            </label>
        <input type="file" id="fileInput" name="file" 
       accept="image/*,video/*,.pdf,.doc,.docx,.zip"
       style="display:none;" onchange="previewFile()">   
            <input type="text" class="message-input" id="messageInput" placeholder="Type a message..." autocomplete="off">
            <button class="send-btn" id="sendBtn" onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
            <div id="filePreview" class="file-preview"></div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let isSending = false;
        let isInitialLoad = true;

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
            const groupId = document.getElementById("groupId").value;

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
            formData.append("uid", groupId);
            if (file) formData.append("file", file);

            $.ajax({
                url: "../Assets/AjaxPages/GAjaxChat.php",
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
        }

        function deleteMessage(chatId) {
            if (confirm("Are you sure you want to delete this message?")) {
                $.ajax({
                    url: `../Assets/AjaxPages/GAjaxChat.php?action=delete&chat_id=${chatId}&uid=${document.getElementById("groupId").value}`,
                    success: () => loadMessages()
                });
            }
        }

        function clearChat() {
            const groupId = document.getElementById("groupId").value;
            if (confirm("Are you sure you want to clear all your messages?")) {
                document.getElementById("loader").style.display = "flex";
                $.ajax({
                    url: `../Assets/AjaxPages/GAjaxChat.php?action=clear&uid=${groupId}`,
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

        function openGroupModal() {
            document.getElementById("groupModal").style.display = "flex";
        }

        function closeGroupModal() {
            document.getElementById("groupModal").style.display = "none";
        }

        // ✅ Track user scroll to control auto-scroll
const chatBody = document.getElementById("chatBody");
let autoScroll = true;

chatBody.addEventListener("scroll", () => {
    autoScroll = chatBody.scrollTop + chatBody.clientHeight >= chatBody.scrollHeight - 50;
});

function loadMessages() {
    const chatBody = document.getElementById("chatBody");
    const groupId = document.getElementById("groupId").value;

    fetch(`../Assets/AjaxPages/GroupChatLoad.php?id=${groupId}`)
        .then(response => response.text())
        .then(data => {
            // ✅ If nothing changed, do nothing
            if (chatBody.dataset.lastData === data) return;

            chatBody.dataset.lastData = data;

            // Save old scroll info
            const oldScrollTop = chatBody.scrollTop;
            const oldScrollHeight = chatBody.scrollHeight;

            chatBody.innerHTML = data;

            const newScrollHeight = chatBody.scrollHeight;

            if (autoScroll) {
                // ✅ Stick to bottom only if user was already there
                chatBody.scrollTop = chatBody.scrollHeight;
            } else {
                // ✅ Keep user at the same place when new messages load
                chatBody.scrollTop = oldScrollTop + (newScrollHeight - oldScrollHeight);
            }
        })
        .catch(err => console.error("Load messages failed:", err));
}

// Auto-load
loadMessages();
setInterval(loadMessages, 1000);


        // Close options menu when clicking outside
        document.addEventListener("click", (e) => {
            const optionsMenu = document.getElementById("optionsMenu");
            if (!e.target.closest(".options") && !e.target.closest(".options-menu")) {
                optionsMenu.style.display = "none";
            }
        });
    </script>
</body>
</html>