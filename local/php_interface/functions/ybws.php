<?php

function YbwsWriteLog($data, $title=false)
{
	if (!file_exists($_SERVER["DOCUMENT_ROOT"].'/logs/'))
	{
		mkdir($_SERVER["DOCUMENT_ROOT"].'/logs/', 0777, true);
	}
	if($title) file_put_contents($_SERVER["DOCUMENT_ROOT"].'/logs/'.date("Y-m-d").'.log', '==='.$title.'==='.PHP_EOL, FILE_APPEND);
	file_put_contents($_SERVER["DOCUMENT_ROOT"].'/logs/'.date("Y-m-d").'.log', print_r($data,true).PHP_EOL, FILE_APPEND);
}

AddEventHandler('voximplant', 'onCallEnd', 'YbwsOnCallEndHandler');
AddEventHandler('im', 'OnAfterMessagesAdd', 'YbwsOnAfterMessagesAdd');

AddEventHandler('crm', 'OnBeforeCrmLeadUpdate', 'YbwsOnBeforeCrmLeadUpdateHandler');
AddEventHandler('crm', 'OnBeforeCrmContactUpdate', 'YbwsOnBeforeCrmContactUpdateHandler');
AddEventHandler('crm', 'OnBeforeCrmDealUpdate', 'YbwsOnBeforeCrmDealUpdateHandler');

AddEventHandler('crm', 'OnBeforeCrmLeadAdd', 'YbwsOnBeforeCrmLeadAddHandler');
AddEventHandler('crm', 'OnBeforeCrmContactAdd', 'YbwsOnBeforeCrmContactAddHandler');
AddEventHandler('crm', 'OnBeforeCrmDealAdd', 'YbwsOnBeforeCrmDealAddHandler');

function YbwsOnBeforeCrmLeadUpdateHandler(&$arEvent)
{
	CModule::IncludeModule("crm");
	//YbwsWriteLog($arEvent, "OnBeforeCrmLeadUpdate");
	if($arEvent['ASSIGNED_BY_ID']>0)
	{

		$leadData = CCrmLead::GetByID($arEvent['ID']);
		//YbwsWriteLog(array($leadData['ASSIGNED_BY_ID'], $arEvent['ASSIGNED_BY_ID']), "OnBeforeCrmLeadUpdate - Assigned changed");
		if($leadData['ASSIGNED_BY_ID']!=$arEvent['ASSIGNED_BY_ID'])
		{
			$datetime = new DateTime();
			$arEvent['UF_CRM_1522699163'] = array($datetime->format('d/m/Y H:i:s'));
		}
		//YbwsWriteLog($arEvent, "OnBeforeCrmLeadUpdate - after assign");
	}

	if($arEvent['MODIFY_BY_ID']!=1)
	{
		//if not system user change this entity...

		$is_need_to_check = false;
		$is_call_values_changed = false;
		$user_fields = YbwsGetCallUserFields()['LEAD'];
		
		foreach ($user_fields as $field_key=>$field_val)
		{
			if(array_key_exists($field_val, $arEvent))
			{		
				$is_need_to_check = true;
				break;
			}
		}

		if($is_need_to_check)
		{
			//YbwsWriteLog("OnBeforeCrmLeadUpdate-NotAdmin-FieldsInArray");
			$crm_entity = new CCrmLead(false);
			$arSelect  = array('ID',
				$user_fields['DIAL ATTEMTPS'], 
				$user_fields['LAST_CALL_DATETIME'], 
				$user_fields['LAST_CALL_DURATION']);
			
			$arFilter = array("ID"=>$arEvent['ID'], 'CHECK_PERMISSIONS'=>'N');

			$current_value = array();
			$current_value = $crm_entity->GetListEx(array(), $arFilter, false, false, $arSelect)->fetch();
			//YbwsWriteLog($current_value, "OnBeforeCrmLeadUpdate-CurrentValue");
			
			foreach ($user_fields as $field_key=>$field_val)
			{
				if(array_key_exists($field_val, $arEvent) && $arEvent[$field_val]!=$current_value[$field_val])
				{
					//need to restrict this lead change....
					//YbwsWriteLog("OnBeforeCrmLeadUpdate-Changing-Last-Call-Fields");
					$arEvent['RESULT_MESSAGE'] = "Can`t change Last Call fields manually. Lead not updated";
					return false;
				}
			}
			
		}
		
		
	}
	
}

function YbwsOnBeforeCrmContactUpdateHandler(&$arEvent)
{
	//YbwsWriteLog($arEvent, "OnBeforeCrmContactUpdate");
	
	if($arEvent['MODIFY_BY_ID']!=1)
	{
		//if not system user change this entity...
		CModule::IncludeModule("crm");
		$is_need_to_check = false;
		$is_call_values_changed = false;
		$user_fields = YbwsGetCallUserFields()['CONTACT'];
		
		foreach ($user_fields as $field_key=>$field_val)
		{
			if(array_key_exists($field_val, $arEvent))
			{		
				$is_need_to_check = true;
				break;
			}
		}
		
		if($is_need_to_check)
		{
			//YbwsWriteLog("OnBeforeCrmContactUpdate-NotAdmin-FieldsInArray");
			$crm_entity = new CCrmContact(false);
			$arSelect  = array('ID',
				$user_fields['DIAL ATTEMTPS'], 
				$user_fields['LAST_CALL_DATETIME'], 
				$user_fields['LAST_CALL_DURATION']);
			
			$arFilter = array("ID"=>$arEvent['ID'], 'CHECK_PERMISSIONS'=>'N');

			$current_value = array();
			$current_value = $crm_entity->GetListEx(array(), $arFilter, false, false, $arSelect)->fetch();
			//YbwsWriteLog($current_value, "OnBeforeCrmContactUpdate-CurrentValue");
			
			foreach ($user_fields as $field_key=>$field_val)
			{
				if(array_key_exists($field_val, $arEvent) && $arEvent[$field_val]!=$current_value[$field_val])
				{
					//need to restrict this contact change....
					//YbwsWriteLog("OnBeforeCrmContactUpdate-Changing-Last-Call-Fields");
					$arEvent['RESULT_MESSAGE'] = "Can`t change Last Call fields manually. Contact not updated";
					return false;
				}
			}
		}
		
		
	}
	
}

function YbwsOnBeforeCrmDealUpdateHandler(&$arEvent)
{
	//YbwsWriteLog($arEvent, "OnBeforeCrmDealUpdate");
	
	if($arEvent['MODIFY_BY_ID']!=1)
	{
		//if not system user change this entity...
		CModule::IncludeModule("crm");
		$is_need_to_check = false;
		$is_call_values_changed = false;
		$user_fields = YbwsGetCallUserFields()['DEAL'];
		
		foreach ($user_fields as $field_key=>$field_val)
		{
			if(array_key_exists($field_val, $arEvent))
			{		
				$is_need_to_check = true;
				break;
			}
		}
		
		if($is_need_to_check)
		{
			//YbwsWriteLog("OnBeforeCrmDealUpdate-NotAdmin-FieldsInArray");
			$crm_entity = new CCrmDeal(false);
			$arSelect  = array('ID',
				$user_fields['DIAL ATTEMTPS'], 
				$user_fields['LAST_CALL_DATETIME'], 
				$user_fields['LAST_CALL_DURATION']);
			
			$arFilter = array("ID"=>$arEvent['ID'], 'CHECK_PERMISSIONS'=>'N');

			$current_value = array();
			$current_value = $crm_entity->GetListEx(array(), $arFilter, false, false, $arSelect)->fetch();
			//YbwsWriteLog($current_value, "OnBeforeCrmDealUpdate-CurrentValue");
			
			foreach ($user_fields as $field_key=>$field_val)
			{
				if(array_key_exists($field_val, $arEvent) && $arEvent[$field_val]!=$current_value[$field_val])
				{
					//need to restrict this deal change....
					//YbwsWriteLog("OnBeforeCrmDealUpdate-Changing-Last-Call-Fields");
					$arEvent['RESULT_MESSAGE'] = "Can`t change Last Call fields manually. Deal not updated";
					return false;
				}
			}
		}
	}	
}

function YbwsOnBeforeCrmLeadAddHandler(&$arEvent)
{
	//YbwsWriteLog($arEvent, "OnBeforeCrmLeadAdd");
	$user_fields = YbwsGetCallUserFields()['LEAD'];
	foreach ($user_fields as $field_key=>$field_val)
	{
		if(array_key_exists($field_val, $arEvent))
		{		
			$arEvent[$field_val]="";
		}
	}
	$datetime = new DateTime();
	$arEvent['UF_CRM_1522699163'] = array($datetime->format('d/m/Y H:i:s'));
}

function YbwsOnBeforeCrmContactAddHandler(&$arEvent)
{
	//YbwsWriteLog($arEvent, "OnBeforeCrmContactAdd");
	$user_fields = YbwsGetCallUserFields()['CONTACT'];
	foreach ($user_fields as $field_key=>$field_val)
	{
		if(array_key_exists($field_val, $arEvent))
		{		
			$arEvent[$field_val]="";
		}
	}
}

function YbwsOnBeforeCrmDealAddHandler(&$arEvent)
{
	//YbwsWriteLog($arEvent, "OnBeforeCrmDealAdd");
	$user_fields = YbwsGetCallUserFields()['DEAL'];
	foreach ($user_fields as $field_key=>$field_val)
	{
		if(array_key_exists($field_val, $arEvent))
		{		
			$arEvent[$field_val]="";
		}
	}
}

function YbwsGetCallUserFields()
{
	return array
	(
		"LEAD"=>array(
			"DIAL ATTEMTPS"=>"UF_CRM_1540229184",
			"LAST_CALL_DATETIME"=>"UF_CRM_1540229240",
			"LAST_CALL_DURATION"=>"UF_CRM_1540229285"
		),
		"CONTACT"=>array(
			"DIAL ATTEMTPS"=>"UF_CRM_1540229472",
			"LAST_CALL_DATETIME"=>"UF_CRM_1540229514",
			"LAST_CALL_DURATION"=>"UF_CRM_1540229556"
		),
		"DEAL"=>array(
			"DIAL ATTEMTPS"=>"UF_CRM_1540229620",
			"LAST_CALL_DATETIME"=>"UF_CRM_1540229656",
			"LAST_CALL_DURATION"=>"UF_CRM_1540229699"
		),
	);
}

function YbwsOnAfterMessagesAdd($messageID, $arFields)
{
	if($arFields['SYSTEM']!="Y")
	{
		if($arFields['CHAT_ID']>0)
		{
			CModule::IncludeModule('crm');
			global $DB;
			$res = $DB->Query("SELECT SUM(MESSAGE_COUNT) as CNT FROM b_imopenlines_session WHERE CHAT_ID=".$arFields['CHAT_ID']);
			if($data = $res->fetch())
			{
				$cnt = $data['CNT'];
				//YbwsWriteLog($cnt, "YBWSLastCallProcessing-CNT");
				$activities_res = $DB->Query("SELECT * FROM `b_crm_act` WHERE `PROVIDER_PARAMS` LIKE '%".$arFields['CHAT_ENTITY_ID']."%'  AND `COMPLETED`='N'");
				while($activity = $activities_res->fetch())
				{
					//YbwsWriteLog($activity, "YBWSLastCallProcessing-activity");
					$bindings = CCrmActivity::GetBindings($activity['ID']);
					//YbwsWriteLog($bindings, "YBWSLastCallProcessing-bindings");
					foreach($bindings as $bind)
					{
						/*if($bind['OWNER_TYPE_ID']==1 && $bind['OWNER_ID']>0)
						{
							$crm_entity = new CCrmLead(false);
							$fields = array('MODIFY_BY_ID'=>'1', 'UF_CRM_1588597207194' => $cnt);
							$crm_entity->update($bind['OWNER_ID'], $fields);
						}*/
					}
				}
			}
		}
	}
}

function YBWSFindCRMByPhone($phone)
{
	//country code by default...
	if(strlen($phone)==9) $phone="27".$phone;
	
	$result=false;
		
	if (CModule::IncludeModule('crm'))
	{
			$criterion = new Bitrix\Crm\Integrity\DuplicateCommunicationCriterion("PHONE", $phone);
			$duplicate = $criterion->find(\CCrmOwnerType::Lead, 5);
			if($duplicate)
			{
				$entities = $duplicate->getEntities();
				if ( !empty($entities) )
				{
					foreach($entities as $entity)
					{
						$result['LEAD'.$entity->getEntityID()] = array("ID"=>$entity->getEntityID(), "TYPE_ID"=>"LEAD");
					}
				}
			}

			$duplicate = $criterion->find(\CCrmOwnerType::Contact, 5);
			if($duplicate)
			{
				$entities = $duplicate->getEntities();
				if (!empty($entities))
				{
					foreach($entities as $entity)
					{
						$result['CONTACT'.$entity->getEntityID()] = array("ID"=>$entity->getEntityID(), "TYPE_ID"=>"CONTACT");

						$deal_res = CCrmDeal::GetList(array(), array("CONTACT_ID"=>$entity->getEntityID(), "CHECK_PERMISSIONS"=>"N"));
						if($deal=$deal_res->fetch())
						{
							$result['DEAL'.$deal['ID']] = array("ID"=>$deal['ID'], "TYPE_ID"=>"DEAL");
						}
					}
				}
			}
			
			$duplicate = $criterion->find(\CCrmOwnerType::Deal, 5);
			if($duplicate)
			{
				$entities = $duplicate->getEntities();
				if (!empty($entities))
				{
					foreach($entities as $entity)
					{
						$result['DEAL'.$entity->getEntityID()] = array("ID"=>$entity->getEntityID(), "TYPE_ID"=>"DEAL");
					}
				}
			}
	}
	
	return $result;
}

function YbwsOnCallEndHandler($arEvent)
{
	CModule::IncludeModule("crm");
	
	if($arEvent['CALL_ID']!="")
	{
		//step1. find this call in voximplant statistics:
		global $DB;
		$res = $DB->Query("SELECT * FROM b_voximplant_statistic WHERE  CALL_ID = '".$arEvent['CALL_ID']."'");
		if ($call_info = $res->Fetch())
		{
			YBWSLastCallProcessing($call_info);
		}
	}
}

function YBWSLastCallProcessing($call_info)
{
	//YbwsWriteLog($call_info, "YBWSLastCallProcessing-call_info");
			
	if($call_info['INCOMING']==1)
	{
		$phone= $call_info['PHONE_NUMBER'];
	
		$CRM_entities = YBWSFindCRMByPhone($phone);
		
		if(
			(
				$call_info["CRM_ENTITY_TYPE"]=="LEAD" || 
				$call_info["CRM_ENTITY_TYPE"]=="CONTACT" || 
				$call_info["CRM_ENTITY_TYPE"]=="DEAL"
			) 
			&& $call_info['CRM_ENTITY_ID']>0
		)
		{
			$CRM_entities[$call_info["CRM_ENTITY_TYPE"].$call_info['CRM_ENTITY_ID']] = array("ID"=>$call_info['CRM_ENTITY_ID'], "TYPE_ID"=>$call_info["CRM_ENTITY_TYPE"]);
		}
		
		//YbwsWriteLog($CRM_entities, "YBWSLastCallProcessing-CRM_entities");
		
		$datetime = DateTime::createFromFormat('Y-m-d H:i:s', $call_info['CALL_START_DATE']);
		
		foreach($CRM_entities as $entity)
		{
			$fields = array('MODIFY_BY_ID'=>'1');
			$crm_entity = false;
			$user_fields = YbwsGetCallUserFields()[$entity['TYPE_ID']];
			
			switch($entity['TYPE_ID'])
			{
				case  "LEAD": $crm_entity = new CCrmLead(false); break;
				case "CONTACT": $crm_entity = new CCrmContact(false); break;
				case "DEAL": $crm_entity=new CCrmDeal(false); break;
			}
			
			//call sucessfull and duration>20 sec...
			if($call_info['CALL_FAILED_CODE']==200 && $call_info['CALL_DURATION']>20)
			{
				$fields[$user_fields['DIAL ATTEMTPS']] = 0;
				$fields[$user_fields['LAST_CALL_DATETIME']] = $datetime->format("d/m/Y H:i:s"); //11/09/2018 00:00:00
				$fields[$user_fields['LAST_CALL_DURATION']] = $call_info['CALL_DURATION'];
			}
			//dialing attempt
			else
			{
				$arSelect  = array('ID',
					$user_fields['DIAL ATTEMTPS'], 
					$user_fields['LAST_CALL_DATETIME'], 
					$user_fields['LAST_CALL_DURATION']);
				
				$arFilter = array("ID"=>$entity['ID'], 'CHECK_PERMISSIONS'=>'N');

				$current_value=array();
				$current_value = $crm_entity->GetListEx(array(), $arFilter, false, false, $arSelect)->fetch();
				
				$fields[$user_fields['DIAL ATTEMTPS']] = $current_value[$user_fields['DIAL ATTEMTPS']]+1;
				$fields[$user_fields['LAST_CALL_DATETIME']] = '';
				$fields[$user_fields['LAST_CALL_DURATION']] = 0;
			}
			//YbwsWriteLog($fields, "YBWSLastCallProcessing-update fields ".$entity['TYPE_ID'].$entity['ID']);
			$crm_entity->update($entity['ID'], $fields); 
		}
	}
}



//****NEW HANDLERS FOR PHONE NUMBER FORMAT CHANGES AND NAMES CHECK....
/*
AddEventHandler('crm', 'OnBeforeCrmLeadAdd', 'YbwsFormatCheckOnAdd');
AddEventHandler('crm', 'OnBeforeCrmLeadUpdate', 'YbwsFormatCheckOnUpdate');

function YbwsFormatCheckOnAdd(&$arEvent)
{
	//YbwsWriteLog($arEvent, "YbwsFormatCheckOnAdd_OLD");

	if($arEvent['NAME'])$arEvent['NAME'] = ucfirst(strtolower($arEvent['NAME']));
	if($arEvent['SECOND_NAME'])$arEvent['SECOND_NAME'] = ucfirst(strtolower($arEvent['SECOND_NAME']));
	if($arEvent['LAST_NAME'])$arEvent['LAST_NAME'] = ucfirst(strtolower($arEvent['LAST_NAME']));
	if($arEvent['FULL_NAME'])$arEvent['FULL_NAME']= implode(" ", array($arEvent['NAME'], $arEvent['SECOND_NAME'],$arEvent['LAST_NAME']));

	foreach($arEvent['FM']['PHONE'] as $key=>$val)
	{
		if(strlen($val['VALUE'])>10) $val['VALUE'] = substr($val['VALUE'], -10);

		if(substr($val['VALUE'],0,2)=="00") {$arEvent['FM']['PHONE'][$key]['VALUE']= "270".substr($val['VALUE'],2); $arEvent['FM']['PHONE'][$key]['VALUE_TYPE']="WORK"; }
		if(substr($val['VALUE'],0,2)=="01") {$arEvent['FM']['PHONE'][$key]['VALUE']= "271".substr($val['VALUE'],2); $arEvent['FM']['PHONE'][$key]['VALUE_TYPE']="WORK"; }
		if(substr($val['VALUE'],0,2)=="02") {$arEvent['FM']['PHONE'][$key]['VALUE']= "272".substr($val['VALUE'],2); $arEvent['FM']['PHONE'][$key]['VALUE_TYPE']="WORK"; }
		if(substr($val['VALUE'],0,2)=="03") {$arEvent['FM']['PHONE'][$key]['VALUE']= "273".substr($val['VALUE'],2); $arEvent['FM']['PHONE'][$key]['VALUE_TYPE']="WORK"; }
		if(substr($val['VALUE'],0,2)=="04") {$arEvent['FM']['PHONE'][$key]['VALUE']= "274".substr($val['VALUE'],2); $arEvent['FM']['PHONE'][$key]['VALUE_TYPE']="WORK"; }
		if(substr($val['VALUE'],0,2)=="05") {$arEvent['FM']['PHONE'][$key]['VALUE']= "275".substr($val['VALUE'],2); $arEvent['FM']['PHONE'][$key]['VALUE_TYPE']="WORK"; }
		if(substr($val['VALUE'],0,2)=="06") {$arEvent['FM']['PHONE'][$key]['VALUE']= "276".substr($val['VALUE'],2); $arEvent['FM']['PHONE'][$key]['VALUE_TYPE']="MOBILE"; }
		if(substr($val['VALUE'],0,2)=="07") {$arEvent['FM']['PHONE'][$key]['VALUE']= "277".substr($val['VALUE'],2); $arEvent['FM']['PHONE'][$key]['VALUE_TYPE']="MOBILE"; }
		if(substr($val['VALUE'],0,2)=="08") {$arEvent['FM']['PHONE'][$key]['VALUE']= "278".substr($val['VALUE'],2); $arEvent['FM']['PHONE'][$key]['VALUE_TYPE']="MOBILE"; }
		if(substr($val['VALUE'],0,2)=="09") {$arEvent['FM']['PHONE'][$key]['VALUE']= "279".substr($val['VALUE'],2); $arEvent['FM']['PHONE'][$key]['VALUE_TYPE']="MOBILE"; }
	}


	//YbwsWriteLog($arEvent, "YbwsFormatCheckOnAdd_NEW");
}

function YbwsFormatCheckOnUpdate(&$arEvent)
{
	//YbwsWriteLog($arEvent, "YbwsFormatCheckOnUpdate_OLD");

	if($arEvent['NAME'])$arEvent['NAME'] = ucfirst(strtolower($arEvent['NAME']));
	if($arEvent['SECOND_NAME'])$arEvent['SECOND_NAME'] = ucfirst(strtolower($arEvent['SECOND_NAME']));
	if($arEvent['LAST_NAME'])$arEvent['LAST_NAME'] = ucfirst(strtolower($arEvent['LAST_NAME']));
	if($arEvent['FULL_NAME'])$arEvent['FULL_NAME']= implode(" ", array($arEvent['NAME'], $arEvent['SECOND_NAME'],$arEvent['LAST_NAME']));


	foreach($arEvent['FM']['PHONE'] as $key=>$val)
	{
		if(strlen($val['VALUE'])>10) $val['VALUE'] = substr($val['VALUE'], -10); 

		if(substr($val['VALUE'],0,2)=="00") {$arEvent['FM']['PHONE'][$key]['VALUE']= "270".substr($val['VALUE'],2); $arEvent['FM']['PHONE'][$key]['VALUE_TYPE']="WORK"; }
		if(substr($val['VALUE'],0,2)=="01") {$arEvent['FM']['PHONE'][$key]['VALUE']= "271".substr($val['VALUE'],2); $arEvent['FM']['PHONE'][$key]['VALUE_TYPE']="WORK"; }
		if(substr($val['VALUE'],0,2)=="02") {$arEvent['FM']['PHONE'][$key]['VALUE']= "272".substr($val['VALUE'],2); $arEvent['FM']['PHONE'][$key]['VALUE_TYPE']="WORK"; }
		if(substr($val['VALUE'],0,2)=="03") {$arEvent['FM']['PHONE'][$key]['VALUE']= "273".substr($val['VALUE'],2); $arEvent['FM']['PHONE'][$key]['VALUE_TYPE']="WORK"; }
		if(substr($val['VALUE'],0,2)=="04") {$arEvent['FM']['PHONE'][$key]['VALUE']= "274".substr($val['VALUE'],2); $arEvent['FM']['PHONE'][$key]['VALUE_TYPE']="WORK"; }
		if(substr($val['VALUE'],0,2)=="05") {$arEvent['FM']['PHONE'][$key]['VALUE']= "275".substr($val['VALUE'],2); $arEvent['FM']['PHONE'][$key]['VALUE_TYPE']="WORK"; }
		if(substr($val['VALUE'],0,2)=="06") {$arEvent['FM']['PHONE'][$key]['VALUE']= "276".substr($val['VALUE'],2); $arEvent['FM']['PHONE'][$key]['VALUE_TYPE']="MOBILE"; }
		if(substr($val['VALUE'],0,2)=="07") {$arEvent['FM']['PHONE'][$key]['VALUE']= "277".substr($val['VALUE'],2); $arEvent['FM']['PHONE'][$key]['VALUE_TYPE']="MOBILE"; }
		if(substr($val['VALUE'],0,2)=="08") {$arEvent['FM']['PHONE'][$key]['VALUE']= "278".substr($val['VALUE'],2); $arEvent['FM']['PHONE'][$key]['VALUE_TYPE']="MOBILE"; }
		if(substr($val['VALUE'],0,2)=="09") {$arEvent['FM']['PHONE'][$key]['VALUE']= "279".substr($val['VALUE'],2); $arEvent['FM']['PHONE'][$key]['VALUE_TYPE']="MOBILE"; }
	}

	//YbwsWriteLog($arEvent, "YbwsFormatCheckOnUpdate_NEW");
}
*/

//****HANDLERS FOR CHAT2DESK REBIND....
AddEventHandler('crm', 'OnAfterCrmLeadAdd', 'YbwsRebindChat2Desk');
AddEventHandler('crm', 'OnAfterCrmLeadUpdate', 'YbwsRebindChat2Desk');

function YbwsRebindChat2Desk(&$arEvent)
{
	global $DB;
	CModule::IncludeModule("crm");
	CModule::IncludeModule('imopenlines');

	//YbwsWriteLog($arEvent, "Rebind_CHAT2DESK_arEvent");
	if(count($arEvent["FM"]["PHONE"]))
	{
		foreach($arEvent["FM"]["PHONE"] as $phone)
		{
			$phone = $phone['VALUE'];
			$phone = str_replace("+", "", $phone);

			YbwsWriteLog($phone, "Rebind_CHAT2DESK_phone");
			if($phone!="")
			{
				$dialogs_res = $DB->Query("SELECT * FROM `chat2desk_imconnector` WHERE `PHONE2`=".$phone."");
				while($dialog = $dialogs_res->Fetch())
				{
					$imol = str_replace("imol|", "", $dialog['IMOL']);
					YbwsWriteLog($imol, "imol");
	
					$activities_res = $DB->Query("SELECT * FROM `b_crm_act` WHERE `PROVIDER_PARAMS` LIKE '%".$imol."%'");
					while($activity = $activities_res->fetch())
					{
						//YbwsWriteLog($activity['ID'], "activity");
						$ACTIVITY_BINDED = false;
						$bindings = CCrmActivity::GetBindings($activity['ID']);
						YbwsWriteLog($bindings, "bindings");
						foreach($bindings as $bind)
						{
							if($bind['OWNER_TYPE_ID']==1 && $bind['OWNER_ID']==$arEvent['ID'])
							{
								$ACTIVITY_BINDED = true;
							}
						}
						if(!$ACTIVITY_BINDED)
						{
							//необходимо привязать это активити к карточке...
							YbwsWriteLog("bind activity");
							$bindings[] = array('OWNER_TYPE_ID' => "1", 'OWNER_ID' => $arEvent['ID']);
							CCrmActivity::SaveBindings($activity['ID'], $bindings, true, false);

							\Bitrix\ImOpenLines\Im::addMessage(array(
								"FROM_USER_ID" => 0,
								"TO_CHAT_ID" => $dialog['DIALOG_ID'],
								"MESSAGE" => 'Dialog was binded to lead [B][URL=/crm/lead/details/'.$arEvent['ID'].'/]#'.$arEvent['ID'].'[/URL][/B]',
								'SYSTEM' => 'Y'
							));
							//перепривяжем основную табличку...
						}
					}
				}
			}
		}
	}
}

//Statistics
AddEventHandler('main', 'OnBeforeProlog', 'YbwsOnBeforeProlog');
function YbwsOnBeforeProlog()
{
	global $USER;
	global $DB;

	if($USER->GetID()>0)
	{
		//YbwsWriteLog("on user hit");
		$DB->Query("INSERT INTO ybws_hits (USER_ID,HIT_COUNT,LAST_HIT) VALUES (".$USER->GetID().",1,NOW())
					ON DUPLICATE KEY UPDATE HIT_COUNT=HIT_COUNT+1, LAST_HIT=NOW();");
	}
}

function YbwsCollectUserStatistics()
{
	global $DB;
	$res = $DB->Query("SELECT * FROM ybws_hits");
	while ($data = $res->fetch())
	{
		if($data['HIT_COUNT']>20)
		{
			$DB->Query("INSERT INTO ybws_user_stat (USER_ID,STAT_TYPE,STAT_SCORE,CREATE_DATE) VALUES (".$data['USER_ID'].",'hit', 5, NOW())");
		}
	}
	$DB->Query("UPDATE ybws_hits SET HIT_COUNT=0");
	return "YbwsCollectUserStatistics();";
}


?>