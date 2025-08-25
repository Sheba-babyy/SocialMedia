<?php
include("../Connection/Connection.php");
session_start();

if (!isset($_SESSION['uid']) || !isset($_GET['pid'])) {
    exit("error");
}

$uid = $_SESSION['uid'];
$pid = $_GET['pid'];

// Check if already liked
$check = $con->query("SELECT * FROM tbl_like WHERE post_id='$pid' AND user_id='$uid'");

if ($check->num_rows > 0) {
    // Unlike
    $con->query("DELETE FROM tbl_like WHERE post_id='$pid' AND user_id='$uid'");
    $status = "disliked";
} else {
    // Like
    $con->query("INSERT INTO tbl_like (post_id, user_id) VALUES('$pid', '$uid')");
    $status = "liked";
}

// Updated like count
$countRes = $con->query("SELECT COUNT(*) AS cnt FROM tbl_like WHERE post_id='$pid'");
$count = $countRes->fetch_assoc()['cnt'];

// Return for JS: liked|count OR disliked|count
echo $status . "|" . $count;
?>
