<?php

namespace Ptlyash\SeminarPlannerCR;


use Illuminate\Support\ServiceProvider;
use Ptlyash\SeminarPlannerCR\Repositories\PlannedDocumentRepository;
use Ptlyash\SeminarPlannerCR\Repositories\PlannedScheduleRepository;
use Ptlyash\SeminarPlannerCR\Repositories\PlannedTaskRepository;
use Ptlyash\SeminarPlannerCR\Repositories\SeminarPlannerRepository;


class SeminarPlannerCRServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        require __DIR__ . "/routes.php";

        $this->publishes([
            __DIR__ . '/Views/' => base_path('resources/views/seminar_planner'),

        ]);

        // Publish the Migrations
//        $this->publishes([
//            __DIR__ . '/Migrations' => base_path('database/migrations')
//        ]);
        $this->publishes([
            __DIR__ . '/Seeds' => base_path('database/smartSeeds')
        ]);

        // Publish the Migrations
        $this->publishes([
            __DIR__ . '/Translations/en' => base_path('resources/lang/en'),
            __DIR__ . '/Translations/de' => base_path('resources/lang/de')
        ]);

        // Publish the Migrations
//        $this->publishes([
//            __DIR__ . '/Models' => base_path('app/Models')
//        ]);

        // Publish assets
        $this->publishes([
            __DIR__ . '/Assets/js' => public_path('js/seminar_planner'),
            __DIR__ . '/Assets/css' => public_path('css/seminar_planner')
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('Ptlyash\SeminarPlannerCR\Interfaces\SeminarPlannerRepositoryInterface', function ($app) {
            return new SeminarPlannerRepository();
        });
        $this->app->bind('Ptlyash\SeminarPlannerCR\Interfaces\PlannedScheduleRepositoryInterface', function ($app) {
            return new PlannedScheduleRepository();
        });
        $this->app->bind('Ptlyash\SeminarPlannerCR\Interfaces\PlannedTaskRepositoryInterface', function ($app) {
            return new PlannedTaskRepository();
        });
        $this->app->bind('Ptlyash\SeminarPlannerCR\Interfaces\PlannedDocumentRepositoryInterface', function ($app) {
            return new PlannedDocumentRepository();
        });


    }
}
