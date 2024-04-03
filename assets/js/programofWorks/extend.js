const ajax_url = "ajax/programsOfWorks/extend";

$(document).ready(function () {
    $("#add_project_frequency").submit(function (e) {
        e.preventDefault();
        if (calculate_total1()) {
            var form_data = $(this).serialize();
            // $("#tag-form-submit-frequency").prop("disabled", true);
            $.ajax({
                type: "post",
                url: ajax_url,
                data: form_data,
                dataType: "json",
                success: function (response) {
                    if (response.success) {
                        success_alert("Record successfully created");
                    } else {
                        error_alert("Record could not be created");
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
        }
    });
});


function add_project_frequency(details) {
    $("#projid").val(details.projid);
    $("#activity_monitoring_frequency").val(details.activity_monitoring_frequency);
    $("#monitoring_frequency").val(details.monitoring_frequency);

    $("#m_site_id").val(details.site_id);
    $("#m_output_id").val(details.output_id);
    $("#m_task_id").val(details.task_id);
    $("#m_subtask_id").val(details.subtask_id);
}

function get_subtasks_wbs(output_id, site_id, task_id, subtask_id, issue_id) {
    $("#t_output_id").val(output_id);
    $("#t_site_id").val(site_id);
    $("#t_task_id").val(task_id);
    $("#t_subtask_id").val(subtask_id);
    $.ajax({
        type: "get",
        url: ajax_url,
        data: {
            get_wbs: "get_wbs",
            projid: $("#projid").val(),
            output_id: output_id,
            site_id: site_id,
            task_id: task_id,
            subtask_id: subtask_id,
            issue_id: issue_id
        },
        dataType: "json",
        success: function (response) {
            if (response.success) {
                $("#tasks_wbs_table_body").html(response.structure);
                var subtask = response.task;
                $("#subtask_start_date").html(response.start_date);
                $("#subtask_duration").html(response.duration);
                $("#subtask_end_date").html(response.end_date);
                $("#subtask_target").html(response.targets + ' ' + subtask.unit);
                $("#requested_units").html(response.requested_units + ' ' + subtask.unit);
                $("#subtask_name").html(subtask.task);
                $("#total_target").val(response.targets);
            } else {
                error_alert("Error please try again later");
            }
        }
    });
}

const calculate_total = (direct_cost_id) => {
    var target = 0;
    $(".targets").each(function (k, v) {
        var getVal = $(v).val();
        if (getVal != '') {
            target += parseFloat(getVal);
        }
    });

    var response = false;
    var total_target = $("#total_target").val();
    if (total_target != '') {
        total_target = parseFloat(total_target);
        if (total_target >= target) {
            response = true;
        } else {
            $(`#direct_cost_id${direct_cost_id}`).val("");
            error_alert('You should not exceed planned target')
        }
    }
    return response;
}

const calculate_total1 = () => {
    var target = 0;
    $(".targets").each(function (k, v) {
        var getVal = $(v).val();
        if (getVal != '') {
            target += parseFloat(getVal);
        }
    });

    var response = false;
    var total_target = $("#total_target").val();
    if (total_target != '') {
        total_target = parseFloat(total_target);
        if (total_target == target) {
            response = true;
        } else {
            error_alert('Subtask Target should be equal to the planned target')
        }
    }
    return response;
}