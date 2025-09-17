<?php
include("../Connection/Connection.php");
session_start();

$selQry = "SELECT c.*, 
                  uf.user_name AS from_user_name, 
                  uf.user_photo AS from_user_photo,
                  ut.user_name AS to_user_name,
                  ut.user_photo AS to_user_photo
           FROM tbl_chat c 
           INNER JOIN tbl_user uf ON uf.user_id = c.user_from_id
           INNER JOIN tbl_user ut ON ut.user_id = c.user_to_id
           WHERE (c.user_from_id = '" . $_SESSION["uid"] . "' OR c.user_to_id = '" . $_SESSION["uid"] . "') 
             AND (c.user_from_id = '" . $_GET["id"] . "' OR c.user_to_id = '" . $_GET["id"] . "') 
           ORDER BY c.chat_datetime";
$result = $con->query($selQry);
$currentDate = '';

while ($data = $result->fetch_assoc()) {
    $messageDate = date('Y-m-d', strtotime($data["chat_datetime"]));
    if ($messageDate != $currentDate) {
        $currentDate = $messageDate;
        echo "<div class='date-divider'>" . date('M d, Y', strtotime($currentDate)) . "</div>";
    }

    $isSent = $data["user_from_id"] == $_SESSION["uid"];
    $messageClass = $isSent ? "sent" : "received";
    ?>
    <div class="message <?php echo $messageClass ?>" data-chat-id="<?php echo $data['chat_id'] ?>">

        <?php 
        // ✅ CASE 1: Profile Share
        if (!empty($data["chat_file"]) && strpos($data["chat_file"], "profile_") === 0) {
            $profile_id = intval(str_replace("profile_", "", $data["chat_file"]));
            $profileRes = $con->query("SELECT user_name, user_photo FROM tbl_user WHERE user_id = $profile_id");
            if ($profileRes && $profileRes->num_rows > 0) {
                $profile = $profileRes->fetch_assoc();
                ?>
                <div class="shared-profile-card" onclick="window.location='../User/ViewProfile.php?pid=<?php echo $profile_id ?>'">
                    <img src="../Assets/Files/UserDocs/<?php echo htmlspecialchars($profile['user_photo'] ?: 'default.avif') ?>" 
                         class="shared-profile-avatar">
                    <div class="shared-profile-info">
                        <strong><?php echo htmlspecialchars($profile['user_name']) ?></strong><br>
                        View Profile
                    </div>
                </div>
                <?php
            }
        }

// ✅ CASE: Shared Post
else if (!empty($data["chat_content"]) && strpos($data["chat_content"], "SHARED_POST:") === 0) {
    $postId = intval(str_replace("SHARED_POST:", "", $data["chat_content"]));
    $postRes = $con->query("SELECT * FROM tbl_post WHERE post_id = $postId");
    if ($postRes && $postRes->num_rows > 0) {
        $post = $postRes->fetch_assoc();
        $file = $post['post_photo'];
        ?>
        <div class="shared-post-card" onclick="window.location='ViewSharedPost.php?pid=<?php echo $postId ?>'">
            <?php 
            if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $file)) { ?>
                <img src="../Assets/Files/Post/<?php echo htmlspecialchars($file) ?>" 
                     class="shared-post-img">
            <?php } elseif (preg_match('/\.(mp4|webm|ogg)$/i', $file)) { 
                $ext = pathinfo($file, PATHINFO_EXTENSION); ?>
                <video class="shared-post-video" controls style="max-width:250px; border-radius:8px;">
                    <source src="../Assets/Files/Post/<?php echo htmlspecialchars($file) ?>" type="video/<?php echo strtolower($ext) ?>">
                    Your browser does not support the video tag.
                </video>
            <?php } ?>

            <div class="shared-post-caption">
                <?php echo htmlspecialchars($post['post_caption']) ?>
            </div>
            <div class="shared-post-link">👉 View Original Post</div>
        </div>
        <?php
    }
}

        // ✅ CASE 2: Normal File (image/video/doc)
else if (!empty($data["chat_file"])) { ?>
    <div class="file-preview">
        <?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $data["chat_file"])) { ?>
            <!-- Clickable image -->
            <a href="../Assets/Files/Chat/<?php echo $data["chat_file"] ?>" target="_blank">
                <img src="../Assets/Files/Chat/<?php echo $data["chat_file"]?>" alt="Attachment">
            </a>
       <?php } elseif (preg_match('/\.(mp4|webm|ogg)$/i', $data["chat_file"])) { ?>
    <!-- Playable video -->
    <?php $ext = pathinfo($data["chat_file"], PATHINFO_EXTENSION); ?>
    <video controls style="max-width:200px; border-radius:8px;">
        <source src="../Assets/Files/Chat/<?php echo htmlspecialchars($data["chat_file"]) ?>" type="video/<?php echo strtolower($ext) ?>">
        Your browser does not support the video tag.
    </video>
<?php } 
 else { ?>
            <!-- Other files as download -->
            <a href="../Assets/Files/Chat/<?php echo $data["chat_file"] ?>" target="_blank">Download File</a>
        <?php } ?>
    </div>
<?php }


        // ✅ CASE 4: Normal Text
        if (!empty($data["chat_content"]) && strpos($data["chat_content"], "SHARED_POST:") !== 0) { ?>
            <div class="message-content"><?php echo htmlspecialchars($data["chat_content"]) ?></div>
        <?php } ?>

        <div class="message-time"><?php echo date('h:i A', strtotime($data["chat_datetime"])) ?></div>
        <?php if ($isSent) { ?>
            <span class="delete-btn" onclick="deleteMessage(<?php echo $data['chat_id'] ?>)">
                <i class="fas fa-trash"></i>
            </span>
        <?php } ?>
    </div>
    <?php
}
?>
