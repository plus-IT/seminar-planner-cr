<div class="frankfurt_pophover">
    <p><i class="fa seat_small" aria-hidden="true"></i>{!! Html::customTrans("seminarPlanner.seats_assigned_organization") !!} :
        <span> ({!! $allocation->name !!} ({!! !empty($allocation->getAttendees)?$allocation->getAttendees->count() :0 !!}/{!! $allocation->allocatedSeat !!})</span></p>
    <p><i class="fa fa-user" aria-hidden="true"></i>{!! Html::customTrans("seminarPlanner.assigned_by") !!} :
        <span>{!!  !empty($allocation->FirstName) ?$allocation->FirstName .' '.$allocation->LastName:'' !!}</span></p>
    <p><i class="fa fa-calendar-o" aria-hidden="true"></i>{!! Html::customTrans("seminarPlanner.assigned_on") !!} :
        <span>{!! !empty($allocation->assginedDate)? format_date($allocation->assginedDate):'' !!}</span></p>
    <hr/>
    <p>{!! Html::customTrans("seminarPlanner.participant_already_registred") !!} {!! $allocation->name !!} </p>
</div>
