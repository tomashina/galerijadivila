<?php
/**
 * filter_admin.php
 *
 * Product filter management
 *
 * @author          Opencart-api.com
 * @copyright       2017
 * @license         License.txt
 * @version         2.0
 * @link            https://opencart-api.com/product/opencart-rest-admin-api/
 * @documentations  https://opencart-api.com/opencart-rest-api-documentations/
 */
require_once(DIR_SYSTEM . 'engine/restadmincontroller.php');

class ControllerRestFilterAdmin extends RestAdminController {

    public function filters()
    {

        $this->checkPlugin();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->listFilters($this->request);
        } else {
            $this->statusCode = 405;
            $this->allowedHeaders = array("GET");
        }

        return $this->sendResponse();
    }

    public function groups()
    {

        $this->checkPlugin();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {

            $this->listGroups($this->request);

        } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $post = $this->getPost();

            if (!empty($post)) {
                $this->addFilterGroup($post);
            } else {
                $this->statusCode = 400;
            }
        } else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $post = $this->getPost();

            if (isset($this->request->get['id']) && $this->isInteger($this->request->get['id'])
                && !empty($post)
            ) {
                $this->editFilterGroup($this->request->get['id'], $post);
            } else {
                $this->json['error'][] = "Missing filter group id";
                $this->statusCode = 400;
            }
        } else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            if ($this->request->get['id']) {
                $this->deleteFilterGroup($this->request->get['id']);
            } else {
                $this->statusCode = 400;
            }
        }

        return $this->sendResponse();
    }

    public function listFilters() {

        $this->load->model('rest/restadmin');

        $parameters = array(
            "limit" => $this->config->get('config_limit_admin'),
            "start" => 1,
        );

        /*check limit parameter*/
        if (isset($this->request->get['limit']) && $this->isInteger($this->request->get['limit'])) {
            $parameters["limit"] = $this->request->get['limit'];
        }

        /*check page parameter*/
        if (isset($this->request->get['page']) && $this->isInteger($this->request->get['page'])) {
            $parameters["start"] = $this->request->get['page'];
        }

        if (isset($this->request->get['filter_group']) && $this->isInteger($this->request->get['filter_group'])) {
            $parameters["filter_group"] = $this->request->get['filter_group'];
        }

        $parameters["start"] = ($parameters["start"] - 1) * $parameters["limit"];

        $data = $this->model_rest_restadmin->getFilters($parameters);

        $this->json['data'] = !empty($data) ? $data : array();

    }

    public function addFilterGroup($post)
    {

        $this->load->language('restapi/filter');
        $this->load->model('rest/restadmin');

        $error = $this->validateForm($post);
        if (empty($error)) {

            $retval = $this->model_rest_restadmin->addFilter($post);
            $this->json["data"]["id"] = $retval;
        } else {
            $this->json["error"] = $error;
            $this->statusCode = 400;
        }

    }

    protected function validateForm($post)
    {
        $this->load->model('rest/restadmin');
        $error = array();

        foreach ($post['filter_group_description'] as $language_id => $value) {
            if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 64)) {
                $error[] = $this->language->get('error_group');
            }
        }

        if (isset($post['filter'])) {
            foreach ($post['filter'] as $filter_id => $filter) {
                foreach ($filter['filter_description'] as $language_id => $filter_description) {
                    if ((utf8_strlen($filter_description['name']) < 1) || (utf8_strlen($filter_description['name']) > 64)) {
                        $error[] = $this->language->get('error_name');
                    }
                }
            }
        }

        return $error;
    }

    public function editFilterGroup($id, $post)
    {

        $this->load->language('restapi/filter');
        $this->load->model('rest/restadmin');

        $data = $this->model_rest_restadmin->getFilterGroup($id);
        if ($data){

            $error = $this->validateForm($post, $id);

            if (empty($error)) {
                $this->model_rest_restadmin->editFilter($id, $post);
            } else {
                $this->json["error"] = $error;
                $this->statusCode = 400;
            }
        } else {
            $this->json['error'][] = "Filter group not found";
            $this->statusCode = 404;
        }
    }

    public function deleteFilterGroup($id)
    {
        $this->load->language('restapi/filter');
        $this->load->model('rest/restadmin');

        $data = $this->model_rest_restadmin->getFilterGroup($id);

        if ($data){
            $this->model_rest_restadmin->deleteFilter($id);
        } else {
            $this->statusCode = 404;
        }
    }

    public function listGroups() {

        $this->load->model('rest/restadmin');

        $parameters = array(
            "limit" => $this->config->get('config_limit_admin'),
            "start" => 1,
            "sort" => 'fgd.name',
            "order" => 'ASC',
        );

        if (isset($this->request->get['sort'])) {
            $parameters["sort"] = $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $parameters["order"] = $this->request->get['order'];
        }

        /*check limit parameter*/
        if (isset($this->request->get['limit']) && $this->isInteger($this->request->get['limit'])) {
            $parameters["limit"] = $this->request->get['limit'];
        }

        /*check page parameter*/
        if (isset($this->request->get['page']) && $this->isInteger($this->request->get['page'])) {
            $parameters["start"] = $this->request->get['page'];
        }

        $parameters["start"] = ($parameters["start"] - 1) * $parameters["limit"];

        $results = $this->model_rest_restadmin->getFilterGroups($parameters);


        foreach ($results as $result) {
            $this->json['data'][] = array(
                'filter_group_id' => (int)$result['filter_group_id'],
                'name'            => $result['name'],
                'sort_order'      => (int)$result['sort_order']
            );
        }
    }
}