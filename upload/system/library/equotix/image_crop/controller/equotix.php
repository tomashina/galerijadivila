<?php
/**
 * @author Equotix Pte Ltd
 * @copyright Copyright (c) 2021, Equotix Pte Ltd
 * @version 2.0.2
 */
require_once(DIR_SYSTEM . 'library/equotix/' . str_replace([DIR_SYSTEM, 'library/equotix/', '/controller'], '', str_replace('\\', '/',__DIR__)) . '/system/loader.php');
if (!class_exists('ControllerEquotix')) {
    class ControllerEquotix extends Controller {
        private $loader;

        public function __construct($registry) {
            parent::__construct($registry);

            $this->loader = new \LoaderEquotix($registry);
        }

        public function output($file, $data) {
            if (isset($this->session->data['token'])) {
                $data['equotix_token'] = '&token=' . $this->session->data['token'];
            } elseif (isset($this->session->data['user_token'])) {
                $data['equotix_token'] = '&user_token=' . $this->session->data['user_token'];
            } else {
                $data['equotix_token'] = '';
            }

            if (!isset($data['header'])) {
                $data['header'] = $this->header($data);
            }

            if (!isset($data['footer'])) {
                $data['footer'] = $this->footer($data);
            }

            $file = version_compare(VERSION, '2.3.0.2', '>=') ? $file : $file . '.tpl';

            $this->response->setOutput($this->load->view($file, $data));
        }

        public function validated($extension_id) {
            return $this->callback($extension_id);
        }

        private function header($data) {
            $data = array_merge($this->loader->language('equotix'), $data);

            if (isset($this->session->data['token'])) {
                $data['equotix_token'] = '&token=' . $this->session->data['token'];
            } elseif (isset($this->session->data['user_token'])) {
                $data['equotix_token'] = '&user_token=' . $this->session->data['user_token'];
            } else {
                $data['equotix_token'] = '';
            }

            $data['heading_title'] = strip_tags($data['heading_title'] ?? $this->loader->getLanguage('heading_title'));
            $data['cancel'] = $data['cancel'] ?? $this->url->link('common/dashboard', $data['equotix_token']);
            $data['home'] = $this->url->link('common/dashboard', $data['equotix_token']);
            $data['license_key'] = !$this->extension_id || $this->validated($this->extension_id);
            $data['documentation'] = isset($this->documentation) ? $this->documentation : '';

            if (!empty($this->request->server['HTTPS'])) {
                $data['base'] = str_replace('http://', 'https://', (defined('HTTPS_SERVER') ? HTTPS_SERVER : HTTP_SERVER));
            } else {
                $data['base'] = str_replace('https://', 'http://', (defined('HTTPS_SERVER') ? HTTPS_SERVER : HTTP_SERVER));
            }

            return $this->loader->view('header', $data);
        }

        private function footer($data) {
            $data = array_merge($this->loader->language('equotix'), $data);

            $data['equotix_folder'] = isset($this->folder) ? $this->folder : 'module';
            $data['equotix_code'] = $this->code;

            if (isset($this->session->data['token'])) {
                $data['equotix_token'] = '&token=' . $this->session->data['token'];
            } elseif (isset($this->session->data['user_token'])) {
                $data['equotix_token'] = '&user_token=' . $this->session->data['user_token'];
            } else {
                $data['equotix_token'] = '';
            }

            $data['opencart'] = sprintf($this->loader->getLanguage('text_opencart'), VERSION);
            $data['copyright'] = sprintf($this->loader->getLanguage('text_copyright'), date('Y'));
            $data['version'] = sprintf($this->loader->getLanguage('text_version'), $this->extension, $this->version);
            $data['license_key'] = !$this->extension_id || $this->validated($this->extension_id);

            if ($data['license_key'] && $this->config->get('eq_' . $this->extension_id . '_license_data')) {
                $license_data = $this->config->get('eq_' . $this->extension_id . '_license_data');

                $data['license'] = $license_data;
                $data['license']['license_key'] = $this->config->get('eq_' . $this->extension_id . '_license_key');
                $data['license']['version'] = $this->version;
                $data['license']['updates'] = version_compare($license_data['latest'], $this->version, '>');

                if (strtotime($license_data['date_expired']) <= time()) {
                    $data['license']['expired'] = true;
                } else {
                    $data['license']['expired'] = false;
                }
            } else {
                $data['license_key'] = false;
            }

            return $this->loader->view('footer', $data);
        }

        public function linkLicense() {
            $this->loader->language('equotix');

            $json = array();

            $json['success'] = false;

            if ($this->user->hasPermission('modify', 'extension/' . (isset($this->folder) ? $this->folder : 'module') . '/' . $this->code)) {
                $this->loader->model('equotix');

                $license_key = false;

                if ($this->request->post['license_type'] == 'opencart') {
                    $license_info = $this->loader->getModel('equotix')->registerLicense($this->request->post);

                    if ($license_info && $license_info['success']) {
                        $license_key = $license_info['license_key'];
                    } elseif ($license_info && $license_info['error']) {
                        $json['error'] = $license_info['error'];
                    } else {
                        $json['error'] = $this->loader->getLanguage('error_unknown');
                    }
                } else {
                    $license_key = $this->request->post['license_key'];
                }

                if ($license_key) {
                    $license_info = $this->loader->getModel('equotix')->getLicense($license_key);

                    if ($license_info && $license_info['success']) {
                        $license_data = [
                            'extension_id' => $license_info['extension_id'] ? $license_info['extension_id'] : $this->extension_id,
                            'license_key'  => $license_info['license_key'],
                            'data'         => [
                                'name'           => $license_info['name'],
                                'order_id'       => $license_info['order_id'],
                                'date_purchased' => $license_info['date_purchased'],
                                'date_expired'   => $license_info['date_expired'],
                                'domains'        => $license_info['domains'],
                                'latest'         => $license_info['version'] ? $license_info['version'] : '1.0.0'
                            ]
                        ];

                        $this->loader->getModel('equotix')->registerExtension($license_data);

                        $json['success'] = true;
                    } elseif ($license_info && isset($license_info['error'])) {
                        $json['error'] = $license_info['error'];
                    } else {
                        $json['error'] = $this->loader->getLanguage('error_unknown');
                    }
                } else {
                    if (!isset($json['error'])) {
                        $json['error'] = $this->loader->getLanguage('error_license_key');
                    }
                }
            } else {
                $json['error'] = $this->loader->getLanguage('error_permission');
            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }

        public function unlinkLicense() {
            $this->loader->language('equotix');

            $json = array();

            $json['success'] = false;

            if ($this->user->hasPermission('modify', 'extension/' . (isset($this->folder) ? $this->folder : 'module') . '/' . $this->code)) {
                $this->loader->model('equotix');

                if (isset($this->request->get['license_key'])) {
                    $license_info = $this->loader->getModel('equotix')->unlinkLicense($this->request->get['license_key']);

                    if ($license_info['success']) {
                        $json['success'] = true;

                        $this->loader->getModel('equotix')->deregisterExtension($license_info['extension_id'] ? $license_info['extension_id'] : $this->extension_id);
                    } elseif ($license_info && isset($license_info['error'])) {
                        $json['error'] = $license_info['error'];
                    } else {
                        $json['error'] = $this->loader->getLanguage('error_unknown');
                    }
                }
            } else {
                $json['error'] = $this->loader->getLanguage('error_permission');
            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }

        private function callback($extension_id) {
            if (!$this->config->get('eq_' . $extension_id . '_license_key')) {
                return false;
            }

            if ((int)$this->config->get('eq_' . $extension_id . '_license_callback') + 432000 > time()) {
                return true;
            }

            $this->loader->model('equotix');

            if ($this->config->get('eq_' . $extension_id . '_license_key')) {
                $domain = $this->loader->getModel('equotix')->getDomain();

                if (is_array($this->config->get('eq_' . $extension_id . '_license_domains'))) {
                    $search = [];

                    foreach ($this->config->get('eq_' . $extension_id . '_license_domains') as $licensed) {
                        $search[] = $licensed['domain'];
                    }

                    if (in_array($domain, $search)) {
                        $license_info = $this->loader->getModel('equotix')->getLicense($this->config->get('eq_' . $extension_id . '_license_key'));

                        if ($license_info && !empty($license_info['success'])) {
                            $license_data = [
                                'extension_id' => $license_info['extension_id'] ?: $this->extension_id,
                                'license_key'  => $license_info['license_key'],
                                'data'         => [
                                    'name'           => $license_info['name'],
                                    'order_id'       => $license_info['order_id'],
                                    'date_purchased' => $license_info['date_purchased'],
                                    'date_expired'   => $license_info['date_expired'],
                                    'domains'        => $license_info['domains'],
                                    'latest'         => $license_info['version'] ? $license_info['version'] : '1.0.0'
                                ]
                            ];

                            $this->loader->getModel('equotix')->registerExtension($license_data);
                        }

                        return true;
                    }
                }

                $this->loader->getModel('equotix')->deregisterExtension($extension_id);
            }

            return false;
        }

        protected function getData($key, $primary = [], $secondary = [], $default = '') {
            if (isset($primary[$key])) {
                $data = $primary[$key];
            } elseif (isset($secondary[$key])) {
                $data = $secondary[$key];
            } else {
                $data = $default;
            }

            return $data;
        }

        protected function getUrl($keys) {
            $data = $this->request->get;

            foreach ($data as $key => $value) {
                if (!in_array($key, $keys)) {
                    unset($data[$key]);
                }
            }

            $url = http_build_query($data);

            if ($url) {
                $url = '&' . $url;
            }

            return $url;
        }

        protected function getTemplateBuffer($route, $event_template_buffer) {
            if ($event_template_buffer) {
                return $event_template_buffer;
            }

            if (defined('DIR_CATALOG')) {
                $dir_template = DIR_TEMPLATE;
            } else {
                if ($this->config->get('config_theme') == 'default') {
                    $theme = $this->config->get('theme_default_directory');
                } else {
                    $theme = $this->config->get('config_theme');
                }

                $dir_template = DIR_TEMPLATE . $theme . '/template/';
            }

            $template_file = $dir_template . $route . '.twig';

            if (file_exists($template_file) && is_file($template_file)) {
                $template_file = $this->modCheck($template_file);

                return file_get_contents($template_file);
            }

            if (defined('DIR_CATALOG')) {
                return false;
            }

            $dir_template = DIR_TEMPLATE . 'default/template/';
            $template_file = $dir_template . $route . '.twig';

            if (file_exists($template_file) && is_file($template_file)) {
                $template_file = $this->modCheck($template_file);

                return file_get_contents($template_file);
            }

            // Support for OC4 catalog
            $dir_template = DIR_TEMPLATE;
            $template_file = $dir_template . $route . '.twig';

            if (file_exists($template_file) && is_file($template_file)) {
                $template_file = $this->modCheck($template_file);

                return file_get_contents($template_file);
            }

            return false;
        }

        protected function modCheck($file) {
            if (defined('DIR_MODIFICATION')) {
                if ($this->startsWith($file, DIR_MODIFICATION)) {
                    if (defined('DIR_CATALOG')) {
                        if (file_exists(DIR_MODIFICATION . 'admin/' . substr($file, strlen(DIR_APPLICATION)))) {
                            $file = DIR_MODIFICATION . 'admin/' . substr($file, strlen(DIR_APPLICATION));
                        }
                    } else {
                        if (file_exists(DIR_MODIFICATION . 'catalog/' . substr($file, strlen(DIR_APPLICATION)))) {
                            $file = DIR_MODIFICATION . 'catalog/' . substr($file, strlen(DIR_APPLICATION));
                        }
                    }
                } elseif ($this->startsWith($file, DIR_SYSTEM)) {
                    if (file_exists(DIR_MODIFICATION . 'system/' . substr($file, strlen(DIR_SYSTEM)))) {
                        $file = DIR_MODIFICATION . 'system/' . substr($file, strlen(DIR_SYSTEM));
                    }
                }
            }

            return $file;
        }

        protected function startsWith($haystack, $needle) {
            if (strlen($haystack) < strlen($needle)) {
                return false;
            }

            return (substr($haystack, 0, strlen($needle)) == $needle);
        }

        protected function replaceNthOccurrence($search, $replace, $string, $nthPositions) {
            $pattern = '/'.preg_quote($search, '/').'/';
            $matches = [];
            $count = preg_match_all($pattern, $string, $matches, PREG_OFFSET_CAPTURE);
            
            if ($count > 0) {
                foreach ($nthPositions as $nth) {
                    if ($nth > 0 && $nth <= $count) {
                        $offset = $matches[0][$nth - 1][1];
                        $string = substr_replace($string, $replace, $offset, strlen($search));
                    }
                }
            }
    
            return $string;
        }

        protected function replaceViews($route, $template, $views) {
            $output = $this->getTemplateBuffer($route, $template);

            foreach ($views as $view) {
                if (isset($view['positions']) && $view['positions']) {
                    $output = $this->replaceNthOccurrence($view['search'], $view['replace'], $output, $view['positions']);
                } else {
                    $output = str_replace($view['search'], $view['replace'], $output);
                }
            }

            return $output;
        }
    }
}