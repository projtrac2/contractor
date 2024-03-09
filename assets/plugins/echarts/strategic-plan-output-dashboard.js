// ============================================================== 
// Bar chart option
// ============================================================== 
var myChart = echarts.init(document.getElementById('bar-chart'));

// specify chart configuration item and data
option = {
    tooltip: {
        trigger: 'axis'
    },
    legend: {
        data: ['Planned Projects', 'Realized Projects']
    },
    toolbox: {
        show: true,
        feature: {

            magicType: { show: true, type: ['line', 'bar'] },
            restore: { show: true },
            saveAsImage: { show: true }
        }
    },
    color: ["#009efb", "#55ce63"],
    calculable: true,
    xAxis: [{
        type: 'category',
        data: ['2013', '2014', '2015', '2016', '2017', '2018', '2019', '2020', '2021', '2022', '2023', '2024']
    }],
    yAxis: [{
        type: 'value'
    }],
    series: [{
            name: 'Planned Projects',
            type: 'bar',
            data: [13, 16, 19, 26, 29, 97, 175, 182, 48, 28, 106, 53],
            markPoint: {
                data: [
                    { type: 'max', name: 'The highest'},
                    { type: 'min', name: 'The Lowest'}
                ]
            },
            markLine: {
                data: [
                    { type: 'average', name: 'Average' }
                ]
            }
        },
        {
            name: 'Realized Projects',
            type: 'bar',
            data: [12, 15, 17, 23, 25, 76, 135, 162, 32, 20, 64, 33],
            markPoint: {
                data: [
                    { type: 'max', name: 'The highest'},
                    { type: 'min', name: 'The Lowest'}
                ]
            },
            markLine: {
                data: [
                    { type: 'average', name: 'Average' }
                ]
            }
        }
    ]
};


// use configuration item and data specified to show chart
myChart.setOption(option, true), $(function() {
    function resize() {
        setTimeout(function() {
            myChart.resize()
        }, 100)
    }
    $(window).on("resize", resize), $(".sidebartoggler").on("click", resize)
});

// ============================================================== 
// Line chart
// ============================================================== 
var dom = document.getElementById("main");
var mytempChart = echarts.init(dom);
var app = {};
option = null;
option = {

    tooltip: {
        trigger: 'axis'
    },
    legend: {
        data: ['Budget', 'Cost']
    },
    toolbox: {
        show: true,
        feature: {
            magicType: { show: true, type: ['line', 'bar'] },
            restore: { show: true },
            saveAsImage: { show: true }
        }
    },
    color: ["#55ce63", "#009efb"],
    calculable: true,
    xAxis: [{
        type: 'category',

        boundaryGap: false,
        data: ['2013', '2014', '2015', '2016', '2017', '2018', '2019', '2020', '2021', '2022']
    }],
    yAxis: [{
        type: 'value',
        axisLabel: {
            formatter: 'Ksh. {value}M'
        }
    }],

    series: [{
            name: 'Budget',
            type: 'line',
            color: ['#000'],
            data: [11, 11, 15, 13, 12, 13, 10, 17, 14, 16],
            markPoint: {
                data: [
                    { type: 'max', name: 'Highest Budget (M)' },
                    { type: 'min', name: 'Lowest Budget (M)' }
                ]
            },
            itemStyle: {
                normal: {
                    lineStyle: {
                        shadowColor: 'rgba(0,0,0,0.3)',
                        shadowBlur: 10,
                        shadowOffsetX: 8,
                        shadowOffsetY: 8
                    }
                }
            },
            markLine: {
                data: [
                    { type: 'average', name: 'Average' }
                ]
            }
        },
        {
            name: 'Cost',
            type: 'line',
            data: [5, 7, 2, 5, 8, 6, 0, 4, 3, 9],
            markPoint: {
                data: [
                    { type: 'max', name: 'Highest Cost (M)' },
                    { type: 'min', name: 'Lowest Cost (M)' }
                ]
            },
            itemStyle: {
                normal: {
                    lineStyle: {
                        shadowColor: 'rgba(0,0,0,0.3)',
                        shadowBlur: 10,
                        shadowOffsetX: 8,
                        shadowOffsetY: 8
                    }
                }
            },
            markLine: {
                data: [
                    { type: 'average', name: 'Average' }
                ]
            }
        }
    ]
};

if (option && typeof option === "object") {
    mytempChart.setOption(option, true), $(function() {
        function resize() {
            setTimeout(function() {
                mytempChart.resize()
            }, 100)
        }
        $(window).on("resize", resize), $(".sidebartoggler").on("click", resize)
    });
}
