<?php
/**
 * Plugin Name: CI Gruppenkalender
 * Description: Zeigt den Gruppenkalender über den Shortcode [ci_Gruppenkalender] an.
 * Version: 1.0
 * Author: Sieber Engineering AG
 */
$errordebug=false;
//$errordebug=true;
if($errordebug)
{
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);         // schreibt Fehler in wp-content/debug.log
define('WP_DEBUG_DISPLAY', true);     // zeigt Fehler direkt im Browser an
@ini_set('display_errors', 1);        // optional: überschreibt PHP-Einstellung
}
require_once plugin_dir_path(__FILE__) . 'admin/ci-gruppenkalender-settings.php';

add_shortcode('ci_Gruppenkalender', 'ci_gruppenkalender_shortcode');

function ci_gruppenkalender_shortcode($atts) {

require_once plugin_dir_path(__FILE__) . 'functions.php';
$Bild =array();

$Bild="https://test.jhs.ch/wp-content/uploads/2025/01/SA702321";

$block = '
<div class="wp-block-obb-link-block organic-block obb-link wp-block-obb-link-blockci"
data-altersgruppe="{Altersgruppe}" 
data-veranstaltung="{Veranstaltung}" 
data-beginn="{Beginn}" 
data-ende="{Ende}" 
data-eventid="{Anreiseab}" 
data-eventid="{Abholung}" 
data-eventid="{Seminarkosten}" 
data-eventid="{PDF}" 
data-beschreibung="{Beschreibung}">
<a class="obb-link-overlay .obb-link-overlayci" href="https://test.jhs.ch/3-gruppen/" target="" rel="noopener"></a><div class="obb-link-content">
<div class="rahmenhoch">
<div class="randhoch">&nbsp;</div>



<div class="wp-block-cover wp-block-coverci has-custom-content-position is-position-bottom-left has-roboto-font-family" style="margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--30);padding-left:var(--wp--preset--spacing--40);font-size:0rem;font-style:normal;font-weight:400;min-height:300px;aspect-ratio:unset;"><img decoding="async" width="2560" height="1707" class="wp-block-cover__image-background wp-block-cover__image-backgroundci wp-image-8083 size-full" alt="" src="{Bild}" style="object-position:50% 66%" data-object-fit="cover" data-object-position="50% 66%" srcset="{Bild2560} 2560w, {Bild300} 300w, {Bild1024} 1024w, {Bild768} 768w, {Bild1536} 1536w, {Bild2048} 2048w, {Bild980} 980w" sizes="(max-width: 2560px) 100vw, 2560px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-40 has-background-dim wp-block-cover__gradient-background has-background-gradient" style="background:linear-gradient(2deg,rgb(0,0,0) 0%,rgba(155,81,224,0) 100%)"></span><div class="wp-block-cover__inner-container wp-block-cover__inner-containerci is-layout-flow wp-block-cover-is-layout-flow">
<div class="wp-block-group is-layout-constrained wp-container-core-group-is-layout-d89aad35 wp-block-group-is-layout-constrained" style="margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)">
</div>
</div></div>
</div>
<p class="veranstaltungs-grid-titel">{Titel}</p><p class="veranstaltungs-grid-datum">{Beginnf} - {Endef}</p>
</div></div>
';

$blocks="";

/*** Events ***/
$url = CI_API_BASE . '/pxapi/v4/events?grp=5&filter=(ZUS_Kool_Gruppen.GruppenID=15%20OR%20ZUS_Kool_Gruppen.GruppenID=19%20OR%20ZUS_Kool_Gruppen.GruppenID=34)';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: BEARER ' . get_option('ci_gruppenkalender_api_key')
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$js=json_decode($response);

$data=array();
for($i=0;$i<sizeof($js);$i++)
{
  $data[$js[$i]->Veranstaltung].="{";
  $data[$js[$i]->Veranstaltung].="\"Eventid\":".$js[$i]->Eventid.",";
  $data[$js[$i]->Veranstaltung].="\"Beginn\":\"".$js[$i]->Beginn."\",";
  $data[$js[$i]->Veranstaltung].="\"Ende\":\"".$js[$i]->Ende."\"";
  $data[$js[$i]->Veranstaltung].="},";
}
$script="<script>";
$script.="var belegt=[".trim($data["belegt"],",")."];";
$script.="var frei=[".trim($data["frei"],",")."];";
$script.="var teilweisebelegt=[".trim($data["teilweise belegt"],",")."];";
$script.="</script>";

return $script.'
<div class="veranstaltungs-grid">
  <div class="custom-availability-calendar">
  <h2 class="wp-block-heading has-text-align-center has-accent-primary-color has-text-color has-link-color wp-elements-fba73e3dffddbb4a56c8a9b5e4cccb5c">Verfügbarkeit</h2>

   <div class="jhs-cal-wrapper">
  <div class="jhs-cal-legend">
  <span class="jhs-badge jhs-badge--free">Ganzes Haus</span>
  <span class="jhs-badge jhs-badge--booked">ausgebucht</span>
  <span class="jhs-badge jhs-badge--partial">teilweise belegt</span>
</div>

  <div class="jhs-cal-nav">
    <button type="button" id="cal-prev" aria-label="Vorheriger Monat">‹</button>
    <button type="button" id="cal-next" aria-label="Nächster Monat">›</button>
  </div>
</div>

<div class="jhs-cal-grids">
  <div id="cal-0" class="jhs-cal"></div>
  <div id="cal-1" class="jhs-cal"></div>
  <div id="cal-2" class="jhs-cal"></div>
</div>

    </div>
</div>

';
}