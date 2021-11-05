<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
$DIALOG_ID = $_REQUEST['DIALOG_ID'];
$DIALOG_ID = str_replace("chat", "", $DIALOG_ID);

CModule::IncludeModule("chat2desk.imconnector");
CModule::IncludeModule("crm");
$bindings = Chat2DeskIMConnector::GetBindingsByChatID($DIALOG_ID);
?>
<html>
<head></head>
	<body style="font-family:Arial;">
	<div style="color: #8f9192; font-size: 10px; line-height: 10px;">dialog id <?=$DIALOG_ID?></div>
	<h4 style="color: #323740; margin-top: 5px; margin-bottom: 5px; padding-bottom: 5px; border-bottom: 1px solid #f3f5f7;">CRM bindings:</h4>
	<?
		foreach($bindings[1] as $lead)
		{
			$lead = CCrmLead::GetByID($lead);
			if($lead['ID']>0)
			{
		?><div style="font-size:0.8rem; color: #323740; margin: 1rem 0;">Lead : <a style="color: #3F51B5; font-weight: bold;" target="_blank" href="https://cytron-cyberfinance.com/crm/lead/details/<?=$lead['ID']?>/"><?=$lead['TITLE']?></a> by <span style="color: #E91E63;"><?=$lead['ASSIGNED_BY_NAME']." ".$lead['ASSIGNED_BY_LAST_NAME']?></span></div><?
			}
			//var_dump($lead);
		}
		foreach($bindings[3] as $contact)
		{
			$contact = CCrmContact::GetByID($contact);
			if($contact['ID']>0)
			{
				?><div style="font-size:0.8rem; color: #323740; margin: 1rem 0;">Contact : <a style="color: #3F51B5; font-weight: bold;" target="_blank" href="https://cytron-cyberfinance.com/crm/contact/details/<?=$contact['ID']?>/"><?=implode(" ", array($contact['NAME'], $contact['SECOND_NAME'], $contact['LAST_NAME']))?></a>  by <span style="color: #E91E63;"><?=$contact['ASSIGNED_BY_NAME']." ".$contact['ASSIGNED_BY_LAST_NAME']?></span></div><?
			}
		}
	?>
</body>
</html>