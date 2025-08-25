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

        <!-- Show file if exists -->
        <?php if ($data["groupchat_file"]) { ?>
            <div class="file-preview">
                <?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $data["groupchat_file"])) { ?>
                    <img src="../Assets/Files/Chat/<?php echo $data["groupchat_file"]?>" alt="Attachment">
                <?php } else { ?>
                    <a href="../Assets/Files/Chat/<?php echo $data["groupchat_file"] ?>" target="_blank">Download File</a>
                <?php } ?>
            </div>
        <?php } ?>

        <!-- Show message content and time -->
        <div class="message-content"><?php echo htmlspecialchars($data["groupchat_content"]) ?></div>
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