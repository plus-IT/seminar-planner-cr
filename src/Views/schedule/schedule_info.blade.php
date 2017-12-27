<div class="control-group apply_table_operation">
    <div class="portlet light">
        <div class="portlet-title">
            <div class="caption caption-md">
                <span class="bold capital schedule_count"
                      data-count="{!! (!empty($day) ? $day : '0') !!}">
                    {!! Html::customTrans("events.schedule") !!} {!! (!empty($day) ? '('.$day.')' : '(0)') !!}
                </span>
            </div>
            <div class="pull-right" style="margin-right: 15px;">
                <a href="#" id="add_schedule_btn"
                   class="btn tooltips btn-circle btn-icon-only btn-default add_schedule tab_plus_btn" data-original-title="{!! Html::customTrans("events.TooltipsAddSchedule") !!}"><i class="fa fa-plus"></i></a>
            </div>
        </div>
        <div id="tab_schedule_info" class="clearfix myaccordian">
            @if(isset($schedule_data))
                <div class="panel-group accordion scrollable" id="accordion2">
                    @foreach ($schedule_data[0]->eventSchedule as $schedule)
                        <div class="panel panel-default schedule_panel_{!! isset($schedule->schedule->id ) ? $schedule->schedule->id : "" !!}" scheduleid="{!! isset($schedule->schedule->id ) ? $schedule->schedule->id : "" !!}">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle accordion-toggle-styled accordionHeading collapsed"
                                       data-toggle="collapse" data-parent="#accordion2"
                                       href="#schedule_row_{!! isset($schedule->schedule) && !empty($schedule->schedule) ? $schedule->schedule->id : ''!!}">
                                        <span data-index='event_days'> {!! isset($schedule->schedule) && !empty($schedule->schedule) ? format_date($schedule->schedule->schedule_date) : '' !!} </span>
                                        <span data-index='location'> {!! isset($schedule->schedule) && isset($schedule->schedule->scheduleLocation) && !empty($schedule->schedule) && !empty($schedule->schedule->scheduleLocation) ?  $schedule->schedule->scheduleLocation->LocationName : '' !!} </span>
                                        <span data-index='slot_count'> {!! (!empty($schedule->schedule) ? Html::customTrans("events.slotsCount") . '(' . count($schedule->schedule->eventScheduleSlot) . ')' : '(0)') !!} </span>
                                        <span data-index='start_time'> {!! (isset($schedule->schedule) && isset($schedule->schedule->eventScheduleSlot[0])  ? format_time($schedule->schedule->eventScheduleSlot[0]->start_time) . " " . Html::customTrans("events.onwards") : '') !!} </span>
                                    </a>
                                </h4>
                            </div>
                            <div class="floating-group-button floating-left float-zoomin"
                                 data-button-state="close">
                                        <span class="btn btn-circle btn-icon-only btn-default pull-right"><i
                                                    class="fa fa-ellipsis-h"></i></span>
                                <ul class="h-floating-effect">
                                    <li>
                                        <a href="javascript:;" data-container="body" data-placement="top" data-original-title="{!! Html::customTrans("events.TooltipsEditSchedule") !!}"
                                           id="schedule_edit" data-id="{!! isset($schedule->schedule) ? $schedule->schedule->id : ''!!}"
                                           class="btn btn-circle btn-icon-only  btn-default schedule_edit pull-right tooltips">
                                            <i class="fa fa fa-pencil-square-o"></i></a>
                                    </li>
                                    <li>
                                        <a href="javascript:;" data-container="body" data-placement="top" data-original-title="{!! Html::customTrans("event.TooltipsCopySchedule") !!}"
                                           id="schedule_duplicate" data-id="{!! isset($schedule->schedule) ? $schedule->schedule->id : '' !!}"
                                           class="schedule_duplicate btn btn-circle btn-icon-only  btn-default tooltips pull-right tooltips">
                                            <i class="fa fa-copy (alias)"></i></a>
                                    </li>
                                    <li>
                                        <a href="javascript:;" data-container="body" data-placement="top" data-original-title="{!! Html::customTrans("event.TooltipsDeleteSchedule") !!}"
                                           id="schedule_delete" data-id="{!! isset($schedule->schedule) ? $schedule->schedule->id : '' !!}"
                                           class="schedule_delete btn btn-circle btn-icon-only btn-default pull-right tooltips">
                                            <i class="fa fa-trash-o"></i></a>
                                    </li>
                                </ul>
                            </div>
                            <div class="panel-collapse clearfix collapse"
                                 id="schedule_row_{!! isset($schedule->schedule) ? $schedule->schedule->id : '' !!}">
                                @if(isset($schedule->schedule))
                                    @foreach($schedule->schedule->eventScheduleSlot as $schedule_slot)
                                        <div class="slotsListingWrapper clearfix">
                                            <span>{!! format_time($schedule_slot->start_time) . '&nbsp-&nbsp' . format_time($schedule_slot->end_time) !!}</span>
                                            <?php
                                            $trainerIDs = explode(',', $schedule_slot->trainer);
                                            $trainerDataArray = $schedule_slot->personDataForSchedule($trainerIDs);
                                            ?>
                                            <span class="span_capitalize">{!! $trainerDataArray['trainerName']  !!}</span>
                                            <span class="slotDescription">{!! $schedule_slot->description !!} </span>
                                        </div>
                                    @endforeach
                                @endif

                            </div>

                        </div>

                    @endforeach
                </div>
            @endif
            <div class="table-responsive">


            </div>

        </div>

    </div>

    <div class="modal fade" id="scheduleModal" role="dialog">
        <div class="modal-dialog model_dialog_center schedule_modal_width myschedule_modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" close_schedule_modal>&times;</button>
                    <h4 class="modal-title">{!! Html::customTrans("events.add_schedule") !!}</h4>
                </div>


                <div class="modal-body">
                    {!!  Form::open(['url' => LaravelLocalization::getCurrentLocale()."/". 'schedule','method' =>"post",'class'=>' form-row-seperated validate_form add_schedule_form',"autocomplete"=>'off']) !!}

                    <input class="form-control form-control-inline schedule-date required"
                           size="16" type="hidden" name="ScheduleID"
                           value="{!! !empty($schedule_details) ? $schedule_details[0]->id: "0" !!}"/>

                    <div class="clearfix schedule-date-stuff">
                        <div class="row scheduletop">
                            <div class="col-md-12">
                                <div class="col-md-2">
                                    <div class="form-group form-md-line-input required">
                                        <input class="form-control form-control-inline  required"
                                               size="16" type="text" name="schedule_date" readonly
                                               value="{!! (isset($schedule_details[0]) && !empty($schedule_details[0]->schedule_date)) ? ($duplicate == true ? format_date($nextDate) : format_date($schedule_details[0]->schedule_date)) : (isset($nextDate) ? format_date($nextDate) : '')  !!}"/>
                                        <input type="hidden" name="event_days" value="{!! (isset($schedule_details[0]) && !empty($schedule_details[0]->event_days)) ? ($duplicate == true ? $nextDay : $schedule_details[0]->event_days) : (isset($nextDay) ? $nextDay : '')  !!}">
                                        <label>{!! Html::customTrans("events.event_days") !!}</label>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group form-md-line-input form-md-floating-label has-info required">
                                        <select name="LocationID" id="LocationID" class="form-control edited ">
                                            <option value=""></option>
                                            @if(!empty($all_location))
                                                @foreach($all_location as $val)
                                                    <option value="{!! $val->LocationID !!}" class="dropdown-capitalize"
                                                    @if(isset($schedule_details))
                                                        {!! (!empty($schedule_details[0]->scheduleLocation->LocationID) && $schedule_details[0]->scheduleLocation->LocationID == $val->LocationID) ? "selected":'' !!}
                                                            @endif>
                                                        {!! $val->LocationName !!}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <label for="LocationID">{!! Html::customTrans("events.location") !!}</label>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group form-md-line-input form-md-floating-label has-info required">
                                        <?php
                                        $roomName = "";
                                        ?>
                                        <select name="scheduleRoomId" id="roomId" class="form-control edited roomId" onchange="this.nextElementSibling.value=''">
                                            <option value=""></option>
                                            @if(!empty($all_rooms))
                                                @foreach($all_rooms as $val)
                                                    <?php
                                                    $roomName = (!empty($schedule_details[0]->roomId) && $schedule_details[0]->roomId == $val->room->RoomID) ? $val->room->RoomName : "";
                                                    ?>
                                                    <option value="{!! $val->room->RoomID !!}"
                                                            class="dropdown-capitalize"
                                                    @if(isset($schedule_details))
                                                        {!! (!empty($schedule_details[0]->roomId) && $schedule_details[0]->roomId == $val->room->RoomID) ? "selected":'' !!}
                                                            @endif>
                                                        {!! $val->room->RoomName !!}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <input class="form-control form-control-inline roomDropDownText edited" type="text" name="customRoomName"
                                               value="{!! (isset($schedule_details[0]) && $schedule_details[0]->roomId == 0 ) ? $schedule_details[0]->custom_room_name   : "" !!}">
                                        <label for="roomId">{!! Html::customTrans("events.room") !!}</label>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group form-md-line-input">
                                        <?php

                                        $trainer_ids = isset($schedule_details[0]->schedule_default_trainer) && !empty($schedule_details[0]->schedule_default_trainer) ? explode(',', $schedule_details[0]->schedule_default_trainer) : "";
                                        $trainer_data_arr = !empty($trainer_ids) ? $schedule_details[0]->personData($trainer_ids) : [];
                                        $trainer_name = "";
                                        foreach ($trainer_data_arr as $k => $value) {
                                            $trainer_data[$k]['id'] = $value->PersonID;
                                            $trainer_data[$k]['text'] = $value->FirstName . ' ' . $value->LastName;
                                            $comma = isset($trainer_data_arr[$k + 1]) ? ", " : "";
                                            $trainer_name .= $value->FirstName . ' ' . $value->LastName . $comma;
                                        }
                                        ?>

                                        <input type="hidden" id="scheduleDefaultTrainer" name="scheduleDefaultTrainer"
                                               value="{!! (isset($schedule_details[0]->schedule_default_trainer) && !empty($schedule_details[0]->schedule_default_trainer)) ? $schedule_details[0]->schedule_default_trainer : "" !!}"
                                               class="form-control scheduleDefaultTrainers ">
                                        <input type="hidden" id="scheduleDefaultTrainerData"
                                               value="{{ (!empty($trainer_data)) ? json_encode($trainer_data) : '' }}">
                                        <label class="control-label"
                                               for="scheduleTrainers"> {!! Html::customTrans("events.trainer") !!}</label>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="md-radio-inline">
                                        <div class="md-radio">
                                            <input type="radio" name="scheduleType" id="default" {!! (isset($schedule_details[0]->schedule_type) ) ? ($schedule_details[0]->schedule_type == 0 ? "checked" : "" ) : "checked" !!} value="0" class="required form-control" checked="">
                                            <label for="default">
                                                <span></span>
                                                <span class="check"></span>
                                                <span class="box"></span>
                                                Default</label>
                                        </div>
                                        <div class="md-radio">
                                            <input type="radio" name="scheduleType" id="individual" {!! (isset($schedule_details[0]->schedule_type) ) ? ($schedule_details[0]->schedule_type == 1 ? "checked" : "" ) : "" !!} value="1" class="form-control">
                                            <label for="individual">
                                                <span></span>
                                                <span class="check"></span>
                                                <span class="box"></span>
                                                Individual</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row col-md-12 scheduletop">


                            <?php
                            if (!empty($schedule_details[0]->weekdays))
                                $check_weekdays = explode(",", $schedule_details[0]->weekdays);
                            else
                                $check_weekdays = explode(",", $allowed_days);
                            ?>
                            <div class="col-md-12 mydaylist {!! (isset($schedule_details[0]->schedule_type) ) ? ($schedule_details[0]->schedule_type == 1 ? "" : "hidden" ) : "hidden" !!}">
                                <div class="col-md-3">
                                    <div class="form-group form-md-line-input required">
                                        <input class="form-control form-control-inline"
                                               type="text" name="duration_between_previous_day"
                                               value="{!! !empty($schedule_details[0]->duration_between_previous_day)? $schedule_details[0]->duration_between_previous_day : 0 !!}"/>
                                        <label>{!! Html::customTrans("event.duration_between_previous_day") !!}</label>
                                    </div>
                                </div>
                                <div class="col-md-9">

                                    <div class="md-checkbox-list ">
                                        <div class="md-checkbox" style="padding:0 12px 0 0;">
                                            <input type="checkbox" id="sunday" class="md-check" name="weekdays[]"
                                                   value="0" {!! (in_array("0",$check_weekdays)) ? 'checked' : '' !!}>
                                            <label for="sunday">
                                                <span></span>
                                                <span class="check"></span>
                                                <span class="box"></span>
                                                {!! Html::customTrans("events.sunday") !!}
                                            </label>
                                        </div>

                                        <div class="md-checkbox" style="padding:0 12px 0 0;">
                                            <input type="checkbox" id="monday" class="md-check" name="weekdays[]"
                                                   value="1" {!! (in_array("1",$check_weekdays)) ? 'checked' : '' !!}>
                                            <label for="monday">
                                                <span></span>
                                                <span class="check"></span>
                                                <span class="box"></span>
                                                {!! Html::customTrans("events.monday") !!}
                                            </label>
                                        </div>

                                        <div class="md-checkbox" style="padding:0 12px 0 0;">
                                            <input type="checkbox" id="tuesday" class="md-check" name="weekdays[]"
                                                   value="2" {!! (in_array("2",$check_weekdays)) ? 'checked' : '' !!}>
                                            <label for="tuesday">
                                                <span></span>
                                                <span class="check"></span>
                                                <span class="box"></span>
                                                {!! Html::customTrans("events.tuesday") !!}
                                            </label>
                                        </div>
                                        <div class="md-checkbox" style="padding:0 12px 0 0;">
                                            <input type="checkbox" id="wednesday" class="md-check" name="weekdays[]"
                                                   value="3" {!! (in_array("3",$check_weekdays)) ? 'checked' : '' !!}>
                                            <label for="wednesday">
                                                <span></span>
                                                <span class="check"></span>
                                                <span class="box"></span>
                                                {!! Html::customTrans("events.wednesday") !!}
                                            </label>
                                        </div>

                                        <div class="md-checkbox" style="padding:0 12px 0 0;">
                                            <input type="checkbox" id="thursday" class="md-check" name="weekdays[]"
                                                   value="4" {!! (in_array("4",$check_weekdays)) ? 'checked' : '' !!}>
                                            <label for="thursday">
                                                <span></span>
                                                <span class="check"></span>
                                                <span class="box"></span>
                                                {!! Html::customTrans("events.thursday") !!}
                                            </label>
                                        </div>
                                        <div class="md-checkbox" style="padding:0 12px 0 0;">
                                            <input type="checkbox" id="friday" class="md-check" name="weekdays[]"
                                                   value="5" {!! (in_array("5",$check_weekdays)) ? 'checked' : '' !!}>
                                            <label for="friday">
                                                <span></span>
                                                <span class="check"></span>
                                                <span class="box"></span>
                                                {!! Html::customTrans("events.friday") !!}
                                            </label>
                                        </div>
                                        <div class="md-checkbox" style="padding:0 12px 0 0;">
                                            <input type="checkbox" id="saturday" class="md-check" name="weekdays[]"
                                                   value="6" {!! (in_array("6",$check_weekdays)) ? 'checked' : '' !!}>
                                            <label for="saturday">
                                                <span></span>
                                                <span class="check"></span>
                                                <span class="box"></span>
                                                {!! Html::customTrans("events.saturday") !!}
                                            </label>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="schedule_slot scheduleSlotList col-md-12 " id="scroller_schedul">
                        <div class="row">
                            <div class="col-md-12 "><h3>{!! Html::customTrans("event.slotHeading") !!}</h3></div>
                        </div>
                        <div class="panel-group schedulListPanelGroup accordion col-md-11" id="accordion2">
                            @if(!empty($schedule_details))
                                @foreach ($schedule_details[0]->eventScheduleSlot as $key=>$schedule_slot)
                                    <?php
                                    $trainer_ids = explode(',', $schedule_slot->trainer);
                                    $trainer_data_arr = $schedule_slot->personData($trainer_ids);
                                    $trainer_name = "";
                                    foreach ($trainer_data_arr as $k => $value) {
                                        $trainer_data[$k]['id'] = $value->PersonID;
                                        $trainer_data[$k]['text'] = $value->FirstName . ' ' . $value->LastName;
                                        $comma = isset($trainer_data_arr[$k + 1]) ? ", " : "";
                                        $trainer_name .= $value->FirstName . ' ' . $value->LastName . $comma;
                                    }
                                    ?>

                                    <div class="panel panel-default schedule_slot_panel_{!! $schedule_slot->schedule_slotID !!}"
                                         id="schedule_slot_panel_{!! $schedule_slot->schedule_slotID !!}">
                                        <div class="panel-heading">
                                            <h4 class="panel-title">
                                                <input type="hidden" id="trainer_data_{{ $key }}"
                                                       value="{{ (!empty($trainer_data)) ? json_encode($trainer_data) : '' }}">
                                                <a class="accordion-toggle accordion-toggle-styled accordionHeading collapsed"
                                                   data-toggle="collapse" data-parent="#accordion2"
                                                   href="#schedule_slot_{!! $schedule_slot->schedule_slotID !!}">

                                                    <span data-index='slot_time'> {!! format_time($schedule_slot->start_time) . '&nbsp-&nbsp' . format_time($schedule_slot->end_time)  !!} </span>
                                                    <span data-index='slot_title'> {!! isset($schedule_slot) ? $schedule_slot->title : "" !!} </span>
                                                    <span data-index='slot_room'> {!! isset($schedule_slot) && $schedule_slot->roomId == 0 ? $schedule_slot->custom_room_name : (isset($schedule_slot->scheduleSlotRoom) && !empty($schedule_slot->scheduleSlotRoom) ? $schedule_slot->scheduleSlotRoom->RoomName : "" )!!} </span>
                                                    <span data-index='slot_trainer'> {!! $trainer_name !!} </span>
                                                </a>
                                            </h4>
                                        </div>
                                        <div class="floating-group-button floating-left float-zoomin"
                                             data-button-state="close">
                                                            <span class="btn btn-circle btn-icon-only btn-default pull-right">
                                                                <i class="fa fa-ellipsis-h"></i>
                                                            </span>
                                            <ul class="h-floating-effect">
                                                <li>
                                                    <a href="javascript:;" data-container="body" data-placement="top" data-original-title="{!! Html::customTrans('events.deleteSlotBtnToolTip') !!}"
                                                       class="btn btn-circle btn-icon-only btn-default slot_delete tooltips"
                                                       data-id="@if(isset($schedule_slot)){!! $schedule_slot->schedule_slotID!!} @endif">
                                                        <i class="fa fa-trash"></i>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="javascript:;" data-container="body" data-placement="top" data-original-title="{!! Html::customTrans('events.copySlotBtnToolTip') !!}"
                                                       class="btn btn-circle btn-icon-only btn-default slot_duplicate tooltips">
                                                        <i class="fa fa-copy"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="panel-collapse clearfix collapse"
                                             id="schedule_slot_{!! $schedule_slot->schedule_slotID!!}">
                                            <div class="col-md-12 slot">
                                                <div class="col-md-1" style="display:none">
                                                    <div class="md-checkbox  time_slot_chk">
                                                        <input type="checkbox" name="time_slot_chk[]"
                                                               id="time_slot_{!! $schedule_slot->schedule_slotID !!}"
                                                               value="{!! $schedule_slot->schedule_slotID !!}"
                                                               class="schedule_time_slot">
                                                        <label for="time_slot_{!! $schedule_slot->schedule_slotID !!}">
                                                            <span></span>
                                                            <span class="check"></span>
                                                            <span class="box"></span>

                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group form-md-line-input required">
                                                        <input class="form-control form-control-inline"
                                                               type="text" name="title[]"
                                                               value="@if(isset($schedule_slot)){!!$schedule_slot->title !!}@endif" id="slotTitle"/>
                                                        <label>{!! Html::customTrans("event.slotTitle") !!}</label>
                                                    </div>

                                                    <div class="textarea-note-schedule">
                                                        <div class="form-group form-md-line-input required pull-left">
                                                        <textarea class="form-control required" name="description[]"
                                                                  id="description" rows="4"
                                                                  cols="86"
                                                                  placeholder="{!! Html::customTrans("events.description") !!}"
                                                                  maxlength="300">@if(isset($schedule_slot)){!!$schedule_slot->description !!}@endif</textarea>
                                                            <label for="description"></label>
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group form-md-line-input required">
                                                        <input class="form-control form-control-inline timepicker-default required start_time"
                                                               size="16" type="text" name="start_time[]"
                                                               value="{!! format_time($schedule_slot->start_time) !!}"/>
                                                        <label>{!! Html::customTrans("events.startTime") !!}</label>
                                                        <input class="form-control form-control-inline"
                                                               size="16" type="hidden" name="slot_id[]"
                                                               value="{!! (!empty($schedule_slot->schedule_slotID))?($schedule_slot->schedule_slotID):'0' !!}"/>
                                                    </div>
                                                </div>

                                                <div class="col-md-2">
                                                    <div class="form-group form-md-line-input required">
                                                        <input class="form-control form-control-inline timepicker-default required end_time"
                                                               size="16" type="text" name="end_time[]"
                                                               value="{!! format_time($schedule_slot->end_time) !!}"/>
                                                        <label>{!! Html::customTrans("events.endTime") !!}</label>
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="form-group form-md-line-input">
                                                        <input type="hidden" name="trainer[]"
                                                               value="{!! $schedule_slot->trainer !!}"
                                                               class="form-control trainer_div scheduleTrainers_{{ $key }} required">
                                                        <label class="control-label"
                                                               for="scheduleTrainers"> {!! Html::customTrans("events.trainer") !!}</label>
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="form-group form-md-line-input form-md-floating-label has-info required">
                                                        <?php
                                                        $roomName = "";
                                                        ?>
                                                        <select name="roomId[]" id="roomId"
                                                                class="form-control edited  ">
                                                            <option value=""></option>
                                                            @if(!empty($all_rooms))
                                                                @foreach($all_rooms as $val)
                                                                    <?php
                                                                    $roomName = (!empty($schedule_slot->roomId) && $schedule_slot->roomId == $val->room->RoomID) ? $val->room->RoomName : "";
                                                                    ?>
                                                                    <option value="{!! $val->room->RoomID !!}"
                                                                            class="dropdown-capitalize"
                                                                    @if(isset($schedule_slot))
                                                                        {!! (!empty($schedule_slot->roomId) && $schedule_slot->roomId == $val->room->RoomID) ? "selected":'' !!}
                                                                            @endif>
                                                                        {!! $val->room->RoomName !!}
                                                                    </option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                        <input class="form-control form-control-inline roomDropDownText edited" type="text" id="slotCustomRoomName" name="slotRoomName[]"
                                                               value="{!! (isset($schedule_slot)) ? $schedule_slot->roomId == 0  ? $schedule_slot->custom_room_name : $roomName  : "" !!}">
                                                        <label for="roomId">{!! Html::customTrans("events.room") !!}</label>
                                                    </div>
                                                </div>
                                                <a href="javascript:;"
                                                   class="btn btn-circle btn-icon-only btn-default saveSlotDetails pull-left"
                                                   data-parentDivId=schedule_slot_panel_{!! $schedule_slot->schedule_slotID !!}>
                                                    <i class="fa fa-arrow-right"></i>
                                                </a>



                                            </div>
                                        </div>
                                    </div>

                                @endforeach
                            @endif
                        </div>

                    </div>
                    <div class="schedule_slot col-md-12 schedule_add_slot scoller clearfix addnewslotbotbox"
                         style="bottom: 0px;background: #eee">
                        <div class="panel-group accordion col-md-11" id="accordion2">
                            <div class="panel panel-default add_schedule_slot_panel"
                                 id="">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a class="accordion-toggle accordion-toggle-styled accordionHeading collapsed"
                                           data-toggle="collapse" data-parent="#accordion2"
                                           href="#addScheduleSlotPanel"><span> {!! Html::customTrans("event.addNewSLotHeading") !!}</span>
                                        </a>
                                    </h4>
                                </div>
                                <div class="panel-collapse clearfix collapse"
                                     id="addScheduleSlotPanel">
                                    <div class="col-md-12 slotWrapper col-md-offset-0">
                                        <div class="col-md-12">
                                            <div class="form-group form-md-line-input required">
                                                <input class="form-control form-control-inline"
                                                       type="text" name=""
                                                       value="" id="slotTitle"/>
                                                <label>{!! Html::customTrans("event.slotTitle") !!}</label>
                                            </div>

                                            <div class="textarea-note-schedule">
                                                <div class="form-group form-md-line-input required pull-left">
                                                                <textarea class="form-control " name="" id="description"
                                                                          rows="4"
                                                                          cols="86"
                                                                          placeholder="{!! Html::customTrans("events.description") !!}"
                                                                          maxlength="300"></textarea>
                                                    <label for="description"></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group form-md-line-input required">
                                                <input class="form-control form-control-inline timepicker-default  start_time"
                                                       size="16" type="text" name=""
                                                       value="{!! !empty($schedule_details) ? '' : !empty(session("setting")->schedule_start_time) ? session("setting")->schedule_start_time : "" !!}"/>
                                                <label>{!! Html::customTrans("events.startTime") !!}</label>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group form-md-line-input required">
                                                <input class="form-control form-control-inline timepicker-default  end_time"
                                                       size="16" type="text" name=""
                                                       value="{!! !empty($schedule_details) ? '' : !empty(session("setting")->schedule_end_time) ? session("setting")->schedule_end_time : "" !!}"/>
                                                <label>{!! Html::customTrans("events.endTime") !!}</label>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group form-md-line-input">
                                                <input type="hidden" name="" id="scheduleTrainers"
                                                       value="{!! (isset($schedule_details[0]->schedule_default_trainer) && !empty($schedule_details[0]->schedule_default_trainer) && $schedule_details[0]->schedule_type == 0 ) ? $schedule_details[0]->schedule_default_trainer : "" !!}"
                                                       class="form-control trainer_div scheduleTrainers ">
                                                <label class="control-label"
                                                       for="scheduleTrainers"> {!! Html::customTrans("events.trainer") !!}</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group form-md-line-input form-md-floating-label has-info required">
                                                <?php
                                                $roomName = "";
                                                ?>
                                                <select name="" id="" class="form-control edited roomId" onchange="this.nextElementSibling.value=''">
                                                    <option value=""></option>
                                                    @if(!empty($all_rooms))
                                                        @foreach($all_rooms as $val)
                                                            <?php
                                                            $roomName = (!empty($schedule_details[0]->roomId) && $schedule_details[0]->roomId == $val->room->RoomID) ? $val->room->RoomName : "";
                                                            ?>
                                                            <option value="{!! $val->room->RoomID !!}"
                                                                    class="dropdown-capitalize"
                                                            @if(isset($schedule_details[0]) && $schedule_details[0]->schedule_type == 0 )
                                                                {!! (!empty($schedule_details[0]->roomId) && $schedule_details[0]->roomId == $val->room->RoomID) ? "selected":'' !!}
                                                                    @endif >
                                                                {!! $val->room->RoomName !!}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                <input class="form-control form-control-inline roomDropDownText edited" type="text" id="slotCustomRoomName" name=""
                                                       value="{!! (isset($schedule_details[0]) && $schedule_details[0]->schedule_type == 0 ) ? $schedule_details[0]->roomId == 0  ? $schedule_details[0]->custom_room_name : ""  : "" !!}">
                                                <label for="roomId">{!! Html::customTrans("events.room") !!}</label>
                                            </div>
                                        </div>
                                        <div class="col-md-12">

                                            <a href="javascript:;"
                                               data-container="body"
                                               data-placement="{!! Config::get('myconfig.tooltip_placement') !!}"
                                               data-original-title="{!! Html::customTrans("events.addNewSlotBtnToolTip") !!}"
                                               class="btn btn-circle btn-icon-only tooltips btn-default addNewSlotDetails pull-left">
                                                <i class="fa fa-plus"></i>
                                            </a>
                                            <a href="javascript:;"
                                               data-container="body"
                                               data-placement="{!! Config::get('myconfig.tooltip_placement') !!}"
                                               data-original-title="{!! Html::customTrans("events.clearSlotBtnToolTip") !!}"
                                               class="btn btn-circle btn-icon-only tooltips btn-default clearSlotDetails pull-left">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="portlet addnewslotbox">


                            </div>
                        </div>
                    </div>


                    <input type="hidden" name="model_mode" value="0" id="model_mode">
                    {!!  Form::close()  !!}
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default save_schedule"
                            id="report_save_button">{!! Html::customTrans("general.save") !!}</button>
                    <button type="button" class="btn btn-default close_schedule_modal"
                            >{!! Html::customTrans("general.close") !!}</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="chart_schedule_data_model" role="dialog">
        <div class="modal-dialog model_dialog_center" style="width: 60%">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">{!! Html::customTrans("events.schedule_details") !!}</h4>
                </div>


                <div class="modal-body">

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default"
                            data-dismiss="modal">{!! Html::customTrans("general.close") !!}</button>
                </div>
            </div>
        </div>
    </div>


    <div class="schedule_slot_clone_div hidden">
        <div class="col-md-12 slot">
            <div class="col-md-1">
                <div class="md-checkbox  time_slot_chk" style="display:none">
                    <input type="checkbox" id="time_slot_chk" value="" name="time_slot_chk[]">
                    <label for="time_slot_chk">
                        <span></span>
                        <span class="check"></span>
                        <span class="box"></span>

                    </label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group form-md-line-input required">
                    <input class="form-control form-control-inline timepicker-default required start_time"
                           size="16" type="text" name="start_time[]" value=""/>
                    <label>{!! Html::customTrans("events.startTime") !!}</label>
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group form-md-line-input required">
                    <input class="form-control form-control-inline timepicker-default required end_time"
                           size="16" type="text" name="end_time[]" value=""/>
                    <label>{!! Html::customTrans("events.endTime") !!}</label>
                </div>
            </div>

            <div class="col-md-5">
                <div class="form-group form-md-line-input">
                    <input type="hidden" name="trainer[]"
                           value=""
                           class="form-control scheduleTrainersClone required">
                    <label class="control-label"
                           for="scheduleTrainers"> {!! Html::customTrans("events.trainer") !!}</label>
                </div>
            </div>

            <div class="col-md-2 mr-t-20">
                <div class="attendance_opration">
                    <div class="floating-group-button floating-left float-zoomin"
                         data-button-state="close">
                                        <span class="btn btn-circle btn-icon-only btn-default pull-right">
                                            <i class="fa fa-ellipsis-h"></i>
                                        </span>
                        <ul class="h-floating-effect">
                            <li>
                                <a href="javascript:;" data-container="body" data-placement="top" data-original-title="{!! Html::customTrans('event.deleteSlotBtnToolTip') !!}"
                                   class="btn btn-circle btn-icon-only btn-default slot_delete tooltips">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:;" data-container="body" data-placement="top" data-original-title="{!! Html::customTrans('events.copySlotBtnToolTip') !!}"
                                   class="btn btn-circle btn-icon-only btn-default slot_duplicate tooltips">
                                    <i class="fa fa-copy"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="cloneAccordianPanel panel panel-default" style="display:none">
        <div class="panel-heading">
            <h4 class="panel-title">
                <a class="accordion-toggle accordion-toggle-styled accordionHeading collapsed" data-toggle="collapse"
                   data-parent="#accordion2" href="#schedule_row_">
                    <span data-index="event_days">  </span>
                    <span data-index="location"> </span>
                    <span data-index="slot_count">  </span>
                    <span data-index="start_time">  </span>
                </a>
            </h4>
        </div>
        <div class="floating-group-button floating-left float-zoomin"
             data-button-state="close">
        <span class="btn btn-circle btn-icon-only btn-default pull-right"><i
                    class="fa fa-ellipsis-h"></i></span>
            <ul class="h-floating-effect">
                <li>
                    <a href="javascript:;" data-container="body" data-placement="top" data-original-title="{!! Html::customTrans("events.TooltipsEditSchedule") !!}"
                       id="schedule_edit" data-id=""
                       class="btn btn-circle btn-icon-only  btn-default schedule_edit pull-right tooltips">
                        <i class="fa fa fa-pencil-square-o"></i></a>
                </li>
                <li>
                    <a href="javascript:;" data-container="body" data-placement="top" data-original-title="{!! Html::customTrans("events.TooltipsCopySchedule") !!}"
                       id="schedule_duplicate" data-id=""
                       class="schedule_duplicate btn btn-circle btn-icon-only  btn-default pull-right tooltips">
                        <i class="fa fa-copy (alias)"></i></a>
                </li>
                <li>
                    <a href="javascript:;" data-container="body" data-placement="top" data-original-title="{!! Html::customTrans("event.TooltipsDeleteSchedule") !!}"
                       id="schedule_delete" data-id=""
                       class="schedule_delete btn btn-circle btn-icon-only btn-default pull-right tooltips">
                        <i class="fa fa-trash-o"></i></a>
                </li>
            </ul>
        </div>
        <div class="panel-collapse collapse" id="schedule_row_">

        </div>


    </div>
    <div class="cloneSlotsListingWrapper clearfix" style="display: none">
        <span class="slotTiming"></span>
        <span class="span_capitalize slotTrainer"></span>
        <span class="slotDescription"></span>
    </div>

    <div class="cloneSlotListPanel panel panel-default" style="display:none">
        <div class="panel-heading">
            <h4 class="panel-title">
                <input type="hidden" class="trainer_data" id="trainer_data_" value="">
                <a class="accordion-toggle accordion-toggle-styled accordionHeading collapsed" data-toggle="collapse"
                   data-parent="#accordion2" href="#schedule_row_">
                    <span data-index="slot_time">  </span>
                    <span data-index="slot_title">  </span>
                    <span data-index="slot_room">  </span>
                    <span data-index="slot_trainer"> </span>
                </a>
            </h4>
        </div>
        <div class="floating-group-button floating-left float-zoomin"
             data-button-state="close">
                                                            <span class="btn btn-circle btn-icon-only btn-default pull-right">
                                                                <i class="fa fa-ellipsis-h"></i>
                                                            </span>
            <ul class="h-floating-effect">
                <li>
                    <a href="javascript:;" data-container="body" data-placement="top" data-original-title="{!! Html::customTrans('events.deleteSlotBtnToolTip') !!}"
                       class="btn btn-circle btn-icon-only btn-default slot_delete tooltips" data-id="">
                        <i class="fa fa-trash"></i>
                    </a>
                </li>
                <li>
                    <a href="javascript:;" data-container="body" data-placement="top" data-original-title="{!! Html::customTrans('event.copySlotBtnToolTip') !!}"
                       class="btn btn-circle btn-icon-only btn-default slot_duplicate tooltips">
                        <i class="fa fa-copy"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="panel-collapse clearfix collapse" id="schedule_row_">

        </div>
    </div>

    <script>
        $(function () {
            setTimeout(function () {
                $('#tab_schedule_info').find('.table-responsive').niceScroll();
            }, 1000);
        });


    </script>


