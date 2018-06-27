<div class="col-md-8 right-content" id="event-detail">
    <div class="row">
        <div class="col-md-12 col-lg-12">
            <input type="hidden" name="eventID" class="eventID" value="{!! $event_data->id !!}"/>
            <div class="tabbable-custom">

                <ul class="nav nav-tabs custom-tabs new-custom-tabs nav-justified">
                   
                    <li class="active">
                        <a data-toggle="tabajax" data-target="#tab_description" href="#tab_detail">
                            <i class="fa fa-info"></i>
                            <p>{!! Html::customTrans("events.description") !!}</p>
                        </a>
                    </li>
                    @can('seminarPlanner.schedule')
                    <li class="">
                        <a data-toggle="tabajax"
                           href="{!!url(LaravelLocalization::getCurrentLocale() . "/" .'seminar-planner/getschedule/'. $event_data->id)!!}"
                           data-target="#tab_schedule">
                           <i class="fa fa-schedule"></i>
                            <p>{!! Html::customTrans("events.schedule") !!}</p>
                        </a>
                    </li>
                    @endcan
                    @can('seminarPlanner.task')
                    <li class="">
                        <a data-toggle="tabajax"
                           href="{!!url(LaravelLocalization::getCurrentLocale() . "/" .'seminar-planner/tasks/'. $event_data->id)!!}"
                           data-target="#tab_activity">
                           <i class="fa fa-file"></i>
                            <p>{!! Html::customTrans("events.activity") !!}</p>
                        </a>
                    </li>
                    @endcan
                    @can('seminarPlanner.document')
                    <li class="">
                        <a data-toggle="tabajax"
                           href="{!!url(LaravelLocalization::getCurrentLocale() . "/" .'seminar-planner/documents/'. $event_data->id)!!}"
                           data-target="#tab_document">
                           <i class="fa fa-file-text"></i>
                            <p>{!! Html::customTrans("events.document") !!}</p>
                        </a>
                    </li>
                    @endcan
                    @can('seminarPlanner.budget')
                    <li class="">
                        <a data-toggle="tabajax"
                           href="{!!url(LaravelLocalization::getCurrentLocale() . "/" .'seminar-planner/seminar_budget/'. $event_data->id)!!}"
                           data-target="#tab_seminar_budget">
                           <i class="fa fa-photo"></i>
                            <p>{!! Html::customTrans("events.seminar_budget") !!}</p>
                        </a>
                    </li>
                    @endcan
                    @if(Auth::user()->LevelValueID!='')
                    <li class="">
                        <a data-toggle="tabajax"
                           href="{!!url(LaravelLocalization::getCurrentLocale() . "/" .'seminar-planner/seminar_seat_allocation/'. $event_data->id)!!}"
                           data-target="#tab_seminar_seat_allocation">
                           <i class="fa fa-photo"></i>
                            <p>{!! Html::customTrans("events.seminar_seat_allocation") !!}</p>
                        </a>
                    </li>
                    @endif

                </ul>
                <div class="tab-content">
                    <div id="tab_description" class="tab-pane active">
                        @include('seminar_planner.description_detail')
                    </div>
                    @can('seminarPlanner.schedule')
                    <div id="tab_schedule" class="tab-pane">
                    </div>
                    @endcan
                    @can('seminarPlanner.task')
                    <div id="tab_activity" class="tab-pane">
                    </div>
                    @endcan
                    @can('seminarPlanner.document')
                    <div id="tab_document" class="tab-pane">

                    </div>
                    @endcan
                    @can('seminarPlanner.budget')
                    <div id="tab_seminar_budget" class="tab-pane">

                    </div>
                    @endcan
                    @if(Auth::user()->LevelValueID!='')
                    <div id="tab_seminar_seat_allocation" class="tab-pane">
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>