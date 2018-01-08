<?php

use Illuminate\Database\Seeder;

class AllocationLevelTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('allocation_level')->delete();
        $allocation_level = array(
            array('LevelID' => '1', 'name' => 'head office'),
            array('LevelID' => '2', 'name' => 'region'),
            array('LevelID' => '3', 'name' => 'city')
        );
        \DB::table('allocation_level')->insert($allocation_level);
    }
}
