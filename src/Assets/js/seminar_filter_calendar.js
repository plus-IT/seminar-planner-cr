$(document).on("ready", function () {
    $source = PrepareFilterUrl();
    $body.on("change", ".filter", function () {
        initcalendar();
    });

    $body.on("click", ".reset-filter", function () {
        $(".filter").each(function () {
            $(this).val("");
        });
        
        $(".filterselect ").select2('val', 'All');
        $('#event_select').select2('val', '');

        initcalendar();
    });


    $(".filterhideshow").click(function(){
        $(".filterable").slideToggle('slow');
        $(".filterhideshow").toggleClass("currentShowFilter currentHideFilter");
    });


    $('#event_select').select2("destroy").select2({
         placeholder: "",
         minimumInputLength: 3,
         delay: 250,
         tags: true,
         tokenSeparators: [','],
         ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
             url: base_url + "event_list",
             dataType: 'json',
             method: "GET",
             data: function (term, page) {
                 return {
                     search_text: term // search term
                 };
             },
             results: function (data, page) { // parse the results into the format expected by Select2. 
                return {
                    results: $.map(data, function (item) {
                        return {
                            text: item.event_name+' ('+formatJavascriptDate(item.event_startdate) +' - '+ formatJavascriptDate(item.event_enddate)+')',
                            slug: item.event_name,
                            id: item.id
                        }
                    })
                 };

             }
         },
         initSelection: function (element, callback) {
         },
         maximumSelectionSize: 10,
    });
//create pdf
});

function initcalendar() {
    $('#calendar').fullCalendar('removeEventSource', $source)
    $('#calendar').fullCalendar('refetchEvents')
    $source = PrepareFilterUrl();

    $('#calendar').fullCalendar('addEventSource', $source);
}


function PrepareFilterUrl() {
    var date_range = $("[name=filter_date]").val();
    var url = base_url + "seminar-planner/calendar/getPlannedSeminars"+get_seminar_planner_event_id+"?event_id=" + $("[name=event]").val() +
        "&location_id=" + $("[name=location]").val() +
        "&event_category_id=" + $("[name=event_category]").val() +
        "&trainer_id=" + $("[name=trainer]").val() +
        "&planned_by=" + $("[name=planned_by]").val() +
        "&status=" + $("[name=event_status]").val();
    return url;
}

