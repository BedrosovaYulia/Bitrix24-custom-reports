<?php
//События для копирования, чтобы поле D.A.T не копировалась
AddEventHandler('crm', 'OnAfterCrmLeadAdd', function($arFields){
    global $DB;
    $rs = $DB->Query($s = "update b_uts_crm_lead
                set
                UF_CRM_1520279212 = '',
                UF_CRM_1520279381 = '',
                UF_CRM_1534770841 = '',
                UF_CRM_1535193505 = NULL
                WHERE VALUE_ID = ".$arFields['ID']."");
});
?>