<?php
global $smarty, $oPlugin;
require_once(PFAD_ROOT.PFAD_PLUGIN.$oPlugin->cVerzeichnis."/".PFAD_PLUGIN_VERSION.$oPlugin->nVersion . '/includes/mailchimp/Mailchimp.php');
require_once(PFAD_ROOT.PFAD_PLUGIN.$oPlugin->cVerzeichnis."/".PFAD_PLUGIN_VERSION.$oPlugin->nVersion . '/includes/defines.php');
require_once(PFAD_ROOT.PFAD_PLUGIN.$oPlugin->cVerzeichnis."/".PFAD_PLUGIN_VERSION.$oPlugin->nVersion . '/includes/functions.php');

$cEinstellungen_arr = ladeEinstellungen();
$oSprachen_arr = $GLOBALS["DB"]->executeQuery("SELECT * FROM tsprache", 2);

if (isset($cEinstellungen_arr["api_key"]) && !empty($cEinstellungen_arr["api_key"]))
{
    $oMCAPI = new Mailchimp($cEinstellungen_arr["api_key"]);
    $oLists_arr = $oMCAPI->lists->getList();
}
else
{
    Jtllog::writeLog("MailChimp: API-Key nicht gesetzt!", JTLLOG_LEVEL_ERROR, false, "kPlugin", $oPlugin->kPlugin);
}

if($_POST["prepareImport"])
{
	$GLOBALS["DB"]->executeQuery("TRUNCATE TABLE ".TABLE_QUEUE, 3);	

    $cFields = "tnewsletterempfaenger.kNewsletterEmpfaenger,tnewsletterempfaenger.kSprache,tnewsletterempfaenger.cAnrede,tnewsletterempfaenger.cVorname,tnewsletterempfaenger.cNachname,tnewsletterempfaenger.cEmail";
    foreach($cEinstellungen_arr as $cKey => $cVal)
    {
        if(strpos($cKey, "list") !== false)
        {
            if($cKey == "list_no_account")
            {
                $cSQL = "SELECT ".$cFields." FROM tnewsletterempfaenger 
                            WHERE nAktiv=1 AND kKunde=0 
                            AND tnewsletterempfaenger.kNewsletterEmpfaenger NOT IN (SELECT kNewsletterEmpfaenger FROM ".TABLE_SYNC.")";
            } 
            else
            {
                $cSQL = "SELECT ".$cFields.", tnewsletterempfaenger . * FROM tnewsletterempfaenger INNER JOIN tkunde ON tkunde.kKunde = tnewsletterempfaenger.kKunde 
                            WHERE nAktiv =1 AND tnewsletterempfaenger.kKunde > 0 AND tkunde.kKundengruppe=".substr($cKey, 20)." 
                            AND tnewsletterempfaenger.kNewsletterEmpfaenger NOT IN (SELECT kNewsletterEmpfaenger FROM ".TABLE_SYNC.")";               
            }
                
            $oNewsletterEmpfaenger_arr = $GLOBALS["DB"]->executeQuery($cSQL, 2);

            if(empty($oNewsletterEmpfaenger_arr))
                continue;

            $xBatch_arr = array();
            foreach($oNewsletterEmpfaenger_arr as $oNewsletterEmpfaenger)
            {
                $cMergeVars_arr = array(
                    "FNAME" => $oNewsletterEmpfaenger->cVorname,
                    "LNAME" => $oNewsletterEmpfaenger->cNachname,
                    "CANREDE" => ($oNewsletterEmpfaenger->cAnrede == "w" ? "Frau" : "Herr"),
                    "optin_time" => date("Y-m-d H:i:s"),
                    "optin_ip" => $_SERVER["REMOTE_ADDR"]
                );

                foreach($oSprachen_arr as $oSprache)
                {
                    if($oSprache->kSprache == $oNewsletterEmpfaenger->kSprache)
                    {
                        if($oSprache->cNameEnglisch == "German")
                        {
                            $cMergeVars_arr["mc_language"] = "de";    
                        } 
                        elseif($oSprache->cNameEnglisch == "English")
                        {
                            $cMergeVars_arr["mc_language"] = "en";
                        }
                        break;
                    }
                }

                $cEmailStruct_arr = array(
                    "email" => $oNewsletterEmpfaenger->cEmail,
                    "euid" => $oNewsletterEmpfaenger->kNewsletterEmpfaenger
                );

                $xBatch_arr[$oNewsletterEmpfaenger->kNewsletterEmpfaenger] = array();
                $xBatch_arr[$oNewsletterEmpfaenger->kNewsletterEmpfaenger]["email"] = $cEmailStruct_arr;
                $xBatch_arr[$oNewsletterEmpfaenger->kNewsletterEmpfaenger]["email_type"] = "html";
                $xBatch_arr[$oNewsletterEmpfaenger->kNewsletterEmpfaenger]["merge_vars"] = $cMergeVars_arr;
            }

            if(!empty($xBatch_arr))
            {
            	$oBatch = new stdClass();
            	$oBatch->cListId = $cVal;
            	foreach($xBatch_arr as $xBatchItem_arr)
            	{
            		$cData = serialize($xBatchItem_arr);
            		$oBatch->cData = $cData;
            		$GLOBALS["DB"]->insertRow(TABLE_QUEUE, $oBatch);	
            	}	
            }
        }
    }
}


$cSQL = "SELECT COUNT(kNewsletterEmpfaenger) as nCount FROM tnewsletterempfaenger WHERE nAktiv=1";
$nEmpfaengerCount_arr = $GLOBALS["DB"]->executeQuery($cSQL, 8);

$cSQL = "SELECT COUNT(kSync) as nCount FROM ".TABLE_SYNC;
$nSyncCount_arr = $GLOBALS["DB"]->executeQuery($cSQL, 8);

$smarty->assign("cAdminmenuPfadURL", $oPlugin->cAdminmenuPfadURL);
$smarty->assign("nEmpfaengerCount", $nEmpfaengerCount_arr["nCount"]);
$smarty->assign("nSyncCount", $nSyncCount_arr["nCount"]);
print($smarty->fetch($oPlugin->cAdminmenuPfad . "templates/import.tpl"));

?>