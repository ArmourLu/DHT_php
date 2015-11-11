var chartData = [];
var chart = null;

var periodformat = [{period:'fff',format:'JJ:NN:SS'},
{period:'ss',format:'JJ:NN:SS'},
{period:'mm',format:'JJ:NN'},
{period:'hh',format:'JJ:NN'},
{period:'DD',format:'MM/DD'},
{period:'WW',format:'MM/DD'},
{period:'MM',format:'MM'},
{period:'YYYY',format:'YYYY'}];

function make_chart(){
    chart = AmCharts.makeChart("chartdiv", {
        "balloon": {
            "borderThickness": 1,
            "shadowAlpha": 0
        },
        "type": "serial",
        "theme": "light",
        "legend": {
            "labelWidth": 30,
            "forceWidth": true,
            "useGraphSettings": true
        },
        "dataProvider": chartData,
        "valueAxes": [{
            "minimum": 0,
            "maximum": 100,
            "labelFunction": formatTemp,
            "id":"v1",
            "axisColor": "#d00000",
            "axisThickness": 2,
            "gridAlpha": 0,
            "axisAlpha": 1,
            "position": "left"
        }, {
            "minimum":0,
            "maximum": 100,
            "labelFunction": formatHumi,
            "id":"v2",
            "axisColor": "#3169e8",
            "axisThickness": 2,
            "gridAlpha": 0,
            "axisAlpha": 1,
            "position": "right"
        }],
        "graphs": [{
            "legendValueText": "[[value]]C",
            "balloonFunction": formatTempgraphs,
            "valueAxis": "v1",
            "lineColor": "#ff9b59",
            "bullet": "round",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": "T0",
            "valueField": "t0",
            "fillAlphas": 0
        }, {
            "legendValueText": "[[value]]C",
            "balloonFunction": formatTempgraphs,
            "valueAxis": "v1",
            "lineColor": "#FCD202",
            "bullet": "square",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": "T1",
            "valueField": "t1",
            "fillAlphas": 0
        }, {
            "legendValueText": "[[value]]C",
            "balloonFunction": formatTempgraphs,
            "valueAxis": "v1",
            "lineColor": "#f23434",
            "bullet": "triangleUp",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": "T2",
            "valueField": "t2",
            "fillAlphas": 0
        },{
            "legendValueText": "[[value]]%",
            "balloonFunction": formatHumigraphs,
            "valueAxis": "v2",
            "lineColor": "#5287ff",
            "bullet": "round",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": "H0",
            "valueField": "h0",
            "fillAlphas": 0
        }, {
            "legendValueText": "[[value]]%",
            "balloonFunction": formatHumigraphs,
            "valueAxis": "v2",
            "lineColor": "#b04af0",
            "bullet": "square",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": "H1",
            "valueField": "h1",
            "fillAlphas": 0
        }, {
            "legendValueText": "[[value]]%",
            "balloonFunction": formatHumigraphs,
            "valueAxis": "v2",
            "lineColor": "#00eb3f",
            "bullet": "triangleUp",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": "H2",
            "valueField": "h2",
            "fillAlphas": 0
        }],
        "chartScrollbar": {},
        "chartCursor": {
            "categoryBalloonDateFormat": "MM/DD JJ:NN",
            "cursorPosition": "mouse"
        },
        "categoryField": "date",
        "categoryAxis": {
            "dateFormats": periodformat,
            "minPeriod": "ss",
            "parseDates": true,
            "axisColor": "#DADADA",
            "minorGridEnabled": true
        },
        "export": {
            "enabled": true,
            "position": "bottom-right"
         }
    });

    //chart.addListener("dataUpdated", zoomChart);
    //zoomChart();
    chart.zoomOut();
}

function zoomChart(){
    chart.zoomToIndexes(chart.dataProvider.length - 20, chart.dataProvider.length - 1);
}

function formatTemp(value, formattedValue, valueAxis){
        return value + "C";
}

function formatHumi(value, formattedValue, valueAxis){
        return value + "%";
}

function formatTempgraphs(GraphDataItem, AmGraph)
{
    return AmGraph.title + ": " + GraphDataItem.values.value + "C";
}

function formatHumigraphs(GraphDataItem, AmGraph)
{
    return AmGraph.title + ": " + GraphDataItem.values.value + "%";
}