<?php
include "/var/www_private/mysql_conn.php";

$start = microtime(true);

$max_query_count = 60 * 60 * 24; // 1 day

//Get count
$query_count = $_GET['c'];
if($query_count=='' || !is_numeric($query_count)) $query_count=1;
if($query_count>$max_query_count) $query_count = $max_query_count;

//Get group
$query_group = $_GET['g'];
if($query_group=='' || !is_numeric($query_group))
{
    $sqlstr = "SELECT ID FROM dht_group order by ID desc";
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

//Get from id
$query_from = $_GET['f'];

//Get end id
$query_end = $_GET['e'];

//Get from date
$query_from_datetime = $_GET['fd'];
if (date('Y-m-d H:i:s', strtotime($query_from_datetime)) != $query_from_datetime) $query_from_datetime="";
else $query_count = $max_query_count;

//Get end date
$query_end_datetime = $_GET['ed'];
if (date('Y-m-d H:i:s', strtotime($query_end_datetime)) != $query_end_datetime) $query_end_datetime="";
else $query_count = $max_query_count;

$query_fields = "ID, replace(replace(replace(replace(Reading,'C,H:',','),'%,T:',','),'T:',''),'%,',''), UNIX_TIMESTAMP(DateTime)";

// Query by datetime
if($query_from_datetime != "" && $query_end_datetime !=""){
	$sqlstr = "select $query_fields from dht where datetime >= '$query_from_datetime' and datetime <= '$query_end_datetime' and GroupID=$query_group order by id desc";
}
else if($query_from_datetime != ""){
	$sqlstr = "select $query_fields from dht where datetime >= '$query_from_datetime' and GroupID=$query_group order by id";
}
else if($query_end_datetime != ""){
	$sqlstr = "select $query_fields from dht where datetime <= '$query_end_datetime' and GroupID=$query_group order by id desc limit 0,$query_count";
}
//Query by ID
else if(is_numeric($query_from) && is_numeric($query_end)){
	$sqlstr = "select $query_fields from dht where id >= $query_from and id <= $query_end and GroupID=$query_group order by id desc";
}
else if(is_numeric($query_from)){
	$sqlstr = "select $query_fields from dht where id >= $query_from and GroupID=$query_group order by id";
}
else if(is_numeric($query_end)){
	$sqlstr = "select $query_fields from dht where id <= $query_end and GroupID=$query_group order by id desc limit 0,$query_count";
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
$PreDate = 0;
$CurrDate = 0;
$MissCount = 0;
$rowindex = 0;
$DhtArrayCount = 0;

while ($row = mysql_fetch_array($result)) {
    $readings = explode(',',$row[1]);
    $readings_count = count($readings)/2;
    
    if($DhtArrayCount == 0)
    {
        $LastID = (int)$row[0];
        $LastDate = $row[2];
    }
	if($DhtArrayCount>=$query_count) break;
    $rowindex++;

    if($DhtArrayCount>0 && $PreDate > $row[2]+1 )
    {
        $MissingSecond = $PreDate - ($row[2]+1);
        $MissCount += $MissingSecond;
    }
    else $MissingSecond=0;
    
    $PreDate = $row[2];
		
	if(is_numeric($query_sensor)){
        $tmpArray = array();
		// Get temperature
        if($readings_count < $query_sensor+1)
        {
            $row_array[0] = 0;
            $row_array[1] = 0;
        }
        else
        {
            $row_array[0] = (float)$readings[$query_sensor*2];
            $row_array[1] = (float)$readings[$query_sensor*2+1];
        }
        $tmpArray[0] = [$row_array[0],$row_array[1]];
	}
	else
	{
        $tmpArray = array();
		for($i=0 ; $i < $readings_count ; $i++){
			$row_array[0] = (float)$readings[$i*2];
			$row_array[1] = (float)$readings[$i*2+1];
            $tmpArray[$i] = [$row_array[0],$row_array[1]];
		}
	}
    
    $DhtArray[$DhtArrayCount] = $tmpArray;
    $DhtArrayCount ++;
    
    for($i=0;($i<$MissingSecond)&&($DhtArrayCount < $query_count);$i++)
    {
        $DhtArray[$DhtArrayCount] = $tmpArray;
        $DhtArrayCount++;
    }
}

$returnjson['TotalTime'] = number_format((microtime(true) - $start), 2) . "s";
$returnjson['Status'] = "OK";
$returnjson['LastID'] = $LastID;
$returnjson['LastDate'] = date("Y-m-d H:i:s",$LastDate);
$returnjson['Count'] = count($DhtArray);
$returnjson['SensorCount'] = count($DhtArray[0]);
$returnjson['Group'] = $query_group;
$returnjson['Sensor'] = $query_sensor;
//$returnjson['MissCount'] = $MissCount;
//$returnjson['rowindex'] = $rowindex;
$returnjson['Data'] = $DhtArray;
echo json_encode($returnjson);

//$con->close();
?>