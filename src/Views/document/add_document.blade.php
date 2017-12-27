{{--<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
    <div class="input-group">
        <label class="control-label text-label">{!! Html::customTrans('label.document') !!}</label>
    </div>
</div>--}}


<div class="tab_document_form seminardocumenttab">

    <?php $method = !empty($document_data->DocumentID) ? "Patch" : "POST"; ?>
    {!!  Form::open(array('url' => LaravelLocalization::getCurrentLocale()."/". 'document','method' =>$method,'id'=>'document_form','class'=>'document_form  form-row-seperated add_document_form validate_form',"files"=>true,"autocomplete"=>'off')) !!}
    <div class="alert alert-danger display-hide custom-alert">
        <button class="close" data-close="alert"></button>
        {!! Html::customTrans("label.error_msg") !!}
    </div>
    <input type="hidden" name="DocumentID"
           value="@if (!empty($document_data->DocumentID)){!!$document_data->DocumentID !!} @endif">
    <input type="hidden" name="person_id" value="">
    <input type="hidden" name="organization_id" value="">
    <input type="hidden" name="event_id" value="" class="event_id">
    <input type="hidden" name="LocationID" value="">
    <input type="hidden" name="pageType" value="">

    <div class="row">
        <div class="col-md-12 text-cener required">
            <div class="col-md-2"></div>
            <div class="dropzone" id="my-dropzone">
            </div>
            {{--<div class="form-inline">--}}
            {{--<i class="fa fa-cloud-upload float-left upload-icon"></i>--}}
            {{--@if(!empty($document_data->DocumentFileName))--}}
            {{--<div class="form-inline pull-left">--}}
            {{--{!! $document_data->DocumentFileName !!}--}}
            {{--</div>--}}
            {{--@endif--}}
            {{--<input type="file" id="DocumentUpload" class="pull-left" style="margin-left: 20px"--}}
            {{--name="DocumentUpload" {!! empty($document_data->DocumentFileName) ? 'required' : '' !!} />--}}
            {{--</div>--}}
        </div>

        <div class="clearfix">&nbsp;</div>

        <div class="col-md-12">

            <div class="form-group form-md-line-input form-md-floating-label required">
                <input type="text" name="DocumentTitle" id="DocumentTitle"
                       class="form-control @if(!empty($document_data->DocumentTitle))edited @endif required"
                       value="@if(!empty($document_data->DocumentTitle)){!! $document_data->DocumentTitle !!} @endif"
                       maxlength="30">
                <label for="DocumentTitle">{!! Html::customTrans("general.title") !!}</label>
            </div>

            <div class="form-group required">
                <div class="form-group form-md-line-input form-md-floating-label has-info required">
                    <select id="DocumentCategoryID" name="DocumentCategoryID" alt="Documentcategory"
                            template="documentcategory" tableType="documentcategory" editView="documentcategory"
                            class="addlookup form-control edited select2 required">
                        <option value=""></option>
                        @if(!empty($all_documentcategory))
                            @foreach($all_documentcategory as $val)
                                <option value="{!! $val->DocumentCategoryID !!}"
                                        @if(!empty($document_data->DocumentCategoryID) && $document_data->DocumentCategoryID == $val->DocumentCategoryID) selected @endif> {!! (LaravelLocalization::getCurrentLocale() == 'en') ? $val->DocumentCategoryName : (!empty($val->DocumentCategoryNameDE) ? $val->DocumentCategoryNameDE : $val->DocumentCategoryName ) !!}</option>
                            @endforeach
                        @endif
                        <option value="createJob">{!! Html::customTrans("general.createNedit") !!}</option>
                    </select>
                    <label for="DocumentCategoryID">{!! Html::customTrans("general.category") !!}</label>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group form-md-line-input">
                            <textarea name="DocumentDescription" id="DocumentDescription"
                                      class="form-control" cols="85" rows="4" >@if(!empty($document_data->DocumentDescription)){!! $document_data->DocumentDescription !!}@endif</textarea>
                        <label for="DocumentDescription">{!! Html::customTrans("general.description") !!}</label>
                    </div>
                </div>
            </div>

            <div class="form-group form-md-line-input form-md-floating-label has-info">
                <label class="control-label">{!! Html::customTrans("Assigned") !!}</label>
                <div class="md-radio-inline">
                    <div class="md-radio">
                        <input id="assignToMeDoc" checked type="radio" name="AssignedTo" class="assignedToMe required"
                               value="1"
                               @if(!empty($document_data->AssignedToUser) && $document_data->AssignedToUser == \Auth::user()->UserID ) checked @endif>
                        <label for="assignToMeDoc">
                            <span></span>
                            <span class="check"></span>
                            <span class="box"></span>
                            {!! Html::customTrans("general.assignMyself") !!}</label>
                    </div>
                    <div class="md-radio">
                        <input id="assignToOtherDoc" type="radio" name="AssignedTo" class="assignedToOther required"
                               value="0"
                               @if((!empty($document_data->AssignedToTeam) || !empty($document_data->AssignedToUser)) && $document_data->AssignedToUser !=  \Auth::user()->UserID ) checked @endif>
                        <label for="assignToOtherDoc">
                            <span></span>
                            <span class="check"></span>
                            <span class="box"></span>
                            {!! Html::customTrans("general.assignToOther") !!}</label>
                    </div>
                </div>

                <div class="assign_to_other_div" style="{!! (!empty($document_data->AssignedToTeam) || !empty($document_data->AssignedToUser)) && $document_data->AssignedToUser !=  \Auth::user()->UserID ? '' : 'display:none;' !!}">
                    <select id="assign_to_other" name="AssignedToOther" class="form-control modal-select2">
                        <option value="">{!! Html::customTrans("general.please_select") !!}</option>
                        @if(!empty($all_agent))

                            @foreach($all_agent as $val)
                                <option value="organization_{!! $val->UserID !!}"
                                        @if(!empty($document_data->AssignedToUser) && $document_data->AssignedToUser == $val->UserID ) selected @endif>{!!$val->FirstName ." ".$val->LastName  !!}</option>
                            @endforeach

                        @endif
                    </select>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group form-md-line-input form-md-floating-label required">
                        <input type="text" name="DocumentFileName" id="DocumentFileName" class="form-control" readonly
                               value="@if(!empty($document_data->DocumentFileName)){!! $document_data->DocumentFileName !!}@endif">
                        <label for="DocumentFileName">{!! Html::customTrans("general.fileName") !!}</label>
                    </div>
                    <div class="form-group form-md-line-input form-md-floating-label required">
                        <input name="Updated" id="Updated" readonly
                               value="{!! !empty($document_data->Updated) ? format_date($document_data->Updated) : format_date(date('Y-m-d'))  !!}"
                               class="form-control form-control-inline" size="16" type="text"/>
                        <label for="Updated">{!! Html::customTrans("general.updated") !!} </label>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group form-md-line-input form-md-floating-label required">
                        <input type="text" name="DocumentSizeMB" id="DocumentSizeMB" class="form-control" readonly
                               value="@if(!empty($document_data->DocumentSizeMB)){!! $document_data->DocumentSizeMB !!}@endif">
                        <label for="DocumentSizeMB">{!! Html::customTrans("general.size") !!} </label>
                    </div>

                    <div class="form-group form-md-line-input form-md-floating-label required">
                        <input name="Created" id="Created" class="form-control form-control-inline "
                               size="16" readonly
                               value="{!! !empty($document_data->Created) ? format_date($document_data->Created) : format_date(date('Y-m-d'))  !!}"
                               type="text"/>
                        <label for="Created">{!! Html::customTrans("general.created") !!}</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group form-md-line-input form-md-floating-label required">
                        <input name="Updated" id="Updated" readonly
                               value="{!! !empty($document_data->created_by) ? $document_data->created_by->FirstName . " " . $document_data->created_by->FirstName  : ""  !!}"
                               class="form-control form-control-inline " size="16" type="text"/>
                        <label for="Updated">{!! Html::customTrans("general.createdBy") !!} </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group form-md-line-input form-md-floating-label required">
                        <input name="Updated" id="Updated" readonly
                               value="{!! !empty($document_data->updated_by) ? $document_data->updated_by->LastName . " " . $document_data->updated_by->LastName : ""  !!}"
                               class="form-control form-control-inline " size="16" type="text"/>
                        <label for="Updated">{!! Html::customTrans("general.updatedBy") !!} </label>
                    </div>
                </div>
            </div>
        </div>

    </div>
    {!!  Form::close()  !!}
</div>

<div class="modal-footer">
    {{-- <button type="button" class="btn default cancel-popup" data-dismiss="modal">{!! Html::customTrans("label.cancel") !!}</button>
     <button type="button" class="btn default save_document green">{!! Html::customTrans("label.save") !!}</button>--}}
</div>


<script>
    $(function () {

    });

    $('.date-picker').datepicker({
        rtl: Metronic.isRTL(),
        orientation: "left",
        autoclose: true,
        language: app_language,
        format: app_date_format_js
    });
    $(".add_document_form").find(".modal-select2").select2();
    form_validate(".add_address_form");
    setTimeout(function () {
        $(".document_form").find(".form-control:first").focus();
    }, 500);
</script>
