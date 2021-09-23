<?
CModule::IncludeModule('crm');
CModule::IncludeModule('iblock');
global $DB;


$lead = array();

$limit=0;
$res = $DB->Query("SELECT ID, DATE_CREATE, CREATED_BY_ID FROM b_crm_lead");
while(($row = $res->fetch()))
{
	$lead[$row['ID']]=$row;
}

//var_dump($lead);
foreach ($lead as $l){
	print_r(array(1,$l["ID"],$l["CREATED_BY_ID"],$l["DATE_CREATE"]));

	$sql = "insert into ybws_responsible_history(UF_ENTITY_TYPE,UF_ENTITY_ID,UF_RESPONSIBLE_ID,UF_DATETIME) VALUES('1','".$l['ID']."','".$l['CREATED_BY_ID']."','".$l['DATE_CREATE']."')";

	$resp = $DB->Query($sql);
}



$RESPONSIBLE_HISTORY=array();

//get responsible history
$sql = "SELECT t1.ID, t1.DATE_CREATE,  t1.EVENT_TEXT_2,  t2.ENTITY_TYPE, t2.ENTITY_ID FROM b_crm_event as t1 LEFT JOIN b_crm_event_relations as t2 ON t1.ID=t2.EVENT_ID  WHERE t2.ENTITY_TYPE = 'LEAD' AND t2.ENTITY_FIELD = 'ASSIGNED_BY_ID' ORDER BY t1.DATE_CREATE ASC";
$resp_history_res = $DB->Query($sql);
while($resp = $resp_history_res->fetch())
{
		$RESPONSIBLE_HISTORY[$resp['ENTITY_ID']][] = $resp;
}

//var_dump($RESPONSIBLE_HISTORY);
$user = array();

//Массив пользователей
$res = $DB->Query("SELECT ID, NAME, LAST_NAME FROM b_user");
while($row = $res->fetch())
{
	$user[$row['NAME'].' '.$row['LAST_NAME']]=$row['ID'];
}

//var_dump($user)


foreach($RESPONSIBLE_HISTORY as $r){

	//print_r($r);
	if (isset($user[$r[0]["EVENT_TEXT_2"]])){
		echo($r[0]["EVENT_TEXT_2"]);
		echo($user[$r[0]["EVENT_TEXT_2"]]);
		print "<br/>";

		print_r(array(1,$r[0]["ENTITY_ID"],$user[$r[0]["EVENT_TEXT_2"]],$r[0]["DATE_CREATE"]));

		$sql = "insert into ybws_responsible_history(UF_ENTITY_TYPE,UF_ENTITY_ID,UF_RESPONSIBLE_ID,UF_DATETIME) 
				VALUES('1','".$r[0]["ENTITY_ID"]."','".$user[$r[0]["EVENT_TEXT_2"]]."','".$r[0]["DATE_CREATE"]."')";

		$resp = $DB->Query($sql);


	}

}

?>