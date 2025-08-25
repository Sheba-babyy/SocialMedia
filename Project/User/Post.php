<?php
include("../Assets/Connection/Connection.php");
session_start();
include("Header.php");

if(isset($_POST['btn_submit'])) {
    $post = $_FILES['file_post']['name'];
    $temp = $_FILES['file_post']['tmp_name'];
    move_uploaded_file($temp,"../Assets/Files/PostDocs/".$post);
    $caption = $_POST['txt_caption'];
    
    $insQry = "insert into tbl_post(post_caption,post_photo,post_date,user_id) values('".$caption."','".$post."',curDate(),'".$_SESSION['uid']."')";
    if($res = $con->query($insQry)) {
        echo '<script>
            alert("Post uploaded successfully");
            if (window.parent.closePostModal) {
                window.parent.closePostModal();
            } else {
                window.location = "HomePage.php";
            }
        </script>';
        exit;
    }
}

$sel = "select user_photo from tbl_user where user_id='".$_SESSION['uid']."'";
$res = $con->query($sel);
$data = $res->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Post</title>
    <style>
        :root {
            --primary: #2d4d2d;
            --secondary: #3a5c3a;
            --bg: #121212;
            --surface: #1e1e1e;
            --on-surface: #e0e0e0;
            --on-surface-light: #a0a0a0;
            --border: #333333;
            --radius: 8px;
            --warning: #ffa726;
            --error: #f44336;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background-color: var(--bg);
            color: var(--on-surface);
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }
        
        .post-container {
            max-width: 600px;
            margin: 140px auto;
            background: var(--surface);
            border-radius: var(--radius);
            overflow: hidden;
        }
        
        .post-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .post-title {
            font-size: 1.125rem;
            font-weight: 600;
        }
        
        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--on-surface-light);
            line-height: 1;
            padding: 0.25rem;
            border-radius: 50%;
            transition: background 0.15s ease;
        }
        
        .close-btn:hover {
            background: rgba(255,255,255,0.05);
        }
        
        .post-form {
            padding: 1.25rem;
        }
        
        .post-content {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .user-avatar {
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid var(--border);
            flex-shrink: 0;
        }
        
        .caption-input {
            flex: 1;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 0.75rem;
            font-size: 0.9375rem;
            min-height: 7.5rem;
            resize: none;
            background: var(--surface);
            color: var(--on-surface);
            line-height: inherit;
        }
        
        .caption-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(45, 77, 45, 0.25);
        }
        
        .char-counter {
            text-align: right;
            font-size: 0.8125rem;
            margin: 0.5rem 0 0.25rem;
            color: var(--on-surface-light);
        }
        
        .char-counter.warning {
            color: var(--warning);
        }
        
        .char-counter.error {
            color: var(--error);
        }
        
        .progress-container {
            height: 0.1875rem;
            background: var(--border);
            border-radius: 0.1875rem;
            margin-bottom: 1rem;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            background: var(--primary);
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .progress-bar.warning {
            background: var(--warning);
        }
        
        .progress-bar.error {
            background: var(--error);
        }
        
        .action-area {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
        }
        
        .media-option {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            background: rgba(255,255,255,0.05);
            border-radius: var(--radius);
            cursor: pointer;
            transition: all 0.15s ease;
            font-size: 0.875rem;
        }
        
        .media-option:hover {
            background: rgba(255,255,255,0.1);
        }
        
        .media-icon {
            margin-right: 0.5rem;
            color: var(--primary);
            width: 1.125rem;
            height: 1.125rem;
        }
        
        .post-button {
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius);
            padding: 0.5rem 1.25rem;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        
        .post-button:hover {
            background-color: var(--secondary);
            transform: translateY(-1px);
        }
        
        .post-button:disabled {
            background-color: var(--border);
            cursor: not-allowed;
            opacity: 0.7;
            transform: none;
        }
        
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="post-container">
        <div class="post-header">
            <div class="post-title">Create Post</div>
        </div>
        
        <form class="post-form" method="post" enctype="multipart/form-data">
            <div class="post-content">
                <img src="../Assets/Files/UserDocs/<?php echo $data['user_photo']?>" class="user-avatar" alt="User profile picture">
                <textarea name="txt_caption" class="caption-input" placeholder="What's on your mind?" maxlength="500" id="postCaption"></textarea>
            </div>
            
            <div class="char-counter" id="charCounter">0/500</div>
            <div class="progress-container">
                <div class="progress-bar" id="progressBar"></div>
            </div>
            
            <div class="action-area">
                <div class="media-option" onclick="document.getElementById('file_post').click()">
                    <span class="media-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9C3 6.17157 3 4.75736 3.87868 3.87868C4.75736 3 6.17157 3 9 3H15C17.8284 3 19.2426 3 20.1213 3.87868C21 4.75736 21 6.17157 21 9V15C21 17.8284 21 19.2426 20.1213 20.1213C19.2426 21 17.8284 21 15 21H9C6.17157 21 4.75736 21 3.87868 20.1213C3 19.2426 3 17.8284 3 15V9Z"/>
                            <path d="M8 14C9.10457 14 10 13.1046 10 12C10 10.8954 9.10457 10 8 10C6.89543 10 6 10.8954 6 12C6 13.1046 6.89543 14 8 14Z"/>
                            <path d="M21 15L16 10L5 21"/>
                        </svg>
                    </span>
                    <span>Add Media</span>
                    <input type="file" name="file_post" id="file_post" accept="image/*,video/*" class="hidden">
                </div>
                
                <button type="submit" name="btn_submit" class="post-button" id="postButton">Post</button>
            </div>
        </form>
    </div>

    <script>
        const postCaption = document.getElementById('postCaption');
        const charCounter = document.getElementById('charCounter');
        const progressBar = document.getElementById('progressBar');
        const postButton = document.getElementById('postButton');
        const fileInput = document.getElementById('file_post');

        // Character limit functionality
        postCaption.addEventListener('input', updateCharacterCount);
        
        function updateCharacterCount() {
            const currentLength = this.value.length;
            const maxLength = this.getAttribute('maxlength');
            const percentage = (currentLength / maxLength) * 100;
            
            charCounter.textContent = `${currentLength}/${maxLength}`;
            progressBar.style.width = `${percentage}%`;
            
            if (currentLength > maxLength * 0.9) {
                charCounter.classList.add('error');
                charCounter.classList.remove('warning');
                progressBar.classList.add('error');
                progressBar.classList.remove('warning');
            } else if (currentLength > maxLength * 0.7) {
                charCounter.classList.add('warning');
                charCounter.classList.remove('error');
                progressBar.classList.add('warning');
                progressBar.classList.remove('error');
            } else {
                charCounter.classList.remove('warning', 'error');
                progressBar.classList.remove('warning', 'error');
            }
            
            validateForm();
        }

        // File input change handler
        fileInput.addEventListener('change', validateForm);

        // Form validation
        function validateForm() {
            postButton.disabled = !(postCaption.value.trim().length > 0 || fileInput.files.length > 0);
        }

        // Initialize
        postCaption.dispatchEvent(new Event('input'));
    </script>
</body>
</html>