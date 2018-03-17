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
            <td>
                @if(LaravelLocalization::getCurrentLocale() == 'en')
                    {!! $selected->EventCategory->event_category_name !!}
                @else
                    {!! $selected->EventCategory->event_category_name_de !!}
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
