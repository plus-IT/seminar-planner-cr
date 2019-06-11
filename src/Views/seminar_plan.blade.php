<script id="bulePrintListTemplate" type="text/x-jquery-tmpl">
    <li class="dd-item dragableDropableItem" >
    <div class="dd-handle"> ${event_name} </div>
    </li>


</script>
<script id="trainerListTemplate" type="text/x-jquery-tmpl">
    <li class="itemTrainer dragableDropableItem" >
    <div class="dd-handle"> ${FirstName} ${LastName}  </div>
    </li>
</script>
<?php echo '<script id="LocationListTemplate" type="texFirst_Namet/x-jquery-tmpl">
    <li class="itemLocation dragableDropableItem" >
        <i class="fa fa-building"></i><div class="dd-handle"> ${LocationName} ( ${Zip}-${City} )</div>
    </li>
    <ul>
        {{each location_room }}
            <li class="itemRoom dragableDropableItem" locatonId="${LocationID}" roomId="${RoomID}" >
                <i class="fa fa-hand-o-right"></i><div class="dd-handle"> ${room.RoomName} ( ${Zip}-${City} )</div>
            </li>
        {{/each}}
    </ul>
</script>'; ?>

<?php echo '<script id="scheduleTemplate" type="text/x-jquery-tmpl">
    <li scheduleDay="${schedule.event_days}" class="itemSchedule dragableDropableItem ${schedule.event_days == 1 ? \'currentActiveSchedule\' : \'\'} ${schedule.locationConflicted == 1 || schedule.trainerConflicted == 1 ? \'scheduleConflictWarning\' : \'\'}" >
        <div class="dd-handle"> ${schedule.schedule_date} Day - ${schedule.event_days} {{if schedule.schedule_location != null }} , ${schedule.schedule_location.LocationName} {{/if}} </div>
        <div class="conflictBy">
            {{if schedule.locationConflicted == 1 }}
                <a href="javascript:;" data-toggle="popover" data-trigger="focus" title="Conflict Message" data-content="${schedule.detailLocationConflictMessage}">Location</a>
            {{/if}}
            {{if schedule.trainerConflicted == 1}}
                <a href="javascript:;" data-toggle="popover" data-trigger="focus" title="Conflict Message" data-content="${schedule.detailTrainerConflictMessage}">Trainer</a>
            {{/if}}
        </div>
    </li>
</script>'; ?>


<?php echo '<script id="slotTemplate" type="text/x-jquery-tmpl">
    <li class="itemSlot dragableDropableItem ${trainerConflicted == 1 ? \'scheduleConflictWarning\' : \'\'}">
        <div class="dd-handle"> ${formatJavascriptTime(start_time)} - ${formatJavascriptTime(end_time)}  {{if slot_room != null }} , ${slot_room.RoomName} {{/if}}</div>
        {{if trainers != null}}
            ${( $data.trainersArray = trainers.split(",") ),\'\'}
        {{else}}
           ${( $data.trainersArray = []),\'\'}
        {{/if}}
        {{each trainersArray }}
            ${( $data.traName = $value.split("-") ),\'\'}
            
                <span class="slotTrainerBox" id="${traName[0]}"> ${traName[1]}{{if traName[2] }}-${traName[2]} {{/if}} <a class="cancelbtnclose">X</a></span>
            
        {{/each}}
        <div class="conflictBy">
        {{if trainerConflicted == 1 }}
            <a href="javascript:;" data-toggle="popover" data-trigger="focus" title="Conflict Message" data-content="${detailTrainerConflictMessage}">Trainer</a>
        {{/if}}
         </div>
    </li>
</script>'; ?>

<div class="row">
    <div class="col-md-3">
        <div class="dd" id="bulePrintListWrapper">
            <ol class="dd-list" id="bulePrintList">

            </ol>
        </div>
    </div>
    <div class="col-md-9" style="margin-top: 15px">
        <div class="topcalander">
            <a class="filterhideshow currentShowFilter">
                <span class="hideStuff"><i class="fa fa-plus"></i> <span>{!! Html::customTrans("seminarPlanner.showFilter") !!}</span></span>
                <span class="showStuff"><i class="fa fa-minus"></i> <span>{!! Html::customTrans("seminarPlanner.hideFilter") !!}</span></span>
            </a>
            <div class="lablecolors">
                <ul>
                    <li><span class="" style="background:{!! $seminar_days_new->planned_calendar_background !!}"></span>{!! Html::customTrans("seminarPlanner.plannedEventsBackground") !!}</li>
                    <li><span class="" style="background:{!! $seminar_days_new->draft_calendar_background !!}"></span>{!! Html::customTrans("seminarPlanner.draftEventsBackground") !!}</li>
                    <li><span class="" style="background:{!! $seminar_days_new->cancel_seminars_background !!}"></span>{!! Html::customTrans("seminarPlanner.cancelEventsBackground") !!}</li>
                    <li><span class="" style="background:{!! $seminar_days_new->new_seminar_calendar_background !!}"></span>{!! Html::customTrans("seminarPlanner.conflictEventsBackground") !!}</li>
                </ul>
            </div>
        </div>

        <fieldset class="scheduler-border filterable " style="display: none">
            <legend class="fieldset-title ">{!! Html::customTrans("seminarPlanner.filters") !!}</legend>
            <div class="top-label">
                <div class="col-md-12">
                    <div class="row">

                        <div class="col-xs-6 col-md-4">
                            <div class="form-group">
                                <label class="col-md-3 control-label">{!! Html::customTrans("seminarPlanner.eventLocation") !!}</label>
                                <div class="col-md-9">
                                    <div class="form-group lineManager manager-auto-select">
                                        {!! Form::select2ajax('location',"",'filterselect table-group-action-input form-control filter', "" ,'location/getAllLocation',"",50)!!}

                                    </div>
<!--                                    <select name="location" multiple id="locationselect2"
                                    class="filterselect table-group-action-input form-control filter">

                                @if(!empty($all_location))
                                @foreach($all_location as $val)
                                <option value="{!! $val->LocationID !!}"
                                        >{!! $val->LocationName !!}</option>
                                @endforeach
                                @endif
                            </select>-->
                                </div>
                            </div>

                        </div>
                        <div class="col-xs-6 col-md-4">
                            <div class="form-group">
                                <label class="col-md-3 control-label">{!! Html::customTrans("seminarPlanner.eventTrainer") !!}</label>
                                <div class="col-md-9">
                                    <div class="form-group lineManager manager-auto-select">
                                        {!! Form::select2ajax('trainer',"",'filterselect table-group-action-input form-control filter', "" ,'contact/getAllTrainer',"",50)!!}

                                    </div>

                                </div>
                            </div>

                        </div>

                        <div class="col-xs-6 col-md-4">
                            <div class="form-group">
                                <label class="col-md-3 control-label">{!! Html::customTrans("seminarPlanner.filterEvent") !!}</label>
                                <div class="col-md-9">
                                    <?php /* <select name="event" multiple id="event_select"
                                      class="filterselect table-group-action-input form-control filter ">

                                      @if(!empty($all_events))
                                      @foreach($all_events as $val)
                                      <option value="{!! $val->id !!}"
                                      >{!! $val->event_name !!}</option>
                                      @endforeach
                                      @endif
                                      </select> */ ?>
                                    <div class="form-group lineManager manager-auto-select">

                                        <input  id="event_select" name="event"
                                                class="form-control filter"
                                                value="">

                                    </div>

                                </div>
                            </div>

                        </div>
                        <div class=" col-xs-6 col-md-4">
                            <div class="form-group">
                                <label class="col-md-3 control-label">{!! Html::customTrans("seminarPlanner.eventCategory") !!}</label>
                                <div class="col-md-9">
                                    <div class="form-group lineManager manager-auto-select">
                                        {!! Form::select2ajax('event_category',"",'filterselect table-group-action-input form-control filter', "" ,'seminar/getAllCategory',"",50)!!}

                                    </div>
<!--                                    <select name="event_category" multiple
                                            class="filterselect table-group-action-input form-control filter ">

                                        @if(!empty($all_event_category))
                                        @foreach($all_event_category as $val)
                                        <option value="{!! $val->id !!}"
                                                >
                                            @if(LaravelLocalization::getCurrentLocale() == 'en')
                                            {!! $val->event_category_name !!}
                                            @else
                                            {!! $val->event_category_name_de !!}
                                            @endif
                                        </option>
                                        @endforeach
                                        @endif
                                    </select>-->
                                </div>
                            </div>

                        </div>
                        <div class=" col-xs-6 col-md-4">
                            <div class="form-group">
                                <label class="col-md-3 control-label">{!! Html::customTrans("seminarPlanner.status") !!}</label>
                                <div class="col-md-9">
                                    <div class="form-group lineManager manager-auto-select">
                                        <select name="event_status" multiple  class="filterselect table-group-action-input form-control filter ">

                                            <option value="draft">{!! Html::customTrans("seminarPlanner.draft") !!}</option>
                                            <option value="confirm">{!! Html::customTrans("seminarPlanner.confirm") !!}</option>
                                            <option value="cancel">{!! Html::customTrans("general.cancel") !!}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class=" col-xs-6 col-md-4">
                            <div class="form-group">
                                <label class="col-md-3 control-label">{!! Html::customTrans("seminarPlanner.seminarPlannedBy") !!}</label>
                                <div class="col-md-9">
                                    <div class="form-group lineManager manager-auto-select">
                                        {!! Form::select2ajax('planned_by',"",'filterselect table-group-action-input form-control filter ', "" ,'filter/getAllAgents',"",50)!!}
                                    </div>
<!--                                    <select name="planned_by" multiple
                                            class="filterselect table-group-action-input form-control filter ">

                                        @if(!empty($all_system_user))
                                        @foreach($all_system_user as $val)
                                        <option value="{!! $val->UserID !!}"
                                                >{!! $val->FirstName  . " " . $val->LastName !!}</option>
                                        @endforeach
                                        @endif
                                    </select>-->
                                </div>
                            </div>

                        </div>
                        <div class="col-xs-6 col-md-4">
                                <div class="form-group lineManager manager-auto-select">
                                    <label class="col-md-3 control-label">{!! Html::customTrans("seminarPlanner.event_region") !!}</label>
                                    <div class="col-md-9">
                                        <div class="form-group">
                                            {!! Form::select2ajax('event_region',"",'filterselect table-group-action-input form-control filter', "" ,'event/getAllRBRegions',"",50)!!}
    
                                        </div>
    
                                    </div>
                                </div>
    
                            </div>
                        <div class=" col-xs-6 col-md-4">
                            <label class="col-md-3 control-label"> </label>
                            <div class="form-group">
                                <a href="javascript:;" class="btn btn-default reset-filter">{!! Html::customTrans("seminarPlanner.reset") !!}</a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </fieldset>
        <div class="mymaincalender" id="calendar"></div>
        <a href="javascript:;" style="display: none;"
           emailToSend = ""
           class="send-mail informParticipantOnCancelSeminar"
           data-templateType="inform-participant-on-cancel-seminar" data-moduleType="seminar-planner">
        </a>
        <a href="javascript:;" style="display: none;"
           emailToSend = ""
           class="send-mail informLevel2UserOnCancelSeminar"
           data-templateType="inform-level2-user-on-cancel-seminar" data-moduleType="seminar-planner">
        </a>
          <a href="javascript:;" style="display: none;"
           emailToSend = ""
           class="send-mail sendemailtoalltrainers"
           data-templateType="send-email-to-all-trainers" data-moduleType="seminar-planner">
        </a>
        <a href="javascript:;" style="display: none;"
           emailToSend = ""
           class="send-mail informParticipantOnChangeSeminar"
           data-templateType="inform-participant-on-change-date-seminar" data-moduleType="seminar-planner">
        </a>
        <a href="javascript:;" style="display: none;"
           emailToSend = ""
           class="send-mail informTrainerOnCancelSeminar"
           data-templateType="inform-trainer-on-cancel-seminar" data-moduleType="seminar-planner">
        </a>
        <a href="javascript:;" style="display: none;"
           emailToSend = ""
           class="send-mail informLocationOnCancelSeminar"
           data-templateType="inform-location-on-cancel-seminar" data-moduleType="seminar-planner">
        </a>

    </div>
</div>
<div style="display:none;">
    <div id='calendar'></div>
    <div id="events-popover-head" class="hide">{!! Html::customTrans('general.action') !!}</div>
    <div id="events-popover-content" class="hide">
        @can('seminarPlanner.editSeminar')
        <a href="javascript:void(0)" class="edit_seminar"
           style="display:inline-block;width: 100%;padding: 20px 0px;text-decoration: underline;">{!! Html::customTrans('events.edit_seminar') !!}</a>
        @endcan
        @can('seminarPlanner.assignTrainer')
        <a href="javascript:void(0)" class="assign_trainer"
           style="display:inline-block;width: 100%;padding: 20px 0px;text-decoration: underline;">{!! Html::customTrans('events.assignTrainer') !!}</a>
        @endcan
    </div>
</div>
    <div class="modal fade " id="edit_seminar" tabindex="-1" role="" aria-hidden="true" data-backdrop="static" data-keyboard="false" >
    <div class="modal-dialog" style=" width: 90%; height: 100%;" >
        <div class="modal-content" >
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <div class="input-group">
                    <label class="control-label text-label">{!! Html::customTrans('seminarPlanner.editSeminar') !!}</label>
                </div>
            </div>
            <div class="modal-body" style="">
                <div class="dl-horizontal editSeminarData">

                </div>
            </div>
            <div class="modal-footer">
                <button style="display: none" type="button"  data-templatetype="training-materials" data-moduletype="seminar-planner" id="send_email_training_materials" class="btn default green btn_simply_green send-mail"
                        >{!! Html::customTrans("general.send_mail") !!}</button>
                <button type="button" id="save_button_class" class="btn default  save_address_data green btn_simply_green"
                        >{!! Html::customTrans("general.save") !!}</button>
                <button type="button" class="btn default cancel-popup"
                        data-dismiss="modal">{!! Html::customTrans("general.close_btn") !!}</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade " id="daysCalculationPopup" tabindex="-1" role="" aria-hidden="true" style="">
    <div class="modal-dialog schedule_modal_width" style="">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <div class="input-group">
                    <label class="control-label text-label">{!! Html::customTrans('seminarPlanner.recalculateDaysTitle') !!}</label>
                </div>
            </div>
            <div class="modal-body" style="">
                <div class="dl-horizontal">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{!! Html::customTrans('seminarPlanner.srNo') !!}</th>
                                <th>{!! Html::customTrans('seminarPlanner.seminarDays') !!}</th>
                                <th>{!! Html::customTrans('seminarPlanner.currentDate') !!}</th>
                                <th>{!! Html::customTrans('seminarPlanner.currentweekday') !!}</th>
                                <th>{!! Html::customTrans('seminarPlanner.changeDay') !!}</th>
                                <th>{!! Html::customTrans('seminarPlanner.recalculateDate') !!}</th>
                                <th>{!! Html::customTrans('seminarPlanner.recalculateDay') !!}</th>
                            </tr>
                        </thead>
                        <tbody class="daysCalculationBody">

                        </tbody>

                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn default recalculateDaysConfirmBtn"
                        data-dismiss="modal">{!! Html::customTrans("seminarPlanner.Recalculate") !!}</button>
                <button type="button" class="btn default cancel-popup-recalculate"
                        data-dismiss="modal">{!! Html::customTrans("seminarPlanner.abort") !!}</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade " id="assignTrainerLocationPopup" tabindex="-1" role="" aria-hidden="true" style="">
    <div class="modal-dialog" style="width: 80%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <div class="input-group">
                    <label class="control-label text-label">{!! Html::customTrans('seminarPlanner.trainerLocationAssignment') !!} <span class='seminarNameAsHeading'> </span></label>
                </div>
            </div>
            <div class="modal-body" style="">
                <div class="row">
                    <div class="col-md-4 trainerlLocationListWrapper">
                        <h3>{!! Html::customTrans('seminarPlanner.selectTrainer') !!}
                            <a href="javascript:;" class="fa fa-info helpInfoForDragDrop" data-toggle="popover" 
                               data-trigger="focus" title="{!! Html::customTrans('seminarPlanner.infoDragTrainerHeading') !!}" 
                               data-content="{!! Html::customTrans('seminarPlanner.infoDragTrainerMessage') !!}">

                            </a>
                        </h3>
                        <div class="" style="margin-bottom: 10px;">
                            <div class="input-icon right form-group">
                                <i class="fa fa-search search-button"></i>
                                <input type="text" placeholder="{!! Html::customTrans('general.search') !!}" 
                                       name="search_trainer" class="form-control">
                            </div>
                        </div>
                        <ul class="trainerList" style="overflow:auto">

                        </ul>
                        <h3>{!! Html::customTrans('seminarPlanner.selectLocation') !!}
                            <a href="javascript:;" class="fa fa-info helpInfoForDragDrop" data-toggle="popover" data-trigger="focus" title="{!! Html::customTrans('seminarPlanner.infoDragLocationRoomHeading') !!}" data-content="{!! Html::customTrans('seminarPlanner.infoDragLocationRoomMessage') !!}" ></a></h3>
                        <div class="" style="margin-bottom: 10px;">
                            <div class="input-icon right form-group">
                                <i class="fa fa-search search-button"></i>
                                <input type="text" placeholder="{!! Html::customTrans('general.search') !!}" 
                                       name="search_location" class="form-control">
                            </div>
                        </div>
                        <ul class="locationList" style="overflow:auto">

                        </ul>

                    </div>
                    <div class="col-md-4 scheduleListWrapper">
                        <h3>{!! Html::customTrans('seminarPlanner.scheduleDays') !!}</h3>

                        <ul class="scheduleList" style="overflow:auto">

                        </ul>
                    </div>
                    <div class="col-md-4 SlotListWrapper">
                        <h3>{!! Html::customTrans('seminarPlanner.scheduleSlot') !!}</h3>
                        <ul class="slotList" style="overflow:auto">

                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn default"
                        data-dismiss="modal">{!! Html::customTrans("seminarPlanner.cancel") !!}</button>
            </div>
        </div>
    </div>
</div>
{{--<div class="modal fade" id="scheduleModal" role="dialog">--}}
{{--<div class="modal-dialog model_dialog_center schedule_modal_width">--}}
{{--<div class="modal-content">--}}
            {{--<div class="modal-header">--}}
                {{--<button type="button" class="close" data-dismiss="modal">&times;</button>--}}
                {{--<h4 class="modal-title">{!! Html::customTrans("seminarPlanner.add_schedule") !!}</h4>--}}
            {{--</div>--}}


          {{--<div class="modal-body">--}}

           {{--</div>--}}


          {{--<div class="modal-footer">--}}
                {{--<button type="button" class="btn btn-default save_schedule"--}}
                        {{--id="report_save_button">{!! Html::customTrans("general.save") !!}</button>--}}
                {{--<button type="button" class="btn btn-default close_schedule_modal"--}}
                        {{--data-dismiss="modal">{!! Html::customTrans("general.close") !!}</button>--}}
            {{--</div>--}}
        {{--</div>--}}

    {{--</div>--}}
{{--</div>--}}

<!-- reasonfor cancellation seminar -->
<div class="modal fade " id="seminarCancellation" tabindex="-1" role="" aria-hidden="true" >
    <div class="modal-dialog" style=" width: 90%; height: 100%;" >
        <div class="modal-content" >
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <div class="input-group">
                    <label class="control-label text-label">{!! Html::customTrans('seminarPlanner.writeReasonForCancelSeminar') !!}</label>
                </div>
            </div>
            <div class="modal-body" style="">
                
                    <div class="form-group form-md-line-input form-md-floating-label has-info">
                        <select name="cancelReason" id="seminarCancelReason" alt="cancel_reason" template="list"
                                tableType="cancel_reason" editView="CancelReason"
                                class="addlookup table-group-action-input  form-control edited required"
                                >
                            <option value=""></option>
                            @if(!empty($cancel_reason_list))
                            @foreach($cancel_reason_list as $val)
                            <option value="{!! $val->id !!}" class="dropdown-capitalize"
                                    >{!! (LaravelLocalization::getCurrentLocale() == 'en') ?  $val->reason_en : (!empty($val->reason_de) ? $val->reason_de : $val->reason_en ) !!}</option>
                            @endforeach
                            @endif
                            <option value="createJob">{!! Html::customTrans("general.createNedit") !!}</option>
                        </select>
                        <label for="seminarCancelReason" class="tooltips"
                               data-placement="{!! Config::get('myconfig.tooltip_placement') !!}"
                               data-original-title="{!! trans('contact.ToolTipCancelReasonText') !!}">{!! Html::customTrans("seminarPlanner.cancel_reason") !!}</label>
                    </div>
</div>
<div class="modal-footer">
    <button type="button" id="btnCancelSeminar" class="btn default btnCancelSeminar"
            >{!! Html::customTrans("seminarPlanner.cancelSeminar") !!}</button>
    <button type="button" class="btn default"
            data-dismiss="modal">{!! Html::customTrans("general.cancel") !!}</button>
</div>
</div>
</div>
</div>


<!-- reason for move confirm seminar -->
<div class="modal fade " id="seminarMove" tabindex="-1" role="" aria-hidden="true" >
    <div class="modal-dialog" style=" width: 90%; height: 100%;" >
        <div class="modal-content" >
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <div class="input-group">
                    <label class="control-label text-label">{!! Html::customTrans('seminarPlanner.writeReasonForMoveSeminar') !!}</label>
                </div>
            </div>
            <div class="modal-body" style="">
                <div class="textarea-note">
                    <div class="form-group form-md-line-input required">
                        <textarea class="form-control required" name="text" id="seminarMoveReason" rows="4" cols="86" placeholder="{!! Html::customTrans("seminarPlanner.writeReasonForMoveSeminar") !!}" maxlength="300"></textarea>
                        <label for="text"></label>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button"  class="btn default btnMoveSeminar green btn_simply_green"
                        >{!! Html::customTrans("seminarPlanner.btnMoveSeminar") !!}</button>
                <button type="button" class="btn default btnCancelRecalculatedSeminar"
                        data-dismiss="modal">{!! Html::customTrans("general.cancel") !!}</button>
            </div>
        </div>
    </div>
</div>
