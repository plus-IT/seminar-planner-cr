<script>
    pagination_url = "<?php echo url(LaravelLocalization::getCurrentLocale() . "/" . "seminar-planner/getDetails") ?>";

    var initial = "";
var limit ="<?php $limit ?>"
    var root_url = "<?php echo url(LaravelLocalization::getCurrentLocale() . "/" . "seminar-planner/getDetails") ?>";
</script>
<table class="table table-bordered">
    <thead>
    <tr>
        <th>
         <div class="md-checkbox" style="margin-top: -18px;">
                <input type="checkbox" name="multicheck" id="multicheck" class="md-check multicheck" value="">
                <label for="multicheck">
                    <span></span>
                    <span class="check"></span>
                    <span class="box"></span>  </label>
            </div>
           <!--  <input type="checkbox" name="multicheck" class="multicheck"> -->
        </th>
        <th>
            {!! Html::customTrans("seminarPlanner.seminar_title") !!}
        </th>
        <th>
            {!! Html::customTrans("seminarPlanner.seminar_category") !!}
        </th>
        <th>
            {!! Html::customTrans("seminarPlanner.total_seminars") !!}
        </th>
        <th>
            {!! Html::customTrans("seminarPlanner.average_participants") !!}
        </th>
        <th>
            {!! Html::customTrans("seminarPlanner.total_participants") !!}
        </th>
        <th>
            {!! Html::customTrans("seminarPlanner.different_locations") !!}
        </th>
        <th>
            {!! Html::customTrans("seminarPlanner.different_trainers") !!}
        </th>

        <th>
            {!! Html::customTrans("seminarPlanner.new") !!}
        </th>
    </tr>
    </thead>
    <tbody>
    @foreach($total_events as $events)
        <tr>
            <td>
                <div class="md-checkbox">
                    <input type="checkbox" id="{!! $events->event_id_final !!}" class="md-check checked_item" checked="" value="{!! $events->event_id_final !!}">
                    <label for="{!! $events->event_id_final !!}">
                        <span></span>
                        <span class="check"></span>
                        <span class="box"></span>  </label>
                </div>
                <!-- <input type="checkbox" class="checked_item" value="{!! $events->event_id_final !!}"> -->
            </td>
            <td>
                {!! $events->event_name !!}
            </td>
            <td>@if(LaravelLocalization::getCurrentLocale() == 'en') {!! $events->event_category_name !!} @else {!! $events->event_category_name_de !!} @endif</td>
            <td>
                {!! $events->total_seminars !!}
            </td>

            <td>

                    {!! ($events->total_participants!=0 && $events->total_seminars!=0) ? number_format($events->total_participants/$events->total_seminars,1,",",".")  : 0 !!}
            </td>
             <td>

                            {!! $events->total_participants !!}
                        </td>
            <td class="getPopOverHere">
                <?php
                    $locationList = !empty($events->location) ? explode("," , $events->location) : [];
                ?>
                <span  id="popover" data-content="{!! !empty($events->location) ? $events->location : 'No record in past'!!}"
                       title="Location">{!!  count($locationList) !!}</span>
            </td>
            <td class="getPopOverHere" >
                <?php
                    $trainerList = !empty($events->personList) ? explode("," , $events->personList) : [];
                ?>
                <span id="popover" data-content="{!! !empty($events->personList) ? $events->personList : 'No record in past' !!}"
                      title="Trainers">{!! count($trainerList) !!}</span>
            </td>

            <td>
                <?php
                $now = isset($events->event_enddate)? $events->final_date : '';
                $created = \Carbon\Carbon::parse($now);
                $end_date =  isset($seminar_days_new->seminar_new_until_days) ? $seminar_days_new->seminar_new_until_days : '';?>

                <i class="fa fa-circle pull-right {!!
                ($created->diffInDays() < $end_date)
                ?'enableClass'
                : 'disableClass'
                !!}" style="width: 100%;text-align: center;"></i>
            </td>

        </tr>
    @endforeach
    </tbody>
</table>