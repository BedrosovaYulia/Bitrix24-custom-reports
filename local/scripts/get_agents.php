<? 
require($_SERVER["DOCUMENT_ROOT"].
"/bitrix/modules/main/include/prolog_before.php");

@set_time_limit(0);
ini_set('max_execution_time', 0);

$rsUsers = CUser::GetList(($by="personal_country"), ($order="desc"), array("GROUPS_ID" => array(11)));
while($user = $rsUsers->GetNext()){
    $ar_templates[$user['ID']] = $user['NAME']." ".$user['LAST_NAME'];
}

echo json_encode($ar_templates, JSON_UNESCAPED_UNICODE);


require($_SERVER["DOCUMENT_ROOT"].
"/bitrix/modules/main/include/epilog_after.php");
?>