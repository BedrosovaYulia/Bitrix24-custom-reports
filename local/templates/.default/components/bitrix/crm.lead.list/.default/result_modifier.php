<?
global $DB;
//YBWS
$leads = array();
foreach($arResult['LEAD'] as $lead_key=>$lead_val)
{
	if($lead_val['~WAITING_TITLE']!="")
	{
		continue;
	}

	$activities = $DB->Query("SELECT * FROM `b_crm_act` WHERE `OWNER_ID`='".$lead_val['ID']."' AND  `OWNER_TYPE_ID`='1' 
		AND `PROVIDER_ID` != 'IMOPENLINES_SESSION' 
		AND `PROVIDER_ID` != 'CRM_WEBFORM' 
		LIMIT 1");

	if($activity=$activities->fetch())
	{
		$arResult['LEAD'][$lead_key]['~C_ACTIVITY_ID'] = $activity['ID'];
		$arResult['LEAD'][$lead_key]['C_ACTIVITY_ID'] = $activity['ID'];

		$arResult['LEAD'][$lead_key]['~ACTIVITY_ID'] = '';
		$arResult['LEAD'][$lead_key]['ACTIVITY_ID'] = '';

		$arResult['LEAD'][$lead_key]['~C_ACTIVITY_SUBJECT'] = $activity['SUBJECT'];
		$arResult['LEAD'][$lead_key]['C_ACTIVITY_SUBJECT'] = $activity['SUBJECT'];
	}
	else
	{
		$arResult['LEAD'][$lead_key]['~C_ACTIVITY_ID'] = '';
		$arResult['LEAD'][$lead_key]['C_ACTIVITY_ID'] = '';

		$arResult['LEAD'][$lead_key]['~ACTIVITY_ID'] = '';
		$arResult['LEAD'][$lead_key]['ACTIVITY_ID'] = '';

		$arResult['LEAD'][$lead_key]['~C_ACTIVITY_SUBJECT'] = '';
		$arResult['LEAD'][$lead_key]['C_ACTIVITY_SUBJECT'] = '';
	}
}

?>