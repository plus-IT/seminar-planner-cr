<div class="frankfurt_pophover">
    <p><i class="fa seat_small" aria-hidden="true"></i>Seats assigned to Organisation:
        <span>Mitte ({!! $allocation->name !!} ({!! !empty($allocation->getAttendees)?$allocation->getAttendees->count() :0 !!}/{!! $allocation->allocatedSeat !!})</span></p>
    <p><i class="fa fa-user" aria-hidden="true"></i>Assigned by:
        <span>{!!  !empty($allocation->FirstName) ?$allocation->FirstName .' '.$allocation->LastName:'' !!}</span></p>
    <p><i class="fa fa-calendar-o" aria-hidden="true"></i>Assigned on:
        <span>{!! !empty($allocation->assginedDate)? format_date($allocation->assginedDate):'' !!}</span></p>
    <hr/>
    <p>Participant is already assigned from {!! $allocation->name !!} </p>
</div>