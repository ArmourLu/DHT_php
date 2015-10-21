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
	$.getJSON("dht_json.php?s=all&ft=1",function(dhtJSON){
		if(dhtJSON.Status == "OK" && dhtJSON.Sensor == "all"){
			var LastDate = dhtJSON.LastDate;
			if(dhtJSON["Count"]>=1){
				for(i=0;i<$(".reading").length/2;i++){
					t = dhtJSON.Data[i][0].toString().split(".");
					h = dhtJSON.Data[i][1].toString().split(".");
					$(".reading").eq(i*2).html(t[0].length>1?t[0]:"0"+t[0]);
                    $(".reading").eq(i*2+1).html(h[0].length>1?h[0]:"0"+h[0]);
                    $(".readingdecimal").eq(i*2).html(t.length>1?"."+t[1][0]:".0");
                    $(".readingdecimal").eq(i*2+1).html(h.length>1?"."+h[1][0]:".0");
				}
				$("#currenttime").html(LastDate);
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
    if(cmd == 'verify' && key != '' && id != ''){
        prepare_submit();
        $.getJSON("dht_alert.php?cmd=" + cmd + "&id=" + id + "&key=" + key,function(alertresult){
            after_submit(alertresult);
        });
    }
});
function prepare_submit(){
    HoldOn.open({
        theme:"sk-bounce",
        message: "<h1> Please wait </h1>",
        content:"",
        backgroundColor:"black",
        textColor:"white"
    });
    $("input").prop('disabled',true);
    $("button").prop('disabled',true);
};
function after_submit(alertresult){
    swal({title:alertresult.Status.toUpperCase(),
          text:alertresult.Comment,
          type:alertresult.Status.toLowerCase()
         },function(){
            $("input").prop('disabled',false);
            $("button").prop('disabled',false);
            HoldOn.close();
    });
    if(history.pushState){
        history.pushState('','',location.href.split('?')[0]);
    }
};