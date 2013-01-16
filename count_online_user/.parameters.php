<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
	
$arComponentParameters = array(

   "PARAMETERS" => array(
   
      "TYPE_UNIQUENESS" => array(
	     "PARENT" => "BASE",
         "NAME" => GetMessage("TYPE_UNIQUENESS"),
         "TYPE" => "STRING",
		 "DEFAULT" => "SESSION",
         "REFRESH" => "Y",
         "COLS" => "15"
      ),
      "SESSION_TIMEOUT" => array(
	     "PARENT" => "BASE",
         "NAME" => GetMessage("SESSION_TIMEOUT"),
         "TYPE" => "STRING",
		 "DEFAULT" => "120",
         "REFRESH" => "Y",
         "COLS" => "15"
      ),
      )
);

?>