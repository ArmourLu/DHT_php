<?php
include "/var/www_private/mysql_conn.php";
$is_Debug = 1;

if($is_Debug) $start = microtime(true);

$max_query_count = 60 * 60 * 24 * 7; // 7 days

//Get count
$query_count = $_GET['c'];
if($query_count=='' || !is_numeric($query_count)) $query_count=1;
if($query_count>$max_query_count) $query_count = $max_query_count;

//Get interval
$query_interval = $_GET['i'];
if($query_interval=='' || !is_numeric($query_interval)) $query_interval=1;

//Get sensor#
$query_sensor = $_GET['s'];
if(strtolower($query_sensor)=='a')
{
	$query_sensor = 'a';
}
else
{
	if($query_sensor=='' || !is_numeric($query_sensor)) $query_sensor=0;
}

//Get from date
$query_from_datetime = $_GET['f'];

//Get sensor number
$sqlstr = "SELECT Value FROM sysinfo where Name='SensorCount'";
$result = mysql_query($sqlstr);
$row = mysql_fetch_row($result);
$sensor_num = (int)$row[0];

if (date('Y-m-d H:i:s', strtotime($query_from_datetime))!= $query_from_datetime)
{
    $query_from_datetime="";
}
else
{
    $query_end_datetime = date("Y-m-d H:i:s",strtotime($query_from_datetime) + $query_count - 1);
}

$query_fields = "ID, Reading, UNIX_TIMESTAMP(DateTime), GroupID";

// Query by datetime
if($query_end_datetime != ""){
    if($query_interval==1)
    {
       $sqlstr = "select $query_fields from dht where datetime <= '$query_end_datetime' and  datetime >= '$query_from_datetime' order by id desc";
    }
    else
    {
        $sqlstr = "select $query_fields from dht where datetime <= '$query_end_datetime' and  datetime >= '$query_from_datetime' and UNIX_TIMESTAMP(DateTime)%$query_interval=0 order by id desc";
    }
}
else{
    if($query_interval==1)
    {
	   $sqlstr = "select $query_fields from dht force index (id) order by id desc limit 0,$query_count";
    }
    else
    {
        $query_count = $query_count / $query_interval;
        $sqlstr = "select $query_fields from dht force index (id) where UNIX_TIMESTAMP(DateTime)%$query_interval=0 order by id desc limit 0,$query_count";
    }
}
if($is_Debug) $returnjson['PrepareTime'] = number_format((microtime(true) - $start), 2) . "s";
if($is_Debug) $start = microtime(true);
//Execute SQL
$result = mysql_query($sqlstr);
if($is_Debug) $returnjson['SQLTime'] = number_format((microtime(true) - $start), 2) . "s";
$DhtArray = array();

// Loop for fetching date from mysql
$LastID = 0;
$LastDate = 0;
$DhtArrayCount = 0;

$PreDate = 0;
$PreGroup = 0;
$PreData = '';
$MissCount = 0;

if($is_Debug) $start = microtime(true);
while ($row = mysql_fetch_row($result)) {
	if($DhtArrayCount>=$query_count) break;
    if($PreDate==$row[2]) continue;

    if($DhtArrayCount == 0)
    {
        $readings_count = $sensor_num;
        $LastID = (int)$row[0];
        $LastDate = $row[2];
    }

    if($DhtArrayCount>0 && $PreDate > $row[2]+$query_interval )
    {
        $MissingSecond = ($PreDate - ($row[2]+$query_interval))/$query_interval;
        $MissCount += $MissingSecond;
    }
    else $MissingSecond=0;

    if($PreData != $row[1])
    {
        $readings = explode(',',$row[1]);
        if(count($readings) != $sensor_num*2) continue;

        if($query_sensor=='a')
        {
            $tmpArray = array();
            for($i=0 ; $i < $readings_count ; $i++){
                $tmpArray[$i] = [(float)$readings[$i*2],(float)$readings[$i*2+1]];
            }
        }
        else{
            $tmpArray = array();
            if($readings_count < $query_sensor+1)
            {
                $tmpArray[0] = [0,0];
            }
            else
            {
                $tmpArray[0] = [(float)$readings[$query_sensor*2],(float)$readings[$query_sensor*2+1]];
            }
        }
        $PreData = $row[1];
    }

    if($MissingSecond>0)
    {
        $pendingArray = $tmpArray;
        
        if($PreGroup != $row[3])
        {
            foreach($pendingArray as $key => $value)
            {
                $pendingArray[$key] = [0,0];
            }
        }
        for($i=0;($i<$MissingSecond)&&($DhtArrayCount < $query_count);$i++)
        {
            $DhtArray[$DhtArrayCount] = $pendingArray;
            $DhtArrayCount++;
        }
    }

    $DhtArray[$DhtArrayCount] = $tmpArray;
    $DhtArrayCount++;

    $PreDate = $row[2];
    $PreGroup = $row[3];
}

if($is_Debug) $returnjson['ParseTime'] = number_format((microtime(true) - $start), 2) . "s";
$returnjson['Status'] = "OK";
if($is_Debug) $returnjson['SQL'] = $sqlstr;
if($is_Debug) $returnjson['LastID'] = $LastID;
$returnjson['LastDate'] = date("Y-m-d H:i:s",$LastDate);
$returnjson['Count'] = count($DhtArray);
$returnjson['Interval'] = (int)$query_interval;
$returnjson['SensorCount'] = count($DhtArray[0]);
//$returnjson['Group'] = $query_group;
$returnjson['Sensor'] = $query_sensor;
if($is_Debug) $returnjson['MissCount'] = $MissCount;
$returnjson['Data'] = $DhtArray;
echo json_encode($returnjson);

//$con->close();
?>