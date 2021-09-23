<?
class GsResponsibleHistory
{
	const HL_ID = 3;
	const HL_TABLE_NAME = 'ybws_responsible_history';
	const ENTITY_TYPES = array(
		"LEAD"=>1,
		"DEAL"=>2,
		"CONTACT"=>3,
		"COMPANY"=>4
	);
	const UNKNOWN_USER_ID = 35;

	public static function Add($ENTITY_TYPE, $ENTITY_ID, $RESPONSIBLE_ID)
	{

		//print_r(array($ENTITY_TYPE, $ENTITY_ID, $RESPONSIBLE_ID));
		//echo(self::ENTITY_TYPES[$ENTITY_TYPE]);
		$now = new DateTime();
		$hl = self::GetHL();
		$hl::add(array(
			'UF_DATETIME'=>$now->format('d/m/Y H:i:00'),
			'UF_ENTITY_TYPE'=>self::ENTITY_TYPES[$ENTITY_TYPE],
			'UF_ENTITY_ID'=>intval($ENTITY_ID), 
			'UF_RESPONSIBLE_ID'=>intval($RESPONSIBLE_ID),
			'UF_SOURCE'=>'onupdate',
		));
		//print_r($result);
	}

	public static function GetRows($filter=array())
	{
		$result = array();
		$hl = self::GetHL();
		$rows = $hl::getList(array(
		   'order' => array('ID'=>'ASC'),
		   'select' => array('*'),
		   'filter' => $filter
		));
		while($row = $rows->fetch())
		{
			$result[array_search($row['UF_ENTITY_TYPE'], self::ENTITY_TYPES)][$row['UF_ENTITY_ID']][] = array('RESPONSIBLE'=>$row['UF_RESPONSIBLE_ID'], 'DATETIME'=>$row['UF_DATETIME']);
		}
		return $result;
	}

	public static function GetEntityRows($ENTITY_TYPE, $ENTITY_ID)
	{
		$filter = array('UF_ENTITY_TYPE'=>self::ENTITY_TYPES[$ENTITY_TYPE], 'UF_ENTITY_ID'=>$ENTITY_ID);
		$result = self::GetRows($filter);
		return $result[$ENTITY_TYPE][$ENTITY_ID];
	}

	public static function GetResponsibleOnDate($ENTITY_TYPE, $ENTITY_ID, $REQUEST_TIME=false)
	{
		if(!$REQUEST_TIME) $REQUEST_TIME = new DateTime();
		$result = false;

		$rows = self::GetEntityRows($ENTITY_TYPE, $ENTITY_ID);
		foreach($rows as $row)
		{
			if(!$result) $result = $row['RESPONSIBLE'];
			$ELEMENT_TIME = DateTime::createFromFormat("Y-m-d H:i:s", $row['DATETIME']);
			if($REQUEST_TIME>=$ELEMENT_TIME)
			{
				$result = $row['RESPONSIBLE'];
			}
		}
		return $result;
	}

	public static function FindResponsibleOnDateInRows($rows, $REQUEST_TIME=false)
	{
		if(!$REQUEST_TIME) $REQUEST_TIME = new DateTime();
		$result = false;
		foreach($rows as $row)
		{
			if(!$result) $result = $row['RESPONSIBLE'];
			$ELEMENT_TIME = DateTime::createFromFormat("Y-m-d H:i:s", $row['DATETIME']);
			if($REQUEST_TIME>=$ELEMENT_TIME)
			{
				$result = $row['RESPONSIBLE'];
			}
		}
		return $result;
	}

	public static function RecreateHistoryByCrmEvents($ENTITIES_PROCEED_LIMIT=0, $ENTITIES_PROCEED_OFFSET=0, $CLEAR_TABLE=true)
	{

		global $DB;
		if($CLEAR_TABLE) $DB->Query("DELETE FROM ".self::HL_TABLE_NAME);

		$hl = self::GetHL();
		$logger = new GsLogger();

		$USERS = array(
		'Elena Kalugina'=>18100,
		'СУАБ Разработка'=>20694,
		'Bogdana Vyshnivska'=>39282,
		'Oleg Radevych'=>39621,
		'-Пусто-'=>0,
		'&lt;Без имени&gt;'=>1,
		'-Удалено-'=>self::UNKNOWN_USER_ID,
		'Мирслава Ковтун'=>42208,
		'Дмитрий Зайцев'=>36766,
		'Николай Мельников'=>47743,
		'Yuliia Mykytynets'=>47744,
		'Anna Tsap'=>46121,
		'Valeriya Makarushkova'=>46616,
		'Елизавета Килдишова'=>46617,
		);

		$order = array('id' => 'asc');
		$tmp = 'sort';
		$rows = CUser::GetList($order, $tmp, array("!UF_DEPARTMENT"=>false));
		while($row = $rows->fetch())
		{
			$name = implode(" ", array($row['NAME'], $row['LAST_NAME']));
			if($name!="")
			{
				$USERS[$name] = $row['ID'];
			} 
		}

		$EVENTS = array();
		$rows = $DB->Query("SELECT 
			t1.EVENT_ID as ID,
			t1.ENTITY_TYPE as ENTITY_TYPE,
			t1.ENTITY_ID as ENTITY_ID,
			t1.ENTITY_FIELD as ENTITY_FIELD,
			t2.EVENT_TEXT_1,
			t2.EVENT_TEXT_2,
			t2.DATE_CREATE 
		FROM b_crm_event_relations as t1 LEFT JOIN b_crm_event as t2 ON t1.EVENT_ID=t2.ID
		WHERE t1.ENTITY_FIELD='ASSIGNED_BY_ID' 
		ORDER BY t1.EVENT_ID ASC");
		while($row = $rows->fetch())
		{
			$row['FROM']=0;
			$row['TO']=0;
			if($row['EVENT_TEXT_1']!="")
			{
				if(array_key_exists($row['EVENT_TEXT_1'], $USERS)) $row['FROM'] = $USERS[$row['EVENT_TEXT_1']];
				else $logger->Error("Не удалось сопоставить '".$row['EVENT_TEXT_1']."' с пользователем");
			}
			if($row['EVENT_TEXT_2']!="")
			{
				if(array_key_exists($row['EVENT_TEXT_2'], $USERS)) $row['TO'] = $USERS[$row['EVENT_TEXT_2']];
				else $logger->Error("Не удалось сопоставить '".$row['EVENT_TEXT_2']."' с пользователем");
			}

			$EVENTS[$row['ENTITY_TYPE']][$row['ENTITY_ID']][] = $row;
		}

		$TABLES = array(
		"LEAD"=>"b_crm_lead",
		"DEAL"=>"b_crm_deal",
		"CONTACT"=>"b_crm_contact",
		"COMPANY"=>"b_crm_company"
		);

		$ENTITIES_COUNTER = 0;
		$ENTITIES_PROCEED = 0;

		foreach($TABLES as $entitytype=>$tablename)
	   {
		$rows = $DB->Query("SELECT ID, DATE_CREATE, ASSIGNED_BY_ID from ".$tablename." ORDER BY ID ASC");
		while($row = $rows->fetch())
		{
			if($ENTITIES_COUNTER >= $ENTITIES_PROCEED_OFFSET)
			{
				if($ENTITIES_COUNTER<$ENTITIES_PROCEED_LIMIT+$ENTITIES_PROCEED_OFFSET || $ENTITIES_PROCEED_LIMIT==0)
				{
					if($EVENTS[$entitytype][$row['ID']])
					{
						$first = true;
						foreach($EVENTS[$entitytype][$row['ID']] as $event)
						{
							if($first)
							{
								if($event['FROM']>0)
								{
									$hl::add(array(
										'UF_DATETIME'=>$row['DATE_CREATE'],
										'UF_ENTITY_TYPE'=>self::ENTITY_TYPES[$entitytype],
										'UF_ENTITY_ID'=>intval($row['ID']), 
										'UF_RESPONSIBLE_ID'=>intval($event['FROM']),
										'UF_SOURCE'=>'events',
									));
								}
								else 
								{
									$logger->Warn("Не удалось определить начального ответственного у ".$entitytype.$row['ID']);
									$hl::add(array(
										'UF_DATETIME'=>$row['DATE_CREATE'],
										'UF_ENTITY_TYPE'=>self::ENTITY_TYPES[$entitytype],
										'UF_ENTITY_ID'=>intval($row['ID']), 
										'UF_RESPONSIBLE_ID'=>self::UNKNOWN_USER_ID,
										'UF_SOURCE'=>'events',
									));
								}
							}
		
							if($event['TO']>0)
							{
								$hl::add(array(
									'UF_DATETIME'=>$event['DATE_CREATE'],
									'UF_ENTITY_TYPE'=>self::ENTITY_TYPES[$entitytype],
									'UF_ENTITY_ID'=>intval($row['ID']), 
									'UF_RESPONSIBLE_ID'=>intval($event['TO']),
									'UF_SOURCE'=>'events',
								));
							}
							else
							{
								$logger->Warn("Не удалось определить ответственного у ".$entitytype.$row['ID']);
								$hl::add(array(
									'UF_DATETIME'=>$event['DATE_CREATE'],
									'UF_ENTITY_TYPE'=>self::ENTITY_TYPES[$entitytype],
									'UF_ENTITY_ID'=>intval($row['ID']), 
									'UF_RESPONSIBLE_ID'=>self::UNKNOWN_USER_ID,
									'UF_SOURCE'=>'events',
								));
							}
							$first=false;
						}
					}
					else
					{
						if($row['ASSIGNED_BY_ID']>0)
						{
							$hl::add(array(
								'UF_DATETIME'=>$row['DATE_CREATE'],
								'UF_ENTITY_TYPE'=>self::ENTITY_TYPES[$entitytype],
								'UF_ENTITY_ID'=>intval($row['ID']), 
								'UF_RESPONSIBLE_ID'=>intval($row['ASSIGNED_BY_ID']),
								'UF_SOURCE'=>'events',
							));
						}
						else 
						{
							$logger->Warn("Не удалось определить начального ответственного у ".$entitytype.$row['ID']);
							$hl::add(array(
								'UF_DATETIME'=>$row['DATE_CREATE'],
								'UF_ENTITY_TYPE'=>self::ENTITY_TYPES[$entitytype],
								'UF_ENTITY_ID'=>intval($row['ID']), 
								'UF_RESPONSIBLE_ID'=>self::UNKNOWN_USER_ID,
								'UF_SOURCE'=>'events',
							));
						}
					}
					$ENTITIES_PROCEED++;
				}
			}
			$ENTITIES_COUNTER++;
		}
	   }
		$logger->Info("Всего пробежали ".$ENTITIES_COUNTER. " и записали ".$ENTITIES_PROCEED);
		return $ENTITIES_PROCEED;
		//return $logger->errors;
	}

	private static function GetHL()
	{
		CModule::IncludeModule('highloadblock');
		$hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById(self::HL_ID);
		if($hlblock = $hlblock->fetch())
		{
			$entity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
			return $entity->getDataClass();
		}
		return false;
	}
}
?>