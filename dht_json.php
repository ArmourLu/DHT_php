<?php
include "/var/www_private/mysql_conn.php";

$start = microtime(true);

$max_query_count = 60 * 60 * 24; // 1 day
$query_interval = 1;

//Get count
$query_count = $_GET['c'];
if($query_count=='' || !is_numeric($query_count)) $query_count=1;
if($query_count>$max_query_count) $query_count = $max_query_count;

//Get group
$query_group = $_GET['g'];
if($query_group=='' || !is_numeric($query_group))
{
    $sqlstr = "SELECT ID FROM dht_group order by ID desc limit 0,1";
    $result = mysql_query($sqlstr);
    $row = mysql_fetch_row($result);
    $query_group = $row[0];
}

//Get float format
$query_ft = $_GET['ft'];
if($query_ft=='' || !is_numeric($query_ft)) $query_ft=0;
if($ft > 1) $ft = 1;

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

//Get end date
$query_end_datetime = $_GET['ed'];

if (date('Y-m-d H:i:s', strtotime($query_end_datetime))!= $query_end_datetime)
{
    $query_end_datetime="";
}
else
{
    $query_from_datetime = date("Y-m-d H:i:s",strtotime($query_end_datetime) - $query_count + 1);
    
    $sqlstr = "select ID from dht where datetime <= '$query_end_datetime' and GroupID=$query_group order by id desc limit 0,1";
    $result = mysql_query($sqlstr);
    $row = mysql_fetch_row($result);
    $query_end = $row[0];
    
    $sqlstr = "select ID from dht where datetime >= '$query_from_datetime' and GroupID=$query_group order by id limit 0,1";
    $result = mysql_query($sqlstr);
    $row = mysql_fetch_row($result);
    $query_from = $row[0];
}

$query_fields = "ID, Reading, UNIX_TIMESTAMP(DateTime)";

// Query by datetime
if($query_end_datetime != ""){
	$sqlstr = "select $query_fields from dht where id <= $query_end and id >= $query_from and GroupID=$query_group order by id desc";
}
else{
	$sqlstr = "select $query_fields from dht where GroupID=$query_group order by id desc limit 0,$query_count";
}

//Execute SQL
$result = mysql_query($sqlstr);
$returnjson['SQLTime'] = number_format((microtime(true) - $start), 2) . "s";
$DhtArray = array();

// Loop for fetching date from mysql
$LastID = 0;
$LastDate = 0;
$DhtArrayCount = 0;
$rowindex = 0;

$PreDate = 0;
$MissCount = 0;
$rowindex = 0;

$start = microtime(true);
while ($row = mysql_fetch_row($result)) {
	if($DhtArrayCount>=$query_count) break;
    $rowindex++;

    $readings = explode(',',$row[1]);

    if($DhtArrayCount == 0)
    {
        $readings_count = count($readings)/2;
        $LastID = (int)$row[0];
        $LastDate = $row[2];
    }

    if($DhtArrayCount>0 && $PreDate > $row[2]+1 )
    {
        $MissingSecond = $PreDate - ($row[2]+1);
        $MissCount += $MissingSecond;
    }
    else $MissingSecond=0;
    $PreDate = $row[2];

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
    
    $DhtArray[$DhtArrayCount] = $tmpArray;
    $DhtArrayCount++;

    for($i=0;($i<$MissingSecond)&&($DhtArrayCount < $query_count);$i++)
    {
        $DhtArray[$DhtArrayCount] = $tmpArray;
        $DhtArrayCount++;
    }
}

$returnjson['ParseTime'] = number_format((microtime(true) - $start), 2) . "s";
$returnjson['Status'] = "OK";
$returnjson['LastID'] = $LastID;
$returnjson['LastDate'] = date("Y-m-d H:i:s",$LastDate);
$returnjson['Count'] = count($DhtArray);
$returnjson['SensorCount'] = count($DhtArray[0]);
$returnjson['Group'] = $query_group;
$returnjson['Sensor'] = $query_sensor;
$returnjson['MissCount'] = $MissCount;
$returnjson['Data'] = $DhtArray;
echo json_encode($returnjson);

//$con->close();
?>