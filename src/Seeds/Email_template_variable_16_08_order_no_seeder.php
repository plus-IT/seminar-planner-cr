<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class Email_template_variable_16_08_order_no_seeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        DB::table('email_template_variable')->insert([
            [
                'moduleId'=>'5',
                'module_name' => 'Event',
                'variable_text' => '#ORDERNAME#',
                'variable_value' => 'OrderName',
                'actual_variable_name' => 'order_name',
                'variable_object_name' => 'seminarDetails',
                'variable_type' => 'singleLine'
            ],
            [
                'moduleId'=>'5',
                'module_name' => 'Event',
                'variable_text' => '#ORDERNUMBER#',
                'variable_value' => 'OrderNumber',
                'actual_variable_name' => 'order_no',
                'variable_object_name' => 'seminarDetails',
                'variable_type' => 'singleLine'
            ],
            [
                'moduleId'=>'5',
                'module_name' => 'Event',
                'variable_text' => '#BEFORESTARTDATE#',
                'variable_value' => 'EventBeforeStartdate',
                'actual_variable_name' => 'event_startdate',
                'variable_object_name' => 'seminarDetails',
                'variable_type' => 'singleLine'
            ]
        ]);
    }

}
