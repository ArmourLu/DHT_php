<?php
$Debug_Table = 0;
$Debug_Time = 0;
$Debug_SQL = 0;

if($Debug_Time) $start = microtime(true);

include "/var/www_private/mysqli_conn.php";

if($Debug_Time) $returnjson['ConnectTime'] = number_format((microtime(true) - $start), 2) . "s";
if($Debug_Time) $start = microtime(true);

$max_query_count = 60 * 60 * 24;

//Get count
$query_count = $_GET['c'];
if($query_count=='' || !is_numeric($query_count)) $query_count=1;

//Get interval
$query_interval = $_GET['i'];
if($query_interval=='' || !is_numeric($query_interval)) $query_interval=1;

//Check query count
if($query_count/$query_interval > $max_query_count) $query_count = $max_query_count * $query_interval;

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
$result = mysqli_query($conni, $sqlstr);
$row = mysqli_fetch_row($result);
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
if($Debug_Time) $returnjson['PrepareTime'] = number_format((microtime(true) - $start), 2) . "s";
if($Debug_Time) $start = microtime(true);
//Execute SQL
$result = mysqli_query($conni, $sqlstr);
if($Debug_Time) $returnjson['SQLTime'] = number_format((microtime(true) - $start), 2) . "s";
$DhtArray = array();

// Loop for fetching date from mysql
$LastID = 0;
$LastDate = 0;
$DhtArrayCount = 0;

$PreDate = 0;
$PreGroup = 0;
$PreData = '';
$MissCount = 0;
$DupCount = 0;

if($Debug_Time) $start = microtime(true);

while ($row = mysqli_fetch_row($result)) {
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

    if($PreData == $row[1])
    {
        $DupCount ++;
    }
    else
    {
        $readings = explode(',',$row[1]);
        if(count($readings) != $sensor_num*2) continue;

        if($query_sensor === 'a')
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

if($Debug_Time) $returnjson['ParseTime'] = number_format((microtime(true) - $start), 2) . "s";
$returnjson['Status'] = "OK";
if($Debug_SQL) $returnjson['SQL'] = $sqlstr;
if($Debug_Table) $returnjson['LastID'] = $LastID;
$returnjson['LastDate'] = date("Y-m-d H:i:s",$LastDate);
$returnjson['Count'] = count($DhtArray);
$returnjson['Interval'] = (int)$query_interval;
$returnjson['SensorCount'] = count($DhtArray[0]);
$returnjson['Sensor'] = $query_sensor;
if($Debug_Table) $returnjson['MissCount'] = $MissCount;
if($Debug_Table) $returnjson['DupCount'] = $DupCount;
$returnjson['Data'] = $DhtArray;
echo json_encode($returnjson);
?>