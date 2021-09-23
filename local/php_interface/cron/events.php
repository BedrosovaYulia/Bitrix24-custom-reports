#!/usr/bin/php

<?php

use \Bitrix\Main;
use \Bitrix\Main\Mail;

$_SERVER["DOCUMENT_ROOT"] = $DOCUMENT_ROOT = realpath(__DIR__ . "/../../..");

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true);
define('CHK_EVENT', true);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

@set_time_limit(0);
@ignore_user_abort(true);

\CAgent::CheckAgents();
define("BX_CRONTAB_SUPPORT", true);
define("BX_CRONTAB", true);
Mail\EventManager::checkEvents();

if(Main\Loader::includeModule("subscribe")) {
    $posting = new \CPosting();
    $posting->AutoSend();
}