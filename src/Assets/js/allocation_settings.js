$.fn.dataTable.ext.order['dom-text-numeric'] = function (settings, col) {
    return this.api().column(col, {order: 'index'}).nodes().map(function (td, i) {
        return $('input', td).val() * 1;
    });
}
$(document).ready(function () {
    $body = $("body");
    $body.on('change', '.allocation_seat_total', function (e) {
        var levelID = $(this).attr('id');
        var eventID = $(".eventID").val();
        var allocatedSeat = $(this).val();
        var organization = $(this).attr('organization');
        var max_participants = parseFloat($(".max_participants").text());
        var total_free_seats = parseFloat($(".total_free_seats").text());
        var total_max_participants = max_participants + total_free_seats;
        var sum = 0;
        var fee_seat_count = 0;
        var old_val = $(this).attr('seatallocated');
        var $me = $(this);
        var is_free_seat = 0;

        $('.allocation_seat_total').each(function () {
            console.log("max participants", $(this).val());
            if ($(this).val() != '') {
                sum += parseFloat($(this).val());  // Or this.innerHTML, this.innerText
            }
        });
        console.log("sum sum", sum);
        if (sum > total_max_participants) {
            notify('error', ' you can not assign seats more then maximum seats allocated');
            $(this).val(old_val);
            return 'false';
        }
        if (sum > max_participants) {
            is_free_seat = 1;
            fee_seat_count = Math.max(allocatedSeat - old_val, 0);
        }
        $.ajax({
            url: base_url + 'seminar-planner/allocateSeat/details/' + eventID + '/' + levelID,
            method: 'post',
            data: {
                'allocatedSeat': allocatedSeat,
                'organization': organization,
                'is_free_seat': is_free_seat,
                'fee_seat_count': fee_seat_count
            },
            beforeSend: function (data) {
                blockUI('modal-body');
            },
            success: function (data) {
                unBlockUI('.model-body');
                notify(data.type, data.message)
                if (data.type == 'success') {
                    $(".still_available_seats").html(Math.max(0, max_participants - sum));
                    $me.attr('seatallocated', allocatedSeat);
                    if (is_free_seat == 1) {
                        $(".total_free_seats").html(Math.max(0, total_free_seats - fee_seat_count));
                    }
                    $(".max_participants").text(sum);
                    //getInitDataTable();
                } else {
                    $me.val(old_val);
                    return 'false';
                }
            }
        });
    });
    $body.on('click', ".btn-allocation", function (e) {
        e.preventDefault();
        $('.seatAllocationDataPlace').removeAttr('style');

        $(".seatUtilizationDataPlace").slideUp('slow');
        getInitDataTable();
    });
    $body.on('click', '.start_allocation', function (e) {
        e.preventDefault();
        var seat_status = $(this).data('seatstatus');
        var event_id = $(".eventID").val();
        var me = $(this);
        bootbox.confirm({
            message: allocation_start,
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
                    changeSeatingStatus(seat_status, event_id, me);
                }
            }
        });

    });
    $body.on('click', '.free_all_seats', function (e) {
        e.preventDefault();
        var seat_status = $(this).data('seatstatus');
        var event_id = $(".eventID").val();
        var me = $(this);
        bootbox.confirm({
            message: all_seat_will_free,
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
                    changeSeatingStatus(seat_status, event_id, me);
                }
            }
        });

    });
    $body.on('click', ".btn-utilization", function (e) {
        e.preventDefault();

        var eventID = $(".eventID").val();
        $.ajax({
            url: base_url + 'seminar-planner/utilizeSeat/details/' + eventID,
            method: 'get',

            beforeSend: function (data) {
                blockUI('modal-body');
            },
            success: function (data) {
                unBlockUI('.model-body');
                $('.seatAllocationDataPlace').hide();
                $(".seatUtilizationDataPlace").html(data);
                $(".seatUtilizationDataPlace").slideDown('slow');
            }
        });
    });

});
function getInitDataTable() {
    $eventID = $(".eventID").val();
    $oTable.ajax.reload();
}

function initAllocationTable() {
    $eventID = $(".eventID").val();
    $oTable = $('#allication_list').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        language: {
            "lengthMenu": "_MENU_ " + records,
            "zeroRecords": noRecordFound,
            "info": showing + " _START_ " + to + " _END_ " + of + " _TOTAL_ " + paginate_entries,
            "infoEmpty": noRecordFound,
            "search": table_search,
        },
        ajax: base_url + 'seminar-planner/allocationData/' + $eventID,
        columns: [
            {
                data: 'name',
                name: 'name'
            },
            {
                data: 'seats',
                name: 'seats',
                orderDataType: 'dom-text-numeric'
            },
            {
                data: 'createdBy',
                name: 'createdBy'
            },
            {
                data: 'updated_at',
                name: 'updated_at'
            },
        ]


    });
}
function changeSeatingStatus(seat_status, event_id, thisButton) {
    $.ajax({
        url: base_url + 'seminar_planned/seat_status/' + seat_status + '/' + event_id,
        method: 'get',
        beforeSend: function () {
            blockUI('.modal-body');
        },
        success: function (data) {

            notify(data.type, data.message);

//            thisButton.parents('.seat_allocation_status').css('display', 'none');
            if (seat_status == '1') {
                $("#tab_seminar_seat_allocation").html("");
                $("[data-target='#tab_seminar_seat_allocation']").trigger('click');
                $("#max_registration").attr('readonly', 'readonly');
            } else {
                $('[data-target="#tab_seminar_seat_allocation"]').attr('disabled', 'disabled');
                $('[data-target="#tab_seminar_seat_allocation"]').parents('li').toggleClass('active').addClass('disabled');
                $('[data-target="#tab_description"]').trigger('click');
            }
            unBlockUI('.modal-body');
        }

    })
}