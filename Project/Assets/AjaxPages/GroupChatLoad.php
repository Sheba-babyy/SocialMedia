<?php
include("../Connection/Connection.php");
session_start();

$selQry = "SELECT gc.*, 
                  uf.user_name AS from_user_name, 
                  uf.user_photo AS from_user_photo
           FROM tbl_groupchat gc 
           INNER JOIN tbl_user uf ON uf.user_id = gc.user_from_id
           WHERE gc.group_id = '" . $_GET["id"] . "'
           ORDER BY gc.groupchat_datetime";
$result = $con->query($selQry);
$currentDate = '';

while ($data = $result->fetch_assoc()) {
    $messageDate = $data["groupchat_datetime"] ? date('Y-m-d', strtotime($data["groupchat_datetime"])) : '';
    if ($messageDate != $currentDate) {
        $currentDate = $messageDate;
        echo "<div class='date-divider'>" . date('M d, Y', strtotime($currentDate)) . "</div>";
    }
    
    $isSent = $data["user_from_id"] == $_SESSION["uid"];
    $messageClass = $isSent ? "sent" : "received";
?>

   <!-- Start message container -->
    <div class="message <?php echo $messageClass ?>" data-chat-id="<?php echo $data['groupchat_id'] ?>">

    <!-- Show sender info for received messages -->
    <?php if (!$isSent) { ?>
        <div class="sender-info">
            <?php if (!empty($data["from_user_photo"])) { ?>
                <img src="../Assets/Files/UserDocs/<?php echo $data["from_user_photo"] ?>" 
                     class="sender-photo" 
                     alt="User icon"
                     onerror="this.onerror=null; this.parentNode.innerHTML='<div class=\'user-icon\'><i class=\'fas fa-user\'></i></div><span class=\'sender-name\'><?php echo htmlspecialchars($data["from_user_name"]) ?></span>'">
            <?php } else { ?>
                <div class="user-icon">
                    <i class="fas fa-user"></i>
                </div>
            <?php } ?>
            <span class="sender-name"><?php echo htmlspecialchars($data["from_user_name"]) ?></span>
        </div>
    <?php } ?>

        <!-- Message Content -->
               <div class="message-content">
        <?php 
        // 🔹 Case 1: Shared Profile
        if (!empty($data["groupchat_file"]) && strpos($data["groupchat_file"], "profile_") === 0) {
            $sharedProfileId = str_replace("profile_", "", $data["groupchat_file"]);

            $profileQry = $con->query("SELECT user_name, user_photo FROM tbl_user WHERE user_id='$sharedProfileId'");
            if ($profileQry && $profileQry->num_rows > 0) {
                $profileData = $profileQry->fetch_assoc();
                ?>
                <div class="shared-profile-card" 
                     onclick="window.location.href='../User/ViewProfile.php?pid=<?php echo $sharedProfileId ?>'">
                    <img src="../Assets/Files/UserDocs/<?php echo $profileData['user_photo'] ?: 'default.avif' ?>" 
                         alt="Profile Photo" class="shared-profile-photo">
                    <div class="shared-profile-info">
                        <span class="shared-profile-name"><?php echo htmlspecialchars($profileData['user_name']) ?></span>
                        <span class="shared-profile-text">View Profile</span>
                    </div>
                </div>
                <?php
            } else {
                echo "<i>Profile not found</i>";
            }
        }

       // 🔹 Case 2: Shared Post in GroupChat
elseif (!empty($data["groupchat_content"]) && strpos($data["groupchat_content"], "SHARED_POST:") === 0) {
    $postId = intval(str_replace("SHARED_POST:", "", $data["groupchat_content"]));
    $postRes = $con->query("SELECT * FROM tbl_post WHERE post_id = $postId");
    if ($postRes && $postRes->num_rows > 0) {
        $post = $postRes->fetch_assoc();
        $file = $post['post_photo']; // may be image or video
        ?>
        <div class="shared-post-card" onclick="window.location='ViewSharedPost.php?pid=<?php echo $postId ?>'">
            <?php 
            // Only show image preview if it exists
            if (!empty($file) && preg_match('/\.(jpg|jpeg|png|gif)$/i', $file)) { ?>
                <img src="/Assets/Files/PostDocs/<?php echo htmlspecialchars($file) ?>" class="shared-post-img">
            <?php } ?>
            <div class="shared-post-caption">
                <?php echo htmlspecialchars($post['post_caption'] ?? '') ?>
            </div>
            <div class="shared-post-link">👉 View Original Post</div>
        </div>
        <?php
    } else {
        echo "<i>Post not found</i>";
    }
}
       // 🔹 Case 3: File upload (image/video/doc/etc.)
elseif (!empty($data["groupchat_file"])) {
    $file = $data["groupchat_file"];
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    echo "<div class='file-preview'>";
    if (in_array($ext, ['jpg','jpeg','png','gif'])) {
        // ✅ Clickable image with enlarge support
        echo "<a href='../Assets/Files/GroupChat/$file' target='_blank'>
                <img src='../Assets/Files/GroupChat/$file' 
                     alt='Attachment' class='chat-image'>
              </a>";
    } elseif (in_array($ext, ['mp4','webm','ogg'])) {
        // ✅ Playable video
        echo "<video controls style='max-width:200px; border-radius:8px;'>
                <source src='../Assets/Files/GroupChat/$file' type='video/$ext'>
                Your browser does not support the video tag.
              </video>";
    } else {
        // ✅ Other files → download
        echo "<a href='../Assets/Files/GroupChat/$file' target='_blank'>Download File</a>";
    }
    echo "</div>";
}

// 🔹 Case 4: Normal Text
elseif (!empty($data["groupchat_content"]) && strpos($data["groupchat_content"], "SHARED_POST:") !== 0) { ?>
    <div class="message-content"><?php echo nl2br(htmlspecialchars($data["groupchat_content"])) ?></div>
<?php }

        ?>
        </div>

        <!-- Message time -->
        <div class="message-time"><?php echo date('h:i A', strtotime($data["groupchat_datetime"])) ?></div>

        <!-- Add delete button for sent messages -->
        <?php if ($isSent) { ?>
            <span class="delete-btn" onclick="deleteMessage(<?php echo $data['groupchat_id'] ?>)">
                <i class="fas fa-trash"></i>
            </span>
        <?php } ?>
    </div>
<?php
}
?>

