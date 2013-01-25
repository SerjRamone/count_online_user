count_online_user
=================
Компонент выводит количество пользователей Online и текущую дату.
Для работы компонента необходим установленный модуль "Веб мессенджер (im)"

**Использование:**
```php
	$APPLICATION->IncludeComponent(
		"fusion:count_online_user", //fusion - directory-namespace for component
		"",
		Array(),
	false
	);
```


**Компонент вывода списка пользователей онлайн:**
```php
	$APPLICATION->IncludeComponent(
		"fusion:count_online_user_list",
		"",
		Array(
		),
	false
);
```
