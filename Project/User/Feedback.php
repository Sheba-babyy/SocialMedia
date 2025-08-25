<?php
session_start();
include '../Assets/Connection/Connection.php';
include '../Assets/Functions/timeAgo.php';
include 'Header.php';

// Handle new feedback
if (isset($_POST['btn_submit'])) {
    $rating = (int) $_POST['rating_value'];
    $content = mysqli_real_escape_string($con, $_POST['txt_feedback']);
    if ($rating > 0 && $content != "") {
        $uid = $_SESSION['uid'];
        $sql = "INSERT INTO tbl_feedback (feedback_content, rating_value, feedback_date, user_id)
                VALUES ('$content', '$rating', CURDATE(), '$uid')";
        $con->query($sql);
    }
}
// Fetch average rating & counts
$avgSql = "SELECT ROUND(AVG(rating_value),1) as avg_rating, COUNT(*) as total FROM tbl_feedback";
$avgRes = $con->query($avgSql)->fetch_assoc();
$avgRating = $avgRes['avg_rating'] ?? 0;
$totalReviews = $avgRes['total'] ?? 0;

// Get rating distribution
$ratingDist = [];
for ($i=5; $i>=1; $i--) {
    $countRes = $con->query("SELECT COUNT(*) as c FROM tbl_feedback WHERE rating_value=$i")->fetch_assoc();
    $ratingDist[$i] = $countRes['c'];
}

// Get feedback list
$feedbacks = $con->query("
    SELECT f.*, u.user_name,u.user_photo,
    (SELECT COUNT(*) FROM tbl_feedback_likes l WHERE l.feedback_id=f.feedback_id) AS like_count,
    (SELECT COUNT(*) FROM tbl_feedback_comments c WHERE c.feedback_id=f.feedback_id) AS comment_count,
    (SELECT COUNT(*) FROM tbl_feedback_likes l WHERE l.feedback_id=f.feedback_id AND user_id='{$_SESSION['uid']}') AS user_liked
    FROM tbl_feedback f
    INNER JOIN tbl_user u ON f.user_id=u.user_id
    ORDER BY f.feedback_date DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Feedback</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
:root {
    --bg-dark: #121212;
    --bg-card: #1e1e1e;
    --bg-hover: #2a2a2a;
    --primary-color: #E53935;
    --primary-hover: #E539351;
    --accent-color: #2196F3;
    --accent-hover: #0b7dda;
    --danger-color: #f44336;
    --danger-hover: #d32f2f;
    --text-primary: #e0e0e0;
    --text-secondary: #b0b0b0;
    --border-color: #333333;
    --border-radius: 8px;
    --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    --input-bg: #2d2d2d;
    --star-color: #FFC107;
    --star-inactive: #555;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

body {
    background-color: var(--bg-dark);
    color: var(--text-primary);
    line-height: 1.6;
    padding: 20px;
}

.container {
    max-width: 800px;
    margin: 100px auto;
}

.heading {
    color: white;
    font-size: 28px;
    font-weight: 600;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card {
    background-color: var(--bg-card);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 25px;
    margin-bottom: 30px;
    border: 1px solid var(--border-color);
}

.rating-summary {
    display: flex;
    align-items: center;
    gap: 30px;
    margin-bottom: 20px;
}

.avg-rating {
    font-size: 48px;
    font-weight: bold;
    color: var(--text-primary);
}

.rating-stars {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.star {
    color: var(--star-inactive);
    cursor: pointer;
    font-size: 24px;
    transition: all 0.2s;
}

.star.active {
    color: var(--star-color);
}

.rating-bar-container {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}

.rating-bar-label {
    width: 30px;
    text-align: right;
}

.rating-bar {
    flex-grow: 1;
    background: var(--border-color);
    height: 8px;
    border-radius: 4px;
    overflow: hidden;
}

.rating-bar-fill {
    background: var(--star-color);
    height: 100%;
}

.feedback-form {
    margin-top: 20px;
}

.form-input {
    width: 100%;
    padding: 12px;
    border-radius: var(--border-radius);
    border: 1px solid var(--border-color);
    background-color: var(--input-bg);
    color: var(--text-primary);
    font-size: 16px;
    margin-bottom: 16px;
    resize: none;
}

.form-submit {
    background-color: var(--primary-color);
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: var(--border-radius);
    cursor: pointer;
    font-size: 16px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.form-submit:hover {
    background-color: var(--primary-hover);
    transform: translateY(-2px);
}

.review-list {
    margin-top: 30px;
}

.review-item {
    border-bottom: 1px solid var(--border-color);
    padding: 20px 0;
}

.review-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.user-photo {
    width:40px;
    height:40px;
    border-radius:50%;
    object-fit:cover;
    margin-right:8px
}

.user-name {
    font-weight: 500;
    color: var(--text-primary);
}

.review-meta {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-top: 5px;
}

.review-date {
    color: var(--text-secondary);
    font-size: 14px;
}

.review-content {
    margin: 10px 0;
    color: var(--text-primary);
}

.review-actions {
    display: flex;
    gap: 15px;
    margin-top: 10px;
}

.action-btn {
    background: none;
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: color 0.2s;
}

.action-btn:hover {
    color: var(--text-primary);
}

.action-btn.liked {
    color: var(--primary-color);
}

.comment-box {
    margin-top: 15px;
    padding-left: 20px;
    border-left: 2px solid var(--border-color);
    display: none;
}

.comment-list {
    margin-bottom: 15px;
}

.comment-item {
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border-color);
}

.comment-author {
    font-weight: 500;
    color: var(--text-primary);
    margin-bottom: 3px;
}

.comment-text {
    color: var(--text-primary);
    margin-bottom: 3px;
}

.comment-date {
    color: var(--text-secondary);
    font-size: 12px;
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: var(--text-secondary);
}

.empty-icon {
    font-size: 50px;
    margin-bottom: 20px;
    color: var(--border-color);
}
</style>
</head>
<body>
<div class="container">
    <h1 class="heading"><i class="fas fa-comment-alt"></i> Feedback & Reviews</h1>
    
    <div class="card">
        <!-- Rating Summary -->
        <div class="rating-summary">
            <div class="avg-rating"><?php echo $avgRating; ?><span style="font-size: 24px; color: var(--text-secondary);">/5</span></div>
            <div class="rating-stars">
                <div>
                    <?php for ($i=1; $i<=5; $i++): ?>
                        <span class="star <?php echo ($i <= round($avgRating)) ? 'active' : ''; ?>">★</span>
                    <?php endfor; ?>
                </div>
                <div style="color: var(--text-secondary);"><?php echo $totalReviews; ?> reviews</div>
            </div>
        </div>
        
        <!-- Rating Distribution -->
        <?php for ($i=5; $i>=1; $i--): 
            $percent = ($totalReviews > 0) ? ($ratingDist[$i] / $totalReviews) * 100 : 0;
        ?>
        <div class="rating-bar-container">
            <div class="rating-bar-label"><?php echo $i; ?>★</div>
            <div class="rating-bar">
                <div class="rating-bar-fill" style="width: <?php echo $percent; ?>%"></div>
            </div>
            <div style="color: var(--text-secondary); width: 30px; text-align: right;"><?php echo $ratingDist[$i]; ?></div>
        </div>
        <?php endfor; ?>
        
        <!-- Feedback Form -->
        <form method="post" class="feedback-form">
            <input type="hidden" name="rating_value" id="rating_value" value="0">
            <div style="margin-bottom: 10px;">
                <?php for ($i=1; $i<=5; $i++): ?>
                    <span class="star form-star" data-value="<?php echo $i; ?>">★</span>
                <?php endfor; ?>
            </div>
            <textarea class="form-input" name="txt_feedback" rows="4" placeholder="Share your experience..." required></textarea>
            <button type="submit" name="btn_submit" class="form-submit">
                <i class="fas fa-paper-plane"></i> Submit Review
            </button>
        </form>
    </div>
    
    <!-- Feedback List -->
    <div class="card review-list">
        <h2 style="margin-bottom: 20px;"><i class="fas fa-list"></i> User Reviews</h2>
        
        <?php if ($feedbacks->num_rows > 0): ?>
            <?php while ($row = $feedbacks->fetch_assoc()): 
                $initials = substr($row['user_name'], 0, 1);
            ?>
            <div class="review-item" data-id="<?php echo $row['feedback_id']; ?>">
                <div class="review-header">
                    <div class="user-photo">
                    <img src="../Assets/Files/UserDocs/<?php echo htmlspecialchars($row['user_photo']);?>" width="40px" height="40px"></div>
                    <div>
                        <div class="user-name"><?php echo htmlspecialchars($row['user_name']); ?></div>
                        <div style="display: flex; gap: 5px;">
                            <?php for ($i=1; $i<=5; $i++): ?>
                                <span class="star <?php echo ($i <= $row['rating_value']) ? 'active' : ''; ?>">★</span>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
                <div class="review-date">
                        <i class="far fa-clock"></i> <?php echo timeAgo($row['feedback_date']); ?>
                    </div>

                <div class="review-content">
                    <?php echo htmlspecialchars($row['feedback_content']); ?>
                </div>
                
                <div class="review-meta">
                    <div class="review-actions">
                        <button class="action-btn like-btn <?= ($row['user_liked'] > 0) ? 'liked' : '' ?>" 
        onclick="likeFeedback(<?= $row['feedback_id'] ?>)">
    <i class="fas fa-thumbs-up"></i> 
    <span id="like-count-<?= $row['feedback_id'] ?>"><?= $row['like_count'] ?></span>
</button>


                        <button class="action-btn view-comments">
                            <i class="fas fa-comment"></i> 
                            <span id="comment-count-<?= $row['feedback_id'] ?>"><?= $row['comment_count'] ?></span>
                        </button>
                    </div>
                </div>
                
                <!-- Comments Section -->
                <div class="comment-box">
                    <div id="comments-list-<?= $row['feedback_id'] ?>"></div>
                    <input id="comment-input-<?= $row['feedback_id'] ?>" type="text" placeholder="Write a comment...">
                    <button onclick="addComment(<?= $row['feedback_id'] ?>)">Post</button>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-comment-slash empty-icon"></i>
                <h3>No Reviews Yet</h3>
                <p>Be the first to share your feedback!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// ⭐ Rating
document.querySelectorAll('.form-star').forEach(star => {
    star.onclick = () => {
        const value = star.dataset.value;
        document.getElementById('rating_value').value = value;
        document.querySelectorAll('.form-star').forEach((s, i) =>
            s.classList.toggle('active', i < value)
        );
    };
});

// 👍 Like Feedback (SIMPLE)
function likeFeedback(fid) {
    fetch('../Assets/AjaxPages/FeedbackLike.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'feedback_id=' + fid
    })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'ok') {
            document.getElementById("like-count-" + fid).textContent = d.like_count;

            // toggle "liked" style
            let btn = document.querySelector(`[data-id="${fid}"] .like-btn`);
            if (btn) {
                if (d.user_liked) {
                    btn.classList.add("liked");
                } else {
                    btn.classList.remove("liked");
                }
            }
        } else {
            alert(d.message || "Something went wrong!");
        }
    })
    .catch(err => console.error(err));
}

// 💬 Toggle Comments
document.querySelectorAll('.view-comments').forEach(btn => {
    btn.onclick = () => {
        const box = btn.closest('.review-item').querySelector('.comment-box');
        box.style.display = (box.style.display === 'block') ? 'none' : 'block';
        if (box.style.display === 'block') loadComments(btn.closest('.review-item').dataset.id);
    };
});

// 🔄 Load Comments
function loadComments(fid) {
    fetch(`../Assets/AjaxPages/FeedbackComment.php?action=get&feedback_id=${fid}`)
    .then(r => r.json())
    .then(d => {
        const list = document.getElementById(`comments-list-${fid}`);
        list.innerHTML = d.comments.map(c => `
            <div class="comment-item">
                <img src="../Assets/Files/UserDocs/${c.user_photo}" width="30" height="30">
                <div>
                    <div class="comment-author">${c.user_name}</div>
                    <div class="comment-text">${c.comment_text}</div>
                    <div class="comment-date">${c.comment_date}</div>
                </div>
            </div>
        `).join('');
    });
}

// ➕ Add Comment
function addComment(fid) {
    const input = document.getElementById(`comment-input-${fid}`);
    if (!input.value.trim()) return alert('Please enter a comment');

    fetch('../Assets/AjaxPages/FeedbackComment.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=add&feedback_id=' + fid + '&comment_text=' + encodeURIComponent(input.value.trim())
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            input.value = '';
            loadComments(fid);
            document.getElementById(`comment-count-${fid}`).textContent = d.count;
        } else {
            alert(d.message || 'Failed to add comment');
        }
    });
}
</script>

</body>
</html>