<?php

/**
 * eBINDr2 Mobile
 */

include 'e2mobileAbstract.php';

if(!class_exists('e2mobile')) {

    class e2mobile extends e2mobileAbstract {

        protected $device;

        public function __construct()
        {
            parent::__construct();
        }

        public function setDevice( $isEditr = false ){
            $this->device = $this->_initClass( 'Template', array() );

            $this->device->templateadd( 'mobile_header_layout', 'header.html', MOBILE_TEMPLATE_URI );
            $this->device->templateadd( 'mobile_nav_layout', 'nav-bar.html', MOBILE_TEMPLATE_URI );
            $this->device->templateadd( 'mobile_footer_layout', 'footer.html', MOBILE_TEMPLATE_URI );

            $this->device->variable("db", $this->variables["db"]);
            $this->device->variable("application_name", APPLICATION_NAME);
            $this->device->variable("application_power", APPLICATION_POWER);
            $this->device->variable("application_owner", APPLICATION_OWNER);

            if(isset($_COOKIE["reportr_username"])) $this->device->variable("postinputs", $this->postinputs);
//$device->variable("printr_string", USE_URI . '/?print=true&'.$poststr);
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
    }
}