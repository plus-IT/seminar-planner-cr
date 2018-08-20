<?php
namespace App;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class EmailTemplate_4_30_2018_training_materials_send_mail extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('emailtemplate')->where('template_type', 'training_materials')->delete();
        \DB::table('emailtemplate')->insert([
            [
                'name' => 'DB T submission',
                'content' => '<p>Sehr geehrte Damen und Herren,</p>

                                <p>hiermit bestellen wir f&uuml;r folgende Veranstaltung #EVENTNAME# (#EVENTSTARTDATE# - #EVENTENDDATE#) folgende Unterlage:</p>

                                <p>Bestellartikel: #EVENT_ORDERNAME#</p>

                                <p>Anzahl: #EVENT_PARTICIPANT_MAX#</p>

                                <p>mit Lieferdatum: #EVENT_BEFORESTARTDATE#.</p>

                                <p>&nbsp;</p>

                                <p>Die Lieferadresse lautet:</p>

                                <p>#LOCATION_NAME#</p>

                                <p>#LOCATION_CONTACT_NAME#</p>

                                <p>#LOCATION_ADDRESS#</p>

                                <p>#EVENTNAME#</p>

                                <p>&nbsp;</p>

                                <p>Anbei die Kostenstelle &uuml;ber die die Bestellung verrechnet wird: 171215 61000.</p>

                                <p>Bei R&uuml;ckfragen zur Bestellung wenden Sie sich bitte an Viktoria Stemmle (95534060), Mischa G&ouml;ttmann (95513101) oder Jessica Vilbrandt (95531436).</p>
                              ',
                'is_default' => '1',
                'email_template_variable_ids' => "",
                'relation_table_id' => '1',
                'template_type'=>'training_materials_send_email',
                'subject' => 'DB T submission',
                'moduleId' => '5',
                'actionId' => '',
                'is_active' => '1',
                'slug' => 'training_materials'
            ]
        ]);
    }
}
