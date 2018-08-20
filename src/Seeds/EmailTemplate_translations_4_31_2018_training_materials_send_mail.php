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

                                <p>Bei R&uuml;ckfragen zur Bestellung wenden Sie sich bitte an Viktoria Stemmle (95534060), Mischa G&ouml;ttmann (95513101) oder Jessica Vilbrandt (95531436).</p>',
                'name' => 'DB T submission',
                'subject' => 'DB T submission',
                'slug' => 'training_materials'
            ],
            [
                'template_id' => $emailTemplate->id,
                'lang' => 'en',
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

                                <p>Bei R&uuml;ckfragen zur Bestellung wenden Sie sich bitte an Viktoria Stemmle (95534060), Mischa G&ouml;ttmann (95513101) oder Jessica Vilbrandt (95531436).</p>',
                'name' => 'DB T submission',
                'subject' => 'DB T submission',
                'slug' => 'training_materials'
            ]
        ]);
    }
}
