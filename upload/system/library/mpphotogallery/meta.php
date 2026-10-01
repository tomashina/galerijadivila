<?php
namespace mpphotogallery;
/**
 * @package		Modulepoints
 * @author		Punit Tejpal
 * @copyright	Copyright (c) 2016 - 2022, Modulepoints, Ltd. (https://www.modulepoints.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.modulepoints.com
 * @opencart_partner		https://www.opencart.com/index.php?route=support/partner/info&partner_id=398558
*/

/**
* Meta class
*/
class Meta {

	private $meta = [];
	public function setMeta($content) {
		/*
		$meta['attribute']
		$meta['attribute_value']
		$meta['content']
		*/
		$this->meta[] = $content;
	}
	public function getMeta() {
		return $this->meta;
	}
}
