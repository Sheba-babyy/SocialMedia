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
        body {
            background-color: #f0f2f5;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .chat-container {
            width: 100%;
            max-width: 900px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 90vh;
            position: relative;
        }

        .chat-header {
            background: #E53935;
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
        }

        .chat-header .user-info {
            flex: 1;
        }

        .chat-header .user-info h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 500;
        }

        .chat-header .options {
            cursor: pointer;
            font-size: 20px;
        }

        .chat-body {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .chat-body::-webkit-scrollbar {
            width: 6px;
        }

        .chat-body::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
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
            background: #dcf8c6;
            align-self: flex-end;
            margin-left: auto;
            border-bottom-right-radius: 2px;
        }

        .message.received {
            background: white;
            align-self: flex-start;
            border-bottom-left-radius: 2px;
            box-shadow: 0 1px 0.5px rgba(0, 0, 0, 0.13);
        }

        .sender-info {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #667781;
        }

        .sender-photo {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            object-fit: cover;
        }

        .sender-name {
            font-weight: 500;
        }

        .message-content {
            word-break: break-word;
            font-size: 14px;
            line-height: 1.4;
        }

        .message-time {
            font-size: 11px;
            color: #667781;
            align-self: flex-end;
        }

        .message .delete-btn {
            display: none;
            position: absolute;
            top: 5px;
            right: 5px;
            cursor: pointer;
            color: #ff4444;
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
            border-radius: 8px;
        }

        .date-divider {
            text-align: center;
            color: #667781;
            font-size: 12px;
            margin: 15px 0;
            background: rgba(255, 255, 255, 0.8);
            padding: 5px;
            border-radius: 5px;
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
        }

        .chat-footer {
            padding: 10px 20px;
            background: #f0f2f5;
            display: flex;
            align-items: center;
            gap: 10px;
            border-top: 1px solid #d9d9d9;
        }

        .message-input {
            flex: 1;
            padding: 10px 15px;
            border: none;
            border-radius: 20px;
            background: white;
            font-size: 14px;
            outline: none;
        }

        .send-btn, .attach-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 20px;
            color:#E53935;
        }

        .send-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .user-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .user-content {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            position: relative;
            max-width: 400px;
        }

        .user-content img {
            width: 200px;
            height: 200px;
            border-radius: 10px;
            object-fit: cover;
        }

        .user-content p {
            margin: 10px 0;
            font-size: 14px;
            color: #333;
        }

        .close-user {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            cursor: pointer;
            color: #667781;
        }

        .options-menu {
            display: none;
            position: absolute;
            right: 20px;
            top: 60px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            z-index: 100;
        }

        .options-menu a {
            display: block;
            padding: 10px 20px;
            color: #333;
            text-decoration: none;
            font-size: 14px;
        }

        .options-menu a:hover {
            background: #f0f2f5;
        }

        .loader {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .loader::after {
            content: '';
            width: 40px;
            height: 40px;
            border: 4px solid #E53935;
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
            <a href="#" onclick="clearChat()"><i class="fas fa-broom"></i> Clear Your Messages</a>
            <a href="ViewProfile.php?id=<?php echo $recipientId; ?>"><i class="fas fa-user"></i> View Profile</a>
        </div>
        <div class="chat-body" id="chatBody"></div>
        <div class="chat-footer">
            <label for="fileInput">
                <i class="fas fa-paperclip attach-btn"></i>
            </label>
            <input type="file" id="fileInput" style="display: none;" onchange="previewFile()">
            <input type="text" class="message-input" id="messageInput" placeholder="Type a message..." autocomplete="off">
            <button class="send-btn" id="sendBtn" onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
            <div id="filePreview" class="file-preview"></div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let isSending = false;
        let isInitialLoad = true;

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

        function loadMessages() {
            const recipientId = document.getElementById("recipientId").value;
            const chatBody = document.getElementById("chatBody");
            const oldScrollHeight = chatBody.scrollHeight;
            const isScrolledToBottom = chatBody.scrollHeight - chatBody.scrollTop <= chatBody.clientHeight + 5;

            $.ajax({
                url: `../Assets/AjaxPages/ChatLoad.php?id=${recipientId}`,
                success: (data) => {
                    const oldScrollTop = chatBody.scrollTop;
                    $("#chatBody").html(data);
                    const newScrollHeight = chatBody.scrollHeight;

                    if (isInitialLoad || isScrolledToBottom) {
                        chatBody.scrollTop = newScrollHeight;
                        isInitialLoad = false;
                    } else {
                        chatBody.scrollTop = oldScrollTop + (newScrollHeight - oldScrollHeight);
                    }
                }
            });
        }

        function previewFile() {
            const file = document.getElementById("fileInput").files[0];
            const preview = document.getElementById("filePreview");
            preview.innerHTML = "";
            if (file) {
                if (file.type.startsWith('image/')) {
                    const img = document.createElement("img");
                    img.src = URL.createObjectURL(file);
                    img.className = "file-preview";
                    img.style.maxWidth = "100px";
                    preview.appendChild(img);
                } else {
                    preview.innerHTML = `<p>Selected: ${file.name}</p>`;
                }
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
    </script>
</body>
</html>