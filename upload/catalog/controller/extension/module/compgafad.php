<?php
// class ControllerExtensioncompgafad extends Controller {

// class ControllerExtensionModulecompgafad extends Controller {
	
//namespace Opencart\Admin\Controller\Extension\compgafad\Module;
//class compgafad extends \Opencart\System\Engine\Controller {

//$this->load->model($this->modpath);
//$json['script'] = $this->{$this->modvar}->getaddtocart($product_id, $quantity, $option);

class ControllerExtensionModulecompgafad extends Controller {
	private $modpath = 'module/compgafad'; 
	private $modvar = 'model_module_compgafad';
	private $modname = 'compgafad';
	private $status = false;
	private $setting = array();
	
	public function __construct($registry) {
		parent::__construct($registry);
		ini_set("serialize_precision", -1);
		
		if(substr(VERSION,0,3)=='2.3') {
			$this->modpath = 'extension/module/compgafad';
			$this->modvar = 'model_extension_module_compgafad';
		}
		if(substr(VERSION,0,3)=='3.0') {			
			$this->modpath = 'extension/module/compgafad';
			$this->modvar = 'model_extension_module_compgafad';
			$this->modname = 'module_compgafad';
		} 
		if(substr(VERSION,0,3)=='4.0') {
			$this->modpath = 'extension/compgafad/module/compgafad';
			$this->modvar = 'model_extension_compgafad_module_compgafad';
			$this->modname = 'module_compgafad';
		}
		
		$this->setting = $this->getSetting();
		$this->status = ($this->config->get($this->modname.'_status') && $this->setting['status']) ? true : false;	
 	}	
	public function pageview(&$route, &$data, &$output) {
		$this->load->model($this->modpath);
		$fbcode = $this->{$this->modvar}->pageview();
		$fbcode .= '</head>';
 		$output = str_replace('</head>', $fbcode, $output);
	}
	public function login(&$route, &$data, &$output) {
		$this->load->model($this->modpath);
		$fbcode = $this->{$this->modvar}->login();
		$fbcode .= '</body>';
 		$output = str_replace('</body>', $fbcode, $output);
	}
	public function logoutbefore(&$route, &$data) {
		$this->load->model($this->modpath);
		$this->{$this->modvar}->logoutbefore();
	}
	public function logout(&$route, &$data, &$output) {
		$this->load->model($this->modpath);
		$gcode = $this->{$this->modvar}->logout();
		$gcode .= '</body>';
		$output = str_replace('</body>', $gcode, $output);
	}
	public function signupbefore(&$route, &$data) {
		$this->load->model($this->modpath);
		$this->{$this->modvar}->signupbefore();
	}
	public function signup(&$route, &$data, &$output) {
		$this->load->model($this->modpath);
		$gcode = $this->{$this->modvar}->signup();
		$gcode .= '</body>';
		$output = str_replace('</body>', $gcode, $output);
	}
	public function contact(&$route, &$data, &$output) {
		$this->load->model($this->modpath);
		$fbcode = $this->{$this->modvar}->signup();
		$fbcode .= '</body>';
 		$output = str_replace('</body>', $fbcode, $output);
	}
	public function addtocart() {
		$this->load->model($this->modpath);
		$this->{$this->modvar}->addtocart();
	}
	public function addtowishlist() {
		$this->load->model($this->modpath);
		$this->{$this->modvar}->addtowishlist();
	}
	public function viewcont(&$route, &$data, &$output) {
		$this->load->model($this->modpath);
		$fbcode = $this->{$this->modvar}->viewcont();
		$fbcode .= '</body>';
 		$output = str_replace('</body>', $fbcode, $output);
	}
	public function viewcategory(&$route, &$data, &$output) {
		$this->load->model($this->modpath);
		$fbcode = $this->{$this->modvar}->viewcategory();
		$fbcode .= '</body>';
 		$output = str_replace('</body>', $fbcode, $output);
	}
	public function search(&$route, &$data, &$output) {
		$this->load->model($this->modpath);
		$fbcode = $this->{$this->modvar}->search();
		$fbcode .= '</body>';
 		$output = str_replace('</body>', $fbcode, $output);
	}
	public function remove_from_cart(&$route, &$data) {
		$this->load->model($this->modpath);
		$this->{$this->modvar}->remove_from_cart();
	}
	public function viewcart(&$route, &$data, &$output) {
		$this->load->model($this->modpath);
		$fbcode = $this->{$this->modvar}->viewcart();
		$fbcode .= '</body>';
 		$output = str_replace('</body>', $fbcode, $output);
	}
	public function beginchk(&$route, &$data, &$output) {
		$this->load->model($this->modpath);
		$fbcode = $this->{$this->modvar}->beginchk();
		$fbcode .= '</body>';
 		$output = str_replace('</body>', $fbcode, $output);		
	}
	public function purchase(&$route, &$data, &$output) {
		$this->load->model($this->modpath);
		$fbcode = $this->{$this->modvar}->purchase();
		$fbcode .= '</body>';
 		$output = str_replace('</body>', $fbcode, $output);
	}
	
	// Helpers
	public function getSetting() {		
		$storeid = $this->config->get('config_store_id');
		
		$setting = $this->config->get($this->modname.'_setting');		
		
		$setting['status'] = (!isset($setting[$storeid]['status'])) ? false : $setting[$storeid]['status'];
		$setting['themenm'] = (!isset($setting[$storeid]['themenm'])) ? 'def' : $setting[$storeid]['themenm'];
		$setting['gmid'] = (!isset($setting[$storeid]['gmid'])) ? '' : $setting[$storeid]['gmid'];
		$setting['prch_adwid'] = (!isset($setting[$storeid]['prch_adwid'])) ? '' : $setting[$storeid]['prch_adwid'];
		$setting['prch_adwlbl'] = (!isset($setting[$storeid]['prch_adwlbl'])) ? '' : $setting[$storeid]['prch_adwlbl'];
		$setting['bgnchk_adwid'] = (!isset($setting[$storeid]['bgnchk_adwid'])) ? '' : $setting[$storeid]['bgnchk_adwid'];
		$setting['bgnchk_adwlbl'] = (!isset($setting[$storeid]['bgnchk_adwlbl'])) ? '' : $setting[$storeid]['bgnchk_adwlbl'];
		$setting['addtc_adwid'] = (!isset($setting[$storeid]['addtc_adwid'])) ? '' : $setting[$storeid]['addtc_adwid'];
		$setting['addtc_adwlbl'] = (!isset($setting[$storeid]['addtc_adwlbl'])) ? '' : $setting[$storeid]['addtc_adwlbl'];
		$setting['signup_adwid'] = (!isset($setting[$storeid]['signup_adwid'])) ? '' : $setting[$storeid]['signup_adwid'];
		$setting['signup_adwlbl'] = (!isset($setting[$storeid]['signup_adwlbl'])) ? '' : $setting[$storeid]['signup_adwlbl'];
		
		return $setting;		
	}
}