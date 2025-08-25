<?php
include("../Assets/Connection/Connection.php");
$vid="";
if(isset($_POST['btn_register']))
{
	$name=$_POST['txt_vname'];
	$email=$_POST['txt_vemail'];
    $contact=$_POST['txt_vcontact'];
	$disid=$_POST['sel_district'];
	$password=$_POST['txt_vpassword'];
	$hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Hash password
	$id=$_POST['txt_id'];
	
	if($id=="")
	{
	$insQry="insert into tbl_verifier(verifier_name,verifier_email,verifier_contact,verifier_password,district_id) values('".$name."','".$email."','".$contact."','".$hashedPassword."','".$disid."')";
	if($res=$con->query($insQry))
	{
		?>
        <script>
		alert("Verifier added");
		window.location="AddVerifier.php";
		</script>
      <?php
	}
	}
	else
	{
		$upqry="update tbl_verifier set verifier_name='".$name."',verifier_email='".$email."',verifier_contact='".$contact."',verifier_password='".$password."',district_id='".$disid."' where verifier_id='".$id."'";
		if($con->query($upqry))
		{
			?>
			<script>
			alert("Record Updated");
			window.location="AddVerifier.php";
			</script>
			<?php
		}
}
}
if(isset($_GET['did']))
{
	$delQry="delete from tbl_verifier where verifier_id='".$_GET['did']."'";
	if($con->query($delQry))
	{
		?>
        <script>
		alert("Deleted");
		window.location="AddVerifier.php";
		</script>
        <?php
	}
}
$name="";
$email="";
$contact="";
$disid="";
$vid="";
$password="";
if(isset($_GET['eid']))
{
	$sel="select * from tbl_verifier where verifier_id='".$_GET['eid']."'";
	$res=$con->query($sel);
	$data=$res->fetch_assoc();
	
	$name=$data['verifier_name'];
	$email=$data['verifier_email'];
    $contact=$data['verifier_contact'];
	$disid=$data['district_id'];
	$vid=$data['verifier_id'];
	$password=$data['verifier_password'];
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Add Verifier</title>
</head>
<body>
<form id="form1" name="form1" method="post" action="">
  <table width="200" border="1" align="center">
    <tr>
      <td>Name</td>
      <td><label for="txt_vname"></label>
      <input name="txt_id" type="hidden" value="<?php echo $vid ?>"/>
      <input required type="text" name="txt_vname" id="txt_vname" value="<?php echo $name ?>" title="Name Allows Only Alphabets,Spaces and First Letter Must Be Capital Letter" pattern="^[A-Z]+[a-zA-Z ]*$"/></td>
    </tr>
    
    <tr>
      <td>Email</td>
      <td><label for="txt_vemail"></label>
      <input required type="email" name="txt_vemail" id="txt_vemail" value="<?php echo $email ?>"/></td>
    </tr>
    
    <tr>
      <td>Contact</td>
      <td><label for="txt_vcontact"></label>
      <input required type="text" name="txt_vcontact" id="txt_vcontact" value="<?php echo $contact ?>" pattern="[7-9]{1}[0-9]{9}" title="Phone number with 7-9 and remaing 9 digit with 0-9"/></td>
    </tr>
    
    <tr>
      <td>District</td>
      <td><label for="sel_district"></label>
        <select name="sel_district" id="sel_district" required>
          <option>-----Select District-----</option>
          <?php
	  $selQry="select * from tbl_district";
	  $res=$con->query($selQry);
	  while($data=$res->fetch_assoc())
	  {
		?>
          <option <?php 
		  if($disid == $data['district_id'])
		  {
			  echo "selected";
		  }
		  ?> value="<?php echo $data['district_id']?>"> <?php echo $data['district_name']?></option>
        <?php
	  }
	  ?>
      </select></td>
    </tr>
    
    <tr>
      <td>Password</td>
      <td><label for="txt_vpassword"></label>
      <input required type="password" name="txt_vpassword" id="txt_vpassword" value="<?php echo $password ?>" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters"/></td>
    </tr>
    
    <tr>
      <td colspan="2"><div align="center">
        <input type="submit" name="btn_register" id="btn_register" value="Register" />
      </div></td>
    </tr>
  </table>
  <p>&nbsp;</p>
  <table width="200" border="1" align="center">
    <tr>
      <td>SL No</td>
      <td>Name</td>
      <td>Email</td>
      <td>Contact</td>
      <td>District</td>
      <td>Action</td>
    </tr>
    <tr>
    <?php
	$i=1;
	$selQry="select * from tbl_verifier v inner join tbl_district d on v.district_id=d.district_id";
	  $res=$con->query($selQry);
	  while($data=$res->fetch_assoc())
	  {
		?>
      <td><?php echo $i++ ?></td>
      <td><?php echo $data['verifier_name']?></td>
      <td><?php echo $data['verifier_email']?></td>
      <td><?php echo $data['verifier_contact']?></td>
      <td><?php echo $data['district_name']?></td>
      <td><a href="AddVerifier.php?did=<?php echo $data['verifier_id'];?>">Delete</a>
    	<a href="AddVerifier.php?eid=<?php echo $data['verifier_id'];?>">Edit</a></td>
    </tr>
    <?php
	  }
	  ?>
  </table>
</form>

</body>
</html>