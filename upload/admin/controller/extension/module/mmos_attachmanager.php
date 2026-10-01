<?php

/*
 * IT IS NOT FREE, YOU SHOULD BUY / REFISTER A LICENSE AT HTTPS://MMOSolution.COM
 * CONTACT: toan@MMOSOLUTION.COM 
 * AUTHOR: MMOSOLUTION TEAM AT VIETNAM
 * All code within this file is copyright MMOSOLUTION.COM TEAM | FOUNDED @2012
 * You can not copy or reuse code within this file without written permission.
*/

 class ControllerExtensionModuleMmosAttachManager extends Controller {

    private $error = array();
    private $extension = array(
        'MMOS_version' => '1.0',
        'MMOS_code_id' => 'MMOSOC100',
        'type' => 'extension/module',
        'name' => 'mmos_attachmanager',
    );

    public function index() {

        $data['MMOS_version'] = $this->extension['MMOS_version'];
        $data['MMOS_code_id'] = $this->extension['MMOS_code_id'];
        $data['user_token'] = $this->session->data['user_token'];

        $data = array_merge($data, $this->load->language($this->extension['type'] . '/' . $this->extension['name']));
        // $this->load->language($this->extension['type'] . '/' . $this->extension['name']);
        $this->document->setTitle($this->language->get('heading_title1'));
        $data['heading_title'] = $this->language->get('heading_title1');
        $data['help_file_thumbnail'] = sprintf($this->language->get('help_file_thumbnail'), DIR_IMAGE.'catalog/');
        $this->load->model('setting/setting');


        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->request->post[$this->extension['name']]['filetype'] = strtolower($this->request->post[$this->extension['name']]['filetype']);
            $this->model_setting_setting->editSetting($this->extension['name'], $this->request->post);
			
			if(isset($this->request->post['mmos_attachmanager_status'])){
				$module_mmos_attachmanager_status = $this->request->post['mmos_attachmanager_status'];
			}else{
				$module_mmos_attachmanager_status = "0";
			}
		    $this->model_setting_setting->editSetting('module_', array('module_mmos_attachmanager_status' => $module_mmos_attachmanager_status));
			
            $this->session->data['success'] = $this->language->get('text_success');

            if (!isset($this->request->get['stay'])) {
                $this->response->redirect($this->url->link($this->extension['type'] . '/' . $this->extension['name'], 'user_token=' . $this->session->data['user_token'], 'SSL'));
            } else {

                $this->response->redirect($this->url->link($this->extension['type'] . '/' . $this->extension['name'], 'user_token=' . $this->session->data['user_token'], 'SSL'));
            }
        }



        if (isset($this->request->post[$this->extension['name']])) {
            $data[$this->extension['name']] = $this->request->post[$this->extension['name']];
        } else {
            $data[$this->extension['name']] = $this->config->get($this->extension['name']);
        }
        if (isset($this->request->post['mmos_attachmanager_status'])) {
            $data['mmos_attachmanager_status'] = $this->request->post['mmos_attachmanager_status'];
        } else {
            $data['mmos_attachmanager_status'] = $this->config->get('mmos_attachmanager_status');
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


        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title1'),
            'href' => $this->url->link($this->extension['type'] . '/' . $this->extension['name'], 'user_token=' . $this->session->data['user_token'], 'SSL')
        );

        $data['action'] = $this->url->link($this->extension['type'] . '/' . $this->extension['name'], 'user_token=' . $this->session->data['user_token'], 'SSL');

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        $data['user_token'] = $this->session->data['user_token'];

        $data['languages'] = array();
        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();
        foreach ($languages as $key => $language) {
            $data['languages'][$key] = $language;

            if (version_compare(VERSION, '2.2.0.0') < 0) {
                $img_src = 'view/image/flags/' . $language['image'];
            } else {
                $img_src = './language/' . $language['code'] . '/' . $language['code'] . '.png';
            }

            $data['languages'][$key]['flag_img'] = $img_src;
        }

        $data['maxfilesize'] = str_replace('M', '', ini_get('upload_max_filesize'));

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view($this->extension['type'] . '/' . $this->extension['name'], $data));
    }

    public function integrateproduct() {
        $output = array();
        $output['tab_title'] = null;
        $output['tab_content'] = null;
        if ($this->config->get('mmos_attachmanager_status')) {
            $this->load->model('tool/image');
			$this->load->model('extension/module/mmos_attachmanager');
            $this->document->addScript('view/javascript/mmos_attachmanager.js');
            $this->load->language($this->extension['type'] . '/' . $this->extension['name']);
            $data['user_token'] = $this->session->data['user_token'];

            $data['text_attach_file_product_thumb'] = $this->language->get('text_attach_file_product_thumb');
            $data['text_attach_file_product_name'] = $this->language->get('text_attach_file_product_name');
            $data['text_attach_file_product_count'] = $this->language->get('text_attach_file_product_count');
            $data['text_attach_file_product_login'] = $this->language->get('text_attach_file_product_login');
            $data['button_add_attach_file_product'] = $this->language->get('button_add_attach_file_product');
            $data['button_add_attach_exten_link'] = $this->language->get('button_add_attach_exten_link');
            $data['tab_attach_internal'] = $this->language->get('tab_attach_internal');
            $data['tab_attach_external'] = $this->language->get('tab_attach_external');
            $data['text_attach_extend_link_name'] = $this->language->get('text_attach_extend_link_name');
            $data['text_attach_extend_link_download'] = $this->language->get('text_attach_extend_link_download');
            $data['button_config_attachments'] = $this->language->get('button_config_attachments');
            $data['button_remove'] = $this->language->get('button_remove');
            $attach_info = $this->config->get($this->extension['name']);
            $data['attach_info_config'] = $attach_info;
            $filetype = explode(',', $attach_info['filetype']);
            $data['no_image'] = $this->model_tool_image->resize('no_image.png', 100, 100);
            $data['exten_links'] = array();

            if (isset($this->request->post['exten_link'])) {
                $data['exten_links'] = $this->request->post['exten_link'];
            } elseif (isset($this->request->get['product_id'])) {
                $data['exten_links'] = $this->model_extension_module_mmos_attachmanager->getLinkdownload($this->request->get['product_id']);
            }

            if (isset($this->request->post['product_attach'])) {
                $product_attachs = $this->request->post['product_attach'];
            } elseif (isset($this->request->get['product_id'])) {
                $product_attachs = $this->model_extension_module_mmos_attachmanager->getProductattachmanager($this->request->get['product_id']);
                $data['exten_links'] = $this->model_extension_module_mmos_attachmanager->getLinkdownload($this->request->get['product_id']);
            } else {
                $product_attachs = array();
            }
            $data['product_attachs'] = array();
            $mmos_images_type = array('jpg', 'jpeg', 'gif', 'png');
            foreach ($product_attachs as $product_attach) {
                if (file_exists(DIR_IMAGE . '' . $product_attach['filename'])) {
                    $exten = strtolower(substr($product_attach['filename'], strrpos($product_attach['filename'], '.') + 1));
                    if (in_array($exten, $filetype)) {

                        if (in_array($exten, $mmos_images_type)) {
                            $thumb = $this->model_tool_image->resize($product_attach['filename'], 100, 100);
                        } else {
                            $thumb = $this->model_tool_image->resize('catalog/attached_icon/' . $exten . '.png', 100, 100);
                        }
                    } else {
                        $thumb = $this->model_tool_image->resize('catalog/attached_icon/default.png', 100, 100);
                    }
                    if (empty($product_attach['login_required'])) {
                        $product_attach['login_required'] = '';
                    }
                    if (empty($product_attach['download'])) {
                        $product_attach['download'] = '';
                    }
                    $data['product_attachs'][] = array(
						'product_attach_file_id' => $product_attach['product_attach_file_id'],
                        'thumb' => $thumb,
                        'filename' => $product_attach['filename'],
                        'mask' => $product_attach['mask'],
                        'ext' => $exten,
                        'login_required' => $product_attach['login_required'],
                        'download' => $product_attach['download'],
						'sort_order' => $product_attach['sort_order']
                    );
                }
            }
            $data['drapdrop'] = $this->load->controller('extension/module/mmos_popup_attachmanager/uploadformdrop');
            $output['tab_title'] = html_entity_decode($attach_info['tab_name'][$this->config->get('config_language_id')]);
            $output['tab_content'] = $this->load->view('extension/module/mmos_attachmanager_include', $data);
        }
        return $output;
    }

    private function validate() {

        if (!$this->user->hasPermission('modify', $this->extension['type'] . '/' . $this->extension['name'])) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    public function uninstall() {

        if ($this->user->hasPermission('modify', 'extension/extension/module')) {
            $this->load->model('setting/setting');
            $this->load->model('extension/module/mmos_attachmanager');
            $this->model_setting_setting->deleteSetting($this->extension['name']);
            //$this->model_extension_module_mmos_attachmanager->uninstall();
        }
    }

    public function install() {

        if ($this->user->hasPermission('modify', 'extension/extension/module')) {
            // add user permission

            $this->load->model('extension/module/mmos_attachmanager');

            $this->load->model('setting/setting');

            $this->load->model('localisation/language');

            $this->model_extension_module_mmos_attachmanager->install();

            $typefile = 'dat,7z,arj,audio,avi,bat,bin,bmp,dll,doc,document,file,gif,hlp,htm,html,image,iso,jar,jpeg,jpg,mov,mp3,mpeg,pdf,png,ppt,psd,rar,rpm,software,swf,tar,tif,tiff,txt,video,wav,wma,wmv,xls,zip';

            $languages = $this->model_localisation_language->getLanguages();

            $name_document = array();

            foreach ($languages as $language) {
                $name_document[$language['language_id']] = "<i class='fa fa-download'></i> Document Files";
            }

            $initial = array(
                $this->extension['name'] => array(
                    'status' => 0,
                    'extendlink' => 0,
                    'show_download' => 1,
                    'name_download' => 1,
                    'maxfilesize' => 80,
                    'filetype' => $typefile,
                    'tab_name' => $name_document
                )
            );

            $this->model_setting_setting->editSetting($this->extension['name'], $initial);

            $this->load->model('user/user_group');
			
            $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/mmos_popup_attachmanager');
            $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/mmos_popup_attachmanager');

            //$this->response->redirect($this->url->link($this->extension['type'] . '/' . $this->extension['name'], 'user_token=' . $this->session->data['user_token'], 'SSL'));
        }
    }

}

?>