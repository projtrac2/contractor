const ajax_url = "ajax/programsOfWorks/index";

$(document).ready(function () {
    $("#add_output").submit(function (e) {
        e.preventDefault();
        var form_data = $(this).serialize();
        $("#tag-form-submit").prop("disabled", true);
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
    });

    $("#add_project_frequency").submit(function (e) {
        e.preventDefault();
        if (calculate_total1()) {
            var form_data = $(this).serialize();
            $("#tag-form-submit-frequency").prop("disabled", true);
            $.ajax({
                type: "post",
                url: "ajax/programsOfWorks/wbs",
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

    $('.tasks_id_header').each((index, element) => {
        var projid = $("#projid").val();
        var site_id = $(element).next().val();
        if (projid != '') {
            $.ajax({
                type: "get",
                url: "ajax/programsOfWorks/get-wbs",
                data: {
                    projid: projid,
                    site_id: site_id,
                    output_id: $(element).next().next().val(),
                    task_id: $(element).val(),
                    get_wbs: 'get_wbs'
                },
                dataType: "json",
                cache: false,
                success: function (response) {
                    let tkid = $(element).val();
                    $(`.peter-${site_id + tkid}`).html(response.table);
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

function get_subtasks_wbs(output_id, site_id, task_id, subtask_id) {
    $("#t_output_id").val(output_id);
    $("#t_site_id").val(site_id);
    $("#t_task_id").val(task_id);
    $("#t_subtask_id").val(subtask_id);
    // if (subtask_id != '' && site_id != '') {
    $.ajax({
        type: "get",
        url: "ajax/programsOfWorks/wbs",
        data: {
            get_wbs: "get_wbs",
            projid: $("#projid").val(),
            output_id: output_id,
            site_id: site_id,
            task_id: task_id,
            subtask_id: subtask_id
        },
        dataType: "json",
        success: function (response) {
            if (response.success) {
                $("#tasks_wbs_table_body").html(response.structure);
                var subtask = response.task;
                $("#subtask_start_date").html(response.start_date);
                $("#subtask_duration").html(response.duration);
                $("#subtask_end_date").html(response.end_date);
                $("#subtask_target").html(subtask.units_no + ' ' + subtask.unit);
                $("#subtask_name").html(subtask.task);
                $("#total_target").val(subtask.units_no);
            } else {
                error_alert("Error please try again later");
            }
        }
    });
    // }
}

function get_tasks(details) {
    var output_id = details.output_id;
    var task_id = details.task_id;
    var site_id = details.site_id;
    var edit = details.edit;
    $("#output_id").val(output_id);
    $("#site_id").val(site_id);
    $("#task_id").val(task_id);
    var projid = $("#projid").val();

    (edit == "1") ? $("#store_tasks").val(1) : $("#store_tasks").val(0);

    $.ajax({
        type: "get",
        url: ajax_url,
        data: {
            get_tasks: "get_tasks",
            projid: projid,
            output_id: output_id,
            task_id: task_id,
            site_id: site_id,
        },
        dataType: "json",
        success: function (response) {
            $("#tasks_table_body").html(response.tasks);
        }
    });
}

function validate_dates(task_id) {
    var project_start_date = $("#project_start_date").val();
    var project_end_date = $("#project_end_date").val();
    var today = $("#today").val();

    if (project_start_date != '' && project_end_date != '') {
        var start_date = $(`#start_date${task_id}`).val();
        if (start_date != "") {
            var d1 = new Date(start_date);
            var d2 = new Date(today);
            var d3 = new Date(project_start_date);
            var d4 = new Date(project_end_date);

            if (d3 <= d1 && d1 <= d4) {
                if (d2 > d1) {
                    $(`#start_date${task_id}`).val("");
                    error_alert("Please ensure task start date is greater today")
                }
                calculate_end_date(task_id)
            } else {
                $(`#start_date${task_id}`).val("");
                error_alert("Please ensure that start date is between contract dates");
            }
        } else {
            $(`#start_date${task_id}`).val("");
        }
    } else {
        $(`#start_date${task_id}`).val("");
    }
}

function calculate_end_date(task_id) {
    var project_end_date = $(`#project_end_date`).val();
    var start_date = $(`#start_date${task_id}`).val();
    var duration = $(`#duration${task_id}`).val();
    if (project_end_date != '') {
        if (start_date != "" && duration != "") {
            $.ajax({
                type: "get",
                url: ajax_url,
                data: {
                    compare_dates: "compare_dates",
                    project_end_date: project_end_date,
                    start_date: start_date,
                    duration: duration
                },
                dataType: "json",
                success: function (response) {
                    if (response.success) {
                        $(`#end_date${task_id}`).val(response.subtask_end_date);
                    } else {
                        $(`#duration${task_id}`).val("");
                        $(`#end_date${task_id}`).val("");
                        error_alert("Please ensure that end date is between contract dates");
                    }
                }
            });
        } else {
            $(`#duration${task_id}`).val("");
            $(`#end_date${task_id}`).val("");
        }
    } else {
        $(`#duration${task_id}`).val("");
        $(`#end_date${task_id}`).val("");
        error_alert('Project start date please check');
    }
}

function approve_project(details) {
    swal({
        title: "Are you sure?",
        text: `You want to submit program of works for ${details.project_name} !`,
        icon: "warning",
        buttons: true,
        dangerMode: true,
    })
        .then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    type: "post",
                    url: ajax_url,
                    data: {
                        approve_stage: "approve_stage",
                        projid: details.projid,
                        csrf_token: details.csrf_token,
                    },
                    dataType: "json",
                    success: function (response) {
                        if (response.success == true) {
                            swal({
                                title: "Project !",
                                text: "Successfully submitted project",
                                icon: "success",
                            });
                            setTimeout(function () {
                                window.location.href = redirect_url;
                            }, 3000);
                        } else {
                            swal({
                                title: "Project !",
                                text: "Error submitting project",
                                icon: "error",
                            });

                            setTimeout(function () {
                                window.location.reload(true);
                            }, 3000);
                        }
                    },
                });
            } else {
                swal("You cancelled the action!");
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

