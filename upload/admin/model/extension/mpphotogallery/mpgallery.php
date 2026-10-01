<?php
class ModelExtensionMpPhotoGalleryMpGallery extends Model {
	public function addMpGallery($data) {

		$this->db->query("INSERT INTO `" . DB_PREFIX . "mpgallery` SET status = '" . $this->db->escape($data['status']) . "', sort_order = '" . (int)$data['sort_order'] . "', width = '" . (int)$data['width'] . "', height = '" . (int)$data['height'] . "', video_width = '" . (int)$data['video_width'] . "', video_height = '" . (int)$data['video_height'] . "', date_added = NOW()");

		$mpgallery_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "mpgallery SET image = '" . $this->db->escape($data['image']) . "' WHERE mpgallery_id = '" . (int)$mpgallery_id . "'");
		}

		foreach ($data['mpgallery_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "mpgallery_description SET mpgallery_id = '" . (int)$mpgallery_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', top_description = '" . $this->db->escape($value['top_description']) . "', bottom_description = '" . $this->db->escape($value['bottom_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "'");
		}

		if (isset($data['mpgallery_photo'])) {
			foreach ($data['mpgallery_photo'] as $mpgallery_photo) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "mpgallery_photo SET mpgallery_id = '" . (int)$mpgallery_id . "', photo = '" . $this->db->escape(html_entity_decode($mpgallery_photo['photo'],  ENT_QUOTES, 'UTF-8')) . "', link = '" . $this->db->escape($mpgallery_photo['link']) . "', sort_order = '" . (int)$mpgallery_photo['sort_order'] . "', highlight = '" . (isset($mpgallery_photo['highlight']) ? (int)$mpgallery_photo['highlight'] : '') . "'");

				$mpgallery_photo_id = $this->db->getLastId();

				foreach ($mpgallery_photo['mpgallery_photo_description'] as $language_id => $mpgallery_photo_description) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "mpgallery_photo_description SET mpgallery_photo_id = '" . (int)$mpgallery_photo_id . "', language_id = '" . (int)$language_id . "', mpgallery_id = '" . (int)$mpgallery_id . "', name = '" . $this->db->escape($mpgallery_photo_description['name']) . "'");
				}
			}
		}
		// gallery for product task starts
		// 07-05-2022: updation task start
		if (isset($data['mpgallery_products'])) {
			foreach ($data['mpgallery_products'] as $mpgallery_product) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "mpgallery_product SET mpgallery_id = '" . (int)$mpgallery_id . "', product_id = '" . (int)$mpgallery_product['product_id'] . "', video = '" . (int)$mpgallery_product['video'] . "', image = '" . (int)$mpgallery_product['image'] . "'");
			}
		}
		// 07-05-2022: updation task end
		// gallery for product task ends

		if (isset($data['mpgallery_video'])) {
			foreach ($data['mpgallery_video'] as $mpgallery_video) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "mpgallery_video SET mpgallery_id = '" . (int)$mpgallery_id . "', video_thumb = '" . $this->db->escape(html_entity_decode($mpgallery_video['video_thumb'],  ENT_QUOTES, 'UTF-8')) . "', link = '" . $this->db->escape($mpgallery_video['link']) . "', sort_order = '" . (int)$mpgallery_video['sort_order'] . "', highlight = '" . (isset($mpgallery_video['highlight']) ? (int)$mpgallery_video['highlight'] : '') . "'");

				$mpgallery_video_id = $this->db->getLastId();

				foreach ($mpgallery_video['mpgallery_video_description'] as $language_id => $mpgallery_video_description) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "mpgallery_video_description SET mpgallery_video_id = '" . (int)$mpgallery_video_id . "', language_id = '" . (int)$language_id . "', mpgallery_id = '" . (int)$mpgallery_id . "', name = '" . $this->db->escape($mpgallery_video_description['name']) . "'");
				}
			}
		}

		return $mpgallery_id;
	}

	public function editMpGallery($mpgallery_id, $data) {
		$this->db->query("UPDATE `" . DB_PREFIX . "mpgallery` SET  image = '" . $this->db->escape(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8')) . "', status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "', width = '" . (int)$data['width'] . "', height = '" . (int)$data['height'] . "', video_width = '" . (int)$data['video_width'] . "', video_height = '" . (int)$data['video_height'] . "', date_modified = NOW() WHERE mpgallery_id = '" . (int)$mpgallery_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "mpgallery_description WHERE mpgallery_id = '" . (int)$mpgallery_id . "'");

		foreach ($data['mpgallery_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "mpgallery_description SET mpgallery_id = '" . (int)$mpgallery_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', top_description = '" . $this->db->escape($value['top_description']) . "', bottom_description = '" . $this->db->escape($value['bottom_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "mpgallery_photo WHERE mpgallery_id = '" . (int)$mpgallery_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "mpgallery_photo_description WHERE mpgallery_id = '" . (int)$mpgallery_id . "'");

		if (isset($data['mpgallery_photo'])) {
			foreach ($data['mpgallery_photo'] as $mpgallery_photo) {
				if ($mpgallery_photo['mpgallery_photo_id']) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "mpgallery_photo SET mpgallery_photo_id = '" . (int)$mpgallery_photo['mpgallery_photo_id'] . "', mpgallery_id = '" . (int)$mpgallery_id . "', photo = '" . $this->db->escape(html_entity_decode($mpgallery_photo['photo'], ENT_QUOTES, 'UTF-8')) . "', link = '" . $this->db->escape($mpgallery_photo['link']) . "', sort_order = '" . (int)$mpgallery_photo['sort_order'] . "', highlight = '" . (isset($mpgallery_photo['highlight']) ? (int)$mpgallery_photo['highlight'] : '') . "'");
				} else {
					$this->db->query("INSERT INTO " . DB_PREFIX . "mpgallery_photo SET mpgallery_id = '" . (int)$mpgallery_id . "', photo = '" . $this->db->escape(html_entity_decode($mpgallery_photo['photo'], ENT_QUOTES, 'UTF-8')) . "', link = '" . $this->db->escape($mpgallery_photo['link']) . "', sort_order = '" . (int)$mpgallery_photo['sort_order'] . "', highlight = '" . (isset($mpgallery_photo['highlight']) ? (int)$mpgallery_photo['highlight'] : '') . "'");
				}

				$mpgallery_photo_id = $this->db->getLastId();

				foreach ($mpgallery_photo['mpgallery_photo_description'] as $language_id => $mpgallery_photo_description) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "mpgallery_photo_description SET mpgallery_photo_id = '" . (int)$mpgallery_photo_id . "', language_id = '" . (int)$language_id . "', mpgallery_id = '" . (int)$mpgallery_id . "', name = '" . $this->db->escape($mpgallery_photo_description['name']) . "'");
				}
			}
		}
		// gallery for product task starts
		$this->db->query("DELETE FROM " . DB_PREFIX . "mpgallery_product WHERE mpgallery_id = '" . (int)$mpgallery_id . "'");
		// gallery for product task ends
		$this->db->query("DELETE FROM " . DB_PREFIX . "mpgallery_video WHERE mpgallery_id = '" . (int)$mpgallery_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "mpgallery_video_description WHERE mpgallery_id = '" . (int)$mpgallery_id . "'");

		// gallery for product task starts
		// 07-05-2022: updation task start
		if (isset($data['mpgallery_products'])) {
			foreach ($data['mpgallery_products'] as $mpgallery_product) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "mpgallery_product SET mpgallery_id = '" . (int)$mpgallery_id . "', product_id = '" . (int)$mpgallery_product['product_id'] . "', video = '" . (int)$mpgallery_product['video'] . "', image = '" . (int)$mpgallery_product['image'] . "'");
			}
		}
		// 07-05-2022: updation task end
		// gallery for product task ends

		if (isset($data['mpgallery_video'])) {
			foreach ($data['mpgallery_video'] as $mpgallery_video) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "mpgallery_video SET mpgallery_id = '" . (int)$mpgallery_id . "', video_thumb = '" . $this->db->escape(html_entity_decode($mpgallery_video['video_thumb'],  ENT_QUOTES, 'UTF-8')) . "', link = '" . $this->db->escape($mpgallery_video['link']) . "', sort_order = '" . (int)$mpgallery_video['sort_order'] . "', highlight = '" . (isset($mpgallery_video['highlight']) ? (int)$mpgallery_video['highlight'] : '') . "'");

				$mpgallery_video_id = $this->db->getLastId();

				foreach ($mpgallery_video['mpgallery_video_description'] as $language_id => $mpgallery_video_description) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "mpgallery_video_description SET mpgallery_video_id = '" . (int)$mpgallery_video_id . "', language_id = '" . (int)$language_id . "', mpgallery_id = '" . (int)$mpgallery_id . "', name = '" . $this->db->escape($mpgallery_video_description['name']) . "'");
				}
			}
		}

	}

	public function deleteMpGallery($mpgallery_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "mpgallery` WHERE mpgallery_id = '" . (int)$mpgallery_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "mpgallery_description WHERE mpgallery_id = '" . (int)$mpgallery_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "mpgallery_photo WHERE mpgallery_id = '" . (int)$mpgallery_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "mpgallery_photo_description WHERE mpgallery_id = '" . (int)$mpgallery_id . "'");
		// gallery for product task starts
		$this->db->query("DELETE FROM " . DB_PREFIX . "mpgallery_product WHERE mpgallery_id = '" . (int)$mpgallery_id . "'");
		// gallery for product task ends
		$this->db->query("DELETE FROM " . DB_PREFIX . "mpgallery_video WHERE mpgallery_id = '" . (int)$mpgallery_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "mpgallery_video_description WHERE mpgallery_id = '" . (int)$mpgallery_id . "'");
	}

	public function getMpGallery($mpgallery_id) {
		// 07-05-2022: updation task start
		$sql_keyword = "";
		if (VERSION < '3.0.0.0') {
			$sql_keyword = ", (SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = 'mpgallery_id=" . (int)$mpgallery_id . "') AS keyword ";
		}
		// 07-05-2022: updation task end
		$query = $this->db->query("SELECT DISTINCT * {$sql_keyword} FROM `" . DB_PREFIX . "mpgallery` g LEFT JOIN " . DB_PREFIX . "mpgallery_description gd ON (g.mpgallery_id = gd.mpgallery_id) WHERE g.mpgallery_id = '" . (int)$mpgallery_id . "' AND gd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getMpGalleries($data = array()) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "mpgallery` g LEFT JOIN " . DB_PREFIX . "mpgallery_description gd ON (g.mpgallery_id = gd.mpgallery_id) WHERE gd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND gd.title LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sort_data = array(
			'gd.title',
			'g.sort_order',
			'g.status',
			'g.viewed',
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY gd.title";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getMpGalleryDescriptions($mpgallery_id) {
		$mpgallery_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mpgallery_description WHERE mpgallery_id = '" . (int)$mpgallery_id . "'");

		foreach ($query->rows as $result) {
			$mpgallery_data[$result['language_id']] = array(
				'title' 			=> $result['title'],
				'description' 		=> $result['description'],
				'top_description' 	=> $result['top_description'],
				'bottom_description'=> $result['bottom_description'],
				'meta_title'		=> $result['meta_title'],
				'meta_description'	=> $result['meta_description'],
				'meta_keyword'		=> $result['meta_keyword'],
			);
		}

		return $mpgallery_data;
	}

	public function getMpGalleryPhoto($mpgallery_photo_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mpgallery_photo gp LEFT JOIN " . DB_PREFIX . "mpgallery_photo_description ghd ON (gp.mpgallery_photo_id = ghd.mpgallery_photo_id) WHERE gp.mpgallery_photo_id = '" . (int)$mpgallery_photo_id . "' AND ghd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getMpGalleryPhotos($mpgallery_id) {
		$mpgallery_photo_data = array();

		$mpgallery_photo_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mpgallery_photo gp WHERE gp.mpgallery_id = '" . (int)$mpgallery_id . "' ORDER BY gp.sort_order");

		foreach ($mpgallery_photo_query->rows as $mpgallery_photo) {
			$mpgallery_photo_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mpgallery_photo gp LEFT JOIN " . DB_PREFIX . "mpgallery_photo_description ghd ON (gp.mpgallery_photo_id = ghd.mpgallery_photo_id) WHERE gp.mpgallery_photo_id = '" . (int)$mpgallery_photo['mpgallery_photo_id'] . "'");

			$mpgallery_photo_description_data = array();
			foreach ($mpgallery_photo_description_query->rows as $mpgallery_photo_description) {
				$mpgallery_photo_description_data[$mpgallery_photo_description['language_id']] = array(
					'name'            => $mpgallery_photo_description['name'],
				);
			}

			$mpgallery_photo_data[] = array(
				'mpgallery_photo_id' 			=> $mpgallery_photo['mpgallery_photo_id'],
				'photo'           			=> $mpgallery_photo['photo'],
				'highlight'     			=> $mpgallery_photo['highlight'],
				'sort_order'     			=> $mpgallery_photo['sort_order'],
				'link'     					=> $mpgallery_photo['link'],
				'mpgallery_photo_description'	=> $mpgallery_photo_description_data,
			);
		}


		return $mpgallery_photo_data;
	}

	public function getMpGalleryPhotoDescriptions($mpgallery_id) {
		$mpgallery_photo_data = array();

		$mpgallery_photo_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mpgallery_photo WHERE mpgallery_id = '" . (int)$mpgallery_id . "' ORDER BY sort_order");

		foreach ($mpgallery_photo_query->rows as $mpgallery_photo) {
			$mpgallery_photo_description_data = array();

			$mpgallery_photo_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mpgallery_photo_description WHERE mpgallery_photo_id = '" . (int)$mpgallery_photo['mpgallery_photo_id'] . "'");

			foreach ($mpgallery_photo_description_query->rows as $mpgallery_photo_description) {
				$mpgallery_photo_description_data[$mpgallery_photo_description['language_id']] = array('name' => $mpgallery_photo_description['name']);
			}

			$mpgallery_photo_data[] = array(
				'mpgallery_photo_id'          => $mpgallery_photo['mpgallery_photo_id'],
				'mpgallery_photo_description' => $mpgallery_photo_description_data,
				'image'                    	=> $mpgallery_photo['image'],
				'highlight'                	=> $mpgallery_photo['highlight'],
				'sort_order'               	=> $mpgallery_photo['sort_order']
			);
		}

		return $mpgallery_photo_data;
	}



	public function getMpGalleryVideos($mpgallery_id) {
		$mpgallery_video_data = array();

		$mpgallery_video_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mpgallery_video gp WHERE gp.mpgallery_id = '" . (int)$mpgallery_id . "' ORDER BY gp.sort_order");

		foreach ($mpgallery_video_query->rows as $mpgallery_video) {
			$mpgallery_video_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mpgallery_video gp LEFT JOIN " . DB_PREFIX . "mpgallery_video_description ghd ON (gp.mpgallery_video_id = ghd.mpgallery_video_id) WHERE gp.mpgallery_video_id = '" . (int)$mpgallery_video['mpgallery_video_id'] . "'");

			$mpgallery_video_description_data = array();
			foreach ($mpgallery_video_description_query->rows as $mpgallery_video_description) {
				$mpgallery_video_description_data[$mpgallery_video_description['language_id']] = array(
					'name'            => $mpgallery_video_description['name'],
				);
			}

			$mpgallery_video_data[] = array(
				'mpgallery_video_id' 			=> $mpgallery_video['mpgallery_video_id'],
				'video_thumb'           	=> $mpgallery_video['video_thumb'],
				'sort_order'     			=> $mpgallery_video['sort_order'],
				'link'     					=> $mpgallery_video['link'],
				'highlight'     			=> $mpgallery_video['highlight'],
				'mpgallery_video_description'	=> $mpgallery_video_description_data,
			);
		}

		return $mpgallery_video_data;
	}

	public function getTotalMpGalleries() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "mpgallery`");

		return $query->row['total'];
	}
	// gallery for product task starts
	// 07-05-2022: updation task start
	public function getMpGalleryProducts($mpgallery_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mpgallery_product WHERE mpgallery_id = '" . (int)$mpgallery_id . "'");
		return $query->rows;
	}
	// 07-05-2022: updation task end
	// gallery for product task ends
}