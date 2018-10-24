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
                Route::post('seminar-planner/export', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@exportToXml');
                Route::get('seminar-planner/exportlist', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@exportList');
                Route::get('seminar_planned/seat_status/{seat_status}/{event_id}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@changeSeatingMethod');
                Route::get('seminar-planner/getTrainerList', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@getTrainerList');
                Route::get('seminar/getAllCategory', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@getAllCategory');
                Route::get('filter/getAllAgents', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@getAllAgents');
                Route::get('seminar-planner/getLocationList', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@getLocationList');
                Route::post('seminar-planner/event/savePlannedParticipant/{eventid}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@savePlannedParticipantDetail');
                Route::post('seminar-planner/event/revenueCalculate/{eventid}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@storeRevenueCalculate');
                Route::put('seminar-planner/event/addPlannedParticipant/{eventid}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@addPlannedParticipant');
                Route::get('seminar-planner/event/delete/revenueCalculate/{eventid}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@deleteSeminarRevenue');
                Route::get('seminar-planner/event/revenueCalculate/{eventid}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@revenueCalculate');
                Route::get('seminar-planner/event/getPlannedParticipant/{eventid}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@getPlannedParticipant');
                Route::get('seminar-planner/documents/{event_id}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@getDocuments');
                Route::get('seminar-planner/seminar_budget/{event_id}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@getSeminarBudget');
                Route::get('seminar-planner/seminar_seat_allocation/{event_id}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@seminarSeatAllocation');
                Route::post('seminar-planner/allocateSeat/details/{event_id}/{allocation_id?}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@saveSeatAllocation');
                Route::get('seminar-planner/utilizeSeat/details/{event_id?}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@seminarUtilizationData');
                Route::get('seminar-planner/allocationData/{event_id}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@seminarAllocationData');
                Route::get('seminar-planner/tasks/{event_id}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@getActivities');
                Route::get('seminar-planner/getschedule/{event_id}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@getSchedules');
                Route::put('seminar-planner/savedescription/{id}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@savedescription');
                Route::get('seminar-planner/getDetailForm/{id}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@getDetailForm');
                Route::get('seminar-planner/getselectedSeminar', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@getSelectedSeminar');
                Route::get('seminar-planner/getSeminarPlannedBy', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@getSeminarPlannedBy');
                Route::get('seminar-planner/getTrainer', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@getTrainer');
                Route::get('seminar-planner/getLocation', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@getLocation');
                Route::get('seminar-planner/getCategory', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@getSeminarCategory');
                Route::get('seminar-planner/getDetails', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@getSeminarTable');
                Route::get('seminar-planner/getSeminarBluePrints', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@getSeminarBluePrints');
                Route::post('seminar-planner/insertBlueprintAsDraftEvent', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@insertBlueprintAsDraftEvent');
                Route::post('seminar-planner/updatePlannedEvent', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@updatePlannedSeminarsSchedule');
                Route::get('seminar-planner/calendar/getPlannedSeminars/{id?}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@getPlannedSeminars');
                Route::get('seminar-planner/getPlannedSeminar/{id}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@getPlannedSeminarById');
                Route::get('seminar-planner/planned-seminar/get-schedule-slot/{plannedEventId}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@getScheduleSlotPlannedEvent');
                Route::get('seminar-planner/assign-trainer-to-slot/{slotId}/{scheduleId}/{trainerId}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@assignTrainerToSlot');
                Route::get('seminar-planner/assign-room-to-slot/{slotId}/{scheduleId}/{roomId}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@assignRoomToSlot');
                Route::get('seminar-planner/assign-location-to-schedule/{locationId}/{scheduleId}/{scheduleDate}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@assignLocationToSchedule');
                Route::get('seminar-planner/confirm-seminar/{eventId}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@confirmSeminar');
                Route::post('seminar-planner/cancel-seminar/{eventId}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@cancelSeminar');
                Route::get('seminar-planner/delete-seminar/{eventId}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@deleteSeminar');
                Route::get('seminar-planner/remove-trainer/{scheduleId}/{slotId}/{trainerId}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@removeTrainer');
                Route::get('seminar-planner/create-task/{seminarId}/{taskForTrainer}/{taskForLocation}/{action}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@createTaskForTrainerLocation');
                Route::get('seminar-planner/getevent/{id}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@index');
                Route::get('seminar-planner/scheduleSlot/delete/{id}', 'Ptlyash\SeminarPlannerCR\Controllers\PlannedScheduleController@deleteScheduleSlot');
                Route::post('seminar-planner/schedule/validateschedule/', 'Ptlyash\SeminarPlannerCR\Controllers\PlannedScheduleController@validateScheduleDate');
                Route::post('seminar-planner/schedule/validate/{id}', 'Ptlyash\SeminarPlannerCR\Controllers\PlannedScheduleController@validateSchedule');
                Route::post('seminar-planner/savePlanned/{id}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@savePlannedEvents');
                Route::Resource('seminar-planner/schedule', 'Ptlyash\SeminarPlannerCR\Controllers\PlannedScheduleController');
                Route::resource('seminar-planner', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController');
                Route::Resource('seminar-planner/activity', 'Ptlyash\SeminarPlannerCR\Controllers\PlannedTaskController');
                Route::Get('seminar-planner/download_document/{document_id?}', 'Ptlyash\SeminarPlannerCR\Controllers\PlannedDocumentController@download_document');
                Route::post('seminar-planner/document/{document_id?}', 'Ptlyash\SeminarPlannerCR\Controllers\PlannedDocumentController@store');
                Route::Resource('seminar-planner/document', 'Ptlyash\SeminarPlannerCR\Controllers\PlannedDocumentController');

                Route::get('seminar-planner/seminar/revenue-list/{event_id}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@seminarRevenueList');
                Route::get('seminar-planner/updatePlannedMinMaxData/{event_id}','Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@updatePlannedMinMaxData');
                Route::get('seminar-planner/cancel/search_result','Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@searchCancelReason');
                
                Route::get('seminar-planner/training_materials/{event_id}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@getTrainnerMaterials');
                Route::get('/seminar-planner/add_training_materials/{id}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@addTrainingMaterials');
                Route::put('/seminar-planner/save_training_materials/{id}', 'Ptlyash\SeminarPlannerCR\Controllers\SeminarPlannerController@saveTrainingMaterials');
            });
        });
    });
});