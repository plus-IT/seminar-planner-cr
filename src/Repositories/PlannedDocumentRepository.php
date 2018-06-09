<?php

namespace Ptlyash\SeminarPlannerCR\Repositories;


use App\Models\PlannedDocument;
use App\Models\PlannedEventDocument;
use Ptlyash\SeminarPlannerCR\Interfaces\PlannedDocumentRepositoryInterface;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\LocationDocument;
use App\Models\OrganizationDocument;
use App\Models\EventDocument;
use App\Models\PersonDocument;
use App\Models\Person;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;

/**
 * Class DocumentRepository
 * @package App\Repositories
 */
class PlannedDocumentRepository implements PlannedDocumentRepositoryInterface
{
    /**
     * get all details of document
     * @param int $document_id
     * @return array
     */
    function getAllDetailsByID($document_id = 0)
    {
        $document_data = $assignedToOther=[];
        if ($document_id != 0) {
            $document_data = $this->getDocumentByID($document_id);
              $assignedToOther = User::where('UserID', $document_data->AssignedToUser)
                ->get([
                    'UserID as id',
                    DB::raw('CONCAT(trim(FirstName), " ", trim(LastName)) as text')
                ])->toArray();
        }
        Cache::flush();
        $all_documentcategory = Cache::remember('all_documentcategory', CACHE_TIMEOUT, function () {
            return DocumentCategory::all();
//            return Auth::user()->DocumentCategory()->get();
        });
        $all_agent =[];/* Cache::remember('all_agent', CACHE_TIMEOUT, function () {
            return User::where('is_support_user','!=','1')->where("UserID",'!=',Auth::id())->get();
        });*/
        $all_team             = Cache::remember('all_team', CACHE_TIMEOUT, function () {
            return Team::all();
//            return Auth::user()->Team()->get();
        });

        return compact('document_data', 'all_documentcategory', 'all_agent', 'all_team','assignedToOther');
    }

    /**
     * get general details of document
     * @param int $document_id
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    function getDocumentByID($document_id = 0)
    {
        return PlannedDocument::with(["created_by","updated_by"])->findOrFail($document_id);
    }

    /**
     * save the document
     * @param int $document_id
     * @return bool
     */
    function storeDocument($document_id = 0)
    {

        $document_id=(Input::get('DocumentID')!='')?Input::get('DocumentID'):0;
        $document_obj    = ($document_id != 0) ? PlannedDocument::findOrFail($document_id) : new PlannedDocument();
        $customer_fields = [
            'DocumentTitle',
            'DocumentDescription',
            'DocumentSizeMB',
            'DocumentFileName',
            'DocumentCategoryID'
        ];
       
        $document_obj->fill(Input::only($customer_fields));
        if (Input::get("DocumentFileName")) {
            $document_obj->DocumentExtension = explode(".", Input::get("DocumentFileName"));
            $document_obj->DocumentExtension = $document_obj->DocumentExtension[1];
        }
        $document_obj->AssignedToTeam = null;
        $document_obj->AssignedToUser = null;
        if($document_id == 0){
            $document_obj->CreatedBy = Auth::user()->UserID;
        }else{
            $document_obj->UpdatedBy = Auth::user()->UserID;
        }
        if (Input::get("AssignedTo") == 0) {
            $other_assignee = explode("_", Input::get("AssignedToOther"));
            if ($other_assignee[0] == "team")
                $document_obj->AssignedToTeam = $other_assignee[1];
            else
                $document_obj->AssignedToUser = $other_assignee[1];
        } else {
            $document_obj->AssignedToUser = Auth::user()->UserID;
        }

        if (Request::file('DocumentUpload')) {
            $fileName = Request::file('DocumentUpload')->getClientOriginalName();
            Request::file('DocumentUpload')->move(
                base_path() . DOCUMENT_UPLOAD_PATH, $fileName
            );
        }
        if (!$document_obj->save()) {
            return false;
        } else {
            if ($document_id == 0) {
                $document_id = $document_obj->DocumentID;

                        $event_task_obj = new PlannedEventDocument();
                        $event_task_obj->fill(array("document_id" => $document_id, "event_id" => Input::get("event_id")))->save();

            }
            return $document_id;
        }
    }
}
