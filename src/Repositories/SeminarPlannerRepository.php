<?php

namespace Ptlyash\SeminarPlannerCR\Repositories;

use App\Models\AllocationLevelValues;
use App\Models\AllocationSettings;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Location;
use App\Models\Person;
use App\Models\PlannedSeminarRevenue;
use App\Models\SeminarSettings;
use App\Models\User;
use App\Models\EventAttendees;
use Illuminate\Support\Facades\DB;
use Ptlyash\SeminarPlannerCR\Interfaces\SeminarPlannerRepositoryInterface;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use Config;
use App\Models\EventSchedule;
use App\Models\PlannedEvent;
use App\Models\PlannedEventSchedule;
use App\Models\PlannedEventTask;
use App\Models\PlannedScheduleSlot;
use App\Models\PlannedSchedule;
use App\Models\PlannedTask;
use App\Models\PlannedDocument;
use App\Models\SeminarRevenue;
use App\Models\SeminarActionsLog;
use App\Library\CustomFunction;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use stdClass;
use Log;

/**
 * Class EventRepository
 * @package App\Repositories
 */
class SeminarPlannerRepository implements SeminarPlannerRepositoryInterface {

    public function search($search_text = "", $sort_by = "", $sort_order = "", $limit = "") {
        $multi_cat_name = '';
        $event = Event::query();
        $is_enable = \App\Accessories\FTM::isEnabled('seminar-multicategory-support');
        if ($is_enable) {
            $event->leftjoin("event_category", function ($join) {
                $join->on(\DB::raw('find_in_set(event_category.id,events.event_category_id)'), \DB::raw(''), \DB::raw(''));
            });
            $multi_cat_name = ' ,GROUP_CONCAT(DISTINCT (event_category.event_category_name) SEPARATOR ", ")as event_category_name,
            GROUP_CONCAT(DISTINCT (event_category.event_category_name_de ) SEPARATOR ", ")as event_category_name_de';
            $event->groupBy('events.id');
        } else {
            $event->join('event_category', 'events.event_category_id', '=', 'event_category.id');
        }

        $event->where(function ($query) use ($search_text) {
            $search_text = strtolower($search_text);
            if ($search_text != "") {
                $query->orWhere('events.event_name', 'like', '%' . $search_text . '%');
            }
        });

        $event->where('status', 1);
        if (Input::has('category_id')) {
            $is_enable = \App\Accessories\FTM::isEnabled('seminar-multicategory-support');
            if ($is_enable) {
                $category_id = str_replace(",", ",|,", Input::get('category_id'));
                $event->whereRaw('CONCAT(",",planned_events.event_category_id, ",") REGEXP ",' . $category_id . ',"');
                //$event->whereRaw('FIND_SET_EQUALS(events.event_category_id, "' . Input::get('category_id') . '")');
            } else {
                $category_id = explode(",", Input::get('category_id'));
                $event->whereIn('event_category_id', $category_id);
            }
        }

        $event->leftjoin('event_schedule', 'events.id', '=', 'event_schedule.event_id')
                ->leftjoin('schedule', 'event_schedule.schedule_id', '=', 'schedule.id')
                ->leftjoin('schedule_slot', 'schedule.id', '=', 'schedule_slot.schedule_slotID')->groupBy('event_schedule.event_id');

        if (Input::has('seminarLocation')) {
            $locationId = explode(",", Input::get('seminarLocation'));
            $event->whereIn("schedule.LocationID", $locationId);
        }

        if (Input::has('trainerId')) {

            $trainerId = explode(",", Input::get('trainerId'));
            $event->whereIn("schedule_slot.trainer", $trainerId);
        }
        if (Input::has('planned_by')) {
            $event->whereIn('events.id', function ($query) {
                $query->select('planned_events.blueprint_id')
                        ->from('planned_events')
                        ->where('planned_events.planned_by', '=', Input::get('planned_by'));
            });
        }

        if (Input::has('is_planned')) {
            if (Input::get('is_planned') == 1) {
                $event->whereIn('events.id', function ($query) {
                    $query->select('planned_events.blueprint_id')
                            ->from('planned_events');
                });
            } else {
                $event->whereNotIn('events.id', function ($query) {
                    $query->select('planned_events.blueprint_id')
                            ->from('planned_events');
                });
            }
        }
        if (Input::has('status'))
            $event->where('status', '=', Input::get('status'));

        if (Input::has('begin'))
            $event->where('event_startdate', '=', date("Y-m-d", strtotime(Input::get('begin'))));

        if (Input::has('end'))
            $event->where('event_enddate', '=', date("Y-m-d", strtotime(Input::get('end'))));

        $query = '';
        if (Input::has('start_date') && Input::has('end_date')) {
            $query = ',(select count(*) from planned_events where planned_events.blueprint_id=events.id
                                AND planned_events.event_startdate>="' . date("Y-m-d", strtotime(Input::get('start_date'))) . '" AND
                                planned_events.event_enddate<= "' . date("Y-m-d", strtotime(Input::get('end_date'))) . '")as total_seminars,
                     (select count(*) from event_attendees where event_attendees.event_id in(select id from planned_events where planned_events.blueprint_id=events.id AND planned_events.event_startdate>="' . date("Y-m-d", strtotime(Input::get('start_date'))) . '" AND
                                planned_events.event_enddate<= "' . date("Y-m-d", strtotime(Input::get('end_date'))) . '"))as total_participants,
                                
                     (select GROUP_CONCAT(DISTINCT CONCAT(TRIM(pr.FirstName)," ",TRIM(pr.LastName)) SEPARATOR ", ") as speaker from planned_events as pe, planned_event_schedule as pes, 
                        planned_schedule as ps, 
                        planned_schedule_slot as pss, 
                        person as pr where pe.blueprint_id=events.id AND pes.event_id = pe.id AND 
                        pes.schedule_id = ps.id AND 
                        pss.ScheduleID = ps.id AND 
                        FIND_IN_SET( pr.PersonID, pss.trainer ) AND pe.event_startdate>="' . date("Y-m-d", strtotime(Input::get('start_date'))) . '" AND
                              pe.event_enddate<= "' . date("Y-m-d", strtotime(Input::get('end_date'))) . '" GROUP BY pe.blueprint_id)as personList,
                              
                     (select GROUP_CONCAT(DISTINCT TRIM(lc.LocationName) SEPARATOR ", ") as location_name from planned_events as pe, planned_event_schedule as pes, 
                        planned_schedule as ps, 
                        location as lc,
                        planned_schedule_slot as pss 
                        where pe.blueprint_id=events.id AND pes.event_id = pe.id AND ps.LocationID = lc.LocationID AND
                        pes.schedule_id = ps.id AND 
                        pss.ScheduleID = ps.id 
                        AND pe.event_startdate>="' . date("Y-m-d", strtotime(Input::get('start_date'))) . '" AND
                              pe.event_enddate<= "' . date("Y-m-d", strtotime(Input::get('end_date'))) . '" GROUP BY pe.blueprint_id )as location,
                       
                     (select sum(price) from planned_events where planned_events.blueprint_id=events.id  AND planned_events.event_startdate>="' . date("Y-m-d", strtotime(Input::get('start_date'))) . '" AND
                                planned_events.event_enddate<= "' . date("Y-m-d", strtotime(Input::get('end_date'))) . '")as total_revenue';
        }
        if ($sort_by == "event_category_id") {
            $sort_by = 'event_category_name';
        }
        if ($sort_by != "")
            $event->orderBy($sort_by, $sort_order);
        else
            $event->orderBy("events.event_name");

        $event->selectRaw(
                'events.*,events.id as event_id_final
            ,events.created_at as final_date
            ,event_category.*' . $multi_cat_name . $query
        );
        //dd($event->paginate($limit));
        return $event->paginate($limit);
    }

    function getAllEventCategory() {
        $search_cat = '';
        if (Input::has('q'))
            $search_cat = Input::get('q');

        if (LaravelLocalization::getCurrentLocale() == 'en') {
            return EventCategory::where(function ($query) use ($search_cat) {
                        $search_text = strtolower($search_cat);
                        if ($search_text != "") {
                            $query->orWhere('event_category.event_category_name', 'like', '%' . $search_cat . '%');
                        }
                    })->get(['id', 'event_category_name as text']);
        } else {
            return EventCategory::where(function ($query) use ($search_cat) {
                        $search_text = strtolower($search_cat);
                        if ($search_text != "") {
                            $query->orWhere('event_category.event_category_name_de', 'like', '%' . $search_cat . '%');
                        }
                    })->get(['id', 'event_category_name_de as text']);
        }
    }

    function getLocation() {
        $search_cat = '';
        if (Input::has('q'))
            $search_cat = Input::get('q');
        return Location::where(function ($query) use ($search_cat) {
                    $search_text = strtolower($search_cat);
                    if ($search_text != "") {
                        $query->orWhere('location.LocationName', 'like', '%' . $search_cat . '%');
                    }
                })->get([
                    'LocationID as id',
                    'LocationName as text',
        ]);
    }

    function getTrainer() {
        $search_cat = '';
        if (Input::has('q'))
            $search_cat = Input::get('q');
        return Person::where(function ($query) use ($search_cat) {
                    $search_text = strtolower($search_cat);
                    if ($search_text != "") {
                        $query->orWhere('person.FirstName', 'like', '%' . $search_cat . '%')
                                ->orWhere('person.LastName', 'like', '%' . $search_cat . '%');
                    }
                })->get([
                    'PersonID as id',
                    DB::raw('CONCAT(FirstName," ",LastName) as text'),
        ]);
    }

    function getSeminarPlannedBy() {
        $search_cat = '';
        if (Input::has('q'))
            $search_cat = Input::get('q');
        return User::where(function ($query) use ($search_cat) {
                    $search_text = strtolower($search_cat);
                    if ($search_text != "") {
                        $query->orWhere('user.FirstName', 'like', '%' . $search_cat . '%')
                                ->orWhere('user.LastName', 'like', '%' . $search_cat . '%');
                    }
                })->get([
                    'UserID as id',
                    DB::raw('CONCAT(FirstName," ",LastName) as text'),
        ]);
    }

    function getSelectedSeminar() {
        $seminar_id = explode(",", Input::get('seminarId'));

        return Event::with('EventCategory')->whereIn("events.id", $seminar_id)->get();
    }

    function getSeminarBluePrints() {
        $bluePrintIds = explode(",", Input::get("bluePrintsId"));
        $bluePrintSeminars = Event::whereIn('id', $bluePrintIds)->with('eventSchedule.schedule')->get();
        return $bluePrintSeminars;
    }

    function insertBlueprintAsDraftEvent() {
        $inputData = Input::all();
        // Planned event setting
        $plannedEventSetting = SeminarSettings::first();

        $event = Event::find($inputData['blueprintEventId']);
        $eventSchedule = EventSchedule::where('event_id', '=', $inputData['blueprintEventId'])->count();
        if ($eventSchedule == 0)
            return false;
        $event->planned_by = Auth::user()->UserID;
        $event->blueprint_id = $inputData['blueprintEventId'];
        $event->event_status = 'draft';
        $event->external_id = null;


        // Add event To planned event table
        $plannedEvents = new PlannedEvent();
        // Check if deploy to internet is on OR not
        if (count($plannedEventSetting) > 0 && isset($plannedEventSetting->set_me_on_waiting_list) && isset($plannedEventSetting->automatic_register)) {
            $plannedEvents->is_deploy_internet = isset($plannedEventSetting->display_seminar_online_portal) ? $plannedEventSetting->display_seminar_online_portal : 0;
            $plannedEvents->set_me_on_waiting_list = isset($plannedEventSetting->set_me_on_waiting_list) ? $plannedEventSetting->set_me_on_waiting_list : 0;
            $plannedEvents->automatic_register = isset($plannedEventSetting->automatic_register) ? $plannedEventSetting->automatic_register : 0;
        } else {
            $plannedEvents->is_deploy_internet = isset($plannedEventSetting->display_seminar_online_portal) ? $plannedEventSetting->display_seminar_online_portal : 0;
        }

        $plannedEvents->fill($event->toArray())->save();

        $event->load('eventSchedule');
        $event->load('eventTask');
        $event->load('eventDocument');
        $event->load('eventRevenue');

        foreach ($event->getRelations() as $relation => $items) {
            foreach ($items as $item) {

                if ($relation == "eventSchedule") {
                    $scheduleDateIndex = array_search($item->schedule->id, array_column($inputData["schedules"], 'scheduleId'));
                    $newschedule = $item->schedule->replicate();
                    $newschedule->schedule_date = $inputData["schedules"][$scheduleDateIndex]['scheduleDate'];

                    $plannedSchedule = new PlannedSchedule();

                    $plannedSchedule->fill($newschedule->toArray())->save();

                    // check If schedule is conflicted with other event schedule
                    $conflictResult = $this->checkLocationConflict($plannedSchedule->LocationID, $plannedSchedule->id, $plannedSchedule->schedule_date, $plannedEvents->id);
                    if ($conflictResult['type'] == "danger") {
                        // Rupesh : commented as already handeled in checkLocationConflict
                        //$plannedSchedule->locationConflicted = 1;
                        $plannedSchedule->detailLocationConflictMessage = $conflictResult["detailedMessage"];
                        //$plannedSchedule->conflictedScheduleIds = $conflictResult["conflictedScheduleIds"];
                        $plannedSchedule->save();
                    }

                    // Load schedule slots
                    $schedule = $item->schedule;
                    $schedule->load('eventScheduleSlot');

                    // duplicate schedule slots
                    foreach ($schedule->getRelations() as $scheduleRelation => $scheduleItems) {
                        foreach ($scheduleItems as $scheduleItem) {
                            unset($scheduleItem->schedule_slotID);
                            $newSlot = $scheduleItem->replicate();
                            $newSlot->ScheduleID = $plannedSchedule->id;
                            $slot = new PlannedScheduleSlot();
                            $slot->fill($newSlot->toArray())->save();

                            // Check If slot trainer is conflicted with other Trainer
                            $conflictResult = $this->checkTrainerConflict($slot->schedule_slotID, $plannedSchedule->id, $slot->trainer, $plannedEvents->id);
                            if ($conflictResult['type'] == "danger") {

                                $plannedSchedule->trainerConflicted = 1;
                                $plannedSchedule->detailTrainerConflictMessage = $conflictResult["detailedMessage"];
                                $plannedSchedule->conflictedSlotIds = $this->addConflictedScheduleSlotId($schedule->conflictedSlotIds, $conflictResult["conflictedSlotIds"]);
                                $plannedSchedule->save();

                                // Mark slot as conflicted
                                $slot->trainerConflicted = 1;
                                $slot->detailTrainerConflictMessage = $conflictResult["detailedMessage"];
                                $slot->conflictedSlotIds = $conflictResult["conflictedSlotIds"];
                                $slot->save();
                            }
                            // Remove - once test : $plannedSchedule->eventScheduleSlot->create($scheduleItem->toArray());
                        }
                    }


                    $item->schedule_id = $plannedSchedule->id;
                    unset($item->id);
                    unset($item->schedule);
                }

                if ($relation == "eventTask") {
                    $newtask = $item->task->replicate();
                    $plannedTask = new PlannedTask();
                    $plannedTask->fill($newtask->toArray())->save();
                    $item->task_id = $plannedTask->TaskID;
                    unset($item->id);
                    unset($item->task);
                }

                if ($relation == "eventDocument") {
                    $newDocument = $item->document->replicate();
                    $plannedDocument = new PlannedDocument();
                    $plannedDocument->fill($newDocument->toArray())->save();
                    $item->document_id = $plannedDocument->DocumentID;
                    unset($item->id);
                    unset($item->document);
                }

                if ($relation == "eventRevenue") {
                    unset($item->id);
                }



                $item->event_id = $plannedEvents->id;


                // $plannedEvents->{$relation}()->create($item->toArray());
                $builder = $plannedEvents->{$relation}();
                $arrayInsert = $item->toArray();
                // unsetting primary keys if any
                // To Do : find a better way to insert without primary keys
                unset($arrayInsert['revenue_item_id']);
                $builder->insert(
                        $arrayInsert
                );
            }
        }

        // Update start and End date of the new draft event
        $event_start_range = PlannedEventSchedule::where("event_id", $plannedEvents->id)->leftjoin("planned_schedule", function ($join) {
                    $join->on("planned_event_schedule.schedule_id", "=", "planned_schedule.id");
                })->orderBy('planned_schedule.schedule_date', 'asc')->first();

        $event_end_range = PlannedEventSchedule::where("event_id", $plannedEvents->id)->leftjoin("planned_schedule", function ($join) {
                    $join->on("planned_event_schedule.schedule_id", "=", "planned_schedule.id");
                })->orderBy('planned_schedule.schedule_date', 'desc')->first();

        $plannedEvents->event_startdate = date('Y-m-d', strtotime($event_start_range->schedule_date));
        $plannedEvents->event_enddate = date('Y-m-d', strtotime($event_end_range->schedule_date));
        $plannedEvents->save();
        if (Auth::user()->LevelValueID != '') {
            $seat_allocation = new AllocationSettings();
            $seat_allocation->modelLevel = Auth::user()->LevelValueID;
            $seat_allocation->allocatedSeat = $plannedEvents->max_registration;
            $seat_allocation->eventID = $plannedEvents->id;
            $seat_allocation->parentID = 0;
            $seat_allocation->createdBy = Auth::id();
            $seat_allocation->save();
        }
        return $plannedEvents::where('id', $plannedEvents->id)->with(["eventSchedule.schedule", "eventSchedule.schedule.scheduleLocation"])->first();
    }

    function getPlannedSeminarById($id) {
        return PlannedEvent::where('id', $id)
                        ->with([
                            'eventSchedule.schedule.scheduleLocation',
                            'eventDocument',
                            'eventTask'
                        ])
                        ->first();
    }

    // Fetch seminars for calendar

    function getPlannedSeminars($plannedSeminarId = null) {
        $start_date = Input::get("start");
        $end_date = Input::get("end");

        $seminarPlannerSetting = SeminarSettings::first();

        $event = PlannedEvent::query();

        $event->with(["eventSchedule.schedule", "eventSchedule.schedule.scheduleLocation"]);

        $event->leftjoin("planned_event_schedule", "planned_event_schedule.event_id", "=", "planned_events.id")
                ->leftjoin("planned_schedule", "planned_schedule.id", "=", "planned_event_schedule.schedule_id")
                ->leftjoin("planned_schedule_slot", "planned_schedule_slot.ScheduleID", "=", "planned_schedule.id")
                ->leftjoin("location", "planned_schedule.LocationID", "=", "location.LocationID")
                ->leftjoin("person", "planned_events.preferred_trainers_id", "=", "person.PersonID")
                ->leftjoin("event_category", "planned_events.event_category_id", "=", "event_category.id");

        if (Input::get("event_id") != "null" && !empty(Input::get("event_id"))) {
            $eventID = explode(",", Input::get("event_id"));
            $event->whereIn('planned_events.blueprint_id', $eventID);
        }

        /*
         * Filter to show only qualification events OR only planned events OR Both
         */
        if (Input::has('event_to_show')) {
            $eventToShow = Input::get('event_to_show');
            if ($eventToShow == 'qualificationEventsOnly') {
                $event->whereNotNull('planned_qualification_id');
            } elseif ($eventToShow == 'plannedEventsOnly') {
                $event->whereNull('planned_qualification_id');
            }
        }

        if (Input::get("conflict_event_id") != "" && Input::get("conflict_event_id") != 0) {
            $event->where('planned_events.id', Input::get("conflict_event_id"));
            $color = "'#a94442'";
        }

        if (Input::get("event_category_id") != "null" && !empty(Input::get("event_category_id"))) {
            $is_enable = \App\Accessories\FTM::isEnabled('seminar-multicategory-support');
            if ($is_enable) {
                $category_id = str_replace(",", "|", Input::get('event_category_id'));
                $event->whereRaw('planned_events.event_category_id REGEXP "' . $category_id . '"');
            } else {
                $event_category_id = explode(",", Input::get("event_category_id"));
                $event->whereIn('planned_events.event_category_id', $event_category_id);
            }
        }

        if (Input::get("location_id") != "null" && !empty(Input::get("location_id"))) {
            $locationId = explode(",", Input::get("location_id"));
            $schedule = PlannedSchedule::leftjoin("planned_event_schedule", "planned_schedule.id", "=", "planned_event_schedule.schedule_id")
                    ->whereIn("planned_schedule.LocationID", $locationId)
                    ->get([DB::raw("group_concat(planned_event_schedule.event_id SEPARATOR ', ') as event_id")]);
            $eventId = explode(",", $schedule[0]->event_id);
            $event->whereIn('planned_events.id', array_unique($eventId));
        }

        if (Input::get("trainer_id") != "null" && !empty(Input::get("trainer_id"))) {
//            $trainer_id = explode(",", Input::get("trainer_id"));
            $trainer_id = str_replace(",", "|", Input::get("trainer_id"));
            $event->whereRaw('CONCAT(",", `planned_schedule_slot`.`trainer`, ",") REGEXP ",' . $trainer_id . ',"');
        }

        if (Input::get("status") != "null" && !empty(Input::get("status"))) {
            $status = explode(",", Input::get("status"));
            $event->whereIn('event_status', $status);
        }

        if (Input::get("planned_by") != "null" && !empty(Input::get("planned_by"))) {
            $plannedBy = explode(",", Input::get("planned_by"));
            $event->whereIn('planned_by', $plannedBy);
        }

        if (!Input::has("conflict_event_id")) {
            $event->whereRaw("`event_startdate` >= DATE('" . $start_date . "') and `event_enddate` <= DATE('" . $end_date . "')");
        }

        // Check we need to show cancel seminars
        if ($seminarPlannerSetting->show_cancel_seminars == 0) {
            $event->where("event_status", '!=', "cancel");
        }

        $event->groupBy('planned_events.id');

        if ($plannedSeminarId != "")
            $event->where('planned_events.id', '=', $plannedSeminarId);

        $event->with(['plannedQualification' => function($qur) {
                $qur->select(['id', 'name', 'start_date', 'end_date']);
            }]);

        $eventList = $event->get([
            'planned_events.id',
            'event_name',
            'event_status',
            'blueprint_id',
            'event_startdate',
            DB::raw('DATE_ADD(event_enddate, INTERVAL 1 DAY)'),
            'min_registration',
            'max_registration',
            'LocationName',
            'status',
            'event_startdate',
            'event_enddate',
            'planned_qualification_id',
            DB::raw('concat(FirstName," ", LastName) as trainer_name'),
            'event_category_name',
            DB::raw('(select count(event_attendees.event_id) from event_attendees where event_attendees.event_id=planned_events.id)as totalParticipant  ')
        ]);

        $calendarEvents = [];
        foreach ($eventList as $singleEvent) {
            foreach ($singleEvent->eventSchedule as $seminarDay) {
                if (!isset($seminarDay->schedule) || $seminarDay->schedule == null) {
                    continue;
                }

                $color = $singleEvent->event_status == 'confirm' ? $seminarPlannerSetting->planned_calendar_background : ($singleEvent->event_status == 'cancel' ? $seminarPlannerSetting->cancel_seminars_background : (($seminarDay->schedule->locationConflicted == 1 || $seminarDay->schedule->trainerConflicted == 1) ? $seminarPlannerSetting->new_seminar_calendar_background : $seminarPlannerSetting->draft_calendar_background));

                $seminar = new stdClass();
                $seminar->title = $singleEvent->event_name . ' ' . CustomFunction::customTrans("seminarPlanner.day") . " - " . $seminarDay->schedule->event_days;
                $seminar->event_name = $singleEvent->event_name;
                $seminar->planned_qualification_id = $singleEvent->planned_qualification_id;
                $seminar->qualification = $singleEvent->plannedQualification;
                $seminar->LocationName = isset($seminarDay->schedule->scheduleLocation) && !empty($seminarDay->schedule->scheduleLocation) ? $seminarDay->schedule->scheduleLocation->LocationName : "";
                $seminar->start = $seminarDay->schedule->schedule_date;
                $seminar->end = $seminarDay->schedule->schedule_date;
                $seminar->color = $color;
                $seminar->allDay = True;
                $seminar->id = $singleEvent->id . "-" . $seminarDay->schedule->id;
                $seminar->event_id = $singleEvent->id;
                $seminar->blueprint_id = $singleEvent->blueprint_id;
                $seminar->className = "plannedSeminar_" . $singleEvent->id;
                $seminar->event_days = $seminarDay->schedule->event_days;
                $seminar->event_schedule = $singleEvent->eventSchedule;
                $seminar->event_status = $singleEvent->event_status;
                $seminar->locationConflicted = $seminarDay->schedule->locationConflicted;
                $seminar->trainerConflicted = $seminarDay->schedule->trainerConflicted;
                $seminar->detailTrainerConflictMessage = $seminarDay->schedule->detailTrainerConflictMessage;
                $seminar->detailLocationConflictMessage = $seminarDay->schedule->detailLocationConflictMessage;

                array_push($calendarEvents, $seminar);
            }
        }

        return $calendarEvents;
    }

    // Update schedule dates if changes on dates

    function updatePlannedSeminarsSchedule() {
        $inputData = Input::all();
        $plannedSeminar = PlannedEvent::where('id', $inputData['blueprintEventId'])->with("eventSchedule.schedule")->first();
        $seminarStartDateBeforeMove = $plannedSeminar->event_startdate;
        $seminarEndDateBeforeMove = $plannedSeminar->event_enddate;

        foreach ($plannedSeminar->eventSchedule as $seminarSchedule) {
            $scheduleDateIndex = array_search($seminarSchedule->schedule->id, array_column($inputData["schedules"], 'scheduleId'));
            $schedule = PlannedSchedule::with("eventScheduleSlot")->where('id', $seminarSchedule->schedule->id)->first();
            $schedule->updateConflictedSchedulesBeforeDelete();
            $schedule->locationConflicted = 0;
            $schedule->detailLocationConflictMessage = "";
            $schedule->schedule_date = $inputData["schedules"][$scheduleDateIndex]['scheduleDate'];

            $schedule->save();

            // Remove current schedule existing conflict and remove currnt schedule from its condlicting schedule as well
            // $this->clearLocationConflict($schedule->id);
            // Check If schedule location is conflicted or resolved with other

            $conflictResult = $this->checkLocationConflict($schedule->LocationID, $schedule->id, $schedule->schedule_date, $inputData['blueprintEventId']);
            if ($conflictResult['type'] == "danger") {
                // commented as already handled in checkLocationConflict
                //$schedule->locationConflicted = 1;
                $schedule->detailLocationConflictMessage = $conflictResult["detailedMessage"];
                //$schedule->conflictedScheduleIds = $conflictResult["conflictedScheduleIds"];
                $schedule->save();
            } else {
                $schedule->locationConflicted = 0;
                $schedule->detailLocationConflictMessage = "";
                $schedule->save();
            }

            // Check If slot trainer is conflicted with other Trainer
            foreach ($schedule->eventScheduleSlot as $scheduleSlot) {
                $slot = PlannedScheduleSlot::find($scheduleSlot->schedule_slotID);

                // Remove current slot existing conflict and remove currnt slot from its condlicting schedule as well
                $this->clearSlotConflict($slot->schedule_slotID);

                $conflictResult = $this->checkTrainerConflict($slot->schedule_slotID, $schedule->id, $slot->trainer, $inputData['blueprintEventId']);
                if ($conflictResult['type'] == "danger") {
                    $schedule->trainerConflicted = 1;
                    $schedule->detailTrainerConflictMessage = $conflictResult["detailedMessage"];
                    $schedule->conflictedSlotIds = $this->addConflictedScheduleSlotId($schedule->conflictedSlotIds, $conflictResult["conflictedSlotIds"]);
                    //$schedule->conflictedScheduleIds = $conflictResult["conflictedScheduleIds"];
                    $schedule->save();


                    // Mark slot as conflicted
                    $slot->trainerConflicted = 1;
                    $slot->detailTrainerConflictMessage = $conflictResult["detailedMessage"];
                    $slot->conflictedSlotIds = $conflictResult["conflictedSlotIds"];
                    $slot->save();
                } else {
                    $slot->trainerConflicted = 0;
                    $slot->detailTrainerConflictMessage = "";
                    $slot->save();
                }
            }

            // Check if all conflicted slots are clear or not
            $conflictedSlots = PlannedScheduleSlot::where("ScheduleID", $schedule->id)->where("trainerConflicted", 1)->get()->toArray();
            if (count($conflictedSlots) == 0) {
                $schedule->trainerConflicted = 0;
                $schedule->detailTrainerConflictMessage = "";
                $schedule->save();

                // Update for the return object
                $seminarSchedule->schedule->trainerConflicted = 0;
                $seminarSchedule->schedule->detailTrainerConflictMessage = "";
            }

            $seminarSchedule->schedule->schedule_date = $inputData["schedules"][$scheduleDateIndex]['scheduleDate'];
        }

        // Update start and End date of the new draft event
        $event_start_range = PlannedEventSchedule::where("event_id", $plannedSeminar->id)->leftjoin("planned_schedule", function ($join) {
                    $join->on("planned_event_schedule.schedule_id", "=", "planned_schedule.id");
                })->orderBy('planned_schedule.schedule_date', 'asc')->first();

        $event_end_range = PlannedEventSchedule::where("event_id", $plannedSeminar->id)->leftjoin("planned_schedule", function ($join) {
                    $join->on("planned_event_schedule.schedule_id", "=", "planned_schedule.id");
                })->orderBy('planned_schedule.schedule_date', 'desc')->first();

        $plannedSeminar->event_startdate = date('Y-m-d', strtotime($event_start_range->schedule_date));
        $plannedSeminar->event_enddate = date('Y-m-d', strtotime($event_end_range->schedule_date));
        $plannedSeminar->moveReason = $inputData["moveReason"];
        $plannedSeminar->save();

        // make entry in log table
        $plannedSeminar->seminarStartDateBeforeMove = $seminarStartDateBeforeMove;
        $plannedSeminar->seminarEndDateBeforeMove = $seminarEndDateBeforeMove;
        $this->seminarActionsLog($plannedSeminar, "move-seminar");

        //

        return PlannedEvent::where('id', $plannedSeminar->id)->with(["eventSchedule.schedule", "eventSchedule.schedule.scheduleLocation"])->first();
//        return $plannedSeminar;
    }

    // Get schedules and slots of planned events to assign location and trainnes
    function getScheduleSlotPlannedEvent($plannedEvent = 0) {
        $plannedSeminar = PlannedEvent::where('id', $plannedEvent)
                ->with(["eventSchedule.schedule", "eventSchedule.schedule.scheduleLocation",
                    "eventSchedule.schedule.eventScheduleSlot" => function ($query) {
                        $query->get(["planned_schedule_slot.*",
                            DB::raw('(select group_concat(concat(PersonID, "-", TRIM(FirstName), " ", TRIM(LastName)) SEPARATOR ", ") as trainerName from person WHERE CONCAT(",", planned_schedule_slot.trainer ,"," ) LIKE CONCAT("%,", PersonID ,",%")) as trainers ')
                        ]);
                    }, "eventSchedule.schedule.eventScheduleSlot.slotRoom"
                ])
                ->first();
        return $plannedSeminar;
    }

    // Assign location to schedule
    function assignLocationToSchedule($locationId, $scheduleId, $scheduleDate) {
        $eventId = Input::get("eventId");
        $schedule = PlannedSchedule::find($scheduleId);
        $data = PlannedScheduleSlot::where('ScheduleID', $scheduleId)->update(['roomId' => 0]);
        if ($schedule->LocationID == $locationId) {
            $result["type"] = "error";
            $result["message"] = trans("seminarPlanner.locationAlreadyAssignToSchedule");
            return $result;
        }
        // Remove current schedule existing conflict and remove currnt schedule from its condlicting schedule as well
        $this->clearLocationConflict($schedule->id);

        $conflictResult = $this->checkLocationConflict($locationId, $schedule->id, $schedule->schedule_date, $eventId);
        if ($conflictResult['type'] == "danger") {
            $schedule->locationConflicted = 1;
            $schedule->detailLocationConflictMessage = $conflictResult["detailedMessage"];
            $schedule->LocationID = $locationId;
            $schedule->save();
        } else {
            $schedule->locationConflicted = 0;
            $schedule->detailLocationConflictMessage = "";
            $schedule->LocationID = $locationId;
            $schedule->save();
        }
        $plannedEvents = PlannedEvent::where('id', $eventId)
                ->with(["eventSchedule.schedule", "eventSchedule.schedule.scheduleLocation",
                    "eventSchedule.schedule.eventScheduleSlot" => function ($query) {
                        $query->get(["planned_schedule_slot.*",
                            DB::raw('(select group_concat(concat(PersonID, "-", TRIM(FirstName), " ", TRIM(LastName)) SEPARATOR ", ") as trainerName from person WHERE CONCAT(",", planned_schedule_slot.trainer ,"," ) LIKE CONCAT("%,", PersonID ,",%")) as trainers ')
                        ]);
                    }, "eventSchedule.schedule.eventScheduleSlot.slotRoom"
                ])
                ->first();

        $result["plannedEvent"] = $plannedEvents;
        $result["conflictStatus"] = $conflictResult['type'];
        $result["message"] = $conflictResult['type'] == "danger" ? trans("seminarPlanner.assignLocationWithConflict") : trans("seminarPlanner.assignLocationSuccessFully");
        $result["type"] = "success";

        return $result;
    }

    // Assign trainer to slot
    function assignTrainerToSlot($slotId, $scheduleId, $trainerId) {
        $eventId = Input::get("eventId");
        $schedule = PlannedSchedule::find($scheduleId);
        $slot = PlannedScheduleSlot::find($slotId);
        // add new trainer if any
        $slotTrainers = explode(",", $slot->trainer);
        if (in_array($trainerId, $slotTrainers)) {
            $result["type"] = "error";
            $result["message"] = trans("seminarPlanner.trainerAlreadyAssignToSlot");
            ;
            return $result;
        } else {
            array_push($slotTrainers, $trainerId);
            $trainerId = implode(",", $slotTrainers);
        }
        // Remove current slot existing conflict and remove currnt slot from its condlicting schedule as well
        $this->clearSlotConflict($slot->schedule_slotID);

        $conflictResult = $this->checkTrainerConflict($slot->schedule_slotID, $schedule->id, $trainerId, $eventId);
        if ($conflictResult['type'] == "danger") {
            $scheduleObj = PlannedSchedule::find($scheduleId);
            $slotObj = PlannedScheduleSlot::find($slotId);

            $scheduleObj->trainerConflicted = 1;
            $scheduleObj->conflictedSlotIds = $this->addConflictedScheduleSlotId($schedule->conflictedSlotIds, $conflictResult["conflictedSlotIds"]);

            $scheduleObj->detailTrainerConflictMessage = $conflictResult["detailedMessage"];
            $scheduleObj->save();

            // Mark slot as conflicted
            $slotObj->trainerConflicted = 1;
            $slotObj->detailTrainerConflictMessage = $conflictResult["detailedMessage"];
            $slotObj->trainer = $trainerId;
            $slotObj->conflictedSlotIds = $conflictResult["conflictedSlotIds"];
            $slotObj->save();
        } else {
            $slotObj = PlannedScheduleSlot::find($slotId);
            $slotObj->trainerConflicted = 0;
            $slotObj->detailTrainerConflictMessage = "";
            $slotObj->trainer = $trainerId;
            $slotObj->save();
        }

        // Check if all conflicted slots are clear or not
        $conflictedSlots = PlannedScheduleSlot::where("ScheduleID", $schedule->id)->where("trainerConflicted", 1)->get()->toArray();
        if (count($conflictedSlots) == 0) {
            $schedule->trainerConflicted = 0;
            $schedule->detailTrainerConflictMessage = "";
            $schedule->save();
        }

        $plannedEvents = PlannedEvent::where('id', $eventId)
                ->with(["eventSchedule.schedule", "eventSchedule.schedule.scheduleLocation",
                    "eventSchedule.schedule.eventScheduleSlot" => function ($query) {
                        $query->get(["planned_schedule_slot.*",
                            DB::raw('(select group_concat(concat(PersonID, "-", TRIM(FirstName), " ", TRIM(LastName)) SEPARATOR ", ") as trainerName from person WHERE CONCAT(",", planned_schedule_slot.trainer ,"," ) LIKE CONCAT("%,", PersonID ,",%")) as trainers ')
                        ]);
                    }, "eventSchedule.schedule.eventScheduleSlot.slotRoom"
                ])
                ->first();

        $result["plannedEvent"] = $plannedEvents;
        $result["conflictStatus"] = $conflictResult['type'];
        $result["message"] = $conflictResult['type'] == "danger" ? trans("seminarPlanner.assignTrainerWithConflict") : trans("seminarPlanner.assignTrainerSuccessFully");
        $result["type"] = "success";
        return $result;
    }

    function assignRoomToSlot($slotId, $scheduleId, $roomId) {
        $eventId = Input::get("eventId");
        $schedule = PlannedSchedule::find($scheduleId);
        $slot = PlannedScheduleSlot::find($slotId);
        if ($slot->roomId == $roomId) {
            $result["type"] = "error";
            $result["message"] = trans("seminarPlanner.roomAlreadyAssignToSlot");
            return $result;
        }

        $slot->roomId = $roomId;
        if (!$slot->save()) {
            $result["type"] = "error";
            $result["message"] = "";
            return $result;
        }

        $plannedEvents = PlannedEvent::where('id', $eventId)
                ->with(["eventSchedule.schedule", "eventSchedule.schedule.scheduleLocation",
                    "eventSchedule.schedule.eventScheduleSlot" => function ($query) {
                        $query->get(["planned_schedule_slot.*",
                            DB::raw('(select group_concat(concat(PersonID, "-", TRIM(FirstName), " ", TRIM(LastName)) SEPARATOR ", ") as trainerName from person WHERE CONCAT(",", planned_schedule_slot.trainer ,"," ) LIKE CONCAT("%,", PersonID ,",%")) as trainers ')
                        ]);
                    }, "eventSchedule.schedule.eventScheduleSlot.slotRoom"
                ])
                ->first();

        $result["plannedEvent"] = $plannedEvents;
        $result["message"] = trans("seminarPlanner.assignRoomSuccessFully");
//        $result["conflictStatus"] = $conflictResult['type'];
//        $result["message"] = $conflictResult['type'] == "danger" ? trans("seminarPlanner.assignLocationWithConflict") : trans("seminarPlanner.assignLocationSuccessFully");
        $result["type"] = "success";

        return $result;
    }

    // check if location is conflict with other schedule
    function checkLocationConflict($locationId, $scheduleId, $scheduleDate, $event = 0) {
        if ($event != 0)
            $event = PlannedEvent::find($event);
        $schedule = PlannedSchedule::find($scheduleId);
        $conflictedScheduleIds = [];
        $conflictedSchedule = PlannedSchedule::with(["ScheduleEvent", "ScheduleEvent.event", "scheduleLocation"])->where("schedule_date", "=", date("Y-m-d", strtotime($scheduleDate)))
                        ->where("LocationID", "=", $locationId)
                        //->where("planned_schedule.id", "!=", $scheduleId) // removed as per new code1
                        ->get()->toArray();
        if (count($conflictedSchedule) > 0) {
            $detailedMessage = "";
            foreach ($conflictedSchedule as $schedule) {
                // new code1 : rupesh
                // needed to refactor as conflicting states were invalid
                // every schedule should have ids of all conflicting schedules exept own 
                // add conflicts on each schedule exept it's own id
                // this is being used in cleaning the data when event is deleted
                // TO DO : do the same for trainer conflict logic

                $ids = [];
                foreach ($conflictedSchedule as $sc) {
                    if ($sc['id'] != $schedule['id']) {
                        $ids[] = $sc['id'];
                    }
                }
                if (empty($ids)) {
                    continue;
                }
                // new code1 ends /////

                $detailedMessage .= "#eventTextForConflictMessage#" . " " . $schedule['schedule_event']['event']['event_name'] . " " . "#locationConflictMessage#" . " " . $schedule["schedule_location"]["LocationName"] . " " . "#locationConflictMessageOnDate#" . " " . format_date($scheduleDate) . " | ";
                array_push($conflictedScheduleIds, $schedule['id']);
                // Updated conflicted schedule with current schedule details
                $plannedSchedule = PlannedSchedule::find($schedule['id']);
                // commented as errornious : rupesh
                //$plannedSchedule->conflictedScheduleIds = $this->addConflictedScheduleSlotId($plannedSchedule->conflictedScheduleIds, $schedule['id']);
                // updating as per new code1
                $plannedSchedule->conflictedScheduleIds = implode(',', $ids);
                $plannedSchedule->detailLocationConflictMessage = "#planned_seminar# " . $event->event_name . " #has_already_been_on_location# " . $schedule["schedule_location"]["LocationName"] . " #on_date# " . format_date($scheduleDate);
                $plannedSchedule->locationConflicted = 1;
                $plannedSchedule->save();
            }
            $result["type"] = "danger";
            $result["message"] = "#schedule_date_exist_on_locations#";
            $result["conflictedSchedule"] = $conflictedSchedule;
            $result["conflictedScheduleIds"] = implode(",", array_unique($conflictedScheduleIds));
            $result["detailedMessage"] = $detailedMessage; //substr($detailedMessage, 0, -2);
            return $result;
        } else {
            $result["type"] = "success";
            $result["message"] = "#location_assign_to_schedule#";
        }

        return $result;
    }

    // check if Trainer is conflict with other schedule slots
    function checkTrainerConflict($slotId, $scheduleId, $trainerIds, $eventId = 0) {
        if ($eventId != 0)
            $event = PlannedEvent::find($eventId);
        $slot = PlannedScheduleSlot::find($slotId);
        $schedule = PlannedSchedule::find($scheduleId);
        $conflictedScheduleSlots = [];
        $detailedMessage = "";
        $conflictedSlotIds = [];
        $conflictedScheduleIds = [];


        // If multiplet trainer assign
        $trainers = explode(",", $trainerIds);
        for ($j = 0; $j < count($trainers); $j++) {
            if (!empty($trainers[$j])) {
                $person = Person::find($trainers[$j]);
                if (count($person) > 0) {
//                dd($person->FirstName);

                    $conflictedSlot = PlannedSchedule::join("planned_schedule_slot", "planned_schedule_slot.ScheduleID", "=", "planned_schedule.id")
                                    ->whereRaw("FIND_IN_SET(" . $trainers[$j] . ",planned_schedule_slot.trainer)")
                                    ->where("planned_schedule_slot.start_time", "=", $slot->start_time)
                                    ->where("planned_schedule_slot.end_time", "=", $slot->end_time)
                                    ->where("schedule_date", "=", $schedule->schedule_date)
                                    ->where("planned_schedule.id", "!=", $scheduleId)
                                    ->groupBy("planned_schedule.id")
                                    ->with(["ScheduleEvent", "ScheduleEvent.event"])
                                    ->get(["planned_schedule.*", "planned_schedule_slot.schedule_slotID"])->toArray();

                    foreach ($conflictedSlot as $singleSlot) {
                        $detailedMessage .= "#eventTextForConflictMessage#" . " " . $singleSlot['schedule_event']['event']['event_name'] . " " . "#alreadyBookTrainerToolTipText#" . " " . $person->FirstName . " " . $person->LastName . " #for_slot# " . $slot->start_time . " - " . $slot->end_time . " #on_date# " . format_date($singleSlot['schedule_date']) . " | ";
                        array_push($conflictedSlotIds, $singleSlot['schedule_slotID']);
                        array_push($conflictedScheduleIds, $singleSlot['id']);
                        // Update the conflicted schedule Slot
                        $plannedScheduleSlot = PlannedScheduleSlot::find($singleSlot['schedule_slotID']);
                        if (count($plannedScheduleSlot) > 0) {
                            $plannedScheduleSlot->conflictedSlotIds = $this->addConflictedScheduleSlotId($plannedScheduleSlot->conflictedSlotIds, $slot->schedule_slotID);
                            $plannedScheduleSlot->trainerConflicted = 1;
                            $detailedMessageForConflictedSLot = '#eventTextForConflictMessage#' . " " . !empty($event) ? $event->event_name : '' . " " . "#alreadyBookTrainerToolTipText#" . " " . $person->FirstName . " " . $person->LastName . " #for_slot# " . $slot->start_time . " - " . $slot->end_time . " #on_date# " . format_date($singleSlot['schedule_date']);
                            $plannedScheduleSlot->detailTrainerConflictMessage = $this->addConflictedScheduleSlotMessage($plannedScheduleSlot->detailTrainerConflictMessage, $detailedMessageForConflictedSLot);
                            $plannedScheduleSlot->save();
                        }

                        $plannedSchedule = PlannedSchedule::find($singleSlot['id']);
                        if (count($plannedSchedule) > 0) {
                            $plannedSchedule->conflictedSlotIds = $this->addConflictedScheduleSlotId($plannedSchedule->conflictedSlotIds, $slot->schedule_slotID);
                            //                      $plannedSchedule->conflictedScheduleIds = $this->addConflictedScheduleSlotId($plannedSchedule->conflictedScheduleIds, $schedule->id);
                            $plannedSchedule->trainerConflicted = 1;
                            // $plannedSchedule->detailTrainerConflictMessage = trans("seminarPlanner.trainerConflictedToolTips");
                            $plannedSchedule->detailTrainerConflictMessage = '#trainerConflictedToolTips#';
                            $plannedSchedule->save();
                        }
                    }

                    if (count($conflictedSlot) > 0) {
                        array_push($conflictedScheduleSlots, $conflictedSlot);
                    }
                }
            }
        }


        if (count($conflictedScheduleSlots) > 0) {

            $result["type"] = "danger";
            $result["message"] = trans("seminarPlanner.trainer_already_occupied");
            $result["detailedMessage"] = substr($detailedMessage, 0, -2);
            $result["conflictedSlotIds"] = implode(",", array_unique($conflictedSlotIds));
            $result["conflictedScheduleIds"] = implode(",", array_unique($conflictedScheduleIds));
            return $result;
        } else {
            $result["type"] = "success";
            $result["message"] = trans("seminarPlanner.tainer_assign_to_slot");
        }

        return $result;
    }

    //clearLocationConflict
    function clearLocationConflict($scheduleId) {
        $plannedSchedule = PlannedSchedule::find($scheduleId);
        $conflictedId = explode(",", $plannedSchedule->conflictedScheduleIds);

        /*
          clear current conflicted ids for schedule
         */

        $plannedSchedule->conflictedScheduleIds = "";
        $plannedSchedule->locationConflicted = 0;
        $plannedSchedule->save();

        /*
          remove current schedule from conflicted schedules
         */
        foreach ($conflictedId as $ids) {
            $currentSchedule = Array($plannedSchedule->id);
            $schedule = PlannedSchedule::find($ids);
            if (count($schedule) > 0) {
                $removeCurrentScheduleId = array_diff(str_getcsv($schedule->conflictedScheduleIds), $currentSchedule);
                $conflictedScheduleIds = implode(',', $removeCurrentScheduleId);
                $schedule->conflictedScheduleIds = $conflictedScheduleIds;
                if (empty($conflictedScheduleIds)) {
                    $schedule->locationConflicted = 0;
                }
                $schedule->save();
            }
        }

        return true;
    }

    //clearLocationConflict
    function clearSlotConflict($SlotId) {
        $plannedScheduleSlot = PlannedScheduleSlot::find($SlotId);
        if ($plannedScheduleSlot) {
            $plannedSchedule = PlannedSchedule::find($plannedScheduleSlot->ScheduleID);
            $conflictedIdList = $plannedScheduleSlot->conflictedSlotIds;
            $conflictedId = explode(",", $plannedScheduleSlot->conflictedSlotIds);

            /*
              clear current conflicted ids for schedule
             */

            $plannedScheduleSlot->conflictedSlotIds = "";
            $plannedScheduleSlot->trainerConflicted = 0;
            $plannedScheduleSlot->save();

            $plannedSchedule->conflictedSlotIds = $this->removeConflictedScheduleSlotId($plannedSchedule->conflictedSlotIds, $conflictedIdList);
            if (empty($plannedSchedule->conflictedSlotIds))
                $plannedSchedule->trainerConflicted = 0;
            $plannedSchedule->save();

            /*
              remove current schedule from conflicted schedules
             */
            foreach ($conflictedId as $ids) {
                $slot = PlannedScheduleSlot::find($ids);
                if (count($slot) > 0) {
                    $conflictedSlotIds = $this->removeConflictedScheduleSlotId($slot->conflictedSlotIds, $plannedScheduleSlot->schedule_slotID);
                    $slot->conflictedSlotIds = $conflictedSlotIds;
                    if (empty($conflictedSlotIds)) {
                        $slot->trainerConflicted = 0;
                    }
                    $slot->save();

                    // Remove that Id from slots schedule

                    $slotSchedule = PlannedSchedule::find($slot->ScheduleID);
                    $conflictedSlotScheduleIds = $this->removeConflictedScheduleSlotId($slotSchedule->conflictedSlotIds, $plannedScheduleSlot->schedule_slotID);
                    $slotSchedule->conflictedSlotIds = $conflictedSlotScheduleIds;
                    if (empty($conflictedSlotScheduleIds)) {
                        $slotSchedule->trainerConflicted = 0;
                    }
                    $slotSchedule->save();
                }
            }
        }

        return true;
    }

    // add new item into comma seperated sting column
    function addConflictedScheduleSlotId($alreadyConflictedIds, $slotIdArray) {
        $slotIds = explode(",", $slotIdArray);
        foreach ($slotIds as $slotId) {
            $conflictedIds = array_filter(explode(",", $alreadyConflictedIds));
            if (!in_array($slotId, $conflictedIds))
                array_push($conflictedIds, $slotId);
        }
        $ids = implode(",", $conflictedIds);
        return $ids;
    }

    // add new item into comma seperated sting column
    function addConflictedScheduleSlotMessage($alreadyConflictedMessages, $slotMessage) {
        $conflictedMessages = explode("|", $alreadyConflictedMessages);
        if (!in_array($slotMessage, $conflictedMessages))
            array_push($conflictedMessages, $slotMessage);
        $messages = implode("|", $conflictedMessages);
        return $messages;
    }

    // remove new item into comma seperated sting column
    function removeConflictedScheduleSlotId($alreadyConflictedIds, $toBeDeletedId) {
        $currentId = explode(",", $toBeDeletedId);
        $removeCurrentScheduleId = array_diff(str_getcsv($alreadyConflictedIds), $currentId);
        $conflictedSlotIds = implode(',', $removeCurrentScheduleId);
        return $conflictedSlotIds;
    }

    //  Confirm seminar
    public function deleteSeminar($eventId) {
        $seminar = PlannedEvent::find($eventId)->delete();
        $schedules = PlannedEventSchedule::where("event_id", "=", $eventId)->get();
        foreach ($schedules as $ss) {
            $schedule = PlannedSchedule::find($ss->schedule_id);
            $schedule->updateConflictedSchedulesBeforeDelete();
            $schedule->delete();
            /* if($ss->schedule){
              $schedules = $ss->schedule->get();
              foreach ($schedules as $key => $sched) {

              $sched->updateConflictedSchedulesBeforeDelete();
              $sched->delete();
              }
              } */
            //$ss->schedule->delete();
            //$ss->delete();
        }

        if ($seminar) {
            $result["type"] = "success";
            $result["message"] = trans("seminarPlanner.deleteSeminarConfirmation");
        } else {
            $result["type"] = "error";
            $result["message"] = trans("general.error");
        }
        return $result;
    }

    public function confirmSeminar($eventId) {
        $seminar = PlannedEvent::find($eventId);
        $seminar->event_status = "confirm";
        $seminar->confirm_by = Auth::user()->UserID;
        $seminar->confirm_at = date("Y-m-d");
        
        if (!$seminar->save()) {
            $result["type"] = "error";
            $result["message"] = trans("general.error");
        } else {
             \App\WorkflowManager\EventManager::trigger('send-email-to-trainer-on-event-confirmed', $seminar);
            $result["type"] = "success";
            $result["message"] = trans("seminarPlanner.successSeminarConfirmation");
        }


//        $plannedSeminar = PlannedSchedule::whereIn('id', function($query) use($eventId){
//                                        $query->select('schedule_id')
//                                        ->from('planned_event_schedule')
//                                        ->where('event_id', $eventId);
//                                    })->where(function ($query) {
//                                            $query->where('locationConflicted', '=', 1)
//                                                  ->orWhere('trainerConflicted', '=', 1);
//                                        })->get();
//
//        // Check If there is any conflict in schedule
//        if(count($plannedSeminar) > 0){
////            $plannedEvents = PlannedEvent::where('id', $eventId)
////                        ->with(["eventSchedule.schedule", "eventSchedule.schedule.scheduleLocation",
////                            "eventSchedule.schedule.eventScheduleSlot" => function ($query) {
////                                $query->get(["planned_schedule_slot.*",
////                                    DB::raw('(select group_concat(concat(TRIM(FirstName), " ", TRIM(LastName)) SEPARATOR ", ") as trainerName from Person WHERE CONCAT(",", planned_schedule_slot.trainer ,"," ) LIKE CONCAT("%,", PersonID ,",%")) as trainers ')
////                                ]);
////                            }
////                        ])
////                        ->first();
////
////            $result["plannedEvent"] = $plannedEvents;
//            $result["message"] = trans("seminarPlanner.assignTrainerSuccessFully");
//            $result["type"] = "danger";
//        }else{
//            $seminar = PlannedEvent::find($eventId);
//            $seminar->event_status = "confirm";
//            $seminar->confirm_by = Auth::user()->UserID;
//            $seminar->confirm_at = date("Y-m-d");
//
//            if(!$seminar->save())
//            {
//                $result["type"] = "error";
//                $result["message"] = trans("general.error");;
//            }else{
//                $result["type"] = "success";
//                $result["message"] = trans("seminarPlanner.successSeminarConfirmation");;
//            }
//        }

        return $result;
    }

    //  Confirm seminar
    public function cancelSeminar($eventId) {
        $inputData = Input::all();
        $seminar = PlannedEvent::find($eventId);
        $seminar->event_status = "cancel";
        $seminar->cancelReason = $inputData['cancelReason'];

        if (!$seminar->save()) {
            $result["type"] = "error";
            $result["message"] = trans("general.error");
        } else {
            // Check if there is participant register
            $participants = EventAttendees::with(["person" => function ($query) {
                            $query->get(["PersonID", "Email", "FirstName", "LastName"]);
                        }])->where('event_id', $eventId)->where("ContactStatusID", 1)->get([
                "event_id",
                "person_id",
                "ContactStatusID"
            ]);
            // Get All trainer for that seminar
            $scheduleLocationIds = PlannedEventSchedule::where('event_id', $eventId)
                    ->leftjoin("planned_schedule", function ($join) {
                        $join->on('planned_event_schedule.schedule_id', '=', 'planned_schedule.id');
                    })
                    ->groupBy("planned_event_schedule.event_id")
                    ->get([
                DB::Raw("GROUP_CONCAT(planned_schedule.LocationID) as locations"),
                DB::Raw("GROUP_CONCAT(planned_schedule.id) as schedules")
            ]);


            // Location Emails
            $locationId = !empty($scheduleLocationIds) ? explode(",", $scheduleLocationIds[0]->locations) : [];
            $locations = Location::whereIn("LocationID", array_unique($locationId))->get(['LocationID as id', 'LocationName as displayName', 'Email']);

            // Trainer Emails
            $scheduleIds = !empty($scheduleLocationIds) ? array_unique(explode(",", $scheduleLocationIds[0]->schedules)) : [];

            $trainersIds = PlannedScheduleSlot::whereIn('ScheduleID', $scheduleIds)
                    ->get([
                DB::Raw("GROUP_CONCAT(planned_schedule_slot.trainer) as trainersId")
            ]);

            $trainers = Person::whereIn('PersonID', array_unique(explode(",", $trainersIds[0]->trainersId)))->get([
                "PersonID", "Email", DB::Raw("CONCAT(FirstName, ' ', LastName) as displayName")
            ]);

            // make entry in log table
            $this->seminarActionsLog($seminar, "cancel-seminar");
            $result["type"] = "success";
            $result["participants"] = $participants;
            $result["trainers"] = $trainers;
            $result["locations"] = $locations;
            $result["users_list"] = $this->getLevel2UsersDetail($eventId);
            $result["message"] = trans("seminarPlanner.successSeminarCancellation");
            ;
        }

        return $result;
    }

    public function getLevel2UsersDetail($event_id) {
        $level2_users = \App\Models\AllocationSettings::join('role', 'allocation_setting.modelLevel', '=', 'role.LevelValueID')
                ->join('user_allocation_role', 'role.RoleID', '=', 'user_allocation_role.RoleID')
                ->join('person', 'user_allocation_role.UserID', '=', 'person.UserID')
                ->where('allocation_setting.parentID', '=', Auth::user()->LevelValueID)
                ->where('allocation_setting.eventID', '=', $event_id)
                ->get()
                ->implode('PersonID', ",");
        return implode(',', array_unique(explode(',', $level2_users)));
    }

    // Remove trainer from the seminar slot
    public function removeTrainer($scheduleId, $slotId, $trainerId) {
        $eventId = Input::get("eventId");
        $scheduleMain = PlannedSchedule::find($scheduleId);
        $slotMain = PlannedScheduleSlot::find($slotId);

        $numberOfTrainers = explode(",", $slotMain->trainer);
        if (count($numberOfTrainers) == 1) {
            $result["message"] = trans("seminarPlanner.cantRemoveSingleTrainer");
            $result["type"] = "error";
            return $result;
        }

        $currentId = explode(",", $trainerId);
        $removeCurrentScheduleId = array_diff(str_getcsv($slotMain->trainer), $currentId);
        $trainerId = implode(',', $removeCurrentScheduleId);

        // Remove current slot existing conflict and remove currnt slot from its condlicting schedule as well
        $this->clearSlotConflict($slotMain->schedule_slotID);

        $conflictResult = $this->checkTrainerConflict($slotMain->schedule_slotID, $scheduleMain->id, $trainerId, $eventId);
        if ($conflictResult['type'] == "danger") {

            $schedule = PlannedSchedule::find($scheduleId);
            $slot = PlannedScheduleSlot::find($slotId);

            $schedule->trainerConflicted = 1;
            $schedule->conflictedSlotIds = $this->addConflictedScheduleSlotId($scheduleMain->conflictedSlotIds, $conflictResult["conflictedSlotIds"]);

            $schedule->detailTrainerConflictMessage = $conflictResult["detailedMessage"];
            $schedule->save();

            // Mark slot as conflicted
            $slot->trainerConflicted = 1;
            $slot->detailTrainerConflictMessage = $conflictResult["detailedMessage"];
            $slot->trainer = $trainerId;
            $slot->conflictedSlotIds = $conflictResult["conflictedSlotIds"];
            $slot->save();
        } else {
            $slot = PlannedScheduleSlot::find($slotId);
            $slot->trainerConflicted = 0;
            $slot->detailTrainerConflictMessage = "";
            $slot->trainer = $trainerId;
            $slot->save();
        }

        // Check if all conflicted slots are clear or not
        $conflictedSlots = PlannedScheduleSlot::where("ScheduleID", $scheduleMain->id)->where("trainerConflicted", 1)->get()->toArray();
        if (count($conflictedSlots) == 0) {
            $scheduleMain->trainerConflicted = 0;
            $scheduleMain->detailTrainerConflictMessage = "";
            $scheduleMain->save();
        }

        $plannedEvents = PlannedEvent::where('id', $eventId)
                ->with(["eventSchedule.schedule", "eventSchedule.schedule.scheduleLocation",
                    "eventSchedule.schedule.eventScheduleSlot" => function ($query) {
                        $query->get(["planned_schedule_slot.*",
                            DB::raw('(select group_concat(concat(PersonID, "-", TRIM(FirstName), " ", TRIM(LastName)) SEPARATOR ", ") as trainerName from person WHERE CONCAT(",", planned_schedule_slot.trainer ,"," ) LIKE CONCAT("%,", PersonID ,",%")) as trainers ')
                        ]);
                    }, "eventSchedule.schedule.eventScheduleSlot.slotRoom"
                ])
                ->first();

        $result["plannedEvent"] = $plannedEvents;
        $result["message"] = trans("seminarPlanner.trainerRemoveSuccessfully");
        $result["type"] = "success";
        return $result;
    }

    public function getAllLocation() {
        return $query = Location::with("person")->get(
                [
                    DB::raw('location.*'),
                    DB::raw('(select count(*) from location_room) as room_count'),
                    DB::raw('(select MaxSize from room inner join location_room where location_room.RoomID=room.RoomID and location_room.LocationID=location.LocationID GROUP BY room.RoomName ORDER BY room.MaxSize DESC limit 1) as max_room')
        ]);
    }

    public function getActivitiesByID($event_id = 0, $id, $param) {
        if ($param == true) {
            return PlannedEvent::with(["eventTask.Task", "eventTask.task.priority", "eventTask.task.taskStatus", "eventTask.task.assignee"])->findOrFail($event_id);
        } else {
            return PlannedEvent::with(["eventTask" => function ($query) use($id) {
                            $query->leftjoin("task", "planned_event_task.task_id", "=", "task.TaskID");
                            $query->where('task.AssignedToUser', '=', $id);
                        }, "eventTask.Task", "eventTask.task.priority", "eventTask.task.taskStatus", "eventTask.task.assignee"])->findOrFail($event_id);
        }
    }

    public function savePlannedParticipantDetail($event_id = 0) {
        $custom_field = [
            'event_price',
            'planned_no_of_participant'
        ];

        $planned_event_obj = PlannedEvent::findOrFail($event_id);
        $planned_event_obj->fill(Input::only($custom_field));
        if ($planned_event_obj->save()) {
            return true;
        } else {
            return false;
        }
    }

    public function getNoOfPlannedParticipant($eventId = 0) {
        $revenue_calculate = PlannedSeminarRevenue::join('seminar_item', 'planned_seminar_revenue.item_id', '=', 'seminar_item.seminar_item_id')
                ->where("event_id", "=", $eventId)
                ->where("revenue_type", "=", 'fix')
                ->get([
            'planned_seminar_revenue.*',
            'seminar_item.*'
        ]);



        $variable_revenue_calculate = PlannedSeminarRevenue::join('seminar_item', 'planned_seminar_revenue.item_id', '=', 'seminar_item.seminar_item_id')
                ->where("event_id", "=", $eventId)
                ->where("revenue_type", "=", 'variable')
                ->get([
            'planned_seminar_revenue.*',
            'seminar_item.*'
        ]);

        $participantNo = PlannedEvent::where("id", "=", $eventId)->first();
        $totalAttendance = EventAttendees::where('event_id', '=', $eventId)->count();

        return compact('participantNo', 'revenue_calculate', 'totalAttendance', 'variable_revenue_calculate');
    }

    public function addPlannedParticipant($eventId) {
        $no_of_participant_planned = Input::get('no_of_participant_planned');
        $event_obj = PlannedEvent::findOrFail($eventId);
        $event_obj->planned_no_of_participant = $no_of_participant_planned;
        if (!$event_obj->save())
            return false;
        else
            return $event_obj;
    }

    public function getSchedulesByID($event_id = 0) {
//        \DB::enableQueryLog();
        return PlannedEvent::with(['eventSchedule' => function ($query) {
                                $query->join('planned_schedule', 'planned_event_schedule.schedule_id', '=', 'planned_schedule.id');
                                $query->orderBy('planned_schedule.schedule_date', 'ASC');
                                $query->get([
                                    'planned_event_schedule.*'
                                ]);
                            }, "eventSchedule.schedule", "eventSchedule.schedule.scheduleLocation", 'eventSchedule.schedule.eventScheduleSlot'])
                        ->where('planned_events.id', $event_id)
                        ->get(['planned_events.*']);
    }

    function deleteScheduleSlot($slotId) {
        $slot = PlannedScheduleSlot::where("schedule_slotID", "=", $slotId)->delete();

        if ($slot) {
            return true;
        } else {
            return false;
        }
    }

    function validateSchedule($schedule_id = 0) {

        $result = [];
        $slotCount = count(Input::get('start_time'));
        $scheduleDate = date('Y-m-d', strtotime(Input::get('schedule_date')));
        $slotStartTime = Input::get('start_time');
        $slotEndTime = Input::get('end_time');
        $locationID = Input::get('LocationID');

        if (Input::has("schedule_date") && !empty($slotCount)) {
            for ($k = 0; $k < $slotCount; $k++) {

//                My Updated Query for validation checkup
                $eventsForSameDateTimeLocation = PlannedEventSchedule::join("planned_schedule", "schedule_id", "=", "planned_schedule.id")
                                ->join("planned_schedule_slot", "ScheduleID", "=", "planned_schedule.id")
                                ->where("schedule_date", "=", $scheduleDate)
                                ->where("schedule.id", "!=", $schedule_id)
                                ->where("start_time", "=", date("H:i:s", strtotime($slotStartTime[$k])))
                                ->where("end_time", "=", date("H:i:s", strtotime($slotEndTime[$k])))
                                ->where("LocationID", "=", $locationID)
                                ->take(1)->get();

//                Old Query to check Validation
//                $eventsForSameDateTimeLocation = \DB::select("SELECT * FROM event_schedule
//                                            INNER JOIN schedule ON schedule_id = schedule.id
//                                            INNER JOIN schedule_slot ON ScheduleID = schedule.id AND schedule_date = '" . $scheduleDate . "'
//                                            AND schedule.id != " . $schedule_id . "  AND start_time = '" . date("H:i:s", strtotime($slotStartTime[$k])) . "'
//                                            AND end_time = '" . date("H:i:s", strtotime($slotEndTime[$k])) . "'
//                                            AND LocationID = '" . $locationID . "' LIMIT 1 ;");
                if (!empty($eventsForSameDateTimeLocation[0]) && count($eventsForSameDateTimeLocation[0]) >= 1) {
                    $result["type"] = "warning";
                    $result["flag"] = "1";
                    $result["message"] = trans("events.scheduleAnotherEventAtSameLocation");
                    $result["conflictScheduleId"] = $eventsForSameDateTimeLocation[0]->schedule_id;
                    $result["event"] = $eventsForSameDateTimeLocation[0]->event_id;
                    return $result;
                }
            }
        }
        return $result;
    }

    public function getDocumentsByID($event_id = 0) {
        return PlannedEvent::with(["eventDocument", "eventDocument.document", "eventDocument.document.documentCategory"])->findOrFail($event_id);
    }

    // Enter seminar action log in file as well as table
    function seminarActionsLog($seminar, $action) {
        $seminarData = PlannedEvent::where('id', $seminar->id)->first(['id', 'event_name', 'event_startdate', 'event_enddate', 'cancelReason', 'moveReason'])->toArray();
        if ($action == "move-seminar") {
            $seminarData['seminarStartDateBeforeMove'] = $seminar->seminarStartDateBeforeMove;
            $seminarData['seminarEndDateBeforeMove'] = $seminar->seminarEndDateBeforeMove;
        }
        $seminarLog = new SeminarActionsLog();
        $seminarLog->planned_seminar_id = $seminarData['id'];
        $seminarLog->actions = $action;
        $seminarLog->log_data = json_encode($seminarData);
        $seminarLog->save();

        $seminarDataText = http_build_query($seminarData, '', ', ');
        Log::useDailyFiles(storage_path() . '/logs/seminars.log');
        Log::info($seminarDataText);
    }

    // Create task for trainer and location for the seminar
    function createTaskForTrainerLocation($seminarId, $createTaskForTrainer, $createTaskForLocation, $action) {
        $plannedSeminar = PlannedEvent::find($seminarId);
        // Check if need to create task fot trainer
        if ($action == 'cancel-seminar') {
            $taskNameForTrainer = "Cancel Trainer";
            $taskNameForLocation = "Cancel Location";
            $taskNoteForTrainer = "Cancel trainers for all schedules of seminar '" . $plannedSeminar->event_name . "' start at " . format_date($plannedSeminar->event_startdate) . " AND End at " . format_date($plannedSeminar->event_enddate);
            $taskNoteForLocation = "Cancel Location for all schedules of seminar '" . $plannedSeminar->event_name . "' start at " . format_date($plannedSeminar->event_startdate) . " AND End at " . format_date($plannedSeminar->event_enddate);
        } else {
            $taskNameForTrainer = "Move Trainer";
            $taskNameForLocation = "Move Location";
            $taskNoteForTrainer = "Change-date for trainers of trainers for all schedules of seminar '" . $plannedSeminar->event_name . "' To start at " . format_date($plannedSeminar->event_startdate) . " AND End at " . format_date($plannedSeminar->event_enddate);
            $taskNoteForLocation = "Change-date for Location for all schedules of seminar '" . $plannedSeminar->event_name . "' To start at " . format_date($plannedSeminar->event_startdate) . " AND End at " . format_date($plannedSeminar->event_enddate);
        }
        if ($createTaskForTrainer == 1) {
            $plannedTask = new PlannedTask();
            $plannedTask->TaskName = $taskNameForTrainer;
            $plannedTask->TaskNote = $taskNoteForTrainer;
            $plannedTask->TaskEnd = date('Y:m:d H:i:s');
            $plannedTask->PriorityID = 2;
            $plannedTask->TaskStatusID = 3;
            $plannedTask->save();

            $plannedSeminar->load("eventTask");
            $plannedSeminar->eventTask()->insert(['task_id' => $plannedTask->TaskID]);
        }

        if ($createTaskForLocation == 1) {
            $plannedTask = new PlannedTask();
            $plannedTask->TaskName = $taskNameForLocation;
            $plannedTask->TaskNote = $taskNoteForLocation;
            $plannedTask->TaskBegin = date("Y:m:d H:i:s");
            $plannedTask->TaskEnd = date('Y:m:d H:i:s');
            $plannedTask->PriorityID = 2;
            $plannedTask->TaskStatusID = 3;
            $plannedTask->save();

            $plannedSeminar->load("eventTask");
            $plannedSeminar->eventTask()->insert(['task_id' => $plannedTask->TaskID]);
        }

        $result['type'] = "success";
        $result['message'] = "Task Created successfully";

        return $result;
    }

    public function seminarSeatAllocation($eventId) {
        $allocated_settings = AllocationSettings::query();
        $allocated_settings->with([
            'children',
            'getAttendees' => function ($query) use ($eventId) {
                $query->where('event_attendees.event_id', '=', $eventId);
            }
        ]);
        return $allocated_settings->where('modelLevel', '=', Auth::user()->LevelValueID)
                        ->where('eventID', '=', $eventId)
                        ->get();
    }

    public function getAllotmentData($eventId = 0) {
        $allocationdata = AllocationLevelValues::with([
                    'getAttendees' => function ($query) use ($eventId) {
                        $query->where('event_attendees.event_id', '=', $eventId);
                    },
                    'children_rec' => function ($query) use ($eventId) {
                        $query->join('allocation_setting', 'allocation_level_values.LevelValuesID', '=', 'allocation_setting.modelLevel')
                                ->where('allocation_setting.eventID', '=', $eventId)
                                ->select([
                                    'allocation_level_values.*',
                                    'allocation_setting.allocatedSeat'
                        ]);
                    }
                ])->leftjoin('allocation_setting as a1', function ($join) use ($eventId) {
                    $join->on('allocation_level_values.LevelValuesID', '=', 'a1.modelLevel')
                            ->where('a1.eventID', '=', $eventId);
                })->leftjoin('user', 'a1.createdBy', '=', 'user.UserID');
        if (Auth::user()->levelID == '3') {
            $allocationdata->where('allocation_level_values.LevelValuesID', '=', Auth::user()->LevelValueID);
        } else {
            $allocationdata->where('allocation_level_values.parent_id', '=', Auth::user()->LevelValueID);
        }
        $allocation_data = $allocationdata->orderBy('allocation_level_values.LevelValuesID', 'ASC')
                ->get([
            'allocation_level_values.*',
            'a1.*',
            'user.*',
            'a1.created_at as assginedDate',
            DB::raw("(select allocatedSeat from allocation_setting where modelLevel=" . Auth::user()->LevelValueID . " AND allocation_setting.eventID=" . $eventId . ")as total_seats")
        ]);
//echo "<pre>";print_r($allocation_data);exit;
        return $allocation_data;
    }

    public function getLevelValuesById($eventId = 0, $levelId = 0) {
        return AllocationSettings::where('eventID', '=', $eventId)
                        ->where('modelLevel', '=', $levelId)
                        ->first();
    }

    public function childLevelSeatAllocatedValue($eventid = 0, $levelID = 0) {
        return AllocationSettings::where('parentID', '=', $levelID)->where('eventID', '=', $eventid)
                        ->sum('allocatedSeat');
    }

    public function changeSeatingMethod($seat_status, $eventId) {
        $plannedEventObj = PlannedEvent::find($eventId);
        if (!empty($plannedEventObj->id)) {
            $success = $plannedEventObj->update([
                'is_seats_allocated' => $seat_status
            ]);
            if ($success) {
                if ($seat_status == 2) {
                    $availableSeatObj = \App\Models\EventAvailableSeat::firstOrNew([
                                'event_id' => $eventId
                    ]);
                    $availableSeatObj->fill([
                        'no_of_release_seat' => $plannedEventObj->max_registration,
                        'released_by' => Auth::id(),
                        'LevelValueID' => Auth::user()->LevelValueID
                    ]);
                    $availableSeatObj->save();
                } else {
                    $allocationSettings = AllocationSettings::firstOrNew([
                                'eventID' => $eventId,
                                'parentID' => '0'
                    ]);
                    $allocationSettings->fill([
                        'modelLevel' => Auth::user()->LevelValueID,
                        'allocatedSeat' => $plannedEventObj->max_registration,
                        'createdBy' => Auth::id(),
                    ]);
                    $allocationSettings->save();
                }
                return true;
            } else
                return false;
        }
    }

}
