<?php
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60) return $diff . " seconds ago";
    $minutes = round($diff / 60);
    if ($minutes < 60) return $minutes . " minutes ago";
    $hours = round($diff / 3600);
    if ($hours < 24) return $hours . " hours ago";
    $days = round($diff / 86400);
    if ($days < 7) return $days . " days ago";
    $weeks = round($diff / 604800);
    if ($weeks < 4) return $weeks . " weeks ago";
    $months = round($diff / 2629746);
    if ($months < 12) return $months . " months ago";
    $years = round($diff / 31556952);
    return $years . " years ago";
}
?>
