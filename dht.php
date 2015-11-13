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
<!doctype html>
<head>
    <title><?=$str_ServiceName?></title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="author" content="Armour Lu, Inventec">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" integrity="sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous">
    <link rel="stylesheet" href="/bootstrap-switch/css/bootstrap3/bootstrap-switch.css">
    <link href='https://fonts.googleapis.com/css?family=Ubuntu' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Roboto:500' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/start/jquery-ui.css">
    <link rel="stylesheet" href="/HoldOn.js/css/HoldOn.css">
    <link href="/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
    <script src="/js/jquery.cookie.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js" integrity="sha512-K1qjQ+NcF2TYO/eI3M6v8EiNYZfA95pQumfvcVrTHtwQVDG+aHRqLi/ETn2uB+1JqwYqVG3LIvdm9lj6imS/pQ==" crossorigin="anonymous"></script>
    <script src="/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
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
                <div class="col-sm-4 col-lg-2">
                    <img src="/image/inventec/inventec_small.png" style="margin:5px; margin-left:10px;">
                </div>
                <div id="currenttime" title="Update Time" class="col-sm-4 col-lg-offset-2 col-lg-4 text-center"></div>
                <div class="col-sm-4 col-lg-offset-1 col-lg-3 text-right"  title="Refresh Last Data and Time">
                    Auto Refresh <input type="checkbox" name="autoupdate">
                </div>
            </div>
        </div>
        <div class="headertext text-center"><?=$str_ServiceName?></div>
        <div id="data-sortable">
<? for($i=0;$i < $sensor_num;$i++){ ?>
            <div class="block-data text-center infotext" id="<?='curdata'.$i?>" data-data-size='max' data-data-resize='min' data-data-resizable='["datamenutext","reading","readingdecimal"]'>
                <div class="row">
                    <div class="col-sm-3 col-lg-1 datamenutext datamenutextmax"><?='#'.$i?></div>
                    <div class="col-sm-6 col-lg-6 col-lg-offset-2">
                        <span class="reading readingmax"></span><span class="readingdecimal readingdecimalmax"></span>
                        <span>&deg;C</span>
                        <span>&nbsp;&nbsp;</span>
                        <span class="reading readingmax"></span><span class="readingdecimal readingdecimalmax"></span>
                        <span>%</span>
                    </div>
                    <div class="col-sm-3 col-lg-1 col-lg-offset-2">
                        <i class="glyphicon glyphicon-menu-up datamenuresize" data-data-parent='<?='curdata'.$i?>' data-icon1='glyphicon-menu-up' data-icon2='glyphicon-menu-down'></i>
                    </div>
                </div>
            </div>
<? }; ?>
        </div>
        <div class="headertext text-center">Graph</div>
        <div id="graph" class="block-info text-center">
            <div class="loadinggif"><img src="/image/loading_spinner.gif"></div>
            <div class="chartnodata">NO DATA</div>
            <div id="chartdiv"></div>
            <br>
            <div class="row">
                <div class="col-sm-4 col-lg-4">
                    <button id="onedaygraph" class="btn btn-lg btn-block">Show Last 24 hrs</button>
                </div>
                <div class="col-sm-4 col-lg-4">
                    <button id="twodaygraph" class="btn btn-lg btn-block">Show Last 48 hrs</button>
                </div>
                <div class="col-sm-4 col-lg-4">
                    <button id="threedaygraph" class="btn btn-lg btn-block">Show Last 72 hrs</button>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-sm-3 col-lg-4"></div>
                <div class="col-sm-6 col-lg-4 infotext">Or Select a day to show</div>
            </div>
            <br>
            <div class="row">
                <div class="col-sm-3 col-lg-3"></div>
                <div class="col-sm-3 col-lg-3">
                    <div class="input-group date">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span><input id="graphdate" type="text" class="form-control input-lg" data-date-end-date="0d">
                    </div>
                </div>
                <div class="col-sm-3 col-lg-3">
                    <button id="updategraph" class="btn btn-primary btn-lg btn-block">Show</button>
                </div>
            </div>
            <br/>
        </div>
        <div class="headertext text-center">Alert Me!</div>
        <div id="alterme" class="block-info">
            <div class="row">
                <div class="col-sm-2 col-lg-4"></div>
                <div class="col-sm-6 col-lg-4">
                   <div class="input-group"  title="When <?=$str_ServiceName?> is online, we will send you a notice.">
                    <span class="input-group-addon">@</span><input id="alertemail" type="email" class="form-control input-lg" placeholder="Email Address">
                    </div>
                </div>
                <div class="col-sm-4 col-lg-4">
                    <button id="alertsubmit" class="btn btn-primary btn-lg">Submit</button>
                    <button id="alertclear" class="btn btn-default btn-lg">Clear</button>
                </div>
            </div>
        </div>
        <div class="headertext text-center">Options</div>
        <div class="block-info text-center">
            <button id='clearsetting' class="btn btn-default btn-lg">Clear Settings</button>
        </div>
    </div>
    <br><br>
</body>
</html>