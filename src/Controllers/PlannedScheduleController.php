<?php

namespace Ptlyash\SeminarPlannerCR\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\PlannedEvent;
use App\Models\PlannedEventSchedule;
use App\Models\PlannedSchedule;
use Ptlyash\SeminarPlannerCR\Interfaces\PlannedScheduleRepositoryInterface;
use App\Models\Event;
use App\Models\EventSchedule;
use App\Models\Location;
use App\Models\LocationRoom;
use App\Models\Schedule;
use App\Models\Task;
use App\Models\SeminarSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;
use Response;
use App\Http\Requests\StoreUpdateScheduleRequest;
use App\Library\CustomFunction;

/**
 * Class TaskController
 * @package App\Http\Controllers
 */
class PlannedScheduleController extends Controller
{

    /**
     * @var ScheduleRepositoryInterface
     */
    protected $schedule_repository;

    /**
     * @param ScheduleRepositoryInterface $schedule_repository
     */
    public function __construct(PlannedScheduleRepositoryInterface $schedule_repository)
    {
        $this->middleware('acl:event.schedule.create', ['only' => ['show', 'store']]);
        $this->middleware('acl:event.schedule.update', ['only' => ['show', 'update']]);
        $this->middleware('acl:event.schedule.delete', ['only' => ['destroy']]);
        $this->schedule_repository = $schedule_repository;
    }

    /**
     * Display the detils of selected task.
     *
     * @param  int $schedule_id
     * @return \Illuminate\Http\Response
     */
    public function show($schedule_id = 0)
    {

        $all_location = Location::with(['locationRoom', 'locationRoom.room'])->get();
        $nextScheduleDate = PlannedEventSchedule::leftjoin("planned_schedule", "planned_event_schedule.schedule_id", "=", "planned_schedule.id")
            ->where("planned_event_schedule.event_id", "=", Input::get('eventID'))
            ->get([DB::Raw('DATE_ADD(max(planned_schedule.schedule_date), INTERVAL 1 DAY)  as nextDate'), DB::Raw('max(planned_schedule.event_days) as event_days') ])->first();
        // Day is use to indicate next schedule to be added
        if(isset($nextScheduleDate) && count($nextScheduleDate) > 0){
            $nextDate = $nextScheduleDate->nextDate;
            $nextDay = $nextScheduleDate->event_days + 1;
        }else{
            $nextDate = date('Y-m-d');
            $nextDay = 1;
        }

        $allowed_days = SeminarSettings::first()->seminar_days;
        if ($schedule_id != 0) {
            $duplicate = Input::has("status") ? (Input::get("status") == 'duplicate' ? true : false ) : false;
            $schedule_details = $this->schedule_repository->getDetailsByID($schedule_id);
            if(isset($schedule_details[0]->scheduleLocation) && !empty($schedule_details[0]->scheduleLocation)){
                $all_rooms = LocationRoom::with(['room'])->where('LocationID', $schedule_details[0]->scheduleLocation->LocationID)->get();
            }else{
                $all_rooms = "";
            }

            return view('seminar_planner.schedule.schedule_info', compact('schedule_details', 'all_location', 'all_rooms', 'day', 'allowed_days','duplicate' ,'nextDate', 'nextDay'));
        } else {
            return view('seminar_planner.schedule.schedule_info', compact('all_location', 'day', 'allowed_days','nextDate', 'nextDay'));
        }

//        $all_location = Location::with(['locationRoom', 'locationRoom.room'])->get();
//        $day = PlannedEventSchedule::join("planned_schedule", "planned_event_schedule.schedule_id", "=", "planned_schedule.id")
//                            ->where("planned_event_schedule.event_id","=",Input::get('eventID'))
//                            ->count();
//        $day+=1;
//
//        $schedule_details = $this->schedule_repository->getDetailsByID($schedule_id);
//
//
//        if ($schedule_id != 0) {
//    //        dd($schedule_details[0]->scheduleLocation->LocationID);
//            $all_rooms = LocationRoom::with(['room'])->where('LocationID', $schedule_details[0]->scheduleLocation->LocationID)->get();
////            dd($schedule_details[0]->roomId);
//
//            return view('seminar_planner.schedule.schedule_info', compact('schedule_details', 'all_location', 'all_rooms', 'day'));
//        } else {
//
//            return view('seminar_planner.schedule.schedule_info', compact('all_location', 'day'));
//        }

    }

    /**
     * store the new task
     * @param Request $request
     * @return mixed
     */
    public function store()
    {
        $result = $this->schedule_repository->storeSchedule();
        return Response::json($result);
    }

    public function deleteScheduleSlot($slotId)
    {
        $result = $this->schedule_repository->deleteScheduleSlot($slotId);
        if ($result) {
            return Response::json([
                "type" => "success",
                "message" => CustomFunction::customTrans("events.scheduleSlotDelete"),

            ]);
        } else {
            return Response::json([
                "type" => "error",
                "message" => CustomFunction::customTrans("events.scheduleSlotNotDelete"),

            ]);
        }

    }

    /**
     * update existing task
     * @param int $task_id
     * @return mixed
     */
    public function update($schedule_id = 0)
    {
        $result = $this->schedule_repository->storeSchedule($schedule_id);
        return Response::json($result);
    }

    /**
     * delete existing task
     * @param int $task_id
     * @return mixed
     */
    /**
     * Remove the specified resource from storage.
     *
     * @param  int $schedule_id
     * @return \Illuminate\Http\Response
     */
    public function destroy($schedule_id)
    {
        if (!PlannedSchedule::findOrFail($schedule_id)->delete()) {
            return Response::json([
                "type" => "error",
                "message" => CustomFunction::customTrans("general.error_message")
            ]);
        } else {
            $event_start_range = PlannedEventSchedule::where("event_id", Input::get('event_id'))->leftjoin("planned_schedule", function ($join) {
                $join->on("planned_event_schedule.schedule_id", "=", "planned_schedule.id");
            })->orderBy('planned_schedule.schedule_date', 'asc')->first();
            $event_end_range = PlannedEventSchedule::where("event_id", Input::get('event_id'))->leftjoin("planned_schedule", function ($join) {
                $join->on("planned_event_schedule.schedule_id", "=", "planned_schedule.id");
            })->orderBy('planned_schedule.schedule_date', 'desc')->first();
            $start_date = '';
            $end_date = '';

            if ((count($event_start_range) > 0) && (count($event_end_range) > 0)) {

                $event_obj = (Input::get('event_id') != 0) ? PlannedEvent::findOrFail(Input::get('event_id')) : new PlannedEvent();
                $event_obj->event_startdate = date('Y-m-d', strtotime($event_start_range->schedule_date));
                $event_obj->event_enddate = date('Y-m-d', strtotime($event_end_range->schedule_date));
                $event_obj->save();
                $start_date = format_date($event_start_range->schedule_date);
                $end_date = format_date($event_end_range->schedule_date);
            } else {

                $event_obj = (Input::get('event_id') != 0) ? PlannedEvent::findOrFail(Input::get('event_id')) : new PlannedEvent();
                $event_obj->event_startdate = '';
                $event_obj->event_enddate = '';
                $event_obj->save();
                $start_date = '';
                $end_date = '';
            }
            return Response::json([
                "type" => "success",
                "message" => CustomFunction::customTrans("events.scheduleDelete"),
                "event_end_date" => $end_date,
                "event_start_date" => $start_date,
            ]);
        }
    }

    public function validateScheduleDate()
    {
        $result = $this->schedule_repository->validateScheduleDate();
        return Response::json($result);
    }

    public function validateSchedule($schedule_id = 0)
    {

        $result = $this->schedule_repository->validateSchedule($schedule_id);
        return Response::json($result);
    }

}
