<?
ini_set('memory_limit', '2048M');

if($_REQUEST['AGENT']=="") $_REQUEST['AGENT']='UA';
if($_REQUEST['DATE_RANGE']=="") $_REQUEST['DATE_RANGE']='day';
if($_REQUEST['PRODUCT']=="") $_REQUEST['PRODUCT']='all';
if($_REQUEST['LEAD_SOURCE']=="")$_REQUEST['LEAD_SOURCE'] = array(0=>"all");
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

/*print "<pre>";
print_r($URERS);
print "</pre>";*/

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

/*print "<pre>";
print_r($DATERANGE);
print "</pre>";*/

/*$ar = CCrmStatus::GetStatusList('STATUS');
print "<pre>";
print_r($ar);
print "</pre>";*/

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


#Получаем основные справочники
$ALL_RESPONSIBLE_HISTORY=array();
$sql = "SELECT * FROM ybws_responsible_history ORDER BY DATE(UF_DATETIME)";
//print $sql;
$all_resp_history_res = $DB->Query($sql);
while($all_resp = $all_resp_history_res->fetch())
{
		$ALL_RESPONSIBLE_HISTORY[] = $all_resp;
}

#Таблицу истории смены ответсвенных за период
$RESPONSIBLE_HISTORY=array();

$sql = "SELECT * FROM ybws_responsible_history WHERE DATE(UF_DATETIME) BETWEEN '".$DATERANGE['startdate']->format('Y-m-d H:i:s')."' AND '".$DATERANGE['enddate']->format('Y-m-d H:i:s')."' ORDER BY DATE(UF_DATETIME)";
//print $sql;
$resp_history_res = $DB->Query($sql);
while($resp = $resp_history_res->fetch())
{
		$RESPONSIBLE_HISTORY[] = $resp;
}

/*print "<pre>";
print_r($RESPONSIBLE_HISTORY);
print "</pre>";*/

#сразу подсоберем вспомогательный справочник лид -> все его ответсвенные за период
$All_RESP_FOR_LEAD=array();
foreach ($ALL_RESPONSIBLE_HISTORY as $r){
	if (array_key_exists($r['UF_RESPONSIBLE_ID'],$URERS)){
		$All_RESP_FOR_LEAD[$r['UF_ENTITY_ID']][]=$r['UF_RESPONSIBLE_ID'];
	}
}

#подсоберем вспомогательный справочник ответсвенный -> дата вступления
$RESP_DATE=array();
foreach ($ALL_RESPONSIBLE_HISTORY as $r){
	//print_r($r);
	if (array_key_exists($r['UF_RESPONSIBLE_ID'],$URERS)){
		$RESP_DATE[$r['UF_ENTITY_ID']][$r['UF_RESPONSIBLE_ID']]=$r['UF_DATETIME'];
	}
}

/*print "<pre>";
print_r($RESP_DATE);
print "</pre>";*/

/*print "<pre>";
print_r($All_RESP_FOR_LEAD);
print "</pre>";*/


#2) Справочник источников лидов
$LEADS_SOURCE=array();

#Собираем ИД лидов, для которых нам нужно получить другие данные из таблицы лидов, в частности из этой таблицы нас интересует источник
$leads_id=array();
$lead_id_str="";
foreach ($RESPONSIBLE_HISTORY as $r){
	$lead_id_str=$lead_id_str.$r['UF_ENTITY_ID'].",";#тут у нас строка со списком всех ид лидов через запятую
	$leads_id[]=$r['UF_ENTITY_ID'];
}

$lead_id_str=mb_substr($lead_id_str,0, -1);

$sql = "SELECT ID, STATUS_ID, SOURCE_ID, STATUS_SEMANTIC_ID FROM b_crm_lead WHERE ID IN (".$lead_id_str.")".$PRODUCT_FILTER;
//print $sql;
$leadsID=array();
$leads_res = $DB->Query($sql);
while($lead = $leads_res->fetch())
	{
		$leadsID[$lead['ID']]=$lead['ID']; //похоже это массив ИД лидов уже профильтрованных источником
		$LEADS_SOURCE[$lead['ID']]=$lead['SOURCE_ID'];
	}


#3) справочник последнего статуса за выбранный период для каждого отвественного
$leadStatuses = array();

$sql = "SELECT ID, OWNER_ID, STATUS_ID, RESPONSIBLE_ID, CREATED_TIME FROM b_crm_lead_status_history WHERE CREATED_TIME <= '".$DATERANGE['enddate']->format('Y-m-d H:i:s')."' and OWNER_ID IN (".$lead_id_str.") ORDER BY CREATED_TIME";
//print $sql;
$leadStatuses = array();
$leads_res = $DB->Query($sql);
while($ly = $leads_res->fetch())
	{

		$leadStatuses[$ly['OWNER_ID']][$ly['RESPONSIBLE_ID']]=$ly['STATUS_ID'];
	}
#для тех лидов, у которых за данный период был только 1 ответсвенный - справочник уже собрался нормально, для тех у кого сменился ответсвенный - нужно дособрать

#справочник того, водил ли ответсвенный данный лид в статус ассемент
$ASSESSMENT=array();
$sql = "SELECT ID, OWNER_ID, STATUS_ID, RESPONSIBLE_ID, CREATED_TIME FROM b_crm_lead_status_history WHERE CREATED_TIME <= '".$DATERANGE['enddate']->format('Y-m-d H:i:s')."' and OWNER_ID IN (".$lead_id_str.") and STATUS_ID=18 ORDER BY CREATED_TIME";
//print $sql."<br/>";
$leads_res = $DB->Query($sql);
while($ly = $leads_res->fetch())
	{

		$ASSESSMENT[$ly['OWNER_ID']][$ly['RESPONSIBLE_ID']]=$ly['STATUS_ID'];
}
/*print "<pre>";
print_r($ASSESSMENT);
print "</pre>";*/

#справочник по сделке и ее sub source
$sql = "select VALUE_ID, UF_CRM_1505283753 from b_uts_crm_lead";
$lid_sub_status = array();
$status_res = $DB->Query($sql);
while($status_row = $status_res->fetch())
{
	$lid_sub_status[$status_row['UF_CRM_1505283753']][]=$status_row['VALUE_ID'];
}

/*print_r($_REQUEST['SUB_SOURCE'][0]);
print "<pre>";
print_r($lid_sub_status[$_REQUEST['SUB_SOURCE'][0]]);
print "</pre>";*/

if($_REQUEST['SUB_SOURCE'][0]!=all)
{
$leadsID=array_intersect($leadsID, $lid_sub_status[$_REQUEST['SUB_SOURCE'][0]]);
}

/*print_r($_REQUEST['SUB_SOURCE'][0]);
print "<pre>";
print_r($leadsID);
print "</pre>";*/

foreach ($leadsID as $l_id){
	#если для данного лида за данный период было несколько ответсвенных
	#запросы в цикле нельзя, но их тут будет мало - мы делаем запрос не для всех лидов а только для тех, у кого ответсвеный сменился в течение выбранного периода
	if (count($All_RESP_FOR_LEAD[$l_id])>1){
		//print "<br/>Lead ID: ".$l_id."<br/> Resp IDs: ";
		#для каждого бывшего ответсвенного за лид - выбираем дату вступления в должность
		$k=0;
		$prev_resp=0;

		/*print "<pre>";
		print_r($All_RESP_FOR_LEAD[$l_id]);
		print "</pre>";*/

		#оно уже отсортировано по дате благодаря тому что делая запрос к таблице респ хистори мы вписали там ордер
		foreach($All_RESP_FOR_LEAD[$l_id] as $resp_id){
			//если это самый первый ответсвенный
			if ($k==0){
				$prev_resp=$resp_id;#то неичего не делаем а вписываем его в предыдущего
				//print $resp_id;
				//print $resp_id." ".$RESP_DATE[$l_id][$resp_id]."<br/>";
			}
			else{
				#а если это уже не первый ответсвенный, то у него есть предыдущий
				#ну и теперь для каждого предыдущего ответсвенного - статус это последний статус до даты вступления нынешнего отственного
				if (array_key_exists($prev_resp,$URERS)){
					//print $resp_id." ".$RESP_DATE[$l_id][$resp_id]."<br/>";
					$sql = "SELECT ID, OWNER_ID, STATUS_ID, CREATED_TIME FROM b_crm_lead_status_history WHERE CREATED_TIME <= '".$RESP_DATE[$l_id][$resp_id]."' and OWNER_ID=".$l_id." ORDER BY CREATED_TIME";
					//print $sql."<br/>";
					$leads_res = $DB->Query($sql);
					while($ly = $leads_res->fetch())
					{ $leadStatuses[$l_id][$prev_resp]=$ly['STATUS_ID']; 


					}
					//print $l_id." ".$prev_resp." ".$leadStatuses[$l_id][$prev_resp]."<br/>";

					//и проверяем водил ли данный ответсвенный данный лид в статус ассемент
					$sql = "SELECT ID, OWNER_ID, STATUS_ID, CREATED_TIME FROM b_crm_lead_status_history WHERE CREATED_TIME <= '".$RESP_DATE[$l_id][$resp_id]."' and OWNER_ID=".$l_id." and STATUS_ID=18 ORDER BY CREATED_TIME";
					//print $sql."<br/>";
					$leads_res = $DB->Query($sql);
					while($ly = $leads_res->fetch())
					{ $ASSESSMENT[$l_id][$prev_resp]=$ly['STATUS_ID']; 


					}

				}
				$prev_resp=$resp_id;
			}
			$k=$k+1;
		}
	}
}


/*print "<pre>";
print_r($leadStatuses);
print "</pre>";*/



/*foreach ($leads as $key => $ls){

		$leads[$key]['STATUS_ID']=$leadStatuses[$key];
}*/


$report=array();
$xx=array();

foreach ($RESPONSIBLE_HISTORY as $r){

	$report[$r['UF_RESPONSIBLE_ID']][]=$r['UF_ENTITY_ID'];

}

/*print "<pre>";
print_r($xx);
print "</pre>";

print "<pre>";
print_r(array_unique($xx));
print "</pre>";*/



$STATUS_CONVERTER = array();
$STATUS_CONVERTER["NEW"] = "NOT_CONTACTED";
$STATUS_CONVERTER["38"] = "NOT_CONTACTED";
$STATUS_CONVERTER["29"] = "NOT_CONTACTED";
$STATUS_CONVERTER["26"] = "NOT_CONTACTED";
$STATUS_CONVERTER["36"] = "IN_CONTACT";
$STATUS_CONVERTER["18"] = "ASSESSMENT";
$STATUS_CONVERTER["19"] = "DOCS_OUT";
$STATUS_CONVERTER["35"] = "DOCS_BACK";
$STATUS_CONVERTER["33"] = "NOT_CONTACTED";
$STATUS_CONVERTER["34"] = "NOT_CONTACTED";
$STATUS_CONVERTER["CONVERTED"] = "CONVERTED";
$STATUS_CONVERTER["JUNK"] = "FAILED";
$STATUS_CONVERTER["1"] = "FAILED";
$STATUS_CONVERTER["2"] = "FAILED";
$STATUS_CONVERTER["3"] = "FAILED";
$STATUS_CONVERTER["4"] = "FAILED";
$STATUS_CONVERTER["5"] = "FAILED";
$STATUS_CONVERTER["6"] = "FAILED";
$STATUS_CONVERTER["7"] = "FAILED";
$STATUS_CONVERTER["10"] = "FAILED";
$STATUS_CONVERTER["15"] = "FAILED";
$STATUS_CONVERTER["20"] = "FAILED";
$STATUS_CONVERTER["21"] = "FAILED";
$STATUS_CONVERTER["22"] = "FAILED";
$STATUS_CONVERTER["40"] = "FAILED";
$STATUS_CONVERTER["41"] = "FAILED";
$STATUS_CONVERTER["42"] = "FAILED";
$STATUS_CONVERTER["43"] = "FAILED";
$STATUS_CONVERTER["44"] = "FAILED";
$STATUS_CONVERTER["45"] = "FAILED";

$NOT_CONTACTED=["NEW","38","29","26"];

/*
$URERS = array();
$URERS["44"] = "Alain Le Roux";
$URERS["652"] = "Leticia Haas";
$URERS["7474"] = "Younis Ramadien";
$URERS["226"] = "Kaydene Fortuin";
$URERS["20835"] = "Rienie Heunis";
*/

$FULL_RESULT = array();
$n = 0;

//print "user_id | all allocted leads count |<br/>";
	foreach ($report as $k=>$v)
		{

			$v_lead = array_intersect($v, $leadsID);
			$v_lead=array_unique($v_lead); // возвращаем массив без дублей
			/*print "<br/>";
				print_r($v_lead);
			print "<br/>";*/

			if(isset($URERS["$k"]))
			{
				/*
				print "<br/>";
				print_r($URERS["$k"]);
				print "<br/>";
				print_r('Allocated');
				print "<br/>";
				print count($v)." ";
	*/

				$FULL_RESULT[$n]['User_name']=$URERS["$k"];
				$FULL_RESULT[$n]['Allocated']=count($v_lead);
				$FULL_RESULT[$n]['Elements']['Allocated'][]=$v_lead;
				$leads_by_st=array();
				foreach ($v_lead as $val)
					{
						#$k - ид ответсвенного
						#$val - ид лида
						//$leads_by_st[$leads[$val]['STATUS_ID']][]=$val;
						$leads_by_st[$leadStatuses[$val][$k]][]=$val;
					}


				/*print "<pre>";
                print_r($leads_by_st);
				print "</pre>";*/

				foreach ($leads_by_st as $kl => $lbs)
					{
						if(isset($STATUS_CONVERTER["$kl"]))
						{
							if ($kl!='36'  and $kl!='18'){
								$FULL_RESULT[$n][$STATUS_CONVERTER["$kl"]]=$FULL_RESULT[$n][$STATUS_CONVERTER["$kl"]]+count($lbs);
								$FULL_RESULT[$n]['Elements'][$STATUS_CONVERTER["$kl"]][]=$lbs;
							}
							if (!in_array($kl,$NOT_CONTACTED)){
								//print $kl;
								$FULL_RESULT[$n]['IN_CONTACT']=$FULL_RESULT[$n]['IN_CONTACT']+count($lbs);
								$FULL_RESULT[$n]['Elements']['IN_CONTACT'][]=$lbs;
							}

							foreach ($lbs as $ass_lead_id){

								if(isset($ASSESSMENT[$ass_lead_id][$k])){

									$FULL_RESULT[$n]['ASSESSMENT']=$FULL_RESULT[$n]['ASSESSMENT']+1;
									$FULL_RESULT[$n]['Elements']['ASSESSMENT'][]=array($ass_lead_id);
								}
							}

						}
					}

					$n++;

			}
		}

/*echo '<pre>';
var_dump($FULL_RESULT);
echo '</pre>';*/


function interest($arg_1, $arg_2)
{
	$inst = round($arg_1 * 100 / $arg_2, 1);
	if(is_nan($inst) || $inst==0)
	{ echo ''; }	
	else {
		echo $inst.'%';
		}

}


$mm=0;



?>

	<h3 style="border-bottom: 1px solid #eef2f4; margin-bottom: 1rem; font-size: 1.1rem; margin-top: 2rem;">
		STATS for period <?=$DATERANGE['startdate']->format('d/m/Y')?> - <?=$DATERANGE['enddate']->format('d/m/Y')?>, lead source: <?=$lead_source_header?></h3>
	<table style="width:100%;">
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
	foreach ($FULL_RESULT as $k => $FR)
	{
		/*echo '<pre>';
var_dump($FR['User_name']);
echo '</pre>';
echo '<pre>';
var_dump($FR['Elements']['Allocated']);
echo '</pre>';
echo '<pre>';
var_dump($FR['Elements']['NOT_CONTACTED']);
echo '</pre>';
echo '<pre>';
var_dump($FR['Elements']['IN_CONTACT']);
echo '</pre>';
echo '<pre>';
var_dump($FR['Elements']['DOCS_OUT']);
echo '</pre>';
echo '<pre>';
var_dump($FR['Elements']['DOCS_BACK']);
echo '</pre>';*/


$allocated="";
foreach ($FR['Elements']['Allocated'] as $al_lead_ids){
	foreach ($al_lead_ids as $al_lead_id){
		//print $al_lead_id;
		//$arr_allocated[]=$al_lead_id;
		$allocated=$allocated.$al_lead_id.", ";
	}
}

		//$arr_allocated=array_unique($arr_allocated);
		//$allocated=implode(",", $arr_allocated);

$notContacted="";
foreach ($FR['Elements']['NOT_CONTACTED'] as $al_lead_ids){
		foreach ($al_lead_ids as $al_lead_id){
			//print $al_lead_id;
			$notContacted=$notContacted.$al_lead_id.", ";
		}
	}
$inContacted="";
foreach ($FR['Elements']['IN_CONTACT'] as $al_lead_ids){
		foreach ($al_lead_ids as $al_lead_id){
			//print $al_lead_id;
			$inContacted=$inContacted.$al_lead_id.", ";
		}
	}
$assessment="";
foreach ($FR['Elements']['ASSESSMENT'] as $al_lead_ids){
		foreach ($al_lead_ids as $al_lead_id){
			//print $al_lead_id;
			$assessment=$assessment.$al_lead_id.", ";
		}
	}
$docsOut="";
foreach ($FR['Elements']['DOCS_OUT'] as $al_lead_ids){
		foreach ($al_lead_ids as $al_lead_id){
			//print $al_lead_id;
			$docsOut=$docsOut.$al_lead_id.", ";
		}
	}
$docsBack="";
foreach ($FR['Elements']['DOCS_BACK'] as $al_lead_ids){
		foreach ($al_lead_ids as $al_lead_id){
			//print $al_lead_id;
			$docsBack=$docsBack.$al_lead_id.", ";
		}
}
$converted="";
foreach ($FR['Elements']['CONVERTED'] as $al_lead_ids){
		foreach ($al_lead_ids as $al_lead_id){
			//print $al_lead_id;
			$converted=$converted.$al_lead_id.", ";
		}
	}
$failed="";
foreach ($FR['Elements']['FAILED'] as $al_lead_ids){
		foreach ($al_lead_ids as $al_lead_id){
			//print $al_lead_id;
			$failed=$failed.$al_lead_id.", ";
		}
}
?>
		<tr style="line-height: 3rem; border-bottom: 1px solid #eef2f4;">
			<td><?=$FR['User_name']?></td>
			<td><?=$FR['Allocated']?$FR['Allocated']:0?> <a class="show-lids" href="#" onClick="alert('<?= $allocated ?>')"><?= $FR['Allocated'] != 0? "show id"  : "" ?></a></td>
			<td><?=$FR['NOT_CONTACTED']?$FR['NOT_CONTACTED']:0?> <font color="#BA2F39"><?= interest($FR['NOT_CONTACTED'], $FR['Allocated']) ?></font> <a class="show-lids" href="#" onClick="alert('<?= $notContacted ?>')"><?= $FR['NOT_CONTACTED'] != 0? "show id"  : "" ?></a></td>
			<td><?=$FR['IN_CONTACT']?$FR['IN_CONTACT']:0?> <font color="#BA2F39"><?= interest($FR['IN_CONTACT'], $FR['Allocated']) ?></font> <a class="show-lids" href="#" onClick="alert('<?= $inContacted ?>')"><?= $FR['IN_CONTACT'] != 0? "show id"  : "" ?></a></td>
			<td><?=$FR['ASSESSMENT']?$FR['ASSESSMENT']:0?> <font color="#BA2F39"><?= interest($FR['ASSESSMENT'], $FR['Allocated']) ?></font> <a class="show-lids" href="#" onClick="alert('<?= $assessment ?>')"><?= $FR['ASSESSMENT'] != 0? "show id"  : "" ?></a></td>
			<td><?=$FR['DOCS_OUT']?$FR['DOCS_OUT']:0?> <font color="#BA2F39"><?= interest($FR['DOCS_OUT'], $FR['Allocated']) ?></font> <a class="show-lids" href="#" onClick="alert('<?= $docsOut ?>')"><?= $FR['DOCS_OUT'] != 0? "show id"  : "" ?></a></td>
			<td><?=$FR['DOCS_BACK']?$FR['DOCS_BACK']:0?> <font color="#BA2F39"><?= interest($FR['DOCS_BACK'], $FR['Allocated']) ?></font> <a class="show-lids" href="#" onClick="alert('<?= $docsBack ?>')"><?= $FR['DOCS_BACK'] != 0? "show id"  : "" ?></a></td>
			<td><?=$FR['CONVERTED']?$FR['CONVERTED']:0?> <font color="#BA2F39"><?= interest($FR['CONVERTED'], $FR['Allocated']) ?></font> <a class="show-lids" href="#" onClick="alert('<?= $converted ?>')"><?= $FR['CONVERTED'] != 0? "show id"  : "" ?></a></td>
			<td><?=$FR['FAILED']?$FR['FAILED']:0?> <font color="#BA2F39"><?= interest($FR['FAILED'], $FR['Allocated']) ?></font> <a class="show-lids" href="#" onClick="alert('<?= $failed ?>')"><?= $FR['FAILED'] != 0? "show id"  : "" ?></a></td>
		</tr>
<?
	}

foreach ($FULL_RESULT as $k => $FR) 
{
$allocatedTotal = $allocatedTotal + $FR['Allocated'];
$notContactedTotal = $notContactedTotal + $FR['NOT_CONTACTED'];
$inContactedTotal = $inContactedTotal + $FR['IN_CONTACT'];
$assessmentTotal = $assessmentTotal + $FR['ASSESSMENT'];
$docsOutTotal = $docsOutTotal + $FR['DOCS_OUT'];
$docsBackTotal = $docsBackTotal + $FR['DOCS_BACK'];
$convertedTotal = $convertedTotal + $FR['CONVERTED'];
$failedTotal = $failedTotal + $FR['FAILED'];
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
<?


/*global $DB;
$LEADS = array();
$STATUS_HISTORY = array();
$GRAND_TOTAL = array("ALLOCATED"=>0,"NOT_CONTACTED"=>0,"IN_CONTACT"=>0,"DOCS_OUT"=>0,"DOCS_BACK"=>0, "DOCS_OUT_SUMM"=>0, "DCOS_BACK_SUMM"=>0);
$PERIOD = array("ALLOCATED"=>0,"NOT_CONTACTED"=>0,"IN_CONTACT"=>0,"DOCS_OUT"=>0,"DOCS_BACK"=>0, "DOCS_OUT_SUMM"=>0, "DCOS_BACK_SUMM"=>0);
$PREVPERIOD = array("ALLOCATED"=>0,"NOT_CONTACTED"=>0,"IN_CONTACT"=>0,"DOCS_OUT"=>0,"DOCS_BACK"=>0, "DOCS_OUT_SUMM"=>0, "DCOS_BACK_SUMM"=>0);
$AGENT_TABLE = array();

$STATUS_CONVERTER = array();
$STATUS_CONVERTER["NEW"] = "NOT_CONTACTED";
$STATUS_CONVERTER["33"] = "NOT_CONTACTED";
$STATUS_CONVERTER["34"] = "NOT_CONTACTED";
$STATUS_CONVERTER["38"] = "NOT_CONTACTED";
$STATUS_CONVERTER["29"] = "NOT_CONTACTED";
$STATUS_CONVERTER["26"] = "NOT_CONTACTED";
$STATUS_CONVERTER["36"] = "IN_CONTACT";
$STATUS_CONVERTER["32"] = "IN_CONTACT";
$STATUS_CONVERTER["13"] = "IN_CONTACT";
$STATUS_CONVERTER["37"] = "IN_CONTACT";
$STATUS_CONVERTER["16"] = "IN_CONTACT";
$STATUS_CONVERTER["25"] = "IN_CONTACT";
$STATUS_CONVERTER["18"] = "IN_CONTACT";
$STATUS_CONVERTER["19"] = "DOCS_OUT";
$STATUS_CONVERTER["28"] = "DOCS_OUT";
$STATUS_CONVERTER["35"] = "DOCS_BACK";
$STATUS_CONVERTER["1"] = "DOCS_BACK";

//AMOUNT STORED IN UF_CRM_1547893468 field
//PRODUCT stored in UF_CRM_1581071161 field

if($_REQUEST['AGENT'])
{
	//define filters
	$AGENT_ID = $_REQUEST['AGENT'];
	$AGENT_ID = str_replace("U", "", $AGENT_ID);

	$AGENT = CUser::GetByID($AGENT_ID)->fetch();




	//PRODUCT
	$PRODUCT_FILTER = "";
	if($_REQUEST['PRODUCT']=='dr')
	{
		$PRODUCT_FILTER = "WHERE UF_CRM_1581071161 LIKE '%i:1029;%'";
	}
	if($_REQUEST['PRODUCT']=='vdr')
	{
		$PRODUCT_FILTER = "WHERE UF_CRM_1581071161 LIKE '%i:1030;%'";;
	}
	if($_REQUEST['PRODUCT']=='lorex')
	{
		$PRODUCT_FILTER = "WHERE UF_CRM_1581071161 LIKE '%i:1031;%'";;
	}

	if($_REQUEST['LEAD_SOURCE'])
	{
		if(!in_array('all', $_REQUEST['LEAD_SOURCE']))
		{
			if($PRODUCT_FILTER=="") $PRODUCT_FILTER.="WHERE (";
			else $PRODUCT_FILTER.=" AND (";
			$first=true;
			foreach($_REQUEST['LEAD_SOURCE'] as $val)
			{
				if(!$first) { $PRODUCT_FILTER.=" OR ";}
				$first=false;
				$PRODUCT_FILTER.=" SOURCE_ID='".$val."'";
			}
			$PRODUCT_FILTER.=")";
		}
	}


	//get all users
	$order = array('sort' => 'asc');
	$tmp = 'sort'; // параметр проигнорируется методом, но обязан быть
	$rsUsers = CUser::GetList($order, $tmp);
	$AGENTS = array();
	$AGENTS_STRINGS = array();
	while($agent = $rsUsers->fetch())
	{
		$AGENTS[$agent['ID']]=$agent;
		$agent_string="";
		if($agent['NAME']!="") $agent_string.=$agent['NAME'];
		if($agent['LAST_NAME']!="")
		{
			if($agent_string!="") $agent_string.=" ";
			$agent_string.=$agent['LAST_NAME'];
		}
		$AGENTS_STRINGS[$agent_string] = $agent['ID'];
	}

	//get status history
	$sql = "SELECT OWNER_ID, CREATED_TIME, RESPONSIBLE_ID, STATUS_SEMANTIC_ID, STATUS_ID FROM b_crm_lead_status_history ORDER BY CREATED_TIME ASC";
	$status_res = $DB->Query($sql);
	while($status = $status_res->fetch())
	{
		$STATUS_HISTORY[$status['OWNER_ID']][] = $status;
	}

	//get responsible history
	$sql = "SELECT t1.ID, t1.DATE_CREATE, t1.EVENT_TEXT_1, t1.EVENT_TEXT_2, t2.EVENT_ID, t2.ENTITY_TYPE, t2.ENTITY_FIELD, t2.ENTITY_ID FROM b_crm_event as t1 LEFT JOIN b_crm_event_relations as t2 ON t1.ID=t2.EVENT_ID  WHERE t2.ENTITY_TYPE = 'LEAD' AND t2.ENTITY_FIELD = 'ASSIGNED_BY_ID' ORDER BY t1.DATE_CREATE ASC";
	$resp_history_res = $DB->Query($sql);
	while($resp = $resp_history_res->fetch())
	{
		$RESPONSIBLE_HISTORY[$resp['ENTITY_ID']][] = $resp;
	}

	//get all leads data...
	$sql = "SELECT ID, DATE_CREATE, ASSIGNED_BY_ID, STATUS_ID, SOURCE_ID, STATUS_SEMANTIC_ID, UF_CRM_1547893468, UF_CRM_1581071161 FROM b_crm_lead LEFT JOIN b_uts_crm_lead ON b_crm_lead.ID = b_uts_crm_lead.VALUE_ID ".$PRODUCT_FILTER;

	gc_collect_cycles();

	$leads_res = $DB->Query($sql);
	//$limit = 10;
	while($lead = $leads_res->fetch())
	{
		gc_disable();

		//made responsibles history
		$RESPONSIBLES = array();
		$last_responsible_date = DateTime::CreateFromFormat("Y-m-d H:i:s", $lead['DATE_CREATE']);
		$RESPONSIBLES[] = array('USER'=>$lead['ASSIGNED_BY_ID'], 'DATE'=>$last_responsible_date);

		foreach($RESPONSIBLE_HISTORY[$lead['ID']] as $history)
		{
			$current_responsible_date = DateTime::CreateFromFormat("Y-m-d H:i:s", $history['DATE_CREATE']); 
			if($current_responsible_date->diff($last_responsible_date)->days<3)
			{
				array_pop($RESPONSIBLES);
			}
			$RESPONSIBLES[] = array('USER'=>$AGENTS_STRINGS[$history['EVENT_TEXT_2']], 'DATE'=>$current_responsible_date);	
			$last_responsible_date = $current_responsible_date;
		}

		unset($last_responsible_date);
		unset($current_responsible_date);

		//analyze responsible history
		$responsible_in_period = false;
		$responsible_in_prevperiod = false;
		$grand_total_responsible = false;

		foreach($RESPONSIBLES as $resp)
		{
			if($resp['USER']==$AGENT_ID) $grand_total_responsible = true;

			if($resp['DATE']>$DATERANGE['startdate'] && $resp['DATE']<$DATERANGE['enddate'])
			{
				$responsible_in_period = $resp['USER'];
			}
			if($resp['DATE']>$DATERANGE['prevstartdate'] && $resp['DATE']<$DATERANGE['prevenddate'])
			{
				$responsible_in_prevperiod = $resp['USER'];
			}
		}

		//set data
		if($grand_total_responsible) $GRAND_TOTAL['ALLOCATED']++;
		if($responsible_in_period) $AGENT_TABLE[$responsible_in_period]['ALLOCATED']++;
		if($responsible_in_period==$AGENT_ID)
		{
			$PERIOD['ALLOCATED']++;
			//var_dump($RESPONSIBLES);
		}
		if($responsible_in_prevperiod==$AGENT_ID)
		{
			$PREVPERIOD['ALLOCATED']++;
		}	

		unset($responsible_in_period);
		unset($responsible_in_prevperiod);
		unset($grand_total_responsible);

		unset($RESPONSIBLES);
		unset($lead);

		gc_enable();
		gc_collect_cycles();


}

	if($AGENT)
	{
	?>

	<h1 style="font-size: 1rem; text-align: center;">Report for <?=implode(" ", array($AGENT['NAME'], $AGENT['SECOND_NAME'], $AGENT['LAST_NAME']))?></h1>
	<h3 style="border-bottom: 1px solid #eef2f4; margin-bottom: 1rem; font-size: 1.1rem;">GRAND TOTAL</h3>
	<div class="row" style="margin: 0 1rem;">
		<div class="col-auto column">
			<div class="field-caption2">Allocated leads</div>
			<div class="field-value2"><?=$GRAND_TOTAL['ALLOCATED']?></div>
		</div>
		<div class="col-auto column">
			<div class="field-caption2">Not contacted</div>
			<div class="field-value2"><?=$GRAND_TOTAL['NOT_CONTACTED']?></div>
		</div>
		<div class="col-auto column">
			<div class="field-caption2">In contact</div>
			<div class="field-value2"><?=$GRAND_TOTAL['IN_CONTACT']?></div>
		</div>
		<div class="col-auto column">
			<div class="field-caption2">Docs out</div>
			<div class="field-value2"><?=$GRAND_TOTAL['DOCS_OUT']?></div>
		</div>
		<div class="col-auto column">
			<div class="field-caption2">Docs back</div>
			<div class="field-value2"><?=$GRAND_TOTAL['DOCS_BACK']?></div>
		</div>
	</div>
	<?
	$period_avg_docs_out=0;
	if($PERIOD['DOCS_OUT']>0 && $PERIOD['DOCS_OUT_SUMM']>0)
	{
		$period_avg_docs_out = number_format($PERIOD['DOCS_OUT_SUMM']/$PERIOD['DOCS_OUT'],2);
	}
	$period_avg_docs_back=0;
	if($PERIOD['DOCS_BACK']>0 && $PERIOD['DOCS_BACK_SUMM']>0)
	{
	$period_avg_docs_back = number_format($PERIOD['DOCS_BACK_SUMM']/$PERIOD['DOCS_BACK'],2);
	}
	$prevperiod_avg_docs_out=0;
	if($PERIOD['DOCS_OUT']>0 && $PERIOD['DOCS_OUT_SUMM']>0)
	{
		$prevperiod_avg_docs_out = number_format($PERIOD['DOCS_OUT_SUMM']/$PERIOD['DOCS_OUT'],2);
	}
	$prevperiod_avg_docs_back=0;
	if($PERIOD['DOCS_BACK']>0 && $PERIOD['DOCS_BACK_SUMM']>0)
	{
		$prevperiod_avg_docs_back = number_format($PERIOD['DOCS_BACK_SUMM']/$PERIOD['DOCS_BACK'],2);
	}
	?>
	<h3 style="border-bottom: 1px solid #eef2f4; margin-bottom: 1rem; font-size: 1.1rem; margin-top: 2rem;">PERIOD</h3>
	<div class="row" style="margin: 0 1rem; margin-bottom: 2rem;">
		<div class="col-auto column">
			<div class="field-caption2">Allocated leads</div>
			<div class="field-value2"><?=$PERIOD['ALLOCATED']?></div>
			<div class="field-value3">previous</div>
			<div class="field-value4"><?=$PREVPERIOD['ALLOCATED']?></div>
		</div>
		<div class="col-auto column">
			<div class="field-caption2">Not contacted</div>
			<div class="field-value2"><?=$PERIOD['NOT_CONTACTED']?></div>
			<div class="field-value3">previous</div>
			<div class="field-value4"><?=$PREVPERIOD['NOT_CONTACTED']?></div>
		</div>
		<div class="col-auto column">
			<div class="field-caption2">In contact</div>
			<div class="field-value2"><?=$PERIOD['IN_CONTACT']?></div>
			<div class="field-value3">previous</div>
			<div class="field-value4"><?=$PREVPERIOD['IN_CONTACT']?></div>
		</div>
		<div class="col-auto column">
			<div class="field-caption2">Docs out</div>
			<div class="field-value2"><?=$PERIOD['DOCS_OUT']?></div>
			<div class="field-value3">previous</div>
			<div class="field-value4"><?=$PREVPERIOD['DOCS_OUT']?></div>
		</div>
		<div class="col-auto column">
			<div class="field-caption2">Docs back</div>
			<div class="field-value2"><?=$PERIOD['DOCS_BACK']?></div>
			<div class="field-value3">previous</div>
			<div class="field-value4"><?=$PREVPERIOD['DOCS_BACK']?></div>
		</div>
			<div class="col-auto column">
			<div class="field-caption2">LCR Rank</div>
			<div class="field-value2">0</div>
			<div class="field-value3">previous</div>
			<div class="field-value4">0</div>
		</div>
	</div>
	<div class="row" style="margin: 0 1rem; margin-bottom:2rem;">
		<div class="col-auto column">
			<div class="field-caption2"> </div>
		</div>
		<div class="col-auto column">
			<div class="field-caption2"> </div>
		</div>
		<div class="col-auto column">
			<div class="field-caption2"> </div>
		</div>
		<div class="col-auto column">
			<div class="field-caption2">Total docs out value</div>
			<div class="field-value2"><?=$PERIOD['DOCS_OUT_SUMM']?$PERIOD['DOCS_OUT_SUMM']:0?></div>
			<div class="field-value3">previous</div>
			<div class="field-value4"><?=$PREVPERIOD['DOCS_OUT_SUMM']?$PREVPERIOD['DOCS_OUT_SUMM']:0?></div>
		</div>
		<div class="col-auto column">
			<div class="field-caption2">Total docs back value</div>
			<div class="field-value2"><?=$PERIOD['DOCS_BACK_SUMM']?$PERIOD['DOCS_BACK_SUMM']:0?></div>
			<div class="field-value3">previous</div>
			<div class="field-value4"><?=$PREVPERIOD['DOCS_BACK_SUMM']?$PREVPERIOD['DOCS_BACK_SUMM']:0?></div>
		</div>
			<div class="col-auto column">
			<div class="field-caption2">Lead conversion ratio</div>
			<div class="field-value2">0</div>
			<div class="field-value3">previous</div>
			<div class="field-value4">0</div>
		</div>
	</div>

	<div class="row" style="margin: 0 1rem; margin-bottom:2rem;">
		<div class="col-auto column">
			<div class="field-caption2"> </div>
		</div>
		<div class="col-auto column">
			<div class="field-caption2"> </div>
		</div>
		<div class="col-auto column">
			<div class="field-caption2"> </div>
		</div>
		<div class="col-auto column">
			<div class="field-caption2">Avg docs out value</div>
			<div class="field-value2"><?=$period_avg_docs_out?></div>
			<div class="field-value3">previous</div>
			<div class="field-value4"><?=$prevperiod_avg_docs_out?></div>
		</div>
		<div class="col-auto column">
			<div class="field-caption2">Avg docs back value</div>
			<div class="field-value2"><?=$period_avg_docs_back?></div>
			<div class="field-value3">previous</div>
			<div class="field-value4"><?=$prevperiod_avg_docs_back?></div>
		</div>
			<div class="col-auto column">
			<div class="field-caption2"> </div>
		</div>
	</div>
	<?
	}
	?>
	<h3 style="border-bottom: 1px solid #eef2f4; margin-bottom: 1rem; font-size: 1.1rem; margin-top: 2rem;">AGENT STATS ACCORDING TO RANK (current state for all time)</h3>
	<table style="width:100%;">
		<tr style="font-weight: bold; text-transform: uppercase;  border-bottom: 1px solid #eef2f4;  font-size: 0.8rem;  line-height: 2rem; color: #717a84;">
			<td>Agent name</td>
			<td>Allocated leads</td>
			<td>Not contacted</td>
			<td>In contact</td>
			<td>Docs out</td>
			<td>Docs back</td>
			<td>Total docs out value</td>
			<td>Avg docs out value</td>
			<td>Total docs back value</td>
			<td>Avg docs back value</td>
			<td>LCR</td>
			<td>LCR Rank</td>
		</tr>
	<?
	foreach ($AGENT_TABLE as $key=>$value)
	{
		//var_dump($AGENTS[$key]);
		if($AGENTS[$key]['ACTIVE']=='Y')
		{
			$agent_name = implode(" ", array($AGENTS[$key]['NAME'], $AGENTS[$key]['SECOND_NAME'], $AGENTS[$key]['LAST_NAME']));
			if(trim($agent_name) == "") $agent_name = $AGENTS[$key]['LOGIN'];
			if(trim($agent_name) == "") $agent_name = "USER_".$key;

			$avg_docs_out=0;
			if($value['DOCS_OUT']>0 && $value['DOCS_OUT_SUMM']>0)
			{
				$avg_docs_out = number_format($value['DOCS_OUT_SUMM']/$value['DOCS_OUT'],2);
			}
			$avg_docs_back=0;
			if($value['DOCS_BACK']>0 && $value['DOCS_BACK_SUMM']>0)
			{
				$avg_docs_back = number_format($value['DOCS_BACK_SUMM']/$value['DOCS_BACK'],2);
			}


			?>
			<tr style="line-height: 3rem; border-bottom: 1px solid #eef2f4;">
				<td><?=$agent_name?></td>
				<td><?=$value['ALLOCATED']?></td>
				<td><?=$value['NOT_CONTACTED']?$value['NOT_CONTACTED']:0?></td>
				<td><?=$value['IN_CONTACT']?$value['IN_CONTACT']:0?></td>
				<td><?=$value['DOCS_OUT']?$value['DOCS_OUT']:0?></td>
				<td><?=$value['DOCS_BACK']?$value['DOCS_BACK']:0?></td>
				<td><?=$value['DOCS_OUT_SUMM']?$value['DOCS_OUT_SUMM']:0?></td>
				<td><?=$avg_docs_out?></td>
				<td><?=$value['DOCS_BACK_SUMM']?$value['DOCS_BACK_SUMM']:0?></td>
				<td><?=$avg_docs_back?></td>
				<td>-</td>
				<td>-</td>
			</tr>
			<?
		}
	}
	?>
	</table>
	<?
}
?>

*/?>

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