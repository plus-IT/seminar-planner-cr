<div class="control-group apply_table_operation">
    <div class="portlet light">
        <div class="portlet-title">
            <div class="caption caption-md">

                <span class="bold capital">{!! Html::customTrans("general.activity") !!}  (<span class="actvity_count">{!! count(($activity_data->eventTask)) !!}</span>)</span>
            </div>
            <div class="pull-right">
                @can('seminarPlanner.taskAdd')
                <a href="javascript:;" id="add_activity_btn"
                   class="btn tooltips btn-circle btn-icon-only btn-default add_activity tab_plus_btn" data-original-title="{!! Html::customTrans("events.TooltipsAddTask") !!}"><i class="fa fa-plus"></i></a>
                @endcan
                <a href="javascript:;"  style="display: none;"
                   class="btn tooltips btn-circle btn-icon-only btn-default close-btn-task" data-original-title="{!! Html::customTrans("events.TooltipsClose") !!}">
                    <i class="fa fa-close"></i></a>
            </div>
        </div>
        <div id="tab_activity_info">
            @if(isset($activity_data))
                @foreach ($activity_data->eventTask as $activity)

                    <div class="activities-item activity_row_{!! $activity->Task->TaskID !!}">
                        <div class="row">
                            <div class="col-sm-7">
                                {{--<div class="activities-checkbox activity_row" data-id="{!! $participant_activity->Task->TaskID !!}">
                                    <input type="hidden" name="personId" value="{!! $participant_activity->PersonID !!}">
                                    <input type="checkbox" id="editActivityId{!! $participant_activity->Task->TaskID !!}" name="editActivityId" value="{!! $participant_activity->Task->TaskID !!}" class="chk_activity">
                                    <label for="editActivityId{!! $participant_activity->Task->TaskID !!}"></label>
                                </div>--}}
                                <div class="activities-info">
                                    <strong data-index="TaskName">{!! $activity->Task->TaskName !!}</strong>

                                    <p data-index="TaskNote">{!! $activity->Task->TaskNote!!} </p>
                                </div>
                            </div>
                            <div class="col-sm-5">

                                <div class="activity-button">
                                    @can('seminarPlanner.taskDelete')
                                    <a href="" class="btn tooltips btn-circle btn-icon-only btn-default delete_activity"
                                       data-id="{!! $activity->Task->TaskID !!}" data-original-title="{!! Html::customTrans("events.TooltipsDeleteTask") !!}"><i
                                                class="fa fa-trash "></i></a>
                                    @endcan
                                    @can('seminarPlanner.taskEdit')
                                    <a href="" class="btn tooltips btn-circle btn-icon-only btn-default edit_activity"
                                       data-id="{!! $activity->Task->TaskID !!}" data-original-title="{!! Html::customTrans("events.TooltipsEditTask") !!}"><i
                                                class="fa fa-pencil"></i></a>
                                    @endcan
                                </div>
                                <div class="activities-status">
                                    <strong data-index="TaskStatusID">
                                        {!! (LaravelLocalization::getCurrentLocale() == 'en') ? $activity->Task->TaskStatus->TaskStatusName : (!empty($participant_activity->Task->TaskStatus->TaskStatusNameDE) ? $activity->Task->TaskStatus->TaskStatusNameDE : $activity->Task->TaskStatus->TaskStatusName )  !!}

                                    </strong>
                                    <p data-index="TaskEnd">{!! format_date(isset($activity->Task) && !empty($activity->Task) ? $activity->Task->TaskEnd : "") !!} </p>

                                </div>

                            </div>


                        </div>

                    </div>

                @endforeach
            @endif
        </div>
    </div>

    <div class="clone-activities-item activities-item" style="display: none;">
        <div class="row">
            <div class="col-sm-7">

                <div class="activities-info">
                    <strong data-index="TaskName"></strong>
                    <p data-index="TaskNote"></p>
                </div>
            </div>
            <div class="col-sm-5">
                <div class="activity-button">
                    <a href="" class="btn tooltips btn-circle btn-icon-only btn-default delete_activity" data-id="0" data-original-title="{!! Html::customTrans("events.TooltipsDeleteTask") !!}"><i class="fa fa-trash " ></i></a>
                    <a href="" class="btn tooltips btn-circle btn-icon-only btn-default edit_activity" data-id="0" data-original-title="{!! Html::customTrans("events.TooltipsEditTask") !!}"><i class="fa fa-pencil"></i></a>
                </div>
                <div class="activities-status">
                <strong data-index="TaskStatusID"></strong>
                <p data-index="TaskBegin"> </p>
            </div>
            </div>

        </div>
    </div>

    <div class="col-md-12">
        <div class="activity_form" id="tab_activity_form" style="display: none">


        </div>
    </div>
</div>

    {{--<div class="control-group apply_table_operation">--}}
    {{--<div class="tools text-right col-md-12">--}}
    {{--@can('event.activity.create')--}}
    {{--<span><a href="{!! url(LaravelLocalization::getCurrentLocale() . "/" ."activity/0") !!}"--}}
    {{--data-target="#add_activity" class="add_activity"--}}
    {{--data-toggle="modal"><i--}}
    {{--class="fa fa-plus"></i></a></span>&nbsp;&nbsp;&nbsp;--}}
    {{--@endcan--}}
    {{--@can('event.activity.update')--}}
    {{--<span><a data-href="{!! url(LaravelLocalization::getCurrentLocale() . "/" ."activity") !!}" href=""--}}
    {{--data-target="#add_activity" class="edit_activity"--}}
    {{--data-toggle="modal"><i--}}
    {{--class="fa fa-pencil"></i></a></span>&nbsp;&nbsp;&nbsp;--}}
    {{--@endcan--}}
    {{--@can('event.activity.delete')--}}
    {{--<span><a data-href="{!! url(LaravelLocalization::getCurrentLocale() . "/" ."activity") !!}" href=""--}}
    {{--class="remove-row delete-activity"><i--}}
    {{--class="fa fa-minus"></i></a></span>&nbsp;&nbsp;&nbsp;--}}
    {{--@endcan--}}
    {{--</div>--}}
    {{--<div class="clear">&nbsp;</div>--}}

    {{--<div class="col-md-12 v">--}}
    {{--<div class="table-responsive">--}}
    {{--<table class="table table-hover dataTable">--}}
    {{--<thead>--}}
    {{--<tr>--}}
    {{--<th>{!! Html::customTrans("label.subject") !!}</th>--}}
    {{--<th>{!! Html::customTrans("label.begin") !!}</th>--}}
    {{--<th>{!! Html::customTrans("label.end") !!}</th>--}}
    {{--<th>{!! Html::customTrans("label.priority") !!}</th>--}}
    {{--<th>{!! Html::customTrans("label.status") !!}</th>--}}
    {{--<th>{!! Html::customTrans("label.assignee") !!}</th>--}}
    {{--<th>{!! Html::customTrans("label.note") !!}</th>--}}
    {{--</tr>--}}
    {{--</thead>--}}
    {{--<tbody>--}}
    {{--@if(isset($activity_data))--}}
    {{--@foreach ($activity_data->eventTask as $activity)--}}
    {{--<tr data-recordid="{!! $activity->Task->TaskID !!}" class="activity_row">--}}
    {{--<td data-index="TaskName">{!! $activity->Task->TaskName !!}</td>--}}
    {{--<td data-index="TaskBegin">{!! format_date($activity->Task->TaskBegin) !!}</td>--}}
    {{--<td data-index="TaskEnd">{!! format_date($activity->Task->TaskEnd) !!}</td>--}}
    {{--<td data-index="PriorityID">{!! $activity->Task->Priority->PriorityName !!}</td>--}}
    {{--<td data-index="TaskStatusID">{!! $activity->Task->TaskStatus->TaskStatusName!!}</td>--}}
    {{--<td data-index="AssignedTo">@if(!empty($activity->task->assignee)){!! $activity->task->assignee->FirstName ." ". $activity->task->assignee->LastName!!}@endif</td>--}}
    {{--<td data-index="TaskNote">{!! $activity->Task->TaskNote!!}</td>--}}
    {{--</tr>--}}
    {{--@endforeach--}}
    {{--@endif--}}

    {{--</tbody>--}}
    {{--</table>--}}
    {{--</div>--}}
    {{--</div>--}}
    {{--</div>--}}

    {{--<div class="modal fade" id="add_activity" tabindex="-1" role="add_activity" aria-hidden="true">--}}
    {{--<div class="modal-dialog" style="width: 800px!important;">--}}
    {{--<div class="modal-content">--}}
    {{--</div>--}}
    {{--</div>--}}
    {{--</div>--}}
    {{--<div class="cloneTable" style="display: none">--}}
    {{--<table>--}}
    {{--<tbody>--}}
    {{--<tr data-recordid="" class="activity_row" >--}}
    {{--<td data-index="TaskName"></td>--}}
    {{--<td data-index="TaskBegin"></td>--}}
    {{--<td data-index="TaskEnd"></td>--}}
    {{--<td data-index="PriorityID"></td>--}}
    {{--<td data-index="TaskStatusID"></td>--}}
    {{--<td data-index="AssignedTo"></td>--}}
    {{--<td data-index="TaskNote"></td>--}}
    {{--</tr>--}}
    {{--</tbody>--}}
    {{--</table>--}}
{{--</div>--}}