<?php
class ControllerExtensionMpPhotoGalleryAlbum extends \mpphotogallery\Controllercatalog {
	use \mpphotogallery\trait_mpphotogallery_catalog;

	public function __construct($registry) {
		parent :: __construct($registry);

		$this->igniteTraitMpPhotoGalleryCatalog($registry);
	}

	// 'trigger' => 'catalog/view/common/footer/after',
	public function footer(&$route, &$data, &$output) {
		if(!$this->config->get('module_mpphotogallery_status')) {
			return;
		}

		$menu_at = $this->config->get('module_mpphotogallery_menu_at');

		if (is_array($menu_at) && in_array('header', $menu_at)) {

			$this->load->language($this->extension_path . 'mpphotogallery/gallery_link');

			$menu = [
				'name' => $this->language->get('mptext_gallery'),
				'href' => $this->url->link($this->extension_path . 'mpphotogallery/album', '', true)
			];

			$gallery_menu = 'album';

			switch ($this->config->get('module_mpphotogallery_menu_at_footer')) {
				case 'album':
				default:
					$menu = [
						'name' => $this->language->get('mptext_gallery'),
						'href' => $this->url->link($this->extension_path . 'mpphotogallery/album', '', true)
					];
				break;
				case 'album_photo':
					$menu = [
						'name' => $this->language->get('mptext_photo'),
						'href' => $this->url->link($this->extension_path . 'mpphotogallery/album_photo', '', true)
					];
				break;
				case 'album_video':
					$menu = [
						'name' => $this->language->get('mptext_video'),
						'href' => $this->url->link($this->extension_path . 'mpphotogallery/album_video', '', true)
					];
				break;
				case 'selected_album':
					$this->load->model($this->extension_path . 'mpphotogallery/album');

					$gallery_info = $this->model_extension_mpphotogallery_album->getMpGalleryinfo((int)$this->config->get('module_mpphotogallery_menufooter_album'));
					if ($gallery_info) {
						$menu = [
							'name' => $gallery_info['title'],
							'href' => $this->url->link($this->extension_path . 'mpphotogallery/photo', 'mpgallery_id=' . $gallery_info['mpgallery_id'])
						];
					}
				break;
			}

			// $find = '<li><a href="' . $data['manufacturer'] . '">' . $data['text_manufacturer'] . '</a></li>';
			$find = '<li><a href="' . $data['manufacturer'] . '">';

			$replace = '<li><a href="' . $menu['href'] . '">' . $menu['name'] . '</a></li>';

			$output = str_replace($find, $replace . "\n" . $find, $output);
		}


	}

	// 'trigger' => 'catalog/view/common/header/after',
	public function header(&$route, &$data, &$output) {
		if(!$this->config->get('module_mpphotogallery_status')) {
			return;
		}

		$menu_at = $this->config->get('module_mpphotogallery_menu_at');

		if (is_array($menu_at) && in_array('header', $menu_at)) {

			$this->load->language($this->extension_path . 'mpphotogallery/gallery_link');

			$menu = [
				'name' => $this->language->get('mptext_gallery'),
				'href' => $this->url->link($this->extension_path . 'mpphotogallery/album', '', true)
			];

			$gallery_menu = 'album';

			switch ($this->config->get('module_mpphotogallery_menu_at_header')) {
				case 'album':
				default:
					$menu = [
						'name' => $this->language->get('mptext_gallery'),
						'href' => $this->url->link($this->extension_path . 'mpphotogallery/album', '', true)
					];
				break;
				case 'album_photo':
					$menu = [
						'name' => $this->language->get('mptext_photo'),
						'href' => $this->url->link($this->extension_path . 'mpphotogallery/album_photo', '', true)
					];
				break;
				case 'album_video':
					$menu = [
						'name' => $this->language->get('mptext_video'),
						'href' => $this->url->link($this->extension_path . 'mpphotogallery/album_video', '', true)
					];
				break;
				case 'selected_album':
					$this->load->model($this->extension_path . 'mpphotogallery/album');

					$gallery_info = $this->model_extension_mpphotogallery_album->getMpGalleryinfo((int)$this->config->get('module_mpphotogallery_menuheader_album'));
					if ($gallery_info) {
						$menu = [
							'name' => $gallery_info['title'],
							'href' => $this->url->link($this->extension_path . 'mpphotogallery/photo', 'mpgallery_id=' . $gallery_info['mpgallery_id'])
						];
					}
				break;
			}

			$find = '<li><a href="' . $data['contact'] . '">';

			$replace = '<li><a href="' . $menu['href'] . '">' . $menu['name'] . '</a></li>';

			$output = str_replace($find, $replace . "\n" . $find, $output);
		}

		$metas = $this->mpphotogallery_meta->getMeta();

		$html_meta = [];
		foreach ($metas as $meta) {
			$html_meta[] = '<meta ' . $meta['attribute'] . '="' . $meta['attribute_value'] . '" content="' . $meta['content'] . '" />' . "\n";
		}

		if ($html_meta) {
			$find = '</head>';
			$output = str_replace($find, implode("\n", $html_meta) . "\n" . $find, $output);
		}


	}

	// 'trigger' => 'catalog/view/common/menu/before',
	public function mpphotogalleryMenu(&$route, &$data, &$code) {
		if(!$this->config->get('module_mpphotogallery_status')) {
			return;
		}

		$menu = [];
		$menu_at = $this->config->get('module_mpphotogallery_menu_at');

		if (is_array($menu_at) && in_array('header_menu', $menu_at)) {

			$this->load->language($this->extension_path . 'mpphotogallery/gallery_link');
			$children = [];

			$children[] = array(
				'name'  => $this->language->get('mptext_photo'),
				'href'  => $this->url->link($this->extension_path . 'mpphotogallery/album_photo', '', true),
			);

			$children[] = array(
				'name'  => $this->language->get('mptext_video'),
				'href'  => $this->url->link($this->extension_path . 'mpphotogallery/album_video', '', true),
			);

			$menu = array(
				'name'     => $this->language->get('mptext_gallery'),
				'children' => $children,
				'column'   => 1,
				'href'     => $this->url->link($this->extension_path . 'mpphotogallery/album', '', true),
			);

			if ($menu) {
				array_unshift($data['categories'], $menu);
				// array_push($data['categories'], $menu);
			}

		}
	}

	public function index() {
		if (!$this->config->get('module_mpphotogallery_status')) {
			return;
		}
		$this->load->language($this->extension_path . 'mpphotogallery/album');

		$this->load->model($this->extension_path . 'mpphotogallery/album');

		$this->load->model('tool/image');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_viewed'] = $this->language->get('text_viewed');

		$this->document->addStyle('catalog/view/javascript/mpphotogallery/assets/style.css');

		$data['mpphotogallery_album_description'] = $this->config->get('module_mpphotogallery_album_description');

		if (isset($this->request->get['page']) && (int)$this->request->get['page']) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		if ((int)$this->config->get('module_mpphotogallery_album_limit')) {
			$limit = (int)$this->config->get('module_mpphotogallery_album_limit');
		} else {
			$limit = 20;
		}

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->extension_path . 'mpphotogallery/album', $url, true)
		);

		$this->load->model('tool/image');

		$data['gallerys'] = [];

		$filter_data = array(
			'start' => ($page - 1) * $limit,
			'limit' => $limit,
		);

		$data['text_photos'] = $this->language->get('text_photos');

		$gallery_total = $this->model_extension_mpphotogallery_album->getTotalMpGalleries($filter_data);

		$gallery_info = $this->model_extension_mpphotogallery_album->getMpGallery($filter_data);

		foreach ($gallery_info as $gallery) {
			if ($gallery) {
				if ($gallery['image'] && file_exists(DIR_IMAGE . $gallery['image'])) {
					$image = $this->model_tool_image->resize($gallery['image'], (int)$this->config->get('module_mpphotogallery_album_width'), (int)$this->config->get('module_mpphotogallery_album_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', (int)$this->config->get('module_mpphotogallery_album_width'), (int)$this->config->get('module_mpphotogallery_album_height'));
				}

				$data['gallerys'][] = array(
					'mpgallery_id'  => $gallery['mpgallery_id'],
					'viewed'  	  => $gallery['viewed'],
					'image'       => $image,
					'title'        => $gallery['title'],
					'total_photos' => $this->model_extension_mpphotogallery_album->getTotalMpGalleryPhotos($gallery['mpgallery_id']),
					'description' => html_entity_decode($gallery['description'], ENT_QUOTES, 'UTF-8'),
					'href'        => $this->url->link($this->extension_path . 'mpphotogallery/photo', 'mpgallery_id=' . $gallery['mpgallery_id'] . $url)
				);
			}
		}

		$data['mpphotogallery_color'] = $this->config->get('module_mpphotogallery_color');

		$url = '';

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}

		$pagination = new Pagination();
		$pagination->total = $gallery_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->url->link($this->extension_path . 'mpphotogallery/album', $url . '&page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($gallery_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($gallery_total - $limit)) ? $gallery_total : ((($page - 1) * $limit) + $limit), $gallery_total, ceil($gallery_total / $limit));

		$data['limit'] = $limit;

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view($this->extension_path . 'mpphotogallery/album', $data));

	}
}