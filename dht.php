<?
include "/var/www_private/mysql_conn.php";

//Get count
$sensor_num = $_GET['s'];
if($sensor_num=='' || !is_numeric($sensor_num)) $sensor_num=3;

//Get command
$cmd = strtolower($_GET['cmd']);

//Get key
$key = strtolower($_GET['key']);

//Get id
$id = strtolower($_GET['id']);

if (!is_numeric($id) || $key=='' ||$cmd != 'verify')
{
    $key = '';
    $id = '';
    $cmd = '';
}

$sqlstr = "SELECT Value FROM sysinfo where Name='ServiceName'";
$result = mysql_query($sqlstr);
$row = mysql_fetch_row($result);
$str_ServiceName = $row[0];

$sqlstr = "SELECT Value FROM sysinfo where Name='ServiceVer'";
$result = mysql_query($sqlstr);
$row = mysql_fetch_row($result);
$str_ServiceVer = $row[0];
    
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<title><?=$str_ServiceName?></title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="/bootstrap/css/bootstrap.min.css">
<link href='https://fonts.googleapis.com/css?family=Ubuntu' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Roboto:500' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Questrial' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="/ui/jquery-ui-themes-1.11.4/themes/start/jquery-ui.css">
<link rel="stylesheet" href="/HoldOn.js/css/HoldOn.css">

<script src="/js/jquery.js"></script>
<script src="/bootstrap/js/bootstrap.min.js"></script>
<script src="/ui/jquery-ui-1.11.4/jquery-ui.js"></script>
<script src="/js/jquery.nicescroll.js"></script>
<script src="/sweetalert/sweetalert.min.js"></script>
<link rel="stylesheet" type="text/css" href="/sweetalert/sweetalert.css">
<script src="/HoldOn.js/js/HoldOn.js"></script>
<script type="text/javascript">
function UpdateCurrentData(){
	$.getJSON("dht_json.php?s=all",function(dhtJSON){
		if(dhtJSON["Status"] == "OK" && dhtJSON["Sensor"] == "all"){
			var LastDate = dhtJSON["LastDate"];
			if(dhtJSON["Count"]>=1){
				time = new Date(LastDate*1000).toString();
				for(i=0;i<<?=$sensor_num?>;i++){
					t = dhtJSON["Data"][i][0];
					h = dhtJSON["Data"][i][1];
					$("#currentdata_t"+i).html(t);
					$("#currentdata_h"+i).html(h);
				}
				$("#currenttime").html(time);
				}
			}
		}
	);
};
$(document).ready(function () {
    UpdateCurrentData();
    setInterval(UpdateCurrentData,1000);
    $( document ).tooltip({track: true});
    $("html").niceScroll();
    $("#alertsubmit").click(function(){
        if($("#alertemail").val()=="") return;
        HoldOn.open({
            theme:"sk-bounce",
            message: "<h1> Please wait </h1>",
            content:"",
            backgroundColor:"black",
            textColor:"white"
        });
        $("input").prop('disabled',true);
        $("button").prop('disabled',true);
        $.getJSON("dht_alert.php?cmd=add&email="+$("#alertemail").val(),function(alertresult){
            if(swal){
                swal(alertresult["Status"].toUpperCase(), alertresult["Comment"], alertresult["Status"].toLowerCase());
            }
            else
            {
                alert(alertresult["Status"].toUpperCase() + ": " + alertresult["Comment"]);
            }
            $("input").prop('disabled',false);
            $("button").prop('disabled',false);
            HoldOn.close();
        });
    });
    $("#alertclear").click(function(){
        $("#alertemail").val('');
    });
<? if($cmd != ''){ ?>
    $.getJSON("dht_alert.php?cmd=<?=$cmd?>&id=<?=$id?>&key=<?=$key?>",function(alertresult){
        if(swal){
            swal(alertresult["Status"].toUpperCase(), alertresult["Comment"], alertresult["Status"].toLowerCase());
        }
        else
        {
            alert(alertresult["Status"].toUpperCase() + ": " + alertresult["Comment"]);
        }
        if(history.pushState){
            history.pushState('','',location.href.split('?')[0]);
        }
    });
<? } ?>
});
</script>
<script>
</script>
</head>
<body>
<div class="container">
    <div class="block-header smallinfotext">
        <div class="row">
            <div class="col-lg-2">
                <img src="/image/inventec/inventec_small.png" style="margin:3px; margin-left:10px;">
            </div>
            <div id="currenttime" title="Update Time" class="col-lg-8 text-center"></div>
        </div>
    </div>
    <div class="headertext text-center" title="by Armour Lu, Software Dept. II"><?=$str_ServiceName?></div>
<? for($i=0;$i < $sensor_num;$i++){ ?>
        <div class="block-data text-center infotext">
            <span><?="#".$i?></span>
            <span>&nbsp;</span>
            <span id="currentdata_t<?=$i?>" class="currentdata"></span>
            <span>&deg;C</span>
            <span>&nbsp;&nbsp;</span>
            <span id="currentdata_h<?=$i?>" class="currentdata"></span>
            <span>%</span>
        </div>
<? }; ?>
            <div class="headertext text-center" title="When Pi2/Remote Temperature Monitoring System is online, we will send you a notice.">Alert Me!</div>
            <div id="alterme" class="block-info">
                <div class="row">
                    <div class="col-lg-4"></div>
                    <div class="col-lg-4">
                        <input id="alertemail" type="email" class="form-control input-lg" id="email" placeholder="Email Address">
                    </div>
                    <div class="col-lg-4">
                        <button id="alertsubmit" class="btn btn-primary btn-lg">Submit</button>
                        <button id="alertclear" class="btn btn-default btn-lg">Clear</button>
                    </div>
                </div>
            </div>
            <br>
    </div>
</body>
</html>