<?php

/*
 * IT IS NOT FREE, YOU SHOULD BUY / REFISTER A LICENSE AT HTTPS://MMOSolution.COM
 * CONTACT: toan@MMOSOLUTION.COM 
 * AUTHOR: MMOSOLUTION TEAM AT VIETNAM
 * All code within this file is copyright MMOSOLUTION.COM TEAM | FOUNDED @2012
 * You can not copy or reuse code within this file without written permission.
*/

 class ControllerExtensionModuleMmosPopupAttachManager extends Controller {

    private $images_type = array('jpg', 'jpeg', 'gif', 'png');
    protected $dir_image_upload = 'catalog/attach_data';

    function __construct($registry) {
//global $registry;
        parent::__construct($registry);

        $info_setting = $this->config->get('mmos_attachmanager');
        $filetype = explode(',', $info_setting['filetype']);
        $this->file_ext_allowed = "." . implode(',.', $filetype);
		
		if(!is_dir(DIR_IMAGE . $this->dir_image_upload)){
			mkdir(DIR_IMAGE . $this->dir_image_upload, 0777);
		}
		
    }

    public function index() {
        $this->load->language('extension/module/mmos_popup_attachmanager');

        $info_setting = $this->config->get('mmos_attachmanager');

        $filetype = $info_setting['filetype'];

        $allowfiletype = explode(',', $info_setting['filetype']);

        if (isset($this->request->get['filter_name'])) {
            $filter_name = rtrim(str_replace(array('../', '..\\', '..', '*'), '', $this->request->get['filter_name']), '/');
        } else {
            $filter_name = null;
        }

// Make sure we have the correct directory
        if (isset($this->request->get['directory'])) {
            $directory = rtrim(DIR_IMAGE . $this->dir_image_upload . str_replace(array('../', '..\\', '..'), '', $this->request->get['directory']), '/');
        } else {
            $directory = DIR_IMAGE . $this->dir_image_upload;
        }


        $url = '&user_token=' . $this->session->data['user_token'];


        if (isset($this->request->get['target'])) {
            $url .= '&target=' . $this->request->get['target'];
        }
        if (isset($this->request->get['thumb'])) {
            $url .= '&thumb=' . $this->request->get['thumb'];
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_root'),
            'href' => $this->url->link('extension/module/mmos_popup_attachmanager' . $url, '', 'SSL')
        );

        if (isset($this->request->get['limit'])) {
            $this->session->data['filter_show_limit'] = (int) $this->request->get['limit'];
        }


        if (isset($this->session->data['filter_show_limit'])) {
            $data['filter_show_limit'] = $this->session->data['filter_show_limit'];
        } else {
            $data['filter_show_limit'] = $this->config->get('config_limit_admin');
        }

        $limits = array($this->config->get('config_limit_admin'), 5, 15, 25, 50, 75, 100);

        $data['limits'] = array_unique($limits);

        asort($data['limits']);

        $foldersbreadcrumbs = str_replace(DIR_IMAGE . $this->dir_image_upload, '', $directory);
// var_dump($this->dir_imag);
        if ($foldersbreadcrumbs != '') {
            $folders = explode('/', $foldersbreadcrumbs);

            $endfolder = end($folders);
            $url_bread = '';
            foreach ($folders as $folder) {
                if ($folder != '') {
                    $url_bread .= '/' . $folder;
                    $data['breadcrumbs'][] = array(
                        'text' => html_entity_decode($folder, ENT_QUOTES, 'UTF-8'),
                        'href' => $this->url->link('extension/module/mmos_popup_attachmanager', '&directory=' . urlencode($url_bread) . $url, 'SSL')
                    );
                }
            }
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $data['objects'] = array();

        $this->load->model('tool/image');

// Get directories
        $directories = glob($directory . '/' . $filter_name . '*', GLOB_ONLYDIR);

        if (!$directories) {
            $directories = array();
        }

        $filess = glob($directory . '/' . $filter_name . '*', GLOB_BRACE);

        $files = array();
        foreach ($filess as $key => $value) {

            if (in_array(utf8_strtolower(utf8_substr(strrchr($value, '.'), 1)), $allowfiletype)) {

                array_push($files, $value);
            }
        }

// Merge directories and files
        $objects = array_merge($directories, $files);

//var_dump($objects);
// Get total number of files and directories
        $object_total = count($objects);

// Split the array based on current page number and max number of items per page of 10
        $objects = array_splice($objects, ($page - 1) * $data['filter_show_limit'], $data['filter_show_limit']);

        foreach ($objects as $object) {
            $name = str_split(basename($object), 14);


            if (is_dir($object)) {
                $url = '';

                if (isset($this->request->get['target'])) {
                    $url .= '&target=' . $this->request->get['target'];
                }

                if (isset($this->request->get['thumb'])) {
                    $url .= '&thumb=' . $this->request->get['thumb'];
                }
                $files = glob($object . "/*");

                $data['objects'][] = array(
                    'thumb' => '',
                    'total_files' => count($files),
                    'name' => implode(' ', $name),
                    'type' => 'directory',
                    'path' => utf8_substr($object, utf8_strlen(DIR_IMAGE)),
                    'href' => $this->url->link('extension/module/mmos_popup_attachmanager', 'user_token=' . $this->session->data['user_token'] . '&directory=' . urlencode(utf8_substr($object, utf8_strlen(DIR_IMAGE . $this->dir_image_upload))) . $url, 'SSL')
                );
            } elseif (is_file($object)) {


                if ($this->request->server['HTTPS']) {
                    $server = HTTPS_CATALOG;
                } else {
                    $server = HTTP_CATALOG;
                }

                $size = filesize($object);

                $i = 0;

                $suffix = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');

                while (($size / 1024) > 1) {
                    $size = $size / 1024;
                    $i++;
                }

//                var_dump(utf8_strtolower(utf8_substr(strrchr($object, '.'), 1)));



                $exten = utf8_strtolower(utf8_substr(strrchr($object, '.'), 1));
                $mask = utf8_strtolower(utf8_substr(strrchr($object, '.'), 0));

                if (in_array(utf8_strtolower(utf8_substr(strrchr($object, '.'), 1)), $this->images_type)) {

                    $thumb = $this->model_tool_image->resize(utf8_substr($object, utf8_strlen(DIR_IMAGE)), 100, 100);
                } else {

                    $thumb = $this->model_tool_image->resize('catalog/attached_icon/' . $exten . '.png', 100, 100);
                }

                if (!$thumb) {

                    $thumb = $this->model_tool_image->resize('catalog/attached_icon/default.png', 100, 100);
                }

//$dotfile = end($name);


                $exten = strtolower(substr($object, strrpos($object, '.') + 1));
                $nameddd = str_split(basename(str_replace('.' . $exten, '', basename($object))), 14);

                $data['objects'][] = array(
                    'thumb' => $thumb,
                    'name' => implode(' ', $nameddd) . '.' . $exten,
                    'exten' => $exten,
                    'type' => 'file',
                    'pre_view' => DIR_IMAGE . $object,
                    'size' => round(utf8_substr($size, 0, strpos($size, '.') + 4), 2) . $suffix[$i],
                    'path' => utf8_substr($object, utf8_strlen(DIR_IMAGE)),
                    'href' => $server . 'image/' . utf8_substr($object, utf8_strlen(DIR_IMAGE))
                );
            }
        }

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_no_results'] = $this->language->get('text_no_results');
        $data['text_confirm'] = $this->language->get('text_confirm');

        $data['text_mmosrefesh'] = $this->language->get('text_mmosrefesh');


        $data['entry_search'] = $this->language->get('entry_search');
        $data['entry_folder'] = $this->language->get('entry_folder');

        $data['button_parent'] = $this->language->get('button_parent');
        $data['button_refresh'] = $this->language->get('button_refresh');
        $data['button_upload'] = $this->language->get('button_upload');
        $data['button_folder'] = $this->language->get('button_folder');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['button_search'] = $this->language->get('button_search');
        $data['button_remove'] = $this->language->get('button_remove');

        $data['user_token'] = $this->session->data['user_token'];

        if (isset($this->request->get['directory'])) {
            $data['directory'] = urlencode($this->request->get['directory']);
        } else {
            $data['directory'] = '';
        }

        if (isset($this->request->get['filter_name'])) {
            $data['filter_name'] = $this->request->get['filter_name'];
        } else {
            $data['filter_name'] = '';
        }

// Return the target ID for the file manager to set the value
        if (isset($this->request->get['target'])) {
            $data['target'] = $this->request->get['target'];
        } else {
            $data['target'] = '';
        }

// Return the thumbnail for the file manager to show a thumbnail
        if (isset($this->request->get['thumb'])) {
            $data['thumb'] = $this->request->get['thumb'];
        } else {
            $data['thumb'] = '';
        }

// Parent
        $url = '';

        if (isset($this->request->get['directory'])) {
            $pos = strrpos($this->request->get['directory'], '/');

            if ($pos) {
                $url .= '&directory=' . urlencode(substr($this->request->get['directory'], 0, $pos));
            }
        }

        if (isset($this->request->get['target'])) {
            $url .= '&target=' . $this->request->get['target'];
        }

        if (isset($this->request->get['thumb'])) {
            $url .= '&thumb=' . $this->request->get['thumb'];
        }

        $data['parent'] = $this->url->link('extension/module/mmos_popup_attachmanager', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL');

// Refresh
        $url = '';

        if (isset($this->request->get['directory'])) {
            $url .= '&directory=' . urlencode($this->request->get['directory']);
        }

        if (isset($this->request->get['target'])) {
            $url .= '&target=' . $this->request->get['target'];
        }

        if (isset($this->request->get['thumb'])) {
            $url .= '&thumb=' . $this->request->get['thumb'];
        }

        $data['refresh'] = $this->url->link('extension/module/mmos_popup_attachmanager', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL');

        $url = '';

        if (isset($this->request->get['directory'])) {
            $url .= '&directory=' . urlencode(html_entity_decode($this->request->get['directory'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_name'])) {
            $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['target'])) {
            $url .= '&target=' . $this->request->get['target'];
        }

        if (isset($this->request->get['thumb'])) {
            $url .= '&thumb=' . $this->request->get['thumb'];
        }




        $pagination = new Pagination();
        $pagination->total = $object_total;
        $pagination->page = $page;
        $pagination->limit = $data['filter_show_limit'];
        $pagination->url = $this->url->link('extension/module/mmos_popup_attachmanager', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', 'SSL');

        $data['pagination'] = $pagination->render();
        $this->response->setOutput($this->load->view('extension/module/mmos_popup_attachmanager', $data));
    }

    public function upload() {
        $this->load->language('extension/module/mmos_popup_attachmanager');

        $info_setting = $this->config->get('mmos_attachmanager');

        $filetype = explode(',', $info_setting['filetype']);

        $attachmanager = $info_setting['maxfilesize'];

        $json = array();

// Check user has permission
        if (!$this->user->hasPermission('modify', 'extension/module/mmos_popup_attachmanager')) {
            $json['error'] = $this->language->get('error_permission');
        }

// Make sure we have the correct directory
        if (isset($this->request->get['directory'])) {
            $directory = rtrim(DIR_IMAGE . $this->dir_image_upload . str_replace(array('../', '..\\', '..'), '', $this->request->get['directory']), '/');
        } else {
            $directory = DIR_IMAGE . $this->dir_image_upload;
        }

// Check its a directory
        if (!is_dir($directory)) {
            $json['error'] = $this->language->get('error_directory');
        }

        if (!$json) {
// Sanitize the filename
            $filename = basename(html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8'));
            if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {

// Validate the filename length
                if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 255)) {

                    $json['mmos_error'] = $filename;


                    $json['error'] = $this->language->get('error_filename');
                } elseif (!in_array(utf8_strtolower(utf8_substr(strrchr($filename, '.'), 1)), $filetype)) {

                    $json['mmos_error'] = $filename;


                    $json['error'] = $this->language->get('error_filetype');
                } elseif ($this->request->files['file']['size'] > ($attachmanager * 8 * 1024 * 1024)) {

                    $json['mmos_error'] = $filename;

// convert to byte

                    $json['error'] = $this->language->get('error_file_size');
                }

// Check to see if any PHP files are trying to be uploaded
// $content = file_get_contents($this->request->files['file']['tmp_name']);
// if (preg_match('/\<\?php/i', $content)) {
//    $json['error'] = $this->language->get('error_filetype');
//  }
// Return any upload error
                elseif ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {

                    $json['mmos_error'] = $filename;


                    $json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
                }
            } else {

                $json['mmos_error'] = $filename;

                $json['error'] = $this->language->get('error_upload');
            }
        }

        if (!$json) {
            move_uploaded_file($this->request->files['file']['tmp_name'], $directory . '/' . $filename);
            $json['mmos_success'] = $filename;


            $json['success'] = $this->language->get('text_uploaded');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function folder() {
        $this->load->language('extension/module/mmos_popup_attachmanager');

        $json = array();

// Check user has permission
        if (!$this->user->hasPermission('modify', 'extension/module/mmos_popup_attachmanager')) {
            $json['error'] = $this->language->get('error_permission');
        }

// Make sure we have the correct directory
        if (isset($this->request->get['directory'])) {
            $directory = rtrim(DIR_IMAGE . $this->dir_image_upload . str_replace(array('../', '..\\', '..'), '', $this->request->get['directory']), '/');
        } else {
            $directory = DIR_IMAGE . $this->dir_image_upload;
        }

// Check its a directory
        if (!is_dir($directory)) {
            $json['error'] = $this->language->get('error_directory');
        }

        if (!$json) {
// Sanitize the folder name
            $folder = str_replace(array('../', '..\\', '..'), '', basename(html_entity_decode($this->request->post['folder'], ENT_QUOTES, 'UTF-8')));

// Validate the filename length
            if ((utf8_strlen($folder) < 3) || (utf8_strlen($folder) > 128)) {
                $json['error'] = $this->language->get('error_folder');
            }

// Check if directory already exists or not
            if (is_dir($directory . '/' . $folder)) {
                $json['error'] = $this->language->get('error_exists');
            }
        }

        if (!$json) {
            mkdir($directory . '/' . $folder, 0777);

            $json['success'] = $this->language->get('text_directory');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function delete() {
        $this->load->language('extension/module/mmos_popup_attachmanager');

        $json = array();

// Check user has permission
        if (!$this->user->hasPermission('modify', 'extension/module/mmos_popup_attachmanager')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (isset($this->request->post['path'])) {
            $paths = $this->request->post['path'];
        } else {
            $paths = array();
        }

// Loop through each path to run validations
        foreach ($paths as $path) {
            $path = rtrim(DIR_IMAGE . str_replace(array('../', '..\\', '..'), '', $path), '/');

// Check path exsists
            if ($path == DIR_IMAGE . $this->dir_image_upload) {
                $json['error'] = $this->language->get('error_delete');

                break;
            }
        }

        if (!$json) {
// Loop through each path
            foreach ($paths as $path) {
                $path = rtrim(DIR_IMAGE . str_replace(array('../', '..\\', '..'), '', $path), '/');

// If path is just a file delete it
                if (is_file($path)) {
                    unlink($path);

// If path is a directory beging deleting each file and sub folder
                } elseif (is_dir($path)) {
                    $files = array();

// Make path into an array
                    $path = array($path . '*');

// While the path array is still populated keep looping through
                    while (count($path) != 0) {
                        $next = array_shift($path);

                        foreach (glob($next) as $file) {
// If directory add to path array
                            if (is_dir($file)) {
                                $path[] = $file . '/*';
                            }

// Add the file to the files to be deleted array
                            $files[] = $file;
                        }
                    }

// Reverse sort the file array
                    rsort($files);

                    foreach ($files as $file) {
// If file just delete
                        if (is_file($file)) {
                            unlink($file);

// If directory use the remove directory function
                        } elseif (is_dir($file)) {
                            rmdir($file);
                        }
                    }
                }
            }

            $json['success'] = $this->language->get('text_delete');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function uploadformdrop() {


        if (isset($this->request->get['product_id'])) {
            $product_id = (int) $this->request->get['product_id'];
        } else {
            $product_id = 0;
        }
		$this->load->language('extension/mmosolution/mmosuploadattach');
        $data['file_ext_allowed'] = $this->file_ext_allowed;
        $data['upload_link'] = str_replace('&amp;', '&', $this->url->link('extension/module/mmos_popup_attachmanager/uploaddrop', 'product_id=' . $product_id . '&user_token=' . $this->session->data['user_token'], 'SSL'));
        $data['delete_link'] = str_replace('&amp;', '&', $this->url->link('extension/module/mmos_popup_attachmanager/deletedrop', 'product_id=' . $product_id . '&user_token=' . $this->session->data['user_token'], 'SSL'));
        $data = array_merge($data, $this->load->language('extension/mmosolution/mmosuploadattach', 'product_id=' . $product_id . '&user_token=' . $this->session->data['user_token'], 'SSL'));

        $data['entry_allow_files'] = sprintf($this->language->get('entry_allow_files'), $this->file_ext_allowed);


        if (version_compare(VERSION, '2.2.0.0') < 0) {
            if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/mmosolution/mmosuploadattach')) {
                $template = $this->config->get('config_template') . '/template/mmosolution/mmosuploadattach';
            } else {
                $template = 'default/template/mmosolution/mmosuploadattach';
            }
        } else {
            $template = 'extension/mmosolution/mmosuploadattach';
        }

        return $this->load->view($template, $data);
    }

    public function uploaddrop() {
        $this->load->language('extension/module/mmos_popup_attachmanager');

        $json = array();

        $info_setting = $this->config->get('mmos_attachmanager');

        $filetype = explode(',', $info_setting['filetype']);

        $attachmanager = $info_setting['maxfilesize'];

        if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {
// Sanitize the filename
            $filename = basename(preg_replace('/[^a-zA-Z0-9\.\-\s+]/', '', html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8')));

// Validate the filename length
            if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 64)) {
                $json['error'] = $this->language->get('error_filename');
            }

// Allowed file extension types
            $allowed = array();
            $allowed = explode(',', strtolower($this->file_ext_allowed));

            if (!in_array('.' . strtolower(substr(strrchr($filename, '.'), 1)), $allowed)) {
                $json['error'] = $this->language->get('error_filetype');
            }

// Allowed file mime types
            $allowed = array();

            $mime_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_mime_allowed'));

            $filetypes = explode("\n", $mime_allowed);

            foreach ($filetypes as $filetype) {
                $allowed[] = trim($filetype);
            }

            if (!in_array($this->request->files['file']['type'], $allowed)) {
                $json['error'] = $this->language->get('error_filetype');
            }


// Return any upload error
            if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
                $json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
            } elseif ($this->request->files['file']['size'] > ($attachmanager * 8 * 1024 * 1024)) {
                $json['mmos_error'] = $filename;
// convert to byte
                $json['error'] = $this->language->get('error_file_size');
            }

            if (!is_dir(DIR_IMAGE . $this->dir_image_upload)) {
                if (!mkdir(DIR_IMAGE . $this->dir_image_upload, 0777, true)) {
                    $json['error'] = $this->language->get('error_server');
                }
            }
            if ($this->request->get['product_id'] == 0) {
                if (isset($this->session->data['attach_session_dir'])) {
                    $product_dir = $this->session->data['attach_session_dir'];
                } else {
                    $product_dir = time();
                    // for moving file after added new product
                    $this->session->data['attach_session_dir'] = $product_dir;
                }
            } else {
                $product_dir = $this->request->get['product_id'];
            }

            if (!is_dir(DIR_IMAGE . $this->dir_image_upload . '/' . $product_dir)) {
                if (!mkdir(DIR_IMAGE . $this->dir_image_upload . '/' . $product_dir, 0777, true)) {
                    $json['error'] = $this->language->get('error_server');
                }
            }
        } else {
            $json['error'] = $this->language->get('error_upload');
        }

        if (!$json) {
// never douplicate

            move_uploaded_file($this->request->files['file']['tmp_name'], DIR_IMAGE . $this->dir_image_upload . '/' . $product_dir . '/' . $filename);

            $exten = utf8_strtolower(utf8_substr(strrchr($filename, '.'), 1));
            $mask = utf8_substr(strrchr($filename, '.'), 0);
            $this->load->model('tool/image');
            if (in_array(strtolower($exten), $this->images_type)) {
                $thumb = $this->model_tool_image->resize(utf8_substr(DIR_IMAGE . $this->dir_image_upload . '/' . $product_dir . '/' . $filename, utf8_strlen(DIR_IMAGE)), 100, 100);
            } else {
                $thumb = $this->model_tool_image->resize('catalog/attached_icon/' . $exten . '.png', 100, 100);
            }

            if (!$thumb) {

                $thumb = $this->model_tool_image->resize('catalog/attached_icon/default.png', 100, 100);
            }

// Hide the uploaded file name so people can not link to it directly.
            $json['filename'] = $this->dir_image_upload . '/' . $product_dir . '/' . $filename;

            $json['ext'] = $exten;
            $json['mask'] = str_replace('.' . $exten, '', $filename);
            $json['thumb'] = $thumb;
            $json['user_token'] = md5(DIR_IMAGE . $this->dir_image_upload . '/' . $product_dir . '/' . $filename);
            $json['success'] = $this->language->get('text_upload');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function deletedrop() {
        $json = array();
        $json['success'] = 1;
        if (isset($this->request->get['file']) && is_file(DIR_IMAGE . $this->dir_image_upload . '/' . $this->request->get['file'])) {
            if (!unlink(DIR_IMAGE . $this->dir_image_upload . '/' . $this->request->get['file'])) {
                $json['success'] = 0;
            }
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

}
