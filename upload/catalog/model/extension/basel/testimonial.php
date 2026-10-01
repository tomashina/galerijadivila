<?php
class ModelExtensionBaselTestimonial extends Model {
	
	public function getTestimonials($limit = 3) {
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "testimonial t WHERE t.status = '1' ORDER BY RAND() LIMIT " . (int)$limit);

		return $query->rows;
	}
	
}