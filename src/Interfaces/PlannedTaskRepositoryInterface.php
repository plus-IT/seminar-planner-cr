<?php

namespace Ptlyash\SeminarPlanner\Interfaces;
/**
 * Interface TaskRepositoryInterface
 * @package App\Interfaces
 */
interface PlannedTaskRepositoryInterface
{
    /**
     * @param int $task_id
     * @return mixed
     */
    function getDetailsByID($task_id = 0);

    /**
     * @param int $task_id
     * @param $user_id
     * @return mixed
     */
    function getAllDetailsByID($task_id = 0);

    /**
     * @param int $task_id
     * @return mixed
     */
    function storeTask($task_id = 0);
}
