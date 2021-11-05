<?
class GsFieldsSync
{
	//Настройки отслеживаемых полей:

	private const crm_lead = array(
		'global_var'=>'CRM_LEAD_UPDATE_FIELDS',
		'primary'=>array(
			'b_crm_lead'=>array('ASSIGNED_BY_ID', 'STATUS_ID')
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


}

?>