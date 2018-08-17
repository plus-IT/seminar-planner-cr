<?php
namespace App;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class Email_template_modules_actions_16_08_05_13_for_training_materials extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('email_template_modules_actions')->insert([
            [
                'moduleId' => 5,
                'actionName' => 'Inform organisation for training materials ',
                'actionSlug' => 'training-materials',
            ]
        ]);
    }
}
