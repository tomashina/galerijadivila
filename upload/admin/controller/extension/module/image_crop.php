<?php
require_once(DIR_SYSTEM . 'library/equotix/image_crop/controller/equotix.php');

class ControllerExtensionModuleImageCrop extends ControllerEquotix {
    public $version = '2.1.0';
    public $code = 'image_crop';
    public $extension = 'Hover Image';
    public $extension_id = '29';
    public $documentation = 'https://marketinsg.zendesk.com/hc/en-us/articles/360044112111-Image-Crop';
    
    public function index() {   
        $this->load->language('extension/module/image_crop');

        $this->document->setTitle(strip_tags($this->language->get('heading_title')));
        
        $data['heading_title'] = $this->language->get('heading_title');

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        $data['heading_title'] = $this->language->get('heading_title');

        $data['action'] = $this->url->link('extension/module/image_crop/save', 'user_token=' . $this->session->data['user_token'], true);
        $data['clear'] = $this->url->link('extension/module/image_crop/clearImageCache', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        
        $this->load->model('setting/setting');
        
        $setting = $this->model_setting_setting->getSetting('module_image_crop');

        $data['module_image_crop_status'] = $this->getData('module_image_crop_status', $this->request->post, $setting);
        
        $this->output('extension/module/image_crop', $data);
    }

    public function save() {
        $json = [];
        
        $this->load->language('extension/module/image_crop');

        if (!$this->user->hasPermission('modify', 'extension/module/image_crop') && !$this->validated($this->extension_id)) {
            $json['error']['warning'] = $this->language->get('error_permission');
        }

        if (!$json) {
            $this->load->model('setting/setting');
            
            $this->model_setting_setting->editSetting('module_image_crop', $this->request->post);
            
            $this->session->data['success'] = $this->language->get('text_success');
            
            $json['redirect'] = html_entity_decode($this->url->link('extension/module/image_crop', 'user_token=' . $this->session->data['user_token'], true), ENT_QUOTES);
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function clearImageCache() {
        if ($this->user->hasPermission('modify', 'extension/module/image_crop') && $this->validated($this->extension_id)) {
            $this->recursiveDelete(DIR_IMAGE . 'cache/');
            
            @mkdir(DIR_IMAGE . 'cache');
            
            $this->load->language('extension/module/image_crop');
            
            $this->session->data['success'] = $this->language->get('text_cache_cleared');;
            
            $this->response->redirect($this->url->link('extension/module/image_crop', 'user_token=' . $this->session->data['user_token'], true));
        }
    }

    protected function recursiveDelete($directory, $empty = false) {
        if (substr($directory, -1) == '/') {
            $directory = substr($directory, 0, -1);
        }
        
        if (!file_exists($directory) || !is_dir($directory)) {
            return false;
        } elseif(!is_readable($directory)) {
            return false;
        } else {
            $handle = opendir($directory);
            
            while ($contents = readdir($handle)) {
                if ($contents != '.' && $contents != '..') {
                    $path = $directory . '/' . $contents;
                    
                    if (is_dir($path)) {
                        $this->recursiveDelete($path);
                    } else {
                        @unlink($path);
                    }
                }
            }
            
            closedir($handle);
            
            if (!rmdir($directory)) {
                return false;
            }
            
            return true;
        }
    }

    public function install() {
        if (!$this->user->hasPermission('modify', 'extension/extension/module')) {
            return;
        }

        $this->load->model('setting/setting');

        $data = array(
            'module_image_crop_status' => true
        );

        $this->model_setting_setting->editSetting('module_image_crop', $data);

        $this->load->model('setting/event');

        $this->model_setting_event->addEvent('module_image_crop', 'catalog/model/tool/image/resize/before', 'extension/module/image_crop/eventModelToolImageBefore');
    }

    public function uninstall() {
        if (!$this->user->hasPermission('modify', 'extension/extension/module')) {
            return;
        }

        $this->load->model('setting/event');

        $this->model_setting_event->deleteEvent('module_image_crop');
    }
}