<?
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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<title>Pi2/Remote Temperature Monitoring System</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="/bootstrap/css/bootstrap.min.css">
<link href='https://fonts.googleapis.com/css?family=Ubuntu' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Fjalla+One' rel='stylesheet' type='text/css'>
<script src="/bootstrap/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="/ui/jquery-ui-themes-1.11.4/themes/start/jquery-ui.css">
<script type="text/javascript" src="/js/flot/jquery.js"></script>
<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="/js/flot/excanvas.min.js"></script><![endif]-->
<script type="text/javascript" src="/js/flot/jquery.flot.js"></script>
<script type="text/javascript" src="/js/flot/jquery.flot.time.js"></script>
<script type="text/javascript" src="/js/flot/jquery.flot.axislabels.js"></script>
<script type="text/javascript" src="/js/flot/jquery.flot.symbol.js"></script>
<script src="/ui/jquery-ui-1.11.4/jquery-ui.js"></script>
<script src="/js/jquery.nicescroll.js"></script>
<script src="/sweetalert/sweetalert.min.js"></script>
<link rel="stylesheet" type="text/css" href="/sweetalert/sweetalert.css">
<script type="text/javascript">
function UpdateFlot(){
	if($('input[name=radio1]:checked').val() == -1){
		$("#SensorGraph").hide();
		$("html").getNiceScroll().resize();
		return;
	}
	$.getJSON("dht_json.php?c=300&s="+$('input[name=radio1]:checked').val(),function(dhtJSON){
		$("#SensorGraph").show();
		$("html").getNiceScroll().resize();
		var result_t = [];
		var result_h = [];
		if(dhtJSON["Status"] == "OK"){
			var LastID = dhtJSON["LastID"];
			var LastDate = dhtJSON["LastDate"];
			for(var k in dhtJSON["Data"]){
				var t1, t2;
				time = LastDate - k;
				t = dhtJSON["Data"][k][0];
				h = dhtJSON["Data"][k][1];
				result_t.push([time*1000,t]);
				result_h.push([time*1000,h]);
				}
			
			var dataSet_t = [
				{
					label: "Sensor#" +dhtJSON["Sensor"] + " Temperature",
					data: result_t,
					color: "#ff7700",
					lines: { show: true }
				}];
			var dataSet_h = [
				{
					label: "Sensor#" +dhtJSON["Sensor"] + " Humidity",
					data: result_h,
					color: "#0077FF",
					lines: { show: true }
				}];
			var options_t = {
			    xaxis: {
			        mode: "time",
			        timeformat: "%H:%M",
			        tickSize: [60, "second"],
			        timezone: "browser"
			        //axisLabel: "Time",
			        //axisLabelUseCanvas: true,
			        //axisLabelFontSizePixels: 35,
			        //axisLabelFontFamily: 'Verdana, Arial',
			        //axisLabelPadding: 0
			    },
			    yaxis: {
			    	tickFormatter: function (val, axis) {
			    		return val+"&deg;C"
			    	}
			        //axisLabel: "Degree C",
			        //axisLabelUseCanvas: true,
			        //axisLabelFontSizePixels: 35,
			        //axisLabelFontFamily: 'Verdana, Arial',
			        //axisLabelPadding: 0
			    },
			series: {            
			    lines: {
			        show: true,
			        fill: true
			    },
			    shadowSize: 0
			},
			legend: {
			    labelBoxBorderColor: "#858585",
			    position: "se"
			}
			};
			var options_h = {
			    xaxis: {
			        mode: "time",
			        timeformat: "%H:%M",
			        tickSize: [60, "second"],
			        timezone: "browser"
			        //axisLabel: "Time",
			        //axisLabelUseCanvas: true,
			        //axisLabelFontSizePixels: 35,
			        //axisLabelFontFamily: 'Verdana, Arial',
			        //axisLabelPadding: 0
			    },
			    yaxis: {
			    	tickFormatter: function (val, axis) {
			    		return val+"%"
			    	}
			        //axisLabel: "%",
			        //axisLabelUseCanvas: true,
			        //axisLabelFontSizePixels: 35,
			        //axisLabelFontFamily: 'Verdana, Arial',
			        //axisLabelPadding: 0
			    },
			series: {            
			    lines: {
			        show: true,
			        fill: true
			    },
			    shadowSize: 0
			},
			legend: {
			    labelBoxBorderColor: "#858585",
			    position: "se"
			}
			};
			$.plot($("#flot-placeholder_t"), dataSet_t, options_t);
			$.plot($("#flot-placeholder_h"), dataSet_h, options_h);
			}
		}
	);
};
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
    UpdateFlot();
    setInterval(UpdateFlot,5000);
    UpdateCurrentData();
    setInterval(UpdateCurrentData,1000);
    $('input[name="radio1"]:radio').change(function(){UpdateFlot();});
    $("#sensor_sel").buttonset();
    $( document ).tooltip({track: true});
    $("html").niceScroll();
    $("#alertsubmit").click(function(){
        if($("#alertemail").val()=="") return;
        $("#alertsubmit").attr('disabled',true);
        $("#alertclear").attr('disabled',true);
        $("#alertemail").attr('disabled',true);
        $.getJSON("dht_alert.php?cmd=add&email="+$("#alertemail").val(),function(alertresult){
            if(swal){
                swal(alertresult["Status"].toUpperCase(), alertresult["Comment"], alertresult["Status"].toLowerCase());
            }
            else
            {
                alert(alertresult["Status"].toUpperCase() + ": " + alertresult["Comment"]);
            }
        });
        $("#alertsubmit").attr('disabled',false);
        $("#alertclear").attr('disabled',false);
        $("#alertemail").attr('disabled',false);
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
    <div class="bgcolor2 smallinfotext">
        <div class="row">
            <div class="col-lg-2">
                <img src="/image/inventec/inventec_small.png" style="margin:3px;">
            </div>
            <div id="currenttime" title="Update Time" class="col-lg-8 text-center"></div>
        </div>
    </div>
    <div class="headertext text-center" title="by Armour Lu, Software Dept. II">Pi2/Remote Temperature Monitoring System</div>
<? for($i=0;$i < $sensor_num;$i++){ ?>
        <div class="bgcolor<?=$i%2?> text-center infotext">
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
            <div id="alterme" class="bgcolor0">
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
            <div class="headertext text-center">Sensor Graph</div>
            <div class="bgcolor0 text-center">
                <div id="sensor_sel">
                    <input type="radio" name="radio1" id="radioX" value="-1" checked="true">
                    <label for="radioX">Disable</label>
<? for($i=0;$i < $sensor_num;$i++){ ?>
                    <input type="radio" name="radio1" id="radio<?=$i?>" value="<?=$i?>">
                    <label for="radio<?=$i?>"><?="#".$i?></label>
<? }; ?>
                </div>
            </div>
            <div id="SensorGraph" class="bgcolor1 infotext">
                </br>
                <div id="flot-placeholder_t" class="flot-placeholder"></div>
                </br>
                <div id="flot-placeholder_h" class="flot-placeholder"></div>
                </br>
            </div>
    </div>
</body>
</html>