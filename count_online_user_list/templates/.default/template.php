<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="online_user_wrapper">
	<b class="r2"></b><b class="r1"></b><b class="r0"></b>
	<div class="online_user_inner">
		<div class="online_user_content">
			<?foreach ($arResult as $key => $arUser) {?>
				<span class="online_user_content_bolder">Пользователей онлайн: (<?=$key?>)</span> <?=print_r($arUser,1);?><?='<br>'?>
			<?}?>
		</div>
	</div>
	<i class="r0"></i><i class="r1"></i><i class="r2"></i>
</div>