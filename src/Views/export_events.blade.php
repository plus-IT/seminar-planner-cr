{!!  Form::open(['url' => LaravelLocalization::getCurrentLocale()."/". 'seminar-planner/schedule','method' =>"post",'class'=>' form-row-seperated validate_form export_form_seminar',"autocomplete"=>'off']) !!}
    <div class="contact-list" style="margin-bottom: 10px;">
        <div class="row" >
            <div class="col-md-3 export_flter_holder" style="padding-left:16px">
                <div class="col-md-3" style="margin-left:5px;">
                    <label for="startOfDate">{!! Html::customTrans("seminarPlanner.startOfDate") !!}</label>
                </div>
                <div class="col-md-7 pull-right">
                    <input class="form-control date-picker getPlanPeriod"
                           size="16" type="text"
                           name="start_date" id="startOfDate"
                           value=""
                    />
                </div>
            </div>
            <div class="col-md-3 export_flter_holder">
                <div class="col-md-3">
                    <label for="endOfDate">{!! Html::customTrans("seminarPlanner.endOfDate") !!}</label>
                </div>
                <div class="col-md-7">
                    <input class="form-control date-picker getPlanPeriod"
                           size="16" type="text"
                           name="end_date" id="endOfDate"
                           value=""
                    />
                </div>
            </div>
            <div class="col-md-3 export_flter_holder">
                <div class="form-group">
                    <label class="col-md-4 control-label">{!! Html::customTrans("seminarPlanner.eventCategory") !!}</label>
                    <div class="col-md-8">
                        <select name="event_category[]" multiple
                                class="seminarCategoryForExport" style="width:100%;">
                            <option value="">{!! Html::customTrans("general.please_select") !!}</option>
                            @if(!empty($all_event_category))
                                @foreach($all_event_category as $val)
                                    <option value="{!! $val->id !!}"
                                    >{!! $val->event_category_name !!}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-3 export_flter_holder">
                <div class="md-checkbox">
                    <input type="checkbox" id="groupSeminars" class="md-check"
                           name="groupSeminars"  >
                    <label for="groupSeminars">
                        <span></span>
                        <span class="check"></span>
                        <span class="box"></span>
                        {!! Html::customTrans("seminarPlanner.groupSeminarForExport") !!}
                    </label>
                </div>
            </div>
        </div>
    </div>
    <h3>{!! Html::customTrans("seminarPlanner.selectColumnsToExportHeading")  !!}</h3>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th><input type="checkbox" class="multi_check"></th>
            <th>{!! Html::customTrans('seminarPlanner.select_column') !!}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($export_array as $key=>$val)
            <tr>
                <td><input type="checkbox" name="single_check[]" class="single_check" value="{!! $val !!}"></td>
                <td>{!! Html::customTrans('seminarPlanner.'.$key) !!} </td>
            </tr>
        @endforeach
        </tbody>
    </table>
{!! Form::close() !!}