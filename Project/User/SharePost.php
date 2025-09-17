<?php
session_start();
include("../Assets/Connection/Connection.php");

if (!isset($_SESSION['uid'])) {
    echo "error: not logged in";
    exit;
}

$uid = $_SESSION['uid'];

// Get original post ID
if (!isset($_POST['original_post_id'])) {
    echo "error: no post selected";
    exit;
}

$original_post_id = intval($_POST['original_post_id']);

// Fetch original post details
$post_q = $con->prepare("SELECT * FROM tbl_post WHERE post_id=?");
$post_q->bind_param("i", $original_post_id);
$post_q->execute();
$post_result = $post_q->get_result();
if ($post_result->num_rows == 0) {
    echo "error: post not found";
    exit;
}
$post_data = $post_result->fetch_assoc();

// 1️⃣ Share to selected friends
$friends = $_POST['friends'] ?? [];
if (!empty($friends)) {
    $stmt = $con->prepare("INSERT INTO tbl_chat (user_from_id, user_to_id, chat_content, chat_file, chat_datetime) VALUES (?, ?, ?, ?, NOW())");
    foreach ($friends as $friend_id) {
        $friend_id = intval($friend_id);

        $content = "[Shared Post] " . $post_data['post_caption'] .
                   "<br><a href='ViewSharedPost.php?pid=" . $post_data['post_id'] . "' target='_blank'>View Original Post</a>";

        $file = $post_data['post_photo'] ?: '';

        $stmt->bind_param("iiss", $uid, $friend_id, $content, $file);
        $stmt->execute();
    }
    $stmt->close();
}

// 2️⃣ Share to selected groups
$groups = $_POST['groups'] ?? [];
if (!empty($groups)) {
    $stmt = $con->prepare("INSERT INTO tbl_groupchat (user_from_id, group_id, groupchat_content, groupchat_file, groupchat_datetime) VALUES (?, ?, ?, ?, NOW())");
    foreach ($groups as $group_id) {
        $group_id = intval($group_id);

        $content = "[Shared Post] " . $post_data['post_caption'] .
                   "<br><a href='ViewSharedPost.php?pid=" . $post_data['post_id'] . "' target='_blank'>View Original Post</a>";

        $file = $post_data['post_photo'] ?: null;

        $stmt->bind_param("iiss", $uid, $group_id, $content, $file);
        $stmt->execute();
    }
    $stmt->close();
}

echo "Post shared successfully!";
?>
