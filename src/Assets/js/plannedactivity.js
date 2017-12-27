$(document).on("ready", function () {
    var $body = $("body");
    /*$body.on("click", ".activity_row", function () {
     $(".edit_activity").attr("href", $(".edit_activity").attr("data-href") + "/" + $(this).attr("data-recordid"));
     });*/

    $body.on("click", ".close-btn-task", function (e) {
        setViewByMode("#tab_activity");
        $(this).css('display', 'none');
        $("#add_activity_btn").show();
    });
    $body.on("click", ".tab_activity_save", function (e) {

        e.preventDefault();
        var url = base_url + "seminar-planner/activity";
        if (!$(".add_activity_form").valid()) {
            return false;
        }
        var actvity_click = $(this);

        var method = "POST";
        var activity_id = $("[name='TaskID']").val();
        if (activity_id != "") {
            method = "PUT";
            url += "/" + $("[name='TaskID']").val();
        }
        var additionalDataByPage = "&event_id=" + $(".eventID").val();


        $.ajax({
            url: url,
            method: method,
            data: $(".activity_form").find("*").serialize() + additionalDataByPage,
            beforeSend: function () {

                blockUI(".modal-body");
            },
            success: function (data) {
                console.log("success");
                unBlockUI(".modal-body");
                notify(data.type, data.message);

                if (data.type == "success") {
                    setViewByMode("#tab_activity");
                    $("#add_activity_btn").show();

                    $(".close-btn-task").css('display', 'none');
                    $clone_tr = $("#tab_activity .clone-activities-item").clone();
                    console.log(data);
                    if (activity_id == "") {
                        activity_array.push(data.taskObject.TaskID);
                        $(".actvity_count").html(parseInt($(".actvity_count").html()) + 1);

                        //$clone_tr = $("#tab_activity .clone-activities-item").clone();
                        $clone_tr.removeClass("clone-activities-item");
                        $clone_tr.addClass("activity_row_" + data.taskObject.TaskID);
                        $clone_tr.css("display", "block");
                        //$clone_tr.appendTo(".activities-item:last");
                        $("#tab_activity_info tbody").prepend($clone_tr);
                        $clone_tr.find("[data-index='TaskName']").html($("[name='TaskName']").val());
                        $clone_tr.find("[data-index='TaskNote']").html($("[name='TaskNote']").val());
                        $clone_tr.find("[data-index='TaskBegin']").html($("[name='TaskBegin']").val());
                        $clone_tr.find("[data-index='TaskStatusID']").html($("[name='TaskStatusID'] option:selected").text());

                        $clone_tr.find("[data-index='TaskEnd']").html($("[name='TaskEnd']").val());
                        $clone_tr.find(".delete_activity").attr('data-id', data.taskObject.TaskID);
                        $clone_tr.find(".edit_activity").attr('data-id', data.taskObject.TaskID);
                        $clone_tr.find(".activities-item").addClass("activity_row_" + data.taskObject.TaskID);

                        $("#tab_activity_info").prepend($clone_tr);

                    }
                    else {
                        //$(".activities-item").addClass('activity_row_'+activity_id);
                        var $updated_activity_row = $(".activity_row_" + data.taskObject.TaskID);

                        console.log($updated_activity_row.find("[data-index='TaskName']").html($("[name='TaskName']").val()));
                        $updated_activity_row.find("[data-index='TaskNote']").html($("[name='TaskNote']").val());
                        $updated_activity_row.find("[data-index='TaskStatusID']").html($("[name='TaskStatusID'] option:selected").text());
                        $updated_activity_row.find("[data-index='TaskBegin']").html($("[name='TaskBegin']").val());
                        $("#tab_activity_info tbody").prepend($updated_activity_row);
                    }
                    $(".activity_row.active-hr").find("td").each(function () {
                        if ($(this).attr("data-index")) {
                            $name = $(this).attr("data-index");
                            val = "";
                            if ($("[name='" + $name + "']").is("select")) {
                                val = $("[name='" + $name + "']").find("option:selected").html();
                            } else if ($name == "AssignedTo") {
                                if ($(".add_activity_form").find("[name='AssignedTo']:checked").val() != 1) {
                                    val = $(".add_activity_form").find("[name='AssignedToOther']").find("option:selected").html();
                                } else {
                                    val = $(".add_activity_form").find("[name='AssignedToOther']").find("option.current_user").html();
                                }
                            } else {
                                val = $("[name='" + $name + "']").val();
                            }
                            $(this).html(val);
                        }
                    })
                    $(".tooltips").tooltip();
                    $(".activity_row.active-hr").attr("data-recordid", data.taskObject.TaskID);
                    $("input[name='TaskID']").val('');
                }
            },
            error: function (request, errordata, errorObject) {
                var errors = request.responseJSON;
                var errorsHtml = '';
                $.each(errors, function (key, value) {
                    if (value[0] == 'validation.unique') {
                        errorsHtml += key + ' is already in database';
                    } else if (value[0] == 'validation.email') {
                        errorsHtml += key + ' is not in proper format';
                    } else {
                        errorsHtml += key + ' is required';
                    }
                });
                unBlockUI(".modal-body");
                notify('error', errorsHtml);
            },
        });
    });

    //Edit Activity Of that buton
    $body.on("click", ".edit_activity", function (e) {
        e.preventDefault();
        var href = $(this).attr('data-id');

        //var activityId = 0;
        //var activityId = $("[name='editActivityId']").val() != "" ? $('[name=editActivityId]').val() : 0;

        $.ajax({
            url: base_url + 'seminar-planner/activity/' + href,
            type: 'GET',
            beforeSend: function () {

                blockUI(".modal-body");
            },
            success: function (data) {
                unBlockUI(".modal-body");
                $("#add_activity_btn").hide();
                $("#tab_activity_form").html(data);
                setViewByMode("#tab_activity");
            },

        });
    });


    $body.on("click", "#add_activity_btn", function (e) {
        //  var href = $(this).attr('href');
        e.preventDefault();
        $("#add_activity_btn").hide();
        $(".close-btn-task").removeAttr('style');
        var activityId = 0;
        var person_id = $("[name='personId']").val();

        $.ajax({
            url: base_url + 'seminar-planner/activity/' + activityId,
            type: 'GET',
            beforeSend: function () {
                blockUI(".modal-body");
            },
            success: function (data) {
                unBlockUI(".modal-body");
                $("#tab_activity_form").html(data);
                setViewByMode("#tab_activity");
            },
        });
    });
    $body.on("click", ".delete_activity", function (e) {
        //  var href = $(this).attr('href');
        e.preventDefault();
        var taskId = $(this).data("id");

        var delete_actvity_click = $(this);

        bootbox.confirm({
            message: delete_activity,
            buttons: {
                'cancel': {
                    label: cancel_button,
                    className: 'btn-default pull-right'
                },
                'confirm': {
                    label: ok_button,
                    className: 'btn-primary pull-right'
                }
            },
            callback: function (response) {
                if (response == true) {
                    $.ajax({
                        url: base_url + 'seminar-planner/activity/' + taskId,
                        beforeSend: function () {
                            blockUI(".modal-body");
                        },
                        type: 'Delete',
                        success: function (data) {
                            unBlockUI(".modal-body");
                            notify(data.type, data.message);
                            $(".actvity_count").html(parseInt($(".actvity_count").html()) - 1);
                            delete_actvity_click.parents('.activities-item').remove();
                        },
                    });
                }
            }
        });


    });

});