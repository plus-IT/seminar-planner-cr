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
        var sum = 0;
        $('.allocation_seat_total').each(function () {
            if ($(this).val() != '') {
                sum += parseFloat($(this).val());  // Or this.innerHTML, this.innerText
            }
        });

        if (sum > max_participants) {
            notify('error', ' you can not assign seats more then maximum seats allocated');
            $(this).val($(this).attr('seatallocated'));
            return 'false';
        }
        $.ajax({
            url: base_url + 'seminar-planner/allocateSeat/details/' + eventID + '/' + levelID,
            method: 'post',
            data: {
                'allocatedSeat': allocatedSeat,
                'organization': organization
            },
            beforeSend: function (data) {
                blockUI('modal-body');
            },
            success: function (data) {
                unBlockUI('.model-body');
                notify(data.type, data.message)
                if (data.type == 'success') {
                    $(".still_available_seats").html(max_participants - sum);
                    getInitDataTable();
                }
            }
        });
    });
    $body.on('click', ".btn-allocation", function (e) {
        e.preventDefault();
        $('.seatAllocationDataPlace').removeAttr('style');

        $(".seatUtilizationDataPlace").slideUp('slow');
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
    $oTable.ajax.url(base_url + 'seminar-planner/allocationData/' + $eventID);
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