var Chart = (function () {
    //PROPERTIES
    let config = {
        applicationStatusData: {},
        applicationData: [],
        applicationStatusElmId: null,
        applicationDataElmId: null,
    };

    //PRIVATE METHODS
    let drawDonutChart = function() {
        var data = google.visualization.arrayToDataTable([
            ['Durum', 'Sayı'],
            ['Kabul Edilen', config.applicationStatusData.approvedApplicationsCount],
            ['Reddedilen', config.applicationStatusData.rejectedApplicationsCount],
            ['Kabule Yakın', config.applicationStatusData.maybeApprovedApplicationsCount],
            ['Karar Bekleyen', config.applicationStatusData.notDecidedApplicationsCount]
        ]);

        var options = {

            backgroundColor: '#161824',
            pieHole: 0.5,
            pieSliceText: 'percentage',
            pieSliceTextStyle: {
                bold: true,
                color: '#000'
            },
            chartArea:{width:'90%',height:'90%'},
            legend: {
                textStyle: {
                    color: '#fff'
                },
                alignment: 'center',
            },

            slices: {
                0: { color: '#28c76f' },
                1: { color: '#ee4343' },
                2: { color: '#1d7bf0' },
                3: { color: '#f5a155' },
            },
        };

        var chart = new google.visualization.PieChart(document.getElementById(config.applicationStatusElmId));
        chart.draw(data, options);
    }

    let drawColumnChart = function() {
        var arr = [
            ['Tarih', 'Başvuru Sayısı']
        ];
        for (var i in config.applicationData) {
            arr.push([config.applicationData[i].date, config.applicationData[i].count])
        }
        var data = google.visualization.arrayToDataTable(arr);

        var options = {
            backgroundColor: '#161824',
            legend: {
                position: 'none'
            },
            chartArea: {
                backgroundColor: '#161824',
            },
            bar: {groupWidth: "40%"},
            colors: [
                'Distance', '#28c76f'
            ],
            vAxis: {
                gridlines: {
                    color: '#262938'
                }
            }
        };

        var chart = new google.charts.Bar(document.getElementById(config.applicationDataElmId));
        chart.draw(data, google.charts.Bar.convertOptions(options));
    }

    //PUBLIC METHODS
    let draw = function() {
        google.charts.load("current", {packages:["corechart","bar"]});
        google.charts.setOnLoadCallback(drawDonutChart);
        google.charts.setOnLoadCallback(drawColumnChart);
    }

    //Initialize
    var init = function (customConfig) {
        $.extend(config, customConfig);
        draw();
    };

    return {
        init: init,
        draw: draw
    };
})();
