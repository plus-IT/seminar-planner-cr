<script>
    var weekendConsider = '<?php echo !empty($seminar_days_new->consider_seminar_days) ? $seminar_days_new->consider_seminar_days : "" ?>';
    var holidaysArray = '<?php echo !empty($holidays) ? json_encode($holidays) : ""; ?>';
    var seminarPlannerTrans = <?php echo json_encode($translations);?>;
</script>
@extends("layouts.master")
@push("styles")
<link href="{!! asset('css/contextMenu.css')!!}" rel="stylesheet" type="text/css"/>
<link href="{!! asset('css/seminar_planner/seminar_planner.css')!!}" rel="stylesheet" type="text/css"/>
<style>
    #cke_content_editor, #cke_target_group_editor, #cke_overview_editor, #cke_requirements_editor {
        z-index: 115000 !important;
        display: block !important;
    }
</style>
@endpush
@section("content")
    <div id="seminarPlannerController" class="row contact-page seminarPlanner">
        <div class="col-md-12 ">
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet box blue" id="form_wizard_1">
                        <input type="hidden" value="{!! isset($id) ? $id : '' !!}" class="task_planned_event"
                               name="task_planned_event">
                        <input type="hidden" value="{!! isset($currentDate) ? $currentDate : '' !!}" class="currentDate"
                               name="currentDate">
                        <!--
                                                <div class="portlet-title">
                                                    <div class="caption">
                                                        <i class="fa fa-gift"></i> Seminar Planner - <span class="step-title">
                                                        Step 1 of 4 </span>
                                                    </div>
                                                    <div class="tools hidden-xs">
                                                        <a href="javascript:;" class="collapse">
                                                        </a>
                                                        <a href="#portlet-config" data-toggle="modal" class="config">
                                                        </a>
                                                        <a href="javascript:;" class="reload">
                                                        </a>
                                                        <a href="javascript:;" class="remove">
                                                        </a>
                                                    </div>
                                                </div>
                        -->
                        <div class="portlet-body ">
                            <form action="#" class="" method="POST">
                                <div class="form-wizard">
                                    <div class="form-body">
                                        <ul class="nav nav-pills nav-justified steps">
                                            <li>
                                                <a href="#tab1" data-toggle="tab" class="step">
												<span class="number">
												1 </span>
                                                    <span class="desc">
												{!! Html::customTrans("seminarPlanner.select_seminar") !!}</span>
                                                    <!--<i class="fa fa-check"></i>-->

                                                </a>
                                            </li>
                                            <li>
                                                <a href="#tab2" data-toggle="tab" class="step">
												<span class="number">
												2 </span>
                                                    <span class="desc">
												{!! Html::customTrans("seminarPlanner.plan_seminar") !!}</span>
                                                    <!--<i class="fa fa-check"></i>-->
                                                </a>
                                            </li>

                                        </ul>
                                        <div id="bar" class="progress progress-striped" role="progressbar">
                                            <div class="progress-bar progress-bar-success">
                                            </div>
                                        </div>
                                        <div class="tab-content">
                                            <div class="alert alert-danger display-none">
                                                <button class="close" data-dismiss="alert"></button>
                                                You have some form errors. Please check below.
                                            </div>
                                            <div class="alert alert-success display-none">
                                                <button class="close" data-dismiss="alert"></button>
                                                Your form validation is successful!
                                            </div>
                                            <div class="tab-pane active" id="tab1">
                                                @include('seminar_planner.seminar_select')
                                            </div>
                                            <div class="tab-pane" id="tab2">
                                                @include('seminar_planner.seminar_plan')
                                            </div>

                                        </div>
                                    </div>
                                    <div class="form-actions splanningperiodbottom">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <a href="javascript:;" class="btn default button-previous" style="float: right;">
                                                    <i class="m-icon-swapleft m-icon-white" ></i> {!! Html::customTrans("seminarPlanner.back") !!}</a>
                                                <a href="javascript:;" class="btn blue button-next pull-right">
                                                    {!! Html::customTrans("seminarPlanner.continue") !!} <i class="m-icon-swapright m-icon-white"></i>
                                                </a>
                                                {{--<a href="javascript:;" class="btn green button-submit">--}}
                                                {{--Submit <i class="m-icon-swapright m-icon-white"></i>--}}
                                                {{--</a>--}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END PAGE CONTENT-->
        </div>
    </div>

@endsection
@section("footer-script")
    <script src="{!! asset('js/seminar_planner/jquery.tmpl.min.js')!!}" type="text/javascript"></script>
    <script src="{!! asset('global/plugins/fullcalendar/lib/moment.min.js') !!}" type="text/javascript"></script>
    <script src="{!! asset('global/plugins/fullcalendar/fullcalendar.js') !!}" type="text/javascript"></script>
    <script src="{!! asset('global/plugins/fullcalendar/lang/de.js') !!}" type="text/javascript"></script>
    <script type="text/javascript"
            src="{!! asset('js/seminar_planner/jquery.bootstrap.wizard.min.js')!!}"></script>

    <script src="{!! asset('js/seminar_planner/planner-form-wizard.js')!!}"></script>
    <script src="{!! asset('js/seminar_planner/seminar_filter_calendar.js')!!}"></script>
    <script src="{!! asset('js/seminar_planner/seminar_planner.js')!!}"></script>
    <script src="{!! asset('js/seminar_planner/plannedactivity.js')!!}"></script>

    <script src="{!! asset('js/seminar_planner/document.js')!!}"></script>

    <script src="{!! asset('js/events/budget.js') !!}" type="text/javascript"></script>
    <script src="{!! asset('js/seminar_planner/allocation_settings.js') !!}" type="text/javascript"></script>
@endsection


