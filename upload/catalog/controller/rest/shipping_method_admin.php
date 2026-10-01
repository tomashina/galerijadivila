<?php
/**
 * shipping_method_admin.php
 *
 * Shipping method management
 *
 * @author          Opencart-api.com
 * @copyright       2017
 * @license         License.txt
 * @version         2.0
 * @link            https://opencart-api.com/product/opencart-rest-admin-api/
 * @documentations  https://opencart-api.com/opencart-rest-api-documentations/
 */
require_once(DIR_SYSTEM . 'engine/restadmincontroller.php');

class ControllerRestShippingMethodAdmin extends RestAdminController
{

    public function shippingmethods()
    {

        $this->checkPlugin();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->listShippingMethods();
        } else {
            $this->statusCode = 405;
            $this->allowedHeaders = array("GET");
        }

        return $this->sendResponse();
    }


    public function listShippingMethods()
    {

        $default = $this->config->get('config_language');

        // Compatibility code for old extension folders
        $files = glob(DIR_APPLICATION . 'language/'.$default.'/extension/shipping/*.php');

        if ($files) {

            foreach ($files as $file) {
                $extension = basename($file, '.php');

                $this->load->language('extension/shipping/' . $extension, 'extension');

                $results[] = array(
                    'name'       => $this->language->get('extension')->get('text_title'),
                    'status'     => $this->config->get('shipping_' . $extension . '_status') ? 1 : 0,
                    'code'       => $extension,
                    'sort_order' => !empty($this->config->get('shipping_' . $extension . '_sort_order')) ? $this->config->get('shipping_' . $extension . '_sort_order') : "",
                );
            }
        }

        $this->json['data'] = !empty($results) ? $results : array();

    }
}
