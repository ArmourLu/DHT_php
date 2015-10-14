<?php
include "/var/www_private/mysql_conn.php";
include "/var/www_private/email_hash.php";

//Get email
$email = strtolower($_GET['email']);

//Get command
$cmd = strtolower($_GET['cmd']);

if($cmd == "verify")
{
}
elseif($cmd == "add")
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $status = "Error";
      $comment = "Invalid Email Format";
    }
    else
    {
        $sqlstr = "select count(*) from useralert where LOWER(email)='$email'";
        $result = mysql_query($sqlstr);
        $row = mysql_fetch_array($result);
        $count = $row[0];
        if($count == 0)
        {
            $hash = email_hash($email);
            $sqlstr = "insert into useralert (Email, Type, Enabled, Hash) VALUES ('$email', 'boot;', FALSE, '$hash')";
            //mysql_query($sqlstr);
            $status = "OK";
            $comment = "Check your Email to activate your alert.";
        }
        else
        {
            $status = "Error";
            $comment = "Duplicate Email Address";
        }
    }
}
else
{
    $status = "Error";
    $comment = "Invalid Command";
}
$returnjson['cmd'] = $cmd;
$returnjson['email'] = $email;
$returnjson['Status'] = $status;
$returnjson['Comment'] = $comment;
//$returnjson['ServerIP'] = $_SERVER['SERVER_ADDR'];
//$returnjson['hash'] = $hash;
echo json_encode($returnjson);
?>