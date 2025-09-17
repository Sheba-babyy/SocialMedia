<?php
include '../Assets/Connection/Connection.php';
session_start();

if (!isset($_SESSION['uid'])) {
    header("Location: ../Guest/login.php");
    exit;
}

$uid = $_SESSION['uid'];
$profile_id = intval($_POST['profile_id'] ?? 0);
$friends = $_POST['friends'] ?? [];
$groups = $_POST['groups'] ?? [];

if (empty($friends) && empty($groups)) {
    $redirect_page = ($profile_id == $uid) ? "MyProfile.php?id=$profile_id" : "ViewProfile.php?pid=$profile_id";
    header("Location: $redirect_page&msg=noselection");
    exit;
}

// Build profile share token
$profileToken = "profile_" . $profile_id;

// 🔹 Share to friends (private chat)
foreach ($friends as $friend_id) {
    $friend_id = intval($friend_id);
    $con->query("INSERT INTO tbl_chat 
        (user_from_id, user_to_id, chat_content, chat_file, chat_datetime) 
        VALUES ($uid, $friend_id, '', '$profileToken', NOW())");
}

// 🔹 Share to groups (group chat)
foreach ($groups as $group_id) {
    $group_id = intval($group_id);
    $con->query("INSERT INTO tbl_groupchat 
        (group_id, user_from_id, groupchat_content, groupchat_file, groupchat_datetime) 
        VALUES ('$group_id', '$uid', '', '$profileToken', NOW())");
}

// Redirect back with success
$redirect_page = ($profile_id == $uid) ? "MyProfile.php?id=$profile_id" : "ViewProfile.php?pid=$profile_id";
header("Location: $redirect_page&msg=shared");
exit;
?>