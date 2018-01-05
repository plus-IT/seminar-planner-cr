<?php

namespace Ptlyash\SeminarPlannerCR\Controllers;

use App\Models\PlannedDocument;
use Ptlyash\SeminarPlannerCR\Interfaces\PlannedDocumentRepositoryInterface;
use App\Models\Document;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;
use Response;
use App\Http\Requests\StoreUpdateDocumentRequest;
use App\Library\CustomFunction;

/**
 * Class DocumentController
 * @package App\Http\Controllers
 */
class PlannedDocumentController extends Controller
{

    /**
     * @var DocumentsRepositoryInterface
     */
    protected $document_repository;

    /**
     * @param DocumentsRepositoryInterface $document_repository
     */
    public function __construct(PlannedDocumentRepositoryInterface $document_repository)
    {
        $this->middleware('acl:document.create', ['only' => ['create', 'store']]);
        $this->middleware('acl:document.update', ['only' => ['edit', 'update']]);
        $this->middleware('acl:document.delete', ['only' => ['destroy']]);
        $this->document_repository = $document_repository;
    }

    /**
     * Display the details of selected document.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($document_id = 0)
    {
        return view("seminar_planner.document.add_document", $this->document_repository->getAllDetailsByID($document_id));
    }

    /**
     * save the new document
     * @param Request $request
     * @return mixed
     */
    public function store($id = 0 )
    {

        if (Input::get('DocumentID') != '') {
            return $this->update($id);
        } else {
            $success = $this->document_repository->storeDocument();
            if (!$success) {
                return Response::json([
                    "type" => "error",
                    "message" => CustomFunction::customTrans("general.error_message")
                ]);
            } else {
                return Response::json([
                    "type" => "success",
                    "message" => CustomFunction::customTrans("general.document_add_message"),
                    "id" => $success
                ]);
            }
        }
    }

    /**
     * update existing document
     * @param int $document_id
     * @return mixed
     */
    public function update($document_id = 0)
    {

        $success = $this->document_repository->storeDocument($document_id);
        if (!$success) {
            return Response::json([
                "type" => "error",
                "message" => CustomFunction::customTrans("general.error_message")
            ]);
        } else {
            return Response::json([
                "type" => "success",
                "message" => CustomFunction::customTrans("general.document_update_message"),
                "id" => $success
            ]);
        }
    }

    /**
     * delete the document
     * @param int $document_id
     * @return mixed
     */
    public function destroy($document_id = 0)
    {
        if (!PlannedDocument::findOrFail($document_id)->delete()) {
            return Response::json([
                "type" => "error",
                "message" => CustomFunction::customTrans("general.error_message")
            ]);
        } else {
            return Response::json([
                "type" => "success",
                "message" => CustomFunction::customTrans("general.document_delete_message")
            ]);
        }
    }

    public function download_document($document_id = '')
    {
        $file_name = PlannedDocument::select('DocumentFileName')->where('DocumentId', $document_id)->first()->toArray();
        $file_name = $file_name['DocumentFileName'];
        $file_path = public_path() . "/" . 'document_upload/' . $file_name;
        if (\File::exists($file_path)) {
            $mimetype = \File::mimeType($file_path);
            $header = array('Content-Type: ' . $mimetype);
            return Response::download($file_path, $file_name, $header);
        }
    }
}
