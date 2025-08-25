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
        <?php if ($data["chat_file"]) { ?>
            <div class="file-preview">
                <?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $data["chat_file"])) { ?>
                    <img src="../Assets/Files/Chat/<?php echo $data["chat_file"]?>" alt="Attachment">
                <?php } else { ?>
                    <a href="../Assets/Files/Chat/<?php echo $data["chat_file"] ?>" target="_blank">Download File</a>
                <?php } ?>
            </div>
        <?php } ?>
        <div class="message-content"><?php echo htmlspecialchars($data["chat_content"]) ?></div>
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