function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;
    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
};
function UpdateCurrentData(){
	$.getJSON("dht_json.php?s=a",function(dhtJSON){
		if(dhtJSON.Status == "OK" && dhtJSON.Sensor == "a"){
			var LastDate = dhtJSON.LastDate;
			if(dhtJSON["Count"]>=1){
				for(i=0;i<$(".reading").length/2&&i<dhtJSON.SensorCount;i++){
					t = dhtJSON.Data[0][i][0].toString().split(".");
					h = dhtJSON.Data[0][i][1].toString().split(".");
                    $("#curdata"+i+" .reading").eq(0).html(t[0].length>1?t[0]:"0"+t[0]);
                    $("#curdata"+i+" .reading").eq(1).html(h[0].length>1?h[0]:"0"+h[0]);
                    $("#curdata"+i+" .readingdecimal").eq(0).html(t.length>1?"."+t[1][0]:".0");
                    $("#curdata"+i+" .readingdecimal").eq(1).html(h.length>1?"."+h[1][0]:".0");
				}
				$("#currenttime").html(LastDate);
				}
			}
		}
	);
};
function UpdateChart(graphdate,period,interval,button){
    if(graphdate != '') graphdate = graphdate + " 00:00:00";
    $("#graph :input").prop("disabled",true);
    $("#graph :button").removeClass("btn-primary");
    $("#graph :button").addClass("btn-default");
    button.removeClass("btn-default");
    button.addClass("btn-primary");
    $(".loadinggif").show();
    $("#chartdiv").hide();
    $(".chartnodata").hide();
	$.getJSON("dht_json.php?c="+period+"&s=a&i="+interval+"&f="+graphdate,function(dhtJSON){
		if(dhtJSON.Status == "OK" && dhtJSON.Sensor == "a"){
			var LastDate = new Date(dhtJSON.LastDate.replace(' ','T')+'+08:00');
            var interval = dhtJSON.Interval;
            chartData = [];
			if(dhtJSON["Count"]>=1){
				for(i=dhtJSON.Count-1;i>=0;i--){
                    var newDate = new Date(LastDate);
                    newDate.setSeconds(newDate.getSeconds() - i * interval);
                    var tmpchartData = {};
                    tmpchartData["date"] = newDate;
                    for(x=0;x<dhtJSON.SensorCount;x++){
                        tmpchartData["t"+x.toString()] = dhtJSON.Data[i][x][0];
                        tmpchartData["h"+x.toString()] = dhtJSON.Data[i][x][1];
                    }
                    chartData.push(tmpchartData);
				}
                if(chart === null){
                    make_chart();
                }
                else{
                    chart.dataProvider = chartData;
                    chart.validateData();
                }
                
                $("#chartdiv").show();
				}
            else
            {
                $(".chartnodata").show();
            }
			}
        $(".loadinggif").hide();
        $("#graph :input").prop("disabled",false);
		}
	);
};
var CurrentDataTimer;
$(document).ready(function ($) {
    UpdateCurrentData();
    $( document ).tooltip({track: true});
    $("html").niceScroll();
    $("#alertsubmit").click(function(){
        if($("#alertemail").val()=="") return;
        prepare_submit();
        $.getJSON("dht_alert.php?cmd=add&email="+$("#alertemail").val(),function(alertresult){
            after_submit(alertresult);
        });
    });
    $("#alertclear").click(function(){
        $("#alertemail").val('');
    });
    cmd = getUrlParameter("cmd");
    key = getUrlParameter("key");
    id = getUrlParameter("id");
    if(cmd != undefined && key != undefined && id != undefined){
        prepare_submit();
        $.getJSON("dht_alert.php?cmd=" + cmd + "&id=" + id + "&key=" + key,function(alertresult){
            after_submit(alertresult);
        });
    }
    $("#onedaygraph").click(function(){
        UpdateChart("",86400,60,$(this));
    });
    $("#twodaygraph").click(function(){
        UpdateChart("",86400*2,60,$(this));
    });
    $("#threedaygraph").click(function(){
        UpdateChart("",86400*3,60,$(this));
    });
    $('#graphdate').datepicker({
        format: "yyyy-mm-dd",
        autoclose: true,
        todayHighlight: true
    });
    $.getJSON("dht_json.php",function(dhtJSON){
        if(dhtJSON.Status == "OK"){
            var LastDate = dhtJSON.LastDate.split(" ")[0];
            $('#graphdate').datepicker('update', LastDate);
            $('#graphdate').datepicker('setEndDate', LastDate);
        }
    });
    $("#updategraph").click(function(){
        if($("#graphdate").val()!='')
        {
            UpdateChart($("#graphdate").val(),86400,60,$(this));
        }
    });
    $("#onedaygraph").click();
    $.fn.bootstrapSwitch.defaults.size = 'small';
    $("input[type=checkbox]").bootstrapSwitch();
    $("input[type=checkbox]").each(function() {
        var mycookie = $.cookie($(this).attr('name'));
        if (mycookie === undefined || mycookie == "true") {
            if(!$(this).is(':checked')) $(this).bootstrapSwitch("toggleState");
            CurrentDataTimer = setInterval(UpdateCurrentData,1000);
        }
        else {
            if($(this).is(':checked')) $(this).bootstrapSwitch("toggleState");
        }
    });
    $("input[name='autoupdate']").on('switchChange.bootstrapSwitch', function(event, state) {
        if($(this).is(':checked')){
            CurrentDataTimer = setInterval(UpdateCurrentData,1000);
        }
        else{
            clearInterval(CurrentDataTimer);
        }
        $.cookie($(this).attr("name"), $(this).prop('checked'), {
            path: '/',
            expires: 365
        });
    });
    $('#data-sortable').sortable({
        axis: "y",
        update: function(event, ui) {
            localStorage.setItem("sorted", $("#data-sortable").sortable("toArray") );
        }
    }).disableSelection();
    restoreSorted();
});
function restoreSorted(){
      var sorted = localStorage["sorted"];      
      if(sorted == undefined) return;

      var elements = $("#data-sortable");
      var sortedArr = sorted.split(",");
      for (var i = 0; i < sortedArr.length; i++){
          var el = elements.find("#" + sortedArr[i]);
          $("#data-sortable").append(el);
      };
};
function prepare_submit(){
    HoldOn.open({
        theme:"sk-bounce",
        message: "<h1> Please wait </h1>",
        content:"",
        backgroundColor:"black",
        textColor:"white"
    });
    $("#alterme :input").prop('disabled',true);
};
function after_submit(alertresult){
    swal({title:alertresult.Status.toUpperCase(),
          text:alertresult.Comment,
          type:alertresult.Status.toLowerCase()
         },function(){
            $("#alterme :input").prop('disabled',false);
            HoldOn.close();
    });
    if(history.pushState){
        history.pushState('','',location.href.split('?')[0]);
    }
};