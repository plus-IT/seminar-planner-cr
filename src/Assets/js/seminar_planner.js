var lastJQueryTS = 0;// this is a global variable.
var selectedSeminarBluePrint = new Array();
var originalDragDate;
var originalDragDateObj;
var dayCalculation = new Array(); // For table display
var dateAdd = new Array(); // Final days array
var oldDates = new Array();// Old days before drag
var callbackForEventDrop;
var clickEventId;
var clickEvent;
var $source = "";
var html;
var participantListForSeminar; // Use for inform particiant on cancel and move seminar
var level2UserList; // Use for inform level2 users on cancel seminar
var trainerListForSeminar; // Use for inform trainer on cancel and move seminar
var locationListForSeminar; // Use for inform location on cancel and move seminar
var newDropDate = false; // used when new event is droped and scheduled on next available day
var saveClickCallback = false;

var currentDate;
$body = $("body");
$(document).ready(function () {

    FormWizard.init();
    loadSelect2WithMacro();
    $(".seminar-search-input").removeAttr('readonly');
    loadAutoCompleteCategoryList();
    loadAutoCompleteLocationList();
    loadAutoCompleteTrainerList();
    loadAutoCompleteSeminarPlannedList();
    getDashboardTaskEvent();
    setHeightToScroller();
    $("#infinite_scroll").niceScroll();
    var today_Date = moment().format(app_date_format_js.toUpperCase());
    $('#startOfDate').val(today_Date);
    $('#startOfDate').attr("placeholder", app_date_format_js);
    $('#endOfDate').attr("placeholder", app_date_format_js);
    $('#startOfDate').datepicker({
        rtl: Metronic.isRTL(),
        orientation: "left",
        autoclose: true,
        format: app_date_format_js,
        language: app_language,
        defaultDate: today_Date
    }).on('blur', function (selected) {
        var minDate = $('#startOfDate').val();
        var start_of_date = moment($("#startOfDate").val().split(".").reverse().join("-")).format('YYYY-MM-DD');
        $(".currentDate").val(start_of_date);
        $('#endOfDate').datepicker('setStartDate', minDate);
        $('#endOfDate').val(minDate);
    }).on('change', function (selected) {
        var minDate = $('#startOfDate').val();
        var start_of_date = moment($("#startOfDate").val().split(".").reverse().join("-")).format('YYYY-MM-DD');
        $(".currentDate").val(start_of_date);
        $('#endOfDate').datepicker('setStartDate', minDate);
        $('#endOfDate').val(minDate);
    });
    $('#endOfDate').datepicker({
        rtl: Metronic.isRTL(),
        orientation: "left",
        autoclose: true,
        format: app_date_format_js,
        language: app_language,
        startDate: $('#startOfDate').datepicker('getDate'),
        minDate: $('#startOfDate').datepicker('getDate'),
    });

    // $('#startOfDate').datepicker({
    //     rtl: Metronic.isRTL(),
    //     orientation: "left",
    //     autoclose: true,
    //     format: app_date_format_js
    // }).on('changeDate', function () {
    //     var temp1 = $(this).datepicker('getDate');
    //     var d1 = new Date(temp1);
    //     d1.setDate(d1.getDate() + 1);
    //     $('#endOfDate').datepicker({
    //         rtl: Metronic.isRTL(),
    //         orientation: "left",
    //         autoclose: true,
    //         format: app_date_format_js,
    //         startDate: d1
    //     });
    // });
    // $('.date-picker').datepicker({
    //     rtl: Metronic.isRTL(),
    //     orientation: "left",
    //     autoclose: true,
    //     format: app_date_format_js
    // });


    $body.on("click", ".person-filter", function (e) {
        e.preventDefault();
        $("#personFilter").slideToggle();
    });

    $body.on("click", ".multicheck", function () {
        $('input:checkbox').not(this).prop('checked', this.checked);
        PopulateSelectedCheckBox();
    });
    $body.on("click", ".checked_item", function () {
        PopulateSelectedCheckBox();
    });

    $(".seminar-search").off("click");
    $(".seminar-search").on("click", function () {

        url = base_url + "seminar-planner/getDetails";
        if (!$(this).hasClass("remove")) {
            url += "?search=" + $(".seminar-search-input").val();
        } else {
            $(".seminar-search-input").val("");
        }

        loadSeminars(url, "");
    });

    // sort functionalityby new design
    $body.on("click", ".custom-tabs li a", function (e) {
        e.preventDefault();
        var className = $(this).attr('data-target');
        className = className.replace('#', '');
        $("#save_button_class").removeClass();
        $("#send_email_training_materials").hide();
        console.log(className);
        if (className == 'tab_description') {
            $("#save_button_class").show();
            $("#save_button_class").html(save_description);
            $("#save_button_class").addClass('green btn_simply_green btn default ' + className + '_save');
        } else if (className == 'tab_activity') {
            $("#save_button_class").show();
            $("#save_button_class").html(save_activity);
            $("#save_button_class").addClass('green btn_simply_green btn default ' + className + '_save');
        } else if (className == 'tab_document') {
            $("#save_button_class").show();
            $("#save_button_class").html(save_document);
            $("#save_button_class").addClass('green btn_simply_green btn default ' + className + '_save');
        } else if (className == 'tab_training_materials') {

            $("#save_button_class").html(saveTrainingMaterials);
            $("#save_button_class").addClass('green btn_simply_green btn default ' + className + '_save');
            $("#save_button_class").hide();
            setTimeout(function () {
                console.log("inside dir");
                if ($(".order_no").text().trim() != "" || $(".order_name").text().trim() != "") {
                    console.log("inside training butoon")
                    $("#send_email_training_materials").show();
                    $("#send_email_training_materials").attr('emailtosend', $('.order_email').val());
                }
            }, 300);
        } else {
            $("#save_button_class").hide();

        }

    });



    $body.on("click", ".tab_training_materials_save", function (e) {
        var event_id = $("[name=eventID]").val();
        //  If add reset opration buttun to the detail tab
        var url = base_url + "seminar-planner/save_training_materials/" + event_id;

        $.ajax({
            method: "PUT",
            url: url,
            beforeSend: function () {
                blockUI(".page-container");
            },
            data: $(".add_description_form").find('*').serialize(),
            success: function (data) {
                unBlockUI(".page-container");
                $("#tab_training_materials").html("");
                Layout.initJsStuff();
                setViewByMode("#tab_detail_form");
                notify(data.type, data.message);
                $("#send_email_training_materials").show();
                $(".training_materials_li a").trigger("click");

                $("#save_button_class").hide();


            }
        });
    });
    $body.on("click", "#tab_training_materials_edit_btn_planner", function (e) {
        e.preventDefault();
        var event_id = $("[name=eventID]").val();
        //  If add reset opration buttun to the detail tab
        var url = base_url + "seminar-planner/add_training_materials/" + event_id;

        $.ajax({
            method: "GET",
            url: url,
            beforeSend: function () {
                blockUI(".page-container");
            },
            success: function (data) {
                unBlockUI(".page-container");
                if (data.type != 'error') {
                    $("#tab_training_materials").html(data);
                    setViewByMode("#tab_training_materials");
                    $('#send_email_training_materials').hide();
                    $("#save_button_class").show();
                } else {
                    notify(data.type, data.message);
                }

            }
        });
    });

    $body.on("click", "#is_deploy_internet", function (e) {
        e.stopPropagation();
        if ($(this).is(':checked')) {
            $(".if_deploy").removeAttr('style');
            $("#show_vacant_seats").val('1');
            $("#show_vacant_seats").attr('checked', 'checked');

        } else {
            $(".if_deploy").css('display', 'none');
            $("#show_vacant_seats").val('0');
            $("#show_vacant_seats").removeAttr('checked');
        }
        e.start
    });


    $body.on("change", "#form_id", function (e) {
        var form_id = $('#form_id').val();
        var eventID = $(".eventID").val();
        var min_registration = $('#min_registration').val();
        var max_registration = $('#max_registration').val();
        var external_id = $('.external_id_save').val();
        var additionalData = '?min_registration=' + min_registration + '&max_registration=' + max_registration + '&external_id=' + external_id + '&form_id=' + form_id;
        setSeminarPlannerData(eventID, additionalData);
    });

    $body.on("blur", "#max_registration , #min_registration, .external_id_save, #form_id", function (e) {
        var eventID = $(".eventID").val();
        var min_registration = $('#min_registration').val();
        var max_registration = $('#max_registration').val();
        var external_id = $('.external_id_save').val();
        var totalAttendees = $('#totalAttendees').val();
        var form_id = $('#form_id').val();
        var additionalData = '?min_registration=' + min_registration + '&max_registration=' + max_registration + '&external_id=' + external_id + '&form_id=' + form_id;
        if (parseInt(max_registration) < parseInt(totalAttendees) || parseInt(max_registration) == 0) {
            $('#max_registration').val(totalAttendees);
            max_registration = $('#max_registration').val();
        }
        if (parseInt(max_registration) < parseInt(min_registration)) {
            $(this).focus();
            notify('error', minMaxErrorMsg);
            return false;
        } else {
            setSeminarPlannerData(eventID, additionalData);

        }
    });

    $body.on("click", ".seminar_details_for_portal", function (e) {

        setTimeout(function () {

            var eventID = $(".eventID").val();
            var str = $('.online_portal_details input:not([type="checkbox"])').serialize();
            var str1 = $(".online_portal_details  input[type='checkbox']").map(function () {
                var me = this;
                var vl = (this.checked) ? $(me).attr('value') : "";
                return this.name + "=" + vl;
            }).get().join("&");

            if (str1 != "" && str != "")
                str += "&" + str1;
            else
                str += str1;

            $.ajax({
                url: base_url + 'seminar-planner/savePlanned/' + eventID,
                method: "post",
                data: str,
                beforeSend: function (data) {
                    blockUI(".modal-content");
                },
                success: function (data) {
                    unBlockUI(".modal-content");
                    $("#calendar").fullCalendar('refetchEvents');
                }
            });
        }, 1000);
    });

    $body.on("click", ".sort-fields", function (e) {
        e.preventDefault();
        $(".sort-wrapper li").removeClass("active");
        var sort_order = $(this).attr('sort-order');
        $(this).attr('sort-order', (sort_order == "ASC" ? "DESC" : "ASC"));
        $(this).parent('li').addClass('active');
        var Data = generateFilterURL();
        if (Data != '' && Data != false) {
            url = base_url + "seminar-planner/getDetails" + Data;
            pageNo = 2;
            loadSeminars(url, "");
        }
    });
    $body.on("click", ".edit_seminar", function (e) {
        e.preventDefault();
        var id = $(this).attr('id');
        $.ajax({
            url: base_url + "seminar-planner/getDetailForm/" + clickEventId,
            method: 'get',
            beforeSend: function (data) {
                blockUI(".page-container");
            },
            success: function (data) {
                unBlockUI(".page-container");
                $("#edit_seminar .editSeminarData").html(data);
                initEditors();
                initDatePicker();
                $("#edit_seminar").modal("show");
                $(".tooltips").tooltip();
                $('[data-target=#tab_description]').trigger('click')
            }
        });
    });

    $('#edit_seminar').on('hidden.bs.modal', function () {
        destroyEditors();
    });
    $('#seminarCancellation').on('hidden.bs.modal', function () {
        $("[name='cancelReason']").val("");
    });

    $body.on("mouseenter", ".getPopOverHere", function (e) {
        var current = $(this);
        $(this).css('cursor', 'pointer');
        $(this).find('#popover').popover({
            placement: "bottom",
            trigger: "hover"
        });
    });

    $body.off("click", ".schedule_delete");
    $body.on("click", ".schedule_delete", function (e) {
        e.preventDefault();
        var $me = $(this);
        var $href = $(this).attr('data-id');
        var $event_id = $('.eventID').val();
        bootbox.confirm({
            message: schedule_delete,
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
                        url: base_url + 'seminar-planner/schedule/' + $href,
                        type: 'Delete',
                        data: {'event_id': $event_id},
                        beforeSend: function () {
                            blockUI(".modal-content");
                        },
                        success: function (data) {
                            unBlockUI(".modal-content");
                            if (data && data.type == "success") {
                                notify(data.type, data.message);
                                var $schedule_count = $('.schedule_count').attr('data-count');
                                if ((parseInt($schedule_count) - 1) >= 0) {
                                    $(".schedule_count").html(schedule + ' (' + (parseInt($schedule_count) - 1) + ')');
                                    $('.schedule_count').attr('data-count', (parseInt($schedule_count) - 1));
                                } else {
                                    $(".schedule_count").html('(0)');
                                    $('.schedule_count').attr('data-count', '0');
                                }

                                $("#event_startdate").val(data.event_start_date);
                                $("#event_enddate").val(data.event_end_date);

                                $me.parents(".panel-default").remove();

                                $("#calendar").fullCalendar('refetchEvents');
                            } else {
                                notify("error", somethingWentWrong);
                            }

                            // Update schedule days number
                            //updateScheduleDays();
                        },
                    });
                }
            }
        });
    });
    $body.off("change", "[name='LocationID']");
    $body.on("change", "[name='LocationID']", function (e) {
        $locationId = $(this).val();
        $.ajax({
            url: base_url + 'getRooms/Location/' + $locationId,
            type: 'GET',
            beforeSend: function () {
            },
            success: function (result) {
                var optionList = "<option></option>";
                if (result.data.location_room.length > 0) {
                    $.each(result.data.location_room, function (i, v) {
                        optionList += "<option value=" + v.room.RoomID + ">" + v.room.RoomName + "</option>"
                    });
                    $(".roomId").html(optionList);

                    // Notify if user changes location selected rooms for already lot becomes null
                    if ($(".scheduleSlotList .panel-default").length > 0)
                        notify("warning", changeLocationSlotRoomWarning);
                    $("[name='roomId[]']").html(optionList);
                }
            }
        });

    })
    $body.off("click", ".schedule_edit,.schedule_duplicate");
    $body.on("click", ".schedule_edit,.schedule_duplicate", function (e) {
        var $href = $(this).attr('data-id');
        var current_btn = $(this);
        var event_id = $('.eventID').val();
        var status = '';
        if (current_btn.hasClass("schedule_duplicate"))
            status = 'duplicate';
        else
            status = 'edit';
        $.ajax({
            url: base_url + 'seminar-planner/schedule/' + $href + "?eventID=" + event_id + "&status=" + status,
            type: 'GET',
            beforeSend: function () {

                blockUI(".page-container");
            },
            success: function (data) {
                //console.log(data);
                unBlockUI(".page-container");
                $('#scheduleModal .modal-body').html($(data).find('#scheduleModal .modal-body'));
                var schedule_model = $('#scheduleModal').find('.modal-title');
                var $eventName = $('.event-details').find('.event_name').text().trim();
                countSlots = $('.add_schedule_form').find('.slot').length;
                if (current_btn.hasClass("schedule_duplicate")) {
                    console.log("In duplicate function");
                    $("[name='ScheduleID']").val('0');
                    $("[name='model_mode']").val('1');
                    $("[name='slot_id[]']").each(function () {
                        $(this).val('');
                    })
                    $(".time_slot_chk").each(function () {
                        $(this).css('display', 'block');
                        $(this).find("[name='time_slot_chk[]']").prop('checked', true);
                    })
                    schedule_model.text(duplicate_schedule + ' | ' + $eventName);
                } else {
                    $("[name='model_mode']").val('0');
                    schedule_model.text(edit_schedule + ' | ' + $eventName);
                }
                initDatePicker();
                initSelectDropDownForEditDuplicate();
                initSelectDropDown();
                initScheduleDefaultSelectDropDown();

                // Crate array for the already added slots
                prepareSlotArray();
                // get next slot start and end time
                getNextSlotTimeRange();
                $('#scheduleModal').modal('show');

            }
        });
    });

    $body.on('shown.bs.modal', '#scheduleModal', function () {

        $(".tooltips").tooltip();
        $(this).find('.modal-body').css({
            width: 'auto', //probably not needed
            height: 'auto', //probably not needed
            'max-height': '100%'
        });

    });

    $body.off("click", ".addNewSlotDetails");
    $body.on("click", ".addNewSlotDetails", function (e) {
        var startTime = $(".schedule_add_slot").find('.start_time').val();
        var endTime = $(".schedule_add_slot").find('.end_time').val();
        var roomId = $(".schedule_add_slot").find('.roomId').val();
        var roomName = $(".schedule_add_slot").find('.roomDropDownText').val();
        var slotTitle = $(".schedule_add_slot").find('#slotTitle').val();
        // check the validation
        if (startTime == "" || endTime == "" || $("input.scheduleTrainers ").val() == "" || $("input#slotTitle").val() == "") {
            notify("error", fillTheRequriedFields, "");
            return false;
        }

        /*
         startTimeForCalculation = endTimeForCalculation = convert any time formate to 24 hrs and use for all the calculation like chk duplication and fetch next time
         */
        var startTimeForCalculation = moment(startTime, [app_time_format_moment_js]).format("HH:mm");
        var endTimeForCalculation = moment(endTime, [app_time_format_moment_js]).format("HH:mm");

        /*
         startTimeForDisplay = endTimeForDisplay = User to display time formate based on the formate choose on setting page
         */

        var startTimeForDisplay = moment(startTime, [app_time_format_moment_js]).format(app_time_format_moment_js);
        var endTimeForDisplay = moment(endTime, [app_time_format_moment_js]).format(app_time_format_moment_js);

        if (startTimeForCalculation >= endTimeForCalculation) {
            notify("error", time_error_schedule_slot, "");
            return false;
        }

        var newSlot = new Object();
        newSlot.start = startTimeForCalculation;
        newSlot.end = endTimeForCalculation;
        newSlot.roomId = roomId != "" ? roomId : roomName;
        newSlot.trainers = $("input.scheduleTrainers").val();
        newSlot.panelClass = "schedule_slot_panel_" + countSlots;
        newSlot.hasConflict = false;
        //slotsArray.push(newSlot);

        //check if there is the conflict with the other slot
        for (var i = 0; i < slotsArray.length; i++) {
            for (var j = 0; j < slotsArray.length; j++) {
                // if it is not the same event
                checkSlotConflit(newSlot, slotsArray[j]);
            }
        }
        //if conflict the notifiy user and highlight the slot
        conflictEvent = $.grep(slotsArray, function (e) {
            return e.hasConflict == true;
        });

        if (conflictEvent.length > 0) {
            notify("error", slotConflictMessage, "");
            $(".schedule_slot_panel_" + conflictEvent.panelClass).css("border-color", "red");
            isConflict = true;
            return false;
        } else {
            //$(".schedule_slot_panel_" + conflictEvent[0].panelClass).css("border-color","#e0e0e0");
            isConflict = false;
            conflictEvent = {};
            //return false;
        }

        // if there is no conflict add that into slot array
        slotsArray.push(newSlot);

        // Clone the Slote panel for the accordian
        var $cloneSlotDiv = $(".cloneSlotListPanel").clone().css("display", "block");
        $cloneSlotDiv.removeClass("cloneSlotListPanel");

        // Assign a class to root panel div
        $cloneSlotDivClass = "schedule_slot_panel_" + countSlots;
        $cloneSlotDiv.addClass($cloneSlotDivClass);
        $cloneSlotDiv.attr('id', $cloneSlotDivClass);

        // Create string for the id and href for the accordian link and content
        $cloneSlotDetailsClassName = "schedule_slot_panel_new_" + countSlots;


        // Clone the add slot div and append as accordian panel content
        $trainers = "";
        $trainersIds = $("input.scheduleTrainers").val().split(",");
        $trainersArray = new Array();

        $(".scheduleTrainers").prev('div').find('.select2-search-choice').each(function ($i, $el) {
            var obj = new Object();
            obj.id = $trainersIds[$i];
            obj.text = $(this).find('div').text();
            $trainersArray.push(obj);
            $trainers += $(this).find('div').text() + ', ';
        });
        var $trainerData = JSON.stringify($trainersArray);
        $cloneSlotDiv.find(".trainer_data").val($trainerData).attr("id", "trainer_data_" + countSlots);

        // Add new object to array of slots for conflict

        $trainerName = $trainers.trim().slice(0, -1);
        $trainers = "";


        var $cloneSlotDetails = $(".schedule_add_slot #addScheduleSlotPanel .slotWrapper").clone();
        $cloneSlotDetails.removeClass("schedule_add_slot");

        console.log($cloneSlotDetails.first());

        $cloneSlotDetails.addClass('slot');
        $cloneSlotDetails.find(".addNewSlotDetails").removeClass("addNewSlotDetails").addClass("saveSlotDetails").removeClass("pull-left").addClass("pull-right").attr('data-parentdivid', $cloneSlotDivClass).find("i").attr('class', 'fa fa-arrow-right');
        $cloneSlotDiv.find(".panel-collapse").attr("id", $cloneSlotDetailsClassName).append($cloneSlotDetails.first());
        $cloneSlotDiv.find(".accordion-toggle").attr("href", "#" + $cloneSlotDetailsClassName);

        console.log($cloneSlotDetails);

        // Set name for the slot field
        $cloneSlotDetails.find(".start_time").attr("name", "start_time[]");
        $cloneSlotDetails.find(".end_time").attr("name", "end_time[]");
        $cloneSlotDetails.find(".roomId").attr("name", "roomId[]").val(roomId);
        $cloneSlotDetails.find("#description").attr("name", "description[]");
        $cloneSlotDetails.find("#slotTitle").attr("name", "title[]");
        $cloneSlotDetails.find("#slotCustomRoomName").attr("name", "slotRoomName[]");


        console.log($cloneSlotDetails.find(".start_time").val());
        // Take value from the fields to display into header of the accordian
        $trainers = "";
        //$time_slot = ( ("0" + $cloneSlotDetails.find(".start_time").val()).slice(-5)) + ' - ' + (("0" + $cloneSlotDetails.find(".end_time").val()).slice(-5));
        $time_slot = startTimeForDisplay + ' - ' + endTimeForDisplay;


        // Create new select2 for trainers
        $scheduleTrainersClass = "scheduleTrainers_" + countSlots;
        // destroy select 2 of add slot
        $cloneSlotDetails.find(".select2-container").remove();
        $cloneSlotDetails.find(".scheduleTrainers").removeClass("scheduleTrainers").addClass($scheduleTrainersClass).attr("name", "trainer[]");
        // Delete clear all button
        $cloneSlotDetails.find(".clearSlotDetails").remove();


        //Set the header for the accordian
        $cloneSlotDiv.find("[data-index='slot_time']").html($time_slot);
        $cloneSlotDiv.find("[data-index='slot_trainer']").html($trainerName);
        $cloneSlotDiv.find("[data-index='slot_title']").html(slotTitle);
        $cloneSlotDiv.find("[data-index='slot_room']").html(roomName);

        // append the panel into panel group

        //$cloneSlotDiv.appendTo(".scheduleSlotList .panel-group");
        $(".scheduleSlotList .schedulListPanelGroup").append($cloneSlotDiv);


        //initSelectDropDown();

        // Reset the add sslot form
        var $addSlotForm = $(".schedule_add_slot");

        // set start and end time value based on the avaiable slot
        getNextSlotTimeRange();

        $addSlotForm.find("#description").val("");
        $addSlotForm.find("#slotTitle").val("");

        // If schedule type default is selected then no need to clear
        if ($("[name='scheduleType']:checked").val() != 0) {
            console.log("In empry condotion");
            $("#scheduleTrainers").select2("destroy");
            $addSlotForm.find("#scheduleTrainers").val("");
            $addSlotForm.find(".roomDropDownText").val("");
            $addSlotForm.find(".roomId").val("");
            initSelectDropDown();
        }


        setTimeout(function () {
            initSelectDropDownForEditDuplicate();
            initDatePicker();
        }, 1000)

        $(".tooltips").tooltip();
        notify("success", slotAddedSuccessMessage);

        // if(saveClickCallback == true){
        //     // make click on save button again to save all
        //     $('.save_schedule').trigger('click');
        // }

        countSlots += 1;

    });

    $body.off("click", ".tab_description_save,.save_address_data");
    $body.on("click", ".tab_description_save,.save_address_data", function (e) {
        var event_id = $(".plannedID").val();
        //  If add reset opration buttun to the detail tab
        var url = base_url + "seminar-planner/savedescription/" + event_id;
        if (CKEDITOR.instances.requirements_editor) {
            $("[name='requirements']").val(CKEDITOR.instances.requirements_editor.getData());
        }
        if (CKEDITOR.instances.content_editor) {
            $("[name='content']").val(CKEDITOR.instances.content_editor.getData());
        }
        if (CKEDITOR.instances.overview_editor) {
            $("[name='overview']").val(CKEDITOR.instances.overview_editor.getData());
        }
        if (CKEDITOR.instances.target_group_editor) {
            $("[name='target_group']").val(CKEDITOR.instances.target_group_editor.getData());
        }
        $.ajax({
            method: "PUT",
            url: url,
            beforeSend: function () {
                blockUI(".modal-content");
            },
            data: $(".add_description_form").find('*').serialize(),
            success: function (data) {
                unBlockUI(".modal-content");
                notify(data.type, data.message);
            }
        });
    });
    $body.off("click", ".add_schedule");
    $body.on("click", ".add_schedule", function (e) {
        e.preventDefault();
        var additional = "?eventID=" + $(".eventID").val();
        $.ajax({
            url: base_url + 'seminar-planner/schedule/0' + additional,
            type: 'GET',
            beforeSend: function () {
                blockUI(".modal-content");
            },
            success: function (data) {
                unBlockUI(".modal-content");
                $('#scheduleModal .modal-body').html($(data).find('#scheduleModal .modal-body'));
                initDatePicker();
                $('#scheduleModal').modal('show');

                // Calculate the slots and assign the next slot count
                countSlots = $('.add_schedule_form').find('.slot').length;
                // reset the slots array
                slotsArray = [];

                //set mode as add new add_schedule
                $("[name='model_mode']").val('0');

                //$(".schedule_slot").niceScroll();
                var $eventName = $('.event-details').find('.event_name').text().trim();
                $('#scheduleModal').find('.modal-title').text(add_schedule + ' | ' + $eventName);
                initSelectDropDown();
                initScheduleDefaultSelectDropDown();
            }
        });
    });
    $body.on('change', ".getPlanPeriod", function (e) {
        e.preventDefault();
        var send = true;
        var checkStartDate = $("#startOfDate").val();
        var checkEndDate = $("#endOfDate").val();
        console.log("checkStartDate=>", checkStartDate);
        if (checkStartDate != '' && checkEndDate != '') {
            var Data = generateFilterURL(true);
            console.log("data", Data);
            if (Data != '' && Data != false) {
                url = base_url + "seminar-planner/getDetails" + Data;
                loadSeminars(url, "");
            }
        }
    });

    $body.on('click', ".search-button", function (e) {
        pageNo = 1;
        var Data = generateFilterURL();
        if (Data != '' && Data != false) {

            url = base_url + "seminar-planner/getDetails" + Data;
            loadSeminars(url, "");
        }
    });

    var timeout = null;
    $body.on('keyup', ".seminar-search-input", function (e) {
        clearTimeout(timeout)
        timeout = setTimeout(function () {
            pageNo = 1;
            var Data = generateFilterURL();
            if (Data != '' && Data != false) {
                url = base_url + "seminar-planner/getDetails" + Data;
                loadSeminars(url, "");
            }
        }, 500)
    });


    $body.on('click', '.exportplannedSeminar', function (e) {
        $.ajax({
            url: base_url + 'seminar-planner/exportlist',
            method: 'get',
            beforeSend: function (data) {
                blockUI('.page-container');
            },
            success: function (data) {
                unBlockUI('.page-container');
                $(".export_seminar_data").html(data);
                $("#export_xml").modal('show');

            }
        });
    });


    $('#export_xml').on('shown.bs.modal', function () {
        console.log('sdsd');
        $('.date-picker').datepicker({
            rtl: Metronic.isRTL(),
            orientation: "left",
            autoclose: true,
            format: app_date_format_js,
            language: app_language
        });
        $(".seminarCategoryForExport").select2({
            allowClear: true
        });
        $('#startOfDateExport').datepicker({
            rtl: Metronic.isRTL(),
            orientation: "left",
            autoclose: true,
            format: app_date_format_js,
            language: app_language
        }).on('changeDate', function (selected) {
            var minDate = new Date(selected.date.valueOf());

            var start_of_date = moment($("#startOfDateExport").val().split(".").reverse().join("-")).format('YYYY-MM-DD');
            $(".currentDate").val(start_of_date);
            $('#endOfDateExport').datepicker('setStartDate', minDate);
        });
        $('#endOfDateExport').datepicker({
            rtl: Metronic.isRTL(),
            orientation: "left",
            autoclose: true,
            format: app_date_format_js,
            language: app_language,
            startDate: $('#startOfDateExport').datepicker('getDate'),
            minDate: $('#startOfDateExport').datepicker('getDate'),
        });
    });

    $body.on("click", ".multi_check", function () {
        $('.single_check').not(this).prop('checked', this.checked);
    });

    $body.on("click", ".export_to_xml", function () {
        var atLeastOneIsChecked = $('.table input:checkbox').is(':checked');
        if (atLeastOneIsChecked) {
            $.ajax({
                url: base_url + 'seminar-planner/export',
                method: 'post',
                data: $(".export_form_seminar").find('*').serialize(),
                beforeSend: function (data) {
                    blockUI('.modal-content');
                },
                success: function (data) {
                    unBlockUI('.modal-content');
                    notify(data.type, data.message);
                    $("#export_xml").modal('hide');
                    downloadURI(data.file_path, data.file_name);
                }
            });
        } else {
            notify('error', 'Please Select required exported Field');
        }
    });
    $body.on("click", ".get_filter_reset", function (e) {
        $("#SeminarCategoryID").select2("val", "");
        $("#CompanyMainContactID").select2("val", "");
        $("#SeminarTrainerId").select2("val", "");
        $("input#SeminarPlannedBy").select2("val", "");
        $('.seminar_planner_type').val('');
        window.setTimeout(function () {
            $('#SeminarCategoryID').trigger('change');
        }, 1000);


    });

    $body.on("change", "#CompanyMainContactID,#SeminarCategoryID,#SeminarTrainerId,.seminar_planner_type,#SeminarPlannedBy", function (e) {
        e.preventDefault();

        var Data = generateFilterURL();


        if (Data != false) {
            url = base_url + "seminar-planner/getDetails" + Data;
            loadSeminars(url, "");
        }

    });

    $body.on('click', '.filter-close', function (e) {
        e.preventDefault();
        $(this).parent().hide();
    });

    $body.on("click", ".open_modal_selected_seminar", function () {
        //alert($(this).attr('class'));
        $.ajax({
            url: base_url + "seminar-planner/getselectedSeminar?seminarId=" + $('#selected_seminar').val(),
            method: "get",
            beforeSend: function () {
                blockUI('.page-container');
            },
            success: function (data) {
                unBlockUI('.page-container');
                $(".get_seminar_selected .seminar_selected_form").html(data);
                $(".get_seminar_selected").modal("show");
            }
        });
    });
    $body.off("click", ".slot_delete");
    $body.on("click", ".slot_delete", function (e) {
        e.preventDefault();
        var slotPanelId = $(this).parents('.panel-default').attr("id");
        console.log($(this).parents('.panel-default'));
        var $length = $(".scheduleSlotList").find(".panel-default").length;
        var current = $(this);
        var slotId = current.data("id");
        if ($length > 1) {
            bootbox.confirm({
                message: slotDeleteConfirmation,
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
                        if (slotId) {
                            $.ajax({
                                url: base_url + 'seminar-planner/scheduleSlot/delete/' + slotId,
                                type: 'GET',
                                beforeSend: function () {
                                    blockUI(".modal-content");
                                },
                                success: function (data) {
                                    current.parents(".panel-default").remove();
                                    unBlockUI(".modal-content");
                                    notify(data.type, data.message);
                                    removeSlotFromArray(slotPanelId);
                                }
                            });
                        } else {
                            current.parents(".panel-default").remove();
                            notify("success", scheduleSlotDelete);
                            removeSlotFromArray(slotPanelId);
                        }
                    }
                }
            });

        } else {
            notify('error', delete_schedule_slot);

        }
    });

    // Clear add new slot details
    $body.off("click", ".clearSlotDetails");
    $body.on("click", ".clearSlotDetails", function () {
        $(".schedule_add_slot").find("#description").val("");
        $(".schedule_add_slot").find("#slotTitle").val("");
        $(".schedule_add_slot").find("#slotCustomRoomName").val("");
        $(".schedule_add_slot").find(".scheduleTrainers").val("");
        $(".schedule_add_slot").find(".roomId").val("");
        $(".schedule_add_slot").find(".trainer_div").select2("destroy");
        initSelectDropDown();
        getNextSlotTimeRange();
        if (isConflict) {
            isConflict = false;
        }
    });


    $body.off("click", ".save_schedule");
    $body.on("click", ".save_schedule", function (e) {
        e.preventDefault();

        var url = base_url + "seminar-planner/schedule";

        saveClickCallback = false;
        if ($('.trainer_div:last').val() != '' && $(".add_schedule_slot_panel #slotTitle").val() != "" && !isConflict) {
            // save the current slot
            // saveClickCallback = true;
            // $('.addNewSlotDetails').trigger('click');
            //return false;
        }

        if (!$(".add_schedule_form").valid()) {
            notify("error", incomplete_message);
            return false;
        }
        var method = "POST";
        var schedule_id = $("[name='ScheduleID']").val();
        if (schedule_id != "0") {
            method = "PUT";
            url += "/" + schedule_id;
        }
        var additionalDataByPage = "";
        if ($('.eventID').val() != '')
            additionalDataByPage = "&EventID=" + $('.eventID').val();

        // check if add slot is valid or form is filled or not
        $(".schedule_add_slot input").each(function () {
            if ($(this).val() != "") {
                $(this).addClass("fill");
            }
        });
        if ($(".schedule_add_slot").find('.fill').length == 3) {
            // $(".addNewSlotDetails").trigger("click");
        }

        if ($(".scheduleSlotList .panel-group .panel-default").length > 0) {
            ajaxForAddScheduleWithValidation(url, method, additionalDataByPage, schedule_id)
        } else {
            notify("error", delete_schedule_slot);
        }

    });

    $body.off("click", ".slot_duplicate");
    $body.on("click", ".slot_duplicate", function (e) {
        e.preventDefault();
        // Next count value from the current slot list
        countSlots = $('.add_schedule_form').find('.slot').length;

        $(this).parents(".panel-default").find(".trainer_div").select2("destroy");
        var $slotCloneDiv = $(this).parents(".panel-default").clone();
        $slotCloneDiv.attr("class", "panel panel-default schedule_slot_panel_" + countSlots);
        $slotCloneDiv.attr("id", "schedule_slot_panel_" + countSlots);
        $slotCloneDiv.find(".panel-title").find('input[type="hidden"]').attr("id", "trainer_data_" + countSlots);
        $slotCloneDiv.find(".accordion-toggle").attr("href", "#schedule_slot_" + countSlots);
        $slotCloneDiv.find(".panel-collapse").attr("id", "schedule_slot_" + countSlots);
        $slotCloneDiv.find(".saveSlotDetails").attr("data-parentdivid", "schedule_slot_panel_" + countSlots);
        $slotCloneDiv.find("input[type='hidden'][name='trainer[]']").attr("class", "form-control trainer_div required select2-offscreen scheduleTrainers_" + countSlots);

        // Calulate next slot timing
        setNextSlotTimeRageOnDuplicate($slotCloneDiv);

        $(".scheduleSlotList .panel-group").append($slotCloneDiv);

        $(this).parents(".panel-default").find('.collapse.in').collapse('hide');

        initDatePicker();
        initSelectDropDownForEditDuplicate();
        $(".tooltips").tooltip();
        notify("success", slotDuplicateSuccessMessage);

    });
    $body.on("click", ".close_schedule_modal", function (e) {
        e.preventDefault();
        $("#scheduleModal").modal('hide');
    });
    // Change the slots view on click of schedule list
    $body.on("click", ".itemSchedule", function () {
        var schedule = $.tmplItem(this).data;
        // make this schedule as selected
        $(".itemSchedule").removeClass("currentActiveSchedule");
        $(this).addClass("currentActiveSchedule")
        $(".slotList").empty();
        // append schedule days
        $("#slotTemplate")
                .tmpl(schedule.schedule.event_schedule_slot)
                .appendTo(".slotList");

        $(".itemSlot").droppable({
            accept: ".itemTrainer, .itemRoom",
            classes: {
                "ui-droppable-active": "ui-state-active",
                "ui-droppable-hover": "ui-state-hover"
            },
            drop: function (event, ui) {
                var dragElement = $.tmplItem(ui.draggable[0]).data;
                var dropElement = $.tmplItem(this).data;

                if ($(ui.draggable[0]).hasClass("itemRoom")) {
                    var locationId = $(ui.draggable[0]).attr("locatonid");
                    var roomId = $(ui.draggable[0]).attr("roomid");
                    var currentLocationId = $.tmplItem($(".currentActiveSchedule")).data;
                    if (locationId == currentLocationId.schedule.LocationID)
                        assignRoomToSlot(roomId, dropElement);
                    else
                        notify("error", errorSameLocation);
                } else if ($(ui.draggable[0]).hasClass("itemTrainer")) {
                    assignTrainerToSlot(dragElement, dropElement);
                }
                event.preventDefault();

            }
        });

        $('[data-toggle="popover"]').popover();
    });

    // Remove trainer from slot
    $body.on("click", ".slotTrainerBox", function () {
        $trainerId = $(this).attr("id");
        $slot = $.tmplItem($(this).parent(".itemSlot")).data;
        $schedule = $.tmplItem($(".currentActiveSchedule")).data;
        var scheduleDay = $(".currentActiveSchedule").attr('scheduleday');
        bootbox.confirm({
            message: removeTrainerConfirmationMessage,
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
                        url: base_url + "seminar-planner/remove-trainer/" + $schedule.schedule.id + '/' + $slot.schedule_slotID + "/" + $trainerId.trim() + "?eventId=" + $schedule.event_id,
                        method: "GET",
                        beforeSend: function () {
                            blockUI(".modal-content");
                        },
                        success: function (data) {
                            unBlockUI(".modal-content");
                            if (data.type == "success") {
                                $(".scheduleList").empty();
                                $(".slotList").empty();
                                $("#scheduleTemplate")
                                        .tmpl(data.plannedEvent.event_schedule)
                                        .appendTo(".scheduleList");

                                // append schedule days
                                $("#slotTemplate")
                                        .tmpl(data.plannedEvent.event_schedule[0].schedule.event_schedule_slot)
                                        .appendTo(".slotList");

                                reassignDragDropEvent();

                                // Notify the messagee
                                notify("success", data.message);

                                // Trigger the last selected schedule days
                                $("[scheduleday='" + scheduleDay + "']").trigger("click");
                            } else {
                                notify("error", data.message);
                            }
                        }
                    });
                }
            }
        });
    });
    $(".btnCancelRecalculatedSeminar").click(function () {
        callbackForEventDrop();
    });
    $(".recalculateDaysConfirmBtn").click(function () {

        bootbox.confirm({
            message: recalculatedDaysConfirmationMessage,
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

                    // Ask for the reason if moved seminar is confirm
                    if (originalDragDateObj.event_status == "confirm") {
                        $("#seminarMove").modal("show");
                    } else {
                        actionsAfterRecalculateDate();
                    }

                } else {
                    callbackForEventDrop();
                }
            }
        });

    });

    $(".cancel-popup-recalculate").click(function () {
        callbackForEventDrop();
    });

    $body.on("click", ".btnCancelSeminar", function () {
        markSeminarAsCancel();
    });

    // After writing reason for move, recalculate dates
    $body.on("click", ".btnMoveSeminar", function () {
        actionsAfterRecalculateDate();
    });

    // Edit Go button > close accordian, update its value
    $body.off("click", ".saveSlotDetails");
    $body.on("click", ".saveSlotDetails", function (e) {
        $slotDiv = $("." + $(this).attr("data-parentdivid"));
        var slotPanelId = $(this).parents('.panel-default').attr("id");

        $trainers = "";

        var startTime = $slotDiv.find($("[name='start_time[]']")).val();
        var endTime = $slotDiv.find($("[name='end_time[]']")).val();
        // check the validation
        if (startTime == "" || endTime == "" || $slotDiv.find($("[name='trainer[]']")).val() == "") {
            notify("error", fillTheRequriedFields, "");
            return false;
        }

        var startTimeForCalculation = moment(startTime, [app_time_format_moment_js]).format("HH:mm");
        var endTimeForCalculation = moment(endTime, [app_time_format_moment_js]).format("HH:mm");

        var startTimeForDisplay = moment(startTime, [app_time_format_moment_js]).format(app_time_format_moment_js);
        var endTimeForDisplay = moment(endTime, [app_time_format_moment_js]).format(app_time_format_moment_js);
        console.log(slotsArray, "inintial");
        // update the slotArray with new value
        var oldSlotDetails = jQuery.extend(true, [], slotsArray);
        var index = slotsArray.findIndex(function (o) {
            return o.panelClass == slotPanelId;
        });
        console.log(index, "Index");
        var newSlotDetails = $.grep(slotsArray, function (e) {
            if (e.panelClass == slotPanelId) {
                e.end = endTimeForCalculation;
                e.start = startTimeForCalculation;
                return e;
            }
        });

        slotsArray.splice(index, 1);

        console.log(slotsArray, "After DElete slot array");

        for (var i = 0; i < slotsArray.length; i++) {
            for (var j = 0; j < slotsArray.length; j++) {
                checkSlotConflit(newSlotDetails[0], slotsArray[j]);
            }
        }

        console.log(slotsArray, "After Check Conflict ");
        conflictEvent = $.grep(slotsArray, function (e) {
            return e.hasConflict == true;
        });

        if (conflictEvent.length > 0) {
            notify("error", slotConflictMessage, "");
            //$("#" + conflictEvent[0].panelClass).css("border-color","red");

            // Revet back to origninal value
            slotsArray = jQuery.extend(true, [], oldSlotDetails);
            var oldSlotValue = $.grep(slotsArray, function (e) {
                if (e.panelClass == slotPanelId) {
                    return e;
                }
            });

            var startTimeForDisplay = moment(oldSlotValue[0].start, [app_time_format_moment_js]).format(app_time_format_moment_js);
            var endTimeForDisplay = moment(oldSlotValue[0].end, [app_time_format_moment_js]).format(app_time_format_moment_js);
            $slotDiv.find($("[name='start_time[]']")).val(startTimeForDisplay);
            $slotDiv.find($("[name='end_time[]']")).val(endTimeForDisplay);
            // End Revet back to origninal value
            return false;
        } else {
            //$(".schedule_slot_panel_" + conflictEvent[0].panelClass).css("border-color","#e0e0e0");
            isConflict = false;
            conflictEvent = {};
            //return false;
        }

        slotsArray.push(newSlotDetails[0]);

        $time_slot = (startTimeForDisplay + ' - ' + endTimeForDisplay);
        $slotDiv.find("[name*='trainer']").prev('div').find('.select2-search-choice').each(function () {
            $trainers += $(this).find('div').text() + ', ';
        });
        $trainerName = $trainers.trim().slice(0, -1);
        console.log('data>> ' + $trainerName);
        $trainers = "";
        $slotDiv.find("[data-index='slot_time']").html($time_slot);
        $slotDiv.find("[data-index='slot_trainer']").html($trainerName);
        $slotDiv.find("[data-index='slot_title']").html($slotDiv.find("[name='title[]']").val());
        $slotDiv.find("[data-index='slot_room']").html($slotDiv.find("[name='slotRoomName[]']").val());

        $slotDiv.find('.collapse.in').collapse('hide');

        // Update the time range for add new
        getNextSlotTimeRange();

    });

    // Sortable change event to update days for the schedule
    $body.on("change", "[name='scheduleType']", function () {
        if ($(this).val() == 1) {
            $(".mydaylist").removeClass("hidden");
        } else {
            if (!$(this).hasClass("hidden")) {
                $(".mydaylist").addClass("hidden");
            }
        }
    });

    $body.on("focusin", ".schedule-date-stuff .roomDropDownText", function () {
        $(".schedule-date-stuff #roomId").val("");
    });

    $body.on("focusin", ".schedule_add_slot .roomDropDownText", function () {
        $(".schedule_add_slot .roomId").val("");
    });

    $body.on("click", "#viewRooms", function () {
        $(".schedulListPanelGroup [data-index='slot_room']").toggle();
    });

    $body.on("click", "#viewTrainer", function () {
        $(".schedulListPanelGroup [data-index='slot_trainer']").toggle();
    });

    $body.on("blur", "[name='customRoomName']", function () {
        if ($("[name='scheduleType']:checked").val() == 0) {
            $("#addScheduleSlotPanel #slotCustomRoomName").val($(this).val());
            $("#addScheduleSlotPanel .roomId").val("");
        }
    });

    $body.on("blur", "[name='slotRoomName[]']", function () {
        $(this).prev().val("");
    });

    $body.on("change", "[name='scheduleRoomId']", function () {
        if ($("[name='scheduleType']:checked").val() == 0) {
            $("#addScheduleSlotPanel .roomId").val($(this).val());
            $("#addScheduleSlotPanel #slotCustomRoomName").val($("[name='customRoomName']").val());
        }
    });


    // $body.on("select2:select", '[name="scheduleDefaultTrainer"]', function(e) {
    $body.on("change", '#scheduleDefaultTrainer', function () {
        if ($("[name='scheduleType']:checked").val() == 0) {
            var selectedData = $("#scheduleDefaultTrainer").select2('data');
            $("[name='scheduleTrainers']").empty();
            $("#scheduleTrainers").select2("data", selectedData, true);
        }
    });

    $(document).click(function () {
        $(".seminarCategoryForExport").select2("close");
    });

    $body.on('keyup', '[name="search_trainer"]', function (e) {
        var search_trainer = $(this).val();
        $.ajax({
            url: base_url + 'seminar-planner/getTrainerList?q=' + search_trainer,
            method: 'get',
            beforeSend: function () {
                blockUI(".modal-content");

            },
            success: function (data) {
                unBlockUI(".modal-content");
                $(".trainerList").html("");
                $("#trainerListTemplate")
                        .tmpl(data)
                        .appendTo(".trainerList");
                reassignDragDropEvent();
                $(".scheduleListWrapper,.SlotListWrapper").css('height', $(".trainerlLocationListWrapper").height());
            }

        });
    });
    $body.on('keyup', '[name="search_location"]', function (e) {
        var search_trainer = $(this).val();
        $.ajax({
            url: base_url + 'seminar-planner/getLocationList?q=' + search_trainer,
            method: 'get',
            beforeSend: function () {
                blockUI(".modal-content");

            },
            success: function (data) {
                unBlockUI(".modal-content");
                $(".locationList").html("");
                $("#LocationListTemplate")
                        .tmpl(data)
                        .appendTo(".locationList");
                reassignDragDropEvent();
                $(".scheduleListWrapper,.SlotListWrapper").css('height', $(".trainerlLocationListWrapper").height());
            }

        });
    });


});

function ajaxForAddScheduleWithValidation(url, method, additionalDataByPage, schedule_id) {
    $.ajax({
        url: url,
        method: method,
        data: $(".myschedule_modal .add_schedule_form").find("*").serialize() + additionalDataByPage,
        beforeSend: function () {
            blockUI(".modal-content");
        },
        success: function (data) {

            unBlockUI(".modal-content");
            if ($("[name='model_mode']").val() != 0) {
                if (data.type == "success")
                    notify(data.type, duplicate_schedule_message);
                else
                    notify(data.type, data.message);
            } else {
                notify(data.type, data.message);
            }
            var $schedule_count = $('.schedule_count').attr('data-count');
            $("#schedule_table").find(".active-hr").removeClass('active-hr');
            var $locationName = $.trim($("[name='LocationID'] option:selected").text().split(" [")[0]);
            if (data.type != "danger") {
                $(".modal-open").css("overflow-y", 'auto');
                if (schedule_id == "0") {
                    $(".schedule_count").html(schedule + '(' + (parseInt($schedule_count) + 1) + ')');
                    $('.schedule_count').attr('data-count', (parseInt($schedule_count) + 1));
                    if ($("#schedule_table").hasClass("hidden")) {
                        $("#schedule_table").removeClass("hidden");
                    }
                    $clone_tr = $("#tab_schedule .cloneAccordianPanel").clone();
                    $clone_tr.css("display", "block");
                    $clone_tr.removeClass("cloneAccordianPanel");
                    $clone_tr.find("[data-index='event_days']").html(data.event_end_date);
                    $clone_tr.find("[data-index='location']").html($locationName);
                    $clone_tr.find("[data-index='slot_count']").html(slotsCount + " (" + $('.add_schedule_form .slot').length + ")");

                    $firstSlot = slotsArray.reduce(function (prev, curr) {
                        return prev.start < curr.start ? prev : curr;
                    });
                    $clone_tr.find("[data-index='start_time']").html($firstSlot.start + " " + onwards);


                    $clone_tr.addClass("schedule_panel_" + data.schedule_id);

                    $clone_tr.find(".accordion-toggle").attr('href', "#schedule_row_" + data.schedule_id);
                    $clone_tr.find(".panel-collapse").attr('id', "schedule_row_" + data.schedule_id);

                    $("#tab_schedule_info .panel-group").prepend($clone_tr);


                    $clone_tr.find('.schedule_edit').attr('data-id', data.schedule_id);
                    $clone_tr.find('.schedule_delete').attr('data-id', data.schedule_id);
                    $clone_tr.find('.schedule_duplicate').attr('data-id', data.schedule_id);

                    $('.close_schedule_modal').trigger('click');
                    var $time_slot = $trainerName = $trainers = '';
                    if ($("[name='model_mode']").val() == 0) {
                        $('.scheduleSlotList').find('.slot').each(function () {
                            console.log("if add");
                            $cloneSlotDiv = $(".cloneSlotsListingWrapper").clone();
                            $cloneSlotDiv.css("display", "block");
                            $cloneSlotDiv.removeClass("cloneSlotsListingWrapper").addClass("slotsListingWrapper");
                            $time_slot = ($(this).find($("[name='start_time[]']")).val()) + ' - ' + ($(this).find($("[name='end_time[]']")).val());
                            $(this).find("[name*='trainer']").prev('div').find('.select2-search-choice').each(function () {
                                $trainers += $(this).find('div').text() + ', ';
                            });
                            $trainerName = $trainers.trim().slice(0, -1) + '<br>';
                            $trainers = "";
                            $cloneSlotDiv.find(".slotTiming").html($time_slot);
                            $cloneSlotDiv.find(".slotTrainer").html($trainerName);
                            $cloneSlotDiv.find(".slotDescription").html($(this).find($("[name='description[]']")).val());
                            console.log($cloneSlotDiv, "Add");
                            $clone_tr.find(".panel-collapse").append($cloneSlotDiv);

                        });
                    }

                    if ($("[name='model_mode']").val() == 1) {
                        $(".scheduleSlotList").find($("[name='time_slot_chk[]']")).each(function () {
                            console.log("if duplicate");
                            $cloneSlotDiv = $(".cloneSlotsListingWrapper").clone();
                            console.log($cloneSlotDiv);
                            $cloneSlotDiv.css("display", "block");
                            $cloneSlotDiv.removeClass("cloneSlotsListingWrapper").addClass("slotsListingWrapper");

                            if ($(this).prop('checked') == true) {
                                var $parentSlot = $(this).parents('.slot');
                                $time_slot = ($parentSlot.find($("[name='start_time[]']")).val()) + ' - ' + ($parentSlot.find($("[name='end_time[]']")).val()) + '<br>';
                                $parentSlot.find("[name*='trainer']").prev('div').find('.select2-search-choice').each(function () {
                                    $trainers += $(this).find('div').text() + ', ';
                                });
                                $trainerName = $trainers.trim().slice(0, -1) + '<br>';
                                $trainers = "";

                                $cloneSlotDiv.find(".slotTiming").html($time_slot);
                                $cloneSlotDiv.find(".slotTrainer").html($trainerName);
                                $cloneSlotDiv.find(".slotDescription").html($(this).find($("[name='description[]']").val()));
                                console.log($cloneSlotDiv, "Duplicate");
                                $clone_tr.find(".panel-collapse").append($cloneSlotDiv);
                            }
                        });
                    }
                } else {
                    var $updatedScheduleRow = $('.schedule_panel_' + schedule_id);
                    $updatedScheduleRow.find("[data-index='event_days']").html(data.event_end_date);
                    $updatedScheduleRow.find("[data-index='location']").html($locationName);
                    $updatedScheduleRow.find("[data-index='slot_count']").html(slotsCount + " (" + $('.add_schedule_form .slot').length + ")");
                    $updatedScheduleRow.find("[data-index='start_time']").html();
                    var $time_slot = $trainerName = $trainers = '';
                    console.log($updatedScheduleRow);
                    $updatedScheduleRow.find(".panel-collapse").html("");
                    $('.add_schedule_form').find('.slot').each(function () {
                        console.log("if updatel");
                        $cloneSlotDiv = $(".cloneSlotsListingWrapper").clone();
                        $cloneSlotDiv.css("display", "block");
                        $cloneSlotDiv.removeClass("cloneSlotsListingWrapper").addClass("slotsListingWrapper");
                        $time_slot = ($(this).find($("[name='start_time[]']")).val()) + ' - ' + ($(this).find($("[name='end_time[]']")).val());
                        $(this).find("[name*='trainer']").prev('div').find('.select2-search-choice').each(function () {
                            $trainers += $(this).find('div').text() + ', ';
                        });
                        $trainerName = $trainers.trim().slice(0, -1);
                        $trainers = "";

                        $cloneSlotDiv.find(".slotTiming").html($time_slot);
                        $cloneSlotDiv.find(".slotTrainer").html($trainerName);
                        $cloneSlotDiv.find(".slotDescription").html($(this).find($("[name='description[]']")).val());
                        console.log($cloneSlotDiv, "updateee");
                        $updatedScheduleRow.find(".panel-collapse").append($cloneSlotDiv);
                    });
                }

                $("#event_startdate").val(data.event_start_date ? data.event_start_date : "");
                $("#event_enddate").val(data.event_start_date ? data.event_end_date : "");

                $("#calendar").fullCalendar('refetchEvents');

                //var start_date = new Date(data.event.event_startdate);
                //var end_date = new Date(data.event.event_enddate);
                if ($(".chats li.active-hr .event_date_range").length <= 0) {
                    $(".chats li.active-hr").find('.event_name').after("<h4 class='event_date_range'><span class='event_start_date'>" + data.event_start_date + "-" + "</span><span class='event_end_date'>" + data.event_end_date + "</span></h4>")
                    $("#event-detail .event_detail").find('.event_name').after("<h4 class='event_date_range'><span class='event_start_date'>" + data.event_start_date + "-" + "</span><span class='event_end_date'>" + data.event_end_date + "</span></h4>")
                } else {
                    $(".chats li.active-hr .event_date_range").html(data.event_start_date + "-" + data.event_end_date)
                    $("#event-detail .event_detail").find('.event_startdate').html(data.event_start_date + "-");
                    $("#event-detail .event_detail").find('.event_enddate').html(data.event_end_date);
                }
                //$('.close_schedule_modal').trigger("click");
                $('#scheduleModal').modal('hide');

            }
            $(".tooltips").tooltip();
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
            unBlockUI(".page-container");
            notify('error', errorsHtml);
        },
    });
}

function PopulateSelectedCheckBox() {
    $(".checked_item").each(function () {
        if ($(this).is(':checked')) {
            console.log("if")
            var i = selectedSeminarBluePrint.indexOf($(this).val());
            if (i == -1) {
                selectedSeminarBluePrint.push($(this).val());
            }

        } else {
            console.log("else");
            var i = selectedSeminarBluePrint.indexOf($(this).val());
            if (i != -1) {
                selectedSeminarBluePrint.splice(i, $(this).val());
            }
        }
    });

    console.log(selectedSeminarBluePrint);
    $('#selected_seminar').val(selectedSeminarBluePrint);
}

function checkAllCheckBox() {
    var getValues = $('#selected_seminar').val();
    $(".checked_item").each(function () {
        var i = getValues.indexOf($(this).val());
        if (i != -1) {
            $(this).prop('checked', 'checked');
        }
    });
}

function removeA(arr) {
    var what, a = arguments, L = a.length, ax;
    while (L > 1 && arr.length) {
        what = a[--L];
        while ((ax = arr.indexOf(what)) !== -1) {
            arr.splice(ax, 1);
        }
    }
    return arr;
}

var currentFilterRequest = false;

function generateFilterURL(no_notice) {
    if (currentFilterRequest == true) {
        return false;
    }
    currentFilterRequest = true;
    no_notice = typeof no_notice == 'undefined' ? false : no_notice;
    var additionalData = '';
    var checkStartDate = $("#startOfDate").val();
    var checkEndDate = $("#endOfDate").val();
    console.log("checkStartDate=>", checkStartDate);
    if (checkStartDate != '' && checkEndDate != '') {
        var startOfdate = moment($("#startOfDate").val().split(".").reverse().join("-")).add(-365, 'days').format('YYYY-MM-DD');
        var endOfDate = moment($("#endOfDate").val().split(".").reverse().join("-")).add(-365, 'days').format('YYYY-MM-DD');

        var sortby = $(".sort-wrapper li.active a").attr('sort-by');
        var sort_order = $(".sort-wrapper li.active a").attr('sort-order');
        var categoryname = $("#CompanyMainContactID").val();
        var seminarLocation = $("#SeminarCategoryID").val();
        var trainerId = $("#SeminarTrainerId").val();
        var seminar_planner_type = $(".seminar_planner_type option:selected").val();
        var seminar_planned_by = $('#SeminarPlannedBy').val();
        additionalData = "?search=" + $(".seminar-search-input").val() + "&sortby=" + sortby + "&sort_order=" + sort_order
                + "&category_id=" + categoryname + "&seminarLocation=" + seminarLocation + "&trainerId="
                + trainerId + "&is_planned=" + seminar_planner_type + "&planned_by=" + seminar_planned_by;

        if (startOfdate != '' && endOfDate != '') {
            additionalData += "&start_date=" + startOfdate + "&end_date=" + endOfDate;
        }
    } else {

        if (!no_notice) {
            notify('error', 'Please select planning period.');
            window.setTimeout(function () {

                if ($("#startOfDate").val() == '') {
                    $("#startOfDate").focus();
                } else {
                    $("#endOfDate").focus();
                }
            }, 1000);
            additionalData = false;
        }

    }
    window.setTimeout(function () {
        currentFilterRequest = false;
    }, 2000);

    if (selectedSeminarBluePrint) {
        $('.checked_item').removeAttr('checked');
        $.each(selectedSeminarBluePrint, function (index, val) {
            console.log(selectedSeminarBluePrint);
            $('.checked_item[value=' + val + ']').attr("checked", "checked");
        });
    }
    return additionalData;
}

function checkSlotConflit(event1, event2) {
    return event1.start < event2.start
            ? checkConflict(event1, event2)
            : checkConflict(event2, event1)

    function checkConflict(first, second) {
        if (first.end > second.start) {
            first.hasConflict = second.hasConflict = true;
            conflictEvent = first;
            return true;
        } else {
            first.hasConflict = second.hasConflict = false;
        }
    }
}

$('#event_startdate,#event_enddate').datepicker({
    autoclose: true,
    pickTime: false
});
/*$body.on("click", ".get_filter_reset", function (e) {
 $("#SeminarCategoryID").select2("val", "");
 $("#CompanyMainContactID").select2("val", "");
 });*/

$('#daysCalculationPopup').on('show.bs.modal', function () {
    $('.recalculateDatePicker').datepicker({
        startDate: new Date(),
        autoclose: true,
        format: app_date_format_js,
        language: app_language
    });
});


function loadAutoCompleteCategoryList() {
    $("input#CompanyMainContactID").select2({
        placeholder: selectCategory,
        minimumInputLength: 3,
        delay: 250,
        tags: true,
        tokenSeparators: [','],
        ajax: {// instead of writing the function to execute the request we use Select2's convenient helper
            url: base_url + "seminar-planner/getCategory",
            dataType: 'json',
            method: "GET",
            data: function (term, page) {
                return {
                    q: term // search term
                };
            },
            results: function (data, page) { // parse the results into the format expected by Select2.
                return {
                    results: data
                };
            }
        },
        maximumSelectionSize: 4,
    });
}

function loadAutoCompleteLocationList() {
    $("input#SeminarCategoryID").select2({
        placeholder: selectLocation,
        minimumInputLength: 3,
        delay: 250,
        tags: true,
        tokenSeparators: [','],
        ajax: {// instead of writing the function to execute the request we use Select2's convenient helper
            url: base_url + "seminar-planner/getLocation",
            dataType: 'json',
            method: "GET",
            data: function (term, page) {
                return {
                    q: term // search term
                };
            },
            results: function (data, page) { // parse the results into the format expected by Select2.
                return {
                    results: data
                };
            }
        },
        maximumSelectionSize: 4,
    });
}

function loadAutoCompleteTrainerList() {
    $("input#SeminarTrainerId").select2({
        placeholder: selectTrainer,
        minimumInputLength: 3,
        delay: 250,
        tags: true,
        tokenSeparators: [','],
        ajax: {// instead of writing the function to execute the request we use Select2's convenient helper
            url: base_url + "seminar-planner/getTrainer",
            dataType: 'json',
            method: "GET",
            data: function (term, page) {
                return {
                    q: term // search term
                };
            },
            results: function (data, page) { // parse the results into the format expected by Select2.
                return {
                    results: data
                };
            }
        },
        maximumSelectionSize: 4,
    });
}

function loadAutoCompleteSeminarPlannedList() {
    $("input#SeminarPlannedBy").select2({
        placeholder: selected_seminar_planned,
        minimumInputLength: 3,
        delay: 250,
        tags: true,
        tokenSeparators: [','],
        ajax: {// instead of writing the function to execute the request we use Select2's convenient helper
            url: base_url + "seminar-planner/getSeminarPlannedBy",
            dataType: 'json',
            method: "GET",
            data: function (term, page) {
                return {
                    q: term // search term
                };
            },
            results: function (data, page) { // parse the results into the format expected by Select2.
                return {
                    results: data
                };
            }
        },
        maximumSelectionSize: 4,
    });
}

function loadSeminars(url, msg_data) {
    $.ajax({
        url: url,
        beforeSend: function () {
            $(".seminar_list_table").html('');
            blockUI(".page-container");
        },
        success: function (data) {
            currentFilterRequest = false;
            unBlockUI(".page-container");
            $(".seminar_list_table").html(data);
            checkAllCheckBox();
            reinitialScrollLoad(2, "#infinite_scroll", ".seminar_list_table table tbody", ".table-bordered tbody");
            $("#infinite_scroll").getNiceScroll().resize();
        }
    }).error(function () {
        currentFilterRequest = false;
    });
}

function reinitialScrollLoad(pageNumNoUse, me, source, destination) {

    $(me).scrollLoad({
        url: getPaginationUrl(), //your ajax file to be loaded when scroll breaks ScrollAfterHeight

        getData: function () {
            //you can post some data along with ajax request
        },

        start: function () {
            /* $('<div class="loading"><img src="' + asset_url + '/global/img/loading-spinner-default.gif"/></div>').appendTo(this);*/
            // you can add your effect before loading data
        },

        ScrollAfterHeight: 95, //this is the height in percentage after which ajax stars

        onload: function (data) {
            var dataObj = $(data);
            $(this).find(source).append(dataObj.find("tbody").html());
            pageNo = pageNo + 1;
            defaults.url = getPaginationUrl();
            /*$('.loading').remove();*/
            setTimeout(function () {
                $(me).niceScroll();
            }, 100);
        }, // this event fires on ajax success

        continueWhile: function (resp) {
            if ($(resp).find('tbody tr').length == 0) { // stops when number of 'li' reaches 100
                notify('error', no_more_load);
                return false;
            }
            return true;
        }
    });
}


function getPaginationUrl() {
    //var filter = $(".filter-wrapper li.active a").attr('rel');

    var additional_data = generateFilterURL();
    if (root_url) {
        return root_url + additional_data + '&page=' + pageNo + '&initial=' + initial + "&limit=15";
    } else {
        return pagination_url + additional_data + '&page=' + pageNo + '&initial=' + initial + "&limit=15";
    }

}

function getCalendarViewToPlannedSeminars() {
    //  selectedSeminarBluePrint = [1,2];
    console.log(selectedSeminarBluePrint, "Selected blue print");
    var selectedBluePrint = selectedSeminarBluePrint.join();
    $.ajax({
        url: base_url + "seminar-planner/getSeminarBluePrints" + "?bluePrintsId=" + selectedBluePrint,
        beforeSend: function () {
            blockUI(".page-container");
        },
        success: function (data) {
            unBlockUI(".page-container");
            if (data.type == "success") {
                $("#bulePrintList").empty();
                $("#bulePrintListTemplate")
                        .tmpl(data.bluePrintSeminars)
                        .appendTo("#bulePrintList");

                $(".dd-item").draggable({
                    zIndex: 999,
                    revert: true, // will cause the event to go back to its
                    revertDuration: 0 // original position after the drag

                });

                initCalendarForPlanning();
            }
        }
    });
}


function initCalendarForPlanning() {
    currentDate = $(".currentDate").val();
    $('#calendar').fullCalendar('destroy');
    $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        lang: app_language,
        defaultDate: moment(currentDate),
        editable: true,
        defaultDate: moment(currentDate),
        events: $source,
        droppable: true, // this allows things to be dropped onto the calendar !!!
        dropAccept: '.dd-item',
        loading: function (bool) {
            if (bool) {
                blockUI(".page-container");
            } else {
                unBlockUI(".page-container");
                $(".tooltips").tooltip();
            }
        },
        eventDragStart: function (event) {
            console.log(event);
            originalDragDate = new Date(event.start);  // Make a copy of the event date
            oldDates = new Array();
            $.each($('#calendar').fullCalendar('clientEvents'), function (index, val) {
                if (val.className == event.className[0]) {
                    oldDates.push(val);
                }
            });
        },
        eventDrop: function (event, allDay, revertFunc, jsEvent, ui, view) {
            originalDragDateObj = event;
            clickEventId = event.id;
            var CurrentDate = new Date();
            var date = event.start.toDate();
            if (date < CurrentDate) {
                notify('error', cantScheduleInPast);
                revertFunc();
                return false;

            }
            var days = new Array();
            var classNam = event.className[0];
            dayCalculation = new Array();
            dateAdd = new Array();

            // check for holidayss
            while (checkForHoliday(date) == true) {
                date.setDate(date.getDate() + 1);
            }

            console.log('alldays>>>>' + allDay);
            console.log(weekendConsider);

            var markup = "<tr>" +
                    "<td>${SeminarDay}</td> " +
                    "<td>${SeminarDayTitle}</td> " +
                    "<td>${SeminarCurrentDate}</td> " +
                    "<td>${SeminarCurrentDay}</td> " +
                    "<td>${SeminarChangeDay}</td> " +
                    "<td><input class='form-control form-control-inline recalculateDatePicker required'" +
                    "size='16' type='text' name='begin'" +
                    "value='${SeminarRecalculateDate}'/></td>" +
                    "<td>${SeminarRecalculateDay}</td> " +
                    "</tr>";

            // Compile the markup as a named template
            $.template("daysCalculationBody", markup);
            if (weekendConsider != "1" && (date.getDay() == 0 || date.getDay() == 6)) {

                bootbox.confirm({
                    message: weekendWarningMessage,
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
                            while (date.getDay() == 0 || date.getDay() == 6) {
                                date.setDate(date.getDate() + 1);
                            }

                        }


                        $(event.event_schedule).each(function (key, val) {

                            // check which day user has moves
                            var calculatedDay = new Object();
                            if (val.schedule.event_days < event.event_days) {
                                console.log("in iffffff");
                                calculatedDay.SeminarDay = val.schedule.event_days;
                                calculatedDay.SeminarDayTitle = event.event_name + " - " + slot_days + " - " + val.schedule.event_days;
                                calculatedDay.SeminarCurrentDate = moment(oldDates[key].start).format(app_date_format_js.toUpperCase());
                                calculatedDay.SeminarCurrentDay = moment(oldDates[key].start).format("dddd");
                                calculatedDay.SeminarChangeDay = "";
                                calculatedDay.SeminarRecalculateDate = "";
                                calculatedDay.SeminarRecalculateDay = "";

                                dateAdd.push(oldDates[key]);
                            } else {
                                console.log(oldDates, "Old dates");
                                calculatedDay.SeminarDay = val.schedule.event_days;
                                calculatedDay.SeminarDayTitle = event.event_name + " - " + slot_days + " - " + val.schedule.event_days;
                                if (val.schedule.event_days == event.event_days) {

                                    calculatedDay.SeminarCurrentDate = moment(originalDragDate).format(app_date_format_js.toUpperCase());
                                    calculatedDay.SeminarCurrentDay = moment(originalDragDate).format("dddd");
                                    calculatedDay.SeminarRecalculateDate = moment(date).format(app_date_format_js.toUpperCase());
                                    calculatedDay.SeminarRecalculateDay = moment(date).format("dddd");
                                    calculatedDay.SeminarChangeDay = moment(date).format("dddd");
                                } else {
                                    console.log("In ELse");
                                    calculatedDay.SeminarCurrentDate = moment(oldDates[key].start).format(app_date_format_js.toUpperCase());
                                    calculatedDay.SeminarCurrentDay = moment(oldDates[key].start).format("dddd");
                                    calculatedDay.SeminarChangeDay = "";

                                    // Check if drop-date is valid OR Find next valid date
                                    var dropDay = new Date(date.getTime());
                                    dropDay.setDate(dropDay.getDate() + (parseInt(val.schedule.duration_between_previous_day) + 1));

                                    if (val.schedule.weekdays) {
                                        console.log(val.schedule.weekdays.split(","))
                                        // Check if that day is weekend
                                        if (weekendConsider == "1" || response == false) {
                                            while (val.schedule.weekdays.indexOf(dropDay.getDay().toString()) == -1 || checkForHoliday(dropDay) == true) {
                                                dropDay.setDate(dropDay.getDate() + 1);
                                            }
                                        } else {
                                            // Check if schedul has allow only weekends and globle weekends consideration settings is  off
                                            var weekdaysArray = val.schedule.weekdays.split(",");
                                            var isOtherWeekDays = $(weekdaysArray).not(["0", "6"]).get();
                                            if (isOtherWeekDays.length > 0) {
                                                while (val.schedule.weekdays.indexOf(dropDay.getDay().toString()) == -1 || dropDay.getDay() == 0 || dropDay.getDay() == 6 || checkForHoliday(dropDay) == true) {
                                                    dropDay.setDate(dropDay.getDate() + 1);
                                                }
                                            } else {
                                                alert("Schedule has configure to occurred only on weekend but your global setting not allow to plan on weekends so we overwrite your weekend setting for this day.");
                                                while (val.schedule.weekdays.indexOf(dropDay.getDay().toString()) == -1 || checkForHoliday(dropDay) == true) {
                                                    dropDay.setDate(dropDay.getDate() + 1);
                                                }

                                            }
                                        }
                                    }
                                    calculatedDay.SeminarRecalculateDate = moment(dropDay).format(app_date_format_js.toUpperCase());
                                    calculatedDay.SeminarRecalculateDay = moment(dropDay).format("dddd");
                                    date = dropDay;
                                }

                                var copiedEventObject = new Object();
                                copiedEventObject.title = event.event_name + " - Day - " + val.schedule.event_days;
                                copiedEventObject.event_name = event.event_name;
                                copiedEventObject.start = date;
                                copiedEventObject.end = date;
                                copiedEventObject.color = event.color;
                                copiedEventObject.allDay = allDay;
                                copiedEventObject.id = event.event_id + "-" + val.schedule.id;
                                copiedEventObject.event_id = event.event_id;
                                copiedEventObject.className = event.className;
                                copiedEventObject.event_schedule = event.event_schedule;
                                copiedEventObject.event_days = val.schedule.event_days;
                                dateAdd.push(copiedEventObject);

                            }

                            dayCalculation.push(calculatedDay);

                        });

                        $(".daysCalculationBody").html("");
                        $.tmpl("daysCalculationBody", dayCalculation)
                                .appendTo(".daysCalculationBody");

                        $("#daysCalculationPopup").modal("show");

                        callbackForEventDrop = revertFunc;
                    }
                });
            } else {
                if (app_language == 'en') {
                    moment.locale('en');
                } else {
                    moment.locale('de');
                }
                var tempcount = 1;
                var maindate;
                var dropDay1 = new Date(date.getTime());
                $(event.event_schedule).each(function (key, val) {
                    // check which day user has moves
                    var calculatedDay = new Object();
                    if (val.schedule.event_days < event.event_days) {
                        console.log("in iffffff");
                        calculatedDay.SeminarDay = val.schedule.event_days;
                        calculatedDay.SeminarDayTitle = event.event_name + " - " + slot_days + " - " + val.schedule.event_days;
                        calculatedDay.SeminarCurrentDate = moment(oldDates[key].start).format(app_date_format_js.toUpperCase());
                        calculatedDay.SeminarCurrentDay = moment(oldDates[key].start).format("dddd");
                        calculatedDay.SeminarChangeDay = "";
                        calculatedDay.SeminarRecalculateDate = "";
                        calculatedDay.SeminarRecalculateDay = "";

                        dateAdd.push(oldDates[key]);
                    } else {
                        console.log(oldDates, "Old dates");
                        calculatedDay.SeminarDay = val.schedule.event_days;
                        calculatedDay.SeminarDayTitle = event.event_name + " - " + slot_days + " - " + val.schedule.event_days;
                        if (tempcount == 1) {
                            dropDay1.setDate(dropDay1.getDate());
                            console.log(dropDay1, "Dateeeee123");
                            tempcount = 2;
                            console.log(dropDay, "Dateeeee1");
                            if (val.schedule.weekdays) {
                                console.log(val.schedule.weekdays.split(","))
                                // Check if that day is weekend
                                if (weekendConsider == "1") {
                                    while (val.schedule.weekdays.indexOf(dropDay1.getDay().toString()) == -1 || checkForHoliday(dropDay1) == true) {
                                        dropDay1.setDate(dropDay1.getDate() + (parseInt(val.schedule.duration_between_previous_day)));
                                    }
                                } else {
                                    // Check if schedul has allow only weekends and globle weekends consideration settings is  off
                                    var weekdaysArray = val.schedule.weekdays.split(",");
                                    var isOtherWeekDays = $(weekdaysArray).not(["0", "6"]).get();
                                    if (isOtherWeekDays.length > 0) {
                                        while (val.schedule.weekdays.indexOf(dropDay.getDay().toString()) == -1 || dropDay.getDay() == 0 || dropDay.getDay() == 6 || checkForHoliday(dropDay) == true) {
                                            dropDay.setDate(dropDay.getDate() + 1);
                                        }
                                    } else {
                                        alert("Schedule has configure to occurred only on weekend but your global setting not allow to plan on weekends so we overwrite your weekend setting for this day.");
                                        while (val.schedule.weekdays.indexOf(dropDay.getDay().toString()) == -1 || checkForHoliday(dropDay) == true) {
                                            dropDay.setDate(dropDay.getDate() + 1);
                                        }

                                    }
                                }
                            }
                        }
                        if (val.schedule.event_days == event.event_days) {
                            console.log(moment(oldDates[key].start).format(app_date_format_js.toUpperCase()), "Dateeeee2");
                            maindate = dropDay1;
                            calculatedDay.SeminarCurrentDate = moment(originalDragDate).format(app_date_format_js.toUpperCase());
                            calculatedDay.SeminarCurrentDay = moment(originalDragDate).format("dddd");
                            calculatedDay.SeminarRecalculateDate = moment(dropDay1).format(app_date_format_js.toUpperCase());
                            calculatedDay.SeminarRecalculateDay = moment(dropDay1).format("dddd");
                            calculatedDay.SeminarChangeDay = moment(dropDay1).format("dddd");
                        } else {
                            console.log(dropDay1, "Dateeeee2");

                            calculatedDay.SeminarCurrentDate = moment(oldDates[key].start).format(app_date_format_js.toUpperCase());
                            calculatedDay.SeminarCurrentDay = moment(oldDates[key].start).format("dddd");
                            calculatedDay.SeminarChangeDay = "";

                            // Check if drop-date is valid OR Find next valid date
                            var dropDay = new Date(date.getTime());
                            dropDay.setDate(dropDay.getDate() + (parseInt(val.schedule.duration_between_previous_day) + 1));
                            console.log(dropDay, "Dateeeee1");
                            if (val.schedule.weekdays) {
                                console.log(val.schedule.weekdays.split(","))
                                // Check if that day is weekend
                                if (weekendConsider == "1") {
                                    while (val.schedule.weekdays.indexOf(dropDay.getDay().toString()) == -1 || checkForHoliday(dropDay) == true) {
                                        dropDay.setDate(dropDay.getDate() + 1);
                                    }
                                } else {
                                    // Check if schedul has allow only weekends and globle weekends consideration settings is  off
                                    var weekdaysArray = val.schedule.weekdays.split(",");
                                    var isOtherWeekDays = $(weekdaysArray).not(["0", "6"]).get();
                                    if (isOtherWeekDays.length > 0) {
                                        while (val.schedule.weekdays.indexOf(dropDay.getDay().toString()) == -1 || dropDay.getDay() == 0 || dropDay.getDay() == 6 || checkForHoliday(dropDay) == true) {
                                            dropDay.setDate(dropDay.getDate() + 1);
                                        }
                                    } else {
                                        alert("Schedule has configure to occurred only on weekend but your global setting not allow to plan on weekends so we overwrite your weekend setting for this day.");
                                        while (val.schedule.weekdays.indexOf(dropDay.getDay().toString()) == -1 || checkForHoliday(dropDay) == true) {
                                            dropDay.setDate(dropDay.getDate() + 1);
                                        }

                                    }
                                }
                            }
                            calculatedDay.SeminarCurrentDate = moment(oldDates[key].start).format(app_date_format_js.toUpperCase());
                            calculatedDay.SeminarCurrentDay = moment(oldDates[key].start).format("dddd");
                            maindate = oldDates[key].start;
                            date = dropDay;
                        }

                        var copiedEventObject = new Object();
                        copiedEventObject.title = event.event_name + " - " + slot_days + " - " + val.schedule.event_days;
                        copiedEventObject.event_name = event.event_name;
                        copiedEventObject.start = maindate;
                        copiedEventObject.end = maindate;
                        copiedEventObject.color = event.color;
                        copiedEventObject.allDay = allDay;
                        copiedEventObject.id = event.event_id + "-" + val.schedule.id;
                        copiedEventObject.event_id = event.event_id;
                        copiedEventObject.className = event.className;
                        copiedEventObject.event_schedule = event.event_schedule;
                        copiedEventObject.event_days = val.schedule.event_days;
                        dateAdd.push(copiedEventObject);

                    }

                    dayCalculation.push(calculatedDay);

                });

                $(".daysCalculationBody").html("");
                $.tmpl("daysCalculationBody", dayCalculation)
                        .appendTo(".daysCalculationBody");

                $("#daysCalculationPopup").modal("show");

                callbackForEventDrop = revertFunc;
            }


            //console.log(dayCalculation, "calclulation");
            console.log(dateAdd, "Recalculates Days");
            //console.log(oldDates, "old Recalculates Days");
            $('.recalculateDatePicker').attr('disabled', 'disabled');


        },
        drop: function (date, allDay) { // this function is called when something is dropped
            console.log(date);
            newDropDate = date;
            var CurrentDate = new Date();
            if (date < CurrentDate) {
                notify('error', plannerCalendarEventPastNotice);
                return false;
            }
            // retrieve the dropped element's stored Event Object
            originalDragDateObj = $.tmplItem(this).data;
            dateAdd = new Array();
            days = new Array();
            var dDate = new Date(date.toDate().getTime());

            // check for holidayss
            while (checkForHoliday(dDate) == true) {
                dDate.setDate(dDate.getDate() + 1);
            }
            if (!originalDragDateObj.event_schedule[0]) {
                notify('error', plannerWithoutScheduleMsg);
                return false;
            }
            if (originalDragDateObj.event_schedule[0].schedule.weekdays.indexOf("0") >= 0 && originalDragDateObj.event_schedule[0].schedule.weekdays.indexOf("6") >= 0) {

                $(originalDragDateObj.event_schedule).each(function (key, val) {

                    if (days.length > 0) {
                        var dropDay = new Date(days[key - 1].getTime());
                        dropDay.setDate(dropDay.getDate() + (parseInt(val.schedule.duration_between_previous_day) + 1));
                    } else {
                        var dropDay = dDate;
                    }

                    var weekdays = val.schedule.weekdays.split(",");

                    if (val.schedule.weekdays) {
                        console.log(val.schedule.weekdays.split(","))
                        // Check if that day is weekend
                        if (originalDragDateObj.event_schedule[0].schedule.weekdays.indexOf("0") >= 0 && originalDragDateObj.event_schedule[0].schedule.weekdays.indexOf("6") >= 0) {
                            while (val.schedule.weekdays.indexOf(dropDay.getDay().toString()) == -1 || checkForHoliday(dropDay) == true) {
                                dropDay.setDate(dropDay.getDate() + 1);
                            }
                        } else {
                            // Check if schedul has allow only weekends and globle weekends consideration settings is  off
                            var weekdaysArray = val.schedule.weekdays.split(",");
                            var isOtherWeekDays = $(weekdaysArray).not(["0", "6"]).get();
                            if (isOtherWeekDays.length > 0) {
                                while (val.schedule.weekdays.indexOf(dropDay.getDay().toString()) == -1 || dropDay.getDay() == 0 || dropDay.getDay() == 6 || checkForHoliday(dropDay) == true) {
                                    dropDay.setDate(dropDay.getDate() + 1);
                                }
                            } else {
                                alert("Schedule has configure to occurred only on weekend but your global setting not allow to plan on weekends so we overwrite your weekend setting for this day.");
                                while (val.schedule.weekdays.indexOf(dropDay.getDay().toString()) == -1 || checkForHoliday(dropDay) == true) {
                                    dropDay.setDate(dropDay.getDate() + 1);
                                }

                            }
                        }
                    }

                    // add dropday into days array
                    days.push(dropDay);
                    // add event object to display day as indipendent day

                    var copiedEventObject = $.extend({}, originalDragDateObj);
                    copiedEventObject.title = originalDragDateObj.event_name + " - " + slot_days + " -" + val.schedule.event_days;
                    copiedEventObject.start = dropDay;
                    copiedEventObject.end = dropDay;
                    copiedEventObject.color = "#cccccc";
                    copiedEventObject.allDay = allDay;
                    copiedEventObject.id = originalDragDateObj.id + "-" + val.schedule.id;
                    copiedEventObject.event_id = originalDragDateObj.id;
                    copiedEventObject.event_days = val.schedule.event_days;
                    copiedEventObject.className = "bluePrint_" + originalDragDateObj.id;

                    dateAdd.push(copiedEventObject);

                });

                console.log(dateAdd);
                // Add this blueprint into planned events as draft and set default schedule dates
                insertBlueprintAsDraftEvent(dateAdd, "drop");

            } else if (weekendConsider != "1" && (dDate.getDay() == 0 || dDate.getDay() == 6)) {
                bootbox.confirm({
                    message: weekendWarningMessage,
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
                            while (dDate.getDay() == 0 || dDate.getDay() == 6) {
                                dDate.setDate(dDate.getDate() + 1);
                            }

                        }
                        $(originalDragDateObj.event_schedule).each(function (key, val) {
                            if (days.length > 0) {
                                var dropDay = new Date(days[key - 1].getTime());
                                dropDay.setDate(dropDay.getDate() + (parseInt(val.schedule.duration_between_previous_day) + 1));
                            } else {
                                var dropDay = dDate;
                            }

                            var weekdays = val.schedule.weekdays.split(",");

                            if (val.schedule.weekdays) {
                                console.log(val.schedule.weekdays.split(","))
                                // Check if that day is weekend
                                if (weekendConsider == "1" || response == false) {
                                    while (val.schedule.weekdays.indexOf(dropDay.getDay().toString()) == -1 || checkForHoliday(dropDay) == true) {
                                        dropDay.setDate(dropDay.getDate() + 1);
                                    }
                                } else {
                                    // Check if schedul has allow only weekends and globle weekends consideration settings is  off
                                    var weekdaysArray = val.schedule.weekdays.split(",");
                                    var isOtherWeekDays = $(weekdaysArray).not(["0", "6"]).get();
                                    if (isOtherWeekDays.length > 0) {
                                        while (val.schedule.weekdays.indexOf(dropDay.getDay().toString()) == -1 || dropDay.getDay() == 0 || dropDay.getDay() == 6 || checkForHoliday(dropDay) == true) {
                                            dropDay.setDate(dropDay.getDate() + 1);
                                        }
                                    } else {
                                        alert("Schedule has configure to occurred only on weekend but your global setting not allow to plan on weekends so we overwrite your weekend setting for this day.");
                                        while (val.schedule.weekdays.indexOf(dropDay.getDay().toString()) == -1 || checkForHoliday(dropDay) == true) {
                                            dropDay.setDate(dropDay.getDate() + 1);
                                        }

                                    }
                                }
                            }

                            // add dropday into days array
                            days.push(dropDay);
                            // add event object to display day as indipendent day

                            var copiedEventObject = $.extend({}, originalDragDateObj);
                            copiedEventObject.title = originalDragDateObj.event_name + " - " + slot_days + " -" + val.schedule.event_days;
                            copiedEventObject.start = dropDay;
                            copiedEventObject.end = dropDay;
                            copiedEventObject.color = "#cccccc";
                            copiedEventObject.allDay = allDay;
                            copiedEventObject.id = originalDragDateObj.id + "-" + val.schedule.id;
                            copiedEventObject.event_id = originalDragDateObj.id;
                            copiedEventObject.event_days = val.schedule.event_days;
                            copiedEventObject.className = "bluePrint_" + originalDragDateObj.id;

                            dateAdd.push(copiedEventObject);

                        });

                        console.log(dateAdd);
                        // Add this blueprint into planned events as draft and set default schedule dates
                        insertBlueprintAsDraftEvent(dateAdd, "drop");
                    }
                });
            } else {
                $(originalDragDateObj.event_schedule).each(function (key, val) {
                    if (days.length > 0) {
                        var dropDay = new Date(days[key - 1].getTime());
                        dropDay.setDate(dropDay.getDate() + (parseInt(val.schedule.duration_between_previous_day) + 1));
                    } else {
                        var dropDay = new Date(date.toDate().getTime());
                    }

                    var weekdays = val.schedule.weekdays.split(",");

                    if (val.schedule.weekdays) {
                        console.log(val.schedule.weekdays.split(","))
                        // Check if that day is weekend
                        if (weekendConsider == "1") {
                            while (val.schedule.weekdays.indexOf(dropDay.getDay().toString()) == -1 || checkForHoliday(dropDay) == true) {
                                dropDay.setDate(dropDay.getDate() + 1);
                            }
                        } else {
                            // Check if schedul has allow only weekends and globle weekends consideration settings is  off
                            var weekdaysArray = val.schedule.weekdays.split(",");
                            var isOtherWeekDays = $(weekdaysArray).not(["0", "6"]).get();
                            if (isOtherWeekDays.length > 0) {
                                while (val.schedule.weekdays.indexOf(dropDay.getDay().toString()) == -1 || dropDay.getDay() == 0 || dropDay.getDay() == 6 || checkForHoliday(dropDay) == true) {
                                    dropDay.setDate(dropDay.getDate() + 1);
                                }
                            } else {
                                alert("Schedule has configure to occurred only on weekend but your global setting not allow to plan on weekends so we overwrite your weekend setting for this day.");
                                while (val.schedule.weekdays.indexOf(dropDay.getDay().toString()) == -1 || checkForHoliday(dropDay) == true) {
                                    dropDay.setDate(dropDay.getDate() + 1);
                                }

                            }
                        }
                    }

                    // add dropday into days array
                    days.push(dropDay);
                    // add event object to display day as indipendent day

                    var copiedEventObject = $.extend({}, originalDragDateObj);
                    copiedEventObject.title = originalDragDateObj.event_name + " - " + slot_days + " -" + val.schedule.event_days;
                    copiedEventObject.start = dropDay;
                    copiedEventObject.end = dropDay;
                    copiedEventObject.color = "#cccccc";
                    copiedEventObject.allDay = allDay;
                    copiedEventObject.id = originalDragDateObj.id + "-" + val.schedule.id;
                    copiedEventObject.event_id = originalDragDateObj.id;
                    copiedEventObject.event_days = val.schedule.event_days;
                    copiedEventObject.className = "bluePrint_" + originalDragDateObj.id;

                    dateAdd.push(copiedEventObject);


                });
                console.log(dateAdd);
                // Add this blueprint into planned events as draft and set default schedule dates
                insertBlueprintAsDraftEvent(dateAdd, "drop");
            }


        },
        eventClick: function (calEvent, jsEvent, view) {
            console.log(calEvent);
            clickEventId = calEvent.event_id;
            clickEvent = calEvent;

        },
        eventRender: function (event, element) {
            var message = "";
            message += event.detailTrainerConflictMessage != "" ? event.detailTrainerConflictMessage : '';
            message += event.detailLocationConflictMessage != "" ? " | " + event.detailLocationConflictMessage : '';
            message = replaceTranslatables(message);
            element.find('.fc-content').append("<span class='location' style='display: block;'> " + eventLocation + " : " + event.LocationName + "</span>");
            element.find('.fc-title').attr('data-container', 'body').attr('data-original-title', message).addClass("tooltips");
            var highlightNewEvent = getHandler('seminarPlanner', 'highlightNewEvent');
            highlightNewEvent(event, element)

        },
        eventAfterAllRender: function (view) {
            //Use view.intervalStart and view.intervalEnd to find date range of holidays
            //Make ajax call to find holidays in range.
            var holidays = $.parseJSON(holidaysArray);
            var holidayMoment;
            for (var i = 0; i < holidays.length; i++) {
                holidayMoment = holidays[i];
                if (view.name == 'month') {
                    var className = holidayMoment.action == 2 ? 'holidayWarning' : 'holidayDanger';
                    // var className = "holidayWarning";
                    $("td.fc-day-number[data-date=" + holidayMoment.start_date + "]").addClass(className);
                    var date = $("td[data-date=" + holidayMoment.start_date + "]").text();
                    $("td.fc-day-number[data-date=" + holidayMoment.start_date + "]").text("");
                    $("td.fc-day-number[data-date=" + holidayMoment.start_date + "]").html("<span class='tooltips' data-container='body' data-original-title='" + holidayMoment.holiday_name + "'>" + date + "</span>");
                }
                // NOTE : NEED TO CHANGE LOGIC
                // else if (view.name =='agendaWeek') {
                //     var classNames = $("th:contains(' " + holidayMoment.format('M/D') + "')").attr("class");
                //     if (classNames != null) {
                //         var classNamesArray = classNames.split(" ");
                //         for(var i = 0; i < classNamesArray.length; i++) {
                //             if(classNamesArray[i].indexOf('fc-col') > -1) {
                //                 $("td." + classNamesArray[i]).addClass('holiday');
                //                 break;
                //             }
                //         }
                //     }
                // } else if (view.name == 'agendaDay') {
                //     if(holidayMoment.format('YYYY-MM-DD') == $('#calendar').fullCalendar('getDate').format('YYYY-MM-DD')) {
                //         $("td.fc-col0").addClass('holiday');
                //     };
                // }
            }
        }
    });
}

// Ceck if day is weekend
function checkForWeekends(dropDay) {
    while (dropDay.getDay() == 0 || dropDay.getDay() == 6) {
        dropDay.getDate() + 1;
    }
    return dropDay;
}

function checkForHoliday(currentDate) {
    var dropDate = moment(currentDate).format('YYYY-MM-DD');
    var holiday = $.parseJSON(holidaysArray);
    var warning = 0;
    var result = $.grep(holiday, function (e) {
        return e.start_date == dropDate
    });
    console.log(currentDate, "currnt date");
    if (result.length > 0) {
        if (result[0].action != 2) {
            notify("error", "It was holiday on that day, So we shift too the next day");
            return true;
        } else if (result[0].action == 2) {
            notify("warning", "It was holiday on that day so please consider as warning");
            return false;
        }
    } else {
        return false;
    }

}

// When blueprint is drop insert that buleprint as draft event
function insertBlueprintAsDraftEvent(blueprintEventObject, actionType) {
    var scheduleObjectArray = new Array();
    var showNotice = false;
    $(blueprintEventObject).each(function (index, val) {
        var scheduleObject = new Object();
        scheduleObject.scheduleDate = moment(val.start).format("YYYY-MM-DD");
        var newDropDateStr = moment(newDropDate).format("YYYY-MM-DD");
        if (scheduleObject.scheduleDate != newDropDateStr && index == 0) {
            showNotice = true;
        }
        console.log(newDropDate);
        scheduleObject.scheduleId = val.id.split("-")[1];
        scheduleObjectArray.push(scheduleObject);
    });
    $.ajax({
        url: base_url + "seminar-planner/insertBlueprintAsDraftEvent",
        method: "POST",
        data: {"schedules": scheduleObjectArray, "blueprintEventId": originalDragDateObj.id, "actionType": actionType},
        beforeSend: function () {
            blockUI(".page-container");
        },
        success: function (data) {
            console.log(data);

            if (showNotice) {
                notify('error', plannerCalendarEventNotice);
            }
            unBlockUI(".page-container");
            if (data.type == "success") {
                dateAdd = new Array();
                $("#calendar").fullCalendar('refetchEvents');
                unBlockUI(".page-container");
                // $(data.bluePrintSeminars.event_schedule).each(function (index, val) {
                //
                //     var copiedEventObject = new Object();
                //     copiedEventObject.title = data.bluePrintSeminars.event_name + " - Day - " + val.schedule.event_days;
                //     copiedEventObject.event_name = data.bluePrintSeminars.event_name;
                //     copiedEventObject.LocationName = val.schedule.schedule_location.LocationName;
                //     copiedEventObject.start = val.schedule.schedule_date;
                //     copiedEventObject.end = val.schedule.schedule_date;
                //     copiedEventObject.color = "#cccccc";
                //     copiedEventObject.allDay = true;
                //     copiedEventObject.id = data.bluePrintSeminars.id + "-" + val.schedule.id;
                //     copiedEventObject.event_id = data.bluePrintSeminars.id;
                //     copiedEventObject.className = ["plannedSeminar_" + data.bluePrintSeminars.id];
                //     copiedEventObject.event_schedule = data.bluePrintSeminars.event_schedule;
                //     copiedEventObject.event_days = val.schedule.event_days;
                //     copiedEventObject.event_status = data.bluePrintSeminars.event_status;
                //     copiedEventObject.locationConflicted  = val.schedule.locationConflicted;
                //     copiedEventObject.trainerConflicted  = val.schedule.trainerConflicted;
                //
                //     dateAdd.push(copiedEventObject);
                //
                // });
                //
                // $('#calendar').fullCalendar('addEventSource', dateAdd);
                // $('#calendar').fullCalendar('rerenderEvents');

            } else {
                notify(data.type, data.message);
            }

        }
    });


}

function destroyEditors() {
    if (CKEDITOR.instances.requirements_editor) {
        CKEDITOR.instances.requirements_editor.destroy();
    }
    if (CKEDITOR.instances.content_editor) {
        CKEDITOR.instances.content_editor.destroy();
    }
    if (CKEDITOR.instances.overview_editor) {
        CKEDITOR.instances.overview_editor.destroy();
    }
    if (CKEDITOR.instances.target_group_editor) {
        CKEDITOR.instances.target_group_editor.destroy();
    }
}

function initEditors() {
    if (CKEDITOR.instances.requirements_editor) {
        CKEDITOR.instances.requirements_editor.destroy();
    }

    CKEDITOR.inline('requirements_editor', {
        toolbarGroups: [
            {name: 'others'},
            {name: 'clipboard', groups: ['clipboard']},
            //{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker' ] },
            //{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
            {name: 'basicstyles', groups: ['basicstyles', 'list', 'cleanup', 'indent']},
            {name: 'stylesonly', groups: ['Styles']},
            '/',
            {name: 'paragraph', groups: ['align']},
            {name: 'styles', groups: ['Format', 'Font', 'FontSize']},
            {name: 'colors'}
        ]
    }, {width: 300});

    if (CKEDITOR.instances.content_editor) {
        CKEDITOR.instances.content_editor.destroy();
    }

    CKEDITOR.inline('content_editor', {
        toolbarGroups: [
            {name: 'others'},
            {name: 'clipboard', groups: ['clipboard']},
            //{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker' ] },
            //{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
            {name: 'basicstyles', groups: ['basicstyles', 'list', 'cleanup', 'indent']},
            {name: 'stylesonly', groups: ['Styles']},
            '/',
            {name: 'paragraph', groups: ['align']},
            {name: 'styles', groups: ['Format', 'Font', 'FontSize']},
            {name: 'colors'}
        ]
    }, {width: 300});

    if (CKEDITOR.instances.overview_editor) {
        CKEDITOR.instances.overview_editor.destroy();
    }

    CKEDITOR.inline('overview_editor', {
        toolbarGroups: [
            {name: 'others'},
            {name: 'clipboard', groups: ['clipboard']},
            //{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker' ] },
            //{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
            {name: 'basicstyles', groups: ['basicstyles', 'list', 'cleanup', 'indent']},
            {name: 'stylesonly', groups: ['Styles']},
            '/',
            {name: 'paragraph', groups: ['align']},
            {name: 'styles', groups: ['Format', 'Font', 'FontSize']},
            {name: 'colors'}
        ]
    }, {width: 300});

    if (CKEDITOR.instances.target_group_editor) {
        CKEDITOR.instances.target_group_editor.destroy();
    }

    CKEDITOR.inline('target_group_editor', {
        toolbarGroups: [
            {name: 'others'},
            {name: 'clipboard', groups: ['clipboard']},
            //{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker' ] },
            //{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
            {name: 'basicstyles', groups: ['basicstyles', 'list', 'cleanup', 'indent']},
            {name: 'stylesonly', groups: ['Styles']},
            '/',
            {name: 'paragraph', groups: ['align']},
            {name: 'styles', groups: ['Format', 'Font', 'FontSize']},
            {name: 'colors'}
        ]
    }, {width: 300});
    for (instance in CKEDITOR.instances) {
        var editor = CKEDITOR.instances[instance];
        if (editor) {
            // Call showToolBarDiv() when editor get the focus
            editor.on('focus', function (event) {
                showToolBarDiv(event);
            });

            // Call hideToolBarDiv() when editor loses the focus
            editor.on('blur', function (event) {
                hideToolBarDiv(event);
            });

            //Whenever CKEditor get focus. We will show the toolbar span.
            function showToolBarDiv(event) {
                //'event.editor.id' returns the id of the spans used in ckeditr.
                $('#' + event.editor.id + '_top').show();
            }

            function hideToolBarDiv(event) {
                //'event.editor.id' returns the id of the spans used in ckeditr.
                $('#' + event.editor.id + '_top').hide()
            }
        }
    }

}


// function initSelectDropDownForEditDuplicate() {
//     console.log($(".trainer_div"));
//     $body.find(".trainer_div").each(function (i, value) {
//         console.log(i, "index");
//         $('.scheduleTrainers_' + i).select2({
//             tags: true,
//             tokenSeparators: [','],
//             createSearchChoice: function (term) {
//                 return false;
//             },
//             query: {
//                 url: base_url + 'event/getTrainersByTerm',
//                 dataType: 'json',
//                 data: function (term, page) {
//                     return {
//                         q: term,
//                         trainer: $("[name=trainer]").val()
//                     };
//                 },
//                 results: function (data, page) {
//                     return {
//                         results: data
//                     };
//                 }
//             },
//             initSelection: function (element, callback) {
//                 var data = [];
//
//                 function splitVal(string, separator) {
//                     var val, i, l;
//                     if (string === null || string.length < 1) return [];
//                     val = string.split(separator);
//                     for (i = 0, l = val.length; i < l; i = i + 1) val[i] = $.trim(val[i]);
//                     return val;
//                 }
//
//                 var selectedTrainers = jQuery.parseJSON($('#trainer_data_' + i).val());
//                 console.log(selectedTrainers);
//                 $(splitVal(element.val(), ",")).each(function (e, val) {
//                     if (selectedTrainers != "") {
//                         var result = $.grep(selectedTrainers, function (e) {
//                             return e.id == val;
//                         });
//                         if (result.length > 0) {
//                             data.push({
//                                 id: result[0].id,
//                                 text: result[0].text
//                             });
//                         }
//                     }
//                 });
//                 query.callback(data);
//             }
//         });
//     });
// }


// When planned event is drag and recalualte update the schedule date from server side
function updatePlannedEvent(blueprintEventObject, actionType) {
    console.log(actionType);
    var scheduleObjectArray = new Array();
    $(blueprintEventObject).each(function (index, val) {
        var scheduleObject = new Object();
        scheduleObject.scheduleDate = moment(val.start).format("YYYY-MM-DD");
        scheduleObject.scheduleId = val.id.split("-")[1];
        scheduleObjectArray.push(scheduleObject);
    });

    var reasonForMoveSeminar = $("#seminarMoveReason").val();
    // close seminar move modal
    $("#seminarMove").modal("hide");

    $.ajax({
        url: base_url + "seminar-planner/updatePlannedEvent",
        method: "POST",
        data: {
            "schedules": scheduleObjectArray,
            "blueprintEventId": originalDragDateObj.id,
            "actionType": actionType,
            "moveReason": reasonForMoveSeminar
        },
        beforeSend: function () {
            blockUI(".page-container");
        },
        success: function (data) {
            console.log(data);

            if (data.type == "success") {
                dateAdd = new Array();
                $("#calendar").fullCalendar('refetchEvents');
                unBlockUI(".page-container");
                if (data.bluePrintSeminars.event_status != "draft" || data.bluePrintSeminars.event_status == "") {
                    askToInformParticipant(data.participants, "move-seminar");
                } else {
                    notify('success', draftRecalculated);
                }
                // call function to ask for inform participant if any

                // $(data.bluePrintSeminars.event_schedule).each(function (index, val) {
                //
                //     var copiedEventObject = new Object();
                //     copiedEventObject.title = data.bluePrintSeminars.event_name + " - Day - " + val.schedule.event_days;
                //     copiedEventObject.event_name = data.bluePrintSeminars.event_name;
                //     copiedEventObject.LocationName = val.schedule.schedule_location.LocationName;
                //     copiedEventObject.start = val.schedule.schedule_date;
                //     copiedEventObject.end = val.schedule.schedule_date;
                //     copiedEventObject.color = (val.schedule.locationConflicted == 1 || val.schedule.trainerConflicted == 1) ? "#dfba49" : "#cccccc";
                //     //copiedEventObject.color = data.bluePrintSeminars.color;
                //     copiedEventObject.allDay = true;
                //     copiedEventObject.event_id = data.bluePrintSeminars.id;
                //     copiedEventObject.id = data.bluePrintSeminars.id + "-" + val.schedule.id;
                //     copiedEventObject.className = ["plannedSeminar_" + data.bluePrintSeminars.id];
                //     copiedEventObject.event_schedule = data.bluePrintSeminars.event_schedule;
                //     copiedEventObject.event_days = val.schedule.event_days;
                //     copiedEventObject.event_status = data.bluePrintSeminars.event_status;
                //     copiedEventObject.locationConflicted  = val.schedule.locationConflicted;
                //     copiedEventObject.trainerConflicted  = val.schedule.trainerConflicted;
                //
                //     dateAdd.push(copiedEventObject);
                //
                // });

                // $('#calendar').fullCalendar('addEventSource', dateAdd);
                // $('#calendar').fullCalendar('rerenderEvents');

            }

        }
    });

}


var slotsArray = new Array();
var conflictEvent;
var isConflict = false;

function removeErrorMessageOfSelect2() {
    $('.scheduleTrainers, .scheduleTrainersClone').off('change');
    $('.scheduleTrainers, .scheduleTrainersClone').on('change', function () {
        $('input[name*=trainer]').each(function () {
            if ($(this).val()) {
                $(this).parent().find('span.error').remove();
            }
        });
    });
}


function initSelectDropDown($className) {
    removeErrorMessageOfSelect2();
    $('.scheduleTrainers').select2({
        tags: true,
        tokenSeparators: [','],
        createSearchChoice: function (term) {
            return false;
        },
        ajax: {
            url: base_url + 'event/getTrainersByTerm',
            dataType: 'json',
            data: function (term, page) {
                return {
                    q: term,
                    trainer: $("[name=trainer]").val()
                };
            },
            results: function (data, page) {
                return {
                    results: data
                };
            }
        },

        // Take default tags from the input value
        initSelection: function (element, callback) {
            var data = [];

            function splitVal(string, separator) {
                var val, i, l;
                if (string === null || string.length < 1)
                    return [];
                val = string.split(separator);
                for (i = 0, l = val.length; i < l; i = i + 1)
                    val[i] = $.trim(val[i]);
                return val;
            }

            var selectedTrainers = jQuery.parseJSON($('#scheduleDefaultTrainerData').val());
            console.log(selectedTrainers);
            // alert(selectedTrainers);
            $(splitVal(element.val(), ",")).each(function (e, val) {
                if (selectedTrainers) {
                    var result = $.grep(selectedTrainers, function (e) {
                        return e.id == val;
                    });
                    //   console.log(result);
                    if (result.length > 0) {
                        data.push({
                            id: result[0].id,
                            text: result[0].text
                        });
                    }
                }
            });


            callback(data);
        }

    });
}

function initScheduleDefaultSelectDropDown() {
    removeErrorMessageOfSelect2();
    $('.scheduleDefaultTrainers').select2({
        tags: true,
        tokenSeparators: [','],
        createSearchChoice: function (term) {
            return false;
        },
        ajax: {
            url: base_url + 'event/getTrainersByTerm',
            dataType: 'json',
            data: function (term, page) {
                return {
                    q: term,
                    trainer: $("[name=trainer]").val()
                };
            },
            results: function (data, page) {
                return {
                    results: data
                };
            }
        },

        // Take default tags from the input value
        initSelection: function (element, callback) {
            var data = [];

            function splitVal(string, separator) {
                var val, i, l;
                if (string === null || string.length < 1)
                    return [];
                val = string.split(separator);
                for (i = 0, l = val.length; i < l; i = i + 1)
                    val[i] = $.trim(val[i]);
                return val;
            }

            var selectedTrainers = jQuery.parseJSON($('#scheduleDefaultTrainerData').val());
            // alert(selectedTrainers);
            $(splitVal(element.val(), ",")).each(function (e, val) {
                if (selectedTrainers) {
                    var result = $.grep(selectedTrainers, function (e) {
                        return e.id == val;
                    });
                    //   console.log(result);
                    if (result.length > 0) {
                        data.push({
                            id: result[0].id,
                            text: result[0].text
                        });
                    }
                }
            });

            callback(data);
        }

    });
}


function initSelectDropDownByClass($className) {
    removeErrorMessageOfSelect2();
    $('.' + $className).select2({
        tags: true,
        tokenSeparators: [','],
        createSearchChoice: function (term) {
            return false;
        },
        ajax: {
            url: base_url + 'event/getTrainersByTerm',
            dataType: 'json',
            data: function (term, page) {
                return {
                    q: term,
                    trainer: $("[name=trainer]").val()
                };
            },
            results: function (data, page) {
                return {
                    results: data
                };
            }
        },

        // Take default tags from the input value
        initSelection: function (element, callback) {
            var data = [];

            function splitVal(string, separator) {
                var val, i, l;
                if (string === null || string.length < 1)
                    return [];
                val = string.split(separator);
                for (i = 0, l = val.length; i < l; i = i + 1)
                    val[i] = $.trim(val[i]);
                return val;
            }

            var selectedTrainers = jQuery.parseJSON($('#trainer_data').val());
            // alert(selectedTrainers);
            $(splitVal(element.val(), ",")).each(function (e, val) {
                if (selectedTrainers) {
                    var result = $.grep(selectedTrainers, function (e) {
                        return e.id == val;
                    });
                    //   console.log(result);
                    if (result.length > 0) {
                        data.push({
                            id: result[0].id,
                            text: result[0].text
                        });
                    }
                }
            });

            callback(data);
        }

    });
}

function initSelectDropDownClone(traner_div) {
    removeErrorMessageOfSelect2();
    $('#' + traner_div).select2({
        tags: true,
        tokenSeparators: [','],
        createSearchChoice: function (term) {
            return false;
        },
        ajax: {
            url: base_url + 'event/getTrainersByTerm',
            dataType: 'json',
            data: function (term, page) {
                return {
                    q: term,
                    trainer: $("[name=trainer]").val()
                };
            },
            results: function (data, page) {
                console.log(data)
                return {
                    results: data
                };
            }
        },
    });
}

function initSelectDropDownForEditDuplicate() {
    console.log($(".trainer_div"));
    $body.find(".trainer_div").each(function (i, value) {
        console.log(i, "index");
        $('.scheduleTrainers_' + i).select2({
            tags: true,
            tokenSeparators: [','],
            createSearchChoice: function (term) {
                return false;
            },
            ajax: {
                url: base_url + 'event/getTrainersByTerm',
                dataType: 'json',
                data: function (term, page) {
                    return {
                        q: term,
                        trainer: $("[name=trainer]").val()
                    };
                },
                results: function (data, page) {
                    return {
                        results: data
                    };
                }
            },
            initSelection: function (element, callback) {
                var data = [];

                function splitVal(string, separator) {
                    var val, i, l;
                    if (string === null || string.length < 1)
                        return [];
                    val = string.split(separator);
                    for (i = 0, l = val.length; i < l; i = i + 1)
                        val[i] = $.trim(val[i]);
                    return val;
                }

                var selectedTrainers = jQuery.parseJSON($('#trainer_data_' + i).val());
                $(splitVal(element.val(), ",")).each(function (e, val) {
                    if (selectedTrainers) {
                        var result = $.grep(selectedTrainers, function (e) {
                            return e.id == val;
                        });
                        if (result.length > 0) {
                            data.push({
                                id: result[0].id,
                                text: result[0].text
                            });
                        }
                    }
                });
                callback(data);
            }
        });
    });
}

function checkSlotConflit(event1, event2) {
    return event1.start < event2.start
            ? checkConflict(event1, event2)
            : checkConflict(event2, event1)

    function checkConflict(first, second) {
        if (first.end > second.start && (first.roomId == second.roomId || first.trainers == second.trainers)) {
            first.hasConflict = second.hasConflict = true;
            conflictEvent = first;
            return true;
        } else {
            first.hasConflict = second.hasConflict = false;
        }
    }
}


function prepareSlotArray() {
    slotsArray = [];
    $(".scheduleSlotList .slot").each(function () {
        var obj = new Object();
        //obj.start =  (app_time_format == "H:i") ? ("0" + $(this).find(".start_time").val()).slice(-5) : $(this).find(".start_time").val() ;
        //obj.end = (app_time_format == "H:i") ? ( "0" + $(this).find(".end_time").val()).slice(-5) : $(this).find(".end_time").val();
        obj.start = moment($(this).find(".start_time").val(), [app_time_format_moment_js]).format("HH:mm");
        obj.end = moment($(this).find(".end_time").val(), [app_time_format_moment_js]).format("HH:mm");
        ;
        obj.trainerId = $(this).find(".end_time").val();
        obj.roomId = $(this).find("[name='roomId[]']").val();
        obj.trainers = $(this).find("[name='trainer[]']").val();
        obj.panelClass = $(this).parents(".panel-default").attr('id');
        obj.hasConflict = false;
        console.log(obj);
        slotsArray.push(obj);
    });
}

// Fill the  start and end time of the slot to add new slot form
function getNextSlotTimeRange() {
    // set start and end time value based on the avaiable slot

    $recentSlot = slotsArray.reduce(function (prev, curr) {
        return prev.start > curr.start ? prev : curr;
    });

    var startTime = moment($recentSlot.end, ["HH:mm"]).format(app_time_format_moment_js)
    var endTime = moment($recentSlot.end, ['HH:mm']).add(slot_default_duration, 'hours').format(app_time_format_moment_js);

    $(".schedule_add_slot").find(".start_time").val(startTime);
    $(".schedule_add_slot").find(".end_time").val(endTime);
}

// on Duplicate slot add next time slot
function setNextSlotTimeRageOnDuplicate($slotCloneDiv) {
    $recentSlot = slotsArray.reduce(function (prev, curr) {
        return prev.start > curr.start ? prev : curr;
    });

    var startTime = moment($recentSlot.end, ["HH:mm"]).format(app_time_format_moment_js)
    var endTime = moment($recentSlot.end, ['HH:mm']).add(slot_default_duration, 'hours').format(app_time_format_moment_js);

    $slotCloneDiv.find(".start_time").val(startTime);
    $slotCloneDiv.find(".end_time").val(endTime);
    console.log($slotCloneDiv, "slot clone dive");
    console.log($slotCloneDiv.find(".slot_time"), "slot time range");
    $slotCloneDiv.find("[name='slot_id[]']").remove();
    $slotCloneDiv.find("[data-index='slot_time']").html(startTime + " - " + endTime);

    // Add new slot into array

    var startTimeForCalculation = moment(startTime, [app_time_format_moment_js]).format("HH:mm");
    var endTimeForCalculation = moment(endTime, [app_time_format_moment_js]).format("HH:mm");

    var newSlot = new Object();
    newSlot.start = startTimeForCalculation;
    newSlot.end = endTimeForCalculation;
    newSlot.hasConflict = false;
    newSlot.panelClass = $slotCloneDiv.attr('id');
    slotsArray.push(newSlot);

    // change time for add new slot
    getNextSlotTimeRange();

}

// on delete slot remove that slot from the slotArray
function removeSlotFromArray($slotPanelId) {
    console.log($slotPanelId);
    slotsArray = slotsArray.filter(function (obj) {
        return obj.panelClass != $slotPanelId;
    });

    // change time for add new slot
    getNextSlotTimeRange();

}

function getScheduleAndSlotForSeminar($eventId) {
    $.ajax({
        url: base_url + "seminar-planner/planned-seminar/get-schedule-slot/" + $eventId,
        method: "GET",
        beforeSend: function () {
            blockUI(".page-container");
        },
        success: function (data) {
            unBlockUI(".page-container");
            if (data.type == "success") {
                // clear all the HTML
                $(".trainerList").empty();
                $(".locationList").empty();
                $(".scheduleList").empty();
                $(".slotList").empty();

                // Set event name for modal popup
                $(".seminarNameAsHeading").html(data.plannedEvent.event_name)

                // append trainer list
                $("#trainerListTemplate")
                        .tmpl(data.trainers)
                        .appendTo(".trainerList");

                // append Locations
                $("#LocationListTemplate")
                        .tmpl(data.locations)
                        .appendTo(".locationList");

                // append schedule days
                $("#scheduleTemplate")
                        .tmpl(data.plannedEvent.event_schedule)
                        .appendTo(".scheduleList");

                // append schedule days
                $("#slotTemplate")
                        .tmpl(data.plannedEvent.event_schedule[0].schedule.event_schedule_slot)
                        .appendTo(".slotList");

                reassignDragDropEvent();

                $(".itemSchedule:first").trigger("click");

                // Open modal to show trains and schedule for the assignemtn
                $("#assignTrainerLocationPopup").modal("show");


            }
        }
    });
}

function assignLocationToSchedule(dragElement, dropElement) {
    $.ajax({
        url: base_url + "seminar-planner/assign-location-to-schedule/" + dragElement.LocationID + "/" + dropElement.schedule.id + "/" + dropElement.schedule.schedule_date + "?eventId=" + dropElement.event_id,
        method: "GET",
        beforeSend: function () {
            blockUI(".modal-dialog");
        },
        success: function (data) {
            unBlockUI(".modal-dialog");
            if (data.type == "success") {
                $(".scheduleList").empty();
                $(".slotList").empty();
                $("#scheduleTemplate")
                        .tmpl(data.plannedEvent.event_schedule)
                        .appendTo(".scheduleList");

                // append schedule days
                $("#slotTemplate")
                        .tmpl(data.plannedEvent.event_schedule[0].schedule.event_schedule_slot)
                        .appendTo(".slotList");

                // Notify the messagee
                notify("success", data.message);

                reassignDragDropEvent();
            } else {
                // Notify the messagee
                notify(data.type, data.message);
            }
        }
    });

}

function assignTrainerToSlot(dragElement, dropElement) {
    var eventId = $.tmplItem($(".currentActiveSchedule")).data.event_id;
    var scheduleDay = $(".currentActiveSchedule").attr("scheduleDay");
    $.ajax({
        url: base_url + "seminar-planner/assign-trainer-to-slot/" + dropElement.schedule_slotID + "/" + dropElement.ScheduleID + "/" + dragElement.PersonID + "?eventId=" + eventId,
        method: "GET",
        beforeSend: function () {
            blockUI(".modal-dialog");
        },
        success: function (data) {
            unBlockUI(".modal-dialog");
            if (data.type == "success") {
                $(".scheduleList").empty();
                $(".slotList").empty();
                $("#scheduleTemplate")
                        .tmpl(data.plannedEvent.event_schedule)
                        .appendTo(".scheduleList");

                // append schedule days
                $("#slotTemplate")
                        .tmpl(data.plannedEvent.event_schedule[0].schedule.event_schedule_slot)
                        .appendTo(".slotList");

                reassignDragDropEvent();

                // Notify the messagee
                notify("success", data.message);

                // Trigger the last selected schedule days
                $("[scheduleday='" + scheduleDay + "']").trigger("click");
            } else {
                // Notify the messagee
                notify(data.type, data.message);
            }
        }
    });

}

function assignRoomToSlot(roomId, dropElement) {
    var eventId = $.tmplItem($(".currentActiveSchedule")).data.event_id;
    var scheduleDay = $(".currentActiveSchedule").attr("scheduleDay");
    $.ajax({
        url: base_url + "seminar-planner/assign-room-to-slot/" + dropElement.schedule_slotID + "/" + dropElement.ScheduleID + "/" + roomId + "?eventId=" + eventId,
        method: "GET",
        beforeSend: function () {
            blockUI(".modal-dialog");
        },
        success: function (data) {
            unBlockUI(".modal-dialog");
            if (data.type == "success") {
                $(".scheduleList").empty();
                $(".slotList").empty();
                $("#scheduleTemplate")
                        .tmpl(data.plannedEvent.event_schedule)
                        .appendTo(".scheduleList");

                // append schedule days
                $("#slotTemplate")
                        .tmpl(data.plannedEvent.event_schedule[0].schedule.event_schedule_slot)
                        .appendTo(".slotList");

                reassignDragDropEvent();

                // Notify the messagee
                notify("success", data.message);

                // Trigger the last selected schedule days
                $("[scheduleday='" + scheduleDay + "']").trigger("click");
            } else {
                // Notify the messagee
                notify(data.type, data.message);
            }
        }
    });

}

function reassignDragDropEvent() {
    $(".itemLocation, .itemTrainer, .itemRoom").draggable({
        zIndex: 999999,
        revert: true, // will cause the event to go back to its
        revertDuration: 0, // original position after the drag
        appendTo: 'body',
        scroll: false,
        helper: 'clone',
        drag: function () {
            if ($(this).hasClass('itemLocation')) {
                $(".scheduleListWrapper").css("background", "#f5f5f5");
                $(".scheduleListWrapper").css("border", "#ccc dotted");
            }
            if ($(this).hasClass('itemRoom') || $(this).hasClass('itemTrainer')) {
                $(".SlotListWrapper").css("background", "#f5f5f5");
                $(".SlotListWrapper").css("border", "#ccc dotted");
            }
        },
        stop: function () {

            $(".scheduleListWrapper").css("background", "none");
            $(".scheduleListWrapper").css("border", "none");
            $(".SlotListWrapper").css("background", "none");
            $(".SlotListWrapper").css("border", "none");

        },

    });


    $(".itemSchedule").droppable({
        accept: ".itemLocation",
        classes: {
            "ui-droppable-active": "ui-state-active",
            "ui-droppable-hover": "ui-state-hover"
        },
        drop: function (event, ui) {

            var dragElement = $.tmplItem(ui.draggable[0]).data;
            var dropElement = $.tmplItem(this).data;
            if (dropElement.schedule.LocationID != '' && dropElement.schedule.LocationID != dragElement.LocationID) {
                bootbox.confirm({
                    message: eventConfirmChangeLocation,
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
                            assignLocationToSchedule(dragElement, dropElement);
                            event.preventDefault();
                        }
                    }
                });
            } else {
                assignLocationToSchedule(dragElement, dropElement);
                event.preventDefault();
            }

        }
    });

    $(".itemSlot").droppable({
        accept: ".itemTrainer, .itemRoom",
        classes: {
            "ui-droppable-active": "ui-state-active",
            "ui-droppable-hover": "ui-state-hover"
        },
        drop: function (event, ui) {
            var dragElement = $.tmplItem(ui.draggable[0]).data;
            var dropElement = $.tmplItem(this).data;

            if ($(ui.draggable[0]).hasClass("itemRoom")) {
                var locationId = $(ui.draggable[0]).attr("locatonid");
                var roomId = $(ui.draggable[0]).attr("roomid");
                var currentLocationId = $.tmplItem($(".currentActiveSchedule")).data;
                if (locationId == currentLocationId.schedule.LocationID)
                    assignRoomToSlot(roomId, dropElement);
                else
                    notify("error", errorSameLocation);
            } else if ($(ui.draggable[0]).hasClass("itemTrainer")) {
                assignTrainerToSlot(dragElement, dropElement);
            }
            // console.log(dragElement, "Drag Elemnt");
            // console.log(dropElement, "Drop Elemnt");
            event.preventDefault();

        }
    });

    //assign popup
    $('[data-toggle="popover"]').popover();
}

function downloadURI(uri, name) {
    console.log(uri);
    console.log(name);
    var link = document.createElement("a");
    link.download = name;
    link.href = asset_url + '/' + uri;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    delete link;
}

// Get confirm semina and ask if conflict is there
function checkSeminarIsReadyToConfirm(eventId) {
    console.log(eventId);
    if (clickEvent.locationConflicted == 1 || clickEvent.trainerConflicted == 1) {

        bootbox.confirm({
            message: eventConflictMessageOnConfirmation,
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
                    confirmSeminar(eventId);
                } else {
                    getScheduleAndSlotForSeminar(eventId);
                }
            }
        });
    } else {
        confirmSeminar(eventId);
    }
}

function confirmSeminar($eventId) {
    $.ajax({
        url: base_url + "seminar-planner/confirm-seminar/" + $eventId,
        method: "GET",
        beforeSend: function () {
            blockUI(".page-container");
        },
        success: function (data) {
            unBlockUI(".page-container");
            if (data.type == "success") {
                notify("success", data.message);
                $("#calendar").fullCalendar('refetchEvents');
            } else if (data.type == "danger") {
                getScheduleAndSlotForSeminar(eventId);
            } else {
                notify("error", data.message);
            }
        }
    });
}

function cancelSeminar($eventId) {
    bootbox.confirm({
        message: seminarCancelConfirmationMessage,
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
                $("#seminarCancellation").modal("show");
            }
        }
    });

}

function deleteSeminar($eventId) {
    bootbox.confirm({
        message: seminarDeleteConfirmationMessage,
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
                markSeminarAsDelete();
            }
        }
    });

}

// change the status of the seminar and store reason
function markSeminarAsCancel($eventId) {
    var reasonForCancellation = $("#seminarCancelReason").val();
    if (reasonForCancellation == "") {
        notify('error', validation_message);
        return false;
    }
    $.ajax({
        url: base_url + "seminar-planner/cancel-seminar/" + clickEventId,
        method: "POST",
        data: {"cancelReason": reasonForCancellation},
        beforeSend: function () {
            blockUI(".modal-content");
        },
        success: function (data) {
            unBlockUI(".modal-content");
            $("#seminarCancellation").modal("hide");
            if (data.type == "success") {
                if (user_level == 1) {
                    informLevel2User(data.users_list,data.participants);
                } 
                
                
                notify("success", seminarCancelSuccess);
                trainerListForSeminar = data.trainers;
                locationListForSeminar = data.locations;
                
                $("#calendar").fullCalendar('refetchEvents');
            } else {
                notify("error", data.message);
            }
        }
    });
}

function markSeminarAsDelete($eventId) {
    $.ajax({
        url: base_url + "seminar-planner/delete-seminar/" + clickEventId,
        method: "GET",

        beforeSend: function () {
            blockUI(".modal-content");
        },
        success: function (data) {
            if (data.type == "success") {
                $("#calendar").fullCalendar('refetchEvents');
                notify("success", data.message);
            } else {
                notify("error", data.message);
            }
            unBlockUI(".modal-content");
        }
    });
}

// action taken after move dates and after writing reason for movement
function actionsAfterRecalculateDate() {
    var classNam = originalDragDateObj.className[0];
    $('#calendar').fullCalendar('removeEvents', function (event) {
        return event.className == classNam;
    });

    // Update the schedules on recalulation of the days
    console.log(dateAdd);
    updatePlannedEvent(dateAdd, "drag-recalculate");
}

// ASk to inform participant based on action and participant count
function informLevel2User(users,participants) {
    level2UserList = users;
    seminar_planner_participants=participants;
    bootbox.confirm({
        message: askToSendEmailToLevel2User,
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
            if (response) {
                $(".informLevel2UserOnCancelSeminar").trigger("click");
            }else{
                askToInformParticipant(participants, "cancel-seminar");
            }

        }
    });
}

function askToInformParticipant(participants, action) {
    var informParticipant = 0;
    var createTaskCancelTrainer = 0;
    var createTaskCancelLocation = 0;
    participantListForSeminar = participants;

    // if(participantListForSeminar.length > 0) {
    bootbox.confirm({
        message: askToCreateTaskToCancelTrainer,
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
                createTaskCancelTrainer = 1;
            }
            bootbox.confirm({
                message: askToCreateTaskToCancelLocation,
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
                        createTaskCancelLocation = 1;
                    }
                    // Call ajax to create task based on user input
                    createTasksForSeminar(createTaskCancelTrainer, createTaskCancelLocation, action);
                    bootbox.confirm({
                        message: askToInformParticipantMessage,
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
                                if (participantListForSeminar.length > 0) {
                                    informParticipant = 1;
                                    if (action == 'cancel-seminar') {
                                        $(".informParticipantOnCancelSeminar").trigger("click");
                                    } else if (action == 'move-seminar') {
                                        $(".informParticipantOnChangeSeminar").trigger("click");
                                    }
                                } else {
                                    notify("warning", noParticipantRegisterWarning);
                                }

                            } else {
                                askToInformTrainer();
                            }
                            // Call ajax to inform participants
                            console.log("trigger event to popup to send email");
                        }
                    });
                }
            });

        }
    });
    // }else{
    //    notify("warning", noParticipantRegisterWarning);
    //
    // }
}

function askToInformTrainer() {
    bootbox.confirm({
        message: askToInformTrainerMessage,
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
                // Trigger button to show email popup
                $(".informTrainerOnCancelSeminar").trigger("click");
            } else {
                askToInformLocation();
            }
        }
    });
}

function askToInformLocation() {
    bootbox.confirm({
        message: askToInformLocationMessage,
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
                // Trigger button to show email popup
                $(".informLocationOnCancelSeminar").trigger("click");
            }
        }
    });
}

function createTasksForSeminar(createTaskCancelTrainer, createTaskCancelLocation, action) {

    var eventId = action == "cancel-seminar" ? clickEventId : originalDragDateObj.event_id;
    if (createTaskCancelTrainer == 1 || createTaskCancelLocation == 1) {
        $.ajax({
            url: base_url + "seminar-planner/create-task/" + eventId + "/" + createTaskCancelTrainer + "/" + createTaskCancelLocation + "/" + action,
            method: "GET",
            beforeSend: function () {
                blockUI(".page-container");
            },
            success: function (data) {
                unBlockUI(".page-container");
                if (data.type == "success") {
                    //notify("success", seminarCancelSuccess)
                }
            }
        });
    }
}

function getDashboardTaskEvent() {
    var task_planned_event = $(".task_planned_event").val();
    if (task_planned_event != "") {
        console.log("Asdadasdad=>", task_planned_event);
        get_seminar_planner_event_id = "/" + task_planned_event;
        $(".button-next").trigger('click');
    }

}


var getFromBetween = {
    results: [],
    string: "",
    getFromBetween: function (sub1, sub2) {
        if (this.string.indexOf(sub1) < 0 || this.string.indexOf(sub2) < 0)
            return false;
        var SP = this.string.indexOf(sub1) + sub1.length;
        var string1 = this.string.substr(0, SP);
        var string2 = this.string.substr(SP);
        var TP = string1.length + string2.indexOf(sub2);
        return this.string.substring(SP, TP);
    },
    removeFromBetween: function (sub1, sub2) {
        if (this.string.indexOf(sub1) < 0 || this.string.indexOf(sub2) < 0)
            return false;
        var removal = sub1 + this.getFromBetween(sub1, sub2) + sub2;
        this.string = this.string.replace(removal, "");
    },
    getAllResults: function (sub1, sub2) {
        // first check to see if we do have both substrings
        if (this.string.indexOf(sub1) < 0 || this.string.indexOf(sub2) < 0)
            return;

        // find one result
        var result = this.getFromBetween(sub1, sub2);
        // push it to the results array
        this.results.push(result);
        // remove the most recently found one from the string
        this.removeFromBetween(sub1, sub2);

        // if there's more substrings
        if (this.string.indexOf(sub1) > -1 && this.string.indexOf(sub2) > -1) {
            this.getAllResults(sub1, sub2);
        } else
            return;
    },
    get: function (string, sub1, sub2) {
        this.results = [];
        this.string = string;
        this.getAllResults(sub1, sub2);
        return this.results;
    }
};
function setSeminarPlannerData(eventID, additionalData) {
    $.ajax({
        url: base_url + 'seminar-planner/updatePlannedMinMaxData/' + eventID + additionalData,
        type: 'get',
        beforeSend: function (data) {
            blockUI(".modal-content");
        },
        success: function (data) {
            if (data.type == 'error') {
                notify(data.type, data.message);
                $('.external_id_save').val('').focus();
            }
            unBlockUI(".modal-content");
        }
    });
}

function replaceTranslatables(message) {
    if (seminarPlannerTrans) {
        var vars = getFromBetween.get(message, '#', '#');
        for (i in vars) {
            if (seminarPlannerTrans[vars[i]]) {
                message = message.replace(new RegExp('#' + vars[i] + '#', 'g'), seminarPlannerTrans[vars[i]]);
            }
        }
    }
    return message;
}


