<?php
/**
 * eBINDr2 Mobile
 */
include '/home/serv/public_html/m/includes/helpers.php';
include 'e2mobileAbstract.php';
if(!class_exists('e2mobileWeb')) {

    class e2mobileWeb extends e2mobileAbstract {

        protected $segments;
        protected $http;

        public function __construct()
        {
            parent::__construct();
            $this->_initHeader();
        }

        public function _initHeader()
        {
            $___ebindr2mobile_http = array();
            include '/home/serv/public_html/m/includes/http.php';
            $this->http = $___ebindr2mobile_http;
        }

        public function getHttp()
        {
            return $this->http;
        }

        public function getContent($path)
        {
            //$base_uri = $this->http['segments'][0] . '/';
            //$path = str_replace( $base_uri, '', $path );
            ob_start();
            include $path;
            $content = ob_get_contents();
            ob_end_clean();

            return $content;
        }

    }
}