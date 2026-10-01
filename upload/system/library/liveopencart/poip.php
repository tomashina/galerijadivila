<?php
//  Product Option Image PRO / Изображения опций PRO
//  Support: support@liveopencart.com / Поддержка: help@liveopencart.ru

// for comp with old adaptations to themes

namespace liveopencart;
class poip {
	
	public static function initLibrary($registry) {
		
		\liveopencart\ext\poip::getInstance($registry);
		
	}
	
	public static function getLibrary($registry) {
		
		\liveopencart\ext\poip::getInstance($registry);
		return $registry->get('liveopencart_ext_poip');
	
	}
	
}