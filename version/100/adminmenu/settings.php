<?php

global $smarty, $oPlugin;
require_once(PFAD_ROOT.PFAD_PLUGIN.$oPlugin->cVerzeichnis."/".PFAD_PLUGIN_VERSION.$oPlugin->nVersion . '/includes/mailchimp/Mailchimp.php');
require_once(PFAD_ROOT.PFAD_PLUGIN.$oPlugin->cVerzeichnis."/".PFAD_PLUGIN_VERSION.$oPlugin->nVersion . '/includes/defines.php');
require_once(PFAD_ROOT.PFAD_PLUGIN.$oPlugin->cVerzeichnis."/".PFAD_PLUGIN_VERSION.$oPlugin->nVersion . '/includes/functions.php');
$oKundengruppen_arr = $GLOBALS["DB"]->executeQuery("SELECT * FROM tkundengruppe", 2);
$cEinstellungen_arr = ladeEinstellungen();

if (isset($cEinstellungen_arr["api_key"]) && !empty($cEinstellungen_arr["api_key"]))
{
    $oMCAPI = new Mailchimp($cEinstellungen_arr["api_key"]);
    $oLists_arr = $oMCAPI->lists->getList();
}

if (isset($_POST["settings"]) && !empty($_POST["settings"]))
{
    if (isset($_POST["settings"]["api_key"]) && !empty($_POST['settings']["api_key"]))
    {
        foreach ($_POST["settings"] as $cKey => $cValue)
        {
            $cSQL = "SELECT * FROM  ".TABLE_EINSTELLUNGEN." WHERE cName='" . $cKey . "'";
            $nFound = $GLOBALS["DB"]->executeQuery($cSQL, 3);

            if ($nFound > 0)
            {
                $oEinstellung = $GLOBALS["DB"]->executeQuery($cSQL, 1);
                if ($oEinstellung->cWert != $cValue)
                {
                    $kEinstellung = $oEinstellung->kEinstellung;
                    unset($oEinstellung->kEinstellung);
                    $oEinstellung->cWert = $cValue;
                    $oEinstellung->dGeandert = date("Y-m-d H:i:s");
                    $GLOBALS["DB"]->updateRow(TABLE_EINSTELLUNGEN, "kEinstellung", $kEinstellung, $oEinstellung);
                }
            } 
            else
            {
                $oEinstellung = new stdClass();
                $oEinstellung->cName = $cKey;
                $oEinstellung->cWert = $cValue;
                $oEinstellung->dErstellt = date("Y-m-d H:i:s");
                $GLOBALS["DB"]->insertRow(TABLE_EINSTELLUNGEN, $oEinstellung);
            }
        }
    }

    // Einstellungen neu laden da diese sich mittlerweile geändert haben könnten
    $cEinstellungen_arr = ladeEinstellungen();
    if (isset($cEinstellungen_arr["api_key"]) && !empty($cEinstellungen_arr["api_key"]))
    {
        $oMCAPI = new Mailchimp($cEinstellungen_arr["api_key"]);
        $oLists_arr = $oMCAPI->lists->getList();
    }
}

$smarty->assign("cEinstellungen_arr", $cEinstellungen_arr);
$smarty->assign("nSettingRef", 3);
$smarty->assign("oLists_arr", $oLists_arr);
$smarty->assign("oKundengruppen_arr", $oKundengruppen_arr);
print($smarty->fetch($oPlugin->cAdminmenuPfad . "templates/settings.tpl"));
?>