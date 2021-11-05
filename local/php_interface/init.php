<?php
use \Bitrix\Main\{EventManager};


// Load event handlers

$iterator = new \RecursiveIteratorIterator(
    new \RecursiveDirectoryIterator(__DIR__ . '/events/', \RecursiveDirectoryIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS | \FilesystemIterator::UNIX_PATHS),
    \RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $item) {
    if ($item->isFile()) {
        if ($item->getExtension() != 'php') {
            continue;
        }

        if ($item->getFilename()[0] == '~') {
            continue;
        }

        require_once $item->getPathname();
    }
}



$eventManager = EventManager::getInstance();
//las code from nastia
//lead add witch rest API, i have not access in the rest API
//check lead add with rest api, update phone number
/*
define("LOG_FILENAME", $_SERVER["DOCUMENT_ROOT"]."/rest_log.txt");
$eventManager->addEventHandler("crm", "OnBeforeCrmLeadAdd", function(&$fields){
    if((string)$fields['COMMENTS'] != '') {
        //search string in the comment
        if(\mb_strpos($fields['COMMENTS'], 'Additional contact number:') !== false) {
            $phoneNumber = \str_replace('Additional contact number: ', '', $fields['COMMENTS']);
            AddMessage2Log($phoneNumber, "phoneNumber");
            if((int)$phoneNumber > 0) {
                if(\substr($phoneNumber, 0, 1) == 0) {
                    $phoneNumber = '27'.\substr($phoneNumber, -(\strlen($phoneNumber)-1));
                }
                $fields['COMMENTS'] = '';
                AddMessage2Log($phoneNumber, "phoneNumbeResult");
                $newPhone = [
                    'VALUE_TYPE' => 'WORK',
                    'VALUE' => $phoneNumber,
                ];
                if(!empty($fields['FM']['PHONE'])) {
                    $countPhone = \count($fields['FM']['PHONE']) + 1;
                    $fields['FM']['PHONE']['n'.$countPhone] = $newPhone;
                }
                else {
                    $fields['FM']['PHONE']['n1'] = $newPhone;
                }
            }
        }
    }
});
*/
//old code
if (isset($_GET['noinit']) && !empty($_GET['noinit']))
{
    $strNoInit = strval($_GET['noinit']);
    if ($strNoInit == 'N')
    {
        if (isset($_SESSION['NO_INIT']))
            unset($_SESSION['NO_INIT']);
    }
    elseif ($strNoInit == 'Y')
    {
        $_SESSION['NO_INIT'] = 'Y';
    }
}

if (!(isset($_SESSION['NO_INIT']) && $_SESSION['NO_INIT'] == 'Y'))
{
    if (file_exists($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/functions/ybws.php"))
        require_once($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/functions/ybws.php");

    if (file_exists($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/functions/crmbot.php"))
        require_once($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/functions/crmbot.php");

	if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/classes/GsLogger.php")) require_once($_SERVER["DOCUMENT_ROOT"] . "/local/classes/GsLogger.php");
	if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/classes/GsResponsibleHistory.php")) require_once($_SERVER["DOCUMENT_ROOT"] . "/local/classes/GsResponsibleHistory.php");
	if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/classes/GsStatusHistory.php")) require_once($_SERVER["DOCUMENT_ROOT"] . "/local/classes/GsStatusHistory.php");
	if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/classes/GsFieldsSync.php")) require_once($_SERVER["DOCUMENT_ROOT"] . "/local/classes/GsFieldsSync.php");


}

AddEventHandler("main", "OnBeforeUserUpdate", "OnBeforeUserUpdateHandler");
// создаем обработчик события "OnBeforeUserUpdate"
function OnBeforeUserUpdateHandler(&$arFields)
{

    if(is_set($arFields, "ACTIVE") && $arFields["ACTIVE"]=="N")
    {
        //gather current data
        $rsUser = CUser::GetByID($arFields['ID']);
        if($arUser = $rsUser->Fetch())
        {
            if($arUser['ACTIVE']!=$arFields['ACTIVE'])
            {
                //user was dismissed
                CModule::IncludeModule('iblock');
                global $USER;
                $el = new CIBlockElement;
                $PROP = array();
                $PROP[11] = $arUser['ID'];
                $PROP[15] = 32;
                $date = new DateTime();

                $arElementFields = array(
                    "MODIFIED_BY"    => $USER->GetID(), // элемент изменен текущим пользователем
                    "IBLOCK_SECTION_ID" => false,          // элемент лежит в корне раздела
                    "IBLOCK_ID" => 6,
                    "PROPERTY_VALUES" => $PROP,
                    "NAME" => "Fired - ".implode(' ', array($arUser['NAME'], $arUser['SECOND_NAME'], $arUser['LAST_NAME'])).'['.$arUser['LOGIN'].']',
                    "ACTIVE" => "Y",
                    "ACTIVE_FROM"=>$date->format('d/m/Y')
                );

                $el->Add($arElementFields);

            }
        }
    }
}

//hidden 'blog' tab in the workgroup

$eventManager->addEventHandler("socialnetwork", "OnFillSocNetMenu", function (&$fields, $arParams) {
    $fields['CanView']['blog'] = false;
});