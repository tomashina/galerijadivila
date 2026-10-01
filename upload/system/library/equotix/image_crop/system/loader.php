<?php
if (!class_exists('LoaderEquotix')) {
    class LoaderEquotix {
        private $registry = [];
        private $language = [];
        private $model = [];
        private $system_directory = '';

        public function __construct($registry) {
            $this->registry = $registry;
            $this->system_directory = str_replace([DIR_SYSTEM, 'library/equotix/', '/system'], '', str_replace('\\', '/',__DIR__));
        }

        public function language($filename) {
            $_ = [];

            require(DIR_SYSTEM . 'library/equotix/' . $this->system_directory . '/language/' . $filename . '.php');

            $this->language = array_merge($this->language, $_);

            return $this->language;
        }

        public function getLanguage($key) {
            return $this->language[$key] ?? $key;
        }

        public function view($filename, $data) {
            foreach ($data as $key => $value) {
                ${$key} = $value;
            }

            ob_start();

            require_once(DIR_SYSTEM . 'library/equotix/' . $this->system_directory . '/view/' . $filename . '.tpl');

            return ob_get_clean();
        }

        public function model($filename) {
            require(DIR_SYSTEM . 'library/equotix/' . $this->system_directory . '/model/' . $filename . '.php');

            $class = 'Model' . preg_replace('/[^a-zA-Z0-9]/', '', $filename);

            $this->model[$filename] = new $class($this->registry);
        }

        public function getModel($filename) {
            $filename = preg_replace('/[^a-zA-Z0-9]/', '', $filename);

            return isset($this->model[$filename]) ? $this->model[$filename] : null;
        }
    }
}