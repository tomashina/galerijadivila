<?php
class ModelExtensionMpPhotoGalleryPhoto extends \mpphotogallery\Modelcatalog {
	public function getPhoto($mpgallery_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mpgallery g LEFT JOIN " . DB_PREFIX . "mpgallery_description gd ON (g.mpgallery_id = gd.mpgallery_id) WHERE g.mpgallery_id = '" . (int)$mpgallery_id . "' AND gd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND g.status = '1'");

		if ($query->num_rows) {
			return array(
				'mpgallery_id'       => $query->row['mpgallery_id'],
				'image'            => $query->row['image'],
				'status'           => $query->row['status'],
				'description'      => $query->row['description'],
				'sort_order'       => $query->row['sort_order'],
				'date_modified'    => $query->row['date_modified'],
				'language_id'      => $query->row['language_id'],
				'title'            => $query->row['title'],
				'date_added'       => $query->row['date_added']
			);
		} else {
			return false;
		}
	}

	public function getPhotoDescription($mpgallery_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mpgallery_photo gp LEFT JOIN " . DB_PREFIX . "mpgallery_photo_description gpd ON (gp.mpgallery_photo_id = gpd.mpgallery_photo_id) WHERE gp.mpgallery_id = '" . (int)$mpgallery_id . "' AND gpd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY gp.sort_order ASC");
		return $query->rows;
	}

	public function getAlbumPhotoDescription($mpgallery_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mpgallery_photo gp LEFT JOIN " . DB_PREFIX . "mpgallery_photo_description gpd ON (gp.mpgallery_photo_id = gpd.mpgallery_photo_id) WHERE gp.mpgallery_id = '" . (int)$mpgallery_id . "' AND gpd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY gp.highlight DESC, gp.sort_order ASC");

		return $query->rows;
	}

	public function getPhotosByMpGallery($mpgallery_id, $limit = '') {
		$start = 0;

		$sql = "SELECT * FROM " . DB_PREFIX . "mpgallery_photo gp LEFT JOIN " . DB_PREFIX . "mpgallery_photo_description gpd ON (gp.mpgallery_photo_id = gpd.mpgallery_photo_id) WHERE gp.mpgallery_id = '" . (int)$mpgallery_id . "' AND gpd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY gp.sort_order ASC";

		if ($limit) {
			$sql .= " LIMIT " . (int)$start . "," . (int)$limit;
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getMpGalleryPhotos($mpgallery_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mpgallery_photo gp WHERE gp.mpgallery_id = '" . (int)$mpgallery_id . "' ORDER BY gp.sort_order ASC");

		return $query->rows;
	}

	public function getMpGallery($data = array()) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "mpgallery` g LEFT JOIN " . DB_PREFIX . "mpgallery_description gd ON (g.mpgallery_id = gd.mpgallery_id) LEFT JOIN " . DB_PREFIX . "mpgallery_photo gp ON (g.mpgallery_id = gp.mpgallery_id) WHERE g.mpgallery_id = gp.mpgallery_id AND gd.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY gp.mpgallery_id ORDER BY g.sort_order ASC";

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

		$sql = "SELECT COUNT(DISTINCT g.mpgallery_id) AS total FROM " . DB_PREFIX . "mpgallery g LEFT JOIN " . DB_PREFIX . "mpgallery_description gd ON (g.mpgallery_id = gd.mpgallery_id) LEFT JOIN " . DB_PREFIX . "mpgallery_photo gp ON (g.mpgallery_id = gp.mpgallery_id) WHERE g.mpgallery_id = gp.mpgallery_id AND gd.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY gp.mpgallery_id";

			$query = $this->db->query($sql);

		return $query->num_rows;
	}
}