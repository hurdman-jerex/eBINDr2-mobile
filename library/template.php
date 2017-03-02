<?php
include _TEMPLATE;

if(!class_exists('mobileDisplay'))
{
    class mobileDisplay extends template
    {
        protected $template_uri;
        /**
         * @return void
         * @param templates array
         * @desc class constructor. accepts what templates to initialize
         */
        function __construct($templates)
        {
            $this->template_uri = dirname( dirname( dirname( dirname(__FILE__) ) ) ) . '/templates/';
            
            $this->template( $this->template_uri );
            $this->templates = array(
                "description" => "description".(isset($_GET["ebindr2"])?"2":"").".php",
                "table" => "table.php",
                "auth" => "auth.php",
                "layout" => "layout".(isset($_GET["ebindr2"])?"2":"").".php",
                "printr_layout" => "printr_layout.php",
                "back" => "back.php",
                "back_active" => "back_active.php",
                "next" => "next.php",
                "next_active" => "next_active.php",
                "table_prefix" => "table_prefix.php",
                "admin_secure" => "admin_login.php",
                "admin_layout" => "admin_layout.php",
                "layout_calendar" => "layout_calendar".(isset($_GET["ebindr2"])?"2":"").".php",
                "layout_noheader" => "layout_noheader".(isset($_GET["ebindr2"])?"2":"").".php",
                "layout_noheader_hidden" => "layout_noheader_hidden.php",
                "layout_merge" => "layout_merge".(isset($_GET["ebindr2"])?"2":"").".php",
                "layout_open" => "layout_open.php",
                "layout_mobile" => "layout_mobile.php", // Added Mobile Template
                "prompt" => "prompt.php",
                "prompt_big" => "prompt_big.php",
                "prompt_hidden" => "prompt_hidden.php",
                "prompt_selector" => "prompt_selector.php",
                "header" => "header.php",
                "layout_ratinginfo_branded" => "layout_ratinginfo_branded.php",
                "layout_mycomplaints" => "layout_mycomplaints".(isset($_GET["ebindr2"])?"2":"").".php",
                "layout_agcomplaints" => "layout_agcomplaints.php",
                "layout_bbbcomplaints" => "layout_bbbcomplaints.php",
                "layout_mycomplaints_branded" => "layout_mycomplaints_branded".(isset($_GET["ebindr2"])?"2":"").".php",
                "layout_agcomplaints_branded" => "layout_agcomplaints_branded.php",
                "layout_sbq_branded" => "layout_sbq_branded.php",
                "layout_sbq" => "layout_sbq.php",
                "layout_couponedit" => "layout_couponedit.php",
                "auth_mycomplaints" => "auth_mycomplaints.php",
                "auth_agcomplaints" => "auth_agcomplaints.php",
                "auth_sbq" => "auth_sbq.php",
                "auth_couponedit" => "auth_couponedit.php"
            );
            if(file_exists( $this->template_uri . "auth_".APPLICATION_FILENAME.".php"))
                $this->templates["auth"]=$auth_template="auth_".APPLICATION_FILENAME.".php";
            if(file_exists( $this->template_uri . "layout_".APPLICATION_FILENAME.".php"))
                $this->templates["layout_".APPLICATION_FILENAME]=$auth_template="layout_".APPLICATION_FILENAME.".php";
            if(file_exists( $this->template_uri .  "layout_".APPLICATION_FILENAME."_branded.php"))
                $this->templates["layout_".APPLICATION_FILENAME."_branded"]=$auth_template="layout_".APPLICATION_FILENAME."_branded".(isset($_GET["ebindr2"])?"2":"").".php";
            $this->init($templates);
        }
        
            /**
         * @return void
         * @param templates array
         * @desc initializes all of the templates using template->addtemplate
         */
        function init($templates)
        {
            if(is_array($templates))
                for($i=0; $i<sizeof($templates); $i++)
                    $this->addtemplate($templates[$i] . "_layout", $this->templates[$templates[$i]]);
            else
                $this->addtemplate($templates . "_layout", $this->templates[$templates]);
        }
        
        /**
         * @return void
         * @param name string
         * @param value string
         * @desc defines a template local variable
         */
        function variable($name, $value)
        {
            $this->define($name, $value);
        }
        
        /**
         * @return string
         * @param name string
         * @desc outputs and combines the template 2 step output into one
         */
        function buffer($name, $print = 0)
        {
            $this->parse($name, $name . "_layout");
            if($print)
                print $this->output($name);
            else
                return $this->output = $this->output($name);
        }
        
    }
}

?>