<?
CModule::IncludeModule("disk");

foreach($arResult['GRID']['ROWS'] as $key=>$val)
{
	//if($val['attrs']['data-is-folder'])
	//{
		$object=$val['object'];
		$realobject = $object->getRealObject();
		$parent = $realobject->getParent();
		if($parent)
		{
			$name = $parent->getName();
			$arResult['TILE_ITEMS'][$key]['name']=$name.'/ '.$arResult['TILE_ITEMS'][$key]['name'];

			$fullpath = $name;
			while ($parent = $parent->getParent())
			{
				$name = $parent->getName();
				$fullpath = $name.'/'.$fullpath;
			}
			$arResult['GRID']['ROWS'][$key]['columns']['NAME']='<span class="path">'.$fullpath.'</span> '.$arResult['GRID']['ROWS'][$key]['columns']['NAME'];

		}
	//}

}
?>


