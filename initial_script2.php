<?
CModule::IncludeModule('crm');
CModule::IncludeModule('iblock');
global $DB;

$clearsql="delete from ybws_status_history";
$DB->Query($clearsql);

$status_rows = array();
$res = $DB->Query("SELECT * FROM b_crm_lead_status_history");
while(($row = $res->fetch()))
{
	$status_rows[$row['ID']]=$row;
	//print_r($row);
}

foreach ($status_rows as $ly){

	//var_dump($ly);

	$sql2="SELECT ID, UF_RESPONSIBLE_ID, UF_DATETIME from ybws_responsible_history where DATE(UF_DATETIME)<'".$ly['CREATED_TIME']."' and UF_ENTITY_ID=".$ly['OWNER_ID']." order by ID desc limit 1";
	//print $sql2."<br/>";

	$realresps = $DB->Query($sql2);
	while($realresp = $realresps->fetch())
		{
			/*	print "<pre>";
			print_r($realresp);
			print "</pre>";*/

			//print $realresp['UF_RESPONSIBLE_ID']."<br/>";

			$sql3 = "insert into ybws_status_history(UF_ENTITY_TYPE,UF_ENTITY_ID,UF_RESPONSIBLE_ID,UF_DATETIME, UF_STATUS_ID) 
				VALUES('1','".$ly["OWNER_ID"]."','".$realresp['UF_RESPONSIBLE_ID']."','".$ly["CREATED_TIME"]."','".$ly["STATUS_ID"]."')";

			$resp3 = $DB->Query($sql3);

		}


}
print "ok";?>