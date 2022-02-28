<?
ini_set('memory_limit', '2048M');

if($_REQUEST['AGENT']=="") $_REQUEST['AGENT']='UA';
if($_REQUEST['DATE_RANGE']=="") $_REQUEST['DATE_RANGE']='day';
if($_REQUEST['PRODUCT']=="") $_REQUEST['PRODUCT']='all';
if($_REQUEST['LEAD_SOURCE']=="")$_REQUEST['LEAD_SOURCE'] = array(0=>"all");
if($_REQUEST['ILEAD_SOURCE']=="")$_REQUEST['ILEAD_SOURCE'] = array(0=>"all");
if($_REQUEST['SUB_SOURCE']=="")$_REQUEST['SUB_SOURCE'] = array(0=>"all");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle("4 weeks CRM Report");

Bitrix\Main\UI\Extension::load('ui.bootstrap4');
?>

<style>
.calendar
	{
	padding: 8px;
    border: 1px solid #DDD;
	}
.calendar-icon
	{
	margin-top: 10px !important;
    margin-left: 5px !important;
	}
</style>
<style>
select
{
	width: 10rem;
	border-color: #e4e4e4;
	padding: 0.6rem;
}

.field-caption
{
	font-size:0.8rem;
	font-weight:bold;
	margin-bottom: 0.5rem;
}

.field-caption2
{
	font-size: 0.9rem;
	margin-bottom: 0.5rem;
	color: #717a84;
}
.field-value2
{
	color: #2067b0;
    font-size: 2rem;
    font-weight: bold;
    font-style: italic;
    line-height: 1.5rem;
}
.field-value3
{
    color: #717a8470;
    line-height: 2rem;
}
.field-value4
{
    color: #20c997;
    font-size: 1.5rem;
    font-weight: bold;
    font-style: italic;
    line-height: 1rem;
}
.column
{
	border-right: 1px solid #eef2f4;
    min-width: 10rem;
    padding: 0.5rem;
    text-align: right;
}
.interest 
{
	font-size: 14px;
	color: #BA2F39; 
	position: absolute; 
	bottom: -15px;
}

.show-lids
{
		text-transform: uppercase;
	font-size: 12px;
	cursor: pointer;
}

@media print {
  .hidden-print {
    display: none !important;
  }
}

</style>

<form class="hidden-print" action="?" method="get">
<div class="row">

	<div class="col-auto">
		<div class="field-caption">Date Range: </div>
		<div><select name="DATE_RANGE" id="DATE_RANGE">
				<option value="month" <?if($_REQUEST['DATE_RANGE'] =='month') echo 'selected'?>>4 week</option>
				<option value="daterange" <?if($_REQUEST['DATE_RANGE'] =='daterange') echo 'selected'?>>Date range</option>
			</select>
		</div>
		<div class="row" id="daterange" style="margin-top: 5px;<?if($_REQUEST['DATE_RANGE'] !='daterange') echo ' display:none;'?>">
			<div class="col-auto">
			<div class="field-caption">Date from: </div>
			<?
				$APPLICATION->IncludeComponent(
				'bitrix:main.calendar',
				'',
				array(
					'SHOW_INPUT' => 'Y',
					'INPUT_ADDITIONAL_ATTR' => 'class="calendar"',
					'INPUT_NAME' => 'DATE_RANGE_FROM',
					'INPUT_VALUE' => $_REQUEST['DATE_RANGE_FROM'],
					'SHOW_TIME' => 'N',
					'HIDE_TIMEBAR' => 'Y',
				),
				null,
				array('HIDE_ICONS' => 'Y')
			);?>
			</div>
			<div class="col-auto">
			<div class="field-caption">Date to: </div>
			<?
				$APPLICATION->IncludeComponent(
				'bitrix:main.calendar',
				'',
				array(
					'SHOW_INPUT' => 'Y',
					'INPUT_ADDITIONAL_ATTR' => 'class="calendar"',
					'INPUT_NAME' => 'DATE_RANGE_TO',
					'INPUT_VALUE' => $_REQUEST['DATE_RANGE_TO'],
					'SHOW_TIME' => 'N',
					'HIDE_TIMEBAR' => 'Y',
				),
				null,
				array('HIDE_ICONS' => 'Y')
			);?>
			</div>
		</div>
	</div>

	<div class="col-auto">
		<div class="field-caption">Source: </div>
		<div><select name="LEAD_SOURCE[]" >
				<option value="all" <?if(in_array('all', $_REQUEST['LEAD_SOURCE'])) echo 'selected'?>>All</option>
				<option value="Cold" <?if(in_array('Cold', $_REQUEST['LEAD_SOURCE'])) echo 'selected'?>>Cold leads</option>
				<option value="Hot" <?if(in_array('Hot', $_REQUEST['LEAD_SOURCE'])) echo 'selected'?>>Hot leads</option>
			</select>
		</div>
	</div>

	<div class="col-auto">
		<div class="field-caption">Individual Source: </div>
		<div><select name="ILEAD_SOURCE[]" >
				<option value="all" <?if(in_array('all', $_REQUEST['ILEAD_SOURCE'])) echo 'selected'?>>All</option>
					<?
					$sql = "select STATUS_ID, NAME from b_crm_status WHERE ENTITY_ID='SOURCE' ORDER BY NAME";
					$ins_res = $DB->Query($sql);
					while($ins = $ins_res->fetch())
					{
						?><option value="<?=$ins['STATUS_ID']?>" <?if(in_array($ins['STATUS_ID'], $_REQUEST['ILEAD_SOURCE'])) echo 'selected'?>><?=$ins['NAME']?></option><?
					}
					?>
			</select>
		</div>
	</div>

	<div class="col-auto">
		<div class="field-caption">Sub Source: </div>
		<div><select name="SUB_SOURCE[]" >
				<option value="all" <?if(in_array('all', $_REQUEST['SUB_SOURCE'])) echo 'selected'?>>All</option>
					<?
					$sql = "select distinct UF_CRM_1505283753 from b_uts_crm_lead ORDER BY UF_CRM_1505283753";
					$status_res = $DB->Query($sql);
					while($sub_status = $status_res->fetch())
					{
						?><option value="<?=$sub_status['UF_CRM_1505283753']?>" <?if(in_array($sub_status['UF_CRM_1505283753'], $_REQUEST['SUB_SOURCE'])) echo 'selected'?>><?=$sub_status['UF_CRM_1505283753']?></option><?
					}
					?>
			</select>
		</div>
	</div>

</div>

<div style="margin: 2rem 0;">
	<input value="Render report" type="submit" style="background-color: #2067b0; color: #FFF;  border: 0; cursor:pointer;  padding: 0.5rem 2rem;">
</div>

</form>

<?

//var_dump($_REQUEST['LEAD_SOURCE'][0]);
CModule::IncludeModule('iblock');
$USRS = CIBlockElement::GetList(array(), array('IBLOCK_ID'=>72));
$URERS = array();

while($ob = $USRS->GetNextElement()){ 
 $arProps = $ob->GetProperties();
 $arFields = $ob->GetFields();  
 $URERS[$arProps['USER_ID']['VALUE']]=$arFields['NAME'];

}

//define periods
	$DATERANGE = array(
		'startdate' => false,
		'start'=> '',
		'enddate' => false,
		'end' => '',
		'prevstartdate' => false,
		'prevstart' => '',
		'prevenddate' => false,
		'prevend' => '',
	);

	switch($_REQUEST['DATE_RANGE'])
	{
		default:
		case 'month': 
			$now = new DateTime();
			$ds = $now->format("d.m.Y H:i:s");
			$DATERANGE['enddate'] = DateTimeImmutable::createFromFormat('d.m.Y H:i:s', $ds);
			$DATERANGE['end'] = $DATERANGE['enddate']->format('Y-m-d H:i:s');

			$now->sub(new DateInterval("P28D"));
			$ds = $now->format("d.m.Y").' 00:00:00';
			$DATERANGE['startdate'] = DateTimeImmutable::createFromFormat('d.m.Y H:i:s', $ds);
			$DATERANGE['start'] = $DATERANGE['startdate']->format('Y-m-d H:i:s');

		break;

		case 'daterange':
			$DATERANGE['startdate'] = DateTimeImmutable::createFromFormat('d/m/Y H:i:s', $_REQUEST['DATE_RANGE_FROM'].' 00:00:00');
			$DATERANGE['start'] = $DATERANGE['startdate']->format('Y-m-d H:i:s');
			$DATERANGE['enddate'] = DateTimeImmutable::createFromFormat('d/m/Y H:i:s', $_REQUEST['DATE_RANGE_TO'].' 00:00:00');
			$DATERANGE['enddate'] = $DATERANGE['enddate']->add(new DateInterval("P1D"));
			$DATERANGE['end'] = $DATERANGE['enddate']->format('Y-m-d H:i:s');


			$days = $DATERANGE['enddate']->diff($DATERANGE['startdate'])->days;
			$now = DateTime::createFromFormat('d/m/Y', $_REQUEST['DATE_RANGE_TO']);
			$now->sub(new DateInterval("P".($days*2)."D"));
			$now->add(new DateInterval("P1D"));
			$ds = $now->format("d.m.Y").' 00:00:00';
			$DATERANGE['prevstartdate'] = DateTimeImmutable::createFromFormat('d.m.Y H:i:s', $ds);
			$DATERANGE['prevstart'] = $DATERANGE['prevstartdate']->format('Y-m-d H:i:s');

			$now->add(new DateInterval("P".$days."D"));
			$ds = $now->format("d.m.Y").' 00:00:00';
			$DATERANGE['prevenddate'] = DateTimeImmutable::createFromFormat('d.m.Y H:i:s', $ds);
			$DATERANGE['prevend'] = $DATERANGE['prevenddate']->format('Y-m-d H:i:s');

		break;
	}

$dend = strtotime($DATERANGE['enddate']->format('Y-m-d H:i:s')); // текущее время (метка времени)
$dstart = strtotime($DATERANGE['startdate']->format('Y-m-d H:i:s')); // какая-то дата в строке (1 января 2017 года)
$datediff = $dend - $dstart; // получим разность дат (в секундах)

if (floor($datediff / (60 * 60 * 24))>3700){
	print "Error: too long period!";
	die();
}
if (floor($datediff / (60 * 60 * 24))<0){
	print "Error: the end date of the period must be greater than the start date of the period!";
	die();
}

//Обновление вспомогательной таблицы*************************************************************************************************
/*$clearsql="delete from ybws_status_history WHERE DATE(UF_DATETIME) BETWEEN '".$DATERANGE['startdate']->format('Y-m-d H:i:s')."' AND '".$DATERANGE['enddate']->format('Y-m-d H:i:s')."'";
$DB->Query($clearsql);

$status_rows = array();
$res = $DB->Query("SELECT * FROM b_crm_lead_status_history WHERE DATE(CREATED_TIME) BETWEEN '".$DATERANGE['startdate']->format('Y-m-d H:i:s')."' AND '".$DATERANGE['enddate']->format('Y-m-d H:i:s')."'");
while(($row = $res->fetch()))
{
	$status_rows[$row['ID']]=$row;
}

foreach ($status_rows as $ly){


	$sql2="SELECT ID, UF_RESPONSIBLE_ID, UF_DATETIME from ybws_responsible_history where DATE(UF_DATETIME)<'".$ly['CREATED_TIME']."' and UF_ENTITY_ID=".$ly['OWNER_ID']." order by ID desc limit 1";

	$realresps = $DB->Query($sql2);
	while($realresp = $realresps->fetch())
		{

			$sql3 = "insert into ybws_status_history(UF_ENTITY_TYPE,UF_ENTITY_ID,UF_RESPONSIBLE_ID,UF_DATETIME, UF_STATUS_ID) 
				VALUES('1','".$ly["OWNER_ID"]."','".$realresp['UF_RESPONSIBLE_ID']."','".$ly["CREATED_TIME"]."','".$ly["STATUS_ID"]."')";

			$resp3 = $DB->Query($sql3);

		}


}*/
//конец обновления вспомогательной таблицы**********************************************************************************************************

/*$ar = CCrmStatus::GetStatusList('STATUS');
print "<pre>";
print_r($ar);
print "</pre>";*/

/*Array
(
    [NEW] => Not Contacted
    [33] => Salary less than 6 000
    [34] => Salary more than 6 000
    [38] => E-mail / sms / Wazzup to get in contact
    [29] => Callback Language
    [26] => Re-pitch
    [36] => IN CONTACT pitching
    [18] => Assessment
    [19] => Docs Out
    [35] => Docs Back
    [CONVERTED] => Converted
    [JUNK] => Junk Lead
    [41] => No Response
    [44] => Opt-Out
    [43] => Competitor Fishing
    [7] => Error number
    [1] => Not over-indebted
    [2] => DC too low
    [3] => Unemployed
    [4] => Under DR active
    [5] => Under DR non-active
    [6] => Salary insufficient
    [10] => Insists on having a loan
    [42] => Wants Personal Loan
    [15] => Not interested
    [46] => Not interested but needs help
    [45] => Needs more time
    [20] => Existing client DR
    [21] => Existing client VDR
    [22] => Existing client
    [40] => Duplicate
)*/


//Источники лидов
$PRODUCT_FILTER="";
if($_REQUEST['LEAD_SOURCE'])
	{
		if(!in_array('all', $_REQUEST['LEAD_SOURCE']))
		{
			if(in_array('Cold', $_REQUEST['LEAD_SOURCE']))
			{
				$PRODUCT_FILTER.="AND (SOURCE_ID IN ('WEBFORM','12','15'))";
			}
			if(in_array('Hot', $_REQUEST['LEAD_SOURCE']))
			{
				$PRODUCT_FILTER.="AND (SOURCE_ID IN ('21','26','28', '29', 'WEB', 'EMAIL', 'SELF', '6', '20'))";
			}
		}
	}

if($_REQUEST['ILEAD_SOURCE'])
	{
		if(!in_array('all', $_REQUEST['ILEAD_SOURCE']))
		{

		$PRODUCT_FILTER.=" AND (SOURCE_ID='".$_REQUEST['ILEAD_SOURCE'][0]."')";

		}

	}
//print $PRODUCT_FILTER."<br/>";


//print "BETWEEN '".$DATERANGE['startdate']->format('Y-m-d H:i:s')."' AND '".$DATERANGE['enddate']->format('Y-m-d H:i:s')."'";

//print $DATERANGE['enddate']->format('Y-m-d H:i:s');
//Найдем лиды, которые были назначены на данных пользователей за выбранный период
$arResult=array();
foreach ($URERS as $user=>$user_name){
	$allocated=array();
	$sql = "SELECT * FROM ybws_responsible_history WHERE UF_RESPONSIBLE_ID=".$user." AND  DATE(UF_DATETIME) BETWEEN '".$DATERANGE['startdate']->format('Y-m-d H:i:s')."' AND '".$DATERANGE['enddate']->format('Y-m-d H:i:s')."' ORDER BY DATE(UF_DATETIME)";
	//print $sql."<br/>";
	$resp_history_res = $DB->Query($sql);
	while($resp = $resp_history_res->fetch())
	{

			if (!in_array($resp['UF_ENTITY_ID'], $allocated)) {
				$allocated[] = $resp['UF_ENTITY_ID'];
			}
	}

	//фильтруем горячие/холодные лиды*************************
	$allocated_str="";
	foreach($allocated as $key=>$all_lead_id) $allocated_str=$allocated_str.$all_lead_id.",";
	$allocated_str=mb_substr($allocated_str,0, -1);

	if($PRODUCT_FILTER && count($allocated)>0)
	{
		$sql = "SELECT ID, STATUS_ID, SOURCE_ID, STATUS_SEMANTIC_ID FROM b_crm_lead WHERE ID IN (".$allocated_str.")".$PRODUCT_FILTER;
		//print $sql;
		$filteredLeadsID=array();
		$filteredLeadsRes = $DB->Query($sql);
		while($filteredLead = $filteredLeadsRes->fetch())
			{
				$filteredLeadsID[]=$filteredLead['ID'];
			}

		/*print "<br/>Allocated:<br/>".count($allocated)."<br/>";
		print_r($allocated);

		print "<br/>Filtered:<br/>".count($filteredLeadsID)."<br/>";
		print_r($filteredLeadsID);*/

		$allocated=$filteredLeadsID;

		/*print "<br/>Allocated2:<br/>".count($allocated)."<br/>";
		print_r($allocated);*/

	}
	//конец фильтра по горячим/холодным лидам**************************************

	//фильтр по подисточнику****************************************************

	$allocated_str="";
	foreach($allocated as $key=>$all_lead_id) $allocated_str=$allocated_str.$all_lead_id.",";
	$allocated_str=mb_substr($allocated_str,0, -1);

	if($_REQUEST['SUB_SOURCE'][0]!=all && count($allocated)>0)
	{

		$sql = "select VALUE_ID, UF_CRM_1505283753 from b_uts_crm_lead WHERE VALUE_ID IN (".$allocated_str.") and UF_CRM_1505283753='".$_REQUEST['SUB_SOURCE'][0]."'";
		//print $sql."<br/>";
		$filteredLeadsID=array();
		$filteredLeadsRes = $DB->Query($sql);
		while($filteredLead = $filteredLeadsRes->fetch())
			{
				$filteredLeadsID[]=$filteredLead['VALUE_ID'];
			}

		/*print "<br/>Allocated:<br/>".count($allocated)."<br/>";
		print_r($allocated);

		print "<br/>Filtered:<br/>".count($filteredLeadsID)."<br/>";
		print_r($filteredLeadsID);*/

		$allocated=$filteredLeadsID;

		/*print "<br/>Allocated2:<br/>".count($allocated)."<br/>";
		print_r($allocated);*/

	}


	//конец фильтра по подисточнику**********************************************

	$arResult[$user]['Allocated']=$allocated;
	$arResult[$user]['Name']=$user_name;

	$leadLastStatus = array();
	$assement=array();
	$dockout=array();
	$dockback=array();


	$inContactStatuses=[36,18,19,35,"JUNK",44,1,2,3,4,5,6,10,42,15,46,45];
	$assesmentStatuses=[18,19,35];
	$dockoutStatuses=[19,35];
	$dockbackStatuses=[35];

	$failed=array();
	$notContacted=array();
	$inContact=array();
	$converted=array();


	$failedStatuses=["JUNK",1,2,3,4,5,6,7,10,15,41,42,43,44,45,46];
	$notContactedStatuses=["NEW",38,29,26, 33, 34];
	$convertedStatuses=["CONVERTED"];

	$duplicatesStatuses=[20,21,22,40];
	$duplicated=array();

	if (count($allocated)>0){
		$allocated_str="";
		foreach($allocated as $key=>$all_lead_id) $allocated_str=$allocated_str.$all_lead_id.",";
		$allocated_str=mb_substr($allocated_str,0, -1);

		//в какие статусы переводил лиды данный ответсвенный за выбранный период времени?
		$sql = "SELECT UF_ENTITY_ID, UF_DATETIME, UF_STATUS_ID, UF_RESPONSIBLE_ID FROM ybws_status_history WHERE DATE(UF_DATETIME) BETWEEN '".$DATERANGE['startdate']->format('Y-m-d H:i:s')."' AND '".$DATERANGE['enddate']->format('Y-m-d H:i:s')."' and UF_ENTITY_ID IN (".$allocated_str.") and UF_RESPONSIBLE_ID=".$user." ORDER BY UF_DATETIME";
		//print $sql."<br/>";

		$leads_res = $DB->Query($sql);
		while($ly = $leads_res->fetch())
			{
				$leadLastStatus[$ly['UF_ENTITY_ID']]=$ly['UF_STATUS_ID'];

				if (in_array($ly['UF_STATUS_ID'], $inContactStatuses) && !in_array($ly['UF_ENTITY_ID'],$inContact)) $inContact[] = $ly['UF_ENTITY_ID'];
				if (in_array($ly['UF_STATUS_ID'], $assesmentStatuses) && !in_array($ly['UF_ENTITY_ID'],$assement)) $assement[] = $ly['UF_ENTITY_ID'];
				if (in_array($ly['UF_STATUS_ID'], $dockoutStatuses) && !in_array($ly['UF_ENTITY_ID'],$dockout)) $dockout[] = $ly['UF_ENTITY_ID'];
				if (in_array($ly['UF_STATUS_ID'], $dockbackStatuses) && !in_array($ly['UF_ENTITY_ID'],$dockback)) $dockback[] = $ly['UF_ENTITY_ID'];

			}

		$arResult[$user]['InContact']=$inContact;
		$arResult[$user]['Assesment']=$assement;
		$arResult[$user]['Dockout']=$dockout;
		$arResult[$user]['Dockback']=$dockback;



		/*print "<pre>";
		print_r($leadLastStatus);
		print "</pre>";*/

		foreach($leadLastStatus as $leadID=>$leadLS){

			if (in_array($leadLS, $failedStatuses) && !in_array($leadID,$failed)) $failed[] = $leadID;
			if (in_array($leadLS, $convertedStatuses) && !in_array($leadID,$converted)) $converted[] = $leadID;
			if (in_array($leadLS, $notContactedStatuses) && !in_array($leadID,$notContacted)) $notContacted[] = $leadID;
			if (in_array($leadLS, $duplicatesStatuses) && !in_array($leadID,$duplicated)) $duplicated[]=$leadID;
		}
		//если остался лид, который не попал никуда - он тоже нот контактед
		foreach ($allocated as $leadID){
			if (!in_array($leadLS, $failed) 
				&& !in_array($leadID,$inContact) 
				&& !in_array($leadID,$converted) 
				&& !in_array($leadID,$notContacted)
				&& !in_array($leadID,$assement)
				&& !in_array($leadID,$dockout)
				&& !in_array($leadID,$dockback)
				&& !in_array($leadID,$duplicated)
				) $notContacted[] = $leadID;
		}
	$arResult[$user]['Failed']=$failed;
	$arResult[$user]['NotContacted']=$notContacted;
	$arResult[$user]['Converted']=$converted;
	$arResult[$user]['Duplicated']=$duplicated;
	}
}




//функция для вычисления процента
function interest($arg_1, $arg_2)
{
	$inst = round($arg_1 * 100 / $arg_2, 1);
	if(is_nan($inst) || $inst==0)
	{ echo ''; }	
	else {
		echo $inst.'%';
		}

}

?>
<?

$sours_str="";


$sql = "select STATUS_ID, NAME from b_crm_status WHERE ENTITY_ID='SOURCE' ORDER BY NAME";
$ins_res = $DB->Query($sql);
$ilead_sourses=array();
while($ins = $ins_res->fetch())
	{
		$ilead_sourses[$ins['STATUS_ID']]=$ins['NAME'];
	}


	$shown_all_leads=true;

	if (isset($_REQUEST['LEAD_SOURCE'][0]) && $_REQUEST['LEAD_SOURCE'][0]!='all'){
		$sours_str=$sours_str." Source: ".$_REQUEST['LEAD_SOURCE'][0];
		$shown_all_leads=false;
	}
	if (isset($_REQUEST['ILEAD_SOURCE'][0]) && $_REQUEST['ILEAD_SOURCE'][0]!='all'){
		$sours_str=$sours_str." Individual Source: ".$ilead_sourses[$_REQUEST['ILEAD_SOURCE'][0]];
		$shown_all_leads=false;
	}
	if (isset($_REQUEST['SUB_SOURCE'][0]) && $_REQUEST['SUB_SOURCE'][0]!='all'){
		$sours_str=$sours_str." Sub Source: ".$_REQUEST['SUB_SOURCE'][0];
		$shown_all_leads=false;
	}
	if ($shown_all_leads){
		$sours_str=$sours_str." all leads: ";
	}

?>


	<h3 style="border-bottom: 1px solid #eef2f4; margin-bottom: 1rem; font-size: 1.1rem; margin-top: 2rem;">
		STATS for period <?=$DATERANGE['startdate']->format('d/m/Y H:i:s')?> - <?=$DATERANGE['enddate']->format('d/m/Y H:i:s')?> <?=$sours_str?></h3>
	<table style="width:1100px;">
		<tr style="font-weight: bold; text-transform: uppercase;  border-bottom: 1px solid #eef2f4;  font-size: 0.8rem;  line-height: 2rem; color: #717a84;">
			<td>Agent name</td>
			<td>Allocated leads</td>
			<td>Not contacted</td>
			<td>In contact</td>
			<td>Assessment</td>
			<td>Docs out</td>
			<td>Docs back</td>
			<td>Converted</td>
			<td>Failed</td>
		</tr>

<?
	/*print "<pre>";
print_r($arResult[26417]);
print "</pre>";*/
foreach ($arResult as $Item){
?>

		<tr style="line-height: 3rem; border-bottom: 1px solid #eef2f4;">
			<td><?=$Item['Name']?></td>
			<td><?=count($Item['Allocated'])?> <a class="show-lids" href="#" onClick="alert('<?=implode(',',$Item['Allocated'])?>')">show IDs</a></td>

			<td><?=count($Item['NotContacted'])?> <font color="#BA2F39"><?= interest(count($Item['NotContacted']),count($Item['Allocated'])) ?></font> <a class="show-lids" href="#" onClick="alert('<?=implode(',',$Item['NotContacted'])?>')">IDs>></a></td>
<td><?=count($Item['InContact'])?> <font color="#BA2F39"><?= interest(count($Item['InContact']),count($Item['Allocated'])) ?></font> <a class="show-lids" href="#" onClick="alert('<?=implode(',',$Item['InContact'])?>')">IDs>></a></td>
<td><?=count($Item['Assesment'])?> <font color="#BA2F39"><?= interest(count($Item['Assesment']),count($Item['Allocated'])) ?></font> <a class="show-lids" href="#" onClick="alert('<?=implode(',',$Item['Assesment'])?>')">IDs>></a></td>
<td><?=count($Item['Dockout'])?> <font color="#BA2F39"><?= interest(count($Item['Dockout']),count($Item['Allocated'])) ?></font> <a class="show-lids" href="#" onClick="alert('<?=implode(',',$Item['Dockout'])?>')">IDs>></a></td>
<td><?=count($Item['Dockback'])?> <font color="#BA2F39"><?= interest(count($Item['Dockback']),count($Item['Allocated'])) ?></font> <a class="show-lids" href="#" onClick="alert('<?=implode(',',$Item['Dockback'])?>')">IDs>></a></td>
<td><?=count($Item['Converted'])?> <font color="#BA2F39"><?= interest(count($Item['Converted']),count($Item['Allocated'])) ?></font> <a class="show-lids" href="#" onClick="alert('<?=implode(',',$Item['Converted'])?>')">IDs>></a></td>
<td><?=count($Item['Failed'])?> <font color="#BA2F39"><?= interest(count($Item['Failed']),count($Item['Allocated'])) ?></font> <a class="show-lids" href="#" onClick="alert('<?=implode(',',$Item['Failed'])?>')">IDs>></a></td>


</tr>
<?
}

$allocatedTotal = 0;
$notContactedTotal = 0;
$inContactedTotal = 0;
$assessmentTotal = 0;
$docsOutTotal = 0;
$docsBackTotal = 0;
$convertedTotal = 0;
$failedTotal = 0;

foreach ($arResult as $FR) 
{
$allocatedTotal = $allocatedTotal + count($FR['Allocated']);
$notContactedTotal = $notContactedTotal + count($FR['NotContacted']);
$inContactedTotal = $inContactedTotal + count($FR['InContact']);
$assessmentTotal = $assessmentTotal + count($FR['Assesment']);
$docsOutTotal = $docsOutTotal + count($FR['Dockout']);
$docsBackTotal = $docsBackTotal + count($FR['Dockback']);
$convertedTotal = $convertedTotal + count($FR['Converted']);
$failedTotal = $failedTotal + count($FR['Failed']);
}
?>
<tr style="line-height: 3rem; border-top: 3px solid black;">
		<td style="font-weight: bold;">TOTAL</td>
		<td><?=$allocatedTotal?></td>
		<td style="position: relative; height: 70px;"><?=$notContactedTotal?> <font color="#BA2F39"><?= interest($notContactedTotal, $allocatedTotal) ?></font></td>
		<td style="position: relative; height: 70px;"><?=$inContactedTotal?> <font color="#BA2F39"><?= interest($inContactedTotal, $allocatedTotal) ?></font></td>
		<td style="position: relative; height: 70px;"><?=$assessmentTotal?> <font color="#BA2F39"><?= interest($assessmentTotal, $allocatedTotal) ?></font></td>
		<td style="position: relative; height: 70px;"><?=$docsOutTotal?> <font color="#BA2F39"><?= interest($docsOutTotal, $allocatedTotal) ?></font></td>
		<td style="position: relative; height: 70px;"><?=$docsBackTotal?> <font color="#BA2F39"><?= interest($docsBackTotal, $allocatedTotal) ?></font></td>
		<td style="position: relative; height: 70px;"><?=$convertedTotal?> <font color="#BA2F39"><?= interest($convertedTotal, $allocatedTotal) ?></font></td>
		<td style="position: relative; height: 70px;"><?=$failedTotal?> <font color="#BA2F39"><?= interest($failedTotal, $allocatedTotal) ?></font></td>
</tr>

<script>
$('#DATE_RANGE').on('change', function() {
	if(this.value=="daterange")
	{
		$('#daterange').show();
	}
	else
	{
		$('#daterange').hide();
	}
});


</script>



<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>