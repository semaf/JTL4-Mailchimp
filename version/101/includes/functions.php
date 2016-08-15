<?php

function ladeEinstellungen()
{
	$cErgebnisse_arr = $GLOBALS["DB"]->executeQuery("SELECT cName,cWert FROM ".TABLE_EINSTELLUNGEN, 9);
	$cEinstellungen_arr = array();
	foreach ($cErgebnisse_arr as $cErgebniss)
	{
	    $cEinstellungen_arr[$cErgebniss["cName"]] = $cErgebniss["cWert"];
	}

	return $cEinstellungen_arr;
}

function ladeSyncEintrag($kNewsletterEmpfaenger)
{
	$oSync = $GLOBALS["DB"]->executeQuery("SELECT * FROM ".TABLE_SYNC, 1);

	if(is_object($oSync))
	{
		return $oSync;
	}
	else
	{
		return new stdClass();
	}
}


?>