<?php
// class ControllerExtensioncompgafad extends Controller {

// class ControllerExtensionModulecompgafad extends Controller {
	
//namespace Opencart\Admin\Controller\Extension\compgafad\Module;
//class compgafad extends \Opencart\System\Engine\Controller {

class ControllerExtensionModulecompgafad extends Controller {
	private $error = array();
	private $modpath = 'module/compgafad'; 
	private $modvar = 'model_module_compgafad';
	private $modtpl = 'module/compgafad.tpl';
	private $modname = 'compgafad';
	private $evntcode = 'compgafad';
 	private $modurl = 'extension/module';
	private $token = '';

	public function __construct($registry) {		
		parent::__construct($registry);		
		ini_set("serialize_precision", -1);
		
		if(substr(VERSION,0,3)=='1.5') {
			$this->token = 'token=' . $this->session->data['token'];
			$this->modtpl = 'module/compgafad15X.tpl';
		}
		if(substr(VERSION,0,3)=='2.0') {
			$this->token = 'token=' . $this->session->data['token'];
		}
		if(substr(VERSION,0,3)=='2.1') {
			$this->token = 'token=' . $this->session->data['token'];
		}		
		if(substr(VERSION,0,3)=='2.2') {
			$this->modtpl = 'module/compgafad';
			$this->token = 'token=' . $this->session->data['token'];
		}
		if(substr(VERSION,0,3)=='2.3') {
			$this->modpath = 'extension/module/compgafad';
			$this->modvar = 'model_extension_module_compgafad';
			$this->modtpl = 'extension/module/compgafad';			
			$this->modurl = 'extension/extension';
			$this->token = 'token=' . $this->session->data['token'] . '&type=module';
		}
		if(substr(VERSION,0,3)=='3.0') {			
			$this->modpath = 'extension/module/compgafad';
			$this->modvar = 'model_extension_module_compgafad';
			$this->modtpl = 'extension/module/compgafad30X';
			$this->modname = 'module_compgafad';
			$this->modurl = 'marketplace/extension'; 
			$this->token = 'user_token=' . $this->session->data['user_token'] . '&type=module';
		} 
		if(substr(VERSION,0,3)=='4.0') {
			$this->modpath = 'extension/compgafad/module/compgafad';
			$this->modvar = 'model_extension_compgafad_module_compgafad';
			$this->modtpl = 'extension/compgafad/module/compgafad40X';			
			$this->modname = 'module_compgafad';
			$this->modurl = 'marketplace/extension'; 
			$this->token = 'user_token=' . $this->session->data['user_token'] . '&type=module';
		}
 	} 
	
	public function index() {
		$lang = $this->load->language($this->modpath); 		
		$data = $this->load->language($this->modpath);

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate() && substr(VERSION,0,3)!='4.0') {
			$this->model_setting_setting->editSetting($this->modname, $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			if(substr(VERSION,0,3)=='1.5') {
				$this->redirect($this->url->link($this->modpath, $this->token, true));
			} else {
				$this->response->redirect($this->url->link($this->modpath, $this->token, true));
			}
		}
 
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->session->data['success'])) {
			$data['text_success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['text_success'] = '';
		}

		if(substr(VERSION,0,3)=='1.5') {
			$this->data['breadcrumbs'] = array();
			$this->data['breadcrumbs'][] = array(
				'separator' => ':',
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link($this->modpath, $this->token, true)
			);
		} else {
			$data['breadcrumbs'] = array();
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link($this->modpath, $this->token, true)
			);
		}
		
		$data['action'] = $this->url->link($this->modpath, $this->token, true);
		$data['cancel'] = $this->url->link($this->modurl, $this->token , true); 
		
		if(substr(VERSION,0,5) == '4.0.0' || substr(VERSION,0,5) == '4.0.1') {
			$data['action'] = $this->url->link($this->modpath.'|save', $this->token);
			$data['cancel'] = $this->url->link($this->modurl, $this->token);
		} 
		if(substr(VERSION,0,5) == '4.0.2') {
			$data['action'] = $this->url->link($this->modpath.'.save', $this->token);
			$data['cancel'] = $this->url->link($this->modurl, $this->token);
		}
		
		if(substr(VERSION,0,3)>='3.0') { 
			$data['user_token'] = $this->session->data['user_token'];
		} else {
			$data['token'] = $this->session->data['token'];
		}
		
		$html = array();
		if(substr(VERSION,0,3)=='1.5') { 
			$html = array('<style>.panel-primary {border: 2px solid black; padding: 20px;} .panel-heading { font-size: 15px; font-weight: bold;} .form-group {padding: 15px; width: 90%; display: block; clear: both; border-bottom: 1px solid #ccc; min-height: 30px;} .form-group .control-label { float: left; width: 150px;}</style>');
		}
		$divcls = substr(VERSION,0,3)>='4.0' ? 'row mb-3' : 'form-group';
		$lblcls = substr(VERSION,0,3)>='4.0' ? 'col-form-label' : 'control-label';
		$wellcls = substr(VERSION,0,3)>='4.0' ? 'form-control' : 'well well-sm';
		$grpcls = substr(VERSION,0,3)>='4.0' ? 'input-group-text' : 'input-group-addon';
		 
		$data[$this->modname.'_status'] = $this->setvalue($this->modname.'_status');	
		$data[$this->modname.'_setting'] = $this->setvalue($this->modname.'_setting');
		if(empty($data[$this->modname.'_setting'])) {
			$data[$this->modname.'_setting'] = array();
		}
		
		$data['stores'] = $this->getStores();
		
		foreach($data['stores'] as $store) {
			if(! isset($data[$this->modname.'_setting'][$store['store_id']]['status']) ) {
				$data[$this->modname.'_setting'][$store['store_id']]['status'] = 0;
			}
			
			if(! isset($data[$this->modname.'_setting'][$store['store_id']]['themenm']) ) {
				$data[$this->modname.'_setting'][$store['store_id']]['themenm'] = 'def';
			}
			
			if(! isset($data[$this->modname.'_setting'][$store['store_id']]['gmid']) ) {
				$data[$this->modname.'_setting'][$store['store_id']]['gmid'] = '';
			}
			
			if(! isset($data[$this->modname.'_setting'][$store['store_id']]['prch_adwid']) ) {
				$data[$this->modname.'_setting'][$store['store_id']]['prch_adwid'] = '';
			}
			if(! isset($data[$this->modname.'_setting'][$store['store_id']]['prch_adwlbl']) ) {
				$data[$this->modname.'_setting'][$store['store_id']]['prch_adwlbl'] = '';
			}
			
			if(! isset($data[$this->modname.'_setting'][$store['store_id']]['bgnchk_adwid']) ) {
				$data[$this->modname.'_setting'][$store['store_id']]['bgnchk_adwid'] = '';
			}
			if(! isset($data[$this->modname.'_setting'][$store['store_id']]['bgnchk_adwlbl']) ) {
				$data[$this->modname.'_setting'][$store['store_id']]['bgnchk_adwlbl'] = '';
			}
			
			if(! isset($data[$this->modname.'_setting'][$store['store_id']]['addtc_adwid']) ) {
				$data[$this->modname.'_setting'][$store['store_id']]['addtc_adwid'] = '';
			}
			if(! isset($data[$this->modname.'_setting'][$store['store_id']]['addtc_adwlbl']) ) {
				$data[$this->modname.'_setting'][$store['store_id']]['addtc_adwlbl'] = '';
			}
			
			if(! isset($data[$this->modname.'_setting'][$store['store_id']]['signup_adwid']) ) {
				$data[$this->modname.'_setting'][$store['store_id']]['signup_adwid'] = '';
			}
			if(! isset($data[$this->modname.'_setting'][$store['store_id']]['signup_adwlbl']) ) {
				$data[$this->modname.'_setting'][$store['store_id']]['signup_adwlbl'] = '';
			}
			
			if(substr(VERSION,0,3)>='4.0') {
				$html[] = sprintf('<div class="card"><div class="card-body"><h3 class="card-title">%s</h3>', $store['name']);
			} else {
				$html[] = sprintf('<div class="panel panel-primary"><div class="panel-heading">%s</div><div class="panel-body">', $store['name']);
			}
			
			$sel1 = $data[$this->modname.'_setting'][$store['store_id']]['status'] == 1 ? 'checked="checked"' : '';
			$sel2 = $data[$this->modname.'_setting'][$store['store_id']]['status'] == 0 ? 'checked="checked"' : '';
			$name = sprintf($this->modname.'_setting[%s][%s]', $store['store_id'], 'status');
			$html[] = sprintf('<div class="'.$divcls.'"> <label class="col-sm-2 '.$lblcls.'">%s</label><div class="col-sm-10"> <label class="radio-inline"> <input type="radio" name="%s" value="1" %s/> %s </label> <label class="radio-inline"> <input type="radio" name="%s" value="0" %s/> %s </label> </div> </div>', $lang['entry_status'], $name, $sel1, $lang['text_yes'], $name, $sel2, $lang['text_no']);
			
			$sel1 = $data[$this->modname.'_setting'][$store['store_id']]['themenm'] == 'def' ? 'checked="checked"' : '';
			$sel2 = $data[$this->modname.'_setting'][$store['store_id']]['themenm'] == 'j2' ? 'checked="checked"' : '';
			$sel3 = $data[$this->modname.'_setting'][$store['store_id']]['themenm'] == 'j3' ? 'checked="checked"' : '';
			$name = sprintf($this->modname.'_setting[%s][%s]', $store['store_id'], 'themenm');
			$html[] = sprintf('<div class="'.$divcls.'"> <label class="col-sm-2 '.$lblcls.'">%s</label><div class="col-sm-10"> <label class="radio-inline"> <input type="radio" name="%s" value="def" %s/> Default </label> <label class="radio-inline"> <input type="radio" name="%s" value="j2" %s/> Journal2 </label> <label class="radio-inline"> <input type="radio" name="%s" value="j3" %s/> Journal3 </label> </div> </div>', $lang['entry_themenm'], $name, $sel1, $name, $sel2, $name, $sel3);
			
			$val = $data[$this->modname.'_setting'][$store['store_id']]['gmid'];
			$name = sprintf($this->modname.'_setting[%s][%s]', $store['store_id'], 'gmid');
			$html[] = sprintf('<div class="'.$divcls.'"> <label class="col-sm-2 '.$lblcls.'">%s</label><div class="col-sm-6"> <input type="text" name="%s" value="%s" class="form-control"/> %s </div> </div>', $lang['entry_gmid'], $name, $val, $lang['entry_g_help']);
			
			$html[] = $lang['entry_gadw'];
			$html[] = $lang['entry_prch'];
			
			$val = $data[$this->modname.'_setting'][$store['store_id']]['prch_adwid'];
			$name = sprintf($this->modname.'_setting[%s][%s]', $store['store_id'], 'prch_adwid');
			$html[] = sprintf('<div class="'.$divcls.'"> <label class="col-sm-2 '.$lblcls.'">%s</label><div class="col-sm-6"> <input type="text" name="%s" value="%s" class="form-control"/> </div> </div>', $lang['entry_adwid'], $name, $val);
			
			$val = $data[$this->modname.'_setting'][$store['store_id']]['prch_adwlbl'];
			$name = sprintf($this->modname.'_setting[%s][%s]', $store['store_id'], 'prch_adwlbl');
			$html[] = sprintf('<div class="'.$divcls.'"> <label class="col-sm-2 '.$lblcls.'">%s</label><div class="col-sm-6"> <input type="text" name="%s" value="%s" class="form-control"/> </div> </div>', $lang['entry_adwlbl'], $name, $val);
			
			$html[] = $lang['entry_bgnchk'];
			
			$val = $data[$this->modname.'_setting'][$store['store_id']]['bgnchk_adwid'];
			$name = sprintf($this->modname.'_setting[%s][%s]', $store['store_id'], 'bgnchk_adwid');
			$html[] = sprintf('<div class="'.$divcls.'"> <label class="col-sm-2 '.$lblcls.'">%s</label><div class="col-sm-6"> <input type="text" name="%s" value="%s" class="form-control"/> </div> </div>', $lang['entry_adwid'], $name, $val);
			
			$val = $data[$this->modname.'_setting'][$store['store_id']]['bgnchk_adwlbl'];
			$name = sprintf($this->modname.'_setting[%s][%s]', $store['store_id'], 'bgnchk_adwlbl');
			$html[] = sprintf('<div class="'.$divcls.'"> <label class="col-sm-2 '.$lblcls.'">%s</label><div class="col-sm-6"> <input type="text" name="%s" value="%s" class="form-control"/> </div> </div>', $lang['entry_adwlbl'], $name, $val);
			
			$html[] = $lang['entry_addtc'];
			
			$val = $data[$this->modname.'_setting'][$store['store_id']]['addtc_adwid'];
			$name = sprintf($this->modname.'_setting[%s][%s]', $store['store_id'], 'addtc_adwid');
			$html[] = sprintf('<div class="'.$divcls.'"> <label class="col-sm-2 '.$lblcls.'">%s</label><div class="col-sm-6"> <input type="text" name="%s" value="%s" class="form-control"/> </div> </div>', $lang['entry_adwid'], $name, $val);
			
			$val = $data[$this->modname.'_setting'][$store['store_id']]['addtc_adwlbl'];
			$name = sprintf($this->modname.'_setting[%s][%s]', $store['store_id'], 'addtc_adwlbl');
			$html[] = sprintf('<div class="'.$divcls.'"> <label class="col-sm-2 '.$lblcls.'">%s</label><div class="col-sm-6"> <input type="text" name="%s" value="%s" class="form-control"/> </div> </div>', $lang['entry_adwlbl'], $name, $val);
			
			$html[] = $lang['entry_signup'];
			
			$val = $data[$this->modname.'_setting'][$store['store_id']]['signup_adwid'];
			$name = sprintf($this->modname.'_setting[%s][%s]', $store['store_id'], 'signup_adwid');
			$html[] = sprintf('<div class="'.$divcls.'"> <label class="col-sm-2 '.$lblcls.'">%s</label><div class="col-sm-6"> <input type="text" name="%s" value="%s" class="form-control"/> </div> </div>', $lang['entry_adwid'], $name, $val);
			
			$val = $data[$this->modname.'_setting'][$store['store_id']]['signup_adwlbl'];
			$name = sprintf($this->modname.'_setting[%s][%s]', $store['store_id'], 'signup_adwlbl');
			$html[] = sprintf('<div class="'.$divcls.'"> <label class="col-sm-2 '.$lblcls.'">%s</label><div class="col-sm-6"> <input type="text" name="%s" value="%s" class="form-control"/> </div> </div>', $lang['entry_adwlbl'], $name, $val);
			
			$html[] = '</div></div>';
		}
		
		if(substr(VERSION,0,3)=='1.5') {
			$this->data['fields_html'] = join($html);
			
			$this->template = $this->modtpl;
			$this->children = array(
				'common/header',
				'common/footer'
			);
			$this->response->setOutput($this->render());
		} else {
			$data['fields_html'] = join($html);
			
			$data['header'] = $this->load->controller('common/header');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['footer'] = $this->load->controller('common/footer');
			$this->response->setOutput($this->load->view($this->modtpl, $data));
		}
	}
	
	public function save() {
		$this->load->language($this->modpath);

		$json = array();

		if (!$this->user->hasPermission('modify', $this->modpath)) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('setting/setting');

			$this->model_setting_setting->editSetting($this->modname, $this->request->post);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function install() {
		$query = $this->db->query("SHOW COLUMNS FROM `".DB_PREFIX."order` LIKE 'compgafad_ordflag' ");
		if(!$query->num_rows){
			$this->db->query("ALTER TABLE `".DB_PREFIX."order` ADD `compgafad_ordflag` TINYINT(1) NULL DEFAULT '0' ");
			$this->db->query("UPDATE `" . DB_PREFIX . "order` set compgafad_ordflag = 1");	
		}
 		
		$viewtmp = '';
 		if(substr(VERSION,0,3)=='2.2' || substr(VERSION,0,3)=='2.3') {
			$viewtmp = '*/template/';
		}
		
		$seprtor = '/';
		if(substr(VERSION,0,5) == '4.0.0' || substr(VERSION,0,5) == '4.0.1') {
			$seprtor = '|';
		} 
		if(substr(VERSION,0,5) == '4.0.2') {
			$seprtor = '.';
		}
		
		$this->addtoevent('catalog/view/'.$viewtmp.'common/header/after', $seprtor. 'pageview');
		
		$this->addtoevent('catalog/view/'.$viewtmp.'account/login/after', $seprtor. 'login');
		$this->addtoevent('catalog/controller/account/logout/before', '/logoutbefore');
		$this->addtoevent('catalog/view/'.$viewtmp.'common/success/after', $seprtor. 'logout');
		$this->addtoevent('catalog/controller/account/success/before', '/signupbefore');
		$this->addtoevent('catalog/view/'.$viewtmp.'common/success/after', $seprtor. 'signup');
		$this->addtoevent('catalog/view/'.$viewtmp.'information/contact/after', $seprtor. 'contact');
		
		$this->addtoevent('catalog/controller/checkout/cart/remove/before', '/remove_from_cart');
		$this->addtoevent('catalog/view/'.$viewtmp.'product/product/after', $seprtor. 'viewcont');
		$this->addtoevent('catalog/view/'.$viewtmp.'product/category/after', $seprtor. 'viewcategory');
		$this->addtoevent('catalog/view/'.$viewtmp.'product/search/after', $seprtor. 'search');
		$this->addtoevent('catalog/view/'.$viewtmp.'checkout/cart/after', $seprtor. 'viewcart');
		$this->addtoevent('catalog/view/'.$viewtmp.'checkout/checkout/after', $seprtor. 'beginchk');
		$this->addtoevent('catalog/view/'.$viewtmp.'journal3/checkout/checkout/after', $seprtor. 'beginchk');
		$this->addtoevent('catalog/view/'.$viewtmp.'journal2/checkout/checkout/after', $seprtor. 'beginchk');
		$this->addtoevent('catalog/view/'.$viewtmp.'common/success/after', $seprtor. 'purchase');
 	}

	public function uninstall() {
		if(substr(VERSION,0,3)=='2.2') {
			$this->load->model('extension/event');
			$this->model_extension_event->deleteEvent($this->evntcode);
		}
		if(substr(VERSION,0,3)=='2.3') {
			$this->load->model('extension/event');
			$this->model_extension_event->deleteEvent($this->evntcode);
		}
		if(substr(VERSION,0,3)=='3.0') {			
			$this->load->model('setting/event');
			$this->model_setting_event->deleteEventByCode($this->evntcode);
		} 
		if(substr(VERSION,0,3)=='4.0') {
			$this->load->model('setting/event');
			$this->model_setting_event->deleteEventByCode($this->evntcode);
		}
	}
	public function addtoevent($taregt, $func) {
		if(substr(VERSION,0,3)=='2.2') {
			$this->load->model('extension/event');
			$this->model_extension_event->addEvent($this->evntcode, $taregt, $this->modpath. $func);
		}
		if(substr(VERSION,0,3)=='2.3') {
			$this->load->model('extension/event');
			$this->model_extension_event->addEvent($this->evntcode, $taregt, $this->modpath. $func);
		}
		if(substr(VERSION,0,3)=='3.0') {		
			$this->load->model('setting/event');	
			$this->model_setting_event->addEvent($this->evntcode, $taregt, $this->modpath. $func);
		}
		if(substr(VERSION,0,3)=='4.0') {
			$this->load->model('setting/event');
			$comval = array('code'=> $this->evntcode, 'description' => '', 'status'=>1, 'sort_order'=>1);
			$this->model_setting_event->addEvent(array_merge($comval, array('trigger' => $taregt, 'action' => $this->modpath. $func)));
		}
	}
	
	public function getStores() {
		$result = array();
		$result[0] = array('store_id' => '0', 'name' => $this->config->get('config_name'));
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "store WHERE 1 ORDER BY store_id");
		if($query->num_rows) { 
			foreach($query->rows as $rs) { 
				$result[$rs['store_id']] = $rs;
			}
		}
		return $result;
	} 
	public function getCustomerGroups() {
 		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_group_description WHERE language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY name");
 		return $query->rows;
	}
	
	protected function setvalue($postfield) {
		if (isset($this->request->post[$postfield])) {
			$postfield_value = $this->request->post[$postfield];
		} else {
			$postfield_value = $this->config->get($postfield);
		} 	
 		return $postfield_value;
	}
	
	public function getLang() {
 		$data['languages'] = array();
		$this->load->model('localisation/language');
  		$languages = $this->model_localisation_language->getLanguages();
		foreach($languages as $language) {
			if(substr(VERSION,0,3)>='3.0' || substr(VERSION,0,3)=='2.3' || substr(VERSION,0,3)=='2.2') {
				$imgsrc = "language/".$language['code']."/".$language['code'].".png";
			} else {
				$imgsrc = "view/image/flags/".$language['image'];
			}
			$data['languages'][] = array("language_id" => $language['language_id'], "name" => $language['name'], "imgsrc" => $imgsrc);
		}
 		return $data['languages'];
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', $this->modpath)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}