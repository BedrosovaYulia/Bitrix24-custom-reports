<?
class GsFieldsSync
{
	//КОД ПОЛЕЙ ДРУГОЙ!!!!!
	//Функции с реакцией на изменения полей...
	public static function Leads($start=0)
	{
		/*global $DB;
		
		$current=0;
		$processed=0;
		$limit_start=$start;
		$limit=500;
		$total=0;

		$rows = $DB->Query("SELECT ID FROM b_crm_lead");
		while($row = $rows->fetch())
		{
			$total++;
			if($current>=$limit_start)
			{
				if($processed<$limit)
				{
					$processed++;
					GsFieldsSync::LeadFMPhone($row['ID']);
				}
			}
			$current++;
		}

		$limit_start += $limit;
		if($limit_start<$total)
		{
			return "GsFieldsSync::Lead(".$limit_start.");";
		}
		else return "";*/
	}


	public static function ProductRowToDeal($dealid, $arFields)
	{
		$logger = new GsLogger();
		CModule::IncludeModule("crm");
		$sections = array();
		foreach($arFields as $product)
		{
			$section = self::GetProductTopSection($product['PRODUCT_ID']);
			if($section>0) $sections[$section] = $section;
		}

		$fields = array('UF_CRM_1622782323'=>$sections);
		//Avivi task JJ24-450 при добавлении товара указываются города в сделке
		/*$cities = self::GetProductCityIds($arFields);
		if(!empty($cities)){
			$fields['UF_CITY'] = $cities;
		}*/
		// Avivi task JJ24-450 end
		$Deal = new CCrmDeal(false);
		$Deal->Update($dealid, $fields);
	}

	public static function LeadFMPhone($lead_id)
	{
		$other_codes = array("880", "852", "855", "86", "82", "886", "81");

		//получим телефоны лида...
		if($lead_id>0)
		{
			$phones = array();
			global $DB;
			$rows = $DB->Query('SELECT * FROM b_crm_field_multi WHERE ENTITY_ID="LEAD" AND TYPE_ID="PHONE" AND ELEMENT_ID='.$lead_id);
			while($row = $rows->fetch())
			{
				$val = $row['VALUE'];
				$val = str_replace(array("+","-"," ","(",")"), "", $val);
				if(substr($val,0,1)=='8')
				{
					$clear=true;
					foreach($other_codes as $code)
					{
						if(substr($val,0,strlen($code))==$code) $clear=false;
					}
			
					if($clear)
					{
						$val = "+7".substr($val,1);	
						$cfm = new CCrmFieldMulti();
						$cfm_fields = array('VALUE'=>$val, 'TYPE_ID'=>$row['TYPE_ID'], 'VALUE_TYPE'=>$row['VALUE_TYPE']);
						$cfm->Update($row['ID'], $cfm_fields);
					}
				}
				$phones[] = $val;
			}

			$countries = array();
			foreach($phones as $phone)
			{
				$found = GsCrmHelper::GetCountryByPhoneCode($phone);
				foreach($found as $fnd)
				{
					$countries[$fnd]=$fnd;
				}
			}

			CModule::IncludeModule("crm");
			$lead = new CCrmLead(false);
			$arfields = array('UF_CRM_1622782451'=>$countries);
			$lead->Update($lead_id, $arfields, true, true, array('ENABLE_SYSTEM_EVENTS'=>false));
		}
	}


	//Настройки отслеживаемых полей:

	private const crm_lead = array(
		'global_var'=>'CRM_LEAD_UPDATE_FIELDS',
		'primary'=>array(
			'b_crm_lead'=>array('ASSIGNED_BY_ID')
		),
		'crm_fm'=>array(
			'entity'=>'LEAD',
		),
	);

	private const crm_deal = array(
		'global_var'=>'CRM_LEAD_UPDATE_FIELDS',
		'primary'=>array(
			'b_crm_deal'=>array('ASSIGNED_BY_ID', 'STAGE_ID')
		),
	);

	private const crm_contact = array(
		'global_var'=>'CRM_CONTACT_UPDATE_FIELDS',
		'primary'=>array(
			'b_crm_contact'=>array('ASSIGNED_BY_ID'))
	);

	private const crm_company = array(
		'global_var'=>'CRM_COMPANY_UPDATE_FIELDS',
		'primary'=>array(
			'b_crm_company'=>array('ASSIGNED_BY_ID')
		),
	);

	//Функции по поиску изменившихся остлеживаемых полей

	private static function GetGlobal($options)
	{
		global $YBWS;
		if($YBWS[$options['global_var']]) return $YBWS[$options['global_var']];
		else return array();
	}

	public static function CheckLeadUpdate($arFields)
	{
		self::CheckFields($arFields, self::crm_lead);
	}

	public static function GetLeadUpdateFields()
	{
		return self::GetGlobal(self::crm_lead);
	}

	public static function CheckDealUpdate($arFields)
	{
		self::CheckFields($arFields, self::crm_deal);
	}

	public static function GetDealUpdateFields()
	{
		return self::GetGlobal(self::crm_deal);
	}

	public static function CheckContactUpdate($arFields)
	{
		self::CheckFields($arFields, self::crm_contact);
	}

	public static function GetContactUpdateFields()
	{
		return self::GetGlobal(self::crm_contact);
	}

	public static function CheckCompanyUpdate($arFields)
	{
		self::CheckFields($arFields, self::crm_company);
	}

	public static function GetCompanyUpdateFields()
	{
		return self::GetGlobal(self::crm_company);
	}

	private static function CheckFields($arFields, $options)
	{
		if(is_array($options['primary'])) self::CheckPrimaryFields($arFields, $options);
		if(is_array($options['crm_fm'])) self::CheckCrmFmFields($arFields, $options);
	}

	private static function CheckPrimaryFields($arFields, $options)
	{
		$result = array();

		global $YBWS;
		if($YBWS[$options['global_var']]) $result = $YBWS[$options['global_var']];

		foreach($options['primary'] as $table=>$fields)
		{
			$found = array();
			if($arFields['ID']>0)
			{
				foreach($fields as $field)
				{
					if(array_key_exists($field, $arFields))
					{
						$found[$field] = array();
					}
				}

				if(count($found)>0)
				{
					global $DB;
					$rows = $DB->Query('SELECT '.implode(",", array_keys($found)).' FROM '.$table.' WHERE ID='.$arFields['ID']);
					if($row=$rows->fetch())
					{
						foreach($found as $field=>$val)
						{
							if($row[$field]!=$arFields[$field])
							{
								$result[$field]['old']=$row[$field];
								$result[$field]['new']=$arFields[$field];
							}
						}
					}
				}
			}
		}
		$YBWS[$options['global_var']] = $result;
	}

	private static function CheckCrmFmFields($arFields, $options)
	{
		$result = array();
		global $YBWS;
		if($YBWS[$options['global_var']]) $result = $YBWS[$options['global_var']];
		if($arFields['ID']>0)
		{
			if(array_key_exists('FM', $arFields))
			{
				global $DB;
				$fm = array();
				$rows = $DB->Query('SELECT * FROM b_crm_field_multi WHERE ENTITY_ID="'.$options['crm_fm']['entity'].'" AND ELEMENT_ID='.$arFields['ID']);
				while($row=$rows->fetch())
				{
					$fm[$row['TYPE_ID']][$row['ID']] = $row;
				}
				foreach($arFields['FM'] as $type=>$values)
				{
					foreach($values as $key=>$value)
					{
						if($fm[$type][$key])
						{
							if($value['VALUE']!=$fm[$type][$key]['VALUE'])
							{
								$result['FM'][$type][$key]['old']=$fm[$type][$key]['VALUE'];
								$result['FM'][$type][$key]['new']=$value['VALUE'];
							}
						}
						else
						{
							if($value['VALUE']!="")	$result['FM'][$type][$key]=array('old'=>'', 'new'=>$value['VALUE']);
						}
					}
				}
			}
		}

		$YBWS[$options['global_var']] = $result;
	}

	//helper functions...
	private static function GetProductTopSection($product)
	{
		CModule::IncludeModule("iblock");
		$result = 0;
		$sections_tree = self::GetProductSectionTree();
		$rows = CIBlockElement::GetByID($product);
		if($row = $rows->GetNext())
		{
			if($row['IBLOCK_SECTION_ID']>0)
			{
				$result = $row['IBLOCK_SECTION_ID'];
				$parent = $result;
				while($parent>0)
				{
					if($sections_tree[$parent])
					{
						if($sections_tree[$parent]['PARENT']>0)
						{
							$parent = $sections_tree[$parent]['PARENT'];
							$result = $parent;
						}
						else
						{
							$parent=0;
						}
					}
					else
					{
						$parent=0;
					}
				}
			}
		}
		return $result;
	}

	private static function GetProductSectionTree()
	{
		$result = array();
		$cache = \Bitrix\Main\Data\Cache::createInstance();
		if ($cache->initCache(3600, 'ybws_product_section_tree', false))
		{
			$result = $cache->getVars();
		}
		elseif ($cache->startDataCache())
		{
			$result = array();
			CModule::IncludeModule('iblock');
			$rows = CIBlockSection::GetTreeList(array('IBLOCK_ID'=>GS_IBLOCK['PRODUCTS']), array());
			while($row = $rows->GetNext()) 
			{
			
				if(!$result[$row['ID']])
				{
					$result[$row['ID']] = array('ID'=>$row['ID'], 'CHILDS'=>array(), 'PARENT'=>0);
				}
				$parent = $row['IBLOCK_SECTION_ID'];
				if($parent>0)
				{
					$result[$row['ID']]['PARENT']=$parent;
					if(!$result[$parent])
					{
						$result[$parent] = array('ID'=>$parent, 'CHILDS'=>array(), 'PARENT'=>0);
					}
					$result[$parent]['CHILDS'][] = $row['ID'];
				}
			}
			$cache->endDataCache($result);
		}
		return $result; 
	}

	//Avivi task JJ24-450  получить список городов из ссписка товаров
	private static function GetProductCityIds($arFields)
	{
		CModule::IncludeModule("iblock");
		$productIDs = [];
		$arCities=[];
		foreach($arFields as $product)
		{
			$productIDs[] = $product['PRODUCT_ID'];
		}
		$objProduct = CIBlockElement::GetList([], [ 'IBLOCK_ID' => GS_IBLOCK['PRODUCTS'], 'ID'=>$productIDs], false, false, ['IBLOCK_ID','ID','PROPERTY_UF_CITY']);
		while ($arItem = $objProduct->Fetch() ) 
		{
			if($arItem['PROPERTY_UF_CITY_VALUE']){
				$arCities[$arItem['PROPERTY_UF_CITY_VALUE']] = $arItem['PROPERTY_UF_CITY_VALUE'];
			}
		}
		return $arCities;
	}

}

?>