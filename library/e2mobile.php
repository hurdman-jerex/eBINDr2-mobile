<?php

/**
 * eBINDr2 Mobile
 */

include 'e2mobileAbstract.php';

if(!class_exists('e2mobile')) {

    class e2mobile extends e2mobileAbstract {

        protected $device;
        protected $current_page;

        public function __construct()
        {
            session_start();
            parent::__construct();
            $this->bbapi = $this->_initClass( 'BBBApi' );
        }

        public function getDevice()
        {
            return $this->device;
        }

        public function setDevice( $page = '', $isEditr = false ){
            $this->current_page = $page;
            $this->device = $this->_initClass( 'Template', array() );
            $this->device->templateadd( 'mobile_default_layout', 'default.html', MOBILE_TEMPLATE_URI );
            $this->device->templateadd( 'mobile_mainmenu_layout', 'nav.php', MOBILE_TEMPLATE_URI );
            $this->setDeviceVariables( array(
                'page_title' => $this->httpvariables['page_title'],
                'uri' => $this->httpvariables['uri'],
                'url' => $this->httpvariables['url'],
                'protocol' => $this->httpvariables['protocol'],
                'username' => $_COOKIE[ 'reportr_username' ]
            ) );

            $this->device->variable("menu", $this->getMenu() );
            $this->device->variable( "main_menu", $this->device->buffer( 'mobile_mainmenu' ) );

            $this->device->variable("db", $this->variables["db"]);
            $this->device->variable("application_name", APPLICATION_NAME);
            $this->device->variable("application_power", APPLICATION_POWER);
            $this->device->variable("application_owner", APPLICATION_OWNER);

            if(isset($_COOKIE["reportr_username"])) $this->device->variable("postinputs", $this->postinputs);
            $this->device->variable("current_location", USE_URI);//.$poststr);
            $this->device->variable("printr_string", USE_URI . '/?print=true&');//.$poststr);


            if( $isEditr )
                $this->device->variable("current_query", $this->variables[0].".editr");
            else
                $this->device->variable("current_query", $this->variables[0]);


            if(isset($_COOKIE["reportr_username"]) and !empty($_COOKIE["reportr_username"])) {
                $this->device->variable("application_logged_in", ucfirst($_COOKIE["reportr_username"]));
            }

            // language definitions
            $this->device->variable("lang", $this->variables["lang"] );
            $this->device->variable("print_page", PRINT_PAGE);
            $this->device->variable("export_data", EXPORT_DATA);
            $this->device->variable("report_error", REPORT_ERROR);
            $this->device->variable("version", APPLICATION_VERSION);

            if(strpos($_SERVER['SERVER_NAME'], "bureaudata")) {
                $this->device->variable("HEADER", $this->device->buffer("header"));
            }
        }

        public function setReportr()
        {
            $this->reportr = $this->_initClass( 'Reportr', array() );
            $this->device->variable("exportr_string", $this->variables[0] . "," . $this->variables[2] . "," . $this->variables[3] . "," . urlencode($this->variables[4]) . $this->editr->extension."tid=". $this->variables[1]);//."&".$poststr);
            $this->reportr->poststr=$this->poststr;
        }

        public static function securityCheck( $accessRequired, $regular_expression = false) {
            $granted = false;
            $security_keys = explode(",", $_COOKIE['reportr_keys']);
            if ($regular_expression) {
                foreach($security_keys as $security_key) {
                    if (preg_match ( $accessRequired, $security_key )) $granted = true;
                }
            } else {
                foreach($security_keys as $security_key) {
                    if ($accessRequired == $security_key) $granted = true;
                }
            }
            return $granted;
        }

        public static function apiGet( $url ){
            global $bbapi;
            return $bbapi->get( $url );
        }

        public function setDeviceVariable( $name, $value ){
            $this->device->variable( $name, $value );
        }

        public function setDeviceVariables( $variables = array() ){
            foreach( $variables as $name => $value )
                $this->setDeviceVariable( $name, $value );
        }

        public function getMenu(){
            $page = $this->current_page;
            ob_start();
                include MOBILE_TEMPLATE_URI . 'nav-bar.php';
                $content = ob_get_contents();
            ob_end_clean();

            return $content;
        }

        public function getBusinessMenu( $side = 'start' ){
            $page = $this->current_page;
            ob_start();
            include MOBILE_TEMPLATE_URI . 'business/_container-'.$side.'.php';
            $content = ob_get_contents();
            ob_end_clean();

            return $content;
        }

        public function getSearchMenu( $side = 'start' ){
            $page = $this->current_page;
            ob_start();
            include MOBILE_TEMPLATE_URI . 'search/_container-'.$side.'.php';
            $content = ob_get_contents();
            ob_end_clean();

            return $content;
        }

        public function getContent( $path )
        {
            ob_start();
            include MOBILE_TEMPLATE_URI . $path;
            $content = ob_get_contents();
            ob_end_clean();

            return $content;
        }
    }
}