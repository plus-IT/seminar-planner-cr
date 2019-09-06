<div class="portlet box">
    <div class="row-height">
        <div class="row">
            <div class="col-md-4">

                <form class="online_portal_details">

                <div class="">
                    <input type="checkbox" id="confirm_seminar" class="seminar_details_for_portal md-check" value="confirm"
                           {!! !empty($event_data->event_status) && $event_data->event_status=='confirm'?'checked':'' !!}
                           name="event_status">
                    <label for="confirm_seminar">
                        <span></span>
                        <span class="check"></span>
                        <span class="box"></span>
                        {!! Html::customTrans("events.confirmSeminarChkBox") !!}
                    </label>
                </div>
                
                <div class="">
                    <input type="checkbox" id="is_deploy_internet" class="seminar_details_for_portal md-check" value="1"
                           {!! !empty($event_data->is_deploy_internet)?'checked':'' !!}
                           name="is_deploy_internet">
                    <label for="is_deploy_internet">
                        <span></span>
                        <span class="check"></span>
                        <span class="box"></span>
                        {!! Html::customTrans("events.deployOnInternetChkBox") !!}
                    </label>
                </div>
                
                @yield('seminarPlannerAdditionalFields')
                    <div class="if_deploy" style="{!! !empty($event_data->is_deploy_internet)?'display:block':'display:none' !!}">
                        @yield('seminarPlannerBenderAdditionRoleFields')
                        
                        <input type="checkbox" id="show_vacant_seats" class="seminar_details_for_portal md-check" value="{!! !empty($event_data->is_deploy_internet)?'1':'0' !!}"
                               {!! !empty($event_data->is_deploy_internet)?(!empty($event_data->show_vacant_seats)?'checked':'' ):'' !!}
                               name="show_vacant_seats">
                        <label for="show_vacant_seats">
                            <span></span>
                            <span class="check"></span>
                            <span class="box"></span>
                            {!! Html::customTrans("events.show_vacant_seats") !!}
                        </label>
                    </div>
                    @yield('setMeOnWaitingList')
                </form>
				<div class="form-group form-md-line-input form-md-floating-label ">

					<input tabindex="11" class="form-control  {!! !empty($event_data->action_number) ? 'edited' : ''  !!}"
						   size="16" type="text" style="margin-right: 7px" name="action_number" id="action_number"
						   value="@if(!empty($event_data->action_number)){!! $event_data->action_number !!}@endif"
						   />
					<label for="action_number">{!! Html::customTrans("event.action_number") !!}</label>
				</div>
                <div class="assign_to_other_div form-group lineManager manager-auto-select">
                       <label class="col-md-4 control-label">{!! trans("events.event_region") !!} </label>
                        {!! Form::select2ajax('event_region',!empty($event_data->event_region)?$event_data->event_region:'','table-group-action-input required save_event_region form-control', trans("general.select") ,'event/getAllRBRegions',!empty($event_data->event_region)?$event_data->event_region:'',1,1)!!}
                </div>
                <div class="">
                    <div class="form-group form-md-line-input form-md-floating-label  required">
                        <input type="text" placeholder="" name="planned_trainer" id="planned_trainer" readonly="readonly"
                               class="form-control {!! (!empty($person_data->trainer_name)) ? 'edited' :'' !!}"
                               value="{!! isset($event_data) && !empty($event_data) ? $event_data->personList : "" !!}">
                        <label for="planned_trainer">{!! Html::customTrans("seminarPlanner.planned_trainer") !!} </label>
                    </div>
                </div>
                <div class="">
                    <div class="form-group form-md-line-input form-md-floating-label  required">
                        <input type="text" placeholder="" name="planned_location" id="planned_location" readonly="readonly"
                               class="form-control {!! (!empty($location->schedule->scheduleLocation)) ? 'edited' :'' !!}"
                               value="{!! isset($event_data) && !empty($event_data) ? $event_data->location : "" !!}">
                        <label for="planned_location">{!! Html::customTrans("seminarPlanner.planned_location") !!} </label>
                    </div>
                </div>
                  <input type="hidden" placeholder="" name="totalAttendees" id="totalAttendees"
                       value="{!! isset($event_data) && !empty($event_data->totalAttendees) ? $event_data->totalAttendees : 1 !!}">
                <div class="">
                    <div class="form-group form-md-line-input form-md-floating-label has-info">
                        <select tabindex="15" name="form_id" id="form_id" alt="Feedback Form"
                                class=" table-group-action-input {!! !empty($event_data->form_id) || !empty(session("setting")->feedback_form_id)  ? 'edited' : '' !!}  form-control">
                            <option value="" @if(!empty($event_data) && $event_data->form_id == 0) selected @endif ></option>
                            @if(!empty($allFeedbackForm) && !empty($allFeedbackForm))
                                @foreach($allFeedbackForm as $val)
                                    <option value="{!! $val->id !!}"
                                            @if(!empty($event_data) && $event_data->form_id == $val->id) selected
                                            @elseif($val->id == session("setting")->feedback_form_id) selected @endif>{!! $val->name !!}</option>
                                @endforeach
                            @endif
                        </select>
                        <label for="feedbackFormId">{!! Html::customTrans("event.selectFeedbackForm") !!}</label>
                    </div>
                    <div class="form-group form-md-line-input form-md-floating-label  required">
                        <input type="number" placeholder="" name="min_registration" id="min_registration"
                               class="form-control {!! (!empty($event_data->min_registration)) ? 'edited' :'' !!}"
                               value="{!! isset($event_data) && !empty($event_data) ? $event_data->min_registration : "" !!}">
                        <label for="min_registration">{!! Html::customTrans("seminarPlanner.min_registration") !!} </label>
                    </div>
                    <div class="form-group form-md-line-input form-md-floating-label  required">
                        <input type="number" placeholder="" name="max_registration" id="max_registration"
                               {!! !empty($event_data->is_seats_allocated)? ($event_data->is_seats_allocated!=0 ? 'readonly':'') :'' !!}
                               class="form-control {!! (!empty($event_data->max_registration)) ? 'edited' :'' !!}"
                               value="{!! isset($event_data) && !empty($event_data) ? $event_data->max_registration : "" !!}">
                        <label for="max_registration">{!! Html::customTrans("seminarPlanner.max_registration") !!} </label>
                    </div>
                </div>
                @if(Auth::user()->is_super_admin)
                        <div class="form-group form-md-line-input form-md-floating-label ">
                            <input data-index="external_id" type="text" placeholder="" name="external_id" id="external_id"
                                   class="form-control @if(!empty($event_data->external_id )) edited @endif external_id_save"
                                   value="@if(!empty($event_data->external_id )){!! $event_data->external_id !!}@endif">
                            <label for="external_id">{!! Html::customTrans("lookupTable.external_id") !!}</label>

                        </div>
                    @endif
                @yield('cancellationDateSection')
                <div class="">
                    <div class="form-group form-md-line-input form-md-floating-label edited required">
                        <input class="form-control {!! (!empty($event_data->event_startdate)) ? 'edited' :'' !!}"
                               size="16" type="text"
                               name="event_startdate" id="event_startdate" id="event_startdate" readonly="readonly"
                               placeholder="{!! Html::customTrans("events.beginDateFormat") !!}"
                               value="{!! (!empty($event_data->event_startdate)) ? format_date($event_data->event_startdate) :'' !!}"/>
                        <label for="event_startdate">{!! Html::customTrans("seminarPlanner.seminarStartDate") !!}</label>
                    </div>
                </div>
                <div class="">
                    <div class="form-group form-md-line-input form-md-floating-label edited required">
                        <input class="form-control {!! (!empty($event_data->event_enddate)) ? 'edited' :'' !!}"
                               size="16" type="text"
                               name="event_startdate" id="event_enddate" id="event_enddate" readonly="readonly"
                               placeholder="{!! Html::customTrans("events.beginDateFormat") !!}"
                               value="{!! (!empty($event_data->event_enddate)) ? format_date($event_data->event_enddate) :'' !!}"/>
                        <label for="event_enddate">{!! Html::customTrans("seminarPlanner.seminarEndDate") !!}</label>
                    </div>
                </div>
                <div class="">
                   <?php 
                      $reportParams = [
                          'id' => $event_data->id,
                      ];
                      $moduleSlug = 'seminar-planner';
                    ?>
                   @include('reports.module_report_list')
                </div>
            </div>
            @include('seminar_planner.planned_seminar_details')
        </div>
    </div>
</div>
@yield('addScripts')
