<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use \Bitrix\Main;
use \Bitrix\Crm;
use \Bitrix\Main\Localization;

$respond = [
    'success' => 'Y',
];

try{
    $application = Main\Application::getInstance();
    $request = $application->getContext()->getRequest();
    $options = $request->getPost('ticket');

    if(!Main\Loader::includeModule('support')){
        throw new \Exception('"support" module is not installed. [040520016.1444.1]');
    }

    if(!Main\Loader::includeModule('crm')){
        throw new \Exception('"crm" module is not installed. [060520016.1925.1]');
    }

    if(!Main\Loader::includeModule('intreface.crmhelpdesk')){
        throw new \Exception('"intreface.crmhelpdesk" module is not installed. [040520016.1849.1]');
    }

    if(!($USER->IsAuthorized() && (\CTicket::IsSupportClient() || \CTicket::IsAdmin() || \CTicket::IsSupportTeam() || \CTicket::IsDemo()))){
        throw new \Exception('Access Denied. [040520016.2344.1]');
    }

    if(!Crm\CompanyTable::getById((int)$options['company_id'])->fetch()){
        throw new \Exception('Company not found. [060520016.1929.1]');
    }

    if(count(array_filter($options)) != 4){
        throw new \Exception('Incorrect parameters. [060520016.2112.1]');
    }

    $params = [
        'UF_CRM_COMPANY' => (int)$options['company_id'],
        'TITLE' => (string)$options['title'],
        'MESSAGE' => (string)$options['description'],
        "CREATED_MODULE_NAME"       => "mail",
        "MODIFIED_MODULE_NAME"      => "mail",
        "OWNER_SID"                 => (string)$options['email'],
        "SOURCE_SID"                => "email",
        "MESSAGE_AUTHOR_SID"        => (string)$options['email'],
        "MESSAGE_SOURCE_SID"        => "email",
    ];
    
    $id = \CTicket::Set($params);
    if($id <= 0){
        throw new \Exception('Error during ticket creation. Please, try again later. [040520016.2344.1]');
    }

    $respond['id'] = $id;
}
catch (\Exception $e){
    $respond['success'] = 'N';
    $respond['error'] = $e->getMessage();
}

echo Main\Web\Json::encode($respond);