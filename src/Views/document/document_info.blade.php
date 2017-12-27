<div class="control-group apply_table_operation">
    <div class="portlet light">
        <div class="tools text-right">
            @can('seminarPlanner.documentAdd')
                <span><a href="{{--{!! url(LaravelLocalization::getCurrentLocale() . "/" ."document/0") !!}--}}"
                         id="add_document_btn"  class="btn tooltips btn-circle btn-icon-only btn-default add_document tab_plus_btn" data-original-title="{!! Html::customTrans("events.TooltipsAddDoc") !!}"><i
                                class="fa fa-plus"></i></a></span>
            @endcan
            <a href="javascript:;"  style="display: none;"
               class="btn btn-circle btn-icon-only btn-default tooltips close-btn-document" data-original-title="{!! Html::customTrans("events.TooltipsClose") !!}">
                <i class="fa fa-close"></i></a>

        </div>
        <div id="tab_document_info">
            <div class="document-list">
                <ul>
                    @if(isset($document_data))
                        @foreach ($document_data->eventDocument as $document)
                            @if(!empty($document->document->DocumentID))
                                <?php $icon = iconBasedOnExtension($document->document->DocumentExtension); ?>
                                <li>
                                    <div class="document-blcok" data-id="{!! $document->document->DocumentID !!}"
                                         id="document_row_{!! $document->document->DocumentID !!}">
                                        <a href="{{ URL::to('seminar-planner/download_document/' . $document->document->DocumentID) }}"
                                           id="document_download_{{$document->document->DocumentID}}"
                                           class="hidden document_download">Download</a>
                                        <h3 data-index="DocumentTitle">{!! $document->document->DocumentTitle !!}</h3>
                                        <strong data-index="DocumentCategoryID">@if(!empty($document->document->documentCategory->DocumentCategoryName)) {!! (LaravelLocalization::getCurrentLocale() == 'en') ? $document->document->documentCategory->DocumentCategoryName : (!empty($document->document->documentCategory->DocumentCategoryNameDE) ? $document->document->documentCategory->DocumentCategoryNameDE : $document->document->documentCategory->DocumentCategoryName)  !!} @endif</strong>
                                        <div class="file-type" data-index="DocumentFileType"><i class="fa {!! $icon !!}"></i></div>
                                        <p data-index="DocumentUpdated">{!! format_date($document->document->Updated)!!} {!! format_time($document->document->Updated)!!}</p>
                                        <p data-index="DocumentSizeMB">{!! $document->document->DocumentSizeMB!!} MB</p>
                                        {{--<input type="checkbox" id="editDocumentId{!! $participant_document->document->DocumentID !!}" name="editDocumentId" value="{!! $participant_document->document->DocumentID !!}" class="chk_document">
                                        <label for="editDocumentId{!! $participant_document->document->DocumentID !!}"></label>--}}
                                    </div>
                                    <figcaption class="view-caption">
                                        @can('seminarPlanner.documentDelete')
                                            <a href="#" class="btn btn-circle btn-icon-only tooltips btn-default  delete_document"
                                               data-id="{!! $document->document->DocumentID !!}" data-original-title="{!! Html::customTrans("events.TooltipsDeleteDoc") !!}"> <i
                                                        class="fa fa-trash"></i> </a>
                                        @endcan
                                        @can('seminarPlanner.documentEdit')
                                            <a href="#" filename="{!! $document->document->DocumentFileName !!}"   filesize="{!! $document->document->DocumentSizeMB !!}" class="btn tooltips btn-circle btn-icon-only btn-default  edit_document"
                                               data-id="{!! $document->document->DocumentID !!}" data-original-title="{!! Html::customTrans("events.TooltipsEditDoc") !!}"> <i
                                                        class="fa fa-pencil"></i> </a>
                                        @endcan
                                    </figcaption>
                                </li>
                            @endif
                        @endforeach
                    @endif

                </ul>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="add_document" tabindex="-1" role="add_document" aria-hidden="true">
    <div class="modal-dialog" style="">
        <div class="modal-content">
        </div>
    </div>
</div>

<div class="cloneTable" style="display: none">
    <table>
        <tbody>
        <tr data-recordid="" class="document_row">
            <td data-index="DocumentTitle"></td>
            <td data-index="DocumentCategoryID"></td>
            <td data-index="DocumentDescription"></td>
            <td data-index="DocumentExtension"></td>
            <td data-index="DocumentSizeMB"></td>
            <td data-index="DocumentFileName"></td>
        </tr>
        </tbody>
    </table>
</div>

<li class="clone_document" style="display:none;">
    <div class="document-blcok" data-id="">
        <a href="" id="" class="hidden document_download">Download</a>
        <h3 data-index="DocumentTitle"></h3>
        <strong data-index="DocumentCategoryID"></strong>
        <div class="file-type" data-index="DocumentFileType"><i class=""></i></div>
        <p data-index="DocumentUpdated"></p>
        <p data-index="DocumentSizeMB"></p>
    </div>
    <figcaption class="view-caption">
        <a href="#" class="btn tooltips btn-circle btn-icon-only btn-default close-btn delete_document" data-id="" data-original-title="{!! Html::customTrans("events.TooltipsDeleteTask") !!}"> <i
                    class="fa fa-trash"></i> </a>
        <a href="#" class="btn btn-circle tooltips btn-icon-only btn-default close-btn edit_document" data-id="" data-original-title="{!! Html::customTrans("events.TooltipsEditDoc") !!}"> <i
                    class="fa fa-pencil"></i> </a>
    </figcaption>
</li>

<div class="col-md-12">
    <div class="document_form" id="tab_document_form" style="display: none">


    </div>
</div>