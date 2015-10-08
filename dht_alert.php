<?php
include "/var/mysql_conn/mysql_conn.php";

//Get email
$email = strtolower($_GET['email']);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  $status = "Error: Invalid Email Format";
}
else
{
	$sqlstr = "select count(*) from useralert where LOWER(email)='$email'";
	$result = mysql_query($sqlstr);
	$row = mysql_fetch_array($result);
	$count = $row[0];
	if($count == 0)
	{
		$sqlstr = "insert into useralert (Email, Type) VALUES ('$email', 'boot;')";
		mysql_query($sqlstr);
		$status = "OK";
	}
	else
	{
		$status = "Error: Duplicate Email Address";
	}
}

$returnjson['Status'] = $status;

echo json_encode($returnjson);
?>