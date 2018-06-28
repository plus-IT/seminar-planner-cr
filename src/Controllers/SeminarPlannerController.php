<?php

namespace Ptlyash\SeminarPlannerCR\Controllers;

use App\Models\AllocationLevels;
use App\Models\AllocationLevelValues;
use App\Models\AllocationSettings;
use App\Models\EventAvailableSeat;
use App\Models\Organization;
use Carbon\Carbon;
use App\Models\Event;
use App\Models\PlannedEvent;
use App\Models\PlannedEventSchedule;
use App\Models\PlannedSchedule;
use App\Models\PlannedSeminarRevenue;
use App\Models\SeminaItem;
use App\Models\SeminarSettings;
use Illuminate\Support\Facades\DB;
use Ptlyash\SeminarRegistrationCR\Repositories\AllocationSeatRepository;
use Response;
use App\Library\CustomFunction;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;
use App\Http\Requests\StorePersonPostRequest;
use Illuminate\Support\Facades\Validator;
use Ptlyash\SeminarPlannerCR\Interfaces\SeminarPlannerRepositoryInterface;
use App\Models\Person;
use App\Models\Location;
use App\Models\HolidayModal;
use App\Models\Region;
use App\Models\EventCategory;
use App\Models\EventAttendees;
use App\Models\User;
use App\WorkflowManager\EventManager;
use Illuminate\Support\Facades\App;
use Yajra\Datatables\Facades\Datatables;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class SeminarPlannerController extends Controller {

    /**
     * @var PersonRepositoryInterface
     */
    protected $seminar_planning_repository;
    protected $allocated_seat_repository;

    /**
     * initializes variable with EventRepository
     * @param EventRepositoryInterface $event_repository
     */
    public function __construct(SeminarPlannerRepositoryInterface $seminar_planning_repository, AllocationSeatRepository $allocated_seat_repository) {
        $this->middleware('acl:seminarPlanner.view', ['only' => ['index']]);
        $this->middleware('acl:seminarPlanner.deleteSeminar', ['only' => ['deleteSeminar']]);
        $this->seminar_planning_repository = $seminar_planning_repository;
        $this->allocated_seat_repository = $allocated_seat_repository;
    }

    public function getSeminarBudget($eventId = 0) {
        $revenueData = $this->seminar_planning_repository->getNoOfPlannedParticipant($eventId);
        return view('event.seminar_budget.budget_detail', $revenueData);
    }

    public function seminarSeatAllocation($eventId = 0) {
        $allocation_settings = $this->seminar_planning_repository->seminarSeatAllocation($eventId);
        $allocated_seats = AllocationSettings::where('parentID', '=', Auth::user()->LevelValueID)
                        ->where('eventID', '=', $eventId)->sum('allocatedSeat');
        $free_seats = EventAvailableSeat::where('event_id', '=', $eventId)->sum('no_of_release_seat');
        $free_seats = !empty($free_seats) ? $free_seats : 0;
        $plannedEventObj = PlannedEvent::where('id', $eventId)->first([
            'is_seats_allocated'
        ]);
//        echo "<pre>";
//        print_r($allocation_settings);
//        print_r($allocated_seats);
//        exit;
        return view('seminar_planner.seat_allocation.seat_allocation_info', compact('allocation_settings', 'allocated_seats', 'free_seats', 'plannedEventObj'));
    }

    public function savePlannedEvents($eventId) {
        $planed_obj = PlannedEvent::findOrFail($eventId);
        $customField = [
            'event_status',
            'is_deploy_internet',
            'show_vacant_seats'
        ];
        $planed_obj->fill(Input::only($customField));
        if ($planed_obj->save()) {
            // Attach trigger
            if (Input::has("is_deploy_internet") && Input::has("is_deploy_internet")) {
                $data = PlannedEvent::getEventForSalesforceEntry($eventId);
                EventManager::trigger("create-campaigns", $data);
            }

            return "true";
        } else {
            return "false";
        }
    }

    public function savePlannedRoleEvents($eventId) {
        if (Input::has("portal_roles")) {
            $planed_obj = PlannedEvent::where('id', $eventId)->update(['portal_roles' => Input::get('portal_roles')]);
            if ($planed_obj) {
                return "true";
            } else {
                return "false";
            }
        } else {
            return false;
        }
    }

    public function getPlannedParticipant($eventId) {
        return $this->seminar_planning_repository->getNoOfPlannedParticipant($eventId);
    }

    public function savePlannedParticipantDetail($eventId) {
        $success = $this->seminar_planning_repository->savePlannedParticipantDetail($eventId);
        if (empty($success)) {
            return Response::json([
                        "type" => "error",
                        "message" => CustomFunction::customTrans("general.error_message")
            ]);
        } else {
            return Response::json([
                        "type" => "success",
                        "message" => CustomFunction::customTrans("events.save_participant"),
                        "data" => $this->getPlannedParticipant($eventId)
            ]);
        }
    }

    public function updatePlannedMinMaxData(Request $request, $eventId) {
        $min_registration = Input::get('min_registration');
        $max_registration = Input::get('max_registration');
        $external_id = Input::get('external_id');
        $modal = 'App\\Models\\PlannedEvent';
        $primaryKey = App::make($modal)->getKeyName();
        $validator = Validator::make($request::all(), [
                    'external_id' => 'unique:tenant.planned_events,external_id,' . $eventId . ',' . $primaryKey
        ]);

        if (!$validator->fails()) {
            if (isset($external_id)) {
                PlannedEvent::where('id', '=', $eventId)->update(['min_registration' => $min_registration,
                    'max_registration' => $max_registration, 'external_id' => $external_id]);
            } else {
                PlannedEvent::where('id', '=', $eventId)->update(['min_registration' => $min_registration,
                    'max_registration' => $max_registration]);
            }

            return Response::json([
                        "type" => "success"
            ]);
        } else {
            $validationMsg = CustomFunction::customTrans("general.uniqueMessagePreText") . " " . CustomFunction::customTrans("lookupTable.external_id") . " " . CustomFunction::customTrans("general.uniqueExternalId_general");

            return Response::json([
                        "type" => "error",
                        "message" => $validationMsg
            ]);
        }
    }

    public function deleteSeminarRevenue($event_id) {
        $seminar_id = Input::get('seminar_id');
        if (!PlannedSeminarRevenue::where('revenue_item_id', "=", $seminar_id)->where('event_id', '=', $event_id)->delete()) {
            return Response::json([
                        "type" => "error",
                        "message" => CustomFunction::customTrans("general.error_message")
            ]);
        } else {
            $messageKey = Input::get('revenue_type') == 'fix' ? 'event.fix_delete_message' : 'event.variable_delete_message';
            return Response::json([
                        "type" => "success",
                        "message" => CustomFunction::customTrans($messageKey)
            ]);
        }
    }

    public function storeRevenueCalculate() {
        $seminar_id = Input::get('seminar_type_id');
        $seminar_data = ($seminar_id != '') ? PlannedSeminarRevenue::findOrFail($seminar_id) : new PlannedSeminarRevenue();
        $custom_field = [
            'item_id',
            'item_description',
            'amount',
            'unit_price',
            'unit_total',
            'event_id',
            'revenue_type'
        ];
        $seminar_data->fill(Input::only($custom_field));
        if ($seminar_id == '')
            $msg = CustomFunction::customTrans("event." . Input::get('revenue_type') . "_add_message");
        else
            $msg = CustomFunction::customTrans("event.success_" . Input::get('revenue_type') . "_update_message");
        if (!$seminar_data->save()) {
            return Response::json([
                        "type" => "error",
                        "message" => CustomFunction::customTrans("event.error_message")
            ]);
        } else {
            $item = SeminaItem::where('seminar_item_id', '=', $seminar_data->item_id)->get();
            $response = [
                "type" => "success",
                "message" => $msg,
                "data" => $seminar_data
            ];
            if (count($item) > 0) {
                $item = $item->first();
                $response['data']['item_name'] = $item->itemName;
            }
            return Response::json($response);
        }
    }

    public function seminarRevenueList($revenue_id) {
        $lines = PlannedSeminarRevenue::where('revenue_item_id', '=', $revenue_id)->get();
        if (count($lines) > 0) {
            $lines = $lines->first()->toArray();
            $items = SeminaItem::where('seminar_item_id', '=', $lines['item_id'])->get();

            $lines['item_name'] = count($items) > 0 ? $items->first()->itemName : '';
            $lines['status'] = 'success';
        } else {
            $lines = ['status' => 'fail'];
        }
        return Response::json($lines);
    }

    public function revenueCalculate() {
        $revenue_type = '';
        if (Input::has('revenue_type'))
            $revenue_type = Input::get('revenue_type');
        $seminar_item = SeminaItem::all();
        if (Input::has('seminar_id')) {
            $seminar_data = PlannedSeminarRevenue::where("revenue_item_id", Input::get('seminar_id'))->first();
            return view('event.seminar_budget.add_seminar_items', compact('seminar_item', 'seminar_data', 'revenue_type'));
        } else {

            return view('event.seminar_budget.add_seminar_items', compact('seminar_item', 'revenue_type'));
        }
    }

    public function addPlannedParticipant($eventId) {
        $event_task = $this->seminar_planning_repository->addPlannedParticipant($eventId);
        if (!empty($event_task)) {
            return Response::json([
                        "type" => "success",
                        "message" => CustomFunction::customTrans("event.updated_planned_participant"),
                        "data" => $event_task
            ]);
        } else {
            return Response::json([
                        "type" => "error",
                        "message" => CustomFunction::customTrans("general.error_message")
            ]);
        }
    }

    public function getActivities($event_id = 0, User $user) {
        $id = Auth::user()->UserID;
        if ($event_id != 0) {
            $activity_data = $this->seminar_planning_repository->getActivitiesByID($event_id, $id, $user->hasPermission('tasks.viewOther'));
            return view('seminar_planner.activity.activity_info', compact('activity_data'));
        } else {
            return view('seminar_planner.activity.activity_info');
        }
    }

    public function getAllDetails() {
        Cache::flush();
        $search_text = Input::get('search');
        $sort_by = Input::get('sortby');
        $sort_order = Input::get('sort_order');
        $limit = Input::has('limit') ? Input::get('limit') : 15;
        $filter_by = Input::get('filterby');
        $op_sort_order = $sort_order == "asc" ? "desc" : "asc";
        $total_events = $this->seminar_planning_repository->search($search_text, $sort_by, $sort_order, $limit, $filter_by);

        if (!empty($search_text)) {
            $total_events->appends(['search' => $search_text]);
        }

        $seminar_days_new = SeminarSettings::first(['seminar_new_until_days', 'consider_seminar_days', 'draft_calendar_background', 'planned_calendar_background', 'new_seminar_calendar_background', 'cancel_seminars_background']);
        $event_category = $this->seminar_planning_repository->getAllEventCategory();
        return compact('total_events', 'seminar_days_new', 'event_category', 'limit');
    }

    public function index($id = "") {
        $all_data = $this->getAllDetails();
        $seminar_days_new = SeminarSettings::first(['seminar_new_until_days', 'consider_seminar_days', 'draft_calendar_background', 'planned_calendar_background', 'new_seminar_calendar_background', 'cancel_seminars_background']);
        $holidays = HolidayModal::all();
        $all_regions = Region::all();
        $all_trainer = []; //Person::where('is_trainer', "=", '1')->get(['PersonID', DB::raw("concat(FirstName,' ',LastName) as PersonName")]);
        $all_system_user_obj = []; //User::where("UserID", "!=", Auth::user()->UserID);
        $all_event_category = [];
        $currentDate = ($id == "") ? Carbon::today()->format('Y-m-d') : PlannedEvent::where('id', $id)->first()->event_startdate;

        // adding all translations of seminar planner
        $translations = \Lang::get('seminarPlanner');

        return view('seminar_planner.seminar_planner', compact('seminar_days_new', 'holidays', 'all_trainer', 'all_regions', 'all_event_category', 'all_system_user', 'id', 'currentDate', 'translations'));
    }

    public function getSeminarTable() {

        $all_data = $this->getAllDetails();
        return view('seminar_planner.seminar_list_table', $all_data);
    }

    public function getSeminarCategory() {
        return $this->seminar_planning_repository->getAllEventCategory();
    }

    function getLocation() {
        return $this->seminar_planning_repository->getLocation();
    }

    function getTrainer() {
        return $this->seminar_planning_repository->getTrainer();
    }

    function getSeminarPlannedBy() {
        return $this->seminar_planning_repository->getSeminarPlannedBy();
    }

    function getSelectedSeminar() {
        $selectedSeminars = $this->seminar_planning_repository->getSelectedSeminar();

        return view('seminar_planner.selected_seminar_list', compact('selectedSeminars'));
    }

    function getSeminarBluePrints() {
        $bluePrintSeminars = $this->seminar_planning_repository->getSeminarBluePrints();

        return Response::json([
                    "type" => "success",
                    "message" => CustomFunction::customTrans("general.success_message"),
                    "bluePrintSeminars" => $bluePrintSeminars
        ]);
    }

    function insertBlueprintAsDraftEvent() {
        $bluePrintSeminars = $this->seminar_planning_repository->insertBlueprintAsDraftEvent();
        if (!$bluePrintSeminars) {
            return Response::json([
                        "type" => "error",
                        "message" => CustomFunction::customTrans("seminarPlanner.seminar_schedule_not_exist")
            ]);
        } else {
            return Response::json([
                        "type" => "success",
                        "message" => CustomFunction::customTrans("general.success_message"),
                        "bluePrintSeminars" => $bluePrintSeminars
            ]);
        }
    }

    function getPlannedSeminarById($id) {

        return view('seminar_planner.edit_seminar');
//        $plannedSeminars = $this->seminar_planning_repository->getPlannedSeminarById($id);
//        return Response::json($plannedSeminars);
    }

    function getPlannedSeminars($id = "") {
        $bluePrintSeminars = $this->seminar_planning_repository->getPlannedSeminars($id);
        return Response::json($bluePrintSeminars);
    }

    function updatePlannedSeminarsSchedule() {
        $bluePrintSeminar = $this->seminar_planning_repository->updatePlannedSeminarsSchedule();
        if (!$bluePrintSeminar) {
            return Response::json([
                        "type" => "error",
                        "message" => CustomFunction::customTrans("general.error_message")
            ]);
        } else {
            // Attach trigger
            if (isset($bluePrintSeminar->id) && !empty($bluePrintSeminar->id)) {
                $data = PlannedEvent::getEventForSalesforceEntry($bluePrintSeminar->id);
                EventManager::trigger("update-campaigns", $data);
            }

            $participants = EventAttendees::with(["person" => function ($query) {
                            $query->get(["PersonID", "Email", DB::Raw("CONCAT(FirstName, ' ', LastName) as displayName")]);
                        }])->where('event_id', $bluePrintSeminar->id)->where("ContactStatusID", 1)->get(["event_id", "person_id", "ContactStatusID"]);
            return Response::json([
                        "type" => "success",
                        "message" => CustomFunction::customTrans("general.success_message"),
                        "bluePrintSeminars" => $bluePrintSeminar,
                        "participants" => $participants
            ]);
        }
    }

    // Get schedules and slots of planned events to assign location and trainnes
    function getScheduleSlotPlannedEvent($plannedEvent = 0) {
        $plannedEvent = $this->seminar_planning_repository->getScheduleSlotPlannedEvent($plannedEvent);
        $trainers = Person::where("is_trainer", "1")->get(['PersonID', 'is_trainer', 'is_free_lancer', 'is_participant', 'FirstName', 'LastName', 'Email', 'Birthdate', 'Age']);
        $locations = Location::with(['locationRoom', 'locationRoom.room'])->get(['LocationID', 'LocationName', 'Street', 'Zip', 'City', 'Phone', 'Email', 'CountryID', 'RegionID']);
        if (!$plannedEvent) {
            return Response::json([
                        "type" => "error",
                        "message" => CustomFunction::customTrans("general.error_message")
            ]);
        } else {
            return Response::json([
                        "type" => "success",
                        "message" => CustomFunction::customTrans("general.success_message"),
                        "plannedEvent" => $plannedEvent,
                        "trainers" => $trainers,
                        "locations" => $locations
            ]);
        }
    }

    function assignLocationToSchedule($locationId, $scheduleId, $scheduleDate) {
        $result = $this->seminar_planning_repository->assignLocationToSchedule($locationId, $scheduleId, $scheduleDate);
        return Response::json($result);
    }

    function assignTrainerToSlot($slotId, $scheduleId, $trainerId) {
        $result = $this->seminar_planning_repository->assignTrainerToSlot($slotId, $scheduleId, $trainerId);
        return Response::json($result);
    }

    function assignRoomToSlot($slotId, $scheduleId, $roomId) {
        $result = $this->seminar_planning_repository->assignRoomToSlot($slotId, $scheduleId, $roomId);
        return Response::json($result);
    }

    function confirmSeminar($eventId) {
        $result = $this->seminar_planning_repository->confirmSeminar($eventId);
        return Response::json($result);
    }

    function cancelSeminar($eventId) {
        $event_obj = PlannedEvent::findOrfail($eventId);
        $row_date = strtotime($event_obj->event_startdate);
        $today = strtotime(date('Y-m-d'));

        if ($row_date > $today) {
            $result = $this->seminar_planning_repository->cancelSeminar($eventId);
            if (isset($result) && !empty($result)) {
                // // Attach trigger
                $data = PlannedEvent::getEventForSalesforceEntry($eventId);
                EventManager::trigger("delete-campaigns", $data);
            }
        } else {
            $result["type"] = "error";
            $result["message"] = CustomFunction::customTrans("events.cancelError");
        }
        return Response::json($result);
    }

    function deleteSeminar($eventId) {
        $event_obj = PlannedEvent::findOrfail($eventId);
        $row_date = strtotime($event_obj->event_startdate);
        $today = strtotime(date('Y-m-d'));

        if ($row_date > $today) {
            $result = $this->seminar_planning_repository->deleteSeminar($eventId);
            if (isset($result) && !empty($result)) {
                // // Attach trigger
                $data = PlannedEvent::getEventForSalesforceEntry($eventId);
                EventManager::trigger("delete-campaigns", $data);
            }
        } else {
            $result["type"] = "error";
            $result["message"] = CustomFunction::customTrans("events.deleteError");
        }
        return Response::json($result);
    }

    // Create task for trainer and location for the seminar
    function createTaskForTrainerLocation($seminarId, $createTaskForTrainer, $createTaskForLocation, $action) {
        $result = $this->seminar_planning_repository->createTaskForTrainerLocation($seminarId, $createTaskForTrainer, $createTaskForLocation, $action);
        return Response::json($result);
    }

    function removeTrainer($scheduleId, $slotId, $trainerId) {
        $result = $this->seminar_planning_repository->removeTrainer($scheduleId, $slotId, $trainerId);
        return Response::json($result);
    }

    function getDetailForm($event_id) {
        $plannedEvent = PlannedEvent::query();

        $plannedEvent->where('id', $event_id);

//        $trainer_id = PlannedEventSchedule::join("planned_schedule", "planned_schedule.id", "=", "planned_event_schedule.schedule_id")
//            ->join("planned_schedule_slot", "planned_schedule_slot.ScheduleID", "=", "planned_schedule.id")
//            ->where("planned_event_schedule.event_id", "=", $event_id)
//            ->first([DB::raw('GROUP_concat(planned_schedule_slot.trainer)as trainer')]);
//        $trainer_data = explode(",", $trainer_id->trainer);
//        $trainer = array_unique($trainer_data);
//        $person_data = Person::whereIn('person.PersonID', $trainer)->first([
//            DB::raw('GROUP_CONCAT(CONCAT(FirstName," ",LastName)) as trainer_name')
//        ]);
//
//        $location = PlannedEventSchedule::with(['schedule.scheduleLocation'])
//            ->where('event_id', $event_id)
//            ->first();

        $query = '(select GROUP_CONCAT(DISTINCT CONCAT(TRIM(pr.FirstName)," ",TRIM(pr.LastName)) SEPARATOR ", ") as speaker from planned_events as pe, planned_event_schedule as pes, 
                        planned_schedule as ps, 
                        planned_schedule_slot as pss, 
                        person as pr where pe.id = ' . $event_id . ' AND pes.event_id = pe.id AND 
                        pes.schedule_id = ps.id AND 
                        pss.ScheduleID = ps.id AND 
                        FIND_IN_SET( pr.PersonID, pss.trainer ) GROUP BY pe.blueprint_id)as personList,
                   (select GROUP_CONCAT(DISTINCT TRIM(lc.LocationName) SEPARATOR ", ") as location_name from planned_events as pe, planned_event_schedule as pes, 
                        planned_schedule as ps, 
                        location as lc,
                        planned_schedule_slot as pss 
                        where pe.id = ' . $event_id . ' AND pes.event_id = pe.id AND ps.LocationID = lc.LocationID AND
                        pes.schedule_id = ps.id AND 
                        pss.ScheduleID = ps.id GROUP BY pe.blueprint_id )as location     
                        ';

        $plannedEvent->selectRaw(
                'planned_events.*,' . $query
        );
        $event_data = $plannedEvent->first();
        $app = App::getFacadeRoot();
        $extentionHandler = $app['extentionHandler'];
        $array = $extentionHandler->getDataAndView('ptlyash_planner_edit_seminar::getDetailForm', compact('event_data'), 'seminar_planner.edit_seminar');
        $viewName = $array['view_name'];
        $data = $array['data'];
        return View($viewName, $data);
    }

    public function savedescription($event_id = 0) {
        $event_obj = PlannedEvent::findOrfail($event_id);
        $event_obj->target_group = Input::get('target_group');
        $event_obj->requirements = Input::get('requirements');
        $event_obj->content = Input::get('content');
        $event_obj->overview = Input::get('overview');
        if (!$event_obj->save()) {
            return Response::json([
                        "type" => "error",
                        "message" => CustomFunction::customTrans("events.event_error"),
            ]);
        } else {
            return Response::json([
                        "type" => "success",
                        "message" => CustomFunction::customTrans("events.event_update_description_success"),
            ]);
        }
    }

    public function getSchedules($event_id = 0) {
        $all_location = $this->seminar_planning_repository->getAllLocation();
        $allowed_days = SeminarSettings::first()->seminar_days;
        if ($event_id != 0) {
            $schedule_data = $this->seminar_planning_repository->getSchedulesByID($event_id);
            $day = isset($schedule_data[0]) && $schedule_data[0]->eventSchedule && !empty($schedule_data[0]->eventSchedule) ? count($schedule_data[0]->eventSchedule) : 0;
            return view('seminar_planner.schedule.schedule_info', compact('schedule_data', 'all_location', 'day', 'allowed_days'));
        } else {
            $day = 1;
            return view('seminar_planner.schedule.schedule_info', compact('all_location', 'all_event', 'day', 'allowed_days'));
        }
    }

    public function validateScheduleDate() {
        $result = $this->seminar_planning_repository->validateScheduleDate();
        return Response::json($result);
    }

    public function validateSchedule($schedule_id = 0) {

        $result = $this->seminar_planning_repository->validateSchedule($schedule_id);
        return Response::json($result);
    }

    public function deleteScheduleSlot($slotId) {
        $result = $this->seminar_planning_repository->deleteScheduleSlot($slotId);
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

    public function getDocuments($event_id = 0) {
        if ($event_id != 0) {
            $document_data = $this->seminar_planning_repository->getDocumentsByID($event_id);
            return view('seminar_planner.document.document_info', compact('document_data'));
        } else {
            return view('seminar_planner.document.document_info');
        }
    }

    public function exportList() {
        $export_array = [
            'event_name' => 'event_name',
            'event_category' => 'EventCategory->event_category_name',
            'min_registration' => 'min_registration',
            'max_registration' => 'max_registration',
            'target_group' => 'target_group',
            'requirements' => 'requirements',
            'content' => 'content',
            'overview' => 'overview',
//            'event_startdate' => 'event_startdate',
//            'event_enddate' => 'event_enddate',
            'event_price' => 'event_price',
        ];

        $all_event_category = EventCategory::all();
        return view('seminar_planner.export_events', compact('export_array', 'all_event_category'));
    }

    public function exportToXml() {
        $columnTagNameForXML = [
            'event_name' => 'SeminarName',
            'EventCategory->event_category_name' => 'SeminarCategory',
            'min_registration' => 'MinRegistration',
            'max_registration' => 'MaxRegistration',
            'target_group' => 'TargetGroup',
            'requirements' => 'Requirements',
            'content' => 'Content',
            'overview' => 'Overview',
//            'event_startdate' => 'SeminarStartdate',
////            'event_enddate' => 'SeminarEnddate',
            'event_price' => 'SeminarPrice',
        ];
        $required_field = Input::get('single_check');
        $required_field[] = 'event_category_id';
        $required_field[] = 'id';
        $required_field[] = 'blueprint_id';
        $required_field[] = DB::raw('GROUP_CONCAT(event_startdate) as event_startdate');
        $plannerEventsObject = PlannedEvent::query();

        $plannerEventsObject->with(['EventCategory', 'eventSchedule.schedule.scheduleLocation', 'eventSchedule.schedule.eventScheduleSlot'])
                ->where('event_startdate', '>=', date("Y-m-d", strtotime(Input::get("start_date"))))
                ->where('event_enddate', '<=', date("Y-m-d", strtotime(Input::get("end_date"))))
                ->where('event_status', 'confirm');

        if (!empty(Input::get("event_category"))) {
            $plannerEventsObject->whereIn('event_category_id', Input::get("event_category"));
        }

        if (Input::has('groupSeminars')) {
            $plannerEventsObject->groupBy('blueprint_id');
            $planner_events = $plannerEventsObject->get(['planned_events.*', DB::raw('GROUP_CONCAT(event_startdate) as eventStartDates'), DB::raw('GROUP_CONCAT(event_enddate) as eventEndDates')]);
        } else {
            $planner_events = $plannerEventsObject->get(['planned_events.*']);
        }

        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->startDocument('1.0');
        $xml->startElement('Seminars');
        foreach ($planner_events as $events) {
            $xml->startElement('PlannedSeminars');
            $xml->WriteAttribute('name', $events->event_name);
            if (Input::has('groupSeminars')) {
                $startDateArray = explode(",", $events->eventStartDates);
                $endDateArray = explode(",", $events->eventEndDates);
                $xml->startElement('SeminarDates');
                for ($i = 0; $i < count($startDateArray); $i++) {
                    $xml->writeElement('SeminarDate', format_date($startDateArray[$i]) . " - " . format_date($endDateArray[$i]));
                }
                $xml->endElement();
            } else {
                $xml->writeElement('SeminarDates', format_date($events->event_startdate) . " - " . format_date($events->event_enddate));
            }
            foreach (Input::get('single_check') as $key => $val) {
                $variable = "";
                eval('$variable = $events->' . $val . ";");
                $xml->writeElement($columnTagNameForXML[$val], $variable);
            }

            if (!empty($events->eventSchedule)) {
                foreach ($events->eventSchedule as $event_schedule) {
                    if (!empty($event_schedule->schedule)) {
                        $xml->startElement('Schedule');
                        $xml->writeElement('EventDays', $event_schedule->schedule->event_days);
                        $xml->writeElement('ScheduleDate', $event_schedule->schedule->schedule_date);
                        $xml->writeElement('DurationBetweenDay', $event_schedule->schedule->duration_between_previous_day);
                        $xml->writeElement('LocationName', isset($event_schedule->schedule->scheduleLocation) ? $event_schedule->schedule->scheduleLocation->LocationName : "");
                        if (!empty($event_schedule->schedule->eventScheduleSlot)) {
                            foreach ($event_schedule->schedule->eventScheduleSlot as $slot) {
                                $xml->startElement('ScheduleSlot');
                                $xml->writeElement('StartTime', $slot->start_time);
                                $xml->writeElement('EndTime', $slot->end_time);
                                $xml->writeElement('Description', $slot->description);
                                $xml->endElement();
                            }
                        }
                        $xml->endElement();
                    }
                }
            }
            $xml->endElement();
        }
        $xml->endElement();
        $xml->endDocument();
        $file_name = time() . '_seminar.xml';
        $file_path = 'seminar_export/' . $file_name;
        file_put_contents($file_path, $xml->outputMemory());
        $xml->flush();

        return Response::json([
                    "type" => "success",
                    "message" => CustomFunction::customTrans("seminarPlanner.export_scueess"),
                    "file_path" => $file_path,
                    "file_name" => $file_name
        ]);
    }

    public function seminarAllocationData($event_id = 0) {
//        echo "<pre>";
//        print_r(Auth::user());exit;

        $allocation_data = $this->seminar_planning_repository->getAllotmentData($event_id);

        return Datatables::of($allocation_data)
                        ->addColumn('seats', function ($allocation_data) {
                            return "<input type='number' name='allocation_seat_total[]' organization='" . $allocation_data->meta_value . "' class='allocation_seat_total' id='" . $allocation_data->LevelValuesID . "' value='" . $allocation_data->allocatedSeat . "' seatAllocated='" . $allocation_data->allocatedSeat . "'>";
                        })
                        ->addColumn('createdBy', function ($allocation_data) {
                            return $allocation_data->FirstName . " " . $allocation_data->LastName;
                        })
                        ->make(true);
    }

    public function seminarUtilizationData($eventId = 0) {
        $allocation_data = $this->seminar_planning_repository->getAllotmentData($eventId);
//        echo "<pre>";
//        print_r($allocation_data);
//        exit;
        $get_free_seat = EventAvailableSeat::where('event_id', '=', $eventId)->sum('no_of_release_seat');
        return view('seminar_planner.seat_allocation.seat_utilize_table', compact('allocation_data', 'get_free_seat', 'eventId'));
    }

    public function saveSeatAllocation($eventid, $levelID) {
        $event_attendees = EventAttendees::where('LevelValuesID', '=', $levelID)
                        ->where('event_id', '=', $eventid)->count();
        if ($event_attendees > Input::get('allocatedSeat')) {
            return Response::json([
                        "type" => "error",
                        "message" => CustomFunction::customTrans("general.participant_is_already_assigned_then_given_seat_number")
            ]);
        }
        $organization_data = $this->seminar_planning_repository->getLevelValuesById($eventid, $levelID);

        $already_allocated_seats = AllocationSettings::where('parentID', '=', Auth::user()->LevelValueID)
                ->where('eventID', '=', $eventid)
                ->where('modelLevel', '!=', $levelID)
                ->sum('allocatedSeat');
        $level_allocated_seats = $this->seminar_planning_repository->getLevelValuesById($eventid, Auth::user()->LevelValueID);
        $child_allocated_seats = $this->seminar_planning_repository->childLevelSeatAllocatedValue($eventid, $levelID);
        $parent_allocated_seats = $this->seminar_planning_repository->getLevelValuesById($eventid, $levelID);
        $get_free_seat = $this->allocated_seat_repository->getTotalFreeSeats($eventid);
        if (!empty($child_allocated_seats) && !empty($parent_allocated_seats->allocatedSeat)) {
            if ($parent_allocated_seats->allocatedSeat <= $child_allocated_seats) {
                return Response::json([
                            "type" => "error",
                            "message" => CustomFunction::customTrans("general.seats_are_allocated_to_child")
                ]);
            }
        }
        $already_allocated_seats = $already_allocated_seats + Input::get('allocatedSeat');

        if (!empty($level_allocated_seats->allocatedSeat)) {
            $total_available_seats = $get_free_seat + $level_allocated_seats->allocatedSeat;
            if ($already_allocated_seats > $total_available_seats) {
                return Response::json([
                            "type" => "error",
                            "message" => CustomFunction::customTrans("general.not_allowed_allocation_more_then_level_allowed")
                ]);
            }
        } else {
            return Response::json([
                        "type" => "error",
                        "message" => CustomFunction::customTrans("general.seat_is_not_allocated")
            ]);
        }
        if (empty($organization_data->AllocationID)) {
            $organization_data = new AllocationSettings();
        }
        $organization = json_decode(Input::get('organization'), true);
        $organizationId = isset($organization['organizationId']) ? $organization['organizationId'] : null;
        $organization_data->allocatedSeat = Input::get('allocatedSeat');
        $organization_data->eventID = $eventid;
        $organization_data->createdBy = Auth::id();
        $organization_data->parentID = Auth::user()->LevelValueID;
        $organization_data->modelLevel = $levelID;
        if (!$organization_data->save()) {
            return Response::json([
                        "type" => "error",
                        "message" => CustomFunction::customTrans("general.error_message")
            ]);
        } else {
            if (Input::get('is_free_seat') == 1) {
                $this->allocated_seat_repository->useFreeSeatToParentLevel(Auth::user()->LevelValueID, $eventid, Input::get('fee_seat_count'));
                $this->allocated_seat_repository->resetFreeSeatsWhichUsed($eventid, Input::get('fee_seat_count'));
            }
            return Response::json([
                        "type" => "success",
                        "message" => CustomFunction::customTrans("events.save_allocation_successfully"),
            ]);
        }
    }

    public function getTrainerList() {

        $search_cat = '';
        if (Input::has('q'))
            $search_cat = strtolower(Input::get('q'));
        $trainers = Person::where("is_trainer", "1")
                ->where(function ($query) use ($search_cat) {
                    $query->orWhere('person.FirstName', 'like', '%' . $search_cat . '%')
                    ->orWhere('person.LastName', 'like', '%' . $search_cat . '%')
                    ->orWhereRaw('CONCAT_WS(" ",trim(person.FirstName),trim(person.LastName))  like "%' . $search_cat . '%"')
                    ->orWhereRaw('CONCAT_WS(" ",trim(person.LastName),trim(person.FirstName))  like "%' . $search_cat . '%"');
                })
                ->get([
            'PersonID',
            'is_trainer',
            'is_free_lancer',
            'is_participant',
            'FirstName',
            'LastName',
            'Email',
            'Birthdate',
            'Age'
        ]);
        return $trainers;
    }

    public function getLocationList() {

        $search_cat = '';
        if (Input::has('q'))
            $search_cat = strtolower(Input::get('q'));
        $locations = Location::with([
                    'locationRoom',
                    'locationRoom.room'
                ])->where(function ($query) use ($search_cat) {
                    $query->where("location.LocationName", "like", "%" . $search_cat . "%")
                            ->orWhere("location.Zip", "like", "%" . $search_cat . "%")
                            ->orWhere("location.City", "like", "%" . $search_cat . "%");
                })->get([
            'LocationID',
            'LocationName',
            'Street',
            'Zip',
            'City',
            'Phone',
            'Email',
            'CountryID',
            'RegionID'
        ]);
        return $locations;
    }

    public function getAllCategory() {
        $get_current_local = LaravelLocalization::getCurrentLocale();
        $categoryName = ($get_current_local == 'en') ? 'event_category_name' : 'event_category_name_de';
        $eventCategoryObj = EventCategory::whereNotNull($categoryName)->select([
            'id',
            DB::raw($categoryName . ' as text')
        ]);

        $event_category_data = $eventCategoryObj->paginate(10);
        return response()->json(['items' => $event_category_data->toArray()['data'], 'pagination' => $event_category_data->nextPageUrl() ? true : false]);
    }

    public function getAllAgents() {
        $user_data = User::where('is_support_user', '!=', '1')
                ->where("UserID", '!=', Auth::id())
                ->orderBy('FirstName', 'ASC')
                ->select([
                    'UserID as id',
                    DB::raw('CONCAT(FirstName," ",LastName)as text')
                ])
                ->paginate(10);
        return response()->json(['items' => $user_data->toArray()['data'], 'pagination' => $user_data->nextPageUrl() ? true : false]);
    }

    public function changeSeatingMethod($seat_status, $eventId) {
        $success = $this->seminar_planning_repository->changeSeatingMethod($seat_status, $eventId);
        if ($success) {
            $message = ($seat_status == 1) ? CustomFunction::customTrans("events.seat_allocation_started") : CustomFunction::customTrans("events.all_seat_free");
            return Response::json([
                        "type" => "success",
                        "message" => $message,
            ]);
        } else {
            return Response::json([
                        "type" => "error",
                        "message" => CustomFunction::customTrans("general.error_message")
            ]);
        }
    }

}
