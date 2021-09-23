<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use \Bitrix\Main;
use \Bitrix\Main\Localization;

$respond = [
    'success' => 'N',
];

try{
    $application = Main\Application::getInstance();
    $request = $application->getContext()->getRequest();

    $parameters = [
        'PATH_TO_USER_PROFILE' => '/company/personal/user/#USER_ID#/',
        'PATH_TO_SUPPORT_TICKET' => '/bitrix/admin/ticket_edit.php?ID=#TICKET_ID#',
    ];

    $elementsOnPage = 20;

    if(!$USER->IsAuthorized()){
        throw new \Exception('Not Authorized. [12072016.2322.1]');
    }

    if(!Main\Loader::includeModule('support')){
        throw new \Exception('"support" module is not installed. [12072016.2323.1]');
    }

    if(!Main\Loader::includeModule('intreface.crmhelpdesk')){
        throw new \Exception('"intreface.crmhelpdesk" module is not installed. [12072016.2324.1]');
    }

    $ticket_id = (int)$request->getQuery('ticket_id');
    if($ticket_id <= 0){
        throw new \Exception('Ticket ID is incorrect. [12072016.2325.1]');
    }

    $source = \CTicket::GetMessageList($by = 'ID', $order = 'desc', ["TICKET_ID" => $ticket_id, "TICKET_ID_EXACT_MATCH" => "Y", 'IS_MESSAGE' => 'Y'], $isFiltered = null);
    while($record = $source->Fetch()){
        $respond['message'][] = $record;
    }

    $respond['ticket'] = \CTicket::GetByID($ticket_id)->Fetch();
    $respond['success'] = 'Y';
}
catch (\Exception $e){
    $respond['error'] = $e->getMessage();
}

echo Main\Web\Json::encode($respond);