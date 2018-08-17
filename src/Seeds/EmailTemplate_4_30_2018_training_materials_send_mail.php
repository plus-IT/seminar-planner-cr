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

                              <p>hiermit bestellen wir für folgende Veranstaltung (#EVENTNAME# #EVENTSTARTDATE# - #EVENTENDDATE#) folgende Unterlagen (#ORDERNAME# Bestellartikel #ORDERNUMBER#  Anzahl Platzhalter max TN Zahl der Veranstaltung)</p>
                              
                              <p>mit Lieferdatum (#BEFORESTARTDATE#).</p>
                              
                              <p>Die Lieferadresse lautet (#LOCATION_NAME#  Veranstaltungsort und nochmals #EVENTNAME#)</p>
                              
                              <p>Anbei die Kostenstelle über die die Bestellung verrechnet wird: 171215 61000.</p>
                              
                              <p>Bei Rückfragen zur Bestellung wenden Sie sich bitte an Viktoria Stemmle (95534060), Mischa Göttmann (95513101) oder Jessica Vilbrandt (95531436).</p>
                              
                              <p>DB T Bestellartikel soll ein neues Feld in der Maske Veranstaltungen - Info sein</p>
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
