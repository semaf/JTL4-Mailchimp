<?php 
require_once(PFAD_ROOT.PFAD_PLUGIN.$oPlugin->cVerzeichnis."/".PFAD_PLUGIN_VERSION.$oPlugin->nVersion . '/includes/mailchimp/Mailchimp.php');
require_once(PFAD_ROOT.PFAD_PLUGIN.$oPlugin->cVerzeichnis."/".PFAD_PLUGIN_VERSION.$oPlugin->nVersion . '/includes/defines.php');
require_once(PFAD_ROOT.PFAD_PLUGIN.$oPlugin->cVerzeichnis."/".PFAD_PLUGIN_VERSION.$oPlugin->nVersion . '/includes/functions.php');
$cEinstellungen_arr = ladeEinstellungen();

if (isset($args_arr["oNewsletterEmpfaenger"]) && is_object($args_arr["oNewsletterEmpfaenger"]))
{
    if (isset($cEinstellungen_arr["api_key"]) && !empty($cEinstellungen_arr["api_key"]))
    {
        $oMCAPI = new Mailchimp($cEinstellungen_arr["api_key"]);
        $oNewsletterEmpfaenger = $args_arr["oNewsletterEmpfaenger"];
        $oSync = ladeSyncEintrag($oNewsletterEmpfaenger->kNewsletterEmpfaenger);

        $cEmailStruct_arr = array(
        	"email" => $oNewsletterEmpfaenger->cEmail,
        	"euid" => $oSync->cEUID,
        	"leid" => $oSync->cLEID
    	);

        try
        {
            $cResponse_arr = $oMCAPI->lists->unsubscribe($oSync->cListId, $cEmailStruct_arr, (bool) $cEinstellungen_arr["delete_member"], (bool) $cEinstellungen_arr["send_goodbye"], (bool) $cEinstellungen_arr["send_notify"]);
        } 
        catch (Exception $oException)
        {
            Jtllog::writeLog("MailChimp: {$oException->getMessage()}", JTLLOG_LEVEL_ERROR, false, "kPlugin", $oPlugin->kPlugin);
        }

        if($cResponse_arr["complete"] === true)
        {
        	$GLOBALS["DB"]->deleteRow(TABLE_SYNC,"kSync",$oSync->kSync);
        	Jtllog::writeLog("MailChimp: Newsletter Empfaenger erfolgreich gel&ouml;scht ".print_r($oNewsletterEmpfaenger,true), JTLLOG_LEVEL_NOTICE, false, "kPlugin", $oPlugin->kPlugin);
        }
    }
    else
    {
        Jtllog::writeLog("MailChimp: API-Key nicht gesetzt!", JTLLOG_LEVEL_ERROR, false, "kPlugin", $oPlugin->kPlugin);
    }
}
?>