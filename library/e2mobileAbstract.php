<?php
include "/home/serv/includes/readme.php";
include DIR_PUBLIC . "m/_autoload/bbapi.php";

abstract class e2mobileAbstract {

    protected $database;
    protected $task;
    protected $template;
    protected $parser;
    protected $reportr;
    protected $editr;
    protected $mybindr;

    protected $bbapi;

    protected $variables;
    protected $params;
    protected $poststr;
    protected $postinputs;

    protected $httpvariables;

    public function __construct(){
        global $variables, $params, $poststr, $postinputs;

        $this->variables = $variables;
        $this->params = $params;
        $this->poststr = $poststr;
        $this->postinputs = $postinputs;

        define( 'MOBILE_ROOT', DIR_PUBLIC . 'm/' );
        define( 'MOBILE_TEMPLATE_URI', MOBILE_ROOT . 'templates/' );
        define( 'MOBILE_LIBRARY_URI', MOBILE_ROOT . 'library/' );

        define( '_MOBILETEMPLATE', MOBILE_LIBRARY_URI . 'template.php' );
        define( '_MOBILEREPORTR', MOBILE_LIBRARY_URI . 'reportr.php' );
        define( '_MOBILEEDITR', MOBILE_LIBRARY_URI . 'editr.php' );
        define( '_MOBILECONFIG', MOBILE_LIBRARY_URI . 'config.php' );

        $this->_include();

        // POST & GET
        foreach($_POST as $key => $value) {
            if($key!='which_table' && $key!='EXCEPTIONLIST' && !ereg("^limit",$key)) {
                $this->poststr.="$key=".urlencode(stripslashes($value))."&";
                $this->postinputs.="<input type=hidden name='$key' id='$key' value=\"".stripslashes($value)."\">\r\n";
            }
        }

        foreach($_GET as $key => $value) {
            if($key!='which_table' && !ereg("^limit",$key)) {
                $this->poststr.="$key=".urlencode(stripslashes($value))."&";
                $this->postinputs.="<input type=hidden name='$key' id='$key' value=\"".stripslashes($value)."\">\r\n";
            }
        }
    }

    public function _include(){
        include _DATABASE;
        include _PARSER;

        include _MOBILETEMPLATE;
        include _MOBILEREPORTR;
        include _MOBILEEDITR;
        //include _MOBILECONFIG;
    }

    public function _initHeader()
    {
        $___ebindr2mobile_http = array(
            'uri' => '',
            'args' => '',
            'protocol' => ( ( $_SERVER[HTTPS]=='on' ) ? 'https://' : 'http://' ),
            'segments' => array(),
            'url' => '',
            'servername' => $_SERVER[ 'SERVER_NAME' ]
        );
// get rid of the get query string
        if( strpos($_SERVER['REQUEST_URI'],"?") ) list( $_SERVER['REQUEST_URI'], $___ebindr2mobile_uri[ 'args' ] ) = explode( "?", $_SERVER['REQUEST_URI'] );

        $___ebindr2mobile_http['uri'] = $_SERVER['REQUEST_URI'];
        $___ebindr2mobile_http['segments'] = array_slice( explode( "/", $_SERVER['REQUEST_URI'] ), 2 );

        if( $_SERVER['SERVER_NAME'] == 'seatac.ebindr.com' ) $___ebindr2mobile_http['servername'] = $_SERVER["SERVER_NAME"] = 'localhost';

        $myHost = $___ebindr2mobile_http['protocol'];
        if (strpos($_SERVER['SERVER_NAME'],'vancouver') !== false) $___ebindr2mobile_http['protocol'] = $myHost = 'https://';
        if (strpos($_SERVER['SERVER_NAME'],'mbc') !== false) $___ebindr2mobile_http['protocol'] = $myHost = 'https://';

// Finally set our Base URL
        $___ebindr2mobile_http[ 'url' ] = $___ebindr2mobile_http[ 'protocol' ] . $___ebindr2mobile_http[ 'servername' ] . '/m/';
        $___ebindr2mobile_http[ 'report_url' ] = $___ebindr2mobile_http[ 'protocol' ] . $___ebindr2mobile_http[ 'servername' ] . '/report/';

        $_segment_count = count( $___ebindr2mobile_http[ 'segments' ] );
        $___ebindr2mobile_http[ 'segments' ][ $_segment_count - 1 ] = str_replace( '.php', '', str_replace( '.html', '', $___ebindr2mobile_http[ 'segments' ][ $_segment_count - 1 ] ) );

// Set Page Title

        if( $___ebindr2mobile_http[ 'segments' ][ $_segment_count - 1 ] == 'business' && isset( $_GET[ 'info' ] ) ) {
            $_business_info = str_replace( '-', ' ', $_GET[ 'info' ] );
            $___ebindr2mobile_http['segments'][$_segment_count - 1] = $___ebindr2mobile_http['segments'][$_segment_count - 1] . ' ' . $_business_info;
        }

        $__page_title =  implode( " ", $___ebindr2mobile_http[ 'segments' ] );
        $__page_title = ' - ' . ucwords( str_replace( 'index', '', $__page_title ) );

        $___ebindr2mobile_http['page_title'] = $__page_title;

        $this->httpvariables = $___ebindr2mobile_http;
    }

    public function _initClass( $class, $args = array() ){
        return $this->{$class}( $args );
    }

    public function BBBApi( $args = array() ){
        return new bbapi();
    }

    public function Db( $args = array() ){
        $variables = array_merge( $this->variables, $args );
        return new db( $variables['db'], $variables['host'] );
    }

    public function Template( $args = array() ){
        return new mobileDisplay( $args );
    }

    public function Parse( $args = array() ){
        return new parse();
    }

    public function Reportr( $args = array() ){
        $variables = array_merge( $this->variables, $args );
        return new mobileEditr( $variables['db'], $variables['host'] );
    }

    public function Editr( $args = array() ){
        return new mobileEditr;
    }

    abstract public function getContent( $path );
}