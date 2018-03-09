$(document).on("ready", function () {

    // updating the dropzon file size
    Dropzone.prototype.filesize = function(size) {
      var string;
      if (size >= 1024 * 1024 * 1024 * 1024 / 10) {
        size = size / (1024 * 1024 * 1024 * 1024 / 10);
        string = "TiB";
      } else if (size >= 1024 * 1024 * 1024 / 10) {
        size = size / (1024 * 1024 * 1024 / 10);
        string = "GiB";
      } else if (size >= 1024 * 1024 / 10) {
        size = size / (1024 * 1024 / 10);
        string = "MB"; // changing units from MiB to MB (not currect but ...)
      } else if (size >= 1024 / 10) {
        size = size / (1024 / 10);
        string = "KB"; // changing units from KiB to KB (not currect but ...)
      } else {
        size = size * 10;
        string = "b";
      }
      return "<strong>" + (Math.round(size) / 10) + "</strong> " + string;
    };

    var $body = $("body");
    var $initDropZone;
    $body.on("click", ".document_row", function () {
        $(".edit_document").attr("href", $(".edit_document").attr("data-href") + "/" + $(this).attr("data-recordid"));
    });

    $body.on("click", "#tab_document_save_btn,.tab_document_save", function () {
        if (!$(".add_document_form").valid() || $("#DocumentFileName").val() == "" || $("div").hasClass("dz-preview") == false) {
            notify("error", validation_message_file_fields);
            return false;
        }
    });

    $body.on("change", "[name='DocumentUpload']", function () {
        $file = $(this).prop("files")[0];

        console.log($(this).val());
        var document_id = $("[name='DocumentID']").val();
        var createdDate = new Date();
        var updatedDate = new Date();

        if (document_id != "") {
            $("[name='Updated']").val(new Date().format(app_date_format));
        } else {
            $("[name='Created']").val(new Date().format(app_date_format));
            $("[name='Updated']").val(new Date().format(app_date_format));
        }
        $("[name='DocumentFileName']").val($file['name']);
        $("[name='DocumentSizeMB']").val(($file['size'] / (1024 * 1024)).toFixed(2));

    })
    $body.on("click", "#tabs_document_save_btn", function (e) {
        e.preventDefault();
        var url = base_url + "seminar-planner/document";

        if (!$(".add_document_form").valid()) {
            return false;
        }
        var document_id = $("[name='DocumentID']").val();
        if (document_id != "") {
            url += "/" + $("[name='DocumentID']").val();
        }
        $("#document_form").attr("action", url);
        $("#document_form").find("[name='user_id']").val($("[name='userID']").val())
        $(".cancel-popup").trigger("click");
        blockUI(".modal-content");
        var additionalDataByPage = "";
        //alert($("#person_detail_id").text());
        console.log($("#person_detail_id").text());

        if ($("#person_detail_id").text() != '') {
            $("#document_form").find("[name='person_id']").val($("#person_detail_id").text())
            $("#document_form").find("[name='pageType']").val('person');
        } else if ($("#organization_detail_id").text() != '') {
            $("#document_form").find("[name='organization_id']").val($("#organization_detail_id").text())
            $("#document_form").find("[name='pageType']").val('organization');
        } else if ($("#event_detail_id").text() != '') {
            $("#document_form").find("[name='event_id']").val($("#event_detail_id").text())
            $("#document_form").find("[name='pageType']").val('event');
        } else if ($("#location_detail_id").text() != '') {
            $("#document_form").find("[name='LocationID']").val($("#location_detail_id").text())
            $("#document_form").find("[name='pageType']").val('location');
        }

        $("#document_form").ajaxSubmit({
            error: function (request, errordata, errorObject) {
                var errors = request.responseJSON;
                var errorsHtml = '';
                $.each(errors, function (key, value) {
                    if (value[0] == 'validation.unique') {
                        errorsHtml += key + ' is already in database';
                    } else if (value[0] == 'validation.email') {
                        errorsHtml += key + ' is not in proper format';
                    } else {
                        errorsHtml += key + ' is required';
                    }
                });
                unBlockUI(".modal-content");
                notify('error', errorsHtml);
            },
            success: function (data) {
                unBlockUI(".modal-content");
                notify(data.type, data.message);
                if (data.type == "success") {
                    setViewByMode("#tab_document");
                    console.log(document_id);
                    var date = new Date(); // for now
                    var current_time = date.getHours() + ':' + date.getMinutes() + ':' + date.getSeconds();
                    if (document_id == "") {
                        document_array.push(data.id);
                        var clone_document = $("#tab_document .clone_document").clone();
                        clone_document.removeClass("clone_document");
                        clone_document.css("display", "inline-block");
                        clone_document.prependTo(".document-list ul");
                        clone_document.find(".document_download").attr('id', 'document_download_' + data.id);
                        clone_document.find(".document_download").parent().attr('data-id', data.id);
                        clone_document.find(".document_download").parent().attr('id', 'document_row_' + data.id);
                        clone_document.find(".document_download").attr('href', asset_url + '/seminar-planner/download_document/' + data.id);
                        clone_document.find("[data-index='DocumentTitle']").html($("[name='DocumentTitle']").val());
                        clone_document.find("[data-index='DocumentCategoryID']").html($("[name='DocumentCategoryID'] option:selected").text());
                        clone_document.find("[data-index='DocumentUpdated']").html($("[name='Updated']").val() + ' ' + current_time);
                        clone_document.find("[data-index='DocumentSizeMB']").html($("[name='DocumentSizeMB']").val() + ' MB');
                        clone_document.find(".delete_document, .edit_document").attr('data-id', data.id);
                        clone_document.find(".document-blcok").addClass("document_row_" + data.id);
                        $(document).find(".delete_document, .edit_document").css("display", "inline-block");
                        $("#add_document_btn").show();
                        $("#tab_document_edit_btn, #tab_document_delete_btn").hide();
                    }
                    else {
                        var $updated_document_row = $("#document_row_" + document_id);
                        $updated_document_row.find("[data-index='DocumentTitle']").html($('#DocumentTitle').val());
                        $updated_document_row.find("[data-index='DocumentCategoryID']").html($("[name='DocumentCategoryID'] option:selected").text());
                        $updated_document_row.find("[data-index='DocumentUpdated']").html($("[name='Updated']").val() + ' ' + current_time);
                        $updated_document_row.find("[data-index='DocumentSizeMB']").html($("[name='DocumentSizeMB']").val() + ' MB');
                        $('#tab_document_info').find(".delete_document, .edit_document").css("display", "inline-block");
                        $("#add_document_btn").show();
                        $("#tab_document_edit_btn, #tab_document_delete_btn").hide();
                    }
                    $(".document_row.active-hr").find("td").each(function () {
                        if ($(this).attr("data-index")) {
                            $name = $(this).attr("data-index");
                            val = "";
                            if ($("[name='" + $name + "']").is("select")) {
                                val = $("[name='" + $name + "']").find("option:selected").html();
                            } else {
                                val = $("[name='" + $name + "']").val();
                            }
                            $(this).html(val);
                        }
                    })
                    $(".document_row.active-hr").attr("data-recordid", data.id);
                }
            }
        });
    });

    $body.on("change", "input.chk_document", function () {
        $("input.chk_document").removeClass("active_document")
        $('input.chk_document').not(this).prop('checked', false);
        $(this).addClass("active_document")
        // alert("change");
    });

    $body.on("change", "[name='defaultDocument']", function () {
        var $documentID = $(this).parents("tr").attr("data-recordid");
        var url = base_url + "document/set_default_document";
        $.ajax({
            url: url,
            method: "POST",
            data: "DocumentID=" + $documentID + "&PersonID=" + $("[name='personId']").val(),
            beforeSend: function () {
                blockUI(".modal-content");
            },
            success: function (data) {

                unBlockUI(".modal-content");
                notify(data.type, data.message);
            }
        });
    })
    $body.on("click", ".close-btn-document", function (e) {
        setViewByMode("#tab_document");
        $(this).css('display', 'none');
        $("#add_document_btn").show();
    });
    $body.on("click", "#add_document_btn", function (e) {
        //  var href = $(this).attr('href');
        my_drop_zone = 1;
        e.preventDefault();
        $("#add_document_btn").hide();
        $(".close-btn-document").removeAttr('style')
        var documentId = 0;
        //var activityId = $("[name='editActivityId']").val() != "" ? $('[name=editActivityId]').val() : 0;
        var eventID = $(".eventID").val();
        $.ajax({
            url: base_url + 'seminar-planner/document/' + documentId,
            type: 'GET',
            beforeSend: function () {
                blockUI(".modal-content");
            },
            success: function (data) {
                $("#tab_document_form").html(data);
                unBlockUI(".modal-content");
                setViewByMode("#tab_document");
                Dropzone.autoDiscover = false;
                var $initDropZone = new Dropzone("#my-dropzone", {
                    url: base_url + 'seminar-planner/document',
                    autoProcessQueue: false,
                    maxFiles: 1,
                    method: "POST"
                });
                //$("#my-dropzone").dropzone({
                //    'url': base_url + "document",
                //    autoProcessQueue: false,
                //    maxFiles: 1
                //});
                console.log("adfasdasdasdasd",eventID);
                $("#tab_document_form .event_id").val(eventID);
                $("[name='event_id']").val(eventID);
            },

        });
    });

    $body.on("click", ".edit_document", function (e) {
        e.preventDefault();
        my_drop_zone = 1;
        $("#add_document_btn").hide();
        $(".close-btn-document").removeAttr('style')
        var href = $(this).data('id');
        var filename = $(this).attr('filename');
        var filesize = $(this).attr('filesize');
        filesize = filesize * (1024 * 1024);
        $.ajax({
            url: base_url + 'seminar-planner/document/' + href,
            type: 'GET',
            beforeSend: function () {
                blockUI(".modal-content");
            },
            success: function (data) {
                //$("#add_document_btn").hide();
                unBlockUI(".modal-content");
                $("#tab_document_form").html(data);
                setViewByMode("#tab_document");

                Dropzone.autoDiscover = false;
                var myDropzone = new Dropzone("#my-dropzone", {
                    url: base_url + 'seminar-planner/document/' + href,
                    autoProcessQueue: false,
                    maxFiles: 1,
                    method: "POST"
                });
                var mockFile = {
                    name: filename,
                    size: filesize,
                    accepted: true,
                    status: Dropzone.ADDED
                };

                myDropzone.emit("addedfile", mockFile);
                myDropzone.emit("thumbnail", mockFile, asset_url + "/document_upload/" + filename);
                myDropzone.emit('complete', mockFile);
                myDropzone.files.push(mockFile)


                // $("#my-dropzone").dropzone({'url': base_url + 'document/' + href + "document",maxFiles: 1});
            },

        });
    });

    $body.on("click", ".delete_document", function (e) {
        //  var href = $(this).attr('href');
        e.preventDefault();
        var href = $(this).data('id');
        var delete_document_click = $(this);

        bootbox.confirm({
            message: delete_document,
            buttons: {
                'cancel': {
                    label: cancel_button,
                    className: 'btn-default pull-right'
                },
                'confirm': {
                    label: ok_button,
                    className: 'btn-primary pull-right'
                }
            },
            callback: function (response) {
                if (response == true) {
                    $.ajax({
                        url: base_url + 'seminar-planner/document/' + href,
                        type: 'Delete',
                        success: function (data) {
                            notify(data.type, data.message);
                            delete_document_click.parents('li').remove();
                            /*alert("success");
                             setViewByMode("#tab_activity");*/
                        },
                    });
                }
            }
        });


    });

    $body.on("click", ".document-blcok", function (e) {
        e.preventDefault();
        var id=$(".edit_document").data('id');
        var href = base_url+'seminar-planner/download_document/'+id;
        //   alert(href);

        bootbox.confirm({
            message: download_document,
            buttons: {
                'cancel': {
                    label: cancel_button,
                    className: 'btn-default pull-right'
                },
                'confirm': {
                    label: ok_button,
                    className: 'btn-primary pull-right'
                }
            },
            callback: function (response) {
                if (response == true) {
                    window.location.href = href;
                }
            }
        });


    });

});