<?php

declare(strict_types=1);

use \Bitrix\Main\{
    EventManager,
    Page
};

EventManager::getInstance()->addEventHandler('main', 'OnProlog', function (){
    if(!\defined('ADMIN_SECTION') && \CSite::InDir('/crm/lead/list/')){
        Page\Asset::getInstance()->addJs('/local/assets/js/lead.list.observer.js', true);
    }
});