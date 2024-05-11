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