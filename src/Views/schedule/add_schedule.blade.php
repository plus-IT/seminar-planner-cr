<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
    <div class="input-group">
        <label class="control-label text-label">{!! Html::customTrans('label.schedule') !!}</label>
    </div>
</div>

<div class="modal-body schedule_form">

    {!!  Form::open(array('url' => LaravelLocalization::getCurrentLocale()."/". 'schedule','method' =>"post",'class'=>'form-row-seperated validate_form add_schedule_form',"autocomplete"=>'off')) !!}
    <div class="alert alert-danger display-hide custom-alert">
        <button class="close" data-close="alert"></button>
        {!! Html::customTrans("general.error_msg") !!}
    </div>
    <input type="hidden" name="ScheduleID" value="{!! (!empty($schedule_data->id)) ? $schedule_data->id : "" !!}">

    <div class="row">
        <div class="col-md-12">

            <div class="row date_container">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-md-4 control-label">{!! Html::customTrans("label.beginDate") !!} *</label>

                        <div class="col-md-8">
                            <input class="form-control form-control-inline schedule-begin required"
                                   size="16" type="text" name="begin"
                                   value="@if(!empty($schedule_data->begin)){!! format_date($schedule_data->begin) !!}@endif"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-4 control-label">{!! Html::customTrans("label.endDate") !!} *</label>

                        <div class="col-md-8"><input
                                    class="form-control form-control-inline schedule-end required" size="16"
                                    type="text" name="end"
                                    value="@if (!empty($schedule_data->end)){!! format_date($schedule_data->end) !!}@endif"/>
                        </div>
                    </div>

                </div>

            </div>

            <div class="form-group">
                <label class="control-label col-md-2">{!! Html::customTrans("label.trainer") !!} </label>

                {{--<div class="col-md-4">--}}
                    {{--<select class="bs-select form-control" name="trainer">--}}
                        {{--<optgroup label="{!! Html::customTrans("label.preferredTrainer") !!}">--}}
                            {{--@if(!empty($event_preferred_trainer))--}}
                                {{--@foreach($event_preferred_trainer as $preferred_trainer)--}}
                                    {{--<option value="{!! $preferred_trainer->id !!}">{!! $preferred_trainer->text !!}</option>--}}
                                {{--@endforeach--}}
                            {{--@endif--}}
                        {{--</optgroup>--}}
                        {{--<optgroup label="{!! Html::customTrans("label.availableTrainer") !!}">--}}
                            {{--@if(!empty($all_trainer))--}}
                                {{--@foreach($all_trainer as $trainer)--}}
                                    {{--<option value="{!! $trainer->id !!}">{!! $trainer->text !!}</option>--}}
                                {{--@endforeach--}}
                            {{--@endif--}}

                        {{--</optgroup>--}}
                    {{--</select>--}}
                {{--</div>--}}
                <div class="col-md-8">
                    <input type="hidden" id="scheduleTrainers" name="trainer" value="{!! (!empty($event_data->trainer)) ? $event_data->trainer : ""  !!}" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-md-2">{!! Html::customTrans("label.preferredTrainer") !!} </label>
                <div class="col-md-4">
                    <select class="bs-select form-control select2 required" name="organization_id">
                        <option ></option>
                        @if(!empty($all_organization))
                            @foreach($all_organization as $val)
                                <option value="{!! $val->OrganizationID !!}" {!! (!empty($schedule_data->organization_id)) ? (($schedule_data->organization_id == $val->OrganizationID) ? 'selected' : '') : '' !!}>{!! $val->CustomerName !!}</option>
                            @endforeach
                        @endif

                    </select>
                </div>

            </div>


            <div class="form-group">
                <label class="col-md-2 control-label input-medium">{!! Html::customTrans("label.description") !!}</label>
                <div class="col-md-8">
                        <textarea class="form-control required" name="description" rows="4" cols="86"
                                  placeholder="{!! Html::customTrans("label.description") !!}">@if (!empty($schedule_data->description)){!!$schedule_data->description !!} @endif</textarea>
                </div>
            </div>



        </div>
    </div>
    {!!  Form::close()  !!}
</div>

<div class="modal-footer">
    <button type="button" class="btn default cancel-popup" data-dismiss="modal">{!! Html::customTrans("general.cancel") !!}</button>
    <button type="button" class="btn default save_schedule green">{!! Html::customTrans("general.save") !!}</button>
</div>
<script>
    //form_validate(".add_schedule_form");
    initDatePicker();
    initSelectDropDown();
</script>