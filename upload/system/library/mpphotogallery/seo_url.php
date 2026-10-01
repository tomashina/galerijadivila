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
* Seo URL class For OC2x, OC3x
* Show Form at Any Page
* Validate SEO URL
* Static Landing Page
* Dynamic page, i.e: products, categories, galleries, etc.
*/

class seo_url extends \Controller {

	use \mpphotogallery\trait_mpphotogallery;

	public function __construct($registry) {
		parent :: __construct($registry);

		$this->igniteTraitMpPhotoGallery($registry);

		$this->load->language($this->extension_path . 'mpphotogallery/seo_url');
	}

	/**
	 * seourl form starts
	 *
	 * @fn getForm
	 * @param array $form_data
	 * @return string seo url / keyword input fields
	 *
	 *
	 * &$form_data.data
	 * &$form_data.error
	 * $form_data.input
	 * $form_data.error_key
	 * $form_data.seo_query_key
	 * $form_data.seo_query_value
	 *
	 *
	 * &$form_data.data
	 * current controller data pass as reference.
	 * Must have data.stores, data.languages
	 *
	 *
	 * &$form_data.error
	 * current controller $this->error pass as refrence
	 *
	 *
	 * $form_data.input
	 * Input Field Name
	 *
	 * $form_data.error_key
	 * key/index for seo url input field in $this->error
	 *
	 *
	 * $form_data.seo_query_key
	 * $form_data.seo_query_value
	 * Table: oc_seo_url or oc_url_alias
	 * oc_seo_url.query 	= oc3x
	 * oc_url_alias.query 	= oc2x
	 *
	 * value. I.e: mpgallery_id=2
	 * mpgallery_id 		= seo_query_key
	 * 2/0 					= seo_query_value
	 *
	 *
	 * @call
	 *
	 *
	 * @for: static page or landing page
	 * $this->seo_url->getForm([
	 *	'data' => &$data,
	 *	'error' => &$this->error,
	 *	'input' => 'module_mpphotogallery_album_page',
	 *	'error_key' => 'keyword_mpphotogallery_album_page',
	 *	'seo_query_key' => 'mpphotogallery_album_page',
	 *	'seo_query_value' => '1',
	 * ]);
	 *
	 *
	 * @for: dynamic page, for example: product, category
	 *
	 * $this->seo_url->getForm([
	 *	'data' => &$data,
	 *	'error' => &$this->error,
	 *	'input' => 'mpgallery_seo_url',
	 *	'error_key' => 'keyword_mpgallery_seo_url',
	 *	'seo_query_key' => 'mpgallery_id',
	 *	'seo_query_value' => isset($this->request->get['mpgallery_id']) ? $this->request->get['mpgallery_id'] : 0,
	 * ]);
	 *
	*/

	public function getForm($form_data) {
		$data = &$form_data['data'];

		$error = &$form_data['error'];
		$error_key = $form_data['error_key'];


		$data['input'] = $input = $form_data['input'];

		if (isset($error[$error_key])) {

			$data['error_seourl'] = $error[$error_key];
		} else {
			if (VERSION >= '3.0.0.0') {
				$data['error_seourl'] = [];
			} else {
				$data['error_seourl'] = '';
			}
		}

		if (isset($this->request->post[$input])) {
			$data['seourl'] = $this->request->post[$input];
		} elseif ($form_data['seo_query_value']) {
			$data['seourl'] = $this->get($form_data['seo_query_key'], $form_data['seo_query_value']);
		} else {
			if (VERSION >= '3.0.0.0') {
				$data['seourl'] = [];
			} else {
				$data['seourl'] = '';
			}
		}

		return $this->viewLoad($this->extension_path . 'mpphotogallery/seo_url', $data, false);
	}

	/**
	 * seourl form ends
	*/

	/**
	 * save data starts
	 *
	 * @fn save
	 * @param array $seo_data
	 * @param string $key
	 * @param string $value
	 * @return void
	 *
	 * &$seo_data
	 * pass as reference, usually _POST data
	 *	_POST.module_mpphotogallery_album_page
	 *
	 * OR
	 *
	 *	_POST.mpgallery_seo_url
	 *
	 *
	 * $key 				= seo_query_key
	 * $value 				= seo_query_value
	 * Table: oc_seo_url or oc_url_alias
	 * oc_seo_url.query 	= oc3x
	 * oc_url_alias.query 	= oc2x
	 *
	 * value. I.e: mpgallery_id=2
	 * mpgallery_id 		= seo_query_key
	 * 2/0 					= seo_query_value
	 *
	 *
	 * @call
	 *
	 * @for: static page or landing page
 	 * $this->seo_url->save($this->request->post['module_mpphotogallery_album_page'], 'mpphotogallery_album_page', '1');
 	 *
 	 *
	 * @for: dynamic page, for example: product, category
	 * $this->seo_url->save($this->request->post['mpgallery_seo_url'], 'mpgallery_id', {$mpgallery_id});
	 *
	 * $this->seo_url->save($this->request->post['mpgallery_seo_url'], 'mpgallery_id', $this->request->get['mpgallery_id']);
	 * {$mpgallery_id} dynamic value
	 *
	*/

	protected function saveSeoUrl(&$seo_data, $key, $value) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE `query` = '{$this->db->escape($key)}={$this->db->escape($value)}'");

		if (isset($seo_data) && is_array($seo_data)) {
			foreach ($seo_data as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = '{$this->db->escape($key)}={$this->db->escape($value)}', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}
	}

	protected function saveUrlAlias(&$seo_data, $key, $value) {

		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = '{$this->db->escape($key)}={$this->db->escape($value)}'");

		if (!empty($seo_data)) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = '{$this->db->escape($key)}={$this->db->escape($value)}', keyword = '" . $this->db->escape($seo_data) . "'");
		}
	}

	public function save(&$seo_data, $key, $value) {
		if (VERSION >= '3.0.0.0') {
			$this->saveSeoUrl($seo_data, $key, $value);
		} else {
			$this->saveUrlAlias($seo_data, $key, $value);
		}
	}

	/**
	 * save data ends
	*/

	/**
	 * delete data starts
	 *
	 * @fn delete
	 * @param string $key
	 * @param string $value
	 * @return void
	 *
	 *
	 * $key 				= seo_query_key
	 * $value 				= seo_query_value
	 * Table: oc_seo_url or oc_url_alias
	 * oc_seo_url.query 	= oc3x
	 * oc_url_alias.query 	= oc2x
	 *
	 * value. I.e: mpgallery_id=2
	 * mpgallery_id 		= seo_query_key
	 * 2/0 					= seo_query_value
	 *
	 *
	 * @call
	 *
	 * @for: static page or landing page
 	 * $this->seo_url->delete('module_mpphotogallery_album_page', '1');
 	 *
 	 *
	 * @for: dynamic page, for example: product, category
	 * $this->seo_url->delete('mpgallery_id', {$mpgallery_id});
	 * {$mpgallery_id} dynamic value
	 *
	 *
	*/

	protected function deleteSeoUrl($key, $value) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE `query` = '{$this->db->escape($key)}={$this->db->escape($value)}'");
	}

	protected function deleteUrlAlias($key, $value) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = '{$this->db->escape($key)}={$this->db->escape($value)}'");
	}

	public function delete($key, $value) {
		if (VERSION >= '3.0.0.0') {
			$this->deleteSeoUrl($key, $value);
		} else {
			$this->deleteUrlAlias($key, $value);
		}
	}

	/**
	 * delete data ends
	*/

	/**
	 * get data starts
	 *
	 * @fn get
	 * @param string $key
	 * @param string $value
	 * @return array 	OC3X
	 * @return string	OC2X
	 *
	 *
	 * $key 				= seo_query_key
	 * $value 				= seo_query_value
	 * Table: oc_seo_url or oc_url_alias
	 * oc_seo_url.query 	= oc3x
	 * oc_url_alias.query 	= oc2x
	 *
	 * value. I.e: mpgallery_id=2
	 * mpgallery_id 		= seo_query_key
	 * 2/0 					= seo_query_value
	 *
	 * @call
	 *
	 * @for: static page or landing page
	 * $data['module_mpphotogallery_album_page'] = $this->seo_url->get('module_mpphotogallery_album_page', '1');
	 *
	 *
	 * @for: dynamic page, for example: product, category
	 * $data['mpgallery_seo_url'] = $this->seo_url->get('mpgallery_id', {$mpgallery_id});
	 * {$mpgallery_id} = dynamic usually from _GET.mpgallery_id
	 * during edit form
	 *
	 *
	*/

	protected function getSeoUrl($key, $value) {
		$seourl_data = [];

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = '{$this->db->escape($key)}={$this->db->escape($value)}'");

		foreach ($query->rows as $result) {
			$seourl_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $seourl_data;
	}
	protected function getUrlAlias($key, $value) {
		$query = $this->db->query("SELECT DISTINCT keyword FROM " . DB_PREFIX . "url_alias WHERE query = '{$this->db->escape($key)}={$this->db->escape($value)}'");
		return $query->row ? $query->row['keyword'] : '';
	}

	public function get($key, $value) {
		if (VERSION >= '3.0.0.0') {
			return $this->getSeoUrl($key, $value);
		} else {
			return $this->getUrlAlias($key, $value);
		}
	}

	/**
	 * get data ends
	*/

	/**
	 * Validation starts
	 *
	 * @fn validate
	 * @param array $seo_keyword
	 * @param string $get_keyword
	 * @return array
	 *
	 * &$seo_keyword
	 * pass as reference, usually _POST data
	 *	_POST.module_mpphotogallery_album_page
	 *
	 * OR
	 *
	 *	_POST.mpgallery_seo_url
	 *
	 *
	 * $get_keyword 		= key in _GET array
	 * usually it is seo table query former part separate by =
	 * i.e: mpphotogallery_album_page or mpgallery_id
	 *
	 * Table: oc_seo_url or oc_url_alias
	 * oc_seo_url.query 	= oc3x
	 * oc_url_alias.query 	= oc2x
	 *
	 * value. I.e: mpgallery_id=2
	 * mpgallery_id 		= seo_query_key
	 * 2/0 					= seo_query_value
	 * seo_query_value 		= in _GET.$get_keyword
	 *
	 *
	 * @call
	 *
	 * @for: static page or landing page
	 * $error = $this->seo_url->validate($this->request->post['module_mpphotogallery_album_page'], 'mpphotogallery_album_page');

	 * if (!empty($error['keyword'])) {
	 *	$this->error['keyword_mpphotogallery_album_page'] = $error['keyword'];
	 * }
	 *
	 *
	 * @for: dynamic page, for example: product, category
	 * $error = $this->seo_url->validate($this->request->post['mpgallery_seo_url'], 'mpgallery_id');

	 * if (!empty($error['keyword'])) {
	 *	$this->error['keyword_mpgallery_seo_url'] = $error['keyword'];
	 * }
	 *
	 *
	*/

	protected function validateSeoUrl(&$seo_keyword, $get_keyword) {
		$error = [];
		$this->load->model('design/seo_url');

		foreach ($seo_keyword as $store_id => $language) {
			foreach ($language as $language_id => $keyword) {
				if (!empty($keyword)) {
					if (count(array_keys($language, $keyword)) > 1) {
						$error['keyword'][$store_id][$language_id] = $this->language->get('error_unique');
					}

					$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);

					foreach ($seo_urls as $seo_url) {
						if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get[$get_keyword]) || (($seo_url['query'] != $get_keyword . '=' . $this->request->get[$get_keyword])))) {
							$error['keyword'][$store_id][$language_id] = $this->language->get('error_keyword');

							break;
						}
					}
				}
			}
		}
		return $error;
	}

	protected function validateUrlAlias(&$seo_keyword, $get_keyword) {
		$error = [];

		$this->load->model('catalog/url_alias');

		$url_alias_info = $this->model_catalog_url_alias->getUrlAlias($seo_keyword);

		if ($url_alias_info && isset($this->request->get[$get_keyword]) && $url_alias_info['query'] != $get_keyword . '=' . $this->request->get[$get_keyword]) {
			$error['keyword'] = sprintf($this->language->get('error_keyword'));
		}

		if ($url_alias_info && !isset($this->request->get[$get_keyword])) {
			$error['keyword'] = sprintf($this->language->get('error_keyword'));
		}


		return $error;
	}

	public function validate(&$seo_keyword, $get_keyword) {

		if (VERSION >= '3.0.0.0') {
			return $this->validateSeoUrl($seo_keyword, $get_keyword);
		} else {
			return $this->validateUrlAlias($seo_keyword, $get_keyword);
		}
	}

	/**
	 * Validation ens
	*/
}
