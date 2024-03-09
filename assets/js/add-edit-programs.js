const url = "ajax/programs/programs";

$(document).ready(function () {
    hide_workplan();
    $('input:radio[name="progstrategyobjective"]').change(function () {
        if ($(this).val() == '1') {
            var get_inputs = "get_independent";
            $("#strat_div").hide();
            $("#strategic_objective").removeAttr("required");
            $("#strategic_objective").val("");
            $.ajax({
                type: "POST",
                url: url,
                data: get_inputs,
                dataType: "html",
                success: function (response) {
                    $("#form_slip").html(response);
                    $("#programworkplan").hide();
                }
            });
        } else if ($(this).val() == '0') {
            $("#strat_div").show();
            $("#strategic_objective").attr("required", "required");
            $("#form_slip").html("");
        }
    });

    hide_workplan(0);
});

function hide_workplan(param){
    if(param){
        $("#programhide").show();
    }else{
        $("#programhide").hide();
    }
}

function get_department() {
    var projsectorid = $("#projsector").val();
    if (projsectorid) {
        $.ajax({
            type: 'POST',
            url: url,
            data: 'getdept=' + projsectorid,
            success: function (html) {
                $('#projdept').html(html);
                $(".indicator").html('<option value="">... Select <?= $departmentlabel ?> First ... </option>');
            }
        });
    } else {
        $('#projdept').html('<option value="">... Select <?= $ministrylabel ?> First ... </option>');
        $(".indicator").html('<option value="">... Select <?= $departmentlabel ?> First ... </option>');
    }
}

function get_outputs() {
    var projdeptid = $("#projdept").val();
    if (projdeptid) {
        $.ajax({
            type: 'POST',
            url: url,
            data: 'getprogindicator=' + projdeptid,
            success: function (html) {
                $(".indicator").html(html);
            }
        });
    } else {
        $(".indicator").html('<option value="">... Select <?= $departmentlabel ?> First ... </option>');
    }
}

function get_plan() {
    var kraid = $("#strategic_objective").val();
    if (kraid) {
        $.ajax({
            type: "POST",
            url: url,
            data: "get_dependent=" + kraid,
            dataType: "html",
            success: function (response) {
                $("#form_slip").html(response);
                $("#programworkplan").hide();
            }
        });
    }
}