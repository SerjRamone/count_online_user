<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule('intranet')) return;
$bSoNet = CModule::IncludeModule('socialnetwork');

$arParams['USERS_PER_PAGE'] = intval($arParams['USERS_PER_PAGE']);

$arParams['NAV_TITLE'] = $arParams['NAV_TITLE'] ? $arParams['NAV_TITLE'] : GetMessage('INTR_ISL_PARAM_NAV_TITLE_DEFAULT');

$arParams['DATE_FORMAT'] = $arParams['DATE_FORMAT'] ? $arParams['DATE_FORMAT'] : CComponentUtil::GetDateFormatDefault(false);
$arParams['DATE_FORMAT_NO_YEAR'] = $arParams['DATE_FORMAT_NO_YEAR'] ? $arParams['DATE_FORMAT_NO_YEAR'] : CComponentUtil::GetDateFormatDefault(true);

InitBVar($arParams['SHOW_NAV_TOP']);
InitBVar($arParams['SHOW_NAV_BOTTOM']);

InitBVar($arParams['SHOW_UNFILTERED_LIST']);

$arParams['DETAIL_URL'] = COption::GetOptionString('intranet', 'search_user_url', '/user/#ID#/');

if (!array_key_exists("PM_URL", $arParams))
	$arParams["~PM_URL"] = $arParams["PM_URL"] = "/company/personal/messages/chat/#USER_ID#/";

if (!array_key_exists("PATH_TO_USER_EDIT", $arParams))
	$arParams["~PATH_TO_USER_EDIT"] = $arParams["PATH_TO_USER_EDIT"] = '/company/personal/user/#user_id#/edit/';

if (!array_key_exists("PATH_TO_CONPANY_DEPARTMENT", $arParams))
	$arParams["~PATH_TO_CONPANY_DEPARTMENT"] = $arParams["PATH_TO_CONPANY_DEPARTMENT"] = "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#";

if (IsModuleInstalled("video") && !array_key_exists("PATH_TO_VIDEO_CALL", $arParams))
	$arParams["~PATH_TO_VIDEO_CALL"] = $arParams["PATH_TO_VIDEO_CALL"] = "/company/personal/video/#USER_ID#/";

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600;

if ($arParams['CACHE_TYPE'] == 'A')
	$arParams['CACHE_TYPE'] = COption::GetOptionString("main", "component_cache_on", "Y");

$bNav = $arParams['SHOW_NAV_TOP'] == 'Y' || $arParams['SHOW_NAV_BOTTOM'] == 'Y';

$bDesignMode = $GLOBALS["APPLICATION"]->GetShowIncludeAreas() && is_object($GLOBALS["USER"]) && $GLOBALS["USER"]->IsAdmin();

// prepare list filter
$arFilter = array();
global $USER;

if (!$USER->CanDoOperation("edit_all_users") && isset($arParams["SHOW_USER"]) && $arParams["SHOW_USER"] != "fired")
	$arParams["SHOW_USER"] = "active";

$arFilter = array('ACTIVE' => 'Y');

$arResult['FILTER_VALUES'] = $arFilter;

if (CModule::IncludeModule("im"))
{
	$res = CIMContactList::GetStatus();
	$arFilter['ID'] = implode(' | ', array_keys($res['users']));
}

// get users list
$obUser = new CUser();

$arSelect = array('ID', 'ACTIVE', 'DEP_HEAD', 'GROUP_ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN', 'EMAIL', 'LID', 'DATE_REGISTER',  'PERSONAL_PROFESSION', 'PERSONAL_WWW', 'PERSONAL_ICQ', 'PERSONAL_GENDER', 'PERSONAL_BIRTHDATE', 'PERSONAL_PHOTO', 'PERSONAL_PHONE', 'PERSONAL_FAX', 'PERSONAL_MOBILE', 'PERSONAL_PAGER', 'PERSONAL_STREET', 'PERSONAL_MAILBOX', 'PERSONAL_CITY', 'PERSONAL_STATE', 'PERSONAL_ZIP', 'PERSONAL_COUNTRY', 'PERSONAL_NOTES', 'WORK_COMPANY', 'WORK_DEPARTMENT', 'WORK_POSITION', 'WORK_WWW', 'WORK_PHONE', 'WORK_FAX', 'WORK_PAGER', 'WORK_STREET', 'WORK_MAILBOX', 'WORK_CITY', 'WORK_STATE', 'WORK_ZIP', 'WORK_COUNTRY', 'WORK_PROFILE', 'WORK_LOGO', 'WORK_NOTES', 'PERSONAL_BIRTHDAY', 'LAST_ACTIVITY_DATE');

$arResult['USERS'] = array();
$arResult['DEPARTMENTS'] = array();
$arResult['DEPARTMENT_HEAD'] = 0;


$arListParams = array('SELECT' => array('UF_*'));
if ($arParams['USERS_PER_PAGE'] > 0)
	$arListParams['NAV_PARAMS'] = array('nPageSize' => $arParams['USERS_PER_PAGE'], 'bShowAll' => false);

$dbUsers = $obUser->GetList(
	($sort_by = 'last_name'), ($sort_dir = 'asc'),
	$arFilter,
	$arListParams
);

$arDepartments = array();
$strUserIDs = '';
while ($arUser = $dbUsers->Fetch())
{
	$arResult['USERS'][$arUser['ID']] = $arUser;
	$strUserIDs .= ($strUserIDs == '' ? '' : '|').$arUser['ID'];
}

foreach ($arResult['USERS'] as $key => $arUser)
{
	$arUser['IS_FEATURED'] = CIntranetUtils::IsUserHonoured($arUser['ID']);

	$arResult['USERS'][$key] = $arUser;
}

if (count($arResult['USERS']) > 0)
{
	$dbRes = CIBlockSection::GetList(
		array("left_margin"=>"asc"),
		array(
			'IBLOCK_ID' => COption::GetOptionInt('intranet', 'iblock_structure'),
			//'ID' => array_unique($arDepartments)
		),
		array('ID', 'NAME', 'SECTION_PAGE_URL', 'DEPTH_LEVEL', 'IBLOCK_SECTION_ID','UF_HEAD')
	);

	while ($arSect = $dbRes->Fetch())
	{
		$arSect['USERS'] = array();
		//$arDepartments[$arSect['ID']] = $arSect['NAME'];
		$arResult['DEPARTMENTS'][$arSect['ID']] = $arSect;
	}
}

$arResult["USERS_NAV"] = $bNav ? $dbUsers->GetPageNavStringEx($navComponentObject=null, $arParams["NAV_TITLE"]) : '';


$ptime = getmicrotime();
$timeLimitResize = 5;
foreach ($arResult['USERS'] as $arUser)
{
	$arDep = array();
	if (is_array($arUser['UF_DEPARTMENT']))
	{
		foreach ($arUser['UF_DEPARTMENT'] as $key => $sect)
		{
			$arDep[$sect] = $arResult['DEPARTMENTS'][$sect]['NAME'];
		}
	}

	$arUser['UF_DEPARTMENT'] = $arDep;

	if ($arParams['DETAIL_URL'])
		$arUser['DETAIL_URL'] = str_replace(array('#ID#', '#USER_ID#'), $arUser['ID'], $arParams['DETAIL_URL']);

	if (!$arUser['PERSONAL_PHOTO'])
	{
		switch ($arUser['PERSONAL_GENDER'])
		{
			case "M":
				$suffix = "male";
				break;
			case "F":
				$suffix = "female";
				break;
			default:
				$suffix = "unknown";
		}
		$arUser['PERSONAL_PHOTO'] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, SITE_ID);
	}

	if($arUser['PERSONAL_PHOTO'])
	{
		$arUser['PERSONAL_PHOTO_SOURCE'] = $arUser['PERSONAL_PHOTO'];
		if ($bExcel)
		{
			$arUser['PERSONAL_PHOTO'] = CFile::GetPath($arUser['PERSONAL_PHOTO']);
		}
		else
		{
			if (round(getmicrotime()-$ptime, 3)>$timeLimitResize)
			{
				$arUser['PERSONAL_PHOTO'] = CFile::ShowImage($arUser['PERSONAL_PHOTO'], 9999, 100);
			}
			else
			{
				$arImage = CIntranetUtils::InitImage($arUser['PERSONAL_PHOTO'], 100);
				$arUser['PERSONAL_PHOTO'] = $arImage['IMG'];
			}
		}
	}

	//$arUser['IS_ONLINE'] = CIntranetUtils::IsOnline($arUser['LAST_ACTIVITY_DATE'], 120);
	$arUser['IS_ONLINE'] = true; //filtered only online users


	$arUser['IS_BIRTHDAY'] = CIntranetUtils::IsToday($arUser['PERSONAL_BIRTHDAY']);
	$arUser['IS_ABSENT'] = CIntranetUtils::IsUserAbsent($arUser['ID']);

	$arResult['USERS'][$arUser['ID']] = $arUser;
}

$arResult['bAdmin'] = $USER->CanDoOperation('edit_all_users') || $USER->CanDoOperation('edit_subordinate_users');

$this->IncludeComponentTemplate();

?>