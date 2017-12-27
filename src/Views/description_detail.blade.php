{{--<div class="header">--}}
{{--<a href="#" class="btn btn-circle btn-icon-only btn-default pull-right">--}}
{{--<i class="fa fa-close"></i></a>--}}

{{--</div>--}}

<div class="description_form" style="">
    {!!  Form::open(array('url' => LaravelLocalization::getCurrentLocale()."/". 'event','method' =>"post",'class'=>'form-row-seperated validate_form add_description_form',"autocomplete"=>'off')) !!}
    <div class="alert alert-danger display-hide custom-alert">
        <button class="close" data-close="alert"></button>
        {!! Html::customTrans("general.error_msg") !!}
    </div>

    <input type="hidden" name="id" value="@if (!empty($event_data->id)){!!$event_data->id !!}@endif" class="plannedID">

    <div class="">
        <div class="col-md-12">

            <div class="form-group form-md-line-input form-md-floating-label required">

                <div class="">
                    <label for="addDescriptionButton">{!! Html::customTrans("events.target_group") !!}</label>
                    <div>
                        <a data-toggle="modal" id="addDescriptionButton" data-target="#add_description"></a>
                        <div placeholder="" style="background: #E3EAF3;padding: 5px;border: 1px #ccc solid;max-height: 110px;overflow: auto;min-height: 110px;" id="target_group_editor" contenteditable="true" name="target_group" class="target_group">
                            @if (!empty($event_data->target_group)){!!$event_data->target_group !!}@endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group form-md-line-input form-md-floating-label required">
                <div class="">
                    <label for="addDescriptionButton">{!! Html::customTrans("events.overview") !!}</label>
                    <div>
                        <a data-toggle="modal" id="addDescriptionButton" data-target="#add_description"></a>
                        <div placeholder="" style="background: #E3EAF3;padding: 5px;border: 1px #ccc solid;max-height: 110px;overflow: auto;min-height: 110px;" id="overview_editor" contenteditable="true" name="overview" class="overview">
                            @if (!empty($event_data->overview)){!!$event_data->overview !!}@endif
                        </div>
                    </div>
                </div>

            </div>
            <div class="form-group form-md-line-input form-md-floating-label required">
                <div class="">
                    <label for="addDescriptionButton">{!! Html::customTrans("events.content") !!}</label>
                    <div>
                        <a data-toggle="modal" id="addDescriptionButton" data-target="#add_description"></a>
                        <div placeholder=""style="background: #E3EAF3;padding: 5px;border: 1px #ccc solid;max-height: 110px;overflow: auto;min-height: 110px;" id="content_editor" contenteditable="true" name="content" class="content">
                            @if (!empty($event_data->content)){!!$event_data->content !!}@endif
                        </div>
                    </div>
                </div>

            </div>
            <div class="form-group form-md-line-input form-md-floating-label required">
                <div class="">
                    <label for="addDescriptionButton">{!! Html::customTrans("events.requirements") !!}</label>
                    <div>
                        <a data-toggle="modal" id="addDescriptionButton" data-target="#add_description"></a>
                        <div placeholder="" style="background: #E3EAF3;padding: 5px;border: 1px #ccc solid;max-height: 110px;overflow: auto;min-height: 110px;" id="requirements_editor" contenteditable="true" name="requirements" class="requirements">
                            @if(!empty($event_data->requirements )){!! $event_data->requirements !!}@endif
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
    <input type="hidden" name="requirements" value=""/>
    <input type="hidden" name="content" value=""/>
    <input type="hidden" name="overview" value=""/>
    <input type="hidden" name="target_group" value=""/>
    {!!  Form::close()  !!}
    </div>

    {{--<div class="footer" style="float: right;">--}}
    {{--<button type="button" class="btn default cancel-popup" data-dismiss="modal">{!! Html::customTrans("label.cancel") !!}</button>--}}
    {{--<button type="button" class="btn default save_address green">{!! Html::customTrans("label.save") !!}</button>--}}
    {{--</div>--}}
    <script>
        // form_validate(".add_address_form");
        setTimeout(function () {
            $(".description_form").find(".form-control:first").focus();
//        if ($(".country_dropdown").length > 0) {
//            $(".country_dropdown").trigger("change");
//        }
        }, 500);

        $(".address_close").click(function (e) {

            $("#address_form").hide("slow");
            $("#address_info").show("slow");
            e.preventDefault();

        });
    </script>