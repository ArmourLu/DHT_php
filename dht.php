<?
include "/var/www_private/mysql_conn.php";

$sqlstr = "SELECT Value FROM sysinfo where Name='ServiceName'";
$result = mysql_query($sqlstr);
$row = mysql_fetch_row($result);
$str_ServiceName = $row[0];

$sqlstr = "SELECT Value FROM sysinfo where Name='SensorCount'";
$result = mysql_query($sqlstr);
$row = mysql_fetch_row($result);
$sensor_num = (int)$row[0];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <title><?=$str_ServiceName?></title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="author" content="Armour Lu, Inventec">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="/bootstrap/css/bootstrap.min.css">
    <link href='https://fonts.googleapis.com/css?family=Ubuntu' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Roboto:500' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Questrial' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="/ui/jquery-ui-themes-1.11.4/themes/start/jquery-ui.css">
    <link rel="stylesheet" href="/HoldOn.js/css/HoldOn.css">
    <link href="/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <script src="/js/jquery.js"></script>
    <script src="/bootstrap/js/bootstrap.min.js"></script>
    <script src="/ui/jquery-ui-1.11.4/jquery-ui.js"></script>
    <script src="/js/jquery.nicescroll.js"></script>
    <script src="/sweetalert/sweetalert.min.js"></script>
    <link rel="stylesheet" type="text/css" href="/sweetalert/sweetalert.css">
    <script src="/HoldOn.js/js/HoldOn.js"></script>
    <script src="dht.js"></script>
    <link type="text/css" href="/amcharts/plugins/export/export.css" rel="stylesheet">
    <script src="/amcharts/amcharts.js"></script>
    <script src="/amcharts/serial.js"></script>
    <script src="/amcharts/themes/light.js"></script>
    <script src="/amcharts/plugins/export/export.js"></script>
    <script src="/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <script src="dht_chart.js"></script>
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
        <div class="headertext text-center"><?=$str_ServiceName?></div>
<? for($i=0;$i < $sensor_num;$i++){ ?>
        <div class="block-data text-center infotext">
            <span class="reading"></span><span class="readingdecimal"></span>
            <span>&deg;C</span>
            <span>&nbsp;&nbsp;</span>
            <span class="reading"></span><span class="readingdecimal"></span>
            <span>%</span>
        </div>
<? }; ?>
        <div class="headertext text-center">Graph</div>
        <div id="graph" class="block-info text-center">
            <div id="chartdiv"></div>
            <div class="loadinggif"><img src="/image/loading_spinner.gif"></div>
            <div class="chartnodata">NO DATA</div>
            </br>
            <div class="row">
                <div class="col-lg-4">
                    <button id="onedaygraph" class="btn btn-lg btn-block">Show Last 24 hrs</button>
                </div>
                <div class="col-lg-4">
                    <button id="twodaygraph" class="btn btn-lg btn-block">Show Last 48 hrs</button>
                </div>
                <div class="col-lg-4">
                    <button id="threedaygraph" class="btn btn-lg btn-block">Show Last 72 hrs</button>
                </div>
            </div>
            </br>
            <div class="row">
                <div class="col-lg-4"></div>
                <div class="col-lg-4 infotext">Or Select a day to show</div>
            </div>
            </br>
            <div class="row">
                <div class="col-lg-3"></div>
                <div class="col-lg-3">
                    <div class="input-group date">
                        <input id="graphdate" type="text" class="form-control input-lg" data-date-end-date="0d"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
                <div class="col-lg-3">
                    <button id="updategraph" class="btn btn-primary btn-lg btn-block">Show</button>
                </div>
            </div>
            <br/>
        </div>
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
        <div class="headertext text-center"></div>
    </div>
    </br></br>
</body>
</html>