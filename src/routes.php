<?php
/**
 * Created by PhpStorm.
 * User: Yash
 * Date: 12/23/2016
 * Time: 3:46 PM
 */

Route::group(['prefix' => (env('APP_ENV') === 'testing' ? 'en' : LaravelLocalization::setLocale()), 'middleware' => ['localeSessionRedirect', 'localizationRedirect']], function () {
    Route::group(['middleware' => 'tenant_acess'], function () {
        Route::group(['middleware' => 'force.ssl'], function () {
            Route::group(['middleware' => 'auth'], function () {
                Route::post('seminar-planner/export', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@exportToXml');
                Route::get('seminar-planner/exportlist', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@exportList');
                Route::post('seminar-planner/event/savePlannedParticipant/{eventid}', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@savePlannedParticipantDetail');
                Route::post('seminar-planner/event/revenueCalculate/{eventid}', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@storeRevenueCalculate');
                Route::put('seminar-planner/event/addPlannedParticipant/{eventid}', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@addPlannedParticipant');
                Route::get('seminar-planner/event/delete/revenueCalculate/{eventid}', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@deleteSeminarRevenue');
                Route::get('seminar-planner/event/revenueCalculate/{eventid}', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@revenueCalculate');
                Route::get('seminar-planner/event/getPlannedParticipant/{eventid}', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@getPlannedParticipant');
                Route::get('seminar-planner/documents/{event_id}', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@getDocuments');
                Route::get('seminar-planner/seminar_budget/{event_id}', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@getSeminarBudget');
                Route::get('seminar-planner/seminar_seat_allocation/{event_id}', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@seminarSeatAllocation');
                Route::post('seminar-planner/allocateSeat/details/{event_id}/{allocation_id?}', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@saveSeatAllocation');
                Route::get('seminar-planner/utilizeSeat/details/{event_id?}', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@seminarUtilizationData');
                Route::get('seminar-planner/allocationData/{event_id}', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@seminarAllocationData');
                Route::get('seminar-planner/tasks/{event_id}', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@getActivities');
                Route::get('seminar-planner/getschedule/{event_id}', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@getSchedules');
                Route::put('seminar-planner/savedescription/{id}', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@savedescription');
                Route::get('seminar-planner/getDetailForm/{id}', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@getDetailForm');
                Route::get('seminar-planner/getselectedSeminar', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@getSelectedSeminar');
                Route::get('seminar-planner/getSeminarPlannedBy', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@getSeminarPlannedBy');
                Route::get('seminar-planner/getTrainer', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@getTrainer');
                Route::get('seminar-planner/getLocation', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@getLocation');
                Route::get('seminar-planner/getCategory', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@getSeminarCategory');
                Route::get('seminar-planner/getDetails', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@getSeminarTable');
                Route::get('seminar-planner/getSeminarBluePrints', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@getSeminarBluePrints');
                Route::post('seminar-planner/insertBlueprintAsDraftEvent', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@insertBlueprintAsDraftEvent');
                Route::post('seminar-planner/updatePlannedEvent', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@updatePlannedSeminarsSchedule');
                Route::get('seminar-planner/calendar/getPlannedSeminars/{id?}', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@getPlannedSeminars');
                Route::get('seminar-planner/getPlannedSeminar/{id}', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@getPlannedSeminarById');
                Route::get('seminar-planner/planned-seminar/get-schedule-slot/{plannedEventId}', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@getScheduleSlotPlannedEvent');
                Route::get('seminar-planner/assign-trainer-to-slot/{slotId}/{scheduleId}/{trainerId}', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@assignTrainerToSlot');
                Route::get('seminar-planner/assign-room-to-slot/{slotId}/{scheduleId}/{roomId}', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@assignRoomToSlot');
                Route::get('seminar-planner/assign-location-to-schedule/{locationId}/{scheduleId}/{scheduleDate}', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@assignLocationToSchedule');
                Route::get('seminar-planner/confirm-seminar/{eventId}', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@confirmSeminar');
                Route::post('seminar-planner/cancel-seminar/{eventId}', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@cancelSeminar');
                Route::get('seminar-planner/delete-seminar/{eventId}', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@deleteSeminar');
                Route::get('seminar-planner/remove-trainer/{scheduleId}/{slotId}/{trainerId}', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@removeTrainer');
                Route::get('seminar-planner/create-task/{seminarId}/{taskForTrainer}/{taskForLocation}/{action}', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@createTaskForTrainerLocation');
                Route::get('seminar-planner/getevent/{id}', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@index');
                Route::get('seminar-planner/scheduleSlot/delete/{id}', 'Ptlyash\SeminarPlanner\Controllers\PlannedScheduleController@deleteScheduleSlot');
                Route::post('seminar-planner/schedule/validateschedule/', 'Ptlyash\SeminarPlanner\Controllers\PlannedScheduleController@validateScheduleDate');
                Route::post('seminar-planner/schedule/validate/{id}', 'Ptlyash\SeminarPlanner\Controllers\PlannedScheduleController@validateSchedule');
                Route::post('seminar-planner/savePlanned/{id}', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@savePlannedEvents');
                Route::Resource('seminar-planner/schedule', 'Ptlyash\SeminarPlanner\Controllers\PlannedScheduleController');
                Route::resource('seminar-planner', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController');
                Route::Resource('seminar-planner/activity', 'Ptlyash\SeminarPlanner\Controllers\PlannedTaskController');
                Route::Get('seminar-planner/download_document/{document_id?}', 'Ptlyash\SeminarPlanner\Controllers\PlannedDocumentController@download_document');
                Route::post('seminar-planner/document/{document_id?}', 'Ptlyash\SeminarPlanner\Controllers\PlannedDocumentController@store');
                Route::Resource('seminar-planner/document', 'Ptlyash\SeminarPlanner\Controllers\PlannedDocumentController');

                Route::get('seminar-planner/seminar/revenue-list/{event_id}', 'Ptlyash\SeminarPlanner\Controllers\SeminarPlannerController@seminarRevenueList');
            });
        });
    });
});