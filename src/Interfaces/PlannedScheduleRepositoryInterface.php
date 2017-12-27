<?php

namespace Ptlyash\SeminarPlanner\Interfaces;
/**
 * Interface TaskRepositoryInterface
 * @package App\Interfaces
 */
interface PlannedScheduleRepositoryInterface
{
    /**
     * @param int $schedule_id
     * @return mixed
     */
    function getDetailsByID($schedule_id = 0);

    /**
     * @param int $schedule_id
     * @param $user_id
     * @return mixed
     */
    function getAllDetailsByID($schedule_id = 0);

    /**
     * @param int $schedule_id
     * @return mixed
     */
    function storeSchedule($schedule_id = 0);
}
