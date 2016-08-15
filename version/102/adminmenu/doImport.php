<?php

error_reporting(E_ALL);
ini_set("display_errors","1");

require_once("../../../../../globalinclude.php");
require_once("../includes/mailchimp/Mailchimp.php");
require_once("../includes/defines.php");
require_once("../includes/functions.php");

global $smarty, $oPlugin;

$cSyncFile = "queue";

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

$cSyncImportLimit = 25;
$cSyncFile = "queue";

if(file_exists($cSyncFile))
{
    $cFile = file_get_contents($cSyncFile);
    $oQueue = json_decode($cFile);
    $bFirst = false;
} 
else
{
    $oFile = fopen($cSyncFile, "a+");
    $oQueue = new stdClass();
    $cSQL = "SELECT COUNT(kQueue) as nCount FROM ".TABLE_QUEUE;
    $oResult = $GLOBALS["DB"]->executeQuery($cSQL, 1);
    $oQueue->nMax = $oResult->nCount;
    $oQueue->nCurrent = 0;
    $bFirst = true;
    $cQueue = json_encode($oQueue);
    fwrite($oFile, $cQueue);
    fclose($oFile);
}

$cSQL = "SELECT * FROM ".TABLE_QUEUE." LIMIT ".$cSyncImportLimit;
$oItems_arr = $GLOBALS["DB"]->executeQuery($cSQL, 2);

$xData_arr = array();
foreach($oItems_arr as $oItem)
{
    $cData_arr = unserialize($oItem->cData);
    $xData_arr[$oItem->cListId][] = $cData_arr;
    $GLOBALS["DB"]->deleteRow(TABLE_QUEUE, "kQueue", $oItem->kQueue);
    $oQueue->nCurrent = $oQueue->nCurrent+1;
}

foreach($xData_arr as $cListId => $xItems_arr)
{
    try
    {
        $cResponse_arr = $oMCAPI->lists->batchSubscribe($cListId, $xItems_arr, false, true, false);
        Jtllog::writeLog("MailChimp: Massen Import durchgeführt! Neu=".$cResponse_arr["add_count"].", Bearbeitet=".$cResponse_arr["update_count"].", Fehler=".$cResponse_arr["error_count"], JTLLOG_LEVEL_NOTICE, false, "kPlugin", $oPlugin->kPlugin);

        if($cResponse_arr["add_count"] > 0)
        {
            foreach($cResponse_arr["adds"] as $cAdd_arr)
            {
                $oNewsletterEmpfaenger = $GLOBALS["DB"]->executeQuery("SELECT kNewsletterEmpfaenger, cEmail FROM tnewsletterempfaenger WHERE cEmail='".$cAdd_arr["email"]."'", 1);
                $oMailChimpSync = new stdClass();
                $oMailChimpSync->kNewsletterEmpfaenger = $oNewsletterEmpfaenger->kNewsletterEmpfaenger;
                $oMailChimpSync->cListId = $cVal;
                $oMailChimpSync->cEUID = $cAdd_arr["euid"];
                $oMailChimpSync->cLEID = $cAdd_arr["leid"];
                $oMailChimpSync->dSync = date("Y-m-d H:i:s");

                $GLOBALS["DB"]->insertRow(TABLE_SYNC,$oMailChimpSync);

                Jtllog::writeLog("MailChimp: Newsletter Empfänger '".$oNewsletterEmpfaenger->cEmail."' zu MailChimp übertragen.", JTLLOG_LEVEL_NOTICE, false, "kPlugin", $oPlugin->kPlugin);
            }
        }

        if($cResponse_arr["error_count"] > 0)
        {
            Jtllog::writeLog("MailChimp: Massen Import durchgeführt mit Fehler!".chr(10).chr(13).print_r($cResponse_arr["errors"], true), JTLLOG_LEVEL_ERROR, false, "kPlugin", $oPlugin->kPlugin);
        }                       
    } 
    catch (Exception $oException)
    {
        Jtllog::writeLog("MailChimp: {$oException->getMessage()}", JTLLOG_LEVEL_ERROR, false, "kPlugin", $oPlugin->kPlugin);
    }
}

$cQueue = json_encode($oQueue);
$oFile = fopen($cSyncFile, "w");
fwrite($oFile, $cQueue);
fclose($oFile);

if($oQueue->nMax == $oQueue->nCurrent)
{
    $bFinished = true;
    unlink($cSyncFile);
}
else
    $bFinished = false;

$oCallback = new stdClass();
$oCallback->nMax = $oQueue->nMax;
$oCallback->nCurrent = $oQueue->nCurrent;
$oCallback->bFinished = $bFinished;
$oCallback->bFirst = $bFirst;
$oCallback->cURL = "https://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];

echo json_encode($oCallback);
exit;
?>