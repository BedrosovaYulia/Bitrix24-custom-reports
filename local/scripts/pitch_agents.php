<? 
require($_SERVER["DOCUMENT_ROOT"].
"/bitrix/modules/main/include/prolog_before.php");

@set_time_limit(0);
ini_set('max_execution_time', 0);
CModule::IncludeModule('iblock');
CModule::IncludeModule('sale');
CModule::IncludeModule('catalog');
CModule::IncludeModule('crm');
CModule::IncludeModule('lists');
if((count($_POST['ar_contact_id'])>0) && (intval($_POST['agent_id'])>0)){

    foreach($_POST['ar_contact_id'] as $key => $value)
    {
        $obFields = CCrmLead::GetList(
            array(),
            array("ID" => $value)
        );
        while($arFieldsUp = $obFields->GetNext()) {
            $format = "DD.MM.YYYY";
            $new_format = CSite::GetDateFormat("FULL");
            $filter_date = $DB->FormatDate(date("d.m.Y", strtotime("-1 week")), $format, $new_format);
            $arFields = array(
                'TITLE' => $arFieldsUp['TITLE'],
                "UF_CRM_1522699163" => array($filter_date),
                "ASSIGNED_BY" => $_POST['agent_id'],
                "ASSIGNED_BY_ID" => $_POST['agent_id'],
                "ID" => $arFieldsUp['ID']
            );
            $CCrmContact = new CCrmLead();
            $success = $CCrmContact->Update(
                $arFieldsUp['ID'],
                $arFields,
                true,
                true,
                array(
                    'REGISTER_SONET_EVENT' => true
                )
            );
        }
    }

}

require($_SERVER["DOCUMENT_ROOT"].
"/bitrix/modules/main/include/epilog_after.php");
?>