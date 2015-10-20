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
		if(dhtJSON["Status"] == "OK" && dhtJSON["Sensor"] == "all"){
			var LastDate = dhtJSON["LastDate"];
			if(dhtJSON["Count"]>=1){
				for(i=0;i<$(".block-data").length;i++){
					t = dhtJSON["Data"][i][0].split(".");
					h = dhtJSON["Data"][i][1].split(".");
					$(".reading").eq(i*2).html(Math.floor(t[0]));
                    $(".readingdecimal").eq(i*2).html("."+Math.floor(t[1][0]));
					$(".reading").eq(i*2+1).html(Math.floor(h[0]));
                    $(".readingdecimal").eq(i*2+1).html("."+Math.floor(h[1][0]));
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
            swal(alertresult["Status"].toUpperCase(), alertresult["Comment"], alertresult["Status"].toLowerCase());
            $("input").prop('disabled',false);
            $("button").prop('disabled',false);
            HoldOn.close();
        });
    });
    $("#alertclear").click(function(){
        $("#alertemail").val('');
    });
    cmd = getUrlParameter("cmd");
    key = getUrlParameter("key");
    id = getUrlParameter("id");
    if(cmd == 'verify'){
        $.getJSON("dht_alert.php?cmd=" + cmd + "&id=" + id + "&key=" + key,function(alertresult){
            swal(alertresult["Status"].toUpperCase(), alertresult["Comment"], alertresult["Status"].toLowerCase());
            if(history.pushState){
                history.pushState('','',location.href.split('?')[0]);
            }
        });
    }

});