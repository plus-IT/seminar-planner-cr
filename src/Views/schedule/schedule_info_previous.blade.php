<div class="control-group clearfix ">
    <div class="tools text-right col-md-12">
        {{--@can('event.activity.create')--}}
        {{--<span><a href="{!! url(LaravelLocalization::getCurrentLocale() . "/" ."schedule/0") !!}"--}}
        <span><a  class="add_schedule btn btn-circle btn-icon-only btn-default pull-right"
                 ><i
                        class="fa fa-plus"></i></a></span>&nbsp;&nbsp;&nbsp;
        {{--@endcan--}}
        {{--@can('event.activity.update')--}}
        {{--<span><a data-href="{!! url(LaravelLocalization::getCurrentLocale() . "/" ."schedule") !!}" href=""--}}
                 {{--data-target="#add_schedule" class="edit_schedule"--}}
                 {{--data-toggle="modal"><i--}}
                        {{--class="fa fa-pencil"></i></a></span>&nbsp;&nbsp;&nbsp;--}}
        {{--@endcan--}}
        {{--@can('event.activity.delete')--}}
        <span><a style="display: none" data-href="{!! url(LaravelLocalization::getCurrentLocale() . "/" ."schedule") !!}" href=""
                 class=" delete-schedule"><i
                        class="fa fa-minus"></i></a></span>&nbsp;&nbsp;&nbsp;
        {{--@endcan--}}
    </div>
    <div class="clear">&nbsp;</div>

    <div class="col-md-12 v">
        <div class="table-responsive">
            <table class="table table-hover dataTable" id="schedule_table">
                <thead>
                    <tr>
                        <th>{!! Html::customTrans("label.begin") !!}</th>
                        <th>{!! Html::customTrans("label.end") !!}</th>
                        <th>{!! Html::customTrans("label.description") !!}</th>
                        <th>{!! Html::customTrans("label.trainer") !!}</th>
                        <th>{!! Html::customTrans("label.location") !!}</th>
                        <th>{!! Html::customTrans("label.action") !!}</th>
                    </tr>
                </thead>
                <tbody>
                @if(isset($schedule_data))
                    @foreach ($schedule_data->eventSchedule as $schedule)
                        {{--<div>{!! dd($schedule_data->eventSchedule) !!}</div>--}}
                        <tr data-recordid="{!! $schedule->schedule->id !!}" class="schedule_row">
                            <td data-index="begin">{!! format_datetime($schedule->schedule->begin) !!}</td>
                            <td data-index="end">{!! format_datetime($schedule->schedule->end) !!}</td>
                            <td data-index="description">{!! $schedule->schedule->description !!}</td>
                            <td data-index="trainer" data-trainerid="{!! $schedule->schedule->trainer_ids !!}" >{!! $schedule->schedule->trainers !!}</td>
                            <td data-index="organization_id" data-organizationid="{!! !empty($schedule->schedule->organization_id) ? $schedule->schedule->organization_id : ''  !!}">{!! !empty($schedule->schedule->organization->CustomerName) ? $schedule->schedule->organization->CustomerName : '' !!}</td>
                            <td data-index="action">
                                <a  style="display: inline-block" class="remove" href=""><i id="remove" class="fa fa-trash-o"></i></a>
                            </td>
                        </tr>
                    @endforeach
                @endif
                </tbody>
            </table>
        </div>

    </div>
</div>
<div class="event-calendar-wrapper col-md-12 clearfix" style="margin-top: 30px">
    <div id="fullcalendar"></div>
</div>

<div class="modal fade" id="add_schedule" tabindex="-1" role="add_schedule" aria-hidden="true">
    <div class="modal-dialog" style="width: 800px!important;">
        <div class="modal-content">
            @include('event.add_schedule')
        </div>
    </div>
</div>
<div class="cloneTable" style="display: none">
    <table>
        <tbody>
        <tr data-recordid="" class="schedule_row" >
            <td data-index="begin"></td>
            <td data-index="end"></td>
            <td data-index="description"></td>
            <td data-index="trainer"></td>
            <td data-index="organization_id"></td>
        </tr>
        </tbody>
    </table>
</div>

<select class="small-select  form-control select2 cloneOrganizationList hide"  >
    <option value="">{!! Html::customTrans("label.please_select") !!}</option>
    @if(!empty($all_organization))
        @foreach($all_organization as $val)
            <option value="{!! $val->OrganizationID !!}" {!! (!empty($schedule_data->organization_id)) ? (($schedule_data->organization_id == $val->OrganizationID) ? 'selected' : '') : '' !!}>{!! $val->CustomerName !!}</option>
        @endforeach
    @endif

</select>

<script>
    $source = base_url + "event/calendar/fetchdata?event_id=&location_id=" +
            "&event_category_id=&trainer=";

    $('#fullcalendar').fullCalendar({
        editable: false,
        height: 600,
        allDay : true,
        header: {
            left: 'prev',
            center: 'title',
            right: 'next'
        },
        eventClick: function (calEvent, jsEvent, view) {
            $(".make-switch").each(function(){
               $(this).bootstrapSwitch('state')
            });

        },

        events:$source

    });

    var oTable = $("#schedule_table").DataTable({
        "bFilter": false,"sDom": 'Rfrtlp',"bPaginate": false});


    var nEditing = null;
    var nTd = null;
    var originalRowData = null;
    var editedRowData = [];



    $('.add_schedule').click( function (e) {
        e.preventDefault();
        if(nEditing !== null)
            restoreRow( oTable, nEditing );
        var nRow = oTable.row.add( [ '', '', '', '', '',
            '<a class="edit" href=""><i class="fa fa-pencil"></i></a>&nbsp;&nbsp;<a class="edit" href=""><i class="fa fa-remove"></i></a>']).draw().node();
        bindTableEditEvent();
        editRow( oTable, nRow, 0 );
        nEditing = nRow;
        nTd = $(nRow).find('td').first();
        originalRowData = null;
    });

    $body.on("click",".resetEdit",function (e) {
        e.preventDefault();
        console.log($(this));
        $(this).remove();
        $(this).parents("[data-index='action']").find(".save").remove();
        restoreRow( oTable, nEditing );
    });

    $(".schedule_row td:not(:last-child)").mouseenter(function(){
        $(this).append("<i class='fa fa-pencil'></i>")
    }).mouseleave(function(){
        $(this).find("i").remove();
    });

   /* $body.off("click", ".delete-schedule");
    $body.on("click", ".delete-schedule", function (e) {
        e.preventDefault();
        var $table = $("#schedule_table");

        bootbox.confirm("Are You sure you want to remove Selected Row?", function(response) {
            var tr = $(this);
            if (response == true) {
                console.log(tr);
                tr.remove();
//                $.ajax({
//                    url: $(".delete-schedule").attr("href"),
//                    method: "DELETE",
//                    beforeSend: function () {
//                     blockUI(".page-container");
//                    },
//                    success: function (data) {
//                        unBlockUI(".page-container");
//                        tr.remove();
//                        $table.find("tr:first").trigger('click');
//                        notify(data.type, data.message);
//
//
//
//
//                    }
//                });
            }
        });

    });*/

//    $body.off("click", ".delete-schedule");
//    $body.on("click", ".delete-schedule", function (e) {
//
//        e.preventDefault();
//
//        var href = $(".delete-schedule").attr("href");
//        var value = href.substring(href.lastIndexOf('/') + 1);
//        var delete_schedule_click=$(this);
//        var $event_id=$('#event_detail_id').text();
//        console.log($("#event-detail .event_detail").find('.event_startdate'));
//
//        bootbox.confirm({
//            message: schedule_delete,
//            buttons: {
//                'cancel': {
//                    label: cancel_button,
//                    className: 'btn-default pull-right'
//                },
//                'confirm': {
//                    label: ok_button,
//                    className: 'btn-primary pull-right'
//                }
//            },
//            callback: function(response) {
//                if (response == true) {
//                    $.ajax({
//                        url: href,
//                        type: 'Delete',
//                        data:{'event_id':$('#event_detail_id').text()},
//                        success: function (data) {
//                            notify(data.type, data.message);
//                            $("#schedule_table tr").each( function() {
//
//                                if($(this).attr('data-recordid')==value)
//                                {
//                                    $(this).remove();
//                                }
//
//                            })
//                            if(data.event_start_date!="") {
//                                $(".chats li.active-hr .event_start_date").html(data.event_start_date + " - ")
//                                $(".chats li.active-hr .event_end_date").html(data.event_end_date)
//                                $("#event-detail .event_detail").find('.event_startdate').html(data.event_start_date + " - ")
//                                $("#event-detail .event_detail").find('.event_enddate').html(data.event_end_date)
//                            }
//                            else
//                            {
//                                $(".chats li.active-hr .event_date_range").remove();
//                                $("#event-detail .event_detail").find('.event_range').remove();
//                            }
//                        },
//                    });
//                }
//            }
//        });
//
//    });

    bindTableEditEvent();

    function bindTableEditEvent()
    {
        $('#schedule_table tr td').on('click', function (e) {
            e.preventDefault();

            console.log(e);
            /* Get the row as a parent of the link that was clicked on */
            var nRow = $(this).parents('tr')[0];

            var td = $(this).index();

            if(e.target.tagName == "INPUT" || e.target.tagName == "TEXTAREA" || e.target.tagName == "SELECT" ){
                return false;
            }

            if($(this).attr("data-index") == 'action') {
                var innner_element = $(this.innerHTML);
            }else
                var innner_element = $(this); // no use as default for other cells

            if ( nEditing !== null && nEditing != nRow ) {
                console.log("in revert last row and set new row");
                /* A different row is being edited - the edit should be cancelled and this row edited */
                restoreRow( oTable, nEditing );
                editRow( oTable, nRow, td );
                nEditing = nRow;
                originalRowData = JSON.stringify(oTable.row( nRow ).data());
                nTd = $(this);

            }
            else if ( nEditing == nRow && $(e.target).attr('id') == "save" ) {
                console.log("save pre edited row");
                /* This row is being edited and should be saved */
                localsave( oTable, nEditing, nTd );
                saveRow( oTable, nEditing, td );
                nEditing = null;
            }
            else if ( nEditing == null && $(e.target).attr('id') == "remove" ) {
                console.log("cancel pre edited row");
                /* This row is being deleted */
                var $url = $(".delete-schedule").attr("data-href") + '/' + $(nRow).attr('data-recordid') ;
                $(".delete-schedule").attr('href',$url);
                $(".delete-schedule").trigger("click");
               // $(nRow).remove();





            }else if ( nEditing == nRow && nTd != null && $(e.target).attr('id') == "cancel" ) {
                restoreRow(oTable,nEditing);

            }else if ( nEditing == nRow && nTd != null ) {
                /* A different column of the same row */
                console.log("cancel td null edited row");
                localsave( oTable, nEditing, nTd );
                nTd = $(this);
                editRow( oTable, nRow, td );
            }
            else {
                console.log("in else");
                /* No row currently being edited */
                editRow( oTable, nRow, td );
                nEditing = nRow;
                originalRowData = JSON.stringify(oTable.row( nRow ).data());
                nTd = $(this);
            }
        });


    }

    function editRow ( oTable, nRow, td )
    {
        var aData = oTable.row( nRow ).data();
        var jqTds = $('>td', nRow);
        var row = oTable.row(nRow);
        var attr = $(jqTds[td]).attr('data-index');
        var selected_td = $(jqTds[td]);

        switch (td)
        {
            case 0 :
                var control = '<input name="begin" class="form-control input-medium required schedule-begin" type="text" value="'+ aData[0] + '">';
                var title = "Begin"
                $(jqTds[0]).html(format(title ,control) );
//                row.child( format(title ,control) ).show();
                if(typeof attr == "undefined") {
                    selected_td.attr("data-index", 'begin');
                }
                break;
            case 1 :
                var control = '<input name="end" class="form-control input-medium schedule-end required" type="text" value="'+aData[1]+'">';
                var title = "End"
//                row.child( format(title ,control) ).show();
                $(jqTds[1]).html(format(title ,control) );
                if(typeof attr == "undefined")
                    selected_td.attr("data-index",'end');
                break;
            case 2 :
                var control = '<textarea row="3" id="rdescription" class="form-control" cols="10" value="">' +aData[2] + '</textarea>';
                var title = "Description"
//                row.child( format(title ,control) ).show();
                $(jqTds[2]).html(format(title ,control) );
                if(typeof attr == "undefined")
                    selected_td.attr("data-index",'description');
                break;
            case 3 :
                var val = ($(jqTds[3]).attr('data-trainerid')) ? $(jqTds[3]).attr('data-trainerid') : '';
                    console.log(val);
                var control = '<input type="hidden" name="trainer" class="input-large" id="scheduleTrainers" value="'+ val  +'">';
                var title = "Trainer"
//                row.child( format(title ,control) ).show();
                $(jqTds[3]).html(format(title ,control) );
                if(typeof attr == "undefined")
                    selected_td.attr("data-index",'trainer');
                break;
            case 4 :
                var cloneSelect = $(".cloneOrganizationList").clone();
                cloneSelect.removeClass("hide").removeClass("cloneOrganizationList");
                cloneSelect.attr("name","organization_id");
                cloneSelect.find("option[value="+ $(jqTds[4]).attr('data-organizationid') +"]").attr('selected', true)
                var control = cloneSelect.prop('outerHTML');
                var title = "Location"
//                row.child( format(title ,control) ).show();
                $(jqTds[4]).html(format(title ,control) );
                if(typeof attr == "undefined")
                    selected_td.attr("data-index",'organization_id');
                break;
            case 5 :
                var control = '<input name="begin" class="form-control input-medium schedule-end required" type="text" value="'+ aData[0] + '">';
                var title = "Begin"
//                row.child( format(title ,control) ).show();
                $(jqTds[5]).html(format(title ,control) );
                if(typeof attr == "undefined") {
                    selected_td.attr("data-index", 'begin');
                }
                break;
            default :
                var control = '<input name="begin" class="form-control input-medium schedule-end required" type="text" value="">';
                var title = "Begin"
//                row.child( format(title ,control) ).show();
                $(jqTds[0]).html(format(title ,control) );
                if(typeof attr == "undefined")
                    selected_td.attr("data-index", 'begin');
                break;

        }

        if(typeof $(jqTds[5]).attr('data-index') == "undefined")
            $(jqTds[5]).attr("data-index",'action');
        jqTds[5].innerHTML = '<a style="display: inline-block" class="save" href=""><i id="save" class="fa fa-save"></i></a>&nbsp;&nbsp;<a  style="display: inline-block" class="remove" href=""><i id="remove" class="fa fa-trash-o"></i></a><a href="javascript:;" class="resetEdit"><i id="cancel" class="fa fa-close"><i/></a>'

        initDatePicker();
        initSelectDropDown();

    }

    function localsave(oTable, nRow, td )
    {
        var control_val = ""
        switch (td.index())
        {
            case 0 :
                control_val = $("[name=begin]").val();
                editedRowData["begin"] = control_val;
                break;
            case 1 :
                control_val = $("[name=end]").val();
                editedRowData["end"] = control_val;
                break;
            case 2 :
                control_val = $("#rdescription").val();
                editedRowData["description"] = control_val;
                break;
            case 3 :
                // fetch the selected trainer text
                var a = [];
                $("#s2id_scheduleTrainers ul li.select2-search-choice").each(function(){
                    a.push($(this).find("div").html());
                });
                control_val = a.join(', ');
                td.attr("data-trainerid",$("[name=trainer]").val())
                editedRowData["trainer"] = $("[name=trainer]").val();
                break;
            case 4 :
                control_val = $("[name=organization_id] option:selected").text();
                td.attr("data-organizationid",$("[name=organization_id]").val())
                editedRowData["organization_id"] = $("[name=organization_id]").val();
                break;

        }

        oTable.cell(td).data(control_val);

    }

    function saveRow ( oTable, nRow ) {
        var aData = oTable.row(nRow).data();
        var tr = $(nRow)
        var jqTds = $('>td', nRow);
        var add_schedule_form = $(".add_schedule_form");
        $(jqTds).each(function () {
            if ($(this).attr("data-index") == 'trainer') {
                add_schedule_form.find("[name=" + $(this).attr("data-index")+"]").val($(this).attr('data-trainerid'));
                editedRowData[$(this).attr("data-index")] = $(this).attr('data-trainerid');
            } else if ($(this).attr("data-index") == 'organization_id') {
                add_schedule_form.find("[name=" + $(this).attr("data-index")+"]").val($(this).attr('data-organizationid'));
                editedRowData[$(this).attr("data-index")] = $(this).attr('data-organizationid');
            } else {
                add_schedule_form.find("[name=" + $(this).attr("data-index")+"]").val($(this).html());
                editedRowData[$(this).attr("data-index")] = $(this).html();
            }
        });


        // make ajax call and save data

        var url = base_url + "schedule";
        var schedule_id = tr.attr('data-recordid');
        var method = "POST";
        if(schedule_id != null && schedule_id != ""){
            method = "PUT";
            url += "/" + schedule_id;
        }
        // if event is in edit mode
        var additionalDataByPage = "";
        if($("#event_detail_id").text() != "")
        {
            additionalDataByPage = "&EventID=" + $("#event_detail_id").text();
        }
        $.ajax({
            url: url,
            method: method,
            data: $(".add_schedule_form").find("*").serialize() + additionalDataByPage,
            beforeSend: function () {
                $(".cancel-popup").trigger("click");
                blockUI(".page-container");
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
            success: function (data) {
                unBlockUI(".page-container");
                notify(data.type, data.message);
//                var start_date = new Date(data.event.event_startdate);
//                var end_date = new Date(data.event.event_enddate);
                if($(".chats li.active-hr .event_date_range").length<=0)
                {
//                    $(".chats li.active-hr").find('.event_name').after("<h4 class='event_date_range'><span class='event_start_date'>"+start_date.format(app_date_format) + "-" +"</span><span class='event_end_date'>"+end_date.format(app_date_format)+"</span></h4>")
//                    $("#event-detail .event_detail").find('.event_name').after("<h4 class='event_date_range'><span class='event_start_date'>"+start_date.format(app_date_format) + "-" +"</span><span class='event_end_date'>"+end_date.format(app_date_format)+"</span></h4>")
                }else
                {
//                    $(".chats li.active-hr .event_date_range").html(start_date.format(app_date_format) + "-" + end_date.format(app_date_format))
//                    $("#event-detail .event_detail").find('.event_startdate').html(start_date.format(app_date_format) + "-");
//                    $("#event-detail .event_detail").find('.event_enddate').html(end_date.format(app_date_format));
                }


                if(data.conflictScheduleId)
                    highlightConflitedSchedule(data.conflictScheduleId, data.type);

                if(data.type == 'danger')
                {
                    initcalendar(data.conflictEventId);
                    if(schedule_id != null && schedule_id != ""){
                        restoreRow(oTable,nRow)
                    }else{
                        oTable.row(nRow).remove().draw();
                    }
                }else {
                    schedule_array.push(data.schedule_id);
                    var row = oTable.row(nRow)
                    row.child.hide();
                }
            }
        });
    }


    function restoreRow ( oTable, nRow )
    {
        if(originalRowData != null) {
            var aData = oTable.row(nRow).data($.parseJSON(originalRowData));
            var row = oTable.row(nRow)
            row.child.hide();
            oTable.draw();
            nEditing = null;
            nTd = null;

        }else{
            oTable.row(nRow).remove().draw();;
        }
    }

    function format (title,control) {
        // `d` is the original data object for the row
//        return '<table id="editTable" cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">'+
//                '<tr>'+
//                '<td>'+ title + '</td>'+
//                '<td>'+ control + '<td>'
//                '</tr>'+
//                '</table>';

//        return '<div id="editTable" class="clearfix" style="position: relative" cellpadding="5" cellspacing="0" border="0" >'+
////                '<a class="resetEdit" style="float: right;margin-right: 8px;"> X </a>' +
//                '<div style="float: left" >'+
////                '<span>'+ title + '</span>'+
//                '<span>'+ control + '</span>'
//                '</div>'+
//                '</div>';
        return '<div id="editTable" class="clearfix" style="position: relative" cellpadding="5" cellspacing="0" border="0" >'+
//                '<a class="resetEdit" style="float: right;margin-right: 8px;"> X </a>' +
                '<div class="form-group form-md-line-input form-md-floating-label" >'+
//                '<span>'+ title + '</span>'+
                 control +
                '</div>'+
                '</div>';
    }

    function initcalendar(conflict_id){
        $('#fullcalendar').fullCalendar('removeEventSource', $source)
        $('#fullcalendar').fullCalendar('refetchEvents')

        $source = base_url + "event/calendar/fetchdata?event_id="+
                "&location_id="+
                "&event_category_id="+
                "&trainer="+
                "&conflict_event_id=" + conflict_id;
        $('#fullcalendar').fullCalendar('addEventSource', $source);
    }

    function highlightConflitedSchedule(conflict_id, type){
        var conflicted_tr = $("#schedule_table").find("[data-recordid=" + conflict_id + "]");
        if(type == "warning") {
            conflicted_tr.find("[data-index='action']").append("<i style='font-size: 23px;color: red;' class='fa fa-warning'></i>");
            conflicted_tr.find("[data-index='organization_id']").css("background","#DFBA49");
        }
        else if(type == "danger") {
            conflicted_tr.find("[data-index='action']").append("<i style='font-size: 23px;color: red;' class='fa fa-info-circle'></i>")
            conflicted_tr.find("[data-index='trainer']").css("background","#FF0000");
        }

    }

</script>