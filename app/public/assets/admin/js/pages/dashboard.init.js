/*
function getChartColorsArray(e) {
    if (null !== document.getElementById(e)) {
        var t = document.getElementById(e).getAttribute("data-colors");
        if (t)
            return (t = JSON.parse(t)).map(function(e) {
                var t = e.replace(" ", "");
                if (-1 === t.indexOf(",")) {
                    var o = getComputedStyle(document.documentElement).getPropertyValue(t);
                    return o || t
                }
                var r = e.split(",");
                return 2 != r.length ? t : "rgba(" + getComputedStyle(document.documentElement).getPropertyValue(r[0]) + "," + r[1] + ")"
            });
        console.warn("data-colors Attribute not found on:", e)
    }
}
var statisticsApplicationColors = getChartColorsArray("chart");
statisticsApplicationColors && (options = {
    series: [{
        name: "Chauffeur VTC",
        type: "column",
        data: [30, 48, 28, 74, 39, 87, 54, 36, 50, 87, 84]
    }, {
        name: "Chauffeur TAXI",
        type: "column",
        data: [20, 50, 42, 10, 24, 28, 60, 35, 47, 64, 78]
    }, {
        name: "Chauffeur LIVREUR",
        type: "area",
        data: [44, 55, 41, 67, 22, 43, 21, 41, 56, 27, 43]
    }],
    chart: {
        height: 350,
        type: "line",
        stacked: !1,
        toolbar: {
            show: !1
        }
    },
    legend: {
        show: !0,
        offsetY: 10
    },
    stroke: {
        width: [0, 0, 2, 2],
        curve: "smooth"
    },
    plotOptions: {
        bar: {
            columnWidth: "30%"
        }
    },
    fill: {
        opacity: [1, 1, .1, 1],
        gradient: {
            inverseColors: !1,
            shade: "light",
            type: "vertical",
            opacityFrom: .85,
            opacityTo: .55,
            stops: [0, 100, 100, 100]
        }
    },
    labels: ["01/01/2022", "02/01/2022", "03/01/2022", "04/01/2022", "05/01/2022", "06/01/2022", "07/01/2022", "08/01/2022", "09/01/2022", "10/01/2022", "11/01/2022"],
    colors: statisticsApplicationColors,
    markers: {
        size: 0
    },
    xaxis: {
        type: "datetime"
    },
    tooltip: {
        shared: !0,
        intersect: !1,
        y: {
            formatter: function(e) {
                return void 0 !== e ? e.toFixed(0) + " souscripteurs" : e
            }
        }
    }
},
    (chart = new ApexCharts(document.querySelector("#chart"),options)).render());
var options, chart, ApplicationReveicedTimeColors = getChartColorsArray("application-received-time");
ApplicationReveicedTimeColors && (options = {
    series: [{
        name: "Inscriptions temps réel",
        data: [34, 44, 54, 21, 12, 43, 33, 80, 66]
    }],
    chart: {
        type: "line",
        height: 378,
        toolbar: {
            show: !1
        }
    },
    stroke: {
        width: 3,
        curve: "smooth"
    },
    labels: ["14 H", "9 H", "10 H", "11 H", "12 H", "13 H", "14 H AM", "15 H", "4 H"],
    dataLabels: {
        enabled: !1
    },
    colors: ApplicationReveicedTimeColors,
    markers: {
        hover: {
            sizeOffset: 4
        }
    }
},
    (chart = new ApexCharts(document.querySelector("#application-received-time"),options)).render());
console.log(ApplicationReveicedTimeColors);

*/

function getChartColorsArray(t) {
    if (null !== document.getElementById(t)) {
        var e = document.getElementById(t).getAttribute("data-colors");
        if (e)
            return (e = JSON.parse(e)).map(function(t) {
                var e = t.replace(" ", "");
                if (-1 === e.indexOf(",")) {
                    var o = getComputedStyle(document.documentElement).getPropertyValue(e);
                    return o || e
                }
                var a = t.split(",");
                return 2 != a.length ? e : "rgba(" + getComputedStyle(document.documentElement).getPropertyValue(a[0]) + "," + a[1] + ")"
            });
        console.warn("data-colors Attribute not found on:", t)
    }
}

var sexChartColors = getChartColorsArray("sex-chart");
var nationalityChartColors = getChartColorsArray("nationality-chart");
var activityChartColors = getChartColorsArray("activity-chart");

var sexChart = document.getElementById("sex-chart");
var nationalityChart = document.getElementById("nationality-chart");
var activityChart = document.getElementById("activity-chart");
var souscriptionDom = document.getElementById("souscription-chart");

sexChart = echarts.init(sexChart, 'roma');
nationalityChart = echarts.init(nationalityChart, 'roma');
activityChart = echarts.init(activityChart, 'roma');
souscriptionChart = echarts.init(souscriptionDom);

$.get('/stats').done(function(data) {

    option = null;
    option = {
        tooltip: {
            trigger: "item",
            formatter: "{a} <br/>{b}: {c} ({d}%)"
        },
        legend: {
            orient: "vertical",
            x: "left",
            data: data.sex.legend,
            textStyle: {
                color: "#8791af"
            }
        },
    //    color: sexChartColors,
        series: [{
            name: "Total",
            type: "pie",
            radius: ["50%", "70%"],
            avoidLabelOverlap: !(app = {}),
            label: {
                normal: {
                    show: !1,
                    position: "center"
                },
                emphasis: {
                    show: !0,
                    textStyle: {
                        fontSize: "30",
                        fontWeight: "bold"
                    }
                }
            },
            labelLine: {
                normal: {
                    show: !1
                }
            },
            data: data.sex.data
        }]
    };
    sexChart.setOption(option, !0);

    option = null;
    option = {
        tooltip: {
            trigger: "item",
            formatter: "{a} <br/>{b}: {c} ({d}%)"
        },
        legend: {
            orient: "horizontal",
            x: "left",
            data: data.nationality.legend,
            textStyle: {
                color: "#8791af"
            }
        },
       // color: nationalityChartColors,
        series: [{
            name: "Total",
            type: "pie",
            radius: ["45%", "70%"],
            avoidLabelOverlap: false,
            itemStyle: {
                borderRadius: 10,
                borderColor: '#fff',
                borderWidth: 2
            },
           // avoidLabelOverlap: !(app = {}),
            label: {
                normal: {
                    show: !1,
                    position: "center"
                },
                emphasis: {
                    show: !0,
                    textStyle: {
                        fontSize: "16",
                        fontWeight: "bold"
                    }
                }
            },
            labelLine: {
                normal: {
                    show: !1
                }
            },
            data: data.nationality.data
        }]
    };
    nationalityChart.setOption(option, !0);

    option = null;
    option = {
        tooltip: {
            trigger: "item",
            formatter: "{a} <br/>{b} : {c} ({d}%)"
        },
        legend: {
            orient: "vertical",
            left: "left",
            data: data.activity.legend,
            textStyle: {
                color: "#8791af"
            }
        },
       // color: activityChartColors,
        series: [{
            name: "Total",
            type: "pie",
            radius: "55%",
            center: ["50%", "60%"],
            data: data.activity.data,
            itemStyle: {
                emphasis: {
                    shadowBlur: 10,
                    shadowOffsetX: 0,
                    shadowColor: "rgba(0, 0, 0, 0.5)"
                }
            }
        }]
    };
    activityChart.setOption(option, !0);


    option = null;
    option = {
        tooltip: {
            trigger: 'axis',
            axisPointer: {
                type: 'shadow'
            }
        },
        legend: {},
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        xAxis: [
            {
                type: 'category',
                data: data.months
            }
        ],
        yAxis: [
            {
                type: 'value'
            }
        ],
        series: [
            {
                name: 'VTC',
                type: 'bar',
                emphasis: {
                    focus: 'series'
                },
                data: data.vtc
            },
            {
                name: 'TAXI COMPTEUR',
                type: 'bar',
                stack: 'Ad',
                emphasis: {
                    focus: 'series'
                },
                data: data.taxi_compteur
            },
            {
                name: 'TAXI COMMUNAL',
                type: 'bar',
                stack: 'Ad',
                emphasis: {
                    focus: 'series'
                },
                data: data.taxi_communal
            },
            {
                name: 'MOTO TAXI',
                type: 'bar',
                stack: 'Ad',
                emphasis: {
                    focus: 'series'
                },
                data: data.moto_taxi
            },
            {
                name: 'LIVREUR',
                type: 'bar',
                stack: 'Ad',
                emphasis: {
                    focus: 'series'
                },
                data: data.livreur
            },
            {
                name: 'TRICYCLE',
                type: 'bar',
                stack: 'Ad',
                emphasis: {
                    focus: 'series'
                },
                data: data.tricycle
            }

        ]
    };
    option && souscriptionChart.setOption(option);

});

// var chartDom = document.getElementById('commune-chart','roma');
// var communeChart = echarts.init(chartDom);
// var option = null;
//
// option = {
//     title: {
//       //  text: 'Total par commune'
//     },
//     tooltip: {
//         trigger: 'axis',
//         axisPointer: {
//             type: 'shadow'
//         }
//     },
//     legend: {},
//     grid: {
//         left: '3%',
//         right: '4%',
//         bottom: '3%',
//         containLabel: true
//     },
//     yAxis: {
//         type: 'value',
//         boundaryGap: [0, 0.01]
//     },
//     xAxis: {
//         type: 'category',
//         data: ['ABOBO', 'YOPOUGON', 'ANYAMA', 'COCODY', 'BINGERVILLE', 'MARCORY']
//     },
//     series: [
//         {
//             name: 'VTC',
//             type: 'line',
//             data: [12, 28, 5, 8, 15, 25]
//         },
//         {
//             name: 'TAXI',
//             type: 'line',
//             data: [ 5, 2, 5, 30, 8, 10]
//         },
//         {
//             name: 'LIVREUR',
//             type: 'line',
//             data: [2, 18, 5, 4, 10, 5]
//         }
//     ]
// };
//
// option && communeChart.setOption(option);





