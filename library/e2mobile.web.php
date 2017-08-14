<?php
/**
 * eBINDr2 Mobile
 */
if(!class_exists('e2mobileWeb')) {

    class e2mobileWeb{

        protected $page = '';

        public function __construct()
        {

        }

        public function setStyles( $styles = array() ){
            foreach( $styles as $style )
                $_SERVER['css'][] = $style;
        }

        public function setScripts( $scripts = array() ){
            foreach( $scripts as $script )
                $_SERVER['js'][] = $script;
        }

        public function setPage( $page = '' ){
            $this->page = $page;
        }

        public function initHeader()
        {
            include '/home/serv/public_html/m/includes/readme.php';
        }

        public function getHeader( $pagetitle = '', $template = '' ){
            $page = $pagetitle;
            include '/home/serv/public_html/m/templates/'. $template .'/header.html';
            include '/home/serv/public_html/m/templates/'. $template .'/nav-bar.html';
        }

        public function getFooter( $template = '' ){
            include '/home/serv/public_html/m/templates/'. $template .'/footer.html';
        }

        public function getBody( $path ){
            include '/home/serv/public_html/m/' . $path;
        }

        public function getContent( $page, $path)
        {
            //$base_uri = $this->http['segments'][0] . '/';
            //$path = str_replace( $base_uri, '', $path );
            ob_start();
            $this->getHeader( $page );
            $this->getBody( $path );
            $this->getFooter();
            $content = ob_get_contents();
            ob_end_clean();

            return $content;
        }

        public function invoke( $path ){
            echo $this->getContent( $this->page, $path );
        }

    }
}
?>