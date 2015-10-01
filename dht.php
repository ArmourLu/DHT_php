<?php
//Get count
$sensor_num = $_GET['s'];
if($sensor_num=='' || !is_numeric($sensor_num)) $sensor_num=3;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">    
<head>
<style type="text/css">
	body {font-family:Verdana, Arial;margin: 0;padding: 0;} 
	.flot-placeholder{width:600px;height:200px;font-size:24px;text-align: center;margin:0 auto;}
	.normaltext{height:80px;line-height:80px;font-size:24px;text-align: center;margin:0 auto;color:#666666;vertical-align:middle;}
	#sensor_sel{font-size:24px;text-align: center;margin:0 auto;}
	.sensorname{width:40px;font-size:35px;text-align: left;display: inline-block;*display: inline;zoom: 1;color:#999999;}
	.item{width:40px;font-size:30px;text-align: center;display: inline-block;*display: inline;zoom: 1;}
	.currentdata{width:100px;height:120px;font-size:80px;text-align: center;display: inline-block;*display: inline;zoom: 1;}
	.unit{width:40px;font-size:30px;text-align: right;display: inline-block;*display: inline;zoom: 1;}
	.newline0{margin:0 auto;text-align:center;background-color: #eeeeee;}
	.newline1{margin:0 auto;text-align:center;background-color: #e6e6e6;}
	.notice{width:400px;font-size:15px;color:#666666;margin:0 auto;text-align: right;}
</style>
<link rel="stylesheet" href="/ui/jquery-ui-themes-1.11.4/themes/start/jquery-ui.css">
<script type="text/javascript" src="/js/flot/jquery.js"></script>
<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="/js/flot/excanvas.min.js"></script><![endif]-->
<script type="text/javascript" src="/js/flot/jquery.flot.js"></script>
<script type="text/javascript" src="/js/flot/jquery.flot.time.js"></script>
<script type="text/javascript" src="/js/flot/jquery.flot.axislabels.js"></script>
<script type="text/javascript" src="/js/flot/jquery.flot.symbol.js"></script>
<script src="/ui/jquery-ui-1.11.4/jquery-ui.js"></script>
<script src="/js/jquery.nicescroll.js"></script>
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
	for(i=0;i<<?=$sensor_num?>;i++){
		$.getJSON("dht_json.php?s="+i,function(dhtJSON){
			if(dhtJSON["Status"] == "OK"){
				var LastDate = dhtJSON["LastDate"];
				if(dhtJSON["Count"]>=1){
					time = new Date(LastDate*1000).toString();
					t = dhtJSON["Data"][0][0];
					h = dhtJSON["Data"][0][1];
					$("#currentdata_t"+dhtJSON["Sensor"]).html(t);
					$("#currentdata_h"+dhtJSON["Sensor"]).html(h);
					$("#currenttime").html(time);
					}
				}
			}
		);
	}
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
});
</script>
</head>
<body>
    <div id="currenttime" title="Update Time" class="normaltext"></div>
<?php
for($i=0;$i < $sensor_num;$i++){
?>
    <div class="newline<?=$i%2?>">
    	<div class="sensorname">#<?=$i?></div
    	><div class="item"></div
    	><div id="currentdata_t<?=$i?>" class="currentdata"></div
    	><div class="unit">&deg;C</div
    	><div class="item"></div
    	><div id="currentdata_h<?=$i?>"  class="currentdata"></div
    	><div class="unit">%</div>
    </div>
<?php
};
?>
    <div class="normaltext">Sensor Graph</div>
    <div id="sensor_sel">
	<input type="radio" name="radio1" id="radioX" value="-1" checked="true"><label for="radioX">Disable</label>
<?php
for($i=0;$i < $sensor_num;$i++){
?>
	<input type="radio" name="radio1" id="radio<?=$i?>" value="<?=$i?>"><label for="radio<?=$i?>">#<?=$i?></label>
<?php
};
?>
    </div></br>
    <div id="SensorGraph"></br>
    <div id="flot-placeholder_t" class="flot-placeholder"></div></br>
    <div id="flot-placeholder_h" class="flot-placeholder"></div></br></br>
    </div>
</body>
</html>