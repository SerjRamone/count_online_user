<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arResult = array();
$session_timeout = $arParams['SESSION_TIMEOUT']; 

//$file - Имя файла с сессией
//$path_sess - Путь до папки с сессиями
//$file_sess - Полный путь до файла с сессией
//$str_file - Строка, содержащая данные сессии
//$str - В конечном итоге - время бездействия пользователя
//$str_id - уникальное id сессии 
//$str_ip - ip посетителя
//$user_id - id пользователя, если он авторизирован

if (COption::GetOptionString("security", "session") == "Y")
  {
    // Сессии хранятся в бд
    if (CModule::IncludeModule("security"))
     {
        $results = $DB->Query("SELECT ALL `SESSION_ID`, `SESSION_DATA` FROM `b_sec_session`");

        while ($row = $results->Fetch())
          {		
             $str_file = CSecuritySession::read($row["SESSION_ID"]);
			 			 
			  // Проверяем не истекло ли время сессии
              if (strpos($str_file, "SESS_TIME|i:") !== false)
                {
                  $str = explode("SESS_TIME|i:", $str_file);
                  $str = explode(";", $str[1]);
                  $str = $str[0];
                  $str = (int)(getmicrotime() - $str);
				  
                  if ($str <= $session_timeout) 
                    {					
                      // Выделяем ip постетителя
                      $str_ip = explode('SESS_IP|s:', $str_file);
                      $str_ip = explode(':"', $str_ip[1]);
                      $str_ip = $str_ip[1];
                      $str_ip = explode('";', $str_ip);
                      $str_ip = $str_ip[0];
					  
					  if ($str_ip != "")
					    {

                           // Выделяем уникальное id сессии
                           $str_id = explode('fixed_session_id|s:32:"', $str_file);
                           $str_id = explode('"', $str_id[1]);
                           $str_id = $str_id[0];
					  
					       //Определяем авторизирован ли пользователь
				           if (strpos($str_file, 'USER_ID";') !== false)
				              {
                                $user_id = explode('USER_ID";', $str_file);
                                $user_id = explode('"', $user_id[1]);
				                $user_id = $user_id[1];
						   
						        // Проверяем есть ли этот пользователь уже в результирующем массиве
						        $l = 0;
						        foreach($arResult["USERS_AUTH"] as $val)
                                 {
							       if ($val["ID"] == $user_id) $l = 1;
						         }
						        if ($l == 0)
						         {
						           $arResult["USERS_AUTH"][] = array("ID" => $user_id, "fixed_session_id" => $str_id, "SESS_IP" => $str_ip);
							     }
					          }
					       else 
					        {
						      $arResult["USERS"][] = array("fixed_session_id" => $str_id, "SESS_IP" => $str_ip);
						    }
						}
                    }
                }	
		  }
     }
  }
else
  {
    // Сессии хранятся в файлах
    $path_sess = session_save_path();
    $dir=opendir($path_sess);
    while(false !== ($file = readdir($dir)))
      {
        if (strpos($file, "sess_") !== false)
           {
              $file_sess = fopen($path_sess."/".$file,"r");
              $str_file = fread($file_sess, filesize($path_sess."/".$file));

              // Проверяем не истекло ли время сессии
              if (strpos($str_file, "SESS_TIME|i:") !== false)
                {
                  $str = explode("SESS_TIME|i:", $str_file);
                  $str = explode(";", $str[1]);
                  $str = $str[0];
                  $str = (int)(getmicrotime() - $str);
				  
                  if ($str <= $session_timeout) 
                    {					
                      // Выделяем ip постетителя
                      $str_ip = explode('SESS_IP|s:', $str_file);
                      $str_ip = explode(':"', $str_ip[1]);
                      $str_ip = $str_ip[1];
                      $str_ip = explode('";', $str_ip);
                      $str_ip = $str_ip[0];
					  
					  if ($str_ip != "")
					    {
                           // Выделяем уникальное id сессии
                           $str_id = explode('fixed_session_id|s:32:"', $str_file);
                           $str_id = explode('"', $str_id[1]);
                           $str_id = $str_id[0];
					  
					       //Определяем авторизирован ли пользователь
				           if (strpos($str_file, 'USER_ID";') !== false)
				              {
                                $user_id = explode('USER_ID";', $str_file);
                                $user_id = explode('"', $user_id[1]);
				                $user_id = $user_id[1];
						   
						        // Проверяем есть ли этот пользователь уже в результирующем массиве
						        $l = 0;
						        foreach($arResult["USERS_AUTH"] as $val)
                                 {
							       if ($val["ID"] == $user_id) $l = 1;
						         }
						        if ($l == 0)
						         {
						           $arResult["USERS_AUTH"][] = array("ID" => $user_id, "fixed_session_id" => $str_id, "SESS_IP" => $str_ip);
							     }
					          }
					       else 
					        {
						      $arResult["USERS"][] = array("fixed_session_id" => $str_id, "SESS_IP" => $str_ip);
						    }
						}
                    }
                }
              fclose($file_sess);
           }
      } 
    closedir($dir);
  }
  
// Оставляем только уникальные ip, если выбран такой способ уникализации посетителей  
if ($arParams['TYPE_UNIQUENESS'] == "IP")
  {
   $arResult_copy = $arResult;
   $arResult = array();	 
	 
	// Оставляем уникальных гостей  
    foreach($arResult_copy["USERS"] as $val)
      {
	     $l = 0;
	     foreach($arResult_copy["USERS"] as $val_1)
          {
		    if (($val["SESS_IP"] == $val_1["SESS_IP"]) and ($val["fixed_session_id"] != $val_1["fixed_session_id"])) $l = 1;
		  }
		 if ($l == 0) $arResult["USERS"][] = $val;
	  }
	  
	// Оставляем уникальных посетителей  
    foreach($arResult_copy["USERS_AUTH"] as $val)
      {
	     $l = 0;
	     foreach($arResult_copy["USERS_AUTH"] as $val_1)
          {
		    if (($val["SESS_IP"] == $val_1["SESS_IP"]) and ($val["fixed_session_id"] != $val_1["fixed_session_id"])) $l = 1;
		  }
		 if ($l == 0) $arResult["USERS_AUTH"][] = $val;
	  }

	$arResult_copy = $arResult;  
    $arResult = array();
	
	// Если посетитель авторизован то удаляем все сессии с этого айпи из массива с гостями
    foreach($arResult_copy["USERS"] as $val)
      {
	     $l = 0;
	     foreach($arResult_copy["USERS_AUTH"] as $val_1)
          {
		    if ($val["SESS_IP"] == $val_1["SESS_IP"]) $l = 1;
		  }
		 if ($l == 0) $arResult["USERS"][] = $val;
	  }
    foreach($arResult_copy["USERS_AUTH"] as $val)
      {
        $arResult["USERS_AUTH"][] = $val;
	  }
  
  }

//echo "<pre>"; print_r($arResult); echo "</pre>";

$this->IncludeComponentTemplate();

?>