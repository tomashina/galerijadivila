<?php
class ModelExtensionMpPhotoGalleryAlbum extends \mpphotogallery\Modelcatalog {
	public function updateViewed($mpgallery_id) {
		$this->db->query("UPDATE " . DB_PREFIX . "mpgallery SET viewed = (viewed + 1) WHERE mpgallery_id = '" . (int)$mpgallery_id . "'");
	}

	public function getMpGalleryinfo($mpgallery_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mpgallery` g LEFT JOIN " . DB_PREFIX . "mpgallery_description gd ON (g.mpgallery_id = gd.mpgallery_id) WHERE g.mpgallery_id = '" . (int)$mpgallery_id . "' AND gd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getMpGallery($data = array()) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "mpgallery` g LEFT JOIN " . DB_PREFIX . "mpgallery_description gd ON (g.mpgallery_id = gd.mpgallery_id) WHERE gd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY g.date_added DESC, g.sort_order ASC";

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 4;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

			$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTotalMpGalleries($data = array()) {

		$sql = "SELECT COUNT(DISTINCT g.mpgallery_id) AS total FROM " . DB_PREFIX . "mpgallery g LEFT JOIN " . DB_PREFIX . "mpgallery_description gd ON (g.mpgallery_id = gd.mpgallery_id)";

			$sql .= " WHERE gd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

			$query = $this->db->query($sql);

		return $query->row['total'];

	}

	public function getTotalMpGalleryPhotos($mpgallery_id) {
		$query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "mpgallery_photo WHERE mpgallery_id = '" . (int)$mpgallery_id . "' ORDER BY sort_order ASC");

		return $query->row['total'];
	}
}