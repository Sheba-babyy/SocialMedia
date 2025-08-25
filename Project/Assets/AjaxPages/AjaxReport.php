<?php
include("../../Connection/Connection.php");
session_start();

if (!isset($_SESSION['uid'])) {
    die('unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = mysqli_real_escape_string($con, $_POST['post_id']);
    $user_id = $_SESSION['uid'];
    $reason = mysqli_real_escape_string($con, $_POST['reason']);
    $details = mysqli_real_escape_string($con, $_POST['details']);
    $report_date = date('Y-m-d H:i:s');
    
    // Check if user already reported this post
    $check_sql = "SELECT * FROM tbl_reports WHERE user_id = '$user_id' AND post_id = '$post_id'";
    $check_result = $con->query($check_sql);
    
    if ($check_result->num_rows > 0) {
        echo 'already_reported';
    } else {
        $insert_sql = "INSERT INTO tbl_reports (user_id, post_id, reason, details, report_date, report_status) 
                      VALUES ('$user_id', '$post_id', '$reason', '$details', '$report_date', 'pending')";
        
        if ($con->query($insert_sql)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
} else {
    echo 'invalid_request';
}
?>