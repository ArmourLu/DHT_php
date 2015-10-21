<?php
include "/var/www_private/mysql_conn.php";

//Get count
$query_count = $_GET['c'];
if($query_count=='' || !is_numeric($query_count)) $query_count=1;

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

//Get end date
$query_end_datetime = $_GET['ed'];
if (date('Y-m-d H:i:s', strtotime($query_end_datetime)) != $query_end_datetime) $query_end_datetime="";

// Query by datetime
if($query_from_datetime != "" && $query_end_datetime !=""){
	$sqlstr = "select * from dht where datetime >= '$query_from_datetime' and datetime <= '$query_end_datetime' order by id desc";
}
else if($query_from_datetime != ""){
	$sqlstr = "select * from dht where datetime >= '$query_from_datetime' order by id";
}
else if($query_end_datetime != ""){
	$sqlstr = "select * from dht where datetime <= '$query_end_datetime' order by id desc limit 0,$query_count";
}
//Query by ID
else if(is_numeric($query_from) && is_numeric($query_end)){
	$sqlstr = "select * from dht where id >= $query_from and id <= $query_end order by id desc";
}
else if(is_numeric($query_from)){
	$sqlstr = "select * from dht where id >= $query_from order by id";
}
else if(is_numeric($query_end)){
	$sqlstr = "select * from dht where id <= $query_end order by id desc limit 0,$query_count";
}
else{
	$sqlstr = "select * from dht order by id desc limit 0,$query_count";
}

//Execute SQL
$result = mysql_query($sqlstr);

$DhtArray = array();

// Loop for fetching date from mysql
$LastID = 0;
$LastDate = 0;
$Count = 0;
if($query_ft == 0)
{
    $t_pattern = "/T:([-\d]*)(\.\d*)?C/U";
    $h_pattern = "/H:([-\d]*)(\.\d*)?%/U";
}
else
{
    $t_pattern = "/T:(.*)C/U";
    $h_pattern = "/H:(.*)%/U";
}
while ($row = mysql_fetch_array($result)) {
	$Count++;
	
	// Get the last ID
	if($LastID < (int)$row[0]) $LastID = (int)$row[0];

	// Get the last date
	if($LastDate < strtotime($row[2])) $LastDate = strtotime($row[2]);
		
	if(is_numeric($query_sensor)){
        $SensorCount = 1;
		// Get temperature
		if(preg_match_all($t_pattern,$row[1],$t)){
			if(count($t[1])<$query_sensor+1) $row_array[0] = 0;
			else $row_array[0] = (float)$t[1][$query_sensor];
		}else{
			$row_array[0] = 0;
		}
		// Get humidity
		if(preg_match_all($h_pattern,$row[1],$h)){
			if(count($h[1])<$query_sensor+1) $row_array[1] = 0;
			else $row_array[1] = (float)$h[1][$query_sensor];
		}else{
			$row_array[1] = 0;
		}
		array_push($DhtArray, $row_array);
	}
	else
	{
		preg_match_all($t_pattern,$row[1],$t);
		preg_match_all($h_pattern,$row[1],$h);
        $SensorCount = count($t[1]);
		foreach($t[1] as $key => $valus){
			$row_array[0] = (float)$t[1][$key];
			$row_array[1] = (float)$h[1][$key];
			array_push($DhtArray, $row_array);
		}
		
	}
}


$returnjson['Status'] = "OK";
$returnjson['LastID'] = $LastID;
$returnjson['LastDate'] = date('Y-m-d H:i:s', $LastDate);
$returnjson['Count'] = $Count;
$returnjson['SensorCount'] = $SensorCount;
$returnjson['Sensor'] = $query_sensor;
$returnjson['Data'] = $DhtArray;

echo json_encode($returnjson);

//$con->close();
?>