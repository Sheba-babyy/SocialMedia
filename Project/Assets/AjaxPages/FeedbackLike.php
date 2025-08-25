<?php
include '../Connection/Connection.php';
session_start();

$fid = $_POST['feedback_id'] ?? 0;
$uid = $_SESSION['uid'] ?? 0;

if (!$fid || !$uid) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

// Check if already liked
$chk = $con->query("SELECT * FROM tbl_feedback_likes WHERE feedback_id='$fid' AND user_id='$uid'");
$already_liked = $chk->num_rows > 0;

if ($already_liked) {
    $con->query("DELETE FROM tbl_feedback_likes WHERE feedback_id='$fid' AND user_id='$uid'");
} else {
    $con->query("INSERT INTO tbl_feedback_likes (feedback_id, user_id) VALUES ('$fid','$uid')");
}

// Get updated count
$res = $con->query("SELECT COUNT(*) as cnt FROM tbl_feedback_likes WHERE feedback_id='$fid'");
$row = $res->fetch_assoc();

echo json_encode([
    'status' => 'ok', 
    'like_count' => $row['cnt'],
    'user_liked' => !$already_liked,
    // 'message' => $already_liked ? 'Unliked' : 'Liked'
]);
?>