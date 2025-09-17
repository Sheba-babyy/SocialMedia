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

        // 🔹 Case 2: File upload (image/doc/etc.)
        elseif (!empty($data["groupchat_file"])) {
            if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $data["groupchat_file"])) {
                echo "<div class='file-preview'>
                        <img src=\"../Assets/Files/Chat/{$data["groupchat_file"]}\" alt=\"Attachment\">
                      </div>";
            } else {
                echo "<div class='file-preview'>
                        <a href=\"../Assets/Files/Chat/{$data["groupchat_file"]}\" target=\"_blank\">Download File</a>
                      </div>";
            }
        } 

        // 🔹 Case 3: Normal Text
        else {
            echo nl2br(htmlspecialchars($data["groupchat_content"]));
        }
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

