<?php
CModule::IncludeModule('crm');
use \Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Event;
use Bitrix\Voximplant;
use Bitrix\Crm;
use Bitrix\Main\Page\Asset;
CJSCore::Init('jquery');
define('DOCKERIZE', true);

$loaderFile = new Main\IO\File(__DIR__ . '/lib/Loader.php');
if($loaderFile->isExists()){
    require_once $loaderFile->getPath();

    \Intreface\Loader::register(new Main\IO\Directory(Main\Application::getDocumentRoot() . '/local/php_interface/lib/'), ['prefix' => '\\Intreface']);
    \Intreface\Loader::register(new Main\IO\Directory(Main\Application::getDocumentRoot() . '/local/php_interface/vendor/'));
}

function logMicros($result, $comment){
    $html = '\-------'.$comment."---------\n";
    $html.= $result;
    $html.="\n".date("d.m.Y H:i:s")."\n--------------------\n\n\n";
    $file=$_SERVER["DOCUMENT_ROOT"]."/upload/log_micros.txt";
    $old_data=file_get_contents($file);
    file_put_contents($file, $html.$old_data);
}

AddEventHandler('voximplant', 'onCallStart',array('LogCall', 'Startcall'),101);
AddEventHandler('voximplant', 'onCallEnd',array('LogCall', 'Hangupcall'),102);
class LogCall {
    function Startcall($CALL_ID){ logMicros(print_r('Start Call', true));	file_put_contents('call-log1.txt', 'call end');}
    function Hangupcall($fields){


        if ($fields['CRM_ACTIVITY_ID']>0){

			global $DB;

			$rs = $DB->Query($s = "SELECT ID,CALL_DURATION,CALL_STATUS,INCOMING,CRM_ENTITY_ID FROM b_voximplant_statistic
                WHERE  CRM_ENTITY_ID = (SElECT CRM_ENTITY_ID FROM  b_voximplant_statistic WHERE CRM_ACTIVITY_ID = ".$fields['CRM_ACTIVITY_ID'].")
                ORDER BY ID DESC");

			while ($ars = $rs->Fetch()){
				$ar[]=$ars;
			}

			$i=0;
			foreach ($ar as &$ar1) {
			    if ($ar1['CALL_DURATION'] > 20) break;
				$i++;
			}

    		if ($i==0){
                $data = $ar1['CALL_DURATION'] . " sec " . date("d.m.Y H:i:s");
            	$rs = $DB->Query($s = "update b_uts_crm_lead
                    set
                    UF_CRM_1520279212 = ".$i.",
                    UF_CRM_1534770841 = ".$ar1['CALL_DURATION'].",
                    UF_CRM_1520279381 = '".date('d/m/Y H:i:s a').",
                    UF_CRM_1535193505 = '".date('Y-m-d H:i:s a')."'
                    WHERE VALUE_ID = ".$ar[0]['CRM_ENTITY_ID']."");
            	logMicros(print_r($ar, true), '$i == 0');
    		}
    		else
    		{
    		    logMicros(print_r($ar, true), '$i != 0');

        		$rs = $DB->Query($s = "update b_uts_crm_lead
                    set
                    UF_CRM_1520279212 = ".$i."
                    WHERE VALUE_ID = ".$ar[0]['CRM_ENTITY_ID']."");
    		}
        }
    }
}

AddEventHandler('main', 'OnEpilog', function(){
    global $USER;
    $arGroups = CUser::GetUserGroup($USER->GetID());

    if(in_array(19, $arGroups)){
        Asset::getInstance()->addJs('/bitrix/js/custom/crm_assign.js');
    }
    $arJsConfig = array(
        'custom_crm' => array(
            'js' => '/bitrix/js/custom/crm_custom.js',
            'css' => '/bitrix/js/custom/crm_custom.css',
            'rel' => array(),
        ),
    );
    foreach ($arJsConfig as $ext => $arExt) {
        \CJSCore::RegisterExt($ext, $arExt);
    }
    CUtil::InitJSCore(array('custom_crm'));

});
//micros 14.08.18 hide general chat for group №21
AddEventHandler("main", "OnAfterUserLogin", "DoNotShowGroup");
function DoNotShowGroup(){
	global $USER;
	global $DB;
	if($USER->GetID() == 148){
		$data = CGroup::GetGroupUser(21);
// 		logMicros(print_r($data,true), "123456");

		$users = implode(",", $data);
// 		logMicros(print_r(implode(",", $data),true), "123456");

		$rs = $DB->Query($s = "DELETE FROM b_im_relation WHERE  USER_ID in (".$users.")  AND CHAT_ID = 1");
	}
}
//micros 26.06.18 block user message
AddEventHandler("im", "OnBeforeMessageNotifyAdd", "contactlisthadler");
function contactlisthadler(&$arParams){
	global $USER;
	global $DB;
// 	if($USER->GetID() == 148){
	logMicros(print_r($arParams,true), $USER->GetID());
	if($arParams['MESSAGE_TYPE'] != "P"){
		$rs = $DB->Query($s = "SELECT ID,CHAT_ID,USER_ID,MESSAGE_STATUS FROM b_im_relation
                WHERE  CHAT_ID = ".$arParams['TO_CHAT_ID']." ORDER BY ID DESC");
		while ($ars = $rs->Fetch()){
// 		 logMicros(print_r($ars['USER_ID'],true), $USER->GetID());
			if($ars['USER_ID'] == $USER->GetID()){
				$arParams['TO_USER_ID'] = 35;
		// 		logMicros(print_r($ars['MESSAGE_STATUS'],true), $arParams['TO_CHAT_ID']);
			}
		}
	}



// 		logMicros(print_r($arParams['MESSAGE_STATUS'],true), $arParams['TO_CHAT_ID']);
// 		if(CSite::InGroup( array($arParams['TO_CHAT_ID']) )){
// 			logMicros(print_r(CUser::GetUserGroup($USER->GetID()),true), $arParams['TO_CHAT_ID']);
// 		}
// 	}


	$Hans = 35;
	$User = 163;

	if ( CSite::InGroup( array(21,23) )){ //if users are in groups 21,23
		If ($arParams[TO_USER_ID] != $Hans){
			return Array(
					'reason' => 'messaging blocked',
					'result' => false,
			);
		}
	}
}
AddEventHandler("main", "OnProlog", "addCSS");
function addCSS(){
global $APPLICATION, $USER;
    //if ($USER->GetID() == 163){
    if ( CSite::InGroup( array(21,23) )){
        $APPLICATION->SetAdditionalCSS("/local/php_interface/micros/additional.css");
    }
}

AddEventHandler("main", "OnAfterUserLogin", "OnAfterUserLoginHandler");
function OnAfterUserLoginHandler(&$fields){
    $arGroups = CUser::GetUserGroup($fields['USER_ID']);
    if (in_array(23, $arGroups)){
        @file_put_contents(__DIR__ . '/login.log', print_r('In Sales ||', true));
    }
}
require 'micros/events.php';
?>
