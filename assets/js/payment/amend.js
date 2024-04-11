var ajax_url = "ajax/payment/amend";
$(document).ready(function () {
    $("#modal_form_submit1").submit(function (e) {
        e.preventDefault();
        var cost_type = $("#purpose1").val();
        $.ajax({
            type: "post",
            url: ajax_url,
            data: $(this).serialize(),
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    success_alert("Record created successfully");
                } else {
                    error_alert(" Error  occured please try again later!!");
                }

                $(".modal").each(function () {
                    $(this).modal("hide");
                    $(this)
                        .find("form")
                        .trigger("reset");
                });

                setTimeout(() => {
                    window.location.reload();
                }, 3000);
            }
        });
    });
});


//function to put commas to the data
function commaSeparateNumber(val) {
    while (/(\d+)(\d{3})/.test(val.toString())) {
        val = val.toString().replace(/(\d+)(\d{3})/, "$1" + "," + "$2");
    }
    return val;
}

const add_request_details = (direct_cost_id) => {
    var request_id = $("#request_id").val();
    $("#direct_cost_id").val(direct_cost_id);
    $.ajax({
        type: "get",
        url: ajax_url,
        data: {
            get_budget_lines: "get_browser_budget_lines",
            direct_cost_id: direct_cost_id,
            request_id: request_id,
        },
        dataType: "json",
        success: function (response) {
            if (response.success) {
                $("#_budget_lines_values_table").html(response.table_body);
            } else {
                error_alert("Sorry error occured");
            }
        }
    });
}

function calculate_total_cost() {
    var unit_cost = $(`#unit_cost`).val();
    var no_units = $(`#no_units`).val();
    var h_no_units = $(`#units_balance`).val();

    unit_cost = (unit_cost != "") ? parseFloat(unit_cost) : 0;
    no_units = (no_units != "") ? parseFloat(no_units) : 0;
    h_no_units = (h_no_units != "") ? parseFloat(h_no_units) : 0;
    var total = 0;
    console.log(h_no_units, no_units);
    if (h_no_units >= no_units && no_units > 0) {
        total = unit_cost * no_units;
        $("#subtotal_cost").html(commaSeparateNumber(total));
    } else {
        error_alert("Sorry ensure that the number of units is less or equal to the number of units specified");
        $(`#no_units`).val("");
    }
}