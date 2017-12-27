<?php

namespace Ptlyash\SeminarPlanner\Repositories;

use App\Models\PlannedEvent;
use App\Models\PlannedEventSchedule;
use App\Models\PlannedSchedule;
use App\Models\PlannedScheduleSlot;
use Ptlyash\SeminarPlanner\Interfaces\PlannedScheduleRepositoryInterface;
use App\Models\Event;
use App\Models\EventSchedule;
use App\Models\EventTask;
use App\Models\Organization;
use App\Models\OrganizationTask;
use App\Models\Person;
use App\Models\PersonTask;
use App\Models\Priority;
use App\Models\Schedule;
use App\Models\ScheduleSlot;
use App\Models\ScheduleTrainer;
use App\Models\Task;
use App\Models\TaskStatus;
use Collective\Html\HtmlFacade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Library\CustomFunction;

/**
 * Class TaskRepository
 * @package App\Repositories
 */

class PlannedScheduleRepository implements PlannedScheduleRepositoryInterface
{
    /**
     * get all details of task
     * @param int $schedule_id
     * @return array
     */
    function getAllDetailsByID($schedule_id = 0)
    {
        $schedule_data = [];

        if ($schedule_id != 0) {
            $schedule_data = $this->getDetailsByID($schedule_id);
        }

        $all_trainer = Person::where('is_trainer', 1)
            ->where('client_id', Auth::user()->client_id)
            ->get(['PersonId as id',
                DB::raw('CONCAT(trim(FirstName), " ", trim(LastName)) as text')
            ]);

        $all_organization = Cache::remember('all_organization', CACHE_TIMEOUT, function () {
            return Organization::all();
        });

        return compact("schedule_data", "all_organization", "all_trainer");
    }

    /**
     * get general details of task
     * @param int $schedule_id
     * @return mixed
     */
    function getDetailsByID($schedule_id = 0, $event_id = 0)
    {
        return PlannedSchedule::with(['eventSchedule', 'scheduleLocation', 'eventScheduleSlot.person','eventScheduleSlot.scheduleSlotRoom'])->where('id', $schedule_id)->get();
    }

    /**s
     * store the task
     * @param int $schedule_id
     * @return bool
     */

    function storeSchedule($schedule_id = 0)
    {

        $schedule_obj = ($schedule_id != 0) ? PlannedSchedule::findOrFail($schedule_id) : new PlannedSchedule();

        // Get next valid date for schedule based on the critearea
        if(Input::has("schedule_date") && Input::get("schedule_date") && $schedule_id == 0 ){
            $weekdays = (Input::has('weekdays') && !empty(Input::get('weekdays')) && Input::get("scheduleType") == 1) ? Input::get('weekdays') : ['0','1','2','3','4','5','6'];
            $daysDiff = (Input::has('duration_between_previous_day') && !empty(Input::get('duration_between_previous_day'))) ? Input::get('duration_between_previous_day') : 0;
            $nextValidDate = $this->getValidDateForSchedule(Input::get("schedule_date"), $weekdays, $daysDiff );
            $schedule_obj->schedule_date = $nextValidDate;
        }

        $customer_fields = ['LocationID','duration_between_previous_day','event_days'];

        $schedule_obj->fill(Input::only($customer_fields));
        if(Input::has("scheduleRoomId") && !empty(Input::get("scheduleRoomId")) ){
            $schedule_obj->roomId = Input::get("scheduleRoomId");
            $schedule_obj->custom_room_name = "";
        }else{
            $schedule_obj->custom_room_name = Input::get("customRoomName");
            $schedule_obj->roomId = "";
        }
        $schedule_obj->fill(Input::only($customer_fields));
        $schedule_obj->schedule_default_trainer = Input::get('scheduleDefaultTrainer');
        $schedule_obj->schedule_type = Input::get('scheduleType');

        $result = [];
        $slotCount = count(Input::get('start_time'));
        $scheduleDate = date('Y-m-d', strtotime(Input::get('schedule_date')));
        $slotStartTime = Input::get('start_time');
        $slotEndTime = Input::get('end_time');
        $slotDescription = Input::get('description');
        $slotTitle = Input::get('title');
        $slotRoomId = Input::get('roomId');
        $slotRoomName = Input::get('slotRoomName');

        $trainer = Input::get('trainer');
        $locationID = Input::get('LocationID');
        $flag = '0';
        if (Input::has('weekdays') && !empty(Input::get('weekdays')) && Input::get("scheduleType") == 1) {
            $schedule_obj->weekdays = implode(",",Input::get('weekdays'));
        }else{
            $schedule_obj->weekdays = '0,1,2,3,4,5,6';
        }
//         Check Schedule date is occupied on preffred location or not
//        if (Input::has('schedule_date') && !empty($slotCount)) {
//            for ($i = 0; $i < count($slotStartTime); $i++) {
//                $is_schedule = PlannedSchedule::where("schedule_date", "=", date("Y-m-d", strtotime(Input::get('schedule_date'))))
//                    ->where("LocationID", "=", $locationID)
//                    ->where("planned_schedule.id", "!=", $schedule_id)
//                    ->get()->toArray();
//                if (count($is_schedule) > 0) {
//                    $result["type"] = "danger";
//                    $result["message"] = CustomFunction::customTrans("events.schedule_date_exist_on_locations");
//                    return $result;
//                }
//            }
//        }
//
//// Check Trainer is occupied or not
//        if (Input::has('trainer') && !empty($trainer)) {
//            for ($i = 0; $i < count($trainer); $i++) {
//                $trainers = explode(",", $trainer[$i]);
//                for ($j = 0; $j < count($trainers); $j++) {
//                    if ($trainers[$j] != '') {
//                        $is_trainer_occupied =PlannedSchedule::join("planned_event_schedule", "schedule_id", "=", "planned_schedule.id")
//                            ->join("planned_schedule_slot", "planned_schedule_slot.ScheduleID", "=", "planned_schedule.id")
//                            ->whereRaw("FIND_IN_SET(" . $trainers[$j] . ",planned_schedule_slot.trainer)")
//                            ->where("schedule_date", "=", $scheduleDate)
//                            ->where("planned_schedule.id", "!=", $schedule_id)
//                            ->groupBy("planned_schedule.id")
//                            ->get()->toArray();
//
//                        if (count($is_trainer_occupied) > 0) {
//                            $result["type"] = "danger";
//                            $result["message"] = CustomFunction::customTrans("events.ScheduleTrainerAlreadyBooked");
//
//                            return $result;
//                        }
//                    }
//                }
//            }
//        }
        if (!$schedule_obj->save()) {
            return false;

        } else {
            // Add Schedule into event schedule table according to specified event id;
            if ($schedule_id == 0) {
                if (Input::has("EventID")) {
                    if (Input::get("EventID") != 'undefined') {
                        $event_schedule_obj = new PlannedEventSchedule();
                        $event_schedule_obj->fill(array_merge(array("schedule_id" => $schedule_obj->id, "event_id" => Input::get("EventID"))))->save();
                    }
                }
            }
        }
        if (Input::has("slot_id")) {
            $schedule_slot_id = Input::get('slot_id');
        }
//  Add Schedule Slot into schedule slot table
        if (Input::get("time_slot_chk")) {
            $count = count(Input::get('time_slot_chk'));
            for ($i = 0; $i < $count; $i++) {
                if(!empty($slotRoomId[$i]) ){
                    $slotRumId = $slotRoomId[$i];
                    $slotRumCustName = "";
                }else{
                    $slotRumId = "";
                    $slotRumCustName = $slotRoomName[$i];
                }
                $scheduleSlotObj = (isset($schedule_slot_id[$i]) && ($schedule_slot_id[$i] != '')) ? PlannedScheduleSlot::findOrFail($schedule_slot_id[$i]) : new PlannedScheduleSlot();
                $scheduleSlotObj->fill(array_merge(array("start_time" => date("H:i:s", strtotime($slotStartTime[$i])), "end_time" => date("H:i:s", strtotime($slotEndTime[$i])), "trainer" => $trainer[$i], "ScheduleID" => $schedule_obj->id, "description" => $slotDescription[$i],"roomId" => $slotRumId, "custom_room_name" => $slotRumCustName, "title" => $slotTitle[$i])))->save();
            }
        } else {
            $count = count(Input::get('start_time'));
            for ($i = 0; $i < $count; $i++) {
                if(!empty($slotRoomId[$i]) ){
                    $slotRumId = $slotRoomId[$i];
                    $slotRumCustName = "";
                }else{
                    $slotRumId = "";
                    $slotRumCustName = $slotRoomName[$i];
                }
                $scheduleSlotObj = (isset($schedule_slot_id[$i]) && ($schedule_slot_id[$i] != '')) ? PlannedScheduleSlot::findOrFail($schedule_slot_id[$i]) : new PlannedScheduleSlot();
                $scheduleSlotObj->fill(array_merge(array("start_time" => date("H:i:s", strtotime($slotStartTime[$i])), "end_time" => date("H:i:s", strtotime($slotEndTime[$i])), "trainer" => $trainer[$i], "ScheduleID" => $schedule_obj->id, "description" => $slotDescription[$i], "roomId" => $slotRumId, "custom_room_name" => $slotRumCustName, "title" => $slotTitle[$i])))->save();
            }
        }

//        Add event start date and end date in to event table
        $event_start_range = PlannedEventSchedule::where("event_id", Input::get('EventID'))
            ->leftjoin("planned_schedule", function ($join) {
            $join->on("planned_event_schedule.schedule_id", "=", "planned_schedule.id");
        })->orderBy('planned_schedule.schedule_date', 'asc')->first();


        $event_end_range = PlannedEventSchedule::where("event_id", Input::get('EventID'))->leftjoin("planned_schedule", function ($join) {
            $join->on("planned_event_schedule.schedule_id", "=", "planned_schedule.id");
        })->orderBy('planned_schedule.schedule_date', 'desc')->first();



        $event_obj = (Input::get('EventID') != 0) ? PlannedEvent::findOrFail(Input::get('EventID')) : new PlannedEvent();

        $event_obj->event_startdate = date('Y-m-d', strtotime($event_start_range->schedule_date));
        $event_obj->event_enddate = date('Y-m-d', strtotime($event_end_range->schedule_date));
        $event_obj->save();
        if ($result) {
            $result["schedule_id"] = $schedule_obj->id;
            $result["event_start_date"] = format_date($event_start_range->schedule_date);
            $result["event_end_date"] = format_date($event_end_range->schedule_date);
            return $result;
        } else {
            //$event_fields = ['AddedByUserID', 'text'];
            if ($schedule_id != 0) {
                $result["message"] = CustomFunction::customTrans("events.scheduleUpdate");
            } elseif (Input::get("model_mode") != 0) {
                $result["message"] = CustomFunction::customTrans("events.scheduleDuplicate");
            } else {
                $result["message"] = CustomFunction::customTrans("events.scheduleInsert");
            }
            $result["type"] = "success";
            $result["schedule_id"] = $schedule_obj->id;
            $result["event_start_date"] = format_date($event_start_range->schedule_date);
            $result["event_end_date"] = format_date($event_end_range->schedule_date);
            return $result;
        }
    }

function getValidDateForSchedule($lastScheduleDate, $validWeekDays = ['0','1','2','3','4','5','6'], $durationBetweenPreviousDay = 0){
    $carbnDateObject = Carbon::parse($lastScheduleDate);
    $carbnDateObject = $carbnDateObject->addDay($durationBetweenPreviousDay);
    $integerDay = date( "w", $carbnDateObject->timestamp);
    $validDate = $carbnDateObject;
    while(!in_array($integerDay, $validWeekDays)){
        $nextDate = $carbnDateObject->addDay(1);
        $integerDay = date( "w", $nextDate->timestamp);
        $validDate = $carbnDateObject;
    }
    return $validDate;
}

function deleteScheduleSlot($slotId){
        $slot=PlannedScheduleSlot::where("schedule_slotID","=",$slotId)->delete();

        if($slot){
            return true;
        }else{
            return false;
        }
}

    function validateSchedule($schedule_id = 0)
    {

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
                    $result["message"] = CustomFunction::customTrans("events.scheduleAnotherEventAtSameLocation");
                    $result["conflictScheduleId"] = $eventsForSameDateTimeLocation[0]->schedule_id;
                    $result["event"] = $eventsForSameDateTimeLocation[0]->event_id;
                    return $result;
                }
            }
        }
        return $result;
    }

}
