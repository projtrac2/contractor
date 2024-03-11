const ajax_url = 'ajax/payment/index';
$(document).ready(function () {
    $("#invoice_div").hide();

    $("#modal_form_submit").submit(function (e) {
        e.preventDefault();
        var form = $('#modal_form_submit')[0];
        var form_data = new FormData(form);
        $("#modal-form-submit").prop("disabled", true);
        $.ajax({
            type: "post",
            url: ajax_url,
            data: form_data,
            processData: false,
            contentType: false,
            cache: false,
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    success_alert("Success!");
                } else {
                    sweet_alert("Error!");
                }
                $("#tag-form-submit").prop("disabled", false);
                setTimeout(() => {
                    window.location.reload(true);
                }, 3000);
            }
        });
    });
});

const get_details = (projid, payment_plan, project_name, contractor_number) => {
    $("#milestones").hide();
    $("#tasks").hide();
    $("#work_measured").hide();
    $("#payment_phase").removeAttr('required');
    $("#tasks_table").html(``);
    $("#milestone_table").html(``);
    $("#modal_form_submit").trigger("reset"); //reset form
    $("#invoice_div").hide();
    $("#projid").val(projid);
    $("#payment_plan").val(payment_plan);
    $("#project_name").val(project_name);
    $("#contractor_number").val(contractor_number);
    $("#complete").val(complete);
    $("#work_measured_table").html("");

    if (payment_plan == 1) {
        // milestones
        $("#milestones").show();
        $("#payment_phase").attr("required", 'required');
        $.ajax({
            type: "get",
            url: ajax_url,
            data: {
                get_payment_phases: 'get_payment_phases',
                projid: projid,
            },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    $("#payment_phase").html(response.payment_phases);
                } else {
                    error_alert("Sorry no record found");
                }
            }
        });
    } else if (payment_plan == 2) {
        // task based
        $("#tasks").show();
        $.ajax({
            type: "get",
            url: ajax_url,
            data: {
                get_task_based_tasks: 'get_task_based_tasks',
                projid: projid,
            },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    $("#tasks_table").html(response.tasks);
                    $("#subtotal").html(response.task_amount);
                    $("#requested_amount").val(response.task_amount);
                    $("#invoice_div").show();
                } else {
                    error_alert("Sorry no record found");
                }
            }
        });
    } else if (payment_plan == 3) {
        // work measured based
        $("#work_measured").show();
        $.ajax({
            type: "get",
            url: ajax_url,
            data: {
                get_work_measurement_based: 'get_work_measurement_based',
                projid: projid,
            },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    $("#work_measured_table").html(response.tasks);
                    $("#subtotal1").html(response.request_amount);
                    $("#requested_amount").val(response.task_amount);
                    $("#invoice_div").show();
                } else {
                    error_alert("Sorry no record found");
                }
            }
        });
    }
}

const get_payment_plan_milestones = () => {
    var payment_phase = $("#payment_phase").val();
    var projid = $("#projid").val();
    $("#invoice_div").hide();

    if (payment_phase != '') {
        $.ajax({
            type: "get",
            url: ajax_url,
            data: {
                get_payment_phase_milestones: 'get_payment_phase_milestones',
                projid: projid,
                payment_phase: payment_phase,
            },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    $("#milestone_table").html(response.milestones);
                    $("#amount_request").val(response.amount_request);
                    $("#request_amount").val(response.request_amount);
                    $("#request_percentage").val(response.request_percentage);
                    $("#requested_amount").val(response.request_amount);
                    $("#invoice_div").show();
                } else {
                    error_alert("Sorry no record found");
                }
            }
        });
    } else {
        error_alert('Please select payment phase');
    }
}

function set_details(details) {
    $("#project_name").val(details.project_name);
    $("#contractor_name").val(details.contractor_name);
    $("#contractor_number").val(details.contract_no);
    $("#request_id").val(details.request_id);
    $("#projid").val(details.projid);
    $("#payment_plan").val(details.payment_plan);
    $("#project_plan").val(details.project_plan);
    $("#stage").val(details.stage);

    $("#milestones").hide();
    $("#tasks").hide();
    var payment_plan = details.payment_plan;

    if (payment_plan == "1") {
        $("#milestones").show();
    } else if (payment_plan == "2") {
        $("#tasks").show();
    } else {
        console.log("payment plan for work measured");
    }
}

function get_more_info(request_id) {
    $("#project_approve_div").hide();
    $("#modal-form-submit").hide();
    var payment_plan = $("#payment_plan").val();
    if (request_id != "") {
        $.ajax({
            type: "get",
            url: ajax_url,
            data: {
                get_more_info: "get_more_info",
                request_id: request_id,
            },
            dataType: "json",
            success: function (response) {
                if (response.details.success) {
                    $("#comments_div").html(response.comments);
                    $("#attachment_div").html(response.attachment);
                    if (payment_plan == '1') {
                        $("#milestone_table1").html(response.details.milestones);
                        $("#amount_request").val(response.details.request_amount);
                        $("#request_percentage").val(response.details.request_percentage);
                        $("#requested_amount").val(response.details.request_amount);
                        $("#payment_phase").val(response.details.payment_plan);
                    } else {
                        $("#tasks_table").html(response.details.tasks);
                        $("#subtotal").html(response.details.task_amount);
                        $("#requested_amount").val(response.details.task_amount);
                    }
                } else {
                    // sweet_alert("No data found !!!")
                }
            }
        });
    } else {
        console.log("Ensure that the request is correct");
    }
}