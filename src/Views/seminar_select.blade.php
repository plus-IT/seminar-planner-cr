<!-- BEGIN PAGE CONTENT-->
<?php $sort_by = "FirstName";
$op_sort_order = "ASC";
$limit = 5;
?>
<div class="row splanningperiod">
    <div class="col-md-12">
        <div class="col-md-2">
            <label class="tooltips" data-container="body" data-original-title="{!! Html::customTrans("seminarPlanner.planningPeriodToolTip") !!}">
                {!! Html::customTrans("seminarPlanner.planning_period") !!}</label>
        </div>
        <div class="col-md-8">
            <div class="col-md-5">
               <label for="startOfDate">{!! Html::customTrans("seminarPlanner.startOfDate") !!}</label>
                <input class="form-control date-picker getPlanPeriod"
                       size="16" type="text"
                       name="startOfDate" id="startOfDate"
                       value=""
                />
            </div>
            <div class="col-md-2 green-arrow">
                <img src="{!! asset('images/arrow.png') !!}" alt="grren-arrow" title="">
            </div>
            <div class="col-md-5">
               <label for="endOfDate">{!! Html::customTrans("seminarPlanner.endOfDate") !!}</label>
                <input class="form-control date-picker getPlanPeriod pull-left"
                       size="16" type="text"
                       name="endOfDate" id="endOfDate"
                       value=""
                />
            </div>
        </div>
        <div class="col-md-2 no-padding">
            <a href="javascript:void(0)" class="exportplannedSeminar btn btn-circle tooltips pull-right btn-block"
               data-container="body" data-original-title="{!! Html::customTrans("seminarPlanner.export_to_xml") !!}" style="padding: 9px 0px;width: 117%">
                <i class="fa fa-file-excel-o"></i>
                {!! Html::customTrans("seminarPlanner.exportPlannedSeminarText")  !!}
            </a>
        </div>

    </div>
</div>
<div class="row splanningperiodbot">
   
    <div class="col-md-12">
        <div class="portlet-body table-list" id="participant-list">
            
            <div class="contact-list ">
                 <div class="row splanningperiodtopbar">
                    <div class="col-md-5">
                        <div class="input-icon right form-group">
                            <i class="fa fa-search search-button"></i>
                            <input type="text"
                                   placeholder="{!! Html::customTrans("general.search") !!}"
                                   class="form-control seminar-search-input">
                        </div>
                    </div>
                <div class="input-group search-user col-md-7 ">

                         <!-- <a href="javascript:void(0)" class="open_modal_selected_seminar btn ">
                            <i class="torchicon"></i>
                        </a>
                         -->
                    <button type="button" class="seminar-search"
                            style="opacity: 0;display: none"
                            id="seminar-search"></button>
                    <div class="groupbtn">
                        <button class="open_modal_selected_seminar btn tooltips"
                                type="button"  data-original-title="{!! Html::customTrans("seminarPlanner.showAllSelectedEvents") !!}">
                            <i class="torchicon"></i>
                        </button>
                        <!-- <button class="btn">
                            <i class="sorticon"></i>
                        </button>
                        <button class="btn">
                            <i class="listicon"></i>
                        </button>
                        <button class="btn">
                            <i class="refreshicon"></i>
                        </button> -->

                    <!-- <div class="input-group-btn btn-group"> -->
                      <span>
                        <button class="btn tooltips filter_trigger" data-ele_id="sort_by_list"
                                type="button" data-original-title="{!! Html::customTrans("general.generalSortby") !!}">
                            <i class="sorticon"  ></i></button>
                        <ul id="sort_by_list" class="dropdown-menu pull-right sort-wrapper margin-right-0 filter_tab"
                            role="menu1" aria-labelledby="menu1">
                            <li role="presentation" class="active" style="display: none;">
                                <a role="menuitem" class="sort-fields" tabindex="-1"
                                   sort-by="event_name"
                                   sort-order="ASC"
                                   href="javascript:;">{!! Html::customTrans("seminarPlanner.event_name") !!}</a>
                            </li>
                             <li role="presentation">
                                <a role="menuitem" class="sort-fields" tabindex="-1"
                                   sort-by="event_category_name"
                                   sort-order="ASC"
                                   href="javascript:;">{!! Html::customTrans("seminarPlanner.seminar_category") !!}</a>
                            </li>
                            <li role="presentation" class="">
                                <a role="menuitem" class="sort-fields" tabindex="-1"
                                   sort-by="total_seminars"
                                   sort-order="ASC"
                                   href="javascript:;">{!! Html::customTrans("seminarPlanner.count_of_seminar") !!}</a>
                            </li>
                            <li role="presentation" class="">
                                <a role="menuitem" class="sort-fields" tabindex="-1"
                                   sort-by="total_participants"
                                   sort-order="ASC"
                                   href="javascript:;">{!! Html::customTrans("seminarPlanner.total_participant_pro") !!}</a>
                            </li>
                            <li role="presentation" class="">
                                <a role="menuitem" class="sort-fields" tabindex="-1"
                                   sort-by="total_revenue"
                                   sort-order="ASC"
                                   href="javascript:;">{!! Html::customTrans("seminarPlanner.total_revenue_pro") !!}</a>
                            </li>

                        </ul>
                      </span>
                         <button class="btn tooltips filter_trigger"
                                type="button"
                                data-ele_id="personFilter" data-original-title="{!! Html::customTrans("general.filter") !!}">
                            <i class="listicon"></i></button>
                    </div>


                    <div class="filter-wrapper dropdown-menu pull-right margin-right-1 filter_tab"
                         id="personFilter" style="display: none ; top: 40px;">
                                                            <span class="filter-close btn btn-icon-only"><i
                                                                        class="fa fa-close"></i></span>
                                                                        
                        
                        <div class="filter-inner">
                            <div class="filter-content">
                                <h4 class="font-bold mr-top-none">{!! Html::customTrans("general.filterBy") !!} </h4>
                                <div class="panel-group accordion " id="accordion2">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <h4 class="panel-title">
                                                <a class="accordion-toggle"
                                                   data-toggle="collapse"
                                                   href="#category"
                                                   data-parent="#accordion2">
                                                    {!! Html::customTrans("seminarPlanner.seminar_category") !!} </a>
                                            </h4>
                                        </div>
                                        <div id="category"
                                             class="panel-collapse collapse">
                                            <form>
                                                <input type="hidden" id="CompanyMainContactID"
                                                       name="CompanyMainContactID"
                                                       class="form-control edited"
                                                       value=""
                                                       tabindex="15">
                                            </form>
                                        </div>

                                        <div class="panel-heading">
                                            <h4 class="panel-title">
                                                <a class="accordion-toggle"
                                                   data-toggle="collapse"
                                                   href="#company"
                                                   data-parent="#accordion2">
                                                    {!! Html::customTrans("seminarPlanner.location") !!} </a>
                                            </h4>
                                        </div>
                                        <div id="company"
                                             class="panel-collapse collapse">
                                            <form>
                                                <input type="hidden" id="SeminarCategoryID" name="SeminarCategoryID"
                                                       class="form-control edited"
                                                       value=""
                                                       tabindex="16">
                                            </form>
                                        </div>
                                        <div class="panel-heading">
                                            <h4 class="panel-title">
                                                <a class="accordion-toggle"
                                                   data-toggle="collapse"
                                                   href="#trainer"
                                                   data-parent="#accordion2">
                                                    {!! Html::customTrans("seminarPlanner.trainer") !!} </a>
                                            </h4>
                                        </div>
                                        <div id="trainer"
                                             class="panel-collapse collapse">
                                            <form>
                                                <input type="hidden" id="SeminarTrainerId" name="SeminarTrainerId"
                                                       class="form-control edited"
                                                       value=""
                                                       tabindex="16">
                                            </form>
                                        </div>
                                        <div class="panel-heading">
                                            <h4 class="panel-title">
                                                <a class="accordion-toggle"
                                                   data-toggle="collapse"
                                                   href="#seminar_planned"
                                                   data-parent="#accordion2">
                                                    {!! Html::customTrans("seminarPlanner.seminar_planner_type") !!} </a>
                                            </h4>
                                        </div>
                                        <div id="seminar_planned"
                                             class="panel-collapse collapse">
                                            <form>
                                                <select name="seminar_planner_type"
                                                        class="seminar_planner_type form-control">
                                                        <option value="">{!! Html::customTrans("seminarPlanner.select_seminar_planner_type") !!}</option>
                                                    <option value="1">{!! Html::customTrans("seminarPlanner.seminar_planned") !!}</option>
                                                    <option value="0">{!! Html::customTrans("seminarPlanner.seminar_unplanned") !!}</option>
                                                </select>
                                            </form>
                                        </div>

                                        <div class="panel-heading">
                                            <h4 class="panel-title">
                                                <a class="accordion-toggle"
                                                   data-toggle="collapse"
                                                   href="#SeminarPlannedBy"
                                                   data-parent="#accordion2">
                                                    {!! Html::customTrans("seminarPlanner.seminar_plannedBy") !!} </a>
                                            </h4>
                                        </div>
                                        <div id="SeminarPlannedBy"
                                             class="panel-collapse collapse">
                                            <form>
                                                <input type="hidden" id="SeminarPlannedBy" name="SeminarPlannedBy"
                                                       class="form-control edited"
                                                       value=""
                                                       tabindex="16">
                                            </form>
                                        </div>
                                        <div class="clearfix">&nbsp;</div>
                                        <div class="filter-btn-group pull-right">
                                            <input type="button"
                                                   name="get_filter_reset"
                                                   class="btn btn-default get_filter_reset"
                                                   value="{!! Html::customTrans("general.reset") !!}  ">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>

<input type="hidden" name="selected_seminar" class="selected_seminar" id="selected_seminar" value="">
<div class="row splanningperiodfulltable">
    <div id="infinite_scroll" style="" data-position="top" data-step="2"
         data-intro="{!! Html::customTrans("intro.participantList") !!}">
        <div class="col-md-12 seminar_list_table">
            {{--@include('seminar_planner.seminar_list_table')--}}
        </div>
    </div>
</div>
<div id="myModal" class="modal fade get_seminar_selected" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <div class="input-group">
                    <label class="control-label text-label">{!! Html::customTrans('seminarPlanner.selected_seminar') !!}</label>
                </div>
            </div>

            <div class="modal-body seminar_selected_form">


            </div>

            <div class="modal-footer">
                <button type="button" class="btn default cancel-popup"
                        data-dismiss="modal">{!! Html::customTrans("general.cancel") !!}</button>

            </div>
        </div>
    </div>
</div>
<div class="modal fade " id="export_xml" role="dialog">
    <div class="modal-dialog model_dialog_center" style="width: 70%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <div class="input-group">
                    <label class="control-label text-label">{!! Html::customTrans('seminarPlanner.export_to_xml') !!}</label>
                </div>
            </div>
            <div class="modal-body" style="">
                <form class="export_form_seminar">
                    <div class="dl-horizontal export_seminar_data">

                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button"
                        class="btn default export_to_xml">{!! Html::customTrans("seminarPlanner.exportBtn") !!}</button>
                <button type="button" class="btn default cancel-popup"
                        data-dismiss="modal">{!! Html::customTrans("general.cancel") !!}</button>
            </div>
        </div>
    </div>
</div>
