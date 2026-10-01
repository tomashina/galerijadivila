<?php
/*
 * IT IS NOT FREE, YOU SHOULD BUY / REFISTER A LICENSE AT HTTPS://MMOSolution.COM
 * CONTACT: toan@MMOSOLUTION.COM 
 * AUTHOR: MMOSOLUTION TEAM AT VIETNAM
 * All code within this file is copyright MMOSOLUTION.COM TEAM | FOUNDED @2012
 * You can not copy or reuse code within this file without written permission.
*/

 class ModelExtensionModuleMmosAttachManager extends Model {
	public function getDownloads($product_attach_file_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_attach_file WHERE product_attach_file_id = '" . (int)$product_attach_file_id . "'");
		 
		return $query->row;
	}
	
	public function getDownloadsUpdate($product_attach_file_id, $count) {
		$query = $this->db->query("UPDATE " . DB_PREFIX . "product_attach_file SET download = '".(int)$count."' WHERE product_attach_file_id = '" . (int)$product_attach_file_id . "' ");

	}


}
?>