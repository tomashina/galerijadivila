<?php

//  Live Price / Живая цена
//  Support: support@liveopencart.com / Поддержка: help@liveopencart.ru

$_['module_name'] = 'Живая цена';
$_['module_page'] = '
<a href="https://liveopencart.ru/opencart-moduli-shablony/moduli/opcii/jivaya-tsena-dinamicheskoe-obnovlenie-tsenyi-3" target="_blank">'.$_['module_name'].' на liveopencart.ru</a>
';

$_lang_file = __DIR__.'/liveprice_common.php';
if ( file_exists($_lang_file) ) {
	require($_lang_file);
}
