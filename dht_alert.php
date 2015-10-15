<?php
include "/var/www_private/mysql_conn.php";
include "/var/www_private/email_hash.php";

//Get email
$email = strtolower($_GET['email']);

//Get command
$cmd = strtolower($_GET['cmd']);

//Get key
$key = strtolower($_GET['key']);

//Get id
$id = strtolower($_GET['id']);

if($cmd == "verify")
{
    if (!is_numeric($id)) {
      $status = "error";
      $comment = "Invalid ID.";
    }
    else
    {
        $sqlstr = "select hash,Enabled from useralert where ID='$id'";
        $result = mysql_query($sqlstr);
        $row = mysql_fetch_array($result);
        $hash = $row[0];
        $Enabled = $row[1];
        if($hash == $key)
        {
            if($Enabled == true)
            {
                $status = "success";
                $comment = "Your alert has been activated.";
            }else
            {
                $status = "success";
                $comment = "Your alert is activated.";
                $sqlstr = "update useralert set Enabled=TRUE where ID='$id'";
                $result = mysql_query($sqlstr);
            }
        }
        else
        {
            $status = "error";
            $comment = "Wrong key value. We can't verify your email.";
        }
    }
}
elseif($cmd == "add")
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $status = "error";
      $comment = "Invalid Email Format";
    }
    else
    {
        $sqlstr = "select ID, Email, Enabled, UpdateTime from useralert where LOWER(email)='$email'";
        $result = mysql_query($sqlstr);
        if(mysql_num_rows($result) == 0)
        {
            $hash = email_hash($email);
            $curtime = date("Y-m-d H:i:s");
            $sqlstr = "insert into useralert (Email, Type, Enabled, Hash, CreateTime, UpdateTime) VALUES ('$email', 'boot;', FALSE, '$hash', '$curtime', '$curtime')";
            mysql_query($sqlstr);
            $status = "info";
            $comment = "Check your Email to activate your alert.";
        }
        else
        {
            $row = mysql_fetch_array($result);
            if($row['Enabled'] == true)
            {
                $status = "error";
                $comment = "The Email has already been used.";
            }
            else
            {
                $UpdateTime = new DateTime($row['UpdateTime']);
                $curtime = new DateTime();
                
                if($curtime > $UpdateTime)
                {
                    $status = "info";
                    $comment = "Check your Email to activate your alert.";
                }
                else
                {
                    $status = "warning";
                    $comment = "If you didn't receive an Email to activate your alert, please submit your Email address again after 10 minues.";
                }
            }
        }
    }
}
else
{
    $status = "error";
    $comment = "Invalid Command";
}
//$returnjson['cmd'] = $cmd;
//$returnjson['email'] = $email;
$returnjson['Status'] = $status;
$returnjson['Comment'] = $comment;
//$returnjson['ServerIP'] = $_SERVER['SERVER_ADDR'];
//$returnjson['hash'] = $hash;
//$returnjson['key'] = $key;
$returnjson['UpdateTime'] = $UpdateTime;
$returnjson['curtime'] = $curtime;
echo json_encode($returnjson);
?>