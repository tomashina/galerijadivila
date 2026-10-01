<?php
/**
 * @author Equotix Pte Ltd
 * @copyright Copyright (c) 2021, Equotix Pte Ltd
 * @version 1.0.0
 */
if (!class_exists('ModelEquotix')) {
    class ModelEquotix extends Model {
        public function getLicense($license_key) {
            $post_data = [
                'license_key' => $license_key,
                'domain'      => $this->getDomain()
            ];

            return $this->sendCurl('public/license/get', $post_data);
        }

        public function registerLicense($data) {
            $post_data = [
                'name'     => $data['name'],
                'username' => $data['username'],
                'order_id' => $data['order_id'],
                'email'    => $data['email']
            ];

            return $this->sendCurl('public/license/register', $post_data);
        }

        public function unlinkLicense($license_key) {
            $post_data = [
                'license_key' => $license_key,
                'domain'      => $this->getDomain()
            ];

            return $this->sendCurl('public/license/unlink', $post_data);
        }

        public function registerExtension($data) {
            $license_data = [
                'eq_' . $data['extension_id'] . '_license_key'      => $data['license_key'],
                'eq_' . $data['extension_id'] . '_license_callback' => time(),
                'eq_' . $data['extension_id'] . '_license_domains'  => $data['data']['domains'],
                'eq_' . $data['extension_id'] . '_license_data'     => $data['data']
            ];

            $this->addSettings('eq_' . $data['extension_id'] . '_license', $license_data);
        }

        public function deregisterExtension($extension_id) {
            $this->deleteSettings('eq_' . $extension_id . '_license');
        }

        public function getDomain() {
            $domain = parse_url(HTTP_SERVER);
            $domain = $domain['host'];
            $domain = str_replace('www.', '', $domain);
            $domain = str_replace('http://', '', $domain);
            $domain = str_replace('https://', '', $domain);
            $domain = str_replace('/', '', $domain);
            $domain = strtolower($domain);

            return $domain;
        }

        public function sendCurl($url, $post_data) {
            if ($post_data) {
                $post_data['auth_timestamp'] = time();

                $hash_data = $post_data;

                ksort($hash_data);

                foreach ($hash_data as $key => $value) {
                    if (is_array($value)) {
                        unset($hash_data[$key]);
                    }
                }

                $post_data['auth_signature'] = hash_hmac('sha256', json_encode($hash_data), $post_data['auth_timestamp']);
            }

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
            curl_setopt($curl, CURLOPT_USERAGENT, 'OpenCart Extension Licensing System');
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_FORBID_REUSE, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            curl_setopt($curl, CURLOPT_URL, 'https://license.marketinsg.com/' . $url);

            if ($post_data) {
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_data));
            }

            $response = curl_exec($curl);

            if (curl_errno($curl)) {
                $curl_error = 'Error ' . curl_errno($curl) . ': ' . curl_error($curl);

                $this->log->write($curl_error);
            }

            curl_close($curl);

            if ($post_data) {
                $decoded = json_decode($response, true);

                if ($decoded) {
                    $response = $decoded;
                } else {
                    $this->log->write($response);
                }
            }

            return $response;
        }

        private function addSettings($group, $data) {
            if (version_compare(VERSION, '2.0.0.0', '>')) {
                $group_keyword = 'code';
            } else {
                $group_keyword = 'group';
            }

            $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE store_id = '0' AND `" . $this->db->escape($group_keyword) . "` = '" . $this->db->escape($group) . "'");

            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $value = version_compare(VERSION, '2.1.0.0', '>=') ? json_encode($value) : serialize($value);
                    $serialized = '1';
                } else {
                    $serialized = '0';
                }

                $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '0', `" . $this->db->escape($group_keyword) . "` = '" . $this->db->escape($group) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "', serialized = '" . (int)$serialized . "'");
            }
        }

        private function deleteSettings($group) {
            if (version_compare(VERSION, '2.0.0.0', '>')) {
                $group_keyword = 'code';
            } else {
                $group_keyword = 'group';
            }

            $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE store_id = '0' AND `" . $this->db->escape($group_keyword) . "` = '" . $this->db->escape($group) . "'");
        }
    }
}