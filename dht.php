<?php
//Get count
$sensor_num = $_GET['s'];
if($sensor_num=='' || !is_numeric($sensor_num)) $sensor_num=3;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">    
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="/bootstrap/css/bootstrap.min.css">
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
<script src="/js/jquery.expandable.js"></script>
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
        $("#alertemail").attr('disabled',true);
        $.getJSON("dht_alert.php?cmd=add&email="+$("#alertemail").val(),function(alertresult){
            if(alertresult["Status"]=="OK"){
                $("#alertresult").html('<strong>Success!</strong> '+alertresult["Comment"]);
            }else{
                $("#alertresult").html('<strong>Error!</strong> '+alertresult["Comment"]);
            }
        });
        $("#alertsubmit").attr('disabled',false);
        $("#alertemail").attr('disabled',false);
    });
    $("#alertemail").keypress(function(){
        $("#alertresult").html("");
    });
    $("#alertclear").click(function(){
        $("#alertemail").val('');
        $("#alertresult").html("");
    });
});
</script>
<script>
</script>
</head>
<body>
    <div class="container">
        <div id="currenttime" title="Update Time" class="headertext text-center"></div>
        <?php
for($i=0;$i < $sensor_num;$i++){
?>
            <div class="bgcolor<?=$i%2?> text-center infotext">
                <span><?="#".$i?></span>
                <span></span>
                <span id="currentdata_t<?=$i?>" class="currentdata"></span>
                <span>&deg;C</span>
                <span></span>
                <span id="currentdata_h<?=$i?>" class="currentdata"></span>
                <span>%</span>
            </div>
            <?php
};
?>
                <div class="headertext text-center" title="When Pi2/Remote Temperature Monitoring System is Ready, we will send you a notice.">Alert Me!</div>
                <div id="alterme" class="bgcolor0">
                    <div class="row">
                        <div class="col-sm-4"></div>
                        <div class="col-sm-4">
                            <input id="alertemail" type="email" class="form-control input-lg" id="email" placeholder="Email Address">
                        </div>
                        <div class="col-sm-4">
                            <button id="alertsubmit" class="btn btn-primary btn-lg">Submit</button>
                            <button id="alertclear" class="btn btn-default btn-lg">Clear</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-2"></div>
                        <div class="col-sm-8">
                           <div id="alertresult" class="smallinfotext text-center"></div>
                        </div>
                    </div>
                </div>
                <div class="headertext text-center">Sensor Graph</div>
                <div class="bgcolor0 text-center">
                    <div id="sensor_sel">
                        <input type="radio" name="radio1" id="radioX" value="-1" checked="true">
                        <label for="radioX">Disable</label>
                        <?php
for($i=0;$i < $sensor_num;$i++){
?>
                            <input type="radio" name="radio1" id="radio<?=$i?>" value="<?=$i?>">
                            <label for="radio<?=$i?>"><?="#".$i?>
                            </label>
                            <?php
};
?>
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