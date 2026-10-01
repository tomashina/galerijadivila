<?php

//  Live Price / Живая цена
//  Support: support@liveopencart.com / Поддержка: help@liveopencart.ru

$_['module_name'] = 'Live Price';
$_['module_page'] = '<a href="https://www.opencart.com/index.php?route=marketplace/extension/info&extension_id=31581" target="_blank">'.$_['module_name'].' on opencart.com</a>';

$_lang_file = __DIR__.'/liveprice_common.php';
if ( file_exists($_lang_file) ) {
	require($_lang_file);
}
