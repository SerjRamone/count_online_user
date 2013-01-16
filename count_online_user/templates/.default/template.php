<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="online_user_wrapper">
	<b class="r2"></b><b class="r1"></b><b class="r0"></b>
	<div class="online_user_inner">
		<div class="online_user_content">
			<span class="online_user_content_bolder">Сегодня:</span> <?=date('d.m.Y')?>
			<br />
			<span class="online_user_content_bolder">Пользователей онлайн:</span> <?=count($arResult["ONLINE_USERS"]);?>
		</div>
	</div>
	<i class="r0"></i><i class="r1"></i><i class="r2"></i>
</div>