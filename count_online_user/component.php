<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

	$arResult = array();
	if (CModule::IncludeModule("im"))
	{
		$res = CIMContactList::GetStatus();
		$arResult['ONLINE_USERS'] = $res['users'];
	}
	
$this->IncludeComponentTemplate();
?>
