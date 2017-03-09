<?php

include _REPORTR;

if(!class_exists('mobileReportr'))
{
    class mobileReportr extends reportr {

        function __construct( $dbname, $dbhost )
        {
            $this->reportr( $dbname, $dbhost );
        }

        function reportr($dbname, $dbhost) {
            global $variables, $task, $key_words, $parse;

            $this->background = $task;
            $this->variables = $variables;
            $this->lang = $this->variables["lang"];
            $this->key_words = $key_words;
            $this->current_query = $this->variables[0];
            // set what the name of the exportr query would be
            $parse->params["exportrquery"] = $this->variables[1];
            $this->db($dbname, $dbhost);
            if(isset($_POST["THREADID"])) $this->threadid=$_POST["THREADID"];
            $parse->params["THREADID"] = $this->threadid;
            $this->display = new mobileDisplay(array("description", "table", "back", "back_active", "next", "next_active", "table_prefix"));
            $this->related_queries = $this->related();
            $this->variable_set();
            $this->networkfolder=$this->background->get_var("select setup(733)");
            if(isset($_POST["EXCEPTIONLIST"])){
                if($_POST["EXCEPTIONLIST"]=="CLEAR")
                    $this->background->query("DELETE from exceptionlist where mergecode=\"".$this->current_query."\"");
                elseif($_POST["EXCEPTIONLIST"]=="VIEW")
                    $parse->params["EXCEPTION LIST"]="-99999999999";
                elseif(ereg("^CLEAR(.+)",$_POST["EXCEPTIONLIST"], $regs)) {
                    $mylist = explode(",", $regs[1]);
                    foreach($mylist as $mylistitem) $this->background->query("UPDATE exceptionlist SET List=REPLACE(List,'$mylistitem','-999999999999') where mergecode=\"".$this->current_query."\"");
                } else
                    $this->background->query("REPLACE INTO exceptionlist select ifnull(mergecode,\"".$this->current_query."\"), ifnull(concat(list,\",".$_POST["EXCEPTIONLIST"]."\"),\"".$_POST["EXCEPTIONLIST"]."\") from exceptionlist where mergecode=\"".$this->current_query."\" having count(*) in (0,1)");
            }
            set_time_limit(1800);
        }
    }
}