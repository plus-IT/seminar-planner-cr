{!!  Form::open(['url' => LaravelLocalization::getCurrentLocale()."/". 'seminar-planner/schedule','method' =>"post",'class'=>' form-row-seperated validate_form add_schedule_form',"autocomplete"=>'off']) !!}

<input class="form-control form-control-inline schedule-date required"
       size="16" type="hidden" name="ScheduleID"
       value="{!! !empty($schedule_details) ? $schedule_details[0]->id: "0" !!}"/>
<div class="row">
    <div class="clearfix schedule-date-stuff">
        <div class="col-md-12">
            <div class="col-md-4">
                <div class="form-group form-md-line-input required">
                    <input class="form-control form-control-inline  required"
                           size="16" type="text" name="event_days" readonly
                           value="{!! !empty($schedule_details[0]->event_days)?$schedule_details[0]->event_days:$day !!}"/>
                    <label>{!! Html::customTrans("events.event_days") !!}</label>
                </div>
                {{--<div class="form-group form-md-line-input required">--}}
                {{--<input class="form-control form-control-inline schedule-date required"--}}
                {{--size="16" type="text" name="schedule_date"--}}
                {{--value="{!! !empty($schedule_details)?format_date($schedule_details[0]->schedule_date):"" !!}"/>--}}
                {{--<label>{!! Html::customTrans("events.beginDate") !!}</label>--}}
                {{--</div>--}}
            </div>

            <input type="hidden" name="next_date"
                   value="{!! !empty($schedule_details)? format_date(\Carbon\Carbon::parse($schedule_details[0]->schedule_date)->addDay(1)):'' !!}"
                   id="next_date">

            <div class="col-md-5">
                <div class="form-group form-md-line-input form-md-floating-label has-info required">
                    <select name="LocationID" id="LocationID" class="form-control edited required ">
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

            <div class="col-md-3">
                <div class="form-group form-md-line-input form-md-floating-label has-info required">
                    <select name="roomId" id="roomId" class="form-control edited required ">
                        <option value=""></option>
                        @if(!empty($all_rooms))
                            @foreach($all_rooms as $val)
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
                    <label for="roomId">{!! Html::customTrans("events.room") !!}</label>
                </div>
            </div>
        </div>
        <?php
        if (!empty($schedule_details[0]->weekdays))
            $check_weekdays = explode(",", $schedule_details[0]->weekdays);
        else
            $check_weekdays = [];
        ?>
        <div class="col-md-12">
            <div class="col-md-8">
                <div class="col-md-6">
                    <div class="md-checkbox-list">
                        <div class="md-checkbox">
                            <input type="checkbox" id="sunday" class="md-check" name="weekdays[]"
                                   value="0" {!! (in_array("0",$check_weekdays)) ? 'checked' : '' !!}>
                            <label for="sunday">
                                <span></span>
                                <span class="check"></span>
                                <span class="box"></span>
                                {!! Html::customTrans("events.sunday") !!}
                            </label>
                        </div>
                        <div class="clearfix"></div>
                        <div class="md-checkbox">
                            <input type="checkbox" id="monday" class="md-check" name="weekdays[]"
                                   value="1" {!! (in_array("1",$check_weekdays)) ? 'checked' : '' !!}>
                            <label for="monday">
                                <span></span>
                                <span class="check"></span>
                                <span class="box"></span>
                                {!! Html::customTrans("events.monday") !!}
                            </label>
                        </div>
                        <div class="clearfix"></div>
                        <div class="md-checkbox">
                            <input type="checkbox" id="tuesday" class="md-check" name="weekdays[]"
                                   value="2" {!! (in_array("2",$check_weekdays)) ? 'checked' : '' !!}>
                            <label for="tuesday">
                                <span></span>
                                <span class="check"></span>
                                <span class="box"></span>
                                {!! Html::customTrans("events.tuesday") !!}
                            </label>
                        </div>
                        <div class="md-checkbox">
                            <input type="checkbox" id="wednesday" class="md-check" name="weekdays[]"
                                   value="3" {!! (in_array("3",$check_weekdays)) ? 'checked' : '' !!}>
                            <label for="wednesday">
                                <span></span>
                                <span class="check"></span>
                                <span class="box"></span>
                                {!! Html::customTrans("events.wednesday") !!}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="md-checkbox-list">
                        <div class="md-checkbox">
                            <input type="checkbox" id="thursday" class="md-check" name="weekdays[]"
                                   value="4" {!! (in_array("4",$check_weekdays)) ? 'checked' : '' !!}>
                            <label for="thursday">
                                <span></span>
                                <span class="check"></span>
                                <span class="box"></span>
                                {!! Html::customTrans("events.thursday") !!}
                            </label>
                        </div>
                        <div class="md-checkbox">
                            <input type="checkbox" id="friday" class="md-check" name="weekdays[]"
                                   value="5" {!! (in_array("5",$check_weekdays)) ? 'checked' : '' !!}>
                            <label for="friday">
                                <span></span>
                                <span class="check"></span>
                                <span class="box"></span>
                                {!! Html::customTrans("events.friday") !!}
                            </label>
                        </div>
                        <div class="md-checkbox">
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
            <div class="col-md-4">
                <div class="form-group form-md-line-input required">
                    <input class="form-control form-control-inline  required"
                           type="text" name="duration_between_previous_day"
                           value="{!! !empty($schedule_details[0]->duration_between_previous_day)? $schedule_details[0]->duration_between_previous_day : '' !!}"/>
                    <label>{!! Html::customTrans("events.duration_between_previous_day") !!}</label>
                </div>
            </div>
        </div>
    </div>

    <div class="schedule_slot scheduleSlotList col-md-12 " id="scroller_schedul">
        <div class="panel-group accordion col-md-11" id="accordion2">
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
                    //                                                dd($trainer_data);
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
                                    <span data-index='slot_trainer'> {!! $trainer_name !!}</span>
                                    
                                    
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
                                    <a href="javascript:;"
                                       class="btn btn-circle btn-icon-only btn-default slot_delete"
                                       data-id="@if(isset($schedule_slot)){!! $schedule_slot->schedule_slotID!!} @endif">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;"
                                       class="btn btn-circle btn-icon-only btn-default slot_duplicate">
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

                                <div class="col-md-5">
                                    <div class="form-group form-md-line-input">
                                        <input type="hidden" name="trainer[]"
                                               value="{!! $schedule_slot->trainer !!}"
                                               class="form-control trainer_div scheduleTrainers_{{ $key }} required">
                                        <label class="control-label"
                                               for="scheduleTrainers"> {!! Html::customTrans("events.trainer") !!}</label>
                                    </div>
                                </div>

                                {{--<div class="col-md-2 mr-t-20">--}}
                                {{--<div class="attendance_opration">--}}
                                {{----}}
                                {{--</div>--}}
                                {{--</div>--}}
                                <div class="col-md-12">
                                    <div class="textarea-note-schedule">
                                        <div class="form-group form-md-line-input required pull-left">
                                                        <textarea class="form-control required" name="description[]"
                                                                  id="description" rows="4"
                                                                  cols="86"
                                                                  placeholder="{!! Html::customTrans("events.description") !!}"
                                                                  maxlength="300">@if(isset($schedule_slot)){!!$schedule_slot->description !!}@endif</textarea>
                                            <label for="description"></label>
                                        </div>
                                        <a href="javascript:;"
                                           class="btn btn-circle btn-icon-only btn-default saveSlotDetails pull-left"
                                           data-parentDivId=schedule_slot_panel_{!! $schedule_slot->schedule_slotID !!}>
                                            <i class="fa fa-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                @endforeach
            @endif
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="portlet ">
        <div class="portlet-title">
            <div class="caption">
                <i><i>


                    </i></i><span> Add New Slot </span></div>
        </div>
        <div class="schedule_slot schedule_add_slot scoller clearfix" style="bottom: 0px">
            <div class="col-md-12">
                <div class="col-md-2">
                    <div class="form-group form-md-line-input required">
                        <input class="form-control form-control-inline timepicker-default  start_time"
                               size="16" type="text" name=""
                               value="{!! !empty($schedule_details) ? '' : !empty(session("setting")->schedule_start_time) ? session("setting")->schedule_start_time : "" !!}"/>
                        <label>{!! Html::customTrans("events.startTime") !!}</label>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group form-md-line-input required">
                        <input class="form-control form-control-inline timepicker-default  end_time"
                               size="16" type="text" name=""
                               value="{!! !empty($schedule_details) ? '' : !empty(session("setting")->schedule_end_time) ? session("setting")->schedule_end_time : "" !!}"/>
                        <label>{!! Html::customTrans("events.endTime") !!}</label>
                    </div>
                </div>

                <div class="col-md-5 ">
                    <div class="form-group form-md-line-input">
                        <input type="hidden" name=""
                               value=""
                               class="form-control trainer_div scheduleTrainers ">
                        <label class="control-label"
                               for="scheduleTrainers"> {!! Html::customTrans("events.trainer") !!}</label>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="textarea-note-schedule">
                        <div class="form-group form-md-line-input required pull-left">
                                                            <textarea class="form-control " name="" id="description"
                                                                      rows="4"
                                                                      cols="86"
                                                                      placeholder="{!! Html::customTrans("events.description") !!}"
                                                                      maxlength="300"></textarea>
                            <label for="description"></label>
                        </div>
                        <a href="javascript:;"
                           data-container=".modal-dialog"
                           data-placement="{!! Config::get('myconfig.tooltip_placement') !!}"
                           data-original-title="{!! Html::customTrans("events.addNewSlotBtnToolTip") !!}"
                           class="btn btn-circle btn-icon-only tooltips btn-default addNewSlotDetails pull-left">
                            <i class="fa fa-plus"></i>
                        </a>
                        <a href="javascript:;"
                           data-container=".modal-dialog"
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

    <input type="hidden" name="model_mode" value="0" id="model_mode">



</div>

{!!  Form::close()  !!}