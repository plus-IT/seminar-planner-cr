<?php

use Illuminate\Database\Seeder;
use App\Models\MenuItem;

class SeminarPlannerMenuAddSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        {
            $contact = MenuItem::where("link", "seminar-planner")->first();
            if (count($contact) < 1) {
                $locationRootMenu = MenuItem::whereNull("parent_id")->where('translation_label', 'general.event')->first();
                if (count($locationRootMenu) < 1) {
                    DB::table('menu_items')->insert([
                        [
                            'parent_id' => null,
                            'sort_order' => 4,
                            'label' => 'event',
                            'description' => 'Event',
                            'link' => 'javascript:;',
                            'translation_label' => 'general.event',
                            'permission' => '',
                            'icon' => 'sideicon4',
                            'is_active' => 1
                        ], [
                            'parent_id' => 4,
                            'sort_order' => 4,
                            'label' => 'general.event',
                            'description' => 'Seminar Planner',
                            'link' => 'seminar-planner',
                            'translation_label' => 'general.seminarPlannerMenu',
                            'permission' => 'seminarPlanner.view',
                            'icon' => '',
                            'is_active' => 1
                        ]
                    ]);
                } else {
                    DB::table('menu_items')->insert([
                        [
                            'parent_id' => 4,
                            'sort_order' => 4,
                            'label' => 'general.event',
                            'description' => 'Seminar Planner',
                            'link' => 'seminar-planner',
                            'translation_label' => 'general.seminarPlannerMenu',
                            'permission' => 'seminarPlanner.view',
                            'icon' => '',
                            'is_active' => 1
                        ]
                    ]);
                }
            }
        }
    }
}

