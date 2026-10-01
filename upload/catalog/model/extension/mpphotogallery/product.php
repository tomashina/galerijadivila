<?php
class ModelExtensionMpPhotoGalleryProduct extends \mpphotogallery\Modelcatalog {

	// 07-05-2022: updation task start
	public function getProductMpGalleries($product_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mpgallery_product` WHERE product_id = '" . (int)$product_id . "'");
		return $query->rows;
	}
	// 07-05-2022: updation task end
}