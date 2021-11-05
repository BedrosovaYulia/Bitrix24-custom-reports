<?php

declare(strict_types=1);

use \Bitrix\Crm\{
    LeadTable
};
use \Bitrix\Main\{
    EventManager
};

$handlerPhone = function (&$fields){
    $phoneNumberList = [];

    // Pre-load comment field
    if(!\array_key_exists('COMMENTS', $fields) && $fields['ID'] > 0){
        $fields['COMMENTS'] = LeadTable::getByPrimary((int)$fields['ID'], [
            'select' => ['COMMENTS']
        ])->fetch()['COMMENTS'];
    }
    $fields['COMMENTS'] = (string)$fields['COMMENTS'];

    // Check if phones defined
    if(\array_key_exists('FM', $fields) && \array_key_exists('PHONE', $fields['FM'])){
        foreach($fields['FM']['PHONE'] as $key => $value) {
            // Remove right and left spaces. Might be useful if value provided via API REST API
            // Remove none numeric symbols
            $number = \trim(\preg_replace('~[^0-9#]~i', '', (string)$value['VALUE']));

            // Skip empty
            if(empty($number)){
                continue;
            }

            [$number, $prefix] = \explode('#', $number);

            // Append 0 for short numbers. Probably user's typo
            $number = \str_pad($number, 10, "0", STR_PAD_LEFT);

            // Append international code for local numbers
            // Fix leading multiple 0 using ltrim
            if($number[0] === "0"){
                $number = 27 . \ltrim($number, '0');
            }

            // Fix potential issue with 0 after country code, ex: +27 0
            $number = \preg_replace('~^(\+)?(27)0+~i', '$1$2', $number);

            // Validate only South Africa numbers
            if(\preg_match('~^(\+)?27((\d)(\d))([\d|#]+)$~i', $number, $matches)){
                // Unset toll fee numbers
                if($matches[2] == 80){
                    unset($fields['FM']['PHONE'][$key]);
                    continue;
                }

                $isMobile = \in_array($matches[3], [6, 7, 8, 9]) && $matches[2] != 87;

                $fields['FM']['PHONE'][$key] = [
                    'VALUE' => \sprintf('+27%s%s%s', $matches[2], $matches[5], empty($prefix) ?'' :'#' . $prefix),
					'VALUE_TYPE' => $isMobile ?'MOBILE' :'WORK',
					//'VALUE_TYPE'=>$value['VALUE_TYPE'],
                ];
            }

            // Remove duplicates
            if(\in_array($fields['FM']['PHONE'][$key]['VALUE'], $phoneNumberList)){
                $fields['FM']['PHONE'][$key]['VALUE'] = '';
                continue;
            }

            // Remove incorrect numbers: too long or short, not +27
            if(\mb_strlen($fields['FM']['PHONE'][$key]['VALUE']) != 12 || !\preg_match('~^\+27~i', $fields['FM']['PHONE'][$key]['VALUE'])){
                // Log removed number
                $fields['COMMENTS'] .= \sprintf("%sIncorrect number '%s' has been removed.", empty($fields['COMMENTS'])?'':'<br/>', $fields['FM']['PHONE'][$key]['VALUE']);

                $fields['FM']['PHONE'][$key]['VALUE'] = '';
                continue;
            }

            $phoneNumberList[] = $fields['FM']['PHONE'][$key]['VALUE'];
        }
    }
};

EventManager::getInstance()->addEventHandler('crm', 'OnBeforeCrmLeadAdd', $handlerPhone, false,50);
EventManager::getInstance()->addEventHandler('crm', 'OnBeforeCrmLeadUpdate', $handlerPhone, false,50);

$handlerName = function(&$fields){
    if($fields['NAME']){
        $fields['NAME'] = \ucfirst(\mb_strtolower($fields['NAME']));
    }

    if($fields['SECOND_NAME']){
        $fields['SECOND_NAME'] = \ucfirst(mb_strtolower($fields['SECOND_NAME']));
    }

    if($fields['LAST_NAME']){
        $fields['LAST_NAME'] = \ucfirst(\mb_strtolower($fields['LAST_NAME']));
    }

    if($fields['FULL_NAME']){
        $fields['FULL_NAME'] = \join(" ", \array_filter([$fields['NAME'], $fields['SECOND_NAME'], $fields['LAST_NAME']]));
    }
};

EventManager::getInstance()->addEventHandler('crm', 'OnBeforeCrmLeadAdd', $handlerName);
EventManager::getInstance()->addEventHandler('crm', 'OnBeforeCrmLeadUpdate', $handlerName);


//--------------------For new report----------------------------------
$eventManager = \Bitrix\Main\EventManager::getInstance();

//--------------------CRM----------------------------------
$eventManager->addEventHandler('crm','OnAfterCrmLeadAdd',function(&$arFields) 
{
	define("LOG_FILENAME", $_SERVER["DOCUMENT_ROOT"]."/reportlog.txt");
	AddMessage2Log($arFields, "after_crm_lead_add");
	GsResponsibleHistory::Add('LEAD', $arFields['ID'], $arFields['ASSIGNED_BY_ID']);
	GsStatusHistory::Add('LEAD', $arFields['ID'], $arFields['STATUS_ID'], $arFields['ASSIGNED_BY_ID']);
});
$eventManager->addEventHandler('crm','OnBeforeCrmLeadUpdate',function(&$arFields) 
{
	define("LOG_FILENAME", $_SERVER["DOCUMENT_ROOT"]."/reportlog.txt");
	//AddMessage2Log($arFields, "before_crm_lead_update");
	GsFieldsSync::CheckLeadUpdate($arFields);
});
$eventManager->addEventHandler('crm','OnAfterCrmLeadUpdate',function(&$arFields) 
{
	define("LOG_FILENAME", $_SERVER["DOCUMENT_ROOT"]."/reportlog.txt");
	//AddMessage2Log($arFields, "after_crm_lead_update");
	$fields = GsFieldsSync::GetLeadUpdateFields();
	//AddMessage2Log($fields, "after_crm_lead_update");
	if($fields['ASSIGNED_BY_ID'])
	{
		if(intval($arFields['ASSIGNED_BY_ID'])>0 && intval($arFields['ID'])>0) 
		{ 
			GsResponsibleHistory::Add('LEAD', $arFields['ID'], $arFields['ASSIGNED_BY_ID']);
		}
	}
	if($fields['STATUS_ID'])
	{
		$YBWS_LEAD_RESP_BEFORE = CCrmLead::GetByID($arFields['ID'])['ASSIGNED_BY_ID'];
		GsStatusHistory::Add('LEAD', $arFields['ID'], $arFields['STATUS_ID'], $YBWS_LEAD_RESP_BEFORE);
	}
});