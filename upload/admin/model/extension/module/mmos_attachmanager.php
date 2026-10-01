<?php

/*
 * IT IS NOT FREE, YOU SHOULD BUY / REFISTER A LICENSE AT HTTPS://MMOSolution.COM
 * CONTACT: toan@MMOSOLUTION.COM 
 * AUTHOR: MMOSOLUTION TEAM AT VIETNAM
 * All code within this file is copyright MMOSOLUTION.COM TEAM | FOUNDED @2012
 * You can not copy or reuse code within this file without written permission.
*/

 class ModelExtensionModuleMmosAttachManager extends Model {
	
	    public function addeditattachmanager($data, $product_id) {
        $this->deleteattachmanager($product_id);
        if (isset($data['product_attach'])) {
            $repair_folder = null;
            if (isset($this->session->data['attach_session_dir']) && rename(DIR_IMAGE . 'catalog/attach_data/' . $this->session->data['attach_session_dir'], DIR_IMAGE . 'catalog/attach_data/' . $product_id)) {
                $repair_folder = $this->session->data['attach_session_dir'];
                unset($this->session->data['attach_session_dir']);
            }

            foreach ($data['product_attach'] as $product_attach) {
                if ($repair_folder) {
                    $product_attach['filename'] = str_replace($repair_folder, $product_id, $product_attach['filename']);
                }
                if (is_file(DIR_IMAGE . '' . $product_attach['filename'])) {
                    $product_attach_file_id = isset($product_attach['product_attach_file_id']) ? (int) $product_attach['product_attach_file_id'] : '';
                    $login_required = isset($product_attach['login_required']) ? 1 : 0;
                    $filename = $this->db->escape($product_attach['filename']);
                    $mask = $this->db->escape($product_attach['mask']);
                    $download = isset($product_attach['download']) ? $product_attach['download'] : 0;
					$sort_order = isset($product_attach['sort_order']) ? $product_attach['sort_order'] : 0;
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_attach_file SET product_attach_file_id = '" . $product_attach_file_id . "', product_id = '" . (int) $product_id . "', filename = '" . $this->db->escape($filename) . "', mask = '" . $this->db->escape($mask) . "',  login_required = '" . (int) $login_required . "' ,download = '" . (int) $download . "' ,sort_order = '" . (int) $sort_order . "'");
                }
            }
        }

        if (isset($data['exten_link'])) {
            foreach ($data['exten_link'] as $link) {
                $link_name = trim($this->db->escape($link['link_name']));
                $link_download = trim($this->db->escape($link['link_download']));
                $login_required = isset($link['login_required']) ? 1 : 0;
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_attach_extendlink SET product_id = '" . (int) $product_id . "',link_name = '" . $this->db->escape($link_name) . "',link_download = '" . $this->db->escape($link_download) . "', login_required = '" . (int) $login_required . "'");
            }
        }
    }

    public function deleteattachmanager($product_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_attach_extendlink WHERE product_id = '" . (int) $product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_attach_file WHERE product_id = '" . (int) $product_id . "'");
    }

    public function getProductattachmanager($product_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_attach_file WHERE product_id = '" . (int) $product_id . "' ORDER BY `sort_order` ASC");
        return $query->rows;
    }

    public function getLinkdownload($product_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_attach_extendlink WHERE product_id = '" . (int) $product_id . "'");
        return $query->rows;
    }

    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "product_attach_file`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "product_attach_extendlink`");
    }

    public function install() {
        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "product_attach_file` (
                `product_attach_file_id` int(11) NOT NULL auto_increment,
                `product_id` int(11) NOT NULL,
                `filename` text collate utf8_bin default NULL,
                `mask` varchar(255) collate utf8_bin NOT NULL,
                `login_required` tinyint(1) NOT NULL DEFAULT  '0',
                `download` int(11) NOT NULL DEFAULT  '0',
                `sort_order` int(3) NOT NULL DEFAULT  '0',
                PRIMARY KEY  (`product_attach_file_id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";

        $sqllink = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "product_attach_extendlink` (
                `product_id` int(11) NOT NULL,
                `link_name` text collate utf8_bin NOT NULL,
                `link_download` text collate utf8_bin NOT NULL,
                `login_required` tinyint(1) NOT NULL DEFAULT  '0'
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";

        $this->db->query($sql);
        $this->db->query($sqllink);
    }

}

?>