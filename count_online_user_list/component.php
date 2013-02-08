<?
/**
 * Sergey Greznov, Fusion LLC
 *
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

	$arResult = array();

	$arResult['PARAMS'] = array (
		'USER_PROPERTY' => array (
			'FULL_NAME',
			'PERSONAL_BIRTHDAY',
			'PERSONAL_POSITION',
			'WORK_PHONE',
			'UF_DEPARTMENT'
		),
		'PM_URL' => '/company/personal/messages/chat/#USER_ID#/',
		'STRUCTURE_PAGE' => 'structure.php',
		'STRUCTURE_FILTER' => 'structure',
		'USER_PROP' => '',
		'NAME_TEMPLATE' => '#LAST_NAME# #NAME# #SECOND_NAME#',
		'SHOW_LOGIN' => 'Y',
		'DATE_FORMAT' => 'd.m.Y',
		'DATE_FORMAT_NO_YEAR' => 'd.m.Y',
		'DATE_TIME_FORMAT' => 'd.m.Y H:i:s',
		'SHOW_YEAR' => 'M',
		'CACHE_TYPE' => 'Y',
		'CACHE_TIME' => '0',
		'PATH_TO_CONPANY_DEPARTMENT' => '/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#',
		'PATH_TO_VIDEO_CALL' => '/company/personal/video/#USER_ID#/',
	);

	if (CModule::IncludeModule("im"))
	{
		$res = CIMContactList::GetStatus();

		$arFilter = array (
			'ID' 		=> implode(' | ', array_keys($res['users'])),
			'ACTIVE' 	=> 'Y',
		);

		$rsUsers = CUser::GetList(($by = "id"), ($order = "asc"), $arFilter, array('SELECT' => array()));

		$rsUsers->NavStart(25);
		$arResult['NAV'] = $rsUsers->GetPageNavString();

		while ($arUser = $rsUsers->Fetch())
		{
			if ($arUser['PERSONAL_PHOTO'] != '')
			{
				//выводим  картинку в профиле
				$arImage = CIntranetUtils::InitImage($arUser['PERSONAL_PHOTO'], 100);
				$arUser['PERSONAL_PHOTO'] = $arImage['IMG'];
			}
			$arUser['DETAIL_URL'] = '/company/personal/user/'.$arUser['ID'].'/';
			$arUser['~DETAIL_URL'] = '/company/personal/user/'.$arUser['ID'].'/';
			$arResult['USERS'][$arUser['ID']] = $arUser;
		}
	}

$this->IncludeComponentTemplate();
?>
