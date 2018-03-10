{{--
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
</div>
--}}

<div class="tab_activity_form tasktabcontact">

    {!!  Form::open(array('url' => LaravelLocalization::getCurrentLocale()."/". 'activity','method' =>"post",'class'=>'form-row-seperated validate_form add_activity_form',"autocomplete"=>'off')) !!}
    <div class="alert alert-danger display-hide custom-alert">
        <button class="close" data-close="alert"></button>
        {!! Html::customTrans("general.error_msg") !!}
    </div>
    <input type="hidden" name="TaskID" value="@if (!empty($task_data->TaskID)){!!$task_data->TaskID !!} @endif">
    {{--<input type="hidden" name="personId" value="{!! $person_id !!}" id="person_id">--}}

    <div class="row">
        <div class="col-md-12">

            <div class="form-group form-md-line-input form-md-floating-label required">
                <input type="text" name="TaskName" id="TaskName"
                       class="form-control @if (!empty($task_data->TaskName)) edited @endif required"
                       value="@if (!empty($task_data->TaskName)){!!$task_data->TaskName !!} @endif">
                <label for="TaskName">{!! Html::customTrans("task.taskName") !!}</label>
            </div>

            <div class="row date_container">
                <div class="col-md-6">

                    <div class="form-group form-md-line-input required">
                        <input class="form-control form-md-floating-label from-date required current-date"
                               size="16" data-currentdate="{!! current_date("d/m/Y") !!}"
                               type="text" name="TaskBegin" id="TaskBegin"
                               value="@if (!empty($task_data->TaskBegin)){!! format_date($task_data->TaskBegin) !!} @endif"/>
                        <label for="TaskBegin">{!! Html::customTrans("task.beginDate") !!}</label>
                    </div>

                    <div class="form-group form-md-line-input form-md-floating-label has-info required">
                        <select name="TaskStatusID" id="TaskStatusID"  alt="activity_status" template="list" tableType="activitystatus" editView="TaskStatus"
                                class="addlookup form-control edited required ">
                            <option value=""></option>
                            @if(!empty($all_status))
                                @foreach($all_status as $val)
                                    <option value="{!! $val->TaskStatusID !!}"
                                            {!! (!empty($task_data->TaskStatusID) && $task_data->TaskStatusID == $val->TaskStatusID) ? "selected" : ($val->TaskStatusName == "not started" ? "selected" : "") !!} >{!! (LaravelLocalization::getCurrentLocale() == 'en') ? $val->TaskStatusName : (!empty($val->TaskStatusNameDE) ? $val->TaskStatusNameDE : $val->TaskStatusName ) !!}</option>
                                @endforeach
                            @endif
                            <option value="createJob">{!! Html::customTrans("general.createNedit") !!}</option>
                        </select>
                        <label for="TaskStatusID">{!! Html::customTrans("task.status") !!}</label>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group form-md-line-input required">
                        <input class="form-control form-md-floating-label form-control-inline to-date required"
                               size="16" type="text" name="TaskEnd" id="TaskEnd"
                               value="@if (!empty($task_data->TaskEnd)){!! format_date($task_data->TaskEnd) !!} @endif"/>
                        <label for="TaskEnd">{!! Html::customTrans("task.endDate") !!}</label>
                    </div>
                    <div class="form-group form-md-line-input form-md-floating-label has-info required">
                        <select name="PriorityID" id="PriorityID" alt="Priority" template="priority" tableType="priority" editView="Priority"
                                class="addlookup form-control edited required">
                            <option value=""></option>
                            @if(!empty($all_priority))
                                @foreach($all_priority as $val)
                                    <option value="{!! $val->PriorityID !!}"   {!! (!empty($task_data->PriorityID) && $task_data->PriorityID == $val->PriorityID) ? "selected" : ($val->PriorityName == "normal" ? "selected" : "") !!}>{!! (LaravelLocalization::getCurrentLocale() == 'en') ? $val->PriorityName : (!empty($val->PriorityNameDE) ? $val->PriorityNameDE : $val->PriorityName ) !!}</option>
                                    {{--@if(!empty($task_data->PriorityID) && $task_data->PriorityID == $val->PriorityID) selected @endif>{!! $val->PriorityName !!}</option>--}}
                                @endforeach
                            @endif
                            <option value="createJob">{!!Html::customTrans("general.createNedit")!!}</option>
                        </select>
                        <label class="PriorityID">
                            <span class="tooltips" data-container="body" data-placement="{!! Config::get('myconfig.tooltip_placement') !!}"
                                  data-original-title="{!! trans("task.priorityToolTipText") !!}">{!! Html::customTrans("task.priority") !!}
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label">Assigned </label>
                <div class="md-radio-inline">
                    <div class="md-radio">
                        <input id="text12" checked type="radio" name="AssignedTo" class="assignedToMe required"
                               value="1" @if(!empty($task_data->AssignedToUser) && $task_data->AssignedToUser == \Auth::user()->UserID ) checked @endif>
                        <label for="text12">
                            <span></span>
                            <span class="check"></span>
                            <span class="box"></span>
                            {!! Html::customTrans("general.assignMyself") !!}</label>
                    </div>
                    <div class="md-radio">
                        <input id="text123" type="radio" name="AssignedTo" class="assignedToOther required"
                               value="0" @if((!empty($task_data->AssignedToTeam) || !empty($task_data->AssignedToUser)) && $task_data->AssignedToUser !=  \Auth::user()->UserID ) checked @endif>
                        <label for="text123">
                            <span></span>
                            <span class="check"></span>
                            <span class="box"></span>
                            {!! Html::customTrans("general.assignToOther") !!}</label>
                    </div>
                </div>

                <div class="assign_to_other_div form-group form-md-line-input form-md-floating-label has-info required" style="{!! (!empty($task_data->AssignedToTeam) || !empty($task_data->AssignedToUser)) && $task_data->AssignedToUser !=  \Auth::user()->UserID ? '' : 'display:none' !!}">
                    <select id="assign_to_other" name="AssignedToOther"
                            class="form-control modal-select2">
                        <option value="">{!! Html::customTrans("general.please_select") !!}</option>
                        @if(!empty($all_agent))
                            @foreach($all_agent as $val)
                                <option value="agent_{!! $val->UserID !!}"
                                        @if(!empty($task_data->AssignedToUser) && $task_data->AssignedToUser == $val->UserID ) selected @endif
                                        @if($val->UserID == \Auth::user()->UserID) class="current_user" @endif>{!! $val->FirstName ." ".$val->LastName !!}
                                </option>
                            @endforeach

                        @endif
                    </select>
                    <label for="assign_to_other"></label>
                </div>
            </div>
            <br>
            <div class="form-group form-md-line-input">
                <textarea class="form-control" name="TaskNote" id="TaskNote" rows="4" cols="86">@if (!empty($task_data->TaskNote)){!!$task_data->TaskNote !!} @endif</textarea>
                <label for="TaskNote">{!! Html::customTrans("task.note") !!}</label>
            </div>
        </div>

        <div class="col-md-12">
            <div class="row">
                <?php //dd($task_data); ?>
                <div class="col-md-6">
                    <div class="form-group form-md-line-input form-md-floating-label required">
                        <input name="Created" id="Created" class="form-control form-control-inline "
                               size="16" readonly value="{!! !empty($task_data->Created) ? format_date($task_data->Created) : format_date(date('Y-m-d'))  !!}" type="text"/>
                        <label for="Created">{!! Html::customTrans("general.created") !!}</label>
                    </div>
                    <div class="form-group form-md-line-input form-md-floating-label required">
                        <input name="Updated" id="Updated" readonly
                               value="{!! !empty($task_data->created_by) ? $task_data->created_by->FirstName . " " . $task_data->created_by->FirstName  : ""  !!}"
                        class="form-control form-control-inline " size="16" type="text"/>
                        <label for="Updated">{!! Html::customTrans("general.createdBy") !!} </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group form-md-line-input form-md-floating-label required">
                        <input name="Updated" id="Updated" readonly
                               value="{!! !empty($task_data->Updated) ? format_date($task_data->Updated) : format_date(date('Y-m-d'))  !!}"
                               class="form-control form-control-inline" size="16" type="text"/>
                        <label for="Updated">{!! Html::customTrans("general.updated") !!} </label>
                    </div>
                    <div class="form-group form-md-line-input form-md-floating-label required">
                        <input name="Updated" id="Updated" readonly
                               value="{!! !empty($task_data->updated_by) ? $task_data->updated_by->LastName . " " . $task_data->updated_by->LastName : ""  !!}"
                        class="form-control form-control-inline" size="16" type="text"/>
                        <label for="Updated">{!! Html::customTrans("general.updatedBy") !!} </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {!!  Form::close()  !!}
</div>

{{--<div class="modal-footer">
    <button type="button" class="btn default cancel-popup" data-dismiss="modal">{!! Html::customTrans("general.cancel") !!}</button>
    <button type="button" class="btn default save_activity green">{!! Html::customTrans("general.save") !!}</button>
</div>--}}
<script>
    $('.date-picker').datepicker({
        rtl: Metronic.isRTL(),
        orientation: "left",
        language: app_language,
        autoclose: true
    });
    $(".add_activity_form").find(".modal-select2").select2();
    //$(".add_activity_form").find(".modal-select2").select2();
    //$(".add_activity_form").find(".assignedToOther").trigger("change");
    //form_validate(".add_activity_form")
    /* setTimeout(function () {
     $(".activity_form").find(".form-control:first").focus();
     }, 500);*/
    initDatePicker();
</script>