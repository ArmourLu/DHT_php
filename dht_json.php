<?php
include "/var/www_private/mysql_conn.php";

$start = microtime(true);

$max_query_count = 60 * 60 * 24 * 7; // 7 days

//Get count
$query_count = $_GET['c'];
if($query_count=='' || !is_numeric($query_count)) $query_count=1;
if($query_count>$max_query_count) $query_count = $max_query_count;

//Get float format
$query_ft = $_GET['ft'];
if($query_ft=='' || !is_numeric($query_ft)) $query_ft=0;
if($ft > 1) $ft = 1;

//Get interval
$query_interval = $_GET['i'];
if($query_interval=='' || !is_numeric($query_interval)) $query_interval=1;

//Get sensor#
$query_sensor = $_GET['s'];
if(strtolower($query_sensor)=='all')
{
	$query_sensor = 'all';
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
    /*
    $sqlstr = "select ID from dht where datetime <= '$query_end_datetime' order by id desc limit 0,1";
    $result = mysql_query($sqlstr);
    $row = mysql_fetch_row($result);
    $query_end = $row[0];
    
    $sqlstr = "select ID from dht where datetime >= '$query_from_datetime' limit 0,1";
    $result = mysql_query($sqlstr);
    $row = mysql_fetch_row($result);
    $query_from = $row[0];
    */
}

$query_fields = "ID, Reading, UNIX_TIMESTAMP(DateTime), GroupID";

// Query by datetime
if($query_end_datetime != ""){
    if($query_interval==1)
    {
	   //$sqlstr = "select $query_fields from dht where id <= $query_end and id >= $query_from order by id desc";
       $sqlstr = "select $query_fields from dht where datetime <= '$query_end_datetime' and  datetime >= '$query_from_datetime' order by id desc";
    }
    else
    {
        //$sqlstr = "select $query_fields from dht where id <= $query_end and id >= $query_from and UNIX_TIMESTAMP(DateTime)%$query_interval=0 order by id desc";
        $sqlstr = "select $query_fields from dht where datetime <= '$query_end_datetime' and  datetime >= '$query_from_datetime' and UNIX_TIMESTAMP(DateTime)%$query_interval=0 order by id desc";
    }
}
else{
    if($query_interval==1)
    {
	   $sqlstr = "select $query_fields from dht order by id desc limit 0,$query_count";
    }
    else
    {
        $query_count = $query_count / $query_interval;
        $sqlstr = "select $query_fields from dht where UNIX_TIMESTAMP(DateTime)%$query_interval=0 order by id desc limit 0,$query_count";
    }
}
$returnjson['PrepareTime'] = number_format((microtime(true) - $start), 2) . "s";
$start = microtime(true);
//Execute SQL
//$returnjson['SQL'] = $sqlstr;
$result = mysql_query($sqlstr);
$returnjson['SQLTime'] = number_format((microtime(true) - $start), 2) . "s";
$DhtArray = array();

// Loop for fetching date from mysql
$LastID = 0;
$LastDate = 0;
$DhtArrayCount = 0;
$rowindex = 0;

$PreDate = 0;
$PreGroup = 0;
$MissCount = 0;
$rowindex = 0;

$start = microtime(true);
while ($row = mysql_fetch_assoc($result)) {
	if($DhtArrayCount>=$query_count) break;
    if($PreDate==$row['UNIX_TIMESTAMP(DateTime)']) continue;
    
    $readings = explode(',',$row['Reading']);
    if(count($readings) != $sensor_num*2) continue;
    $rowindex++;

    if($DhtArrayCount == 0)
    {
        $readings_count = count($readings)/2;
        $LastID = (int)$row['ID'];
        $LastDate = $row['UNIX_TIMESTAMP(DateTime)'];
    }

    if($DhtArrayCount>0 && $PreDate > $row['UNIX_TIMESTAMP(DateTime)']+$query_interval )
    {
        $MissingSecond = ($PreDate - ($row['UNIX_TIMESTAMP(DateTime)']+$query_interval))/$query_interval;
        $MissCount += $MissingSecond;
    }
    else $MissingSecond=0;

	if(is_numeric($query_sensor)){
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
	else
	{
        $tmpArray = array();
		for($i=0 ; $i < $readings_count ; $i++){
            $tmpArray[$i] = [(float)$readings[$i*2],(float)$readings[$i*2+1]];
		}
	}

    if($MissingSecond>0)
    {
        $pendingArray = $tmpArray;
        
        if($PreGroup != $row['GroupID'])
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

    $PreDate = $row['UNIX_TIMESTAMP(DateTime)'];
    $PreGroup = $row['GroupID'];
}

$returnjson['ParseTime'] = number_format((microtime(true) - $start), 2) . "s";
$returnjson['Status'] = "OK";
$returnjson['LastID'] = $LastID;
$returnjson['LastDate'] = date("Y-m-d H:i:s",$LastDate);
$returnjson['Count'] = count($DhtArray);
$returnjson['Interval'] = (int)$query_interval;
$returnjson['SensorCount'] = count($DhtArray[0]);
//$returnjson['Group'] = $query_group;
$returnjson['Sensor'] = $query_sensor;
$returnjson['MissCount'] = $MissCount;
$returnjson['Data'] = $DhtArray;
echo json_encode($returnjson);

//$con->close();
?>