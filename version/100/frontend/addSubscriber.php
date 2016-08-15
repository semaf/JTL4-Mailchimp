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

        if ($oNewsletterEmpfaenger->kKunde > 0)
        {
            $oKunde = $GLOBALS["DB"]->executeQuery("SELECT * FROM tkunde WHERE kKunde=" . $oNewsletterEmpfaenger->kKunde, 1);

            if (isset($oKunde) && is_object($oKunde))
            {
                $oListId = $GLOBALS["DB"]->executeQuery("SELECT cWert FROM " . TABLE_EINSTELLUNGEN . " WHERE cName='list_customer_group_" . $oKunde->kKundengruppe . "'", 1);
            }
        }
        else
        {
            $oListId = $GLOBALS["DB"]->executeQuery("SELECT * FROM " . TABLE_EINSTELLUNGEN . " WHERE cName='list_no_account'", 1);
        }

        if (!empty($oListId->cWert))
        {
            $cMergeVars_arr = array(
                "FNAME" => $oNewsletterEmpfaenger->cVorname,
                "LNAME" => $oNewsletterEmpfaenger->cNachname,
                "CANREDE" => ($oNewsletterEmpfaenger->cAnrede == "w" ? "Frau" : "Herr"),
                "optin_time" => date("Y-m-d H:i:s"),
                "optin_ip" => $_SERVER["REMOTE_ADDR"]
            );

            $oSprache = $GLOBALS["DB"]->executeQuery("SELECT * FROM tsprache WHERE kSprache = ".$oNewsletterEmpfaenger->kSprache, 1);
            
            if(is_object($oSprache))
            {
                if($oSprache->cNameEnglisch == "German")
                {
                    $cMergeVars_arr["mc_language"] = "de";    
                } 
                elseif($oSprache->cNameEnglisch == "English")
                {
                    $cMergeVars_arr["mc_language"] = "en";
                }
                
            }

            $cEmailStruct_arr = array(
                "email" => $oNewsletterEmpfaenger->cEmail,
                "euid" => $oNewsletterEmpfaenger->kNewsletterEmpfaenger
            );

            try
            {
                $cResponse_arr = $oMCAPI->lists->subscribe($oListId->cWert, $cEmailStruct_arr, $cMergeVars_arr, "html", false, false, true, (bool) $cEinstellungen_arr["send_welcome"]);
            } 
            catch (Exception $oException)
            {
                Jtllog::writeLog("MailChimp: {$oException->getMessage()}", JTLLOG_LEVEL_ERROR, false, "kPlugin", $oPlugin->kPlugin);
            }

            $oMailChimpSync = new stdClass();
            $oMailChimpSync->kNewsletterEmpfaenger = $oNewsletterEmpfaenger->kNewsletterEmpfaenger;
            $oMailChimpSync->cListId = $oListId->cWert;
            $oMailChimpSync->cEUID = $cResponse_arr["euid"];
            $oMailChimpSync->cLEID = $cResponse_arr["leid"];
            $oMailChimpSync->dSync = date("Y-m-d H:i:s");

            $GLOBALS["DB"]->insertRow(TABLE_SYNC,$oMailChimpSync);

            Jtllog::writeLog("MailChimp: Newsletter Empfänger '".$oNewsletterEmpfaenger->cEmail."' zu MailChimp übertragen.", JTLLOG_LEVEL_NOTICE, false, "kPlugin", $oPlugin->kPlugin);

        } 
        else
        {
            Jtllog::writeLog("MailChimp: Keine Liste für die Empfänger Gruppe gesetzt!", JTLLOG_LEVEL_ERROR, false, "kPlugin", $oPlugin->kPlugin);
        }
    }
    else
    {
        Jtllog::writeLog("MailChimp: API-Key nicht gesetzt!", JTLLOG_LEVEL_ERROR, false, "kPlugin", $oPlugin->kPlugin);
    }
}
else
{
    Jtllog::writeLog("MailChimp: Hook übergibt Newsletter Empfänger nicht!", JTLLOG_LEVEL_ERROR, false, "kPlugin", $oPlugin->kPlugin);
}
?>