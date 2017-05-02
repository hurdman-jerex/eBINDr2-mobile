<?php

include _PARSER;

if(!class_exists('mobileParse'))
{
    class mobileParse extends parse {

        public function __construct()
        {
            $this->parse();
        }

        /**
         * @return void
         * @desc prompts for post filling of parameters
         */
        function adopt($setdefaultparms=false)
        {
            global $device;
            $device->define("prompt", "Yes");
            // template handler (own instance)
            $display = new mobileDisplay(array("table", "prompt", "prompt_selector"));
            $this->html_prompt = '<table border="0">' . "\n";
//			if($setdefaultparms && sizeof($this->orphan)==0) die("no orphans!");
            for($i=0; $i<sizeof($this->orphan); $i++)
            {
//				echo $this->orphan[$i]."<br>\r\n";
                $defaultvalue=""; unset($selectoptions);
                if($this->instructions[$this->orphan[$i]]!="") {
                    $display->variable("instructions", "<tr><td colspan=3>".$this->instructions[$this->orphan[$i]]."</td></tr>");
                } else $display->variable("instructions", "");
                if(empty($this->pipe_options[$this->orphan[$i]])) $defaults = '';
                elseif(sizeof($this->pipe_options[$this->orphan[$i]]) == 1 && !eregi("^\*", $this->orphan[$i]))
                {
                    $defaults = '';
                    $value[$i] = $this->pipe_options[$this->orphan[$i]][0];
                }
                else
                {
                    $value[$i] = array($this->pipe_options[$this->orphan[$i]][0][1], (empty($this->pipe_options[$this->orphan[$i]][0][0]) ? $this->pipe_options[$this->orphan[$i]][0][1]:$this->pipe_options[$this->orphan[$i]][0][0]));
                    $display->variable("element", ($i+1));
                    if(sizeof($this->pipe_options[$this->orphan[$i]])>99) $display->variable("selectsize",8); else $display->variable("selectsize",5);
                    for($l=0; $l<sizeof($this->pipe_options[$this->orphan[$i]]); $l++) {
                        if(!isset($this->pipe_options[$this->orphan[$i]][$l][0])) {
                            $defaults .= "<option ".($this->pipe_options[$this->orphan[$i]][$l][2]=="selected"?'selected':'')." value=\"" . $this->pipe_options[$this->orphan[$i]][$l][1] . "\">" . $this->pipe_options[$this->orphan[$i]][$l][1]. "</option>";
                            if($defaultvalue=="") $defaultvalue=$this->pipe_options[$this->orphan[$i]][$l][1];
                            $selectoptions[]=$this->pipe_options[$this->orphan[$i]][$l][1];
                        } else {
                            $defaults .= "<option ".($this->pipe_options[$this->orphan[$i]][$l][2]=="selected"?'selected':'')." value=\"" . $this->pipe_options[$this->orphan[$i]][$l][0] . "\">" . $this->pipe_options[$this->orphan[$i]][$l][1] . "</option>";
                            if($defaultvalue=="") $defaultvalue=$this->pipe_options[$this->orphan[$i]][$l][0];
                            $selectoptions[]=$this->pipe_options[$this->orphan[$i]][$l][0];
                        }
                    }
                    $display->variable("defaults", $defaults);
                }
                if(eregi("(^| )date",$this->orphan[$i])) {
                    if(COUNTRY=="CANADA") $display->define("country_var","&country=Canada");
                    $display->templates["prompt"]="prompt_date".(isset($_GET["ebindr2"])?"2":"").".php";
                    $display->addtemplate("prompt_layout", "prompt_date".(isset($_GET["ebindr2"])?"2":"").".php");
                } elseif((strtoupper($this->orphan[$i])!=$this->orphan[$i] && sizeof($this->pipe_options[$this->orphan[$i]])>1) || eregi("^\*", $this->orphan[$i])) {
                    $display->templates["prompt"]="prompt_hidden.php";
                    $display->addtemplate("prompt_layout", "prompt_hidden.php");
                } elseif(eregi("^\@", $this->orphan[$i])) {
                    $display->templates["prompt"]="prompt_big.php";
                    $display->addtemplate("prompt_layout", "prompt_big.php");
                } else {
                    $display->templates["prompt"]="prompt.php";
                    $display->addtemplate("prompt_layout", "prompt.php");
                }
                if(eregi("^\*", $this->orphan[$i])) {
                    $display->templates["prompt_selector"]="prompt_selector_multi.php";
                    $display->addtemplate("prompt_selector_layout", "prompt_selector_multi.php");
                    if(!$dontchangemsg) $display->variable("selection_message", "*Pressing CTRL while clicking in the list will allow you to select multiple items.");
                } else {
                    $display->templates["prompt_selector"]="prompt_selector.php";
                    $display->addtemplate("prompt_selector_layout", "prompt_selector.php");
                }
                if(is_array($this->pipe_options[$this->orphan[$i]][0]) && in_array("ALL*",$this->pipe_options[$this->orphan[$i]][0])) {
                    $display->variable("selection_message", "*Pressing CTRL while clicking in the list will allow you to select multiple items. If you choose the item ALL*, any additional items selected will NOT be included.");
                    $dontchangemsg=true;
                }

                // validate the entered data to make sure its correctly entered
                if(VALIDATE_ENTERED_DATA == 1) {
                    if($this->pipe_validate[$this->orphan[$i]] == $this->orphan[$i]) {
                        // javascript validation
                        $on_blur = "CompareDate(format_date('" . $value[$i][1] . "'), format_date(document.getElementById('prompt_value_" . ($i+1) . "').value), 'adoptee');";
                        $do_validate = true;
                    }
                }

                // get the previous entered variables if there are any.
                if(STORE_ENTERED_VARS == 1) {
                    $previous_vars = read_tmp_file();
                    // if it finds a previous entered variable with the same name as the orphan,
                    // set the temp value for the text box
                    if(!$setdefaultparms && $value[$i][1]=="" && !ereg("^\*",$this->orphan[$i]) && !empty($previous_vars[str_replace(" ", "_", $this->orphan[$i])]))
                        $temp_value = $previous_vars[str_replace(" ", "_", $this->orphan[$i])];
                    // otherwise just set it to the value of the pipe defaults if there are any
                    // set in the query.
                    else
                        $temp_value = $value[$i][1];
                }

                $display->variable("on_blur", $on_blur);
                if(eregi("date",$this->orphan[$i]) && $temp_value!="") {
                    if(isset($_GET["ebindr2"])) $display->variable("date_value",date("m/d/Y", strtotime($temp_value)));
                    else $display->variable("date_value",date("d-M-Y", strtotime($temp_value)));
                    $defaultvalue=date("Y-m-d", strtotime($temp_value));
                }

                if(!eregi("^\*", $this->orphan[$i])) {
                    $display->variable("value", $temp_value);
                    if($defaultvalue=="") $defaultvalue=$temp_value;
                } else {
                    $display->variable("value", "'".$temp_value."'");
//					if($defaultvalue=="") $defaultvalue="'".$temp_value."'";
                    if($defaultvalue=="ALL*") $defaultvalue="'***ALLSELECTED***','".implode("','", $selectoptions)."'";
                    elseif($defaultvalue=="ALL") $defaultvalue="'".$defaultvalue."'";
                    else $defaultvalue="'".$temp_value."'";
                }
                $display->variable("id", "prompt_value_" . ($i+1));
                $display->variable("title", $this->orphan[$i]);
                $display->variable("name", $this->orphan[$i]);
                if(!empty($defaults))
                    $display->variable("default_options", $display->buffer("prompt_selector"));
                else
                    $display->variable("default_options", "");

                if($setdefaultparms && $defaultvalue=="" && isset($this->used_parameters[$this->orphan[$i]])) $this->params[$this->orphan[$i]]=$this->used_parameters[$this->orphan[$i]];
                elseif($setdefaultparms) $this->params[$this->orphan[$i]]=$defaultvalue;


                $this->html_prompt .= $display->buffer("prompt");
                $defaults = '';
                $on_blur = '';
            }

            if(!$setdefaultparms) {
                $this->html_prompt .= '</table>';
                $display->variable("data", $this->html_prompt);
                $this->html_prompt = $display->buffer("table");

                $device->define("content", $this->html_prompt);
                $device->define("head_desc", "Please enter the required information.");

                if($do_validate) $device->define("submit", '<br /><input id="adoptee" type="submit" value="Continue" disabled></form>');
                else $device->define("submit", '<br /><script>var submitted=false;</script><input id="adoptee" type="submit" value="Continue" onclick="if(submitted) { alert(\'Your request has already been submitted... please wait.\'); this.disabled=true; return false; }; submitted=true;"></form>');
            }// else print_r($this->params);
            return true;
        }
    }
}