<?php
session_start(); // Make sure session is started at the top
include '../Assets/Connection/Connection.php';
$sql = "SELECT * 
        FROM tbl_post 
        INNER JOIN tbl_user ON tbl_post.user_id = tbl_user.user_id";
$result = $con->query($sql);

if(isset($_GET['pid']))
{
	
	$insQry="insert into tbl_like(user_id,post_id,like_datetime) values('".$_SESSION['uid']."','".$_GET['pid']."',NOW())";
	if($con->query($insQry))
	{
		?>
        <script>
		alert("Liked");
		window.location="ViewPost.php";
		</script>
		<?php
	}
}
if(isset($_GET['did']))
{
	
	$delQry="delete from tbl_like where user_id='".$_SESSION['uid']."' and post_id='".$_GET['did']."'";
	if($con->query($delQry))
	{
		?>
        <script>
		alert("Disliked");
		window.location="ViewPost.php";
		</script>
		<?php
	}
}

if(isset($_GET['cdid']))
{
	$delQry="delete from tbl_comment where comment_id='".$_GET['cdid']."'";
	if($con->query($delQry))
	{
		?>
        <script>
		alert("Comment Deleted");
		window.location="Comment.php?cid=<?php echo $_GET['cid']?>";
		</script>
        <?php
	}
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>View Post</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
<table border="1" align="center">
<?php
if ($result->num_rows > 0)
{
	$i=1;
    while ($row = $result->fetch_assoc()) {
        $file = $row["post_photo"];
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $file_path = '../Assets/Files/PostDocs/' . $file;
        $post_id = $row['post_id'];
        $user_id = $_SESSION['uid'];

    
?>

  <tr>
    <td><?php echo $i++ ?></td>
    <td><img src="../Assets/Files/UserDocs/<?php echo $row["user_photo"]; ?>" 
                     alt="User Photo" width="100" height="100">
    </td>
    <td><h2>@<?php echo $row["user_name"]; ?></h2>
      <h3><?php echo $row["post_date"]; ?></h3></td>
    <td><?php echo $row["post_caption"]; ?></td>
    <td><?php if(in_array($extension,['jpg', 'jpeg', 'png', 'gif', 'webp'])){ ?>
      <img src="<?php echo $file_path; ?>" alt="Post Image" 
                        
                         data-post-id="<?php echo $post_id; ?>" width="100" height="100">
      <?php 
			} else if(in_array($extension, ['mp4', 'webm', 'ogg']))
			 { 
			?>
      <video class="post-media" controls width="100" height="100">
        <source src="<?php echo $file_path; ?>" type="video/<?php echo $extension; ?>">
        Your browser does not support the video tag. </video>
      <?php
			 }
			 else {
                echo "<p>Unsupported file type.</p>";
            }
	       ?>
    </td>
           <td>
           <?php
		   $sel = "select count(like_id) from tbl_like where post_id='".$post_id."'";
		   $res = $con->query($sel);
		   $data = $res->fetch_assoc();
		   ?>
           <a href="#" onClick="like(this,<?php echo $post_id ?>)"><i class="fa-regular fa-heart"></i></a> <?php 
		   if($data['count(like_id)'] > 0)
		   {
		   echo $data['count(like_id)'];
		   }
		   ?>  <br />
		   <a href="ViewPost.php?did=<?php echo $post_id ?>">Dislike</a><br />
           <a href="ViewPost.php?vlid=<?php echo $post_id ?>">View who Liked</a><br />
           <a href="Comment.php?cid=<?php echo $post_id ?>">Comment</a>
           </td>
  </tr>
  <?php
	}
  ?>
</table>
<form id="form1" name="form1" method="post" action="">
  <table width="200" border="1">
    <tr>
      <td>Photo</td>
      <td>Name</td>
    </tr>
    <tr>
    <?php
    if(isset($_GET['vlid']))
   {
	$sel = "select * from tbl_like inner join tbl_user on tbl_like.user_id=tbl_user.user_id where type='post' and post_id='".$_GET['vlid']."'";
	$res = $con->query($sel);
	while($data = $res->fetch_assoc())
	{
	  ?>
      <td><img src="<?php echo $data['user_photo']?>" height="100px" width="100px"/></td>
      <td><?php echo $data['user_name']?></td>
    </tr>
    <?php
	}
   }
   ?>
  </table>
</form>
  <?php
}

 else {
?>
</p>
<h2>No active posts available.</h2>
<?php
 }
?>

<form id="form1" name="form1" method="post" action="">
  <table width="200" border="1" align="center">
    <tr>
      <td>User Photo</td>
      <td>User Name</td>
      <td>Date</td>
      <td>Comment</td>
      <td>Action</td>
    </tr>
    <tr>
    <?php
	$selQry="select * from tbl_comment inner join tbl_user on tbl_comment.user_id=tbl_user.user_id";
	$res=$con->query($selQry);
	while($data=$res->fetch_assoc())
	{
		?>
      <td><img src="../Assets/Files/UserDocs/<?php echo $data['user_photo']?>" width="100px" height="100px"/></td>
      <td>@<?php echo $data['user_name'] ?></td>
      <td><?php echo $data['comment_date']?></td>
      <td><?php echo $data['comment_content']?></td>
      <td><a href="Comment.php?cdid=<?php echo $data['comment_id']?>&cid=<?php echo $_GET['cid]']?>">Delete</a></td>
    </tr>
    <?php
	}
	?>
  </table>
</form>

</body>
</html>


<script src="../Assets/JQ/JQuery.js"></script> 


<script>
    function like(anchor, pid) {
    $.ajax({
        url: "../Assets/AjaxPages/AjaxLike.php?pid=" + pid,
        success: function(response) {
            const icon = anchor.querySelector("i");
            icon.classList.remove("fa-regular");
            icon.classList.add("fa-solid", "text-danger");
        }
    });
}

</script>