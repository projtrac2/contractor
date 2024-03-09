$(document).ready(function(){
  $.ajax({
    url: "http://104.197.153.188/projtrac-dashboard/billsdata.php",
    method: "GET",
    success: function(data) {
      console.log(data);
      var sector = [];
      var projects = [];

      for(var i in data) {
        sector.push(data[i].sector);
        projects.push(data[i].projno);
      }

      var chartdata = {
        labels: sector,
        datasets : [
          {
            label: 'No of Projects',
            backgroundColor: 'rgba(200, 200, 200, 0.55)',
            borderColor: 'rgba(200, 200, 200, 0.55)',
            hoverBackgroundColor: 'rgba(200, 200, 200, 1)',
            hoverBorderColor: 'rgba(200, 200, 200, 1)',
            data: projects
          }
        ]
      };

      var ctx = $("#mycanvas");

      var barGraph = new Chart(ctx, {
        type: 'bar',
        data: chartdata
      });
    },
    error: function(data) {
      console.log(data);
    }
  });
});