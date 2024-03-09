$(function () {
    new Chart(document.getElementById("line_chart").getContext("2d"), getChartJs('line'));
    new Chart(document.getElementById("bar_chart").getContext("2d"), getChartJs('bar'));
    new Chart(document.getElementById("radar_chart").getContext("2d"), getChartJs('radar'));
    new Chart(document.getElementById("proj_pie_chart").getContext("2d"), getChartJs('pie'));
});

function getChartJs(type) {
    var config = null;

    if (type === 'line') {
        config = {
            type: 'line',
            data: {
                labels: ["Agriculture", "Building Construction", "Health", "Education", "Water", "Finance", "ICT & Trade", "Lands & Housing", "Devolution & Public Services", "Energy & Public Works", "Enterprise Development"],
                datasets: [{
                    label: "Total Projects Costs",
                    data: [65, 59, 80, 81, 56, 55, 40, 80, 81, 56, 55],
                    borderColor: 'rgba(0, 188, 212, 0.75)',
                    backgroundColor: 'rgba(0, 188, 212, 0.3)',
                    pointBorderColor: 'rgba(0, 188, 212, 0)',
                    pointBackgroundColor: 'rgba(0, 188, 212, 0.9)',
                    pointBorderWidth: 1
                }, {
                        label: "Total Funds Disbursed",
                        data: [28, 48, 40, 19, 86, 27, 90, 40, 19, 86, 27],
                        borderColor: 'rgba(233, 30, 99, 0.75)',
                        backgroundColor: 'rgba(233, 30, 99, 0.3)',
                        pointBorderColor: 'rgba(233, 30, 99, 0)',
                        pointBackgroundColor: 'rgba(233, 30, 99, 0.9)',
                        pointBorderWidth: 1
                    }]
            },
            options: {
                responsive: true,
                legend: false
            }
        }
    }
    else if (type === 'bar') {
        config = {
            type: 'bar',
            data: {
                labels: ["Agriculture", "Building Construction", "Health", "Education", "Water", "Finance", "ICT & Trade", "Lands & Housing", "Devolution & Public Services", "Energy & Public Works", "Enterprise Development"],
                datasets: [{
                    label: "Projects Allocation (Ksh)",
                    data: [650000000, 590000000, 800000000, 810000000, 860000000, 550000000, 900000000, 800000000, 810000000, 560000000, 550000000],
                    backgroundColor: 'rgba(0, 188, 212, 0.8)'
                }, {
                        label: "Projects Absorption (Ksh)",
                        data: [280000000, 480000000, 400000000, 190000000, 560000000, 270000000, 400000000, 400000000, 190000000, 360000000, 270000000],
                        backgroundColor: 'rgb(139, 195, 74)'
                    }]
            },
            options: {
                responsive: true,
                legend: false
            }
        }
    }
    else if (type === 'radar') {
        config = {
            type: 'radar',
            data: {
                labels: ["Agriculture", "Building Construction", "Health", "Education", "Water", "Finance", "ICT & Trade", "Lands & Housing", "Devolution & Public Services", "Energy & Public Works", "Enterprise Development"],
                datasets: [{
                    label: "Projects Allocation",
                    data: [65, 25, 90, 81, 56, 55, 40, 90, 81, 56, 55],
                    borderColor: 'rgba(0, 188, 212, 0.8)',
                    backgroundColor: 'rgba(0, 188, 212, 0.5)',
                    pointBorderColor: 'rgba(0, 188, 212, 0)',
                    pointBackgroundColor: 'rgba(0, 188, 212, 0.8)',
                    pointBorderWidth: 1
                }]
            },
            options: {
                responsive: true,
                legend: false
            }
        }
    }
    else if (type === 'pie') {
        config = {
            type: 'pie',
            data: {
                datasets: [{
                    data: [225, 50, 100, 40],
                    backgroundColor: [
                        "rgb(233, 30, 99)",
                        "rgb(255, 193, 7)",
                        "rgb(0, 188, 212)",
                        "rgb(139, 195, 74)"
                    ],
                }],
                labels: [
                    "Pink",
                    "Amber",
                    "Cyan",
                    "Light Green"
                ]
            },
            options: {
                responsive: true,
                legend: false
            }
        }
    }
    return config;
}