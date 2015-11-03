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

$sqlstr = "SELECT Value FROM sysinfo where Name='PythonPath'";
$result = mysql_query($sqlstr);
$row = mysql_fetch_row($result);
$PythonPath = $row[0];

if($cmd == "verify")
{
    if (!is_numeric($id)) {
      $status = "error";
      $comment = "Invalid ID.";
    }
    else
    {
        $sqlstr = "select Email, hash, Enabled from useralert where ID='$id'";
        $result = mysql_query($sqlstr);
        $row = mysql_fetch_array($result);
        $email = $row['Email'];
        $hash = $row['hash'];
        $Enabled = $row['Enabled'];
        if($hash == $key)
        {
            if($Enabled == true)
            {
                $status = "success";
                $comment = "Your alert has been activated.";
            }else
            {
                $status = "success";
                $comment = "Your alert is now activated.";
                $hash = email_hash($email);
                $curtime = date("Y-m-d H:i:s");
                $sqlstr = "update useralert set Enabled=TRUE, UpdateTime='$curtime', hash='$hash' where ID='$id'";
                $result = mysql_query($sqlstr);
                exec("python $PythonPath mail $email remove > /dev/null &");
            }
        }
        else
        {
            $status = "error";
            $comment = "Wrong verification code. We can't verify your email address.";
        }
    }
}
elseif($cmd == "remove")
{
    if (!is_numeric($id)) {
      $status = "error";
      $comment = "Invalid ID.";
    }
    else
    {
        $sqlstr = "select hash, Enabled from useralert where ID='$id'";
        $result = mysql_query($sqlstr);
        $row = mysql_fetch_array($result);
        $hash = $row['hash'];
        $Enabled = $row['Enabled'];
        if($hash == $key)
        {
            if($Enabled == true)
            {
                $status = "success";
                $comment = "Your alert had been removed.";
                $sqlstr = "delete from useralert where ID='$id'";
                $result = mysql_query($sqlstr);
            }else
            {
                $status = "error";
                $comment = "Your alert is not activated.";
            }
        }
        else
        {
            $status = "error";
            $comment = "Wrong verification code. We can't remove your email address.";
        }
    }
}
elseif($cmd == "add")
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $status = "error";
      $comment = "Your email address is invalid.";
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
            $comment = "A confirmation email has been sent to your email address. Please click on the Activation Link to activate your alert.";
            exec("python $PythonPath mail $email verify > /dev/null &");
        }
        else
        {
            $row = mysql_fetch_array($result);
            if($row['Enabled'] == true)
            {
                $status = "error";
                $comment = "The email address has already been used.";
            }
            else
            {
                $UpdateTime = new DateTime($row['UpdateTime']);
                $UpdateTime->modify("+3 minutes");
                $curtime = new DateTime();
                
                if($curtime > $UpdateTime)
                {
                    $status = "info";
                    $comment = "A confirmation email has been resent to your email address. Please click on the Activation Link to activate your alert.";
                    $curtime = date("Y-m-d H:i:s");
                    $id = $row['ID'];
                    $sqlstr = "update useralert set UpdateTime='$curtime' where ID=$id";
                    mysql_query($sqlstr);
                    exec("python $PythonPath mail $email verify > /dev/null &");
                }
                else
                {
                    $status = "warning";
                    $comment = "If you didn't receive a confirmation email, please submit your email address again after 3 minues.";
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
$returnjson['Status'] = $status;
$returnjson['Comment'] = $comment;
echo json_encode($returnjson);
?>