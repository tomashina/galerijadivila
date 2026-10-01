<?php
/**
 * payment_method_admin.php
 *
 * Payment method management
 *
 * @author          Opencart-api.com
 * @copyright       2017
 * @license         License.txt
 * @version         2.0
 * @link            https://opencart-api.com/product/opencart-rest-admin-api/
 * @documentations  https://opencart-api.com/opencart-rest-api-documentations/
 */
require_once(DIR_SYSTEM . 'engine/restadmincontroller.php');

class ControllerRestPaymentMethodAdmin extends RestAdminController
{

    public function paymentmethods()
    {

        $this->checkPlugin();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->listPaymentMethods();
        } else {
            $this->statusCode = 405;
            $this->allowedHeaders = array("GET");
        }

        return $this->sendResponse();
    }


    public function listPaymentMethods()
    {

        // Compatibility code for old extension folders
        $files = glob(DIR_APPLICATION . 'controller/extension/payment/*.php');

        if ($files) {
            foreach ($files as $file) {
                $extension = basename($file, '.php');

                $this->load->language('extension/payment/' . $extension, 'extension');

                $results[] = array(
                    'name'       => $this->language->get('extension')->get('text_title'),
                    'status'     => $this->config->get('payment_' . $extension . '_status') ? 1 : 0,
                    'code'       => $extension,
                    'sort_order' => !empty($this->config->get('payment_' . $extension . '_sort_order')) ? $this->config->get('payment_' . $extension . '_sort_order') : "",
                );
            }
        }

        $this->json['data'] = !empty($results) ? $results : array();

    }
}