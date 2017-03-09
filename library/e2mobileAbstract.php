<?php
include "/home/serv/includes/readme.php";

abstract class e2mobileAbstract {

    protected $database;
    protected $task;
    protected $template;
    protected $parser;
    protected $reportr;
    protected $editr;
    protected $mybindr;

    protected $variables;
    protected $params;
    protected $poststr;
    protected $postinputs;

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
        include _MOBILECONFIG;
    }

    public function _initClass( $class, $args = array() ){
        return $this->{$class}( $args );
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
        return new mobileEditr();
    }
}