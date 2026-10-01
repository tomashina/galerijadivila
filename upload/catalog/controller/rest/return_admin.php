<?php
/**
 * return_admin.php
 *
 * Return management
 *
 * @author          Opencart-api.com
 * @copyright       2017
 * @license         License.txt
 * @version         2.0
 * @link            https://opencart-api.com/product/opencart-rest-admin-api/
 * @documentations  https://opencart-api.com/opencart-rest-api-documentations/
 */
require_once(DIR_SYSTEM . 'engine/restadmincontroller.php');

class ControllerRestReturnAdmin extends RestAdminController
{

    private static $defaultFields = array(
        "product_id" => '',
        "customer_id" => '',
        "return_reason_id" => '',
        "return_action_id" => '',
        "return_status_id" => '',
        "comment" => '',
        "quantity" => '',
        "opened" => 0,
        "date_ordered" => '',
    );
    public function returns()
    {

        $this->load->language('restapi/return');
        $this->load->model('rest/restadmin');

        $this->checkPlugin();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->listReturns($this->request);

        } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $post = $this->getPost();

            $this->addReturn($post);

        } else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $post = $this->getPost();

            if (isset($this->request->get['id']) && $this->isInteger($this->request->get['id'])) {
                $this->editReturn($this->request->get['id'], $post);
            } else {
                $this->statusCode = 400;
            }

        } else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

            if (isset($this->request->get['id']) && $this->isInteger($this->request->get['id'])) {


                $return = $this->model_rest_restadmin->getReturn($this->request->get['id']);

                if($return) {
                    $post["returns"] = array($this->request->get['id']);
                    $this->deleteReturn($post);
                } else {
                    $this->json['error'][] = "Return not found";
                    $this->statusCode = 404;
                }
            } else {

                $post = $this->getPost();

                if (isset($post["returns"])) {
                    $this->deleteReturn($post);
                } else {
                    $this->statusCode = 400;
                }
            }
        }

        return $this->sendResponse();
    }

    public function listReturns($request)
    {


        $parameters = array(
            "limit" => $this->config->get('config_limit_admin'),
            "start" => 1,
        );

        /*check limit parameter*/
        if (isset($request->get['limit']) && $this->isInteger($request->get['limit'])) {
            $parameters["limit"] = $request->get['limit'];
        }

        /*check page parameter*/
        $page = 1;
        if (isset($request->get['page']) && $this->isInteger($request->get['page'])) {
            $parameters["start"] = $request->get['page'];
            $page = $request->get['page'];
        }

        if (isset($request->get['sort'])) {
            $parameters["sort"] = $request->get['sort'];
        }

        if (isset($request->get['order'])) {
            $parameters["order"] = $request->get['order'];
        }

        $parameters["start"] = ($parameters["start"] - 1) * $parameters["limit"];

        if (isset($request->get['filter_return_id']) && $this->isInteger($request->get['filter_return_id'])) {
            $parameters["filter_return_id"] = $request->get['filter_return_id'];
        }

        if (isset($request->get['filter_order_id']) && $this->isInteger($request->get['filter_order_id'])) {
            $parameters["filter_order_id"] = $request->get['filter_order_id'];
        }

        if (isset($request->get['filter_customer']) && $this->isInteger($request->get['filter_customer'])) {
            $parameters["filter_customer"] = $request->get['filter_customer'];
        }

        if (isset($request->get['filter_return_status_id']) && $this->isInteger($request->get['filter_return_status_id'])) {
            $parameters["filter_return_status_id"] = $request->get['filter_return_status_id'];
        }


        if (isset($this->request->get['filter_date_added'])) {
            $date_date_added = date('Y-m-d', strtotime($this->request->get['filter_date_added']));
            if ($this->validateDate($date_date_added, 'Y-m-d')) {
                $parameters["filter_date_added"] = $date_date_added;
            }
        }

        if (isset($this->request->get['filter_date_modified'])) {
            $date_modified = date('Y-m-d', strtotime($this->request->get['filter_date_modified']));
            if ($this->validateDate($date_modified, 'Y-m-d')) {
                $parameters["filter_date_modified"] = $date_modified;
            }
        }

        $total = $this->model_rest_restadmin->getTotalReturns($parameters);

        $results = $this->model_rest_restadmin->getReturns($parameters);

        $returns = array();

        foreach ($results as $result) {
            $returns[] = array(
                'return_id'     => $result['return_id'],
                'order_id'      => $result['order_id'],
                'customer_id'      => $result['customer_id'],
                'firstname'     => $result['firstname'],
                'lastname'      => $result['lastname'],
                'email'      => $result['email'],
                'telephone'      => $result['telephone'],
                'customer'      => $result['customer'],
                'product'       => $result['product'],
                'model'         => $result['model'],
                'quantity'         => $result['quantity'],
                'return_reason_id'         => $result['return_reason_id'],
                'return_action_id'         => $result['return_action_id'],
                'return_status_id'         => $result['return_status_id'],
                'opened'         => $result['opened'],
                'comment'         => $result['comment'],
                'return_status'     => $result['return_status'],
                'date_ordered'    => date($this->language->get('date_format_short'), strtotime($result['date_ordered'])),
                'date_added'    => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                'date_modified' => date($this->language->get('date_format_short'), strtotime($result['date_modified'])),
            );
        }

        $this->response->addHeader('X-Total-Count: ' . (int)$total);
        $this->response->addHeader('X-Pagination-Limit: ' . (int)$parameters["limit"]);
        $this->response->addHeader('X-Pagination-Page: ' . (int)$page);

        $this->json['data'] = !empty($returns) ? $returns : array();
    }

    public function addReturn($post)
    {

        $error = $this->validateForm($post);

        if (!empty($post) && empty($error)) {

            $post = array_merge(static::$defaultFields, $post);

            $retval = $this->model_rest_restadmin->addReturn($post);
            $this->json["data"]["id"] = $retval;
        } else {
            $this->json['error'] = $error;
            $this->statusCode = 400;
        }

    }

    protected function validateForm($post, $id=null)
    {

        $error = array();

        if (!empty($id)) {
            if (isset($post['order_id']) && empty($post['order_id'])) {
                $error[] = $this->language->get('error_order_id');
            }

            if (isset($post['firstname']) && ((utf8_strlen(trim($post['firstname'])) < 1) || (utf8_strlen(trim($post['firstname'])) > 32))) {
                $error[] =  $this->language->get('error_firstname');
            }

            if (isset($post['lastname']) && ((utf8_strlen(trim($post['lastname'])) < 1) || (utf8_strlen(trim($post['lastname'])) > 32))) {
                $error[] =  $this->language->get('error_lastname');
            }

            if (isset($post['email']) && ((utf8_strlen($post['email']) > 96) || !filter_var($post['email'], FILTER_VALIDATE_EMAIL))) {
                $error[] =  $this->language->get('error_email');
            }

            if (isset($post['telephone']) && ((utf8_strlen($post['telephone']) < 3) || (utf8_strlen($post['telephone']) > 32))) {
                $error[] =  $this->language->get('error_telephone');
            }

            if (isset($post['product']) && ((utf8_strlen($post['product']) < 1) || (utf8_strlen($post['product']) > 255))) {
                $error[] =  $this->language->get('error_product');
            }

            if (isset($post['model']) && ((utf8_strlen($post['model']) < 1) || (utf8_strlen($post['model']) > 64))) {
                $error[] =  $this->language->get('error_model');
            }

            if (isset($post['return_reason_id']) && empty($post['return_reason_id'])) {
                $error[] =  $this->language->get('error_reason');
            }

            if (isset($post['warning']) && $this->error && !isset($this->error['warning'])) {
                $error[] =  $this->language->get('error_warning');
            }
        } else {
            if (empty($post['order_id'])) {
                $error[] = $this->language->get('error_order_id');
            }

            if ((utf8_strlen(trim($post['firstname'])) < 1) || (utf8_strlen(trim($post['firstname'])) > 32)) {
                $error[] =  $this->language->get('error_firstname');
            }

            if ((utf8_strlen(trim($post['lastname'])) < 1) || (utf8_strlen(trim($post['lastname'])) > 32)) {
                $error[] =  $this->language->get('error_lastname');
            }

            if ((utf8_strlen($post['email']) > 96) || !filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
                $error[] =  $this->language->get('error_email');
            }

            if ((utf8_strlen($post['telephone']) < 3) || (utf8_strlen($post['telephone']) > 32)) {
                $error[] =  $this->language->get('error_telephone');
            }

            if ((utf8_strlen($post['product']) < 1) || (utf8_strlen($post['product']) > 255)) {
                $error[] =  $this->language->get('error_product');
            }

            if ((utf8_strlen($post['model']) < 1) || (utf8_strlen($post['model']) > 64)) {
                $error[] =  $this->language->get('error_model');
            }

            if (empty($post['return_reason_id'])) {
                $error[] =  $this->language->get('error_reason');
            }

            if ($this->error && !isset($this->error['warning'])) {
                $error[] =  $this->language->get('error_warning');
            }
        }



        return $error;
    }

    public function editReturn($id, $post)
    {

        $data = $this->model_rest_restadmin->getReturn($id);

        if($data){
            $error = $this->validateForm($post, $id);

            if (!empty($post) && empty($error)) {
                $post = array_merge($data, $post);
                $this->model_rest_restadmin->editReturn($id, $post);
            } else {
                $this->json['error'] = $error;
                $this->statusCode = 400;
            }
        } else {
            $this->json['error'][] = "Return not found";
            $this->statusCode = 404;
        }
    }

    public function deleteReturn($post)
    {


        if (isset($post['returns'])) {
            foreach ($post['returns'] as $returnId) {
                $this->model_rest_restadmin->deleteReturn($returnId);
            }
        } else {
            $this->json['error'][] = "Error";
            $this->statusCode = 400;
        }

    }

    private function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }


    public function history()
    {

        $this->checkPlugin();

        $this->load->model('rest/restadmin');

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (isset($this->request->get['id']) && $this->isInteger($this->request->get['id'])) {
                $this->listHistory($this->request->get['id']);
            } else {
                $this->statusCode = 400;
            }

        } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $post = $this->getPost();

            if (isset($this->request->get['id']) && $this->isInteger($this->request->get['id'])) {
                $this->addHistory($this->request->get['id'], $post);
            } else {
                $this->statusCode = 400;
            }

        } else {
            $this->statusCode = 405;
            $this->allowedHeaders = array("GET");
        }

        return $this->sendResponse();
    }


    public function listHistory($return_id)
    {
        $this->load->model('rest/restadmin');

        $this->json['data'] = $this->model_rest_restadmin->getReturnHistories($return_id, 0, 500);
    }

    public function addHistory($returnId, $post)
    {

        if (!empty($post)) {
            $return_status_id   = isset($post['return_status_id']) ? $post['return_status_id'] : "";
            $comment            = isset($post['comment']) ? $post['comment'] : "";
            $notify             = isset($post['notify']) ? (int)$post['notify'] : 0;

            $retval = $this->model_rest_restadmin->addReturnHistory($returnId, $return_status_id, $comment, $notify);

            $this->json["data"]["id"] = $retval;

        } else {
            $this->json['error'] = "Empty POST";
            $this->statusCode = 400;
        }

    }
}