<?php

/*
 * IT IS NOT FREE, YOU SHOULD BUY / REFISTER A LICENSE AT HTTPS://MMOSolution.COM
 * CONTACT: toan@MMOSOLUTION.COM
 * AUTHOR: MMOSOLUTION TEAM AT VIETNAM
 * All code within this file is copyright MMOSOLUTION.COM TEAM | FOUNDED @2012
 * You can not copy or reuse code within this file without written permission.
*/

 class ControllerExtensionModuleMmosAttachManager extends Controller
 {
     public function attachfile()
     {
         $output = array();

         if ($this->config->get('mmos_attachmanager_status')) {
             $attach_info = $this->config->get('mmos_attachmanager');

             $this->load->language('extension/module/mmos_attachmanager');
             $data['attach_button_download'] = $this->language->get('attach_button_download');
             $data['attach_error_login'] = $this->language->get('attach_error_login');
             $data['attach_thumb'] = $this->language->get('attach_thumb');
             $data['attach_filename'] = $this->language->get('attach_filename');
             $data['attach_downloaded'] = $this->language->get('attach_downloaded');
             $data['attach_filesize'] = $this->language->get('attach_filesize');
             $data['attach_action'] = $this->language->get('attach_action');
             $data['attach_linkname'] = $this->language->get('attach_linkname');
             $data['external_link'] = $this->language->get('external_link');


             $data['tab_name'] = html_entity_decode($attach_info['tab_name'][$this->config->get('config_language_id')]);

             $data['show_download'] = $attach_info['show_download'];
             $filetype = explode(',', $attach_info['filetype']);

             $data['product_attachs'] = array();
             $data['exten_links'] = array();

             if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
                 $http_protocol = HTTPS_SERVER;
             } else {
                 $http_protocol = HTTP_SERVER;
             }
             $this->load->model('tool/image');

             //if (isset($product_info)) {
             $results = $this->getProductattachmanager($this->request->get['product_id']);
             $resultlinks = $this->getExtenlinkdownload($this->request->get['product_id']);
             $mmos_images_type = array('jpg', 'jpeg', 'gif', 'png');
             foreach ($results as $result) {
                 if (file_exists(DIR_IMAGE . '' . $result['filename'])) {
                     $size = filesize(DIR_IMAGE . '' . $result['filename']);
                     $i = 0;
                     $suffix = array(
                            'B',
                            'KB',
                            'MB',
                            'GB',
                            'TB',
                            'PB',
                            'EB',
                            'ZB',
                            'YB'
                        );
                     while (($size / 1024) > 1) {
                         $size = $size / 1024;
                         $i++;
                     }


                     $exten = strtolower(substr($result['filename'], strrpos($result['filename'], '.') + 1));
                     $filename = substr($result['filename'], strrpos($result['filename'], '/') + 1);
                     $filename = substr($filename, 0, strrpos($filename, '.'));
                     if (in_array($exten, $filetype)) {
                         if (in_array($exten, $mmos_images_type)) {
                             $thumb = $this->model_tool_image->resize($result['filename'], 100, 100);
                         } else {
                             $thumb = $this->model_tool_image->resize('catalog/attached_icon/' . $exten . '.png', 50, 50);
                         }
                     } else {
                         $thumb = $this->model_tool_image->resize('catalog/attached_icon/default.png', 50, 50);
                     }

                     if ($result['login_required'] == 1) {
                         $url_get_file = $this->customer->isLogged() ? $http_protocol . 'index.php?route=extension/module/mmos_attachmanager/getfile&product_attach_file_id=' . $result['product_attach_file_id'] : "";
                     } else {
                         $url_get_file = $http_protocol . 'index.php?route=extension/module/mmos_attachmanager/getfile&product_attach_file_id=' . $result['product_attach_file_id'];
                     }
                     $data['product_attachs'][] = array(
                            'name' => ($result['mask']) ? $result['mask'] . '.' . $exten : $filename . '.' . $exten,
                            'thumb' => $thumb,
                            'size' => round(substr($size, 0, strpos($size, '.') + 4), 2) . $suffix[$i],
                            'download' => $result['download'],
                            'href' => $url_get_file
                        );
                 }
             }

             foreach ($resultlinks as $resultlink) {
                 if ($resultlink['link_download']) {
                     $thumb_link = $this->model_tool_image->resize('catalog/attached_icon/link.png', 50, 50);
                     if ($resultlink['login_required']) {
                         $link_url = $this->customer->isLogged() ? $resultlink['link_download'] : '';
                     } else {
                         $link_url = $resultlink['link_download'];
                     }

                     $data['exten_links'][] = array(
                            'name' => $resultlink['link_name'],
                            'thumb' => $thumb_link,
                            'href' => $link_url
                        );
                 }
             }
             //  }
             $check_journal_theme = strpos($this->config->get('config_theme'),'journal');
             if ($check_journal_theme !== false) {
                 $data['class_panel'] = '';
             } else {
                 $data['class_panel'] = 'tab-pane tab-content fade';
             }

             $content = $this->load->view('mmosolution/mmos_attachmanager', $data);
         }
         if (!empty($content)) {
             $output['content'] = $content;
             $output['tab_name'] = isset($attach_info['tab_name'][$this->config->get('config_language_id')]) ? html_entity_decode($attach_info['tab_name'][$this->config->get('config_language_id')]) : 'Files';
		 } else {
			  $output['content'] = '';
             $output['tab_name'] = '';
		 }



         return $output;
     }

     public function getfile()
     {
         if ($this->config->get('mmos_attachmanager_status')) {
             $attach_info = $this->config->get('mmos_attachmanager');
             $this->load->model('extension/module/mmos_attachmanager');

             if (isset($this->request->get['product_attach_file_id'])) {
                 $product_attach_file_id = $this->request->get['product_attach_file_id'];
             } else {
                 $product_attach_file_id = 0;
             }

             $download_info = $this->model_extension_module_mmos_attachmanager->getDownloads($product_attach_file_id);

             if (!empty($download_info) && isset($download_info['filename']) && is_file(DIR_IMAGE . '' . $download_info['filename'])) {
                 $file = DIR_IMAGE . '' . $download_info['filename'];

                 $exten = strtolower(substr($download_info['filename'], strrpos($download_info['filename'], '.') + 1));

                 if ($attach_info['name_download'] == 1) {
                     $file_array = explode('/', $download_info['filename']);

                     $file_named = end($file_array);
                 } else {
                     $file_named = $download_info['mask'] ? $download_info['mask'] . '.' . $exten : basename($file);
                 }

                 $file_named = str_replace(' ', '_', $file_named);


                 $mime = 'application/octet-stream';
				if(strtolower($exten) == 'pdf'){
				 $mime = 'application/pdf';
				}
                 $encoding = 'binary';

                 if ($download_info['login_required'] == 1) { // check required login
                     if ($this->customer->isLogged()) {
                         if (!headers_sent()) {
                             if (file_exists($file)) {
                                 header('Pragma: public');
                                 header('Expires: 0');
                                 header('Content-Description: File Transfer');
                                 header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                                 header('Content-Type: ' . $mime);
                                 header('Content-Transfer-Encoding: ' . $encoding);
                                 if(strtolower($exten) == 'pdf'){
									header('Content-Disposition: inline; filename=' . $file_named . '');
										echo '<html><title>'.$file_named.'</title>';
									} else {
										header('Content-Disposition: attachment; filename=' . $file_named . '');
									}
                                 // header('Content-Length: ' . filesize($file));

                                 echo readfile($file, 'rb');


                                 $download_count = $download_info['download'] + 1;
                                 $download_count = $this->getDownloadsUpdate($product_attach_file_id, $download_count);
                                 exit();
                             } else {
                                 exit('Error: Could not findd file ' . $file . '!');
                             }
                         } else {
                             exit('Error: Headers already sent out!');
                         }
                     } else {
                         $this->response->redirect($this->url->link('account/login'));
                     }
                 } else {
                     if (!headers_sent()) {
                         if (file_exists($file)) {
                             header('Pragma: public');
                             header('Expires: 0');
                             header('Content-Description: File Transfer');
                             header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                             header('Content-Type: ' . $mime);
                             header('Content-Transfer-Encoding: ' . $encoding);
                             if(strtolower($exten) == 'pdf'){
								header('Content-Disposition: inline; filename=' . $file_named . '');
										echo '<html><title>'.$file_named.'</title>';
							} else {
										header('Content-Disposition: attachment; filename=' . $file_named . '');
							}
                             // header('Content-Length: ' . filesize($file));

                             echo readfile($file, 'rb');


                             $download_count = $download_info['download'] + 1;
                             $download_count = $this->model_extension_module_mmos_attachmanager->getDownloadsUpdate($product_attach_file_id, $download_count);
                             exit();
                         } else {
                             exit('Error: Could not findd file ' . $file . '!');
                         }
                     } else {
                         exit('Error: Headers already sent out!');
                     }
                 }
             }
         }
     }


     public function getProductattachmanager($product_id)
     {
         $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_attach_file WHERE product_id = '" . (int) $product_id . "' ORDER BY `sort_order` ASC");

         return $query->rows;
     }

     public function getExtenlinkdownload($product_id)
     {
         $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_attach_extendlink WHERE product_id = '" . (int) $product_id . "'");

         return $query->rows;
     }
 }
