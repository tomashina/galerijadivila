<?php
if (version_compare(VERSION,'3.0.0.0','>=' )) {
	define('TEMPLATE_FOLDER', 'oc3');
	define('EXTENSION_BASE', 'marketplace/extension');
	define('TOKEN_NAME', 'user_token');
	define('TEMPLATE_EXTN', '');
	define('EXTN_ROUTE', 'extension/hbseo');
}else if (version_compare(VERSION,'2.2.0.0','<=' )) {
	define('TEMPLATE_FOLDER', 'oc2');
	define('EXTENSION_BASE', 'extension/hbseo');
	define('TOKEN_NAME', 'token');
	define('TEMPLATE_EXTN', '.tpl');
	define('EXTN_ROUTE', 'hbseo');
}else{
	define('TEMPLATE_FOLDER', 'oc2');
	define('EXTENSION_BASE', 'extension/extension');
	define('TOKEN_NAME', 'token');
	define('TEMPLATE_EXTN', '');
	define('EXTN_ROUTE', 'extension/hbseo');
}
define('EXTN_VERSION', '5.1'); 
class ControllerExtensionHbseoHbBrokenlinks extends Controller {
	
	private $error = array(); 
	
	public function index() {   
		$data['extension_version'] =  EXTN_VERSION;
		
		if (isset($this->request->get['store_id'])){
			$data['store_id'] = (int)$this->request->get['store_id'];
		}else{
			$data['store_id'] = 0;
		}
		
		$this->load->language(EXTN_ROUTE.'/hb_brokenlinks');
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/store');
		$data['stores'] = $this->model_setting_store->getStores();

		$this->load->model('setting/setting');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('hb_brokenlinks', $this->request->post, $this->request->get['store_id']);	
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link(EXTN_ROUTE.'/hb_brokenlinks', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME].'&store_id='.$data['store_id'], true));
		}
		
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		
		$text_strings = array(
				'heading_title','text_extension',
				'tab_broken','tab_redirect', 'tab_setup','tab_templates','tab_tools','tab_keyword','tab_replace',
				'column_enable_page','column_page_designer','column_smart_url','column_keyword_url','column_replacer','column_default_url','column_redirect_type','column_query_exclude','column_error_exclude','column_ignore_ip','column_ignore_agent',
				'column_auto_delete',
				'text_error_url','text_redirect_url','text_redirect_type','text_redirect_author','text_error_url_help',
				'tool_redirect_update','tool_assign_default','tool_reset','tool_type_update',
				'button_save','button_cancel'
		);
		
		foreach ($text_strings as $text) {
			$data[$text] = $this->language->get($text);
		}
	
 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/dashboard', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME], true)
   		);
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link(EXTENSION_BASE, TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME] . '&type=hbseo', true)
		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link(EXTN_ROUTE.'/hb_brokenlinks', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME].'&store_id='.$data['store_id'], true)
   		);
		
		$data['action'] = $this->url->link(EXTN_ROUTE.'/hb_brokenlinks', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME].'&store_id='.$data['store_id'], true);
		
		$data['cancel'] = $this->url->link(EXTENSION_BASE, TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME] . '&type=hbseo', true);
		$data['delete'] = $this->url->link(EXTN_ROUTE.'/hb_brokenlinks/delete', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME], true);
		
		$data[TOKEN_NAME] = $this->session->data[TOKEN_NAME];
		$data['base_route'] = EXTN_ROUTE;
		
		
		$store_info = $this->model_setting_setting->getSetting('hb_brokenlinks', $this->request->get['store_id']);
		
		//Broken links
		$data['hb_brokenlinks_sauthor'] = isset($store_info['hb_brokenlinks_sauthor'])?$store_info['hb_brokenlinks_sauthor']:'0';
		$data['hb_brokenlinks_sredirect'] = isset($store_info['hb_brokenlinks_sredirect'])?$store_info['hb_brokenlinks_sredirect']:'0';
		$data['hb_brokenlinks_ssort'] = isset($store_info['hb_brokenlinks_ssort'])?$store_info['hb_brokenlinks_ssort']:'date_modified';
		$data['hb_brokenlinks_sorder'] = isset($store_info['hb_brokenlinks_sorder'])?$store_info['hb_brokenlinks_sorder']:'DESC';
		
		
		//settings
		$data['hb_brokenlinks_excludequery'] = isset($store_info['hb_brokenlinks_excludequery'])?$store_info['hb_brokenlinks_excludequery']:'sort,order';
		$data['hb_brokenlinks_excludeterms'] = isset($store_info['hb_brokenlinks_excludeterms'])?$store_info['hb_brokenlinks_excludeterms']:'robots.txt,module/,favicon.ico';
		$data['hb_brokenlinks_ignoreip'] = isset($store_info['hb_brokenlinks_ignoreip'])?$store_info['hb_brokenlinks_ignoreip']:'';
		$data['hb_brokenlinks_ignoreagents'] = isset($store_info['hb_brokenlinks_ignoreagents'])?$store_info['hb_brokenlinks_ignoreagents']:'';
		$data['hb_brokenlinks_defaulturl'] = isset($store_info['hb_brokenlinks_defaulturl'])?$store_info['hb_brokenlinks_defaulturl']:'';
		$data['hb_brokenlinks_smarturl'] = isset($store_info['hb_brokenlinks_smarturl'])?$store_info['hb_brokenlinks_smarturl']:'';
		$data['hb_brokenlinks_keywordurl'] = isset($store_info['hb_brokenlinks_keywordurl'])?$store_info['hb_brokenlinks_keywordurl']:'';
		$data['hb_brokenlinks_replacer'] = isset($store_info['hb_brokenlinks_replacer'])?$store_info['hb_brokenlinks_replacer']:'';
		$data['hb_brokenlinks_rtype'] = isset($store_info['hb_brokenlinks_rtype'])?$store_info['hb_brokenlinks_rtype']:'301';
		
		$data['hb_brokenlinks_adel_count'] = isset($store_info['hb_brokenlinks_adel_count'])?$store_info['hb_brokenlinks_adel_count']:'4';
		$data['hb_brokenlinks_adel_days'] = isset($store_info['hb_brokenlinks_adel_days'])?$store_info['hb_brokenlinks_adel_days']:'15';
		
		$data['hb_brokenlinks_enablepage'] = isset($store_info['hb_brokenlinks_enablepage'])?$store_info['hb_brokenlinks_enablepage']:'';
		
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		foreach ($data['languages'] as $language){
	 		$language_id = $language['language_id'];	
			$data['hb_brokenlinks_page'][$language_id] =  isset($store_info['hb_brokenlinks_page'.$language_id])?$store_info['hb_brokenlinks_page'.$language_id]:'';
		}
					
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/hbseo/'.TEMPLATE_FOLDER.'/hb_brokenlinks'.TEMPLATE_EXTN, $data));

	}
	
	public function brokenlinks() {		
		$store_id = (int)$this->request->get['store_id'];
		
		if (isset($this->request->get['search'])) {
			$search_link = $this->request->get['search'];
		}else{
			$search_link = false;
		}
		
		$this->load->language(EXTN_ROUTE.'/hb_brokenlinks'); 
		$this->load->model('setting/setting');
		$this->load->model('extension/hbseo/hb_brokenlinks');
		$store_info = $this->model_setting_setting->getSetting('hb_brokenlinks', $this->request->get['store_id']);
		
		$data['hb_brokenlinks_sauthor'] = isset($store_info['hb_brokenlinks_sauthor'])?$store_info['hb_brokenlinks_sauthor']:'0';
		$data['hb_brokenlinks_sredirect'] = isset($store_info['hb_brokenlinks_sredirect'])?$store_info['hb_brokenlinks_sredirect']:'0';
		$data['hb_brokenlinks_ssort'] = isset($store_info['hb_brokenlinks_ssort'])?$store_info['hb_brokenlinks_ssort']:'date_modified';
		$data['hb_brokenlinks_sorder'] = isset($store_info['hb_brokenlinks_sorder'])?$store_info['hb_brokenlinks_sorder']:'DESC';
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		if (isset($this->request->get['search'])) {
			$url .= '&search=' . $this->request->get['search'];
		}
		
		$data = array(
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin'),
			'sauthor' => $data['hb_brokenlinks_sauthor'],
			'sredirect' => $data['hb_brokenlinks_sredirect'],
			'ssort' => $data['hb_brokenlinks_ssort'],
			'sorder' => $data['hb_brokenlinks_sorder'],
			'search_link' => $search_link,
			'store_id'=> $store_id
		);
		
		$text_strings = array('column_error_url','column_redirect_url','column_hits','column_redirect_hits','column_referrer','column_date');
		
		foreach ($text_strings as $text) {
			$data[$text] = $this->language->get($text);
		}

		$data[TOKEN_NAME] = $this->session->data[TOKEN_NAME];	
		
		$reports_total = $this->model_extension_hbseo_hb_brokenlinks->getTotalrecords($data); 		
		$records = $this->model_extension_hbseo_hb_brokenlinks->getrecords($data);
		$data['records'] = array();
		foreach ($records as $record) {
			$data['records'][] = array(
				'id' => $record['id'],
				'error' => urldecode($record['error']),
				'redirect' => urldecode($record['redirect']),
				'type' => $record['type'],
				'author' => $record['author'],
				'hits' => $record['hits'],
				'redirect_hits' => $record['redirect_hits'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($record['date_added'])),
				'date_modified' => date($this->language->get('date_format_short'), strtotime($record['date_modified'])),
				'selected404'      => isset($this->request->post['selected404']) && in_array($result['id'], $this->request->post['selected404'])
			);
		}
		
		$pagination = new Pagination();
		$pagination->total = $reports_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link(EXTN_ROUTE.'/hb_brokenlinks/brokenlinks', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME] . '&store_id='.$store_id.'&page={page}', true);

		$data['pagination'] = $pagination->render();
		$limit = $this->config->get('config_limit_admin');

		$data['results'] = sprintf($this->language->get('text_pagination'), ($pagination->total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($pagination->total - $limit)) ? $pagination->total : ((($page - 1) * $limit) + $limit), $pagination->total, ceil($pagination->total / $limit));

		$this->response->setOutput($this->load->view('extension/hbseo/'.TEMPLATE_FOLDER.'/hb_brokenlinks_records'.TEMPLATE_EXTN, $data));
	}
	
	public function pageredirects() {  
		$store_id = (int)$this->request->get['store_id'];
		
		if (isset($this->request->get['search'])) {
			$search_link = $this->request->get['search'];
		}else{
			$search_link = false;
		}
		
		$this->load->language(EXTN_ROUTE.'/hb_brokenlinks'); 
		$this->load->model('setting/setting');
		$this->load->model('extension/hbseo/hb_brokenlinks');
		$store_info = $this->model_setting_setting->getSetting('hb_brokenlinks', $this->request->get['store_id']);
		
		$data['hb_brokenlinks_sauthor'] = isset($store_info['hb_brokenlinks_sauthor'])?$store_info['hb_brokenlinks_sauthor']:'0';
		$data['hb_brokenlinks_sredirect'] = isset($store_info['hb_brokenlinks_sredirect'])?$store_info['hb_brokenlinks_sredirect']:'0';
		$data['hb_brokenlinks_ssort'] = isset($store_info['hb_brokenlinks_ssort'])?$store_info['hb_brokenlinks_ssort']:'date_modified';
		$data['hb_brokenlinks_sorder'] = isset($store_info['hb_brokenlinks_sorder'])?$store_info['hb_brokenlinks_sorder']:'DESC';
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		if (isset($this->request->get['search'])) {
			$url .= '&search=' . $this->request->get['search'];
		}
		
		$data = array(
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin'),
			'sauthor' => 1,
			'sredirect' => 0,
			'ssort' => $data['hb_brokenlinks_ssort'],
			'sorder' => $data['hb_brokenlinks_sorder'],
			'search_link' => $search_link,
			'store_id'=> $store_id
		);
		
		$text_strings = array('column_error_url','column_redirect_url','column_hits','column_redirect_hits','column_referrer','column_date');
		
		foreach ($text_strings as $text) {
			$data[$text] = $this->language->get($text);
		}
		
		$data['column_error_url'] = 'Links';

		$data[TOKEN_NAME] = $this->session->data[TOKEN_NAME];	
		
		$reports_total = $this->model_extension_hbseo_hb_brokenlinks->getTotalrecords($data); 		
		$records = $this->model_extension_hbseo_hb_brokenlinks->getrecords($data);
		$data['records'] = array();
		foreach ($records as $record) {
			$data['records'][] = array(
				'id' => $record['id'],
				'error' => urldecode($record['error']),
				'redirect' => urldecode($record['redirect']),
				'type' => $record['type'],
				'author' => $record['author'],
				'hits' => $record['hits'],
				'redirect_hits' => $record['redirect_hits'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($record['date_added'])),
				'date_modified' => date($this->language->get('date_format_short'), strtotime($record['date_modified'])),
				'selected200'      => isset($this->request->post['selected200']) && in_array($result['id'], $this->request->post['selected200'])
			);
		}
		
		$pagination = new Pagination();
		$pagination->total = $reports_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link(EXTN_ROUTE.'/hb_brokenlinks/pageredirects', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME] . '&store_id='.$store_id.'&page={page}', true);

		$data['pagination'] = $pagination->render();
		$limit = $this->config->get('config_limit_admin');

		$data['results'] = sprintf($this->language->get('text_pagination'), ($pagination->total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($pagination->total - $limit)) ? $pagination->total : ((($page - 1) * $limit) + $limit), $pagination->total, ceil($pagination->total / $limit));

		$this->response->setOutput($this->load->view('extension/hbseo/'.TEMPLATE_FOLDER.'/hb_brokenlinks_pageredirects'.TEMPLATE_EXTN, $data));
	}
	
	
	public function addlinks(){
		$this->load->language(EXTN_ROUTE.'/hb_brokenlinks'); 
		$this->load->model('extension/hbseo/hb_brokenlinks');
		
		$links = $this->request->post['links'];
		$redirect_url = $this->request->post['redirect'];
		$response = $this->request->post['response'];
		$type = $this->request->post['type'];
		
		if ($type == 0){
			$json['warning'] = 'Please choose the appropriate redirect type!';
		}else{
			$links = explode(',',$links);
		
			foreach ($links as $link){
				$link = trim(html_entity_decode($link));
				$redirect_url = trim($redirect_url);
				
				if (!empty($link) and !empty($redirect_url)){
					$this->model_extension_hbseo_hb_brokenlinks->insertRecord(urlencode($link),urlencode($redirect_url),$response,$type,$this->request->get['store_id']);
					$json['success'] = $this->language->get('text_insert_success');
				}else{
					$json['warning'] = 'Improper Data. Please check all fields!';
				}
			}
		}
		
		$this->response->setOutput(json_encode($json));
	}
	
	public function keywords(){
		$store_id = (int)$this->request->get['store_id'];
		$this->load->model('extension/hbseo/hb_brokenlinks');
		$records = $this->model_extension_hbseo_hb_brokenlinks->getkeywords($store_id);
		$data['records'] = array();
		if ($records) {
			foreach ($records as $record) {
				$data['records'][] = array(
					'id' => $record['id'],
					'keyword' => urldecode($record['keyword']),
					'redirect_url' => urldecode($record['redirect_url']),
					'date_added' => date($this->language->get('date_format_short'), strtotime($record['date_added']))
				);
			}
		}
		
		$this->response->setOutput($this->load->view('extension/hbseo/'.TEMPLATE_FOLDER.'/hb_brokenlinks_keywords'.TEMPLATE_EXTN, $data));

	}
	
	public function url_replacer(){
		$store_id = (int)$this->request->get['store_id'];
		$this->load->model('extension/hbseo/hb_brokenlinks');
		$records = $this->model_extension_hbseo_hb_brokenlinks->getUrlReplacers($store_id);
		$data['records'] = array();
		if ($records) {
			foreach ($records as $record) {
				$data['records'][] = array(
					'id' => $record['id'],
					'match' => urldecode($record['match']),
					'replace' => urldecode($record['replace']),
					'date_added' => date($this->language->get('date_format_short'), strtotime($record['date_added']))
				);
			}
		}
		
		$this->response->setOutput($this->load->view('extension/hbseo/'.TEMPLATE_FOLDER.'/hb_brokenlinks_replacer'.TEMPLATE_EXTN, $data));

	}
	
	public function addkeyword(){
		$this->load->model('extension/hbseo/hb_brokenlinks');
		
		$keyword = trim($this->request->post['keyword']);
		$redirect_url = trim($this->request->post['redirect']);
		

		if (!empty($keyword) and !empty($redirect_url)){
			$this->model_extension_hbseo_hb_brokenlinks->insertKeyword(urlencode($keyword),urlencode($redirect_url),$this->request->get['store_id']);
			$json['success'] = 'Keyword Entry Added';
		}else{
			$json['warning'] = 'Improper Data. Please check all fields!';
		}

		$this->response->setOutput(json_encode($json));
	}
	
	public function addreplacer(){
		$this->load->model('extension/hbseo/hb_brokenlinks');
		
		$matchsting = trim($this->request->post['matchsting']);
		$replacestring = trim($this->request->post['replacestring']);
		
		if (!empty($matchsting) and !empty($replacestring)){
			if (strpos($replacestring,$matchsting) !== false){
				$json['warning'] = 'Replace string cannot contain the match string.';
			}else{
				$this->model_extension_hbseo_hb_brokenlinks->insertReplacer(urlencode($matchsting),urlencode($replacestring),$this->request->get['store_id']);
				$json['success'] = 'Entry Added';
			}
		}else{
			$json['warning'] = 'Improper Data. Please check all fields!';
		}

		$this->response->setOutput(json_encode($json));
	}
	
	public function deletekeyword(){
		$id = $this->request->post['id'];
		$this->db->query("DELETE FROM `" . DB_PREFIX . "error_keyword` WHERE `id` = '".(int)$id."'");
		$json['success'] = 'Keyword Entry Deleted';
		$this->response->setOutput(json_encode($json));
	}
	
	public function deletereplacer(){
		$id = $this->request->post['id'];
		$this->db->query("DELETE FROM `" . DB_PREFIX . "error_replacer` WHERE `id` = '".(int)$id."'");
		$json['success'] = 'Entry Deleted';
		$this->response->setOutput(json_encode($json));
	}
	
	public function delete(){
		$this->load->model('extension/hbseo/hb_brokenlinks');
		
		if (!isset($this->request->post['selected404']) and !isset($this->request->post['selected200'])){
			$json['warning'] = 'No Record Selected!';
		}else{
			$count404 = 0;
			$count200 = 0;
			$json['success'] = '';
			if (isset($this->request->post['selected404'])){
				foreach ($this->request->post['selected404'] as $id) {
					$this->model_extension_hbseo_hb_brokenlinks->deleteRecord($id);
					$count404 = $count404 + 1;
				}
				$json['success'] .= $count404.' BROKEN LINK(S) DELETED.<br>';
			}
			
			if (isset($this->request->post['selected200'])){
				foreach ($this->request->post['selected200'] as $id) {
					$this->model_extension_hbseo_hb_brokenlinks->deleteRecord($id);
					$count200 = $count200 + 1;
				}
				$json['success'] .= $count200.' PAGE REDIRECTS LINK(S) DELETED.';
			}
		}
		
		$this->response->setOutput(json_encode($json));
	}
	
	public function updateredirect(){
		$id = $this->request->post['id'];
		$redirect = urlencode($this->request->post['redirect']);
		
		$this->load->language(EXTN_ROUTE.'/hb_brokenlinks'); 
		$this->load->model('setting/setting');
		$this->load->model('extension/hbseo/hb_brokenlinks');
		$store_info = $this->model_setting_setting->getSetting('hb_brokenlinks', $this->request->get['store_id']);
		
		$hb_brokenlinks_rtype = isset($store_info['hb_brokenlinks_rtype'])?$store_info['hb_brokenlinks_rtype']:'301';
		
		if (!$this->model_extension_hbseo_hb_brokenlinks->isSameRedirect($redirect,$id)){
			if ($this->model_extension_hbseo_hb_brokenlinks->checkRedirect($redirect) == true){
				$this->model_extension_hbseo_hb_brokenlinks->updateRecord($id,$redirect,$hb_brokenlinks_rtype);	
				$json['success'] = sprintf($this->language->get('text_redirect_updated'),urldecode($redirect));
			}else {
				$json['warning'] = 'Redirect URL cannot be a Broken URL';
			}
		}else{
			$json['sameurl'] = 'No change in Redirect URL';
		}
				
		$this->response->setOutput(json_encode($json));
	}
	
	public function referrers() {
		$this->load->language(EXTN_ROUTE.'/hb_brokenlinks'); 
		$this->load->model('extension/hbseo/hb_brokenlinks');

		if (isset($this->request->get['id'])) {
				$id = $this->request->get['id'];
		}
		
		$data['records'] = $this->model_extension_hbseo_hb_brokenlinks->getReferrers($id);
		$data['referrers'] = array();

		if ($data['records']) {
			foreach ($data['records'] as $record) {
				$data['referrers'][] = array(
					'referrer' => (!empty($record['referrer']))? urldecode($record['referrer']) : ' - ',
					'user_agent' => $record['user_agent'],
					'ip' => $record['ip'],
					'datetime' => $record['date_added']
				);
			}
		}

		$data[TOKEN_NAME] = $this->session->data[TOKEN_NAME];
		$data['column_referrer'] = $this->language->get('column_referrer');
		$data['column_useragent'] = $this->language->get('column_useragent');
		$data['column_ip'] = $this->language->get('column_ip');
		$data['column_datetime'] = $this->language->get('column_datetime');
		$data['text_no_results'] = $this->language->get('text_no_results');

		$this->response->setOutput($this->load->view('extension/hbseo/'.TEMPLATE_FOLDER.'/hb_brokenlinks_referrers'.TEMPLATE_EXTN, $data));
	}
	
	public function tool_resetall() {
		$query = $this->db->query("DELETE FROM `" . DB_PREFIX . "error` WHERE store_id = ".(int)$this->request->get['store_id']);
		$query = $this->db->query("DELETE FROM `" . DB_PREFIX . "error_logs`");
		$this->session->data['success'] = 'TRUNCATED ALL DATA';
		$this->response->redirect($this->url->link(EXTN_ROUTE.'/hb_brokenlinks', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME].'&store_id='.$this->request->get['store_id'], true));
	}
	
	public function tool_bulkredirectupdate() {
		$old = $this->request->get['old'];
		$new = $this->request->get['new'];
		
		if (isset($old) and isset($new)){
			$query = $this->db->query("UPDATE `" . DB_PREFIX . "error` SET `redirect` = '".$this->db->escape(urlencode($new))."' WHERE `redirect` = '".$this->db->escape(urlencode($old))."'");
			$this->session->data['success'] = 'UPDATED SUCCESSFULLY';
		}
		$this->response->redirect($this->url->link(EXTN_ROUTE.'/hb_brokenlinks', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME].'&store_id='.$this->request->get['store_id'], true));
	}
	
	public function tool_bulktype() {
		$old = $this->request->get['old'];
		$new = $this->request->get['new'];
		
		if (isset($old) and isset($new)){
			$query = $this->db->query("UPDATE `" . DB_PREFIX . "error` SET `type` = '".(int)$new."' WHERE `type` = '".(int)$old."'");
			$this->session->data['success'] = 'UPDATED SUCCESSFULLY';
		}
		$this->response->redirect($this->url->link(EXTN_ROUTE.'/hb_brokenlinks', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME].'&store_id='.$this->request->get['store_id'], true));
	}
	
	public function tool_bulkdefault() {
		$this->load->model('setting/setting');
		$store_info = $this->model_setting_setting->getSetting('hb_brokenlinks', $this->request->get['store_id']);
		
		$value = isset($store_info['hb_brokenlinks_defaulturl'])?$store_info['hb_brokenlinks_defaulturl']:'';
		$type = isset($store_info['hb_brokenlinks_rtype'])?$store_info['hb_brokenlinks_rtype']:'301';
		
		$query = $this->db->query("UPDATE `" . DB_PREFIX . "error` SET `redirect` = '".$this->db->escape(urlencode($value))."', `type` = '".(int)$type."' WHERE `redirect` IS NULL or trim(redirect) = '' AND store_id = '".(int)$this->request->get['store_id']."'");
		$this->session->data['success'] = 'UPDATED SUCCESSFULLY';
		$this->response->redirect($this->url->link(EXTN_ROUTE.'/hb_brokenlinks', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME].'&store_id='.$this->request->get['store_id'], true));
	}
	
	public function install() { 
		$this->load->model('extension/hbseo/hb_brokenlinks');
		$this->model_extension_hbseo_hb_brokenlinks->install();
	}
	
	public function uninstall() { 
		$this->load->model('extension/hbseo/hb_brokenlinks');
		$this->model_extension_hbseo_hb_brokenlinks->uninstall();
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', EXTN_ROUTE.'/hb_brokenlinks')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}	
	}
	
	
}
?>