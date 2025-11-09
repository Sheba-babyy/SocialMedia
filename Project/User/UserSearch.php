<?php
include '../Assets/Connection/Connection.php';
session_start();

// logged-in user
$uid = isset($_SESSION['uid']) ? intval($_SESSION['uid']) : 0;

// Fetch friends & groups for share modal
if (isset($_GET['fetch_options']) && $_GET['fetch_options'] == 1) {
    $uid = $_SESSION['uid'];

    // ✅ Fetch only accepted friends
    $friendsRes = $con->query("
    SELECT u.user_id, u.user_name 
    FROM tbl_user u
    INNER JOIN tbl_friends f 
        ON (f.user_from_id = u.user_id OR f.user_to_id = u.user_id)
    WHERE f.friends_status = '1' 
        AND '$uid' IN (f.user_from_id, f.user_to_id)
        AND u.user_id != '$uid'
    ORDER BY u.user_name ASC
");
    $friends = [];
    while ($f = $friendsRes->fetch_assoc()) {
        $friends[] = $f;
    }

    // ✅ Fetch groups where user is a member
    $groupsRes = $con->query("
    SELECT g.group_id, g.group_name
    FROM tbl_group g
    LEFT JOIN tbl_groupmembers gm 
        ON g.group_id = gm.group_id AND gm.user_id = '$uid' AND gm.groupmembers_status = 1
    WHERE g.user_id = '$uid' OR gm.user_id = '$uid'
");

    $groups = [];
    while ($g = $groupsRes->fetch_assoc()) {
        $groups[] = $g;
    }

    echo json_encode(['friends' => $friends, 'groups' => $groups]);
    exit; // stop execution only for this request
}


// Toggle like (POST)
if (isset($_POST['action']) && $_POST['action'] === 'toggle_like') {
    $post_id = intval($_POST['post_id']);
    if ($uid === 0) { echo json_encode(['ok'=>false,'msg'=>'login']); exit; }

    $chk = $con->query("SELECT 1 FROM tbl_like WHERE post_id='$post_id' AND user_id='$uid' LIMIT 1");
    if ($chk && $chk->num_rows) {
        $con->query("DELETE FROM tbl_like WHERE post_id='$post_id' AND user_id='$uid'");
        $liked = false;
    } else {
        $con->query("INSERT INTO tbl_like (post_id, user_id, like_datetime) VALUES ('$post_id','$uid', NOW())");
        $liked = true;
    }
    $count = intval($con->query("SELECT COUNT(*) AS c FROM tbl_like WHERE post_id='$post_id'")->fetch_assoc()['c']);
    echo json_encode(['ok'=>true,'liked'=>$liked,'count'=>$count]);
    exit;
}

// Fetch comments (POST)
if (isset($_POST['action']) && $_POST['action'] === 'fetch_comments') {
    $post_id = intval($_POST['post_id']);
    $res = $con->query("
        SELECT c.comment_text, c.comment_date, u.user_name, u.user_photo
        FROM tbl_comment c
        JOIN tbl_user u ON c.user_id = u.user_id AND u.user_status=1
        WHERE c.post_id = '$post_id'
        ORDER BY c.comment_date DESC
    ");
    $out = [];
    while ($r = $res->fetch_assoc()) {
        $out[] = [
            'name' => $r['user_name'],
            'photo' => $r['user_photo'] ?: 'default.avif',
            'text' => nl2br(htmlspecialchars($r['comment_text'])),
            'date' => $r['comment_date']
        ];
    }
    echo json_encode($out);
    exit;
}

// Add comment (POST)
if (isset($_POST['action']) && $_POST['action'] === 'add_comment') {
    $post_id = intval($_POST['post_id']);
    $text = trim($_POST['text'] ?? '');
    if ($uid === 0) { echo json_encode(['ok'=>false,'msg'=>'login']); exit; }
    if ($text === '') { echo json_encode(['ok'=>false,'msg'=>'empty']); exit; }

    $safe = $con->real_escape_string($text);
    $con->query("INSERT INTO tbl_comment (post_id, user_id, comment_text, comment_date) VALUES ('$post_id','$uid','$safe', NOW())");
    $count = intval($con->query("SELECT COUNT(*) AS c FROM tbl_comment WHERE post_id='$post_id'")->fetch_assoc()['c']);
    echo json_encode(['ok'=>true,'count'=>$count]);
    exit;
}

/* -------------------
   AJAX search (GET q)
   - suggestions (users + groups) when full not set
   - full results when full=1
   ------------------- */
if (isset($_GET['q'])) {
    $qRaw = $_GET['q'];
    $q = $con->real_escape_string($qRaw);
    $full = isset($_GET['full']) && $_GET['full'] == '1';

    // users (limit 5)
    $users = $con->query("SELECT user_id, user_name, user_photo FROM tbl_user WHERE user_status='active'AND user_name LIKE '%$q%' LIMIT 5");

    // groups (limit 5) - check membership (status=1 means approved member)
    $groups = $con->query("
        SELECT g.group_id, g.group_name, g.group_photo,
               IFNULL(gm.groupmembers_status,0) AS is_member
        FROM tbl_group g
        LEFT JOIN tbl_groupmembers gm
          ON g.group_id = gm.group_id AND gm.user_id = '$uid' AND gm.groupmembers_status = 1
        WHERE g.group_name LIKE '%$q%'
        LIMIT 5
    ");

    if (!$full) {
        // suggestions HTML (simple)
        echo "<div class='suggestions'>";
        if ($users->num_rows) {
            while ($u = $users->fetch_assoc()) {
                $name = htmlspecialchars($u['user_name']);
                $photo = htmlspecialchars($u['user_photo'] ?: 'default.avif');
                $uidEsc = (int)$u['user_id'];
                echo "<a class='s-user' href='ViewProfile.php?pid={$uidEsc}'>
                        <img src='../Assets/Files/UserDocs/{$photo}' class='s-photo'>
                        <span class='s-name'>{$name}</span>
                      </a>";
            }
        }
        if ($groups->num_rows) {
            while ($g = $groups->fetch_assoc()) {
                $gname = htmlspecialchars($g['group_name']);
                $gphoto = htmlspecialchars($g['group_photo'] ?: 'default.png');
                $gid = (int)$g['group_id'];
                $link = $g['is_member'] ? "GroupChat.php?id={$gid}" : "Groups.php?id={$gid}";
                echo "<a class='s-group' href='{$link}'>
                        <img src='../Assets/Files/GroupDocs/{$gphoto}' class='s-photo'>
                        <span class='s-name'>{$gname}</span>
                      </a>";
            }
        }
        echo "</div>";
        exit;
    }

    // Full results: users, groups, posts
    echo "<div class='full-results'>";

    // People
    echo "<div class='section'><h3>People</h3>";
    if ($users->num_rows) {
        while ($u = $users->fetch_assoc()) {
            $name = htmlspecialchars($u['user_name']);
            $photo = htmlspecialchars($u['user_photo'] ?: 'default.avif');
            $uidEsc = (int)$u['user_id'];
            echo "<div class='res-user'><img src='../Assets/Files/UserDocs/{$photo}' class='r-photo'><a href='ViewProfile.php?pid={$uidEsc}'>{$name}</a></div>";
        }
    } else echo "<div class='no-results'>No people found</div>";
    echo "</div>";

    // Groups
    echo "<div class='section'><h3>Groups</h3>";
    if ($groups->num_rows) {
        while ($g = $groups->fetch_assoc()) {
            $gname = htmlspecialchars($g['group_name']);
            $gphoto = htmlspecialchars($g['group_photo'] ?: 'default.png');
            $gid = (int)$g['group_id'];
            $is_member = intval($g['is_member']);
            $link = $is_member ? "GroupChat.php?id={$gid}" : "Groups.php?id={$gid}";
            echo "<div class='res-group'><img src='../Assets/Files/GroupDocs/{$gphoto}' class='r-photo'><a href='{$link}'>{$gname}</a></div>";
        }
    } else echo "<div class='no-results'>No groups found</div>";
    echo "</div>";

    // Posts (limit 10)
    $posts = $con->query("
        SELECT p.post_id, p.post_caption, p.post_photo, p.post_date, p.user_id,
               u.user_name, u.user_photo,
               (SELECT COUNT(*) FROM tbl_like WHERE post_id = p.post_id) AS like_count,
               (SELECT COUNT(*) FROM tbl_comment WHERE post_id = p.post_id) AS comment_count,
               EXISTS(SELECT 1 FROM tbl_like WHERE post_id = p.post_id AND user_id = '$uid') AS user_liked
        FROM tbl_post p
        JOIN tbl_user u ON p.user_id = u.user_id AND u.user_status=1
        WHERE p.post_caption LIKE '%$q%'
        ORDER BY p.post_date DESC
        LIMIT 10
    ");

    echo "<div class='section'><h3>Posts</h3>";
    if ($posts->num_rows) {
        while ($p = $posts->fetch_assoc()) {
            $pid = (int)$p['post_id'];
            $author = htmlspecialchars($p['user_name']);
            $authorPhoto = htmlspecialchars($p['user_photo'] ?: 'default.avif');
            $cap = nl2br(htmlspecialchars($p['post_caption']));
            $date = htmlspecialchars(date('M j, Y', strtotime($p['post_date'])));
            $media = htmlspecialchars($p['post_photo']);
            $likes = intval($p['like_count']);
            $comments = intval($p['comment_count']);
            $liked = intval($p['user_liked']) ? true : false;

            // media html
            $mediaHtml = '';
            if ($media !== '') {
                $ext = strtolower(pathinfo($media, PATHINFO_EXTENSION));
                if (in_array($ext, ['mp4','webm','ogg'])) {
                    $mediaHtml = "<video class='post-media' controls><source src='../Assets/Files/PostDocs/{$media}' type='video/{$ext}'>Your browser doesn't support video</video>";
                } else {
                    $mediaHtml = "<img class='post-media' src='../Assets/Files/PostDocs/{$media}'>";
                }
            }

            $likedClass = $liked ? 'liked' : '';

            echo "<div class='post' data-post='{$pid}'>
                    <div class='ph'>
                        <img src='../Assets/Files/UserDocs/{$authorPhoto}' class='r-photo'>
                        <div><strong>{$author}</strong><br><small>{$date}</small></div>
                    </div>
                    <div class='pc'>{$cap}</div>
                    <div class='pm'>{$mediaHtml}</div>
                    <div class='pa'>
                        <button class='like-btn {$likedClass}' data-post='{$pid}'>❤️ <span class='like-count'>{$likes}</span></button>
                        <button class='comment-btn' data-post='{$pid}'>💬 <span class='comment-count'>{$comments}</span></button>
                        <a href='#' class='post-action share-btn' data-post-id='{$pid}' style='text-decoration:none'>↗️</a>
                    </div>
                  </div>";
        }
    } else echo "<div class='no-results'>No posts found</div>";
    echo "</div>";

    echo "</div>"; // full-results
    exit;
}
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Search</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
:root {
    --primary: #E53935;
    --primary-hover: #E53935;
    --danger: #f02849;
    --danger-hover: #d61f3a;
    --success: #42b72a;
    --success-hover: #36a420;
    --dark: #18191a;
    --dark-card: #242526;
    --dark-border: #3e4042;
    --dark-hover: #3a3b3c;
    --text-primary: #e4e6eb;
    --text-secondary: #b0b3b8;
    --card-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    --border-radius: 10px;
    --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

body {
    background-color: var(--dark);
    color: var(--text-primary);
    line-height: 1.6;
    padding: 20px;
}

.container {
    max-width: 760px;
    margin: 0 auto;
}

/* Search Input */
#search {
    width: 100%;
    padding: 14px 20px;
    border-radius: 50px;
    border: none;
    background-color: var(--dark-card);
    color: var(--text-primary);
    font-size: 16px;
    box-shadow: var(--card-shadow);
    transition: var(--transition);
}

#search:focus {
    outline: none;
    box-shadow: 0 0 0 2px var(--primary);
}

#search::placeholder {
    color: var(--text-secondary);
}

#results {
    margin-top: 20px;
}

/* Suggestions & Results */
.suggestions, .section, .full-results {
    background-color: var(--dark-card);
    border-radius: var(--border-radius);
    padding: 12px;
    box-shadow: var(--card-shadow);
    margin-bottom: 15px;
    border: 1px solid var(--dark-border);
}

.section h3 {
    color: var(--text-primary);
    margin-bottom: 15px;
    font-size: 18px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--dark-border);
}

/* User/Group Items */
.s-user, .s-group, .res-user, .res-group {
    display: flex;
    align-items: center;
    padding: 10px;
    text-decoration: none;
    color: var(--text-primary);
    border-radius: var(--border-radius);
    transition: var(--transition);
    margin-bottom: 5px;
}

.s-user:hover, .s-group:hover, .res-user:hover, .res-group:hover {
    background-color: var(--dark-hover);
}
.res-user a, .res-group a
{
    text-decoration:none;
    color:white;
    font-weight:600;
}
.s-photo, .r-photo, .c-photo {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 12px;
    border: 2px solid var(--dark-border);
}

.s-name {
    font-weight: 600;
    color: var(--text-primary);
}

/* Posts */
.post {
    padding: 15px;
    border-radius: var(--border-radius);
    background-color: var(--dark-card);
    margin-bottom: 15px;
    border: 1px solid var(--dark-border);
    transition: var(--transition);
}

.post:hover {
    background-color: var(--dark-hover);
}

.ph {
    display: flex;
    gap: 12px;
    align-items: center;
    margin-bottom: 12px;
}

.post-media {
    max-width: 100%;
    max-height: 400px;
    border-radius: var(--border-radius);
    margin-bottom: 12px;
    display: block;
}

.pc {
    margin-bottom: 12px;
    line-height: 1.5;
}

.pa {
    display: flex;
    gap: 15px;
    align-items: center;
    padding-top: 10px;
    border-top: 1px solid var(--dark-border);
}

/* Buttons */
.like-btn, .comment-btn, .share-btn, #postComment {
    background: none;
    border: none;
    color: var(--text-secondary);
    font-size: 15px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: var(--transition);
    padding: 6px 12px;
    border-radius: var(--border-radius);
}

.like-btn:hover, .comment-btn:hover, .share-btn:hover, #postComment:hover {
    background-color: rgba(255,255,255,0.1);
}

.like-btn.liked {
    color: var(--danger);
}

.like-btn.liked:hover {
    color: var(--danger-hover);
}

.comment-btn:hover {
    color: var(--primary);
}

.share-btn:hover {
    color: var(--success);
}

/* Modal */
#modalOverlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.7);
    display: none;
    z-index: 1000;
    backdrop-filter: blur(5px);
}

#commentModal {
    position: fixed;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    width: 90%;
    max-width: 600px;
    background: var(--dark-card);
    border-radius: var(--border-radius);
    padding: 20px;
    display: none;
    max-height: 85vh;
    overflow: auto;
    z-index: 1001;
    border: 1px solid var(--dark-border);
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);
}

#modalPostArea {
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid var(--dark-border);
}

#modalComments {
    max-height: 50vh;
    overflow-y: auto;
    margin-bottom: 15px;
}

.comment-item {
    display: flex;
    gap: 12px;
    padding: 12px 0;
    border-top: 1px solid var(--dark-border);
}

.comment-item:first-child {
    border-top: none;
}

#newComment {
    width: 100%;
    padding: 12px;
    border-radius: var(--border-radius);
    border: 1px solid var(--dark-border);
    background-color: var(--dark-card);
    color: var(--text-primary);
    resize: none;
    margin-bottom: 12px;
}

#newComment:focus {
    outline: none;
    border-color: var(--primary);
}

#postComment {
    background-color: var(--primary);
    color: white;
    padding: 10px 20px;
    border-radius: var(--border-radius);
    font-weight: 600;
    transition: var(--transition);
}

#postComment:hover {
    background-color: var(--primary-hover);
    transform: translateY(-2px);
}

/* No Results */
.no-results {
    padding: 15px;
    color: var(--text-secondary);
    text-align: center;
}

/* Responsive */
@media (max-width: 768px) {
    .container {
        padding: 0;
    }
    
    #search {
        padding: 12px 16px;
    }
    
    .s-photo, .r-photo, .c-photo {
        width: 40px;
        height: 40px;
    }
    
    #commentModal {
        width: 95%;
        padding: 15px;
    }
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.suggestions, .section, .post, .comment-item {
    animation: fadeIn 0.3s ease-out forwards;
}
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="container">
    <input id="search" placeholder="Search users, groups or posts..." autocomplete="off">
    <div id="results"></div>
</div>

<!-- Comment Modal -->
<div id="modalOverlay"></div>
<div id="commentModal" aria-hidden="true">
    <div id="modalPostArea"></div>
    <div id="modalComments"></div>
    <textarea id="newComment" rows="3" placeholder="Write a comment..."></textarea>
    <div style="text-align:right">
        <button id="postComment">Post Comment</button>
    </div>
</div>

<!-- Share Modal -->
<div id="shareModalOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:1000;"></div>
<div id="shareModal" style="display:none;position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);width:90%;max-width:500px;background:var(--dark-card);padding:20px;border-radius:12px;z-index:1001;border:1px solid var(--dark-border);">
    <h3 style="margin-bottom:15px;">Share Post</h3>
    <div id="shareFriends" style="margin-bottom:15px;">Loading friends...</div>
    <div id="shareGroups" style="margin-bottom:15px;">Loading groups...</div>
    <div style="text-align:right;">
        <button id="btnSharePost" style="background-color:var(--primary);color:#fff;padding:10px 20px;border-radius:12px;">Share</button>
    </div>
</div>


<script>
$(function(){
    let timer = null;
    let lastReq = 0;
    const $input = $('#search');
    const $results = $('#results');

    // suggestions while typing
    $input.on('input', function(){
        const q = $(this).val().trim();
        if (!q) { $results.html(''); return; }
        lastReq++; const req = lastReq;
        $.get('', { q: q }, function(html){
            if (req !== lastReq) return;
            $results.html(html);
        });
    });

    // full search on Enter
    $input.on('keydown', function(e){
        if (e.key === 'Enter') {
            e.preventDefault();
            const q = $(this).val().trim();
            if (!q) return;
            $.get('', { q: q, full: 1 }, function(html){
                $results.html(html);
            });
        }
    });

    // click outside closes suggestions
    $(document).on('click', function(e){
        if (!$(e.target).closest('.container').length) $results.html('');
    });

    // toggle like
    $(document).on('click', '.like-btn', function(e){
        e.stopPropagation();
        const $b = $(this);
        const postId = $b.data('post');
        $b.html('❤️ <span class="like-count">' + $b.find('.like-count').text() + '</span>');
        $.post('', { action: 'toggle_like', post_id: postId }, function(resp){
            try {
                const d = JSON.parse(resp);
                if (!d.ok) { if (d.msg === 'login') alert('Please login to like posts'); return; }
                $b.find('.like-count').text(d.count);
                if (d.liked) $b.addClass('liked'); else $b.removeClass('liked');
            } catch(err){ console.error(err); }
        });
    });

    // open comments modal
    let currentPost = null;
    $(document).on('click', '.comment-btn', function(e){
        e.stopPropagation();
        currentPost = $(this).data('post');
        // show modal
        $('#modalOverlay').fadeIn();
        $('#commentModal').fadeIn();
        $('#modalPostArea').html('');
        $('#modalComments').html('<div class="no-results">Loading comments...</div>');
        // copy post media & caption into modal
        const postCard = $(`.post[data-post='${currentPost}']`);
        const media = postCard.find('.pm').html() || '';
        const caption = postCard.find('.pc').html() || '';
        $('#modalPostArea').html(media + '<div style="margin-top:12px;">' + caption + '</div>');
        // fetch comments
        $.post('', { action: 'fetch_comments', post_id: currentPost }, function(resp){
            try {
                const list = JSON.parse(resp);
                let html = '';
                if (!list.length) html = '<div class="no-results">No comments yet</div>';
                else {
                    list.forEach(function(c){
                        html += `<div class="comment-item"><img class="c-photo" src="../Assets/Files/UserDocs/${c.photo || 'default.avif'}"><div><strong>${c.name}</strong><div style="font-size:13px;color:var(--text-secondary)">${c.date}</div><div style="margin-top:6px">${c.text}</div></div></div>`;
                    });
                }
                $('#modalComments').html(html);
            } catch(err){ console.error(err); $('#modalComments').html('<div class="no-results">Error loading comments</div>'); }
        });
    });

    // post comment
    $('#postComment').on('click', function(){
        const text = $('#newComment').val().trim();
        if (!text) { alert('Please enter a comment'); return; }
        const $btn = $(this);
        $btn.html('Posting...').prop('disabled', true);
        $.post('', { action: 'add_comment', post_id: currentPost, text: text }, function(resp){
            try {
                const d = JSON.parse(resp);
                if (!d.ok) { 
                    if (d.msg === 'login') alert('Please login to comment');
                    else alert('Error posting comment'); 
                    $btn.html('Post Comment').prop('disabled', false);
                    return; 
                }
                // update comment count in results
                $(`.post[data-post='${currentPost}']`).find('.comment-count').text(d.count);
                // reload comments in modal
                $('#newComment').val('');
                $btn.html('Post Comment').prop('disabled', false);
                $.post('', { action: 'fetch_comments', post_id: currentPost }, function(r2){
                    const list = JSON.parse(r2);
                    let html = '';
                    if (!list.length) html = '<div class="no-results">No comments yet</div>';
                    else {
                        list.forEach(function(c){
                            html += `<div class="comment-item"><img class="c-photo" src="../Assets/Files/UserDocs/${c.photo || 'default.avif'}"><div><strong>${c.name}</strong><div style="font-size:13px;color:var(--text-secondary)">${c.date}</div><div style="margin-top:6px">${c.text}</div></div></div>`;
                        });
                    }
                    $('#modalComments').html(html);
                });
            } catch(err){ 
                console.error(err); 
                $btn.html('Post Comment').prop('disabled', false);
            }
        });
    });

    // close modal
    $('#modalOverlay').on('click', function(){
        $('#modalOverlay').fadeOut();
        $('#commentModal').fadeOut();
        $('#modalComments').html('');
        $('#newComment').val('');
    });
});

let sharePostId = null;

// Open share modal
$(document).on('click', '.share-btn', function(e){
    e.stopPropagation();
    sharePostId = $(this).data('post-id');

    $('#shareModalOverlay, #shareModal').fadeIn();
    $('#shareFriends').html('Loading friends...');
    $('#shareGroups').html('Loading groups...');

    // Fetch friends and groups via AJAX
    $.get('UserSearch.php', { fetch_options: 1, post_id: sharePostId }, function(resp){
        try {
            const data = JSON.parse(resp);
            // Friends checkboxes
            if(data.friends.length){
                let htmlF = '<strong>Friends:</strong><br>';
                data.friends.forEach(f => {
                    htmlF += `<label style="display:block;margin:5px 0;"><input type="checkbox" class="shareFriend" value="${f.user_id}"> ${f.user_name}</label>`;
                });
                $('#shareFriends').html(htmlF);
            } else { $('#shareFriends').html('No friends found'); }

            // Groups checkboxes
            if(data.groups.length){
                let htmlG = '<strong>Groups:</strong><br>';
                data.groups.forEach(g => {
                    htmlG += `<label style="display:block;margin:5px 0;"><input type="checkbox" class="shareGroup" value="${g.group_id}"> ${g.group_name}</label>`;
                });
                $('#shareGroups').html(htmlG);
            } else { $('#shareGroups').html('No groups found'); }

        } catch(err){ console.error(err); $('#shareFriends,#shareGroups').html('Error loading options'); }
    });
});

// Share button click
$('#btnSharePost').on('click', function(){
    const selectedFriends = [];
    const selectedGroups = [];

    $('.shareFriend:checked').each(function(){ selectedFriends.push($(this).val()); });
    $('.shareGroup:checked').each(function(){ selectedGroups.push($(this).val()); });

    if(selectedFriends.length === 0 && selectedGroups.length === 0){
        alert('Select at least one friend or group');
        return;
    }

    $.post('SharePost.php', {
        original_post_id: sharePostId,
        friends: selectedFriends,
        groups: selectedGroups
    }, function(resp){
        alert(resp);
        $('#shareModalOverlay, #shareModal').fadeOut();
    });
});

// Close modal
$('#shareModalOverlay').on('click', function(){
    $('#shareModalOverlay, #shareModal').fadeOut();
});


</script>
</body>
</html>