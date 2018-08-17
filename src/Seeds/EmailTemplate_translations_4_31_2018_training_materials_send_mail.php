<?php
namespace App;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmailTemplate_translations_4_31_2018_training_materials_send_mail extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $emailTemplate = \DB::table('emailtemplate')->where('template_type', 'training_materials')->first();

        DB::table('emailtemplate_translations')->insert([
            [
                'template_id' => $emailTemplate->id,
                'lang' => 'de',
                'content' => '<p>Sehr geehrte Damen und Herren,</p>

                              <p>hiermit bestellen wir für folgende Veranstaltung (#EVENTNAME# #EVENTSTARTDATE# - #EVENTENDDATE#) folgende Unterlagen (#ORDERNAME# Bestellartikel #ORDERNUMBER#  Anzahl Platzhalter max TN Zahl der Veranstaltung)</p>
                              
                              <p>mit Lieferdatum (#BEFORESTARTDATE#).</p>
                              
                              <p>Die Lieferadresse lautet (#LOCATION_NAME#  Veranstaltungsort und nochmals #EVENTNAME#)</p>
                              
                              <p>Anbei die Kostenstelle über die die Bestellung verrechnet wird: 171215 61000.</p>
                              
                              <p>Bei Rückfragen zur Bestellung wenden Sie sich bitte an Viktoria Stemmle (95534060), Mischa Göttmann (95513101) oder Jessica Vilbrandt (95531436).</p>
                              
                              <p>DB T Bestellartikel soll ein neues Feld in der Maske Veranstaltungen - Info sein</p>',
                'name' => 'DB T submission',
                'subject' => 'DB T submission',
                'slug' => 'training_materials'
            ],
            [
                'template_id' => $emailTemplate->id,
                'lang' => 'en',
                'content' => '<p>Sehr geehrte Damen und Herren,</p>

                              <p>hiermit bestellen wir für folgende Veranstaltung (#EVENTNAME# #EVENTSTARTDATE# - #EVENTENDDATE#) folgende Unterlagen (#ORDERNAME# Bestellartikel #ORDERNUMBER#  Anzahl Platzhalter max TN Zahl der Veranstaltung)</p>
                              
                              <p>mit Lieferdatum (#BEFORESTARTDATE#).</p>
                              
                              <p>Die Lieferadresse lautet (#LOCATION_NAME#  Veranstaltungsort und nochmals #EVENTNAME#)</p>
                              
                              <p>Anbei die Kostenstelle über die die Bestellung verrechnet wird: 171215 61000.</p>
                              
                              <p>Bei Rückfragen zur Bestellung wenden Sie sich bitte an Viktoria Stemmle (95534060), Mischa Göttmann (95513101) oder Jessica Vilbrandt (95531436).</p>
                              
                              <p>DB T Bestellartikel soll ein neues Feld in der Maske Veranstaltungen - Info sein</p>',
                'name' => 'DB T submission',
                'subject' => 'DB T submission',
                'slug' => 'training_materials'
            ]
        ]);
    }
}
