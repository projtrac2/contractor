$(document).ready(function () {
    var percent2 = $("#percent2").val();
    var percentage_progress_remaining = $("#percentage_progress_remaining").val();
    var percentage_duration_consumed = $("#percentage_duration_consumed").val();
    var percentage_duration_remaining = $("#percentage_duration_remaining").val();
    var rate = $("#rate").val();
    var rate_balance = $("#rate_balance").val();

    Highcharts.chart('highcharts-progress', {
        chart: {
            type: 'pie',
            options3d: {
                enabled: true,
                alpha: 45,
                beta: 0
            }
        },
        title: {
            text: 'Project % Progress',
            align: 'left'
        },
        accessibility: {
            point: {
                valueSuffix: '%'
            }
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                depth: 35,
                dataLabels: {
                    enabled: true,
                    format: '{point.name}'
                }
            }
        },
        series: [{
            type: 'pie',
            name: 'Percentage',
            data: [{
                name: 'Achieved',
                y: percent2,
                sliced: true,
                selected: true
            },
            ['Pending', percentage_progress_remaining]
            ],
            colors: [
                '#db03fc',
                '#03e3fc'
            ]
        }]
    });

    Highcharts.chart('highcharts-time', {
        colors: ['#FF9655', '#FFF263', '#24CBE5', '#64E572', '#50B432', '#ED561B', '#DDDF00', '#6AF9C4'],
        chart: {
            type: 'pie',
            options3d: {
                enabled: true,
                alpha: 45,
                beta: 0
            }
        },
        title: {
            text: 'Project % Time Consumed',
            align: 'left'
        },
        accessibility: {
            point: {
                valueSuffix: '%'
            }
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                depth: 35,
                dataLabels: {
                    enabled: true,
                    format: '{point.name}'
                }
            }
        },
        series: [{
            type: 'pie',
            name: 'Percentage',
            data: [{
                name: 'Consumed',
                y: percentage_duration_consumed,
                sliced: true,
                selected: true
            },
            ['Pending', percentage_duration_remaining]
            ],
            colors: [
                '#50B432',
                '#FFF263'
            ]
        }]
    });

    Highcharts.chart('highcharts-funds', {
        chart: {
            type: 'pie',
            options3d: {
                enabled: true,
                alpha: 45,
                beta: 0
            }
        },
        title: {
            text: 'Project % Funds Consumed',
            align: 'left'
        },
        accessibility: {
            point: {
                valueSuffix: '%'
            }
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                depth: 35,
                dataLabels: {
                    enabled: true,
                    format: '{point.name}'
                }
            }
        },
        series: [{
            type: 'pie',
            name: 'Percentage',
            data: [{
                name: 'Consumed',
                y: rate,
                sliced: true,
                selected: true
            },
            ['Pending', rate_balance]
            ],
            colors: [
                '#6AF9C4',
                '#CB2326'
            ]
        }]
    });
});
