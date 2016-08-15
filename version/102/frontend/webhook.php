<?php
require_once(PFAD_ROOT.PFAD_PLUGIN.$oPlugin->cVerzeichnis."/".PFAD_PLUGIN_VERSION.$oPlugin->nVersion . '/includes/mailchimp/Mailchimp.php');
require_once(PFAD_ROOT.PFAD_PLUGIN.$oPlugin->cVerzeichnis."/".PFAD_PLUGIN_VERSION.$oPlugin->nVersion . '/includes/defines.php');
require_once(PFAD_ROOT.PFAD_PLUGIN.$oPlugin->cVerzeichnis."/".PFAD_PLUGIN_VERSION.$oPlugin->nVersion . '/includes/functions.php');
$cEinstellungen_arr = ladeEinstellungen();

if(isset($_REQUEST["mc_webhook"]) && !empty($_REQUEST["mc_webhook"]))
{
	Jtllog::writeLog("MailChimp: Webhook aufgerufen".chr(10).chr(13).print_r($_REQUEST, true), JTLLOG_LEVEL_DEBUG, false, "kPlugin", $oPlugin->kPlugin);

	switch($_REQUEST["type"]) {
		case "profile":
			$xData_arr = $_REQUEST["data"];

			$cSQL = "SELECT * FROM ".TABLE_SYNC." WHERE cEUID='".$xData_arr["id"]."'";
			$oSync = $GLOBALS["DB"]->executeQuery($cSQL, 1);

			if(is_object($oSync) && !empty($oSync))
			{
				$cSQL = "SELECT * FROM tnewsletterempfaenger WHERE kNewsletterEmpfaenger=".$oSync->kNewsletterEmpfaenger."";
				$oNewsletterEmpfaenger = $GLOBALS["DB"]->executeQuery($cSQL, 1);

				if(is_object($oNewsletterEmpfaenger) && !empty($oNewsletterEmpfaenger))
				{
					$oNewsletterEmpfaenger->cAnrede = $xData_arr["merges"]["CANREDE"];
					$oNewsletterEmpfaenger->cVorname = $xData_arr["merges"]["FNAME"];
					$oNewsletterEmpfaenger->cNachname = $xData_arr["merges"]["LNAME"];
					$oNewsletterEmpfaenger->cEmail = $xData_arr["merges"]["EMAIL"];

					$nErfolg = $GLOBALS["DB"]->updateRow("tnewsletterempfaenger", "kNewsletterEmpfaenger", $oNewsletterEmpfaenger->kNewsletterEmpfaenger, $oNewsletterEmpfaenger);

					if($nErfolg > 0)
					{
						$oSync->dLastSync = date("Y-m-d H:i:s");
						$GLOBALS["DB"]->updateRow(TABLE_SYNC, "kSync", $oSync->kSync, $oSync);
					}
				}
				else
				{
					Jtllog::writeLog("MailChimp: Sync Eintrag vorhanden, Newsletter Empfänger aber nicht gefunden".chr(10).chr(13).print_r($xData_arr,true).print_r($oSync,true), JTLLOG_LEVEL_ERROR, false, "kPlugin", $oPlugin->kPlugin);
				}
			}
			else
			{
				Jtllog::writeLog("MailChimp: Newsletter Empfänger nicht gefunden".chr(10).chr(13).print_r($xData_arr,true), JTLLOG_LEVEL_ERROR, false, "kPlugin", $oPlugin->kPlugin);
			}
			
			break;
		case "upemail":
			$xData_arr = $_REQUEST["data"];

			$cSQL = "SELECT * FROM tnewsletterempfaenger WHERE cEmail='".$xData_arr["old_email"]."'";
			$oNewsletterEmpfaenger = $GLOBALS["DB"]->executeQuery($cSQL, 1);

			if(is_object($oNewsletterEmpfaenger) && !empty($oNewsletterEmpfaenger))
			{
				$cSQL = "SELECT * FROM ".TABLE_SYNC." WHERE kNewsletterEmpfaenger=".$oNewsletterEmpfaenger->kNewsletterEmpfaenger." AND cListId='".$xData_arr["list_id"]."'";
				$oSync = $GLOBALS["DB"]->executeQuery($cSQL, 1);

				if(is_object($oSync) && !empty($oSync))
				{
					$oNewsletterEmpfaenger->cEmail = $xData_arr["new_email"];

					$nErfolg = $GLOBALS["DB"]->updateRow("tnewsletterempfaenger", "kNewsletterEmpfaenger", $oNewsletterEmpfaenger->kNewsletterEmpfaenger, $oNewsletterEmpfaenger);

					if($nErfolg > 0)
					{
						$oSync->cEUID = $xData_arr["new_id"];
						$oSync->dLastSync = date("Y-m-d H:i:s");
						$GLOBALS["DB"]->updateRow(TABLE_SYNC, "kSync", $oSync->kSync, $oSync);
					}
				}
				else
				{
					Jtllog::writeLog("MailChimp: Newsletter Empfänger vorhanden, Sync Eintrag aber nicht gefunden".chr(10).chr(13).print_r($xData_arr,true).print_r($oNewsletterEmpfaenger,true), JTLLOG_LEVEL_ERROR, false, "kPlugin", $oPlugin->kPlugin);
				}
			}
			else
			{
				Jtllog::writeLog("MailChimp: Newsletter Empfänger nicht gefunden".chr(10).chr(13).print_r($xData_arr,true), JTLLOG_LEVEL_ERROR, false, "kPlugin", $oPlugin->kPlugin);
			}
			break;
		case "unsubscribe":
			$xData_arr = $_REQUEST["data"];

			$cSQL = "SELECT * FROM ".TABLE_SYNC." WHERE cEUID='".$xData_arr["id"]."'";
			$oSync = $GLOBALS["DB"]->executeQuery($cSQL, 1);

			if(is_object($oSync) && !empty($oSync))
			{
				$cSQL = "SELECT * FROM tnewsletterempfaenger WHERE kNewsletterEmpfaenger=".$oSync->kNewsletterEmpfaenger."";
				$oNewsletterEmpfaenger = $GLOBALS["DB"]->executeQuery($cSQL, 1);

				if(is_object($oNewsletterEmpfaenger) && !empty($oNewsletterEmpfaenger))
				{
					$nErfolg = $GLOBALS["DB"]->deleteRow("tnewsletterempfaenger", "kNewsletterEmpfaenger", $oNewsletterEmpfaenger->kNewsletterEmpfaenger);

					if($nErfolg > 0)
					{
						$GLOBALS["DB"]->deleteRow(TABLE_SYNC, "kSync", $oSync->kSync, $oSync);
						Jtllog::writeLog("MailChimp: Newsletter Empfänger gelöscht".chr(10).chr(13).print_r($oNewsletterEmpfaenger, true), JTLLOG_LEVEL_NOTICE, false, "kPlugin", $oPlugin->kPlugin);
					}
				}
				else
				{
					Jtllog::writeLog("MailChimp: Sync Eintrag vorhanden, Newsletter Empfänger aber nicht gefunden".chr(10).chr(13).print_r($xData_arr,true).print_r($oSync,true), JTLLOG_LEVEL_ERROR, false, "kPlugin", $oPlugin->kPlugin);
				}
			}
			else
			{
				Jtllog::writeLog("MailChimp: Newsletter Empfänger nicht gefunden".chr(10).chr(13).print_r($xData_arr,true), JTLLOG_LEVEL_ERROR, false, "kPlugin", $oPlugin->kPlugin);
			}
			
			break;
	}	
}
?>