$(function () {
    new Chart(document.getElementById("my_bar_chart").getContext("2d"), getChartsJs('bar'));
});

function getChartsJs(type) {
    var config = null;
	var r = (50000000).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');

    if (type === 'bar') {
        config = {
            type: 'bar',
            data: {
                labels: ["Agriculture", "Building Construction", "Health", "Education", "Water", "Finance", "ICT & Trade", "Lands & Housing", "Devolution & Public Services", "Energy & Public Works", "Enterprise Development"],
                datasets: [{
                    label: "Projects Pending Bills (Ksh)",
                    data: [50000000, 90000000, 5000000, 10000000, 0, 75000000, 190000000, 38000000, 31000000, 56000000, 55000000],
                    backgroundColor: 'rgb(233, 30, 99)'
                }]
            },
            options: {
                responsive: true,
                legend: false
            }
        }
    }
    return config;
}