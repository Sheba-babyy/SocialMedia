<?php
header('Content-Type: application/json');
session_start();
require '../Connection/Connection.php';
include '../Functions/timeAgo.php';

if (!isset($_SESSION['uid'])) {
    echo json_encode(['success' => false, 'message' => 'Login required']);
    exit;
}

$userId     = (int)$_SESSION['uid'];
$feedbackId = (int)($_REQUEST['feedback_id'] ?? 0);
$action     = $_REQUEST['action'] ?? 'get';

if ($feedbackId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid feedback']);
    exit;
}

// Add new comment
if ($action === 'add') {
    $comment = trim($_POST['comment_text'] ?? '');
    if ($comment === '') {
        echo json_encode(['success' => false, 'message' => 'Empty comment']);
        exit;
    }

    $stmt = $con->prepare("INSERT INTO tbl_feedback_comments (feedback_id, user_id, comment_text, comment_date) 
                           VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $feedbackId, $userId, $comment);
    $stmt->execute();
}

// Fetch comments
$stmt = $con->prepare("SELECT u.user_name, u.user_photo, c.comment_text, c.comment_date
                       FROM tbl_feedback_comments c
                       JOIN tbl_user u ON u.user_id = c.user_id
                       WHERE c.feedback_id = ?
                       ORDER BY c.comment_date DESC");
$stmt->bind_param("i", $feedbackId);
$stmt->execute();
$res = $stmt->get_result();

$comments = [];
while ($row = $res->fetch_assoc()) {
    $comments[] = [
        'user_name'    => $row['user_name'],
        'user_photo'   => $row['user_photo'],
        'comment_text' => $row['comment_text'],
        'comment_date' => timeAgo($row['comment_date'])
    ];
}

// Count comments
$countRes = $con->query("SELECT COUNT(*) AS c FROM tbl_feedback_comments WHERE feedback_id=$feedbackId");
$count = $countRes->fetch_assoc()['c'] ?? 0;

echo json_encode([
    'success'  => true,
    'message'  => ($action === 'add') ? 'Comment added' : 'Comments loaded',
    'comments' => $comments,
    'count'    => $count
]);
