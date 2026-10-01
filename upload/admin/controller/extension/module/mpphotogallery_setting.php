<?php
class ControllerExtensionModuleMpPhotoGallerySetting extends Controller {
	use mpphotogallery\trait_mpphotogallery;
	private $error = [];
	private $installed = [];
	private $layouts = [
		[
			'name' => 'ModulePoints - Photo Gallery Albums',
			'path' => 'extension/mpphotogallery/album',
			'module' => [
			]
		], [
			'name' => 'ModulePoints - Photo Gallery Albums Photos',
			'path' => 'extension/mpphotogallery/album_photo',
			'module' => [
			]
		], [
			'name' => 'ModulePoints - Photo Gallery Albums Videos',
			'path' => 'extension/mpphotogallery/album_video',
			'module' => [
			]
		], [
			'name' => 'ModulePoints - Photo Gallery Album',
			'path' => 'extension/mpphotogallery/photo',
			'module' => [
			]
		]
	];
	private $modules = [
		[
			'name' => 'ModulePoints - Gallery Album',
			'path' => 'extension/module/mpgallery',
			'type' => 'module',
			'code' => 'mpgallery',
			'extension' => 'mpphotogallery',
			'modules' => true
		],
		[
			'name' => 'ModulePoints - Gallery Photo',
			'path' => 'extension/module/mpphoto',
			'type' => 'module',
			'code' => 'mpphoto',
			'extension' => 'mpphotogallery',
			'modules' => true
		],
		[
			'name' => 'ModulePoints - Gallery Video',
			'path' => 'extension/module/mpvideo',
			'type' => 'module',
			'code' => 'mpvideo',
			'extension' => 'mpphotogallery',
			'modules' => true
		]
	];
	private $files = [
		'extension/mpphotogallery/mpgallery',
		'extension/mpphotogallery/mtabs',
		'extension/module/mpgallery',
		'extension/module/mpphoto',
		'extension/module/mpphotogallery_setting',
		'extension/module/mpvideo'
	];

	private $events_code = 'module_mpphotogallery';
	private $events = [[
		'trigger' => 'admin/view/common/column_left/before',
		'action' => 'extension/module/mpphotogallery_setting/getMenu'
	], [
		'trigger' => 'catalog/view/common/menu/before',
		'action' => 'extension/mpphotogallery/album/mpphotogalleryMenu'

	], [
		'trigger' => 'catalog/view/common/header/after',
		'action' => 'extension/mpphotogallery/album/header'

	], [
		'trigger' => 'catalog/view/common/footer/after',
		'action' => 'extension/mpphotogallery/album/footer'

	], [
		'trigger' => 'catalog/view/product/product/before',
		'action' => 'extension/mpphotogallery/product/product'

	], [
		'trigger' => 'catalog/controller/product/product/before',
		'action' => 'extension/mpphotogallery/product/controllerProduct'

	]];

	private $seo_url;

	public function __construct($registry) {
		parent :: __construct($registry);
		$this->igniteTraitMpPhotoGallery($registry);

		$this->seo_url = new \mpphotogallery\seo_url($registry);
		// 17-march-2023: improvements start

		/* OC2.3 lower when extension folder not exist starts */
		if (VERSION <= '2.2.0.0') {
			foreach ($this->files as $key => $value) {
				// remove extension path from begning
				$this->files[$key] = substr($value['trigger'], strlen($this->extension_path . ''));
			}
		}
		/* OC2.3 lower when extension folder not exist ends */

		/* OC2x layout: extension folder fix starts */
		if (VERSION <= '2.2.0.0') {
			foreach ($this->layouts as $key => $value) {
				// remove extension path from begning
				$this->layouts[$key] = substr($value['path'], strlen($this->extension_path . ''));
			}
		}
		/* OC2x layout: extension folder fix ends */

		/* OC2x module: extension folder fix starts */
		if (VERSION <= '2.2.0.0') {
			foreach ($this->modules as $key => $value) {
				// remove extension path from begning
				$this->modules[$key] = substr($value['path'], strlen($this->extension_path . ''));
			}
		}
		/* OC2x module: extension folder fix ends */

		/* OC2.3x event: view/after fix starts */
		if (VERSION > '2.2.0.0' && VERSION <= '2.3.0.2') {

			foreach ($this->events as $key => $value) {
				if (strpos($value['trigger'], 'admin/') !== false) {
					continue;
				}

				$trigger_parts = explode('/', $value['trigger']);
				$tigger_end = end($trigger_parts);

				$str_part = 'catalog/view/';
				if (strpos($value['trigger'], 'catalog/view') !== false &&  $tigger_end === 'after') {
					$this->events[$key]['trigger'] = $str_part . '*/' . substr($value['trigger'], strlen($str_part));
				}
			}
		}
		/* OC2.3x event: view/after fix ends */

		// 17-march-2023: improvements end
	}

	public function install() {
		// install database tables
		$this->load->model($this->extension_path . 'mpphotogallery/dump');
		$this->{'model_'. $this->extension_model .'mpphotogallery_dump'}->install();

		$this->createEvents($this->events, $this->events_code);

		// Add permissions to extension files dynamically
		$this->addFilesInPermissions($this->files);

		$this->installModules($this->modules);
		$this->addLayouts($this->layouts);

		// 17-march-2023: improvements start
		$this->installDemoData();
		// 17-march-2023: improvements end
	}

	public function uninstall() {
		$this->removeEventsByCode($this->events_code);

	}

	private function installModules($modules) {

		// $this->model_file['extension/module']['obj']

		// hold old _get.extension to restore if any
		$old_get_extension = '';
		if (isset($this->request->get['extension'])) {
			$old_get_extension = $this->request->get['extension'];
		}

		foreach ($modules as $module) {
			$this->request->get['extension'] = 	$module['code'];
			$this->load->controller($this->extension_path . 'extension/module/install');
		}

		// restore old _get.extension if any
		if ($old_get_extension) {
			$this->request->get['extension'] = $old_get_extension;
		}

	}

	private function addLayouts($layouts) {
		$this->load->model('design/layout');

		foreach ($layouts as $layout) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "layout_route WHERE route='". $this->db->escape($layout['path']) ."'");
			if (!$query->num_rows) {
				$layout_route = [];
				$layout_route[] = [
					'store_id' => (int)$this->config->get('config_store_id'),
					'route' => $layout['path']
				];

				$layout_module = [];
				foreach ($layout['module'] as $module) {
					foreach ($module['modules'] as $sort_order => $code) {
						$layout_module[] = [
							'code' => $code,
							'position' => $module['position'],
							'sort_order' => $sort_order,
						];
					}
				}

				$this->model_design_layout->addLayout([
					'name' => $layout['name'],
					'layout_route' => $layout_route,
					'layout_module' => $layout_module
				]);
			}
		}
	}

	private function detectLayoutsForAdd() {
		$layouts = [];

		foreach ($this->layouts as $layout) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "layout_route WHERE route='". $this->db->escape($layout['path']) ."'");
			if (!$query->num_rows) {
				$layouts[] = $layout;
			}
		}
		return $layouts;
	}
	// 17-march-2023: improvements start
	private function layoutsAdded() {
		$layouts = [];

		foreach ($this->layouts as $layout) {
			$sql = $this->db->query("SELECT * FROM " . DB_PREFIX . "layout_route WHERE route='". $this->db->escape($layout['path']) ."'");
			if ($sql->num_rows) {
				$layouts[] = $layout;
			}
		}
		return $layouts;
	}
	// 17-march-2023: improvements end
	public function updateLayouts() {
		$json = [];
		$this->load->language($this->extension_path . 'module/mpphotogallery_setting');

		$this->addLayouts($this->detectLayoutsForAdd());
		$this->session->data['success'] = $this->language->get('text_success_layouts_update');
		$json['redirect'] = str_replace("&amp;", "&", $this->url->link($this->extension_path . 'module/mpphotogallery_setting', $this->token.'=' . $this->session->data[$this->token], true));

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	private function addFilesInPermissions($files) {
		if ($this->user->hasPermission('modify', $this->extension_path . 'module/mpphotogallery_setting')) {
			$this->load->model('user/user_group');
			foreach ($files as $file) {
				// remove list of files from permissions array to avoid troubles
				$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', $file);
				$this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', $file);

				$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', $file);
				$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', $file);
			}
		}
	}

	private function detectFilesForPermissions() {
		$this->load->model('user/user_group');
		$user_group = $this->model_user_user_group->getUserGroup($this->user->getGroupId());

		$files = [];

		foreach ($this->files as $file) {
			if (!in_array($file, $user_group['permission']['access']) || !in_array($file, $user_group['permission']['modify'])) {
				$files[] = $file;
			}
		}

		return $files;
	}

	public function updatePermissions() {
		$json = [];
		$this->load->language($this->extension_path . 'module/mpphotogallery_setting');

		$this->addFilesInPermissions($this->detectFilesForPermissions());
		$this->session->data['success'] = $this->language->get('text_success_files_permission');
		$json['redirect'] = str_replace("&amp;", "&", $this->url->link($this->extension_path . 'module/mpphotogallery_setting', $this->token.'=' . $this->session->data[$this->token], true));

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function moduleIsInstalled($module) {
		if (empty($this->installed['module'])) {
			$this->load->model($this->model_file['extension/extension']['path']);
			$this->installed['module'] = $this->{$this->model_file['extension/extension']['obj']}->getInstalled('module');
		}

		return in_array($module, $this->installed['module']);
	}

	// 17-march-2023: improvements start
	// ajax callable
	public function activateEvents() {
		$json = [];

		if (($this->request->server['REQUEST_METHOD'] == 'GET') && $this->accessValidate() && isset($this->request->get['ae']) && $this->request->get['ae'] == '1') {

			$this->load->language($this->extension_path . 'module/mpphotogallery_setting');
			$this->load->model($this->model_file['extension/event']['path']);

			$disable_events = $this->areEventsDisable($this->events_code);

			if ($disable_events) {
				foreach ($disable_events as $event_id) {
					$this->{$this->model_file['extension/event']['obj']}->enableEvent($event_id);
				}

				$json['success'] = $this->language->get('text_success_activate_events');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	// 17-march-2023: improvements end

	// 17-march-2023: improvements start
	// ajax callable for insert dump.
	public function addDump() {
		$json = [];

		$this->load->language($this->extension_path . 'module/mpphotogallery_setting');

		$this->load->model($this->extension_path . 'mpphotogallery/dump');

		$tables = $this->{'model_'. $this->extension_model .'mpphotogallery_dump'}->detectIfNoData();

		$dumps = $this->{'model_'. $this->extension_model .'mpphotogallery_dump'}->getDumps();
		$linkers = $this->{'model_'. $this->extension_model .'mpphotogallery_dump'}->getLinkers();

		foreach ($dumps as $table => $dump_datas) {
			if (in_array($table, $tables)) {
				$this->{'model_'. $this->extension_model .'mpphotogallery_dump'}->truncate($table);
				if (isset($linkers[$table])) {
					foreach ($linkers[$table] as $tablelinker) {
						$this->{'model_'. $this->extension_model .'mpphotogallery_dump'}->truncate($tablelinker);
					}
				}
				foreach ($dump_datas as $dump_data) {
					$this->{'model_'. $this->extension_model .'mpphotogallery_dump'}->dump($dump_data);
				}
			}
		}

		$this->session->data['success'] = $this->language->get('text_mpphotogallery_success_dump');
		$json['redirect'] = str_replace("&amp;", "&", $this->url->link($this->extension_path . 'module/mpphotogallery_setting', $this->token.'=' . $this->session->data[$this->token], true));

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	// 17-march-2023: improvements end
	public function index() {

		// 17-march-2023: improvements start
		$this->load->model($this->extension_path . 'mpphotogallery/dump');
		$this->{'model_'. $this->extension_model .'mpphotogallery_dump'}->alter();
		// 17-march-2023: improvements end

		$this->load->language($this->extension_path . 'module/mpphotogallery_setting');

		$this->document->addStyle('view/javascript/mpphotogallery/colorpicker/css/bootstrap-colorpicker.css');
		$this->document->addScript('view/javascript/mpphotogallery/colorpicker/js/bootstrap-colorpicker.js');

		// show a alert message for files that are not in premissions list
		if ($this->user->hasPermission('modify', $this->extension_path . 'module/mpphotogallery_setting')) {
			$data['files'] = $this->detectFilesForPermissions();
		} else {
			$data['files'] = [];
		}

		// show a alert message for blog layouts that can be added if want
		if ($this->user->hasPermission('modify', $this->extension_path . 'module/mpphotogallery_setting')) {
			$data['layouts'] = $this->detectLayoutsForAdd();
		} else {
			$data['layouts'] = [];
		}

		// 17-march-2023: improvements start
		$layoutsadded = $this->layoutsAdded();


		$data['text_add_layouts'] = sprintf($this->language->get('text_add_layouts'), $this->url->link('design/layout', 'user_token=' . $this->session->data['user_token']));

		$data['layouts_info'] = [];
		foreach ($this->layouts as $layout) {
			$exists = false;
			foreach ($layoutsadded as $layoutadded) {
				if ($layoutadded['path'] == $layout['path']) {
					$exists = true;
					break;
				}
			}
			$data['layouts_info'][] = [
				'name' => $layout['name'],
				'path' => $layout['path'],
				'exists' => $exists,
			];
		}
		// 17-march-2023: improvements end

		// 17-march-2023: improvements start
		// explicit code for 2x, and 2.3x versions only.
		if (VERSION < '3.0.0.0') {
			$this->getAllLanguageMpPhotoGallery($data);
		}
		// 17-march-2023: improvements end

		// 17-march-2023: improvements start
		$data['text_disable_events'] = '';
		$data['disable_events'] = false;

		if ($this->user->hasPermission('modify', $this->extension_path . 'module/mpphotogallery_setting')) {
			$this->createEvents($this->events, $this->events_code);
			$disable_events = $this->areEventsDisable($this->events_code);
			if ($disable_events) {
				$data['disable_events'] = true;
				$data['text_disable_events'] = $this->language->get('text_disable_events');
			}
		}
		// 17-march-2023: improvements end

		// 17-march-2023: improvements start
		// show a alert message for insert dummy data for quick setup
		if ($this->user->hasPermission('modify', $this->extension_path . 'module/mpphotogallery_setting')) {
			$data['dump'] = $this->{'model_'. $this->extension_model .'mpphotogallery_dump'}->detectIfNoData();
		// $data['dump'] = ['mpphotogallery'];
		} else {
			$data['dump'] = [];
		}

		if ($data['dump']) {
			$dumps = [];
			$linkers = $this->{'model_'. $this->extension_model .'mpphotogallery_dump'}->getLinkers();
			foreach ($data['dump'] as $dump) {
				if (isset($linkers[$dump])) {
					$dumps[] = $dump;
					foreach ($linkers[$dump] as $linker) {
						$dumps[] = $linker;
					}
				}
			}

			$this->load->language($this->extension_path . 'module/mpphotogallery_setting');
			$data['text_mpphotogallery_info_dump'] = sprintf($this->language->get('text_mpphotogallery_info_dump'), implode("<br/>", array_map(function($value) {
					return DB_PREFIX . $value;
				}, $dumps)));
		}
		// 17-march-2023: improvements end

		$data['store_id'] = $store_id = 0;
		if (isset($this->request->get['store_id'])) {
			$data['store_id'] = $store_id = (int)$this->request->get['store_id'];
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			$this->model_setting_setting->editSetting('module_mpphotogallery', $this->request->post, $store_id);

			// SEO URL
			// 17-march-2023: improvements start
			$this->seo_url->save($this->request->post['module_mpphotogallery_album_page'], 'mpphotogallery_album_page', '1');
			$this->seo_url->save($this->request->post['module_mpphotogallery_albumphoto_page'], 'mpphotogallery_albumphoto_page', '1');
			$this->seo_url->save($this->request->post['module_mpphotogallery_albumvideo_page'], 'mpphotogallery_albumvideo_page', '1');
			$this->seo_url->save($this->request->post['module_mpphotogallery_photo_page'], 'mpphotogallery_photo_page', '1');
			// 17-march-2023: improvements end

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link($this->extension_path . 'module/mpphotogallery_setting', $this->token.'=' . $this->session->data[$this->token], true));
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['album_limit'])) {
			$data['error_album_limit'] = $this->error['album_limit'];
		} else {
			$data['error_album_limit'] = '';
		}

		if (isset($this->error['albumphoto_limit'])) {
			$data['error_albumphoto_limit'] = $this->error['albumphoto_limit'];
		} else {
			$data['error_albumphoto_limit'] = '';
		}

		if (isset($this->error['popup_size'])) {
			$data['error_popup_size'] = $this->error['popup_size'];
		} else {
			$data['error_popup_size'] = '';
		}

		if (isset($this->error['video_size'])) {
			$data['error_video_size'] = $this->error['video_size'];
		} else {
			$data['error_video_size'] = '';
		}

		if (isset($this->error['album_size'])) {
			$data['error_album_size'] = $this->error['album_size'];
		} else {
			$data['error_album_size'] = '';
		}
		// 07-05-2022: updation task start
		if (isset($this->error['albumphoto_size'])) {
			$data['error_albumphoto_size'] = $this->error['albumphoto_size'];
		} else {
			$data['error_albumphoto_size'] = '';
		}
		if (isset($this->error['albumvideo_size'])) {
			$data['error_albumvideo_size'] = $this->error['albumvideo_size'];
		} else {
			$data['error_albumvideo_size'] = '';
		}
		// 07-05-2022: updation task end
		if (isset($this->error['albumvideo_limit'])) {
			$data['error_albumvideo_limit'] = $this->error['albumvideo_limit'];
		} else {
			$data['error_albumvideo_limit'] = '';
		}
		// gallery for product task starts
		if (isset($this->error['galleryproduct_description'])) {
			$data['error_galleryproduct_description'] = $this->error['galleryproduct_description'];
		} else {
			$data['error_galleryproduct_description'] = [];
		}
		if (isset($this->error['galleryproduct_size'])) {
			$data['error_galleryproduct_size'] = $this->error['galleryproduct_size'];
		} else {
			$data['error_galleryproduct_size'] = '';
		}
		if (isset($this->error['galleryproduct_photo_size'])) {
			$data['error_galleryproduct_photo_size'] = $this->error['galleryproduct_photo_size'];
		} else {
			$data['error_galleryproduct_photo_size'] = '';
		}
		// gallery for product task ends


		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->token.'=' . $this->session->data[$this->token], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->extension_path . 'module/mpphotogallery_setting', $this->token.'=' . $this->session->data[$this->token], true)
		);

		$data['get_token'] = $this->token;
		$data['token'] = $this->session->data[$this->token];
		$data['extension_path'] = $this->extension_path;
		$data['languages'] = $this->getLanguages();

		// 17-march-2023: improvements start
		$this->load->model('setting/store');
		$stores = $this->model_setting_store->getStores();
		$data['stores'] = [];
		$data['stores'][] = [
			'store_id' => '0',
			'name' => $this->language->get('text_default'),
			'href' => $this->url->link($this->extension_path . 'module/mpphotogallery_setting', $this->token.'=' . $this->session->data[$this->token], true)
		];

		$data['store_name'] = $this->language->get('text_default');

		foreach ($stores as $store) {
			$data['stores'][] = [
				'store_id' => $store['store_id'],
				'name' => $store['name'],
				'href' => $this->url->link($this->extension_path . 'module/mpphotogallery_setting', $this->token.'=' . $this->session->data[$this->token] . '&store_id=' . $store['store_id'], true)
			];
		}
		// 17-march-2023: improvements end

		$data['action'] = $this->url->link($this->extension_path . 'module/mpphotogallery_setting', $this->token.'=' . $this->session->data[$this->token], true);

		$data['cancel'] = $this->url->link($this->extension_page_path, $this->token.'=' . $this->session->data[$this->token] . '&type=module', true);

		$data['menu_at'] = array();
		$data['menu_at'][] = array(
			'text' => $this->language->get('text_header'),
			'value' =>  'header'
		);
		$data['menu_at'][] = array(
			'text' => $this->language->get('text_footer'),
			'value' =>  'footer'
		);
		$data['menu_at'][] = array(
			'text' => $this->language->get('text_header_menu'),
			'value' =>  'header_menu'
		);

		$data['gallery_menus_at'] = array();
		$data['gallery_menus_at'][] = array(
			'text' => $this->language->get('text_gallery_albums'),
			'value' =>  'album'
		);
		$data['gallery_menus_at'][] = array(
			'text' => $this->language->get('text_gallery_album_photo'),
			'value' =>  'album_photo'
		);
		$data['gallery_menus_at'][] = array(
			'text' => $this->language->get('text_gallery_album_video'),
			'value' =>  'album_video'
		);
		$data['gallery_menus_at'][] = array(
			'text' => $this->language->get('text_gallery_album'),
			'value' =>  'selected_album'
		);

		$this->load->model($this->extension_path . 'mpphotogallery/mpgallery');
		$results = $this->{'model_' . $this->extension_model . 'mpphotogallery_mpgallery'}->getMpGalleries();

		$data['mpgallery_albums'] = array();

		foreach ($results as $result) {
			$data['mpgallery_albums'][] = array(
				'mpgallery_id' => $result['mpgallery_id'],
				'title' => $result['title'],
				'ostatus' => $result['status'],
				'status' => $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')
			);
		}


		$module_info = $this->model_setting_setting->getSetting('module_mpphotogallery', $store_id);

		if (isset($this->request->post['module_mpphotogallery_status'])) {
			$data['module_mpphotogallery_status'] = $this->request->post['module_mpphotogallery_status'];
		} elseif (isset($module_info['module_mpphotogallery_status'])) {
			$data['module_mpphotogallery_status'] = $module_info['module_mpphotogallery_status'];
		} else {
			$data['module_mpphotogallery_status'] = '';
		}

		// 17-march-2023: improvements start

		$data['module_mpphotogallery_album_page'] = $this->seo_url->getForm([
			'data' => &$data,
			'error' => &$this->error,
			'input' => 'module_mpphotogallery_album_page',
			'error_key' => 'keyword_mpphotogallery_album_page',
			'seo_query_key' => 'mpphotogallery_album_page',
			'seo_query_value' => '1',
		]);
		$data['module_mpphotogallery_albumphoto_page'] = $this->seo_url->getForm([
			'data' => &$data,
			'error' => &$this->error,
			'input' => 'module_mpphotogallery_albumphoto_page',
			'error_key' => 'keyword_mpphotogallery_albumphoto_page',
			'seo_query_key' => 'mpphotogallery_albumphoto_page',
			'seo_query_value' => '1',
		]);
		$data['module_mpphotogallery_albumvideo_page'] = $this->seo_url->getForm([
			'data' => &$data,
			'error' => &$this->error,
			'input' => 'module_mpphotogallery_albumvideo_page',
			'error_key' => 'keyword_mpphotogallery_albumvideo_page',
			'seo_query_key' => 'mpphotogallery_albumvideo_page',
			'seo_query_value' => '1',
		]);
		$data['module_mpphotogallery_photo_page'] = $this->seo_url->getForm([
			'data' => &$data,
			'error' => &$this->error,
			'input' => 'module_mpphotogallery_photo_page',
			'error_key' => 'keyword_mpphotogallery_photo_page',
			'seo_query_key' => 'mpphotogallery_photo_page',
			'seo_query_value' => '1',
		]);

		$data['text_seo_mpphotogallery_album_page'] = sprintf($this->language->get('text_seo_mpphotogallery_album_page'), $this->extension_path . 'mpphotogallery/album');
		$data['text_seo_mpphotogallery_albumphoto_page'] = sprintf($this->language->get('text_seo_mpphotogallery_albumphoto_page'), $this->extension_path . 'mpphotogallery/album_photo');
		$data['text_seo_mpphotogallery_albumvideo_page'] = sprintf($this->language->get('text_seo_mpphotogallery_albumvideo_page'), $this->extension_path . 'mpphotogallery/album_video');
		$data['text_seo_mpphotogallery_photo_page'] = sprintf($this->language->get('text_seo_mpphotogallery_photo_page'), $this->extension_path . 'mpphotogallery/photo');
		// 17-march-2023: improvements end

		if (isset($this->request->post['module_mpphotogallery_menu_at'])) {
			$data['module_mpphotogallery_menu_at'] = $this->request->post['module_mpphotogallery_menu_at'];
		} elseif (isset($module_info['module_mpphotogallery_menu_at'])) {
			$data['module_mpphotogallery_menu_at'] = $module_info['module_mpphotogallery_menu_at'];
		} else {
			$data['module_mpphotogallery_menu_at'] = array('header', 'header_menu', 'footer');
		}
		if (isset($this->request->post['module_mpphotogallery_menu_at_header'])) {
			$data['module_mpphotogallery_menu_at_header'] = $this->request->post['module_mpphotogallery_menu_at_header'];
		} elseif (isset($module_info['module_mpphotogallery_menu_at_header'])) {
			$data['module_mpphotogallery_menu_at_header'] = $module_info['module_mpphotogallery_menu_at_header'];
		} else {
			$data['module_mpphotogallery_menu_at_header'] = 'album';
		}
		if (isset($this->request->post['module_mpphotogallery_menu_at_footer'])) {
			$data['module_mpphotogallery_menu_at_footer'] = $this->request->post['module_mpphotogallery_menu_at_footer'];
		} elseif (isset($module_info['module_mpphotogallery_menu_at_footer'])) {
			$data['module_mpphotogallery_menu_at_footer'] = $module_info['module_mpphotogallery_menu_at_footer'];
		} else {
			$data['module_mpphotogallery_menu_at_footer'] = 'album';
		}
		if (isset($this->request->post['module_mpphotogallery_menuheader_album'])) {
			$data['module_mpphotogallery_menuheader_album'] = $this->request->post['module_mpphotogallery_menuheader_album'];
		} elseif (isset($module_info['module_mpphotogallery_menuheader_album'])) {
			$data['module_mpphotogallery_menuheader_album'] = $module_info['module_mpphotogallery_menuheader_album'];
		} else {
			$data['module_mpphotogallery_menuheader_album'] = '';
		}
		if (isset($this->request->post['module_mpphotogallery_menufooter_album'])) {
			$data['module_mpphotogallery_menufooter_album'] = $this->request->post['module_mpphotogallery_menufooter_album'];
		} elseif (isset($module_info['module_mpphotogallery_menufooter_album'])) {
			$data['module_mpphotogallery_menufooter_album'] = $module_info['module_mpphotogallery_menufooter_album'];
		} else {
			$data['module_mpphotogallery_menufooter_album'] = '';
		}

		if (isset($this->request->post['module_mpphotogallery_album_limit'])) {
			$data['module_mpphotogallery_album_limit'] = $this->request->post['module_mpphotogallery_album_limit'];
		} elseif (isset($module_info['module_mpphotogallery_album_limit'])) {
			$data['module_mpphotogallery_album_limit'] = $module_info['module_mpphotogallery_album_limit'];
		} else {
			$data['module_mpphotogallery_album_limit'] = 20;
		}

		if (isset($this->request->post['module_mpphotogallery_popup_width'])) {
			$data['module_mpphotogallery_popup_width'] = $this->request->post['module_mpphotogallery_popup_width'];
		} elseif (isset($module_info['module_mpphotogallery_popup_width'])) {
			$data['module_mpphotogallery_popup_width'] = $module_info['module_mpphotogallery_popup_width'];
		} else {
			$data['module_mpphotogallery_popup_width'] = 500;
		}

		if (isset($this->request->post['module_mpphotogallery_popup_height'])) {
			$data['module_mpphotogallery_popup_height'] = $this->request->post['module_mpphotogallery_popup_height'];
		} elseif (isset($module_info['module_mpphotogallery_popup_height'])) {
			$data['module_mpphotogallery_popup_height'] = $module_info['module_mpphotogallery_popup_height'];
		} else {
			$data['module_mpphotogallery_popup_height'] = 729;
		}

		if (isset($this->request->post['module_mpphotogallery_color'])) {
			$data['module_mpphotogallery_color'] = $this->request->post['module_mpphotogallery_color'];
		} elseif (isset($module_info['module_mpphotogallery_color'])) {
			$data['module_mpphotogallery_color'] = $module_info['module_mpphotogallery_color'];
		} else {
			$data['module_mpphotogallery_color'] = [];
		}

		if (isset($this->request->post['module_mpphotogallery_social_status'])) {
			$data['module_mpphotogallery_social_status'] = $this->request->post['module_mpphotogallery_social_status'];
		} elseif (isset($module_info['module_mpphotogallery_social_status'])) {
			$data['module_mpphotogallery_social_status'] = $module_info['module_mpphotogallery_social_status'];
		} else {
			$data['module_mpphotogallery_social_status'] = 1;
		}

		if (isset($this->request->post['module_mpphotogallery_album_width'])) {
			$data['module_mpphotogallery_album_width'] = $this->request->post['module_mpphotogallery_album_width'];
		} elseif (isset($module_info['module_mpphotogallery_album_width'])) {
			$data['module_mpphotogallery_album_width'] = $module_info['module_mpphotogallery_album_width'];
		} else {
			$data['module_mpphotogallery_album_width'] = 213;
		}

		if (isset($this->request->post['module_mpphotogallery_album_height'])) {
			$data['module_mpphotogallery_album_height'] = $this->request->post['module_mpphotogallery_album_height'];
		} elseif (isset($module_info['module_mpphotogallery_album_height'])) {
			$data['module_mpphotogallery_album_height'] = $module_info['module_mpphotogallery_album_height'];
		} else {
			$data['module_mpphotogallery_album_height'] = 310;
		}
		// 07-05-2022: updation task start
		if (isset($this->request->post['module_mpphotogallery_albumphoto_width'])) {
			$data['module_mpphotogallery_albumphoto_width'] = $this->request->post['module_mpphotogallery_albumphoto_width'];
		} elseif (isset($module_info['module_mpphotogallery_albumphoto_width'])) {
			$data['module_mpphotogallery_albumphoto_width'] = $module_info['module_mpphotogallery_albumphoto_width'];
		} else {
			$data['module_mpphotogallery_albumphoto_width'] = 213;
		}

		if (isset($this->request->post['module_mpphotogallery_albumphoto_height'])) {
			$data['module_mpphotogallery_albumphoto_height'] = $this->request->post['module_mpphotogallery_albumphoto_height'];
		} elseif (isset($module_info['module_mpphotogallery_albumphoto_height'])) {
			$data['module_mpphotogallery_albumphoto_height'] = $module_info['module_mpphotogallery_albumphoto_height'];
		} else {
			$data['module_mpphotogallery_albumphoto_height'] = 310;
		}
		// 07-05-2022: updation task end
		if (isset($this->request->post['module_mpphotogallery_show_videos'])) {
			$data['module_mpphotogallery_show_videos'] = $this->request->post['module_mpphotogallery_show_videos'];
		} elseif (isset($module_info['module_mpphotogallery_show_videos'])) {
			$data['module_mpphotogallery_show_videos'] = $module_info['module_mpphotogallery_show_videos'];
		} else {
			$data['module_mpphotogallery_show_videos'] = true;
		}

		if (isset($this->request->post['module_mpphotogallery_albumphoto_description'])) {
			$data['module_mpphotogallery_albumphoto_description'] = $this->request->post['module_mpphotogallery_albumphoto_description'];
		} elseif (isset($module_info['module_mpphotogallery_albumphoto_description'])) {
			$data['module_mpphotogallery_albumphoto_description'] = $module_info['module_mpphotogallery_albumphoto_description'];
		} else {
			$data['module_mpphotogallery_albumphoto_description'] = 1;
		}

		if (isset($this->request->post['module_mpphotogallery_album_description'])) {
			$data['module_mpphotogallery_album_description'] = $this->request->post['module_mpphotogallery_album_description'];
		} elseif (isset($module_info['module_mpphotogallery_album_description'])) {
			$data['module_mpphotogallery_album_description'] = $module_info['module_mpphotogallery_album_description'];
		} else {
			$data['module_mpphotogallery_album_description'] = 1;
		}

		if (isset($this->request->post['module_mpphotogallery_albumphoto_limit'])) {
			$data['module_mpphotogallery_albumphoto_limit'] = $this->request->post['module_mpphotogallery_albumphoto_limit'];
		} elseif (isset($module_info['module_mpphotogallery_albumphoto_limit'])) {
			$data['module_mpphotogallery_albumphoto_limit'] = $module_info['module_mpphotogallery_albumphoto_limit'];
		} else {
			$data['module_mpphotogallery_albumphoto_limit'] = 20;
		}

		if (isset($this->request->post['module_mpphotogallery_photo_cursive_font'])) {
			$data['module_mpphotogallery_photo_cursive_font'] = $this->request->post['module_mpphotogallery_photo_cursive_font'];
		} elseif (isset($module_info['module_mpphotogallery_photo_cursive_font'])) {
			$data['module_mpphotogallery_photo_cursive_font'] = $module_info['module_mpphotogallery_photo_cursive_font'];
		} else {
			$data['module_mpphotogallery_photo_cursive_font'] = '';
		}

		if (isset($this->request->post['module_mpphotogallery_albumphoto_cursive_font'])) {
			$data['module_mpphotogallery_albumphoto_cursive_font'] = $this->request->post['module_mpphotogallery_albumphoto_cursive_font'];
		} elseif (isset($module_info['module_mpphotogallery_albumphoto_cursive_font'])) {
			$data['module_mpphotogallery_albumphoto_cursive_font'] = $module_info['module_mpphotogallery_albumphoto_cursive_font'];
		} else {
			$data['module_mpphotogallery_albumphoto_cursive_font'] = '';
		}

		if (isset($this->request->post['module_mpphotogallery_albumvideo_cursive_font'])) {
			$data['module_mpphotogallery_albumvideo_cursive_font'] = $this->request->post['module_mpphotogallery_albumvideo_cursive_font'];
		} elseif (isset($module_info['module_mpphotogallery_albumvideo_cursive_font'])) {
			$data['module_mpphotogallery_albumvideo_cursive_font'] = $module_info['module_mpphotogallery_albumvideo_cursive_font'];
		} else {
			$data['module_mpphotogallery_albumvideo_cursive_font'] = '';
		}

		if (isset($this->request->post['module_mpphotogallery_albumvideo_description'])) {
			$data['module_mpphotogallery_albumvideo_description'] = $this->request->post['module_mpphotogallery_albumvideo_description'];
		} elseif (isset($module_info['module_mpphotogallery_albumvideo_description'])) {
			$data['module_mpphotogallery_albumvideo_description'] = $module_info['module_mpphotogallery_albumvideo_description'];
		} else {
			$data['module_mpphotogallery_albumvideo_description'] = 1;
		}

		if (isset($this->request->post['module_mpphotogallery_albumvideo_limit'])) {
			$data['module_mpphotogallery_albumvideo_limit'] = $this->request->post['module_mpphotogallery_albumvideo_limit'];
		} elseif (isset($module_info['module_mpphotogallery_albumvideo_limit'])) {
			$data['module_mpphotogallery_albumvideo_limit'] = $module_info['module_mpphotogallery_albumvideo_limit'];
		} else {
			$data['module_mpphotogallery_albumvideo_limit'] = 20;
		}
		// 07-05-2022: updation task start
		if (isset($this->request->post['module_mpphotogallery_albumvideo_width'])) {
			$data['module_mpphotogallery_albumvideo_width'] = $this->request->post['module_mpphotogallery_albumvideo_width'];
		} elseif (isset($module_info['module_mpphotogallery_albumvideo_width'])) {
			$data['module_mpphotogallery_albumvideo_width'] = $module_info['module_mpphotogallery_albumvideo_width'];
		} else {
			$data['module_mpphotogallery_albumvideo_width'] = 213;
		}

		if (isset($this->request->post['module_mpphotogallery_albumvideo_height'])) {
			$data['module_mpphotogallery_albumvideo_height'] = $this->request->post['module_mpphotogallery_albumvideo_height'];
		} elseif (isset($module_info['module_mpphotogallery_albumvideo_height'])) {
			$data['module_mpphotogallery_albumvideo_height'] = $module_info['module_mpphotogallery_albumvideo_height'];
		} else {
			$data['module_mpphotogallery_albumvideo_height'] = 310;
		}
		// 07-05-2022: updation task end
		// gallery for product task starts
		if (isset($this->request->post['module_mpphotogallery_galleryproduct_status'])) {
			$data['module_mpphotogallery_galleryproduct_status'] = $this->request->post['module_mpphotogallery_galleryproduct_status'];
		} elseif (isset($module_info['module_mpphotogallery_galleryproduct_status'])) {
			$data['module_mpphotogallery_galleryproduct_status'] = $module_info['module_mpphotogallery_galleryproduct_status'];
		} else {
			$data['module_mpphotogallery_galleryproduct_status'] = 0;
		}
		if (isset($this->request->post['module_mpphotogallery_galleryproduct_description'])) {
			$data['module_mpphotogallery_galleryproduct_description'] = $this->request->post['module_mpphotogallery_galleryproduct_description'];
		} elseif (isset($module_info['module_mpphotogallery_galleryproduct_description'])) {
			$data['module_mpphotogallery_galleryproduct_description'] = (array)$module_info['module_mpphotogallery_galleryproduct_description'];
		} else {
			$data['module_mpphotogallery_galleryproduct_description'] = [];
		}
		if (isset($this->request->post['module_mpphotogallery_galleryproduct_viewas'])) {
			$data['module_mpphotogallery_galleryproduct_viewas'] = $this->request->post['module_mpphotogallery_galleryproduct_viewas'];
		} elseif (isset($module_info['module_mpphotogallery_galleryproduct_viewas'])) {
			$data['module_mpphotogallery_galleryproduct_viewas'] = $module_info['module_mpphotogallery_galleryproduct_viewas'];
		} else {
			$data['module_mpphotogallery_galleryproduct_viewas'] = 'GAP';
		}
		// 07-05-2022: updation task start
		if (isset($this->request->post['module_mpphotogallery_galleryproduct_override_video'])) {
			$data['module_mpphotogallery_galleryproduct_override_video'] = $this->request->post['module_mpphotogallery_galleryproduct_override_video'];
		} elseif (isset($module_info['module_mpphotogallery_galleryproduct_override_video'])) {
			$data['module_mpphotogallery_galleryproduct_override_video'] = $module_info['module_mpphotogallery_galleryproduct_override_video'];
		} else {
			$data['module_mpphotogallery_galleryproduct_override_video'] = '';
		}
		if (isset($this->request->post['module_mpphotogallery_galleryproduct_override_image'])) {
			$data['module_mpphotogallery_galleryproduct_override_image'] = $this->request->post['module_mpphotogallery_galleryproduct_override_image'];
		} elseif (isset($module_info['module_mpphotogallery_galleryproduct_override_image'])) {
			$data['module_mpphotogallery_galleryproduct_override_image'] = $module_info['module_mpphotogallery_galleryproduct_override_image'];
		} else {
			$data['module_mpphotogallery_galleryproduct_override_image'] = '';
		}
		// 07-05-2022: updation task end
		if (isset($this->request->post['module_mpphotogallery_galleryproduct_width'])) {
			$data['module_mpphotogallery_galleryproduct_width'] = $this->request->post['module_mpphotogallery_galleryproduct_width'];
		} elseif (isset($module_info['module_mpphotogallery_galleryproduct_width'])) {
			$data['module_mpphotogallery_galleryproduct_width'] = $module_info['module_mpphotogallery_galleryproduct_width'];
		} else {
			$data['module_mpphotogallery_galleryproduct_width'] = '213';
		}
		if (isset($this->request->post['module_mpphotogallery_galleryproduct_height'])) {
			$data['module_mpphotogallery_galleryproduct_height'] = $this->request->post['module_mpphotogallery_galleryproduct_height'];
		} elseif (isset($module_info['module_mpphotogallery_galleryproduct_height'])) {
			$data['module_mpphotogallery_galleryproduct_height'] = $module_info['module_mpphotogallery_galleryproduct_height'];
		} else {
			$data['module_mpphotogallery_galleryproduct_height'] = '310';
		}
		if (isset($this->request->post['module_mpphotogallery_galleryproduct_photo_width'])) {
			$data['module_mpphotogallery_galleryproduct_photo_width'] = $this->request->post['module_mpphotogallery_galleryproduct_photo_width'];
		} elseif (isset($module_info['module_mpphotogallery_galleryproduct_photo_width'])) {
			$data['module_mpphotogallery_galleryproduct_photo_width'] = $module_info['module_mpphotogallery_galleryproduct_photo_width'];
		} else {
			$data['module_mpphotogallery_galleryproduct_photo_width'] = '200';
		}
		if (isset($this->request->post['module_mpphotogallery_galleryproduct_photo_height'])) {
			$data['module_mpphotogallery_galleryproduct_photo_height'] = $this->request->post['module_mpphotogallery_galleryproduct_photo_height'];
		} elseif (isset($module_info['module_mpphotogallery_galleryproduct_photo_height'])) {
			$data['module_mpphotogallery_galleryproduct_photo_height'] = $module_info['module_mpphotogallery_galleryproduct_photo_height'];
		} else {
			$data['module_mpphotogallery_galleryproduct_photo_height'] = '200';
		}
		if (isset($this->request->post['module_mpphotogallery_galleryproduct_carousel'])) {
			$data['module_mpphotogallery_galleryproduct_carousel'] = $this->request->post['module_mpphotogallery_galleryproduct_carousel'];
		} elseif (isset($module_info['module_mpphotogallery_galleryproduct_carousel'])) {
			$data['module_mpphotogallery_galleryproduct_carousel'] = $module_info['module_mpphotogallery_galleryproduct_carousel'];
		} else {
			$data['module_mpphotogallery_galleryproduct_carousel'] = '';
		}
		if (isset($this->request->post['module_mpphotogallery_galleryproduct_extitle'])) {
			$data['module_mpphotogallery_galleryproduct_extitle'] = $this->request->post['module_mpphotogallery_galleryproduct_extitle'];
		} elseif (isset($module_info['module_mpphotogallery_galleryproduct_extitle'])) {
			$data['module_mpphotogallery_galleryproduct_extitle'] = $module_info['module_mpphotogallery_galleryproduct_extitle'];
		} else {
			$data['module_mpphotogallery_galleryproduct_extitle'] = '';
		}
		// gallery for product task ends
		$data['mtabs'] = $this->load->controller($this->extension_path . 'mpphotogallery/mtabs');
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->viewLoad($this->extension_path . 'module/mpphotogallery_setting', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', $this->extension_path . 'module/mpphotogallery_setting')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		// 17-march-2023: improvements start

		// mpphotogallery_album_page

		$this->request->get['mpphotogallery_album_page'] = 1;
		if (VERSION >= '3.0.0.0') {
			// oc3x
			if (isset($this->request->post['module_mpphotogallery_album_page']) && $this->request->post['module_mpphotogallery_album_page']) {

				$error = $this->seo_url->validate($this->request->post['module_mpphotogallery_album_page'], 'mpphotogallery_album_page');

				if (!empty($error['keyword'])) {
					$this->error['keyword_mpphotogallery_album_page'] = $error['keyword'];
				}
			}
		} else {

			// oc2x
			if (isset($this->request->post['module_mpphotogallery_album_page']) && utf8_strlen($this->request->post['module_mpphotogallery_album_page']) > 0) {
				$error = $this->seo_url->validate($this->request->post['module_mpphotogallery_album_page'], 'mpphotogallery_album_page');
				if (!empty($error['keyword'])) {
					$this->error['keyword_mpphotogallery_album_page'] = $error['keyword'];
				}
			}
		}

		// mpphotogallery_albumphoto_page

		$this->request->get['mpphotogallery_albumphoto_page'] = 1;
		if (VERSION >= '3.0.0.0') {

			// oc3x
			if (isset($this->request->post['module_mpphotogallery_albumphoto_page']) && $this->request->post['module_mpphotogallery_albumphoto_page']) {

				$error = $this->seo_url->validate($this->request->post['module_mpphotogallery_albumphoto_page'], 'mpphotogallery_albumphoto_page');

				if (!empty($error['keyword'])) {
					$this->error['keyword_mpphotogallery_albumphoto_page'] = $error['keyword'];
				}
			}
		} else {

			// oc2x
			if (isset($this->request->post['module_mpphotogallery_albumphoto_page']) && utf8_strlen($this->request->post['module_mpphotogallery_albumphoto_page']) > 0) {
				$error = $this->seo_url->validate($this->request->post['module_mpphotogallery_albumphoto_page'], 'mpphotogallery_albumphoto_page');
				if (!empty($error['keyword'])) {
					$this->error['keyword_mpphotogallery_albumphoto_page'] = $error['keyword'];
				}
			}
		}


		// mpphotogallery_albumvideo_page

		$this->request->get['mpphotogallery_albumvideo_page'] = 1;
		if (VERSION >= '3.0.0.0') {

			// oc3x
			if (isset($this->request->post['module_mpphotogallery_albumvideo_page']) && $this->request->post['module_mpphotogallery_albumvideo_page']) {

				$error = $this->seo_url->validate($this->request->post['module_mpphotogallery_albumvideo_page'], 'mpphotogallery_albumvideo_page');

				if (!empty($error['keyword'])) {
					$this->error['keyword_mpphotogallery_albumvideo_page'] = $error['keyword'];
				}
			}

		} else {

			// oc2x
			if (isset($this->request->post['module_mpphotogallery_albumvideo_page']) && utf8_strlen($this->request->post['module_mpphotogallery_albumvideo_page']) > 0) {
				$error = $this->seo_url->validate($this->request->post['module_mpphotogallery_albumvideo_page'], 'mpphotogallery_albumvideo_page');
				if (!empty($error['keyword'])) {
					$this->error['keyword_mpphotogallery_albumvideo_page'] = $error['keyword'];
				}
			}

		}

		// mpphotogallery_photo_page

		$this->request->get['mpphotogallery_photo_page'] = 1;
		if (VERSION >= '3.0.0.0') {

			// oc3x
			if (isset($this->request->post['module_mpphotogallery_photo_page']) && $this->request->post['module_mpphotogallery_photo_page']) {

				$error = $this->seo_url->validate($this->request->post['module_mpphotogallery_photo_page'], 'mpphotogallery_photo_page');

				if (!empty($error['keyword'])) {
					$this->error['keyword_mpphotogallery_photo_page'] = $error['keyword'];
				}
			}

		} else {

			// oc2x
			if (isset($this->request->post['module_mpphotogallery_photo_page']) && utf8_strlen($this->request->post['module_mpphotogallery_photo_page']) > 0) {
				$error = $this->seo_url->validate($this->request->post['module_mpphotogallery_photo_page'], 'mpphotogallery_photo_page');
				if (!empty($error['keyword'])) {
					$this->error['keyword_mpphotogallery_photo_page'] = $error['keyword'];
				}
			}

		}

		// 17-march-2023: improvements end

		if (!$this->request->post['module_mpphotogallery_album_limit']) {
			$this->error['album_limit'] = $this->language->get('error_album_limit');
		}

		if (!$this->request->post['module_mpphotogallery_albumphoto_limit']) {
			$this->error['albumphoto_limit'] = $this->language->get('error_albumphoto_limit');
		}

		if (!$this->request->post['module_mpphotogallery_popup_width'] || !$this->request->post['module_mpphotogallery_popup_height']) {
			$this->error['popup_size'] = $this->language->get('error_popup_size');
		}

		if (!$this->request->post['module_mpphotogallery_album_width'] || !$this->request->post['module_mpphotogallery_album_height']) {
			$this->error['album_size'] = $this->language->get('error_album_size');
		}
		// 07-05-2022: updation task start
		if (!$this->request->post['module_mpphotogallery_albumphoto_width'] || !$this->request->post['module_mpphotogallery_albumphoto_height']) {
			$this->error['albumphoto_size'] = $this->language->get('error_albumphoto_size');
		}
		if (!$this->request->post['module_mpphotogallery_albumvideo_width'] || !$this->request->post['module_mpphotogallery_albumvideo_height']) {
			$this->error['albumvideo_size'] = $this->language->get('error_albumvideo_size');
		}
		// 07-05-2022: updation task end
		// gallery for product task starts

		// gallery for product task ends

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	protected function accessValidate() {
		if (!$this->user->hasPermission('access', $this->extension_path . 'module/mpphotogallery_setting')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	// event functions starts

	public function getMpPhotoGalleryMenu() {
		$route = 'common/menu';
		$code = '';

		$data = [];
		$data['menus'] = [];
		// $data['menus'][] = [
		// 	'id'       => 'menu-catalog',
		// 	'icon'	   => 'fa-puzzle-piece',
		// 	'name'	   => '',
		// 	'href'     => '',
		// 	'children' => []
		// ];


		$this->getMenu($route, $data, $code);

		return isset($data['menus'][0]) ? $data['menus'][0] : [];
	}

	// 'trigger' => 'admin/view/common/column_left/before',
	public function getMenu(&$route, &$data, &$output) {
		$this->load->language($this->extension_path . 'mpphotogallery/mpgallery_menu');
		$menu = [];
		$children = [];


		if ($this->user->hasPermission('access', $this->extension_path . 'module/mpphotogallery_setting')) {
			$children[] = [
				'name'     => $this->labelEnableDisable((int)$this->config->get('module_mpphotogallery_status')) . ' ' . $this->language->get('text_settings'),
				'icon'	   => 'fa-cog',
				'href'     => $this->url->link($this->extension_path . 'module/mpphotogallery_setting', $this->token.'=' . $this->session->data[$this->token], true),
				'children' => []
			];
		}

		if ($this->user->hasPermission('access', $this->extension_path . 'mpphotogallery/mpgallery')) {
			$children[] = [
				'name'     => $this->language->get('text_mpphotogallery_gallery_add'),
				'icon'	   => 'fa-plus',
				'href'     => $this->url->link($this->extension_path . 'mpphotogallery/mpgallery/add', $this->token.'=' . $this->session->data[$this->token], true),
				'children' => []
			];
			$children[] = [
				'name'     => $this->language->get('text_mpphotogallery_gallery'),
				'icon'	   => 'fa-list',
				'href'     => $this->url->link($this->extension_path . 'mpphotogallery/mpgallery', $this->token.'=' . $this->session->data[$this->token], true),
				'children' => []
			];
		}

		if ($this->moduleIsInstalled('mpgallery') && $this->user->hasPermission('access', $this->extension_path . 'module/mpgallery')) {

			$this->load->model($this->model_file['extension/module']['path']);
			$mpgallery_modules = $this->{$this->model_file['extension/module']['obj']}->getModulesByCode('mpgallery');



			$subchildren = [];
			$subchildren[] = [
				'name'     => $this->language->get('text_mppgm_gallery_add'),
				'href'     => $this->url->link($this->extension_path . 'module/mpgallery', $this->token.'=' . $this->session->data[$this->token], true),
				'children' => []
			];

			foreach ($mpgallery_modules as $mpgallery_module) {

				$mpgallery_module_setting = [];
				if ($mpgallery_module['setting']) {
					$mpgallery_module_setting = json_decode($mpgallery_module['setting'], 1);
				}
				if (isset($mpgallery_module_setting['status'])) {
					$mpgallery_module['name'] = $this->labelEnableDisable((int)$mpgallery_module_setting['status']) . ' ' . $mpgallery_module['name'];
				}

				$subchildren[] = [
					'name'     => $mpgallery_module['name'],
					'href'     => $this->url->link($this->extension_path . 'module/mpgallery', $this->token.'=' . $this->session->data[$this->token] . '&module_id=' . $mpgallery_module['module_id'], true),
					'children' => []
				];
			}

			$children[] = [
				'name'     => $this->language->get('text_mppgm_gallery'),
				'href'     => '',
				'icon'	   => 'fa-puzzle-piece',
				'children' => $subchildren
			];
		}
		if ($this->moduleIsInstalled('mpphoto') && $this->user->hasPermission('access', $this->extension_path . 'module/mpphoto')) {
			$this->load->model($this->model_file['extension/module']['path']);
			$mpgallery_modules = $this->{$this->model_file['extension/module']['obj']}->getModulesByCode('mpphoto');

			$subchildren = [];
			$subchildren[] = [
				'name'     => $this->language->get('text_mppgm_gallery_add'),
				'href'     => $this->url->link($this->extension_path . 'module/mpphoto', $this->token.'=' . $this->session->data[$this->token], true),
				'children' => []
			];
			foreach ($mpgallery_modules as $mpgallery_module) {

				$mpgallery_module_setting = [];
				if ($mpgallery_module['setting']) {
					$mpgallery_module_setting = json_decode($mpgallery_module['setting'], 1);
				}
				if (isset($mpgallery_module_setting['status'])) {
					$mpgallery_module['name'] = $this->labelEnableDisable((int)$mpgallery_module_setting['status']) . ' ' . $mpgallery_module['name'];
				}

				$subchildren[] = [
					'name'     => $mpgallery_module['name'],
					'href'     => $this->url->link($this->extension_path . 'module/mpphoto', $this->token.'=' . $this->session->data[$this->token] . '&module_id=' . $mpgallery_module['module_id'], true),
					'children' => []
				];
			}

			$children[] = [
				'name'     => $this->language->get('text_mppgm_photo'),
				'href'     => '',
				'icon'	   => 'fa-puzzle-piece',
				'children' => $subchildren
			];
		}
		if ($this->moduleIsInstalled('mpvideo') && $this->user->hasPermission('access', $this->extension_path . 'module/mpvideo')) {
			$this->load->model($this->model_file['extension/module']['path']);
			$mpgallery_modules = $this->{$this->model_file['extension/module']['obj']}->getModulesByCode('mpvideo');

			$subchildren = [];
			$subchildren[] = [
				'name'     => $this->language->get('text_mppgm_gallery_add'),
				'href'     => $this->url->link($this->extension_path . 'module/mpvideo', $this->token.'=' . $this->session->data[$this->token], true),
				'children' => []
			];
			foreach ($mpgallery_modules as $mpgallery_module) {

				$mpgallery_module_setting = [];
				if ($mpgallery_module['setting']) {
					$mpgallery_module_setting = json_decode($mpgallery_module['setting'], 1);
				}
				if (isset($mpgallery_module_setting['status'])) {
					$mpgallery_module['name'] = $this->labelEnableDisable((int)$mpgallery_module_setting['status']) . ' ' . $mpgallery_module['name'];
				}

				$subchildren[] = [
					'name'     => $mpgallery_module['name'],
					'href'     => $this->url->link($this->extension_path . 'module/mpvideo', $this->token.'=' . $this->session->data[$this->token] . '&module_id=' . $mpgallery_module['module_id'], true),
					'children' => []
				];
			}

			$children[] = [
				'name'     => $this->language->get('text_mppgm_video'),
				'href'     => '',
				'icon'	   => 'fa-puzzle-piece',
				'children' => $subchildren
			];
		}

		if ($children && $this->moduleIsInstalled('mpphotogallery_setting')) {
			$menu = [
				'id'       => 'menu-mpphotogallery-setting',
				'icon'	   => 'fa-file-image-o',
				'name'     => $this->language->get('text_mpphotogallery'),
				'href'     => '',
				'children' => $children
			];
		}

		// || !$this->config->get('module_mpphotogallery_setting_status')
		if ($menu) {

			$index = 2;
			foreach ($data['menus'] as $key => $value) {
				if ($value['id'] == 'menu-extension') {
					$index = $key;
				}
			}

			array_splice($data['menus'], $index, 0, [$menu]);
		}

	}

	// event functions ends

	// default data starts
	public function installDemoData() {

		$this->load->config('mpphotogallery/mpphotogallery');

		$this->load->model('setting/setting');

		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages([
			'sort' => 'sort_order',
			'order' => 'ASC'
		]);

		// Stores
		$this->load->model('setting/store');
		$stores = $this->model_setting_store->getStores();

		$settings = !empty($this->config->get('module_mpphotogallery_settings')) ? $this->config->get('module_mpphotogallery_settings') : [];

		$_post = $this->parse($settings, $languages, $stores);

		// save setings into database
		$this->model_setting_setting->editSetting('module_mpphotogallery', $_post);
	}

	protected function parse($array, &$languages, &$stores) {
		$find = [
			'[[CONFIG_EMAIL]]',
			'[[CONFIG_LOGO]]',
			'[[CONFIG_NAME]]',
			'[[CONFIG_OWNER]]',
			'[[CONFIG_TELEPHONE]]'
		];
		$replace = [
			'CONFIG_EMAIL' => $this->config->get('config_email'),
			'CONFIG_LOGO' => $this->config->get('config_logo'),
			'CONFIG_NAME' => $this->config->get('config_name'),
			'CONFIG_OWNER' => $this->config->get('config_owner'),
			'CONFIG_TELEPHONE' => $this->config->get('config_telephone')
		];
		foreach ($array as $key => $value) {

			if (is_array($value)) {
				$value = $array[$key] = $this->parse($value, $languages, $stores);
			} else {
				foreach ($find as $fvalue) {
					if (strpos($value, $fvalue) !== false) {
						$value = $array[$key] = str_replace($find, $replace, $value);
					}
				}

				// if ($value === '[CONFIG_EMAIL]') {
				// 	$value = $array[$key] = $this->config->get('config_email');
				// }
				// if ($value === '[CONFIG_LOGO]') {
				// 	$value = $array[$key] = $this->config->get('config_logo');
				// }
			}

			if ($key === '[[LANGUAGE_ID]]') {
				$l = [];

				foreach ($languages as $language) {
					$l[$language['language_id']] = $value;
				}

				$array = $l;
			}

			if ($key === '[[STORE_ID]]') {
				$l = [];

				foreach ($stores as $store) {
					$l[$store['store_id']] = $value;
				}

				$array = $l;
			}
		}

		return $array;
	}

	// default data ends

	// model like functions starts
	public function areEventsDisable($code) {
		$disable_events = [];

		// no events for oc version 2.2.0.0 or below. Only OCMOD.
		if (VERSION <= '2.2.0.0') {
			return $disable_events;
		}

		// get events from db
		$query = $this->db->query("SELECT DISTINCT `event_id` FROM `" . DB_PREFIX . "event` WHERE `code`='" . $this->db->escape($code) . "' AND `status`=0");

		foreach ($query->rows as $key => $value) {
			$disable_events[] = $value['event_id'];
		}

		return $disable_events;
	}

	public function removeEventsByCode($code) {
		// no events for oc version 2.2.0.0 or below. Only OCMOD.
		if (VERSION <= '2.2.0.0') {
			return;
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "event` WHERE `code`='" . $this->db->escape($code) . "'");
	}

	public function removeEvent($event_id) {
		// no events for oc version 2.2.0.0 or below. Only OCMOD.
		if (VERSION <= '2.2.0.0') {
			return;
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "event` WHERE `event_id`='" . (int)$event_id . "'");
	}

	public function createEvents($events, $code) {

		// no events for oc version 2.2.0.0 or below. Only OCMOD.
		if (VERSION <= '2.2.0.0') {
			return;
		}

		$this->load->model($this->model_file['extension/event']['path']);
		$defaults = [
			'status' => 1,
			'sort_order' => 0,
			'description' => '',
		];

		// get events from db
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "event` WHERE `code`='" . $this->db->escape($code) . "'");

		$db_events = [];
		foreach ($query->rows as $key => $value) {
			$triact = "{$value['trigger']}==={$value['action']}";
			$db_events[] = $triact;
		}

		$removed_events_in_db = [];
		$trion = [];
		foreach ($events as $key => $event) {
			$triact = "{$event['trigger']}==={$event['action']}";
			$trion[] = $triact;
			if (!in_array($triact, $db_events)) {
				$removed_events_in_db[] = $event;
			}
		}

		// non required events present in database.
		$non_required_events = [];
		foreach ($query->rows as $key => $value) {
			$triact = "{$value['trigger']}==={$value['action']}";
			if (!in_array($triact, $trion)) {
				$non_required_events[] = $value;
			}
		}

		// delete non required events from database
		foreach ($non_required_events as $key => $value) {
			$this->removeEvent($value['event_id']);
		}

		foreach ($removed_events_in_db as $event) {

			// add default keys in array
			foreach ($defaults as $key => $value) {
				if (!isset($event[$key])) {
					$event[$key] = $value;
				}
			}

			$this->{$this->model_file['extension/event']['obj']}->addEvent($code, $event['trigger'], $event['action'], $event['status'], $event['sort_order']);
		}
	}
	// model like functions ends
}