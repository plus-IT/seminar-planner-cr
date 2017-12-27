<!-- HTML Part-->
<?php
$total_seats = !empty($allocation_data[0]->total_seats) ? $allocation_data[0]->total_seats : 0;
$assigned_seats = 0;
$remaining_Seats = 0;
?>
<div class="col-md-12 seat_row">
    <p> My Region: <strong> {!! Auth::user()->roleName !!}</strong></p>
    <div class="row">
        <div class="col_wrapper">
            @if(!empty($allocation_data))
                @foreach($allocation_data as $allocation)
                    <?php
                    $assigned_seats += $allocation->allocatedSeat;
                    ?>
                    @if(Auth::user()->LevelValueID==1)
                        <ul class="seat_map with_bkgd devider frankfurt_btn">
                            @include('seminar_planner.seat_allocation.seat_popover')
                            <li class="seat_header ">{!! $allocation->name !!}</li>
                            @if(!empty($allocation->children_rec->toArray()))
                                @foreach($allocation->children_rec as $key=>$child_seat)
                                    <?php  $total_attendees = !empty($child_seat->getAttendees) ? $child_seat->getAttendees->count() : 0 ?>
                                    <ul class="seat_map with_bkgd devider frankfurt_btn" style="margin-top: 45px;">
                                        <li class="seat_header ">{!! $child_seat->name !!}</li>
                                        @for($i=1;$i<=$child_seat->allocatedSeat;$i++)
                                            <?php  $total_attendees = !empty($child_seat->getAttendees) ? $child_seat->getAttendees->count() : 0 ?>
                                            @if($i <= $total_attendees)
                                                <li class="seat registered" id="{!! $total_attendees !!}"
                                                    alt="{!! $allocation->allocatedSeat !!}"></li>
                                            @else
                                                <li class="seat reserved" id="{!! $total_attendees !!}"
                                                    alt="{!! $allocation->allocatedSeat !!}"></li>
                                            @endif
                                        @endfor

                                    </ul>
                                @endforeach
                            @else
                                <ul class="seat_map with_bkgd devider frankfurt_btn">
                                    @include('seminar_planner.seat_allocation.seat_popover')
                                    <li class="seat_header ">{!! $allocation->name !!}</li>
                                    @for($i=1;$i<=$allocation->allocatedSeat;$i++)
                                        <?php  $total_attendees = !empty($allocation->getAttendees) ? $allocation->getAttendees->count() : 0 ?>
                                        @if($i <= $total_attendees)
                                            <li class="seat registered" id="{!! $total_attendees !!}"
                                                alt="{!! $allocation->allocatedSeat !!}"></li>
                                        @else
                                            <li class="seat reserved" id="{!! $total_attendees !!}"
                                                alt="{!! $allocation->allocatedSeat !!}"></li>
                                        @endif
                                    @endfor

                                </ul>
                            @endif
                        </ul>
                    @else
                        <ul class="seat_map with_bkgd devider frankfurt_btn">
                            @include('seminar_planner.seat_allocation.seat_popover')
                            <li class="seat_header ">{!! $allocation->name !!}</li>
                            @for($i=1;$i<=$allocation->allocatedSeat;$i++)
                                <?php  $total_attendees = !empty($allocation->getAttendees) ? $allocation->getAttendees->count() : 0 ?>
                                @if($i <= $total_attendees)
                                    <li class="seat registered" id="{!! $total_attendees !!}"
                                        alt="{!! $allocation->allocatedSeat !!}"></li>
                                @else
                                    <li class="seat reserved" id="{!! $total_attendees !!}"
                                        alt="{!! $allocation->allocatedSeat !!}"></li>
                                @endif
                            @endfor

                        </ul>
                    @endif
                @endforeach
            @endif


            <ul class="seat_map devider">

                @if(!empty($total_seats))
                    <?php
                    $remaining_Seats = $total_seats - $assigned_seats;
                    ?>
                    @for($i=0;$i<$remaining_Seats;$i++)
                        <li class="seat available"></li>
                    @endfor
                @endif
            </ul>
            <ul class="seat_map devider">

                @if(isset($get_free_seat) && $get_free_seat>0)
                    @for($i=0;$i<$get_free_seat;$i++)
                        <li class="seat seat_free"></li>
                    @endfor
                @endif
            </ul>


        </div>
    </div>
    <div class="row">
        <div class="seat_info">
            <ul>
                <li><img src="../../images/seat_reserved.png"/> Reserved</li>
                <li><img src="../../images/seat_free.png"/> Free</li>
                <li><img src="../../images/seat_registered.png"/> Registered</li>
                <li><img src="../../images/seat_available.png"/> Available</li>
                <ul>
        </div>
    </div>
</div>


</div>
<!--End HTML Part-->
