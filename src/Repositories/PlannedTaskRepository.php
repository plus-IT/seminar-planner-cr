<?php

namespace  Ptlyash\SeminarPlannerCR\Repositories;


use App\Models\PlannedEvent;
use App\Models\PlannedEventTask;
use App\Models\PlannedTask;
use Ptlyash\SeminarPlannerCR\Interfaces\PlannedTaskRepositoryInterface;
use App\Models\EventTask;
use App\Models\OrganizationTask;
use App\Models\Person;
use App\Models\PersonTask;
use App\Models\Priority;
use App\Models\Task;
use App\Models\User;
use App\Models\TaskStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;

/**
 * Class TaskRepository
 * @package App\Repositories
 */
class PlannedTaskRepository implements PlannedTaskRepositoryInterface
{
    /**
     * get all details of task
     * @param int $task_id
     * @return array
     */
    function getAllDetailsByID($task_id = 0)
    {
        $task_data       = [];

        if ($task_id != 0) {
            $task_data = $this->getDetailsByID($task_id);
        }
        /*
         * Remove the client_id
         */
        Cache::flush();
        $all_status       = Cache::remember('all_status', CACHE_TIMEOUT, function () {
            return TaskStatus::all();
//            return Auth::user()->TaskStatus()->get();
        });
        $all_priority     = Cache::remember('all_priority', CACHE_TIMEOUT, function () {
            return Priority::all();
//            return Auth::user()->Priority()->get();
        });

        $all_agent = Cache::remember('all_agent', CACHE_TIMEOUT, function () {
            return User::where("UserID",'!=',Auth::id())->get();
        });

        return compact("task_data", "all_status", "all_priority", 'all_agent');
    }

    /**
     * get general details of task
     * @param int $task_id
     * @return mixed
     */
    function getDetailsByID($task_id = 0)
    {
        return PlannedTask::with(["created_by","updated_by"])->find($task_id);
    }

    /**
     * store the task
     * @param int $task_id
     * @return bool
     */
    function storeTask($task_id = 0)
    {
        $task_obj            = ($task_id != 0) ? PlannedTask::findOrFail($task_id) : new PlannedTask();
        $customer_fields     = [
            'TaskName',
            'PriorityID',
            'TaskStatusID',
            'TaskNote'
        ];
        $task_obj->TaskBegin = date("Y:m:d H:i:s", strtotime(Input::get("TaskBegin")));
        $task_obj->TaskEnd   = date("Y:m:d H:i:s", strtotime(Input::get("TaskEnd")));
        $task_obj->fill(Input::only($customer_fields));
        $task_obj->AssignedToTeam = null;
        $task_obj->AssignedToUser = null;

        if($task_id == 0){
            $task_obj->CreatedBy = Auth::user()->UserID;
        }else{
            $task_obj->UpdatedBy = Auth::user()->UserID;
        }

        if (Input::get("AssignedTo") == 0) {
            $other_assignee = explode("_", Input::get("AssignedToOther"));
            if ($other_assignee[0] == "team")
                $task_obj->AssignedToTeam = $other_assignee[1];
            else
                $task_obj->AssignedToUser = $other_assignee[1];
        } else {
            $task_obj->AssignedToUser = Auth::user()->UserID;
        }
        if (!$task_obj->save()) {
            return false;
        } else {
            if ($task_id == 0) {
                if(Input::has("PersonID")) {
                    if (Input::get("PersonID") != 'undefined') {
                        // If multiple registrtion done then we have multiple participant to add the task
                        $personIds = explode(",",Input::get("PersonID"));
                        foreach($personIds as $personId) {
                            $person_task_obj = new PersonTask();
                            $person_task_obj->fill(array("TaskID" => $task_obj->TaskID, "PersonID" => $personId))->save();
                        }
                    }
                }
                if(Input::has("OrganizationID")) {
                    if (Input::get("OrganizationID") != 'undefined') {
                        $organization_task_obj = new OrganizationTask();
                        $organization_task_obj->fill(array("task_id" => $task_obj->TaskID, "organization_id" => Input::get("OrganizationID")))->save();
                    }
                }
                if(Input::has("event_id")) {
                    if (Input::get("event_id") != 'undefined') {
                        $event_task_obj = new PlannedEventTask();
                        $event_task_obj->fill(array("task_id" => $task_obj->TaskID, "event_id" => Input::get("event_id")))->save();
                    }
                }
            }
            return $task_obj;
        }
    }

    function storeTaskToEvent($person_id = 0)
    {
        $task_obj=new PlannedTask();
        $taskstatus_obj=TaskStatus::where("client_id",Auth::user()->client_id)->where('TaskStatusName','not started')->select(['TaskStatusID'])->get();
        $taskpriority_obj=Priority::where("client_id",Auth::user()->client_id)->where('PriorityName','Normal')->select('PriorityID')->get();
        $task_obj->TaskName=Input::get('TaskName');
        $task_obj->TaskStatusID=$taskstatus_obj[0]->TaskStatusID;
        $task_obj->PriorityID=$taskpriority_obj[0]->PriorityID;
        $task_obj->CreatedBy = Auth::user()->UserID;
        $task_obj->TaskBegin = date("Y:m:d H:i:s", strtotime(date("Y-m-d H:i:s")));
        $task_obj->TaskEnd   = date("Y:m:d H:i:s", strtotime(Input::get("TaskEnd")));

        if (!$task_obj->save()) {
            return false;
        }else{
            if($person_id!=0) {

                    $person_task_obj = new PersonTask();
                    $person_task_obj->fill(array_merge(array("TaskID" => $task_obj->TaskID,"PersonID"=>$person_id)))->save();

            }
            return $task_obj->TaskID;
        }
    }
}
