<?php
class ControllerExtensionMpPhotoGalleryMpGallery extends Controller {
	use mpphotogallery\trait_mpphotogallery;
	private $error = [];

	private $seo_url;

	public function __construct($registry) {
		parent :: __construct($registry);
		$this->igniteTraitMpPhotoGallery($registry);

		$this->seo_url = new \mpphotogallery\seo_url($registry);
	}

	public function index() {
		$this->load->language($this->extension_path . 'mpphotogallery/mpgallery');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model($this->extension_path . 'mpphotogallery/mpgallery');
		$this->load->model('tool/image');

		$this->getList();
	}

	public function add() {
		$this->load->language($this->extension_path . 'mpphotogallery/mpgallery');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model($this->extension_path . 'mpphotogallery/mpgallery');
		$this->load->model('tool/image');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$mpgallery_id = $this->{'model_' . $this->extension_model . 'mpphotogallery_mpgallery'}->addMpGallery($this->request->post);

			if (isset($this->request->post['mpgallery_seo_url']) && $this->request->post['mpgallery_seo_url']) {

				$this->seo_url->save($this->request->post['mpgallery_seo_url'], 'mpgallery_id', $mpgallery_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link($this->extension_path . 'mpphotogallery/mpgallery', $this->token.'=' . $this->session->data[$this->token] . $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language($this->extension_path . 'mpphotogallery/mpgallery');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model($this->extension_path . 'mpphotogallery/mpgallery');
		$this->load->model('tool/image');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->{'model_' . $this->extension_model . 'mpphotogallery_mpgallery'}->editMpGallery($this->request->get['mpgallery_id'], $this->request->post);

			if (isset($this->request->post['mpgallery_seo_url']) && $this->request->post['mpgallery_seo_url']) {

				$this->seo_url->save($this->request->post['mpgallery_seo_url'], 'mpgallery_id', $this->request->get['mpgallery_id']);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link($this->extension_path . 'mpphotogallery/mpgallery', $this->token.'=' . $this->session->data[$this->token] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language($this->extension_path . 'mpphotogallery/mpgallery');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model($this->extension_path . 'mpphotogallery/mpgallery');
		$this->load->model('tool/image');
		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $mpgallery_id) {
				$this->{'model_' . $this->extension_model . 'mpphotogallery_mpgallery'}->deleteMpGallery($mpgallery_id);

				$this->seo_url->delete('mpgallery_id', $mpgallery_id);

			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link($this->extension_path . 'mpphotogallery/mpgallery', $this->token.'=' . $this->session->data[$this->token] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'gd.title';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link($this->extension_path . 'module/mpphotogallery_setting', $this->token.'=' . $this->session->data[$this->token], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->extension_path . 'mpphotogallery/mpgallery', $this->token.'=' . $this->session->data[$this->token] . $url, true)
		);

		// 17-march-2023: improvements start
		// explicit code for 2x, and 2.3x versions only.
		if (VERSION < '3.0.0.0') {
			$this->getAllLanguageMpPhotoGallery($data);
		}
		// 17-march-2023: improvements end

		$data['get_token'] = $this->token;
		$data['token'] = $this->session->data[$this->token];
		$data['extension_path'] = $this->extension_path;
		$data['languages'] = $this->getLanguages();

		$url_front = new Url(HTTP_CATALOG, HTTPS_CATALOG);

		$data['add'] = $this->url->link($this->extension_path . 'mpphotogallery/mpgallery/add', $this->token.'=' . $this->session->data[$this->token] . $url, true);
		$data['delete'] = $this->url->link($this->extension_path . 'mpphotogallery/mpgallery/delete', $this->token.'=' . $this->session->data[$this->token] . $url, true);

		$data['galleries'] = [];

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$gallery_total = $this->{'model_' . $this->extension_model . 'mpphotogallery_mpgallery'}->getTotalMpGalleries();

		$results = $this->{'model_' . $this->extension_model . 'mpphotogallery_mpgallery'}->getMpGalleries($filter_data);

		foreach ($results as $result) {

			if ($result['image'] && file_exists(DIR_IMAGE . $result['image'])) {
				$thumb = $this->model_tool_image->resize($result['image'], 50, 50);
			} else {
				$thumb = $this->model_tool_image->resize('no_image.png', 50, 50);
			}

			$data['galleries'][] = array(
				'mpgallery_id'  => $result['mpgallery_id'],
				'title'       => $result['title'],
				'status'       => $result['status'],
				'viewed'       => $result['viewed'],
				'status_str'       => $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'thumb'       => $thumb,
				'sort_order' => $result['sort_order'],
				'edit'       => $this->url->link($this->extension_path . 'mpphotogallery/mpgallery/edit', $this->token.'=' . $this->session->data[$this->token] . '&mpgallery_id=' . $result['mpgallery_id'] . $url, true),
				'view'       => $url_front->link($this->extension_path . 'mpphotogallery/photo', 'mpgallery_id=' . $result['mpgallery_id'], true)
			);
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = [];
		}

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link($this->extension_path . 'mpphotogallery/mpgallery', $this->token.'=' . $this->session->data[$this->token] . '&sort=gd.title' . $url, true);
		$data['sort_viewed'] = $this->url->link($this->extension_path . 'mpphotogallery/mpgallery', $this->token.'=' . $this->session->data[$this->token] . '&sort=g.viewed' . $url, true);
		$data['sort_sort_order'] = $this->url->link($this->extension_path . 'mpphotogallery/mpgallery', $this->token.'=' . $this->session->data[$this->token] . '&sort=g.sort_order' . $url, true);
		$data['sort_status'] = $this->url->link($this->extension_path . 'mpphotogallery/mpgallery', $this->token.'=' . $this->session->data[$this->token] . '&sort=g.status' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $gallery_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link($this->extension_path . 'mpphotogallery/mpgallery', $this->token.'=' . $this->session->data[$this->token] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($gallery_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($gallery_total - $this->config->get('config_limit_admin'))) ? $gallery_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $gallery_total, ceil($gallery_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['mtabs'] = $this->load->controller($this->extension_path . 'mpphotogallery/mtabs');
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->viewLoad($this->extension_path . 'mpphotogallery/mpgallery_list', $data));

	}

	protected function getForm() {

		$data['text_form'] = !isset($this->request->get['mpgallery_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link($this->extension_path . 'module/mpphotogallery_setting', $this->token.'=' . $this->session->data[$this->token], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->extension_path . 'mpphotogallery/mpgallery', $this->token.'=' . $this->session->data[$this->token] . $url, true)
		);

		// 17-march-2023: improvements start
		// explicit code for 2x, and 2.3x versions only.
		if (VERSION < '3.0.0.0') {
			$this->getAllLanguageMpPhotoGallery($data);
		}
		// 17-march-2023: improvements end

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['meta_title'])) {
			$data['error_meta_title'] = $this->error['meta_title'];
		} else {
			$data['error_meta_title'] = [];
		}

		if (isset($this->error['title'])) {
			$data['error_title'] = $this->error['title'];
		} else {
			$data['error_title'] = [];
		}

		if (isset($this->error['mpgallery_photo'])) {
			$data['error_gallery_photo'] = $this->error['mpgallery_photo'];
		} else {
			$data['error_gallery_photo'] = [];
		}

		if (isset($this->error['mpgallery_video'])) {
			$data['error_gallery_video'] = $this->error['mpgallery_video'];
		} else {
			$data['error_gallery_video'] = [];
		}
		// // 07-05-2022: updation task end

		$data['get_token'] = $this->token;
		$data['token'] = $this->session->data[$this->token];
		$data['extension_path'] = $this->extension_path;
		$data['languages'] = $this->getLanguages();
		$data['text_editor'] = $this->textEditor($data);


		$this->load->model('setting/store');

		$data['stores'] = [];

		$data['stores'][] = array(
			'store_id' => '0',
			'name'     => $this->language->get('text_default')
		);

		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}

		if (!isset($this->request->get['mpgallery_id'])) {
			$data['action'] = $this->url->link($this->extension_path . 'mpphotogallery/mpgallery/add', $this->token.'=' . $this->session->data[$this->token] . $url, true);
			$data['mpgallery_id'] = '';
		} else {
			$data['action'] = $this->url->link($this->extension_path . 'mpphotogallery/mpgallery/edit', $this->token.'=' . $this->session->data[$this->token] . '&mpgallery_id=' . $this->request->get['mpgallery_id'] . $url, true);
			$data['mpgallery_id'] = $this->request->get['mpgallery_id'];
		}

		$data['cancel'] = $this->url->link($this->extension_path . 'mpphotogallery/mpgallery', $this->token.'=' . $this->session->data[$this->token] . $url, true);

		if (isset($this->request->get['mpgallery_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$gallery_info = $this->{'model_' . $this->extension_model . 'mpphotogallery_mpgallery'}->getMpGallery($this->request->get['mpgallery_id']);
		}

		if (isset($this->request->post['mpgallery_description'])) {
			$data['mpgallery_description'] = $this->request->post['mpgallery_description'];
		} elseif (isset($this->request->get['mpgallery_id'])) {
			$data['mpgallery_description'] = $this->{'model_' . $this->extension_model . 'mpphotogallery_mpgallery'}->getMpGalleryDescriptions($this->request->get['mpgallery_id']);
		} else {
			$data['mpgallery_description'] = [];
		}

		if (isset($this->request->post['title'])) {
			$data['title'] = $this->request->post['title'];
		} elseif (!empty($gallery_info)) {
			$data['title'] = $gallery_info['title'];
		} else {
			$data['title'] = '';
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($gallery_info)) {
			$data['sort_order'] = $gallery_info['sort_order'];
		} else {
			$data['sort_order'] = '';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($gallery_info)) {
			$data['status'] = $gallery_info['status'];
		} else {
			$data['status'] = true;
		}

		if (isset($this->request->post['width'])) {
			$data['width'] = $this->request->post['width'];
		} elseif (!empty($gallery_info)) {
			$data['width'] = $gallery_info['width'];
		} else {
			$data['width'] = 200;
		}

		if (isset($this->request->post['height'])) {
			$data['height'] = $this->request->post['height'];
		} elseif (!empty($gallery_info)) {
			$data['height'] = $gallery_info['height'];
		} else {
			$data['height'] = 200;
		}

		if (isset($this->request->post['description'])) {
			$data['description'] = $this->request->post['description'];
		} elseif (!empty($gallery_info)) {
			$data['description'] = $gallery_info['description'];
		} else {
			$data['description'] = '';
		}

		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} elseif (!empty($gallery_info)) {
			$data['image'] = $gallery_info['image'];
		} else {
			$data['image'] = '';
		}

		if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
		} elseif (!empty($gallery_info) && is_file(DIR_IMAGE . $gallery_info['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($gallery_info['image'], 100, 100);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		// Photos
		if (isset($this->request->post['mpgallery_photo'])) {
			$gallery_photos = $this->request->post['mpgallery_photo'];
		} elseif (isset($this->request->get['mpgallery_id'])) {
			$gallery_photos = $this->{'model_' . $this->extension_model . 'mpphotogallery_mpgallery'}->getMpGalleryPhotos($this->request->get['mpgallery_id']);

		} else {
			$gallery_photos = [];
		}

		$data['mpgallery_photos'] = [];

		foreach ($gallery_photos as $gallery_photo) {
			if (is_file(DIR_IMAGE . $gallery_photo['photo'])) {
				$photo = $gallery_photo['photo'];
				$thumb = $gallery_photo['photo'];
			} else {
				$photo = '';
				$thumb = 'no_image.png';
			}


			$data['mpgallery_photos'][] = array(
				'mpgallery_photo_id'    => $gallery_photo['mpgallery_photo_id'],
				'photo'     					=> $photo,
				'thumb'     					=> $this->model_tool_image->resize($thumb, 100, 100),
				'highlight' 					=> isset($gallery_photo['highlight']) ? $gallery_photo['highlight'] : '',
				'link' 							=> $gallery_photo['link'],
				'sort_order' 					=> $gallery_photo['sort_order'],
				'mpgallery_photo_description'     => $gallery_photo['mpgallery_photo_description'],
			);
		}

		// Videos
		if (isset($this->request->post['mpgallery_video'])) {
			$gallery_videos = $this->request->post['mpgallery_video'];
		} elseif (isset($this->request->get['mpgallery_id'])) {
			$gallery_videos = $this->{'model_' . $this->extension_model . 'mpphotogallery_mpgallery'}->getMpGalleryVideos($this->request->get['mpgallery_id']);

		} else {
			$gallery_videos = [];
		}

		$data['mpgallery_videos'] = [];

		foreach ($gallery_videos as $gallery_video) {
			if (is_file(DIR_IMAGE . $gallery_video['video_thumb'])) {
				$video_thumb = $gallery_video['video_thumb'];
				$thumb = $gallery_video['video_thumb'];
			} else {
				$video_thumb = '';
				$thumb = 'no_image.png';
			}


			$data['mpgallery_videos'][] = array(
				'mpgallery_video_id'   			=> $gallery_video['mpgallery_video_id'],
				'video_thumb'     				=> $video_thumb,
				'thumb'     					=> $this->model_tool_image->resize($thumb, 100, 100),
				'link' 							=> $gallery_video['link'],
				'highlight' 					=> isset($gallery_video['highlight']) ? $gallery_video['highlight'] : '',
				'sort_order' 					=> $gallery_video['sort_order'],
				'mpgallery_video_description'     => $gallery_video['mpgallery_video_description'],
			);
		}


		if (isset($this->request->post['video_width'])) {
			$data['video_width'] = $this->request->post['video_width'];
		} elseif (!empty($gallery_info)) {
			$data['video_width'] = $gallery_info['video_width'];
		} else {
			$data['video_width'] = 200;
		}

		if (isset($this->request->post['video_height'])) {
			$data['video_height'] = $this->request->post['video_height'];
		} elseif (!empty($gallery_info)) {
			$data['video_height'] = $gallery_info['video_height'];
		} else {
			$data['video_height'] = 200;
		}

		// gallery for product task starts
		if (isset($this->request->post['mpgallery_products'])) {
			$gallery_products = $this->request->post['mpgallery_products'];
		} elseif (isset($this->request->get['mpgallery_id'])) {
			$gallery_products = $this->{'model_' . $this->extension_model . 'mpphotogallery_mpgallery'}->getMpGalleryProducts($this->request->get['mpgallery_id']);
		} else {
			$gallery_products = [];
		}

		$data['mpgallery_products'] = [];

		$this->load->model('catalog/product');
		// 07-05-2022: updation task start
		foreach ($gallery_products as $gallery_product) {
			$product_info = $this->model_catalog_product->getProduct($gallery_product['product_id']);

			if ($product_info) {
				if ($product_info['image'] && file_exists(DIR_IMAGE . $product_info['image'])) {
					$thumb = $this->model_tool_image->resize($product_info['image'], 65, 65);
				} else {
					$thumb = $this->model_tool_image->resize('no_image.png', 65, 65);
				}
				$data['mpgallery_products'][] = array(
					'product_id' => $product_info['product_id'],
					'name'       => $product_info['name'],
					'thumb'       => $thumb,
					'video'       => $gallery_product['video'],
					'image'       => $gallery_product['image'],
				);
			}
		}
		// 07-05-2022: updation task end

		// gallery for product task ends

		// 07-05-2022: updation task start
		$data['mpgallery_seo_url'] = $this->seo_url->getForm([
			'data' => &$data,
			'error' => &$this->error,
			'input' => 'mpgallery_seo_url',
			'error_key' => 'keyword_mpgallery_seo_url',
			'seo_query_key' => 'mpgallery_id',
			'seo_query_value' => isset($this->request->get['mpgallery_id']) ? $this->request->get['mpgallery_id'] : 0,
		]);
		// 07-05-2022: updation task end

		$data['mtabs'] = $this->load->controller($this->extension_path . 'mpphotogallery/mtabs');
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->viewLoad($this->extension_path . 'mpphotogallery/mpgallery_form', $data));

	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'extension/mpphotogallery/mpgallery')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		foreach ($this->request->post['mpgallery_description'] as $language_id => $value) {
			if ((utf8_strlen($value['title']) < 2) || (utf8_strlen($value['title']) > 255)) {
				$this->error['title'][$language_id] = $this->language->get('error_title');
			}

			if ((utf8_strlen($value['meta_title']) < 3) || (utf8_strlen($value['meta_title']) > 255)) {
				$this->error['meta_title'][$language_id] = $this->language->get('error_meta_title');
			}
		}

		if (isset($this->request->post['mpgallery_photo'])) {
			foreach ($this->request->post['mpgallery_photo'] as $gallery_photo_id => $gallery_photo) {
				foreach ($gallery_photo['mpgallery_photo_description'] as $language_id => $gallery_photo_description) {
					if ((utf8_strlen($gallery_photo_description['name']) < 1) || (utf8_strlen($gallery_photo_description['name']) > 128)) {
						$this->error['mpgallery_photo'][$gallery_photo_id][$language_id] = $this->language->get('error_gallery_photo');
					}
				}
			}
		}

		if (isset($this->request->post['mpgallery_video'])) {
			foreach ($this->request->post['mpgallery_video'] as $gallery_video_id => $gallery_video) {
				foreach ($gallery_video['mpgallery_video_description'] as $language_id => $gallery_video_description) {
					if ((utf8_strlen($gallery_video_description['name']) < 1) || (utf8_strlen($gallery_video_description['name']) > 128)) {
						$this->error['mpgallery_video'][$gallery_video_id][$language_id] = $this->language->get('error_gallery_video');
					}
				}
			}
		}

		// 07-05-2022: updation task start

		if (VERSION >= '3.0.0.0') {
			// oc3x
			if (isset($this->request->post['mpgallery_seo_url']) && $this->request->post['mpgallery_seo_url']) {

				$error = $this->seo_url->validate($this->request->post['mpgallery_seo_url'], 'mpgallery_id');

				if (!empty($error['keyword'])) {
					$this->error['keyword_mpgallery_seo_url'] = $error['keyword'];
				}
			}
		} else {

			// oc2x
			if (isset($this->request->post['mpgallery_seo_url']) && utf8_strlen($this->request->post['mpgallery_seo_url']) > 0) {
				$error = $this->seo_url->validate($this->request->post['mpgallery_seo_url'], 'mpgallery_id');
				if (!empty($error['keyword'])) {
					$this->error['keyword_mpgallery_seo_url'] = $error['keyword'];
				}
			}
		}

		// 07-05-2022: updation task end


		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'extension/mpphotogallery/mpgallery')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function autocomplete() {
		$json = [];

		if (isset($this->request->get['filter_name'])) {
			$this->load->language($this->extension_path . 'mpphotogallery/mpgallery');

			$this->load->model($this->extension_path . 'mpphotogallery/mpgallery');
			$this->load->model('tool/image');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'start'       => 0,
				'limit'       => 5
			);

			$galleries = $this->{'model_' . $this->extension_model . 'mpphotogallery_mpgallery'}->getMpGalleries($filter_data);

			foreach ($galleries as $gallery) {
				$gallery_photo_data = [];

				$gallery_photos = $this->{'model_' . $this->extension_model . 'mpphotogallery_mpgallery'}->getMpGalleryPhotos($gallery['mpgallery_id']);

					foreach ($gallery_photos as $gallery_photo) {
						if (is_file(DIR_IMAGE . $gallery_photo['photo'])) {
							$photo = $this->model_tool_image->resize($gallery_photo['photo'], 50, 50);
						} else {
							$photo = $this->model_tool_image->resize('no_image.png', 50, 50);
						}

						$gallery_photo_data[] = array(
							'mpgallery_photo_id' => $gallery_photo['mpgallery_photo_id'],
							'photo'           => $photo
						);
					}

					$sort_order = [];

					foreach ($gallery_photo_data as $key => $value) {
						$sort_order[$key] = $value['photo'];
					}

					array_multisort($sort_order, SORT_ASC, $gallery_photo_data);

				$json[] = array(
					'mpgallery_id'    => $gallery['mpgallery_id'],
					'title'         => strip_tags(html_entity_decode($gallery['title'], ENT_QUOTES, 'UTF-8')),
					'mpgallery_photo' => $gallery_photo_data
				);
			}
		}

		$sort_order = [];

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['title'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	// gallery for product task starts
	// 07-05-2022: updation task start
	public function productAutocomplete() {
		$json = [];

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/product');
			$this->load->model('tool/image');

			if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}

			if (isset($this->request->get['limit'])) {
				$limit = $this->request->get['limit'];
			} else {
				$limit = 5;
			}

			$filter_data = array(
				'filter_name'  => $filter_name,
				'start'        => 0,
				'limit'        => $limit
			);

			$results = $this->model_catalog_product->getProducts($filter_data);

			foreach ($results as $result) {
				if ($result['image'] && file_exists(DIR_IMAGE . $result['image'])) {
					$thumb = $this->model_tool_image->resize($result['image'], 65, 65);
				} else {
					$thumb = $this->model_tool_image->resize('no_image.png', 65, 65);
				}

				$json[] = array(
					'product_id' => $result['product_id'],
					'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'thumb'      => $thumb,
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	// 07-05-2022: updation task end
	// gallery for product task ends
}