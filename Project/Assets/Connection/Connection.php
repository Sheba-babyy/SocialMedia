<?php

	$servername="localhost";
	$username="root";
	$password="";
	$database="db_miniProject";
    $con=mysqli_connect($servername,$username,$password,$database);
	if(!$con)
	{
		echo "connection failed";
	}

?>