const ajax_url = "ajax/monitoring/index";

function get_records(projid) {
    if (projid) {
        let start_date = $("#start_date").val();
        let end_date = $("#end_date").val();
        $.ajax({
            type: "get",
            url: ajax_url,
            data: {
                get_filter_record: "get_filter_record",
                projid: projid,
                start_date: start_date,
                end_date: end_date,
            },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    $("#filter_data").html(response.data);
                } else {
                    error_alert("Error no record found")
                }
            }
        });
    }
}