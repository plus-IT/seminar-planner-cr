<?php

namespace Ptlyash\SeminarPlanner\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\PlannedTask;
use Ptlyash\SeminarPlanner\Interfaces\PlannedTaskRepositoryInterface;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;
use Response;
use App\Http\Requests\StoreUpdateActivityRequest;
use App\Library\CustomFunction;
use Illuminate\Support\Facades\Auth;


/**
 * Class TaskController
 * @package App\Http\Controllers
 */
class PlannedTaskController extends Controller
{

    /**
     * @var TaskRepositoryInterface
     */
    protected $task_repository;

    /**
     * @param TaskRepositoryInterface $task_repository
     */
    public function __construct(PlannedTaskRepositoryInterface $task_repository)
    {
        $this->middleware('acl:activity.create', ['only' => ['create', 'store']]);
        $this->middleware('acl:activity.update', ['only' => ['edit', 'update']]);
        $this->middleware('acl:activity.delete', ['only' => ['destroy']]);
        $this->task_repository = $task_repository;
    }

    /**
     * Display the detils of selected task.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($task_id = 0, User $user)
    {   
        $id = Auth::user()->UserID;
        if($task_id != 0){
            $data = Task::findOrFail($task_id);
            if($user->hasPermission('tasks.editOther') == false && $id != $data['AssignedToUser']){
                return Response::json([
                    "type"    => "error",
                    "message" => CustomFunction::customTrans("general.unauthorizedError"),
                ]);
             }
            
        }   
        return view("seminar_planner.activity.add_activity", $this->task_repository->getAllDetailsByID($task_id));
    }

    /**
     * store the new task
     * @param Request $request
     * @return mixed
     */
    public function store(StoreUpdateActivityRequest $request)
    {

        
        $success = $this->task_repository->storeTask();
        if (!$success) {
            return Response::json([
                "type"    => "error",
                "message" => CustomFunction::customTrans("general.error_message")
            ]);
        } else {
            return Response::json([
                "type"    => "success",
                "message" => CustomFunction::customTrans("general.task_add_message"),
                "taskObject" => $success,

            ]);
        }
    }

    /**
     * update existing task
     * @param int $task_id
     * @return mixed
     */
    public function update($task_id = 0, StoreUpdateActivityRequest $request, User $user)
    {
        $success = $this->task_repository->storeTask($task_id);
        if (!$success) {
            return Response::json([
                "type"    => "error",
                "message" => CustomFunction::customTrans("general.error_message"),
            ]);
        } else {
         //   echo $success;exit;
        if(Input::has('type') && Input::get('type')=='dashboard'){
          $obj=  PlannedTask::join("taskstatus","planned_task.TaskStatusID","=","taskstatus.TaskStatusID")
                    ->join("user","planned_task.AssignedToUser","=","user.UserID")
                    ->where("TaskID","=",$success->TaskID)
                    ->first(["planned_task.*","taskstatus.*","user.FirstName","user.LastName"]);
            $obj->TaskEnd= format_date($obj->TaskEnd);
        }
        $result=isset($obj)?$obj:$success;

            return Response::json([
                "type"    => "success",
                "message" => CustomFunction::customTrans("general.task_update_message"),
                "taskObject"=>$result
            ]);
        }
    }

    /**
     * delete existing task
     * @param int $task_id
     * @return mixed
     */
    public function destroy($task_id = 0,User $user)
    {
      $id = Auth::user()->UserID;
      $data = PlannedTask::findOrFail($task_id);
      if($id != $data['AssignedToUser'] && $user->hasPermission('tasks.deleteOther') == false){
           return Response::json([
                "type"    => "error",
                "message" => CustomFunction::customTrans("general.unauthorizedError"),
            ]);
      }
      
        if (!PlannedTask::findOrFail($task_id)->delete()) {
            return Response::json([
                "type"    => "error",
                "message" => CustomFunction::customTrans("general.error_message"),
            ]);
        } else {
            return Response::json([
                "type"    => "success",
                "message" => CustomFunction::customTrans("general.task_delete_message"),
            ]);
        }
    }

    public function AddTaskToEvent($person_id=0)
    {

        $success = $this->task_repository->storeTaskToEvent($person_id);
        if (!$success) {
            return Response::json([
                "type"    => "error",
                "message" => CustomFunction::customTrans("general.error_message"),
            ]);
        } else {
            return Response::json([
                "type"    => "success",
                "message" => CustomFunction::customTrans("general.task_add_message"),
                "activity_id" => $success
            ]);
        }

    }

}
