<?php
$total_participant = !empty($allocation_settings[0]->allocatedSeat) ? $allocation_settings[0]->allocatedSeat : '0';
$already_assigned = !empty($allocation_settings[0]->getAttendees) ? $allocation_settings[0]->getAttendees->count() : '0';
$remaining_seats = $total_participant - $allocated_seats;
?>
<style>
    .tabbable-custom {
        overflow: visible;
    }
</style>
<div class="control-group apply_table_operation">
    <div class="portlet light">
        <div class="portlet-title">
            @if($plannedEventObj->is_seats_allocated=='0')
            <div class="col-md-12 text-cener seat_allocation_status">
                <a href="javascript:void(0)" class="btn btn-default start_allocation" data-seatstatus="1">
                    {!! Html::customTrans("event.start_allocation") !!}
                </a>
                <a href="javascript:void(0)" class="btn btn-default free_all_seats" data-seatstatus="2">
                    {!! Html::customTrans("event.free_all_seats") !!}
                </a>
            </div>
            @endif
            <div class="col-md-12 show_allocation_settings" style="{!! !empty($plannedEventObj->is_seats_allocated)? ($plannedEventObj->is_seats_allocated==1?'display:block':'display:none'):'display:none'!!}">
                <div class="col-md-4">

                    <a href="javascript:void(0)"
                       class="btn btn-default btn-allocation">{!! Html::customTrans("event.allocation") !!}</a>
                    <a href="javascript:void(0)"
                       class="btn btn-default btn-utilization">{!! Html::customTrans("event.utilization") !!}</a>
                </div>
                <div class="col-md-8" >
                    <label class="form-control">{!! Html::customTrans("event.max_participants") !!} <span
                            class="max_participants">{!! $total_participant !!}</span></label>
                    <label class="form-control">{!! Html::customTrans("event.already_registered") !!} <span
                            class="already_registered">{!! $already_assigned !!}</span></label>
                    <label class="form-control">{!! Html::customTrans("event.still_available_seats") !!} <span
                            class="still_available_seats">
                            @if(Auth::user()->levelID!='3'){!! $remaining_seats !!} @else 0 @endif</span> </label>
                    <label class="form-control">{!! Html::customTrans("event.free_seats") !!} <span
                            class="total_free_seats">
                            {!! $free_seats !!} </span> </label>
                </div>
            </div>
        </div>
        <div id="tab_schedule_info" class="clearfix show_allocation_settings" style="{!! !empty($plannedEventObj->is_seats_allocated)? ($plannedEventObj->is_seats_allocated==1?'display:block':'display:none'):'display:none'!!}">
            <div class="row seatAllocationDataPlace">

                <div class="col-md-12" >
                    @include('seminar_planner.seat_allocation.seat_allocation_table')
                </div>
            </div>
            <div class="row seatUtilizationDataPlace" style="display: none;">
                @include('seminar_planner.seat_allocation.seat_allocation_table')
            </div>
        </div>
    </div>
</div>