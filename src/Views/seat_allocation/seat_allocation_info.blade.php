<?php

$total_participant = !empty($allocation_settings[0]->allocatedSeat) ? $allocation_settings[0]->allocatedSeat : '0';
$already_assigned = !empty($allocation_settings[0]->getAttendees) ? $allocation_settings[0]->getAttendees->count() : '0';
$remaining_seats = $total_participant - $allocated_seats;
$remaining_seats += $free_seats;
?>
<style>
    .tabbable-custom {
        overflow: visible;
    }
</style>
<div class="control-group apply_table_operation">
    <div class="portlet light">
        <div class="portlet-title">
            <div class="col-md-12">
                <div class="col-md-4">

                    <a href="javascript:void(0)"
                       class="btn btn-default btn-allocation">{!! Html::customTrans("event.allocation") !!}</a>
                    <a href="javascript:void(0)"
                       class="btn btn-default btn-utilization">{!! Html::customTrans("event.utilization") !!}</a>
                </div>
                <div class="col-md-8">
                    <label class="form-control">{!! Html::customTrans("event.max_participants") !!} <span
                                class="max_participants">{!! $total_participant !!}</span></label>
                    <label class="form-control">{!! Html::customTrans("event.already_registered") !!} <span
                                class="already_registered">{!! $already_assigned !!}</span></label>
                    <label class="form-control">{!! Html::customTrans("event.still_available_seats") !!} <span
                                class="still_available_seats">
                            @if(Auth::user()->levelID!='3'){!! $remaining_seats !!} @else 0 @endif</span> </label>
                </div>
            </div>
        </div>
        <div id="tab_schedule_info" class="clearfix">
            <div class="row seatAllocationDataPlace">
                @include('seminar_planner.seat_allocation.seat_allocation_table')
            </div>
            <div class="row seatUtilizationDataPlace" style="display: none;">
                @include('seminar_planner.seat_allocation.seat_allocation_table')
            </div>
        </div>
    </div>
</div>