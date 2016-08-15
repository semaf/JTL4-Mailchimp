<?php

global $smarty, $oPlugin;
require_once(PFAD_ROOT.PFAD_PLUGIN.$oPlugin->cVerzeichnis."/".PFAD_PLUGIN_VERSION.$oPlugin->nVersion . '/includes/mailchimp/Mailchimp.php');
require_once(PFAD_ROOT.PFAD_PLUGIN.$oPlugin->cVerzeichnis."/".PFAD_PLUGIN_VERSION.$oPlugin->nVersion . '/includes/defines.php');
require_once(PFAD_ROOT.PFAD_PLUGIN.$oPlugin->cVerzeichnis."/".PFAD_PLUGIN_VERSION.$oPlugin->nVersion . '/includes/functions.php');
$cEinstellungen_arr = ladeEinstellungen();

if (isset($cEinstellungen_arr["api_key"]) && !empty($cEinstellungen_arr["api_key"]))
{
    $oMCAPI = new Mailchimp($cEinstellungen_arr["api_key"]);
    $oLists_arr = $oMCAPI->lists->getList();

    $cSQL = "SELECT IF(tnewsletterempfaenger.cAnrede = 'm','Herr','Frau') as cAnrede, tnewsletterempfaenger.cVorname, 
    tnewsletterempfaenger.cNachname, tnewsletterempfaenger.cEmail, tnewsletterempfaenger.dEingetragen, ".TABLE_SYNC.".*, tkundengruppe.cName as cKundengruppe
    FROM tnewsletterempfaenger
    LEFT JOIN ".TABLE_SYNC." ON tnewsletterempfaenger.kNewsletterEmpfaenger=".TABLE_SYNC.".kNewsletterEmpfaenger
    LEFT JOIN tkunde ON tkunde.kKunde=tnewsletterempfaenger.kKunde
    LEFT JOIN tkundengruppe ON tkunde.kKundengruppe=tkundengruppe.kKundengruppe";

    $oNewsletterEmpfaenger_arr = $GLOBALS["DB"]->executeQuery($cSQL, 2);

    if(!empty($oNewsletterEmpfaenger_arr))
    {
        foreach($oNewsletterEmpfaenger_arr as $oNewsletterEmpfaenger)
        {
            foreach($oLists_arr["data"] as $xList_arr)
            {
                if($xList_arr["id"] == $oNewsletterEmpfaenger->cListId)
                {
                    $oNewsletterEmpfaenger->cList = utf8_decode($xList_arr["name"]);
                    continue;
                }
            }
        }
    }

    $smarty->assign("oNewsletterEmpfaenger_arr", $oNewsletterEmpfaenger_arr);
    $smarty->assign("oLists_arr", $oLists_arr);
}
else
{
    Jtllog::writeLog("MailChimp: API-Key nicht gesetzt!", JTLLOG_LEVEL_ERROR, false, "kPlugin", $oPlugin->kPlugin);
}


print($smarty->fetch($oPlugin->cAdminmenuPfad . "templates/abonnenten.tpl"));
?>