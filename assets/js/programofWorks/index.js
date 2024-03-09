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
    });

    $('.tasks_id_header').each((index, element) => {
        var projid = $("#projid").val();
        if (projid != '') {
            $.ajax({
                type: "get",
                url: "ajax/programsOfWorks/get-wbs.php",
                data: {
                    projid: projid,
                    site_id: $(element).next().val(),
                    output_id: $(element).next().next().val(),
                    task_id: $(element).val(),
                    get_wbs: 'get_wbs'
                },
                dataType: "json",
                success: function (response) {
                    let tkid = $(element).val();
                    $(`.peter-${tkid}`).html(response.table);
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
                $("#subtask_target").html(subtask.units_no);
                $("#subtask_name").html(subtask.task);
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
    var start_date = $(`#start_date${task_id}`).val();
    var today = $("#today").val();
    if (start_date != "") {
        $(`#end_date${task_id}`).val("");
        var d1 = new Date(start_date);
        var d2 = new Date(today);
        if (d2 > d1) {
            $(`#start_date${task_id}`).val("");
            error_alert("Please ensure task start date is greater today")
        }
        calculate_end_date(task_id)
    } else {
        $(`#start_date${task_id}`).val("");
    }
}

function calculate_end_date(task_id) {
    var start_date = $(`#start_date${task_id}`).val();
    var duration = $(`#duration${task_id}`).val();

    var project_start_date = $(`#project_start_date`).val();
    var project_end_date = $(`#project_end_date`).val();

    if (project_start_date != '' && project_end_date != '') {
        var project_sdate = new Date(project_start_date).getTime();
        var project_edate = new Date(project_end_date).getTime();
        if (start_date != "" && duration != "") {
            duration = parseInt(duration);
            var today = new Date(start_date);
            var time = today.getTime();
            if (time >= project_sdate && time <= project_edate) {
                var end_date = new Date(today);
                end_date.setDate(today.getDate() + duration);
                var today = new Date(end_date).toISOString().split('T')[0];
                $(`#end_date${task_id}`).val(today);
            } else {
                $(`#duration${task_id}`).val("");
                $(`#end_date${task_id}`).val("");
            }
        } else {
            $(`#duration${task_id}`).val("");
            $(`#end_date${task_id}`).val("");
        }
    } else {
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
                    },
                    dataType: "json",
                    success: function (response) {
                        if (response.success == true) {
                            swal({
                                title: "Project !",
                                text: "Successfully submitted project",
                                icon: "success",
                            });
                        } else {
                            swal({
                                title: "Project !",
                                text: "Error submitting project",
                                icon: "error",
                            });
                        }
                        setTimeout(function () {
                            window.location.href = redirect_url;
                        }, 3000);
                    },
                });
            } else {
                swal("You cancelled the action!");
            }
        });
}
