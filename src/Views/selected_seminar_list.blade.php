<table class="table table-bordered">
    <thead>
    <tr>
        <th>{!! Html::customTrans("seminarPlanner.seminar_title") !!}</th>
        <th>
            {!! Html::customTrans("seminarPlanner.seminar_category") !!}
        </th>
    </tr>
    </thead>
    <tbody>
    @foreach($selectedSeminars as $selected)
        <tr>
            <td>{!! $selected->event_name !!}</td>
            <td>{!! $selected->EventCategory->event_category_name !!}</td>
        </tr>
    @endforeach
    </tbody>
</table>
