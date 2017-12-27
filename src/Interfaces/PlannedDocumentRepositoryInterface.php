<?php

namespace Ptlyash\SeminarPlanner\Interfaces;
/**
 * Interface DocumentRepositoryInterface
 * @package App\Interfaces
 */
interface PlannedDocumentRepositoryInterface
{
    /**
     * get general details of document
     * @param int $document_id
     * @return mixed
     */
    function getDocumentByID($document_id = 0);

    /**
     * get all details of document
     * @param int $document_id
     * @return mixed
     */
    function getAllDetailsByID($document_id = 0);

    /**
     * save the document
     * @param int $document_id
     * @return mixed
     */
    function storeDocument($document_id = 0);
}
