<?php

include _REPORTR;

if(!class_exists('mobileReportr'))
{
    class mobileReportr extends reportr {

        protected $__views = array();

        function __construct( $dbname, $dbhost )
        {
            $this->__views = include MOBILE_INCLUDE_URI . 'views.php';
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

            $this->initMobileDisplay( array("description", "table", "back", "back_active", "next", "next_active", "table_prefix") );

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

        function initMobileDisplay( $templates ){
            global $device;
            $this->display = new mobileDisplay( $templates );
            /* Layouts */
            foreach( $this->__views['layouts']['views'] as $view ){
                $__layout_path = $this->__views['layouts']['path'];
                $this->display->templateadd( $view . '_layout',
                    $view . '.php',
                    $__layout_path );

                if( $device instanceof template )
                    $device->templateadd( $view . '_layout',
                        $view . '.php',
                        $__layout_path );

            }

            /* Components */
            foreach( $this->__views['components']['views'] as $view ){
                $__layout_path = $this->__views['components']['path'];
                
                $this->display->templateadd( $view . '_layout',
                    $view . '.php',
                        $__layout_path );


                if( $device instanceof template )
                    $device->templateadd( $view . '_layout',
                        $view . '.php',
                        $__layout_path );
            }
        }

        /**
         * @return void
         * @desc builds the field name html table for display. Also handles the fields if there needs to be data
         * displayed vertically it will add it to that row and push the next field to a new <tr>
         */
        function fields()
        {
            $this->skipped_fields=0;
            unset($this->identityrow);
            unset($this->groupindex);
            for ($i=0; $i<count($this->col_info); $i++)
            {
                if($this->colwidths[$i]>0) $this->col_info[$i]->width=$this->colwidths[$i];
                $this->col_info[$i]->hidezeros=false;
                if(ereg("^\(.*\)$",$this->col_info[$i]->name)) {
                    $this->groupindex=$i;
                }
                if(ereg(" [$]$",$this->col_info[$i]->name) || ereg("^[0-9]+$",$this->col_info[$i]->name)) {
                    $this->col_info[$i]->hidezeros=true;
                    if(count($this->data)==1) {
//					print_r($this);
                        //				exit();
                    }
                    for($z=0; $z<count($this->data); $z++)
                    {
                        if(null($this->data[$z][$i])!=0) $this->col_info[$i]->hidezeros=false;
                    }
                }
                $this->col_info[$i]->name = trim($this->col_info[$i]->name);
                // setting the field alignment for integer fields
                if($this->col_info[$i]->type == 'int' || $this->col_info[$i]->type == 'real' || ereg("%$",$this->col_info[$i]->name))
                    $this->align[$i] = ' align="right"'; else $this->align[$i] = '';
                if($this->colalign[$i]=="r") $this->align[$i] = ' align="right"';
                elseif($this->colalign[$i]=="l") $this->align[$i] = ' align="left"';
                // checking to see if hte field is a primary key
                if(SHOW_PRIMARY_KEY_ICON == 1) {
                    if($this->col_info[$i]->primary_key)
                        $this->icon = "<img src=\"" . $this->sub_dir . "/images/key.gif\" title=\"" . PRIMARY_KEY . "\">"; else $this->icon = "";
                }

                if($i == 0) // get the first value in the row
                    $first = $this->col_info[$i]->name;

                // entering HTML empty data
                if((empty($this->col_info[$i]->name) || !$this->col_info[$i]->name) && (!isset($_GET[noheader]) || !isset($_GET[hideempty])))
                    $this->col_info[$i]->name = ' &nbsp;';

                $this->class = 'table_heading';
                if($this->loop_at == 0) // check to see if our loop is at the start
                {

                    // if the field name starts with a $ or ends with a $ then we automatically devide by 100
                    if($this->col_info[$i]->name{0} == "$" || substr($this->col_info[$i]->name, -1) == '$')
                        $this->auto_devide[$i] = $this->col_info[$i]->name;

                    if(eregi("^[(]*(cid|bid)[)]*$", $this->col_info[$i]->name, $regs)) $this->identityrow[strtolower($regs[1])]=$i;
                    if(eregi("^[*](cid|bid)$", $this->col_info[$i]->name, $regs)) $this->identityrow[strtolower($regs[1])]=$i;
                    // if the current field is found as a related query; we need to link it.
                    if(ereg("(BID|CID|MED|SC|MISC|VORP|VIP)FILES", $this->col_info[$i]->name, $regs))
                    {
                        $this->field[$i] = "";
                        $this->skipped_fields++;
                        $this->field_cell($i, "", false);
                        $this->col_info[$i]->files=$regs[1];
                    }
                    elseif(@in_array($this->col_info[$i]->name, $this->related_queries))
                    {
                        $this->class = "table_heading_link";
                        $this->field[$i] = $this->col_info[$i]->name;
                        $this->linked[$i] = $this->col_info[$i]->name;
                        $this->field_cell($i, $this->col_info[$i]->name, false);
                    }
                    // if the current field is found as an editr link, we link it accordingly
                    elseif(@in_array($this->col_info[$i]->name . ".editr", $this->related_queries))
                    {
                        $this->class = "table_heading_link_editr";
                        $this->field[$i] = $this->col_info[$i]->name;
                        $append = ".editr";
                        $this->linked[$i] = $this->col_info[$i]->name . ".editr";
                        $this->field_cell($i, $this->col_info[$i]->name, true);
                    }
                    // if the current field is found in the Key Words array we link it a special way
                    elseif(@in_array($this->col_info[$i]->name, $this->key_words))
                    {
                        $this->class = "table_heading_link";
                        $this->field[$i] = $this->col_info[$i]->name;
                        $this->linked[$i] = $this->col_info[$i]->name;
                        $this->field_cell($i, $this->col_info[$i]->name, false);
                    }
                    // if the current field is hidden with a "*" we need to setup the skip sub-program
                    elseif($this->col_info[$i]->name{0} == "*" || $this->col_info[$i]->name{0} == "&" || $this->col_info[$i]->hidezeros)
                    {
                        $this->skipped_fields++;
                        $this->field[$i] = $this->col_info[$i]->name;
                        $this->skip[$i] = $this->col_info[$i]->name;

                        /*if( $this->col_info[$i]->name == 'DTABLE' )
                            $this->display->variable("DTABLE", "datatable");*/
                    }
                    // all other possibles are handled here. That includes normal data and matrix table setups
                    else
                    {
                        $this->field[$i] = $this->col_info[$i]->name;
                        $this->field_cell($i, $this->col_info[$i]->name, false);
                    }
                    // the description for linked fields
                    $this->related_desc[$i] = $this->background->get_var("select description from " . QUERY_DB . "." . QUERY_TABLE . " where mergecode='" . $this->current_query . "." . $this->col_info[$i]->name . $append . "'");

                } else { // This is for subtables, so fields can be hidden
                    if($this->col_info[$i]->name{0} == "*" || $this->col_info[$i]->name{0} == "&" || $this->col_info[$i]->hidezeros)
                    {
                        $this->skipped_fields++;
                        $this->field[$i] = $this->col_info[$i]->name;
                        $this->skip[$i] = $this->col_info[$i]->name;
                    }
                }
            }
            if($this->loop_at==0) $this->output.="</tr></thead><tbody id=\"thebody$this->query_run\">";
        }

        function field_cell($i, $innerHTML, $link=false)
        {
            // if we have empty data we need to skip the building of field cell's also
            // if we are running a matrix we need to skip this check and build all cells and
            // all fields in the table
            // set classes and the starting html tags for vertical data
            if(isset($_GET[suppressfieldheaders])) return;
            if($this->options[0] == 'v')
            {
                $this->class = 'v_heading';
                $this->output .= "<tr>\n";
            }
            if($this->options[0] != "v") {
                //if(is_array($this->orderbys) && in_array($innerHTML, $this->orderbys)) { // $this->current_order==$innerHTML) {
                if($this->doorderlinks && !eregi("^<",$innerHTML)) { // && !isset($_GET[noheader])) {
                    $neworderdir=$this->current_order_direction=="asc" && $this->current_order_display==$innerHTML ? "desc" : (eregi("(open|close)", ereg_replace("^\((.*)\)$","\\1",$innerHTML) && $this->current_order_direction!="desc")?"desc":"asc");

                    if( isset( $_GET['e2mfindr'] ) )
                        $innerHTML="<a title='Click to sort by this column' href='javascript:void(0);'>".ereg_replace("^\((.*)\)$","\\1",$innerHTML)."</a>";
                    else
                        $innerHTML="<a title='Click to sort by this column' onclick='do_submit_order(this.href);return false;' href='".ereg_replace("&*TABLE_ORDERBY=[^&]*(&|$)","",ereg_replace("&*FIELD_ORDERBY=[^&]*(&|$)","",ereg_replace("&*FIELD_ORDERDIR=[^&]*(&|$)","",ereg_replace("[\]{0,1}'","%27",$_SERVER[REQUEST_URI])))).((strpos($_SERVER[REQUEST_URI],"?")>0)?"":"/?")."&TABLE_ORDERBY=".$this->query_run."&FIELD_ORDERBY=$innerHTML&FIELD_ORDERDIR=".$neworderdir."'>".ereg_replace("^\((.*)\)$","\\1",$innerHTML)."</a>"; //.(!isset($_REQUEST["FIELD_ORDERBY"])?"&".$this->poststr:"")
                }
                //}
                if($this->col_info[$i]->numeric) {
                    if( isset( $_GET['e2mfindr'] ) )
                        $this->output .= "<td ondblclick=\"Filter('thebody$this->query_run', ".($i-$this->skipped_fields).")\" nowrap align=left valign=top class=\"" . $this->class . "\" id=\"" . $this->class . "\"><b>" . ereg_replace("^\((.*)\)$","\\1",$innerHTML) . $this->icon . "</b></td>\n";
                    elseif(ALLOW_TABLE_FILTERING == 1 && false)
                        $this->output .= "<td ondblclick=\"Filter('thebody$this->query_run', ".($i-$this->skipped_fields).")\" onclick=\"document.body.style.cursor = 'wait';var myparams='TableSort(\\'thebody$this->query_run\\', ".($i-$this->skipped_fields).", \\'n\\')'; var tid=setTimeout(myparams,100);\" nowrap align=left valign=top class=\"" . $this->class . "\" id=\"" . $this->class . "\"><b>" . ereg_replace("^\((.*)\)$","\\1",$innerHTML) . $this->icon . "</b></td>\n";
                    else
                        $this->output .= "<td nowrap ".($this->col_info[$i]->width>0?"width=".$this->col_info[$i]->width." ":"")."align=left valign=top class=\"" . $this->class . "\" id=\"" . $this->class . "\"><b>" . ereg_replace("^\((.*)\)$","\\1",$innerHTML) . $this->icon . "</b></td>\n";
                } else {
                    if( isset( $_GET['e2mfindr'] ) )
                        $this->output .= "<td ondblclick=\"Filter('thebody$this->query_run', ".($i-$this->skipped_fields).")\" nowrap align=left valign=top class=\"" . $this->class . "\" id=\"" . $this->class . "\"><b>" . ereg_replace("^\((.*)\)$","\\1",$innerHTML) . $this->icon . "</b></td>\n";
                    elseif(ALLOW_TABLE_FILTERING == 1 && false)
                        $this->output .= "<td ".($this->col_info[$i]->width>0?"width=".$this->col_info[$i]->width." ":"")."ondblclick=\"Filter('thebody$this->query_run', ".($i-$this->skipped_fields).")\" onclick=\"document.body.style.cursor = 'wait';var myparams='TableSort(\\'thebody$this->query_run\\', ".($i-$this->skipped_fields).", \\'ai\\')'; var tid=setTimeout(myparams,100);\" nowrap align=left valign=top class=\"" . $this->class . "\" id=\"" . $this->class . "\"><b>" . ereg_replace("^\((.*)\)$","\\1",$innerHTML) . $this->icon . "</b></td>\n";
                    else
                        $this->output .= "<td nowrap ".($this->col_info[$i]->width>0?"width=".$this->col_info[$i]->width." ":"")."align=left valign=top class=\"" . $this->class . "\" id=\"" . $this->class . "\"><b>" . ereg_replace("^\((.*)\)$","\\1",$innerHTML) . $this->icon . "</b></td>\n";
                }
            }
            else
                $this->output .= "<td nowrap align=left ".($this->col_info[$i]->width>0?"width=".$this->col_info[$i]->width." ":"")."valign=top class=\"" . $this->class . "\" id=\"" . $this->class . "\"><b>" . ereg_replace("^\((.*)\)$","\\1",$innerHTML) . $this->icon . "</b></td>\n";				// if we have a vertical table we need to display all data in this

            // result-set in the single row
            if($this->options[0] == 'v')
            {
                // if the data is not linked then display normally
                if(!$link)
                {
                    for($z=0; $z<count($this->data); $z++)
                        $this->output .= "<td class=\"v_data\" valign=top>" . $this->data[$z][$i] . "</td>\n";
                }
                // otherwise loop through and setup the RID for outputting
                else
                {
                    for($z=0; $z<count($this->data); $z++)
                    {
                        if($z==0) $first_item = null($this->data[$z][$i]);
                        $this->output .= "<td class=\"v_data\" valign=top><a class=\"heading\" href=\"" . $this->sub_dir . "/" . $this->filename . "/" . $this->current_query . '.' . $this->col_info[$i]->name . "," . $this->query_run . "," . $this->col_info[$i]->name . "," . $first_item . "," . $this->data[$z][$i] . "\">" . $this->data[$z][$i] . "</a></td>\n";
                    }
                }
                $this->output .= "</tr>\n";
            }
        }

        /**
         * @return string
         * @param options array
         * @desc sets class global parameter options and queries titles and descriptions
         */
        function parameters()
        {
            global $device;
            $device->define("printperpage", $this->options[1]);



            list($this->options[2], $colwidths) = explode(":",$this->options[2],2);
            $this->colwidths=explode(":",$colwidths);
            $i=0;
            foreach($this->colwidths as $mywidth) {
                $this->colalign[$i]=strtolower(ereg_replace("[^lrLR]", "", $mywidth));
                $this->colwidths[$i++]=ereg_replace("[^0-9]", "", $mywidth);
            }
            if(!empty($this->options[2])) $this->width = $this->options[2]; else $this->width = "";
            if($this->options[1]==0) $this->options[1]=25;
            $this->display->variable("query_run", $this->query_run);
            $this->display->variable("calc_rows", $this->calc_rows);
            $this->page_links="";
            $this->num_pages=ceil($this->calc_rows/$this->options[1]);
            $this->current_page=floor($this->ident/$this->options[1])+1;
            $startnum=1;$endnum=$this->num_pages;
            if($this->num_pages>15) $endnum=15;
            if($this->current_page>7 && $this->num_pages>15) {
                $startnum=$this->current_page-7;
                if($this->num_pages-$startnum<14) $startnum=$this->num_pages-14;
                $endnum=$startnum+14;
            }
            for($i=$startnum;$i<=$endnum;$i++) $this->page_links.=(($i==$this->current_page)?"<font color=black>":"<font color=blue><a href=\"javascript:void(0);\" onmouseover='this.style.cursor=\"hand\"' onmouseout='this.style.cursor=\"default\"' onclick=\"document.limit.limit".$this->query_run.".value=".(($i-1)*$this->options[1]).";do_submit('".$this->query_run."');\">")."$i</a></font> ";
            if($endnum<$this->num_pages) { $i=$this->num_pages; $this->page_links.="... <font color=blue><a href=\"javascript:void(0);\" onmouseover='this.style.cursor=\"hand\"' onmouseout='this.style.cursor=\"default\"' onclick=\"document.limit.limit".$this->query_run.".value=".(($i-1)*$this->options[1]).";do_submit('".$this->query_run."');\">$i</a></font> "; }
            if($startnum>1) { $i=1; $this->page_links="<font color=blue><a href=\"javascript:void(0);\" onmouseover='this.style.cursor=\"hand\"' onmouseout='this.style.cursor=\"default\"' onclick=\"document.limit.limit".$this->query_run.".value=".(($i-1)*$this->options[1]).";do_submit('".$this->query_run."');\">$i</a></font> ... ".$this->page_links; }
            $this->display->variable("page_list", "<b>".$this->page_links."</b>| ");
            $this->display->variable("ident", ($this->ident+1));
            $this->display->variable("column_span", (count($this->col_info)+1));
            $this->display->variable("width", $this->width);
            if(!empty($this->options[3]))
            {
                if($this->options[0] == 'v')
                    // vertical table needs a different colspan count (data)
                    $this->display->variable("colspan", (count($this->data)+1));
                else
                    // horizonal table has a colspan count of the columns
                    $this->display->variable("colspan", (count($this->col_info)+1));

                if(eregi("^select ", $this->options[3]))
                    // if our title has a select at the beginning then execut it
                    $title = $this->background->get_var($this->options[3]);
                else
                    // otherwise just set the title according to what it is
                    $title = $this->options[3];

                // replace the <rr> tag with the number of records returned (only avaliable in the title)
                $title = str_replace("<rr>", $this->calc_rows, $title);
                $this->display->variable("title", (($this->use_database_list[0]!="") ? "<div align='left'>Database: ".$this->loop_db."</div> " : "").$title);
                $this->display->variable("description", $this->display->buffer("description"));
            }
            else $this->display->variable("description", '');
            if($this->loop_at == 0)
            {
                if($this->do_limiting)
                {
                    if(($this->calc_rows > $this->num_rows) && $_GET["print"] != "true") $this->display->variable("show", "style=\"display:\"");
                    else $this->display->variable("show", "style=\"display:none\"");
                }
                else
                    $this->display->variable("show", "style=\"display:none\"");
                if($this->options[0]==strtoupper($this->options[0])) $this->display->variable("show", "style=\"display:\"");
                // back and next buttons
                if($this->ident > 0)
                    $this->display->variable("back", $this->display->buffer("back_active"));
                else
                    $this->display->variable("back", $this->display->buffer("back"));

                if($this->calc_rows <= ($this->ident+$this->num_rows))
                    $this->display->variable("next", $this->display->buffer("next"));
                else
                    $this->display->variable("next", $this->display->buffer("next_active"));
                $this->display->variable("last_row", $this->ident+$this->num_rows);
                if($this->options[0] == 'v') $this->display->variable("table_class", "dataset_v");
                elseif( !empty( $this->options[4] ) && $this->options[4] == 'init_datatables' ) $this->display->variable("table_class", "init-datatable table table-striped table-bordered responsive nowrap");
                else $this->display->variable("table_class", "dataset");



                if( isset($_GET['e2mfindr']) ) {
                    $this->output .= $this->display->buffer("table_prefix_findr");
                }else
                    $this->output .= $this->display->buffer("table_prefix");
            } else {
                //$this->query_run--;
            }
            $this->ident=0;
        }

        /**
         * @return void
         * @desc return formated data array elements
         */
        function data()
        {
            // if there is a result-set and the table is horizonal or a matrix and
            // there are more than 0 results
            if($this->last_result && (strtolower($this->options[0]) == 'h' || strtolower($this->options[0]) == 'm' || empty($this->options[0])) && ($this->num_rows > 0))
            {
                // handles the table next/back tabulation
                if(!empty($this->limit_vars[1]))
                    $ident = $this->limit_vars[1];
                $background='#FFFFFF';
                foreach ($this->data as $one_row)
                {
                    if(isset($this->groupindex)) {
                        if($one_row[$this->groupindex]!=$previtem) $background=($background=='#FFFFFF'?'#E6E6E6':'#FFFFFF');
                        $previtem=$one_row[$this->groupindex];
                    }

                    if( isset($_GET["e2mvert"]) )
                        $this->row_data_mobile( $one_row, 0, 'none' );
                    elseif(isset($_GET["e2mfindr"]))
                        $this->row_data_findr($one_row, 0, $background);
                    elseif(isset($_GET["ebindr2"]))
                        $this->row_data2($one_row, 0, $background);
                    else
                        $this->row_data($one_row, 0, $background);

                    $this->ident++;
                }
            }
        }

        /**
         * @return void
         * @param item string
         * @param location integer
         * @param rowid integer
         * @desc builds the row of data according to item looping
         */
        function row_data_findr($one_row, $location=0, $background='#FFFFFF')
        {
            //if(!isset($_GET["noheader"])) $mouseover = "onmouseover=\"show_row('" . ($this->ident+1) . "', '" . $this->query_run . "');\"";
            //else
            global $parse, $browse_auto_bid_dir;
            $mouseover = "";
            $this->output .= "<tr id=\"" . ($this->ident+1) . "-" . $this->query_run . "\" " . $mouseover . ">\n";
            $this->currentbid="";
            $listremove="";$rowonclick="";
            foreach ($one_row as $item)
            {
                $item = $this->auto_devide($item, $location);
                $item = preg_replace(array("/\xe2\xae/", "/\xae/", "/\xe0/", "/\xe6/", "/\xe7/", "/\xe8/", "/\xc9/", "/\xe9/", "/\xea/", "/\xeb/", "/\xee/", "/\xef/", "/\xf4/", "/\xfb/", "/\xfc/"), array("&reg;", "&reg;", "&agrave;", "&aelig;", "&ccedil;", "&egrave;", "&Eacute;", "&eacute;", "&ecirc;", "&euml;", "&icirc;", "&iuml;", "&ocirc;", "&ucirc;", "&uuml;"), $item); //str_replace(chr(233),"&eacute;", $item);
                if(!empty($this->auto_devide[$location])) {
                    if($item<0) $item="<font color='red'>(".number_format(abs($item),2).")</font>";				}
                if($this->col_info[$location]->name=='$Total') $item="<b>".$item."</b>";
                if((ereg("^\?", $this->col_info[$location]->name) || ereg("\?$", $this->col_info[$location]->name)) && ($item=='y' || $item=='n')) {
                    $this->align[$location] = ' align="center"';
                    if(strpos($this->linked[$location], "editr"))
                        $editr_link = $this->filename . '/' . $this->current_query . "," . $this->query_run . ",yn," . (str_replace('/','',$value_first)) . "," . (str_replace('"','%22',str_replace('#','%23',str_replace('/','',$item)))) . $this->extension ."&editr";
                    $item = "<input type=\"checkbox\" onclick=\"this.checked=".(($item=='y')?"true":"false").";".($editr_link?"window.location='$editr_link';":"")."\"".(($item=='y')?"checked":"").">";
                }
                if($location == 0)
                    $value_first = null($item);
                // if the data is linked and its not in the skip sub-program and not a matrix
                if($listremove=="" && eregi("^[*]*(b|c|e)id$", $this->col_info[$location]->name) && isset($parse->params["EXCEPTION LIST"])) {
                    $listremove="<td><input title='Remove this item from the list' type=checkbox id='except$item'".(ereg("(^|,)".$item."(,|$)",$this->exceptionlist)?"checked":"")."></td>";
                    $this->display->variable("exceptionlist", "<input title=\"Remove checked items from list\" type=button value='Remove' onclick='Except()'> <input title=\"Show removed items\" type=button value='Show' onclick='ShowExcept()'".(ereg("[0-9]",$parse->params["EXCEPTION LIST"])?"":" disabled")."> <input title=\"Restore removed items\" type=button value='Restore' onclick='ClearExcept()'".(($this->exceptionlist>"" || ereg("[0-9]",$parse->params["EXCEPTION LIST"])) && $_POST["EXCEPTIONLIST"]=="VIEW"?"":" disabled").">");
                }

                if(eregi("^[(]*bid[)]*$", $this->col_info[$location]->name)) {
                    /*if( isset( $_GET['e2mfindr'] ) ){
                        $onclick = "onclick='window.parent.ebindr.openBID(\"$item\");'";
                        $dblclick = "onmouseover=\"document.body.style.cursor = 'pointer';\" onmouseout=\"document.body.style.cursor = 'default';\"";
                        $dblclickeditr = "onmouseover=\"document.body.style.cursor = 'pointer';\" onmouseout=\"document.body.style.cursor = 'default';\"";
                        $jumpback = "onmouseover=\"document.body.style.cursor = 'pointer';\" onmouseout=\"document.body.style.cursor = 'default';\" style='text-decoration:underline;'";
                    }else{*/

                        $dblclick = "onmouseover=\"document.body.style.cursor = 'pointer';\" onmouseout=\"document.body.style.cursor = 'default';\" onclick='window.parent.ebindr.findr2.openBID(\"$item\");'";
                        $dblclickeditr = "onmouseover=\"document.body.style.cursor = 'pointer';\" onmouseout=\"document.body.style.cursor = 'default';\" onclick='window.parent.ebindr.findr2.openBID(\"$item\", null, null, false);'";
                        $jumpback = "onmouseover=\"document.body.style.cursor = 'pointer';\" onmouseout=\"document.body.style.cursor = 'default';\" style='text-decoration:underline;' onclick='window.parent.ebindr.findr2.openBID(\"$item\"); window.parent.dopage(\"records\");'";

                    //}
                    $this->currentbid=$item;
                    $item = "<a href=\"javascript:void(0);\" onclick=\"findrOpenBID('".$item."');\">".$item."</a>";
                }
                elseif(eregi("^[(]*cid[)]*$", $this->col_info[$location]->name)) {
                    if($this->currentbid=="") $this->currentbid=$one_row[0];
                    /*if( isset( $_GET['e2mfindr'] ) ){
                        $onclick = "onclick='window.parent.ebindr.openBID(\"$item\");'";
                        $dblclick = "onmouseover=\"document.body.style.cursor = 'pointer';\" onmouseout=\"document.body.style.cursor = 'default';\"";
                        $dblclickeditr = "onmouseover=\"document.body.style.cursor = 'pointer';\" onmouseout=\"document.body.style.cursor = 'default';\"";
                        $jumpback = "onmouseover=\"document.body.style.cursor = 'pointer';\" onmouseout=\"document.body.style.cursor = 'default';\" style='text-decoration:underline;'";
                    }else{*/

                        $dblclick = "onmouseover=\"document.body.style.cursor = 'pointer';\" onmouseout=\"document.body.style.cursor = 'default';\" onclick='window.parent.ebindr.findr2.openBID(\"".$this->currentbid."\",false,\"$item\");'";
                        $dblclickeditr = "onmouseover=\"document.body.style.cursor = 'pointer';\" onmouseout=\"document.body.style.cursor = 'default';\" onclick='window.parent.ebindr.findr2.openBID(\"".$this->currentbid."\",false,\"$item\",false);'";
                        $jumpback = "onmouseover=\"document.body.style.cursor = 'pointer';\" onmouseout=\"document.body.style.cursor = 'default';\" style='text-decoration:underline;' onclick='window.parent.ebindr.findr2.openBID(\"".$this->currentbid."\",false,\"$item\"); window.parent.dopage(\"records\");'";
                        $item = "<div onclick=\"window.parent.ebindr.openBID('".$this->currentbid."',false,'".$item."');\">".$item."</div>";
                    //}
                }
                else
                {
                    $dblclick="";
                    $jumpback="";
                }


                if(eregi("^[*]ONCLICK$", $this->col_info[$location]->name)) {
                    $rowonclick=$item;
                }
                if(!empty($this->linked[$location]) && empty($this->skip[$location]))
                {
                    $atitle=$this->related_desc[$location];
                    while(ereg("\[FIELD ([0-9])\]",$atitle,$regs))
                        $atitle=ereg_replace("\[FIELD ".$regs[1]."\]", strip_tags($one_row[$regs[1]]),$atitle);

                    $this->output .= "<td id=\"$this->query_run-".($this->ident+1)."-$location\">".$item."</td>";

                }
                // not in the skip sub-program or matrix table
                elseif(empty($this->skip[$location]))
                {
                    // normal data
                    if($this->options[0] != 'm') {
                        if(ereg("(BID|CID|MED|SC|MISC|VORP|VIP)FILES", $item, $regs)) {
                            $filesexist=false; $filename="";
                            $filecid=$this->data[$this->ident][$this->identityrow[strtolower(($regs[1]=="VORP" || $regs[1]=="MED" || $regs[1]=="SC" || $regs[1]=="MISC" || $regs[1]=="VIP"?"CID":$regs[1]))]];
                            if(strlen($filecid)<3) $filecid.="XX";
                            if(MAKE_ALL_FOLDERS=="YES") {
                                @mkdir(DOCS_BASE_DIR.'/'.strtolower($regs[1]));
                                @mkdir(DOCS_BASE_DIR.'/'.strtolower($regs[1]).'/'.substr($filecid,strlen($filecid)-2));
                                @mkdir(DOCS_BASE_DIR.'/'.strtolower($regs[1]).'/'.substr($filecid,strlen($filecid)-2).'/'.substr($filecid,0,strlen($filecid)-2));
                                $basedir=DOCS_BASE_DIR.'/'.strtolower($regs[1]).'/'.substr($filecid,strlen($filecid)-2).'/'.substr($filecid,0,strlen($filecid)-2);
                                @chmod(DOCS_BASE_DIR.'/'.strtolower($regs[1]), 0777);
                                @chmod(DOCS_BASE_DIR.'/'.strtolower($regs[1]).'/'.substr($filecid,strlen($filecid)-2), 0777);
                                @chmod(DOCS_BASE_DIR.'/'.strtolower($regs[1]).'/'.substr($filecid,strlen($filecid)-2).'/'.substr($filecid,0,strlen($filecid)-2), 0777);
                                if(is_array($browse_auto_bid_dir) && ereg("BID", $regs[1]))
                                    foreach($browse_auto_bid_dir as $onedir) if(!file_exists($basedir."/".$onedir) && (!eregi("member",$onedir))) mkdir($basedir."/".$onedir);
                                if(!file_exists($basedir."/trash")) {
                                    mkdir($basedir."/trash");
                                }
                            }
                            $directory = DOCS_BASE_DIR.'/'.strtolower($regs[1]).'/'.substr($filecid,strlen($filecid)-2).'/'.substr($filecid,0,strlen($filecid)-2);
                            if($d = @opendir($directory)) {
                                while(false !== ($filename = readdir($d)))
                                    if ($filename != "." && $filename != ".." && $filename!='trash' && !ereg("^[.]", $filename)) { $filelist[]=$filename; if(!is_dir($filename)) $filesexist=true; }
                            }
                            if(!$this->is_empty_dir($directory)) $item="docs"; else $item="docsnone";
                            $context='';
                            if($this->networkfolder>'') {
                                $context='window.clipboardData.setData("Text","'.str_replace("\\", "\\\\", $this->networkfolder).$regs[1]."\\\\".substr($filecid,strlen($filecid)-2)."\\\\".substr($filecid,0,strlen($filecid)-2).'"); return false;';
                            }
                            $item="<a oncontextmenu='$context' title=\"Click here to add/manage/view documents associated with this record.\" href=\"javascript:window.parent.FileBrowser('".strtolower($regs[1])."', '".str_replace("X","",$filecid)."')\"><img src='/css/$item.gif' border=0></a>";
                            // onclick='document.getElementById(\"FILELIST$filecid\").style.display=\"block\"'><span style=\"display:none;position:absolute\" id='FILELIST$filecid' onmouseout='this.style.display=\"none\"'>$myfiles</span>"//							$item=$directory;
                        }
                        /*if( isset($_GET['e2mfindr']) && isset( $onclick ) )
                            $this->output .= "<td $jumpback id=\"$this->query_run-" . ($this->ident+1) . "-$location\" onclick=\"this_select(this, '".str_replace('\"','',addslashes($one_row[0]))."','" . str_replace('\"','',addslashes($one_row[1])). "', '$rowonclick');\" nowrap class=\"data\" style=\"background:$background\" valign=top " . $this->align[$location] . "><a href='javascript:void(0)' ". $onclick .">" . $item . ($item==""?"&nbsp;":"")."</a></td>\n";
                        else*/
                        //$this->output .= "<td $jumpback id=\"$this->query_run-" . ($this->ident+1) . "-$location\" onclick=\"this_select(this, '".str_replace('\"','',addslashes($one_row[0]))."','" . str_replace('\"','',addslashes($one_row[1])). "', '$rowonclick');\" nowrap class=\"data\" style=\"background:$background\" valign=top " . $this->align[$location] . "><span ". $onclick .">" . $item . ($item==""?"&nbsp;":"")."</span></td>\n";
                        $this->output .= "<td $jumpback id=\"$this->query_run-" . ($this->ident+1) . "-$location\" onclick=\"this_select(this, '".str_replace('\"','',addslashes($one_row[0]))."','" . str_replace('\"','',addslashes($one_row[1])). "', '$rowonclick');\" nowrap class=\"data\" style=\"background:$background\" valign=top " . $this->align[$location] . ">" . $item . ($item==""?"&nbsp;":"")."</td>\n";
                    }
                    // matrix linking of data (run on all cells)
                    else
                    { // added the started linking variable to bypass invisible columns in 1st field skipping in the matrix tables
                        if($location === 0 || ($location == 1 && ($this->col_info[$location]->name{0} == "&" || $this->col_info[$location]->name{0} == "*" ||  $this->col_info[$location]->hidezeros)) || (!$this->started_linking[$this->ident])) {
                            /*if( isset($_GET['e2mfindr']) && isset( $onclick ) )
                                $this->output .= "<td id=\"$this->query_run-" . ($this->ident+1) . "-$location\" onclick=\"this_select(this, '".str_replace('\"','',addslashes($one_row[0]))."','" . str_replace('\"','',addslashes($one_row[1])). "', '$rowonclick');\" nowrap class=\"data\" valign=top " . $this->align[$location] . "><a href='javascript:void(0)' ". $onclick .">" . $item . "</a></td>\n";
                            else*/
                                $this->output .= "<td id=\"$this->query_run-" . ($this->ident+1) . "-$location\" onclick=\"this_select(this, '".str_replace('\"','',addslashes($one_row[0]))."','" . str_replace('\"','',addslashes($one_row[1])). "', '$rowonclick');\" nowrap class=\"data\" valign=top " . $this->align[$location] . ">" . $item . "&nbsp;</td>\n";
                        } else {
                            $this->output .= "<td $jumpback id=\"$this->query_run-" . ($this->ident+1) . "-$location\" onclick=\"this_select(this, '".str_replace('\"','',addslashes($one_row[0]))."','" . str_replace('\"','',addslashes($one_row[1])). "');\" nowrap class=\"data\" valign=top " . $this->align[$location] . "><a title=\"".$this->related_desc[$location]."\" href=\"" . $this->sub_dir . "/" . $this->filename . "/" . $this->current_query . '.' . $this->matrix_next . "," . urlencode($this->query_run) . "," . urlencode($this->field[$location]) . "," . urlencode(str_replace('/','',$value_first)) . "," . urlencode(str_replace('/','',$item)) . $this->extension . "\">" . $item . "&nbsp;</a></td>\n";
                        }
                        // if the next field isn't hidden then we need to start linking
                        if($this->col_info[($location+1)]->name{0} != "*") {
                            $this->started_linking[$this->ident] = true;
                        }
                    }
                }
                $location++;
            }
            $this->output .= $listremove."</tr>\n";
        }

        /**
         * @return void
         * @param item string
         * @param location integer
         * @param rowid integer
         * @desc builds the row of data according to item looping
         */
        function row_data_mobile($one_row, $location=0, $background='none')
        {
            //if(!isset($_GET["noheader"])) $mouseover = "onmouseover=\"show_row('" . ($this->ident+1) . "', '" . $this->query_run . "');\"";
            //else
            global $parse, $browse_auto_bid_dir;
            $mouseover = "";
            $this->output .= "<tr id=\"" . ($this->ident+1) . "-" . $this->query_run . "\" " . $mouseover . "><td colspan=\"". count( $this->col_info ) ."\" class=\"mobile-list-col\">\n";
            $this->output .= "<ul class='mobile-business-collection list-group unstyled'>";
            $this->currentbid="";
            $listremove="";$rowonclick="";
            foreach ($one_row as $index => $item)
            {
                $item = $this->auto_devide($item, $location);
                $item = preg_replace(array("/\xe2\xae/", "/\xae/", "/\xe0/", "/\xe6/", "/\xe7/", "/\xe8/", "/\xc9/", "/\xe9/", "/\xea/", "/\xeb/", "/\xee/", "/\xef/", "/\xf4/", "/\xfb/", "/\xfc/"), array("&reg;", "&reg;", "&agrave;", "&aelig;", "&ccedil;", "&egrave;", "&Eacute;", "&eacute;", "&ecirc;", "&euml;", "&icirc;", "&iuml;", "&ocirc;", "&ucirc;", "&uuml;"), $item); //str_replace(chr(233),"&eacute;", $item);
                if(!empty($this->auto_devide[$location])) {
                    if($item<0) $item="<font color='red'>(".number_format(abs($item),2).")</font>";				}
                if($this->col_info[$location]->name=='$Total') $item="<b>".$item."</b>";
                if((ereg("^\?", $this->col_info[$location]->name) || ereg("\?$", $this->col_info[$location]->name)) && ($item=='y' || $item=='n')) {
                    $this->align[$location] = ' align="center"';
                    if(strpos($this->linked[$location], "editr"))
                        $editr_link = $this->filename . '/' . $this->current_query . "," . $this->query_run . ",yn," . (str_replace('/','',$value_first)) . "," . (str_replace('"','%22',str_replace('#','%23',str_replace('/','',$item)))) . $this->extension ."&editr";
                    $item = "<input type=\"checkbox\" onclick=\"this.checked=".(($item=='y')?"true":"false").";".($editr_link?"window.location='$editr_link';":"")."\"".(($item=='y')?"checked":"").">";
                }
                if($location == 0)
                    $value_first = null($item);
                // if the data is linked and its not in the skip sub-program and not a matrix
                if($listremove=="" && eregi("^[*]*(b|c|e)id$", $this->col_info[$location]->name) && isset($parse->params["EXCEPTION LIST"])) {
                    $listremove="<li><input title='Remove this item from the list' type=checkbox id='except$item'".(ereg("(^|,)".$item."(,|$)",$this->exceptionlist)?"checked":"")."></li>";
                    $this->display->variable("exceptionlist", "<input title=\"Remove checked items from list\" type=button value='Remove' onclick='Except()'> <input title=\"Show removed items\" type=button value='Show' onclick='ShowExcept()'".(ereg("[0-9]",$parse->params["EXCEPTION LIST"])?"":" disabled")."> <input title=\"Restore removed items\" type=button value='Restore' onclick='ClearExcept()'".(($this->exceptionlist>"" || ereg("[0-9]",$parse->params["EXCEPTION LIST"])) && $_POST["EXCEPTIONLIST"]=="VIEW"?"":" disabled").">");
                }

                if(eregi("^[(]*bid[)]*$", $this->col_info[$location]->name)) {
                    $dblclick = "onmouseover=\"document.body.style.cursor = 'pointer';\" onmouseout=\"document.body.style.cursor = 'default';\" onclick='window.parent.ebindr.openBID(\"$item\");'";
                    $dblclickeditr = "onmouseover=\"document.body.style.cursor = 'pointer';\" onmouseout=\"document.body.style.cursor = 'default';\" onclick='window.parent.ebindr.openBID(\"$item\", null, null, false);'";
                    $jumpback = "onmouseover=\"document.body.style.cursor = 'pointer';\" onmouseout=\"document.body.style.cursor = 'default';\" style='text-decoration:underline;' onclick='window.parent.ebindr.openBID(\"$item\"); window.parent.dopage(\"records\");'";
                    $this->currentbid=$item;
                } elseif(eregi("^[(]*cid[)]*$", $this->col_info[$location]->name)) {
                    if($this->currentbid=="") $this->currentbid=$one_row[0];
                    $dblclick = "onmouseover=\"document.body.style.cursor = 'pointer';\" onmouseout=\"document.body.style.cursor = 'default';\" onclick='window.parent.ebindr.openBID(\"".$this->currentbid."\",false,\"$item\");'";
                    $dblclickeditr = "onmouseover=\"document.body.style.cursor = 'pointer';\" onmouseout=\"document.body.style.cursor = 'default';\" onclick='window.parent.ebindr.openBID(\"".$this->currentbid."\",false,\"$item\",false);'";
                    $jumpback = "onmouseover=\"document.body.style.cursor = 'pointer';\" onmouseout=\"document.body.style.cursor = 'default';\" style='text-decoration:underline;' onclick='window.parent.ebindr.openBID(\"".$this->currentbid."\",false,\"$item\"); window.parent.dopage(\"records\");'";
                } else {
                    $dblclick="";
                    $jumpback="";
                }
                if(eregi("^[*]ONCLICK$", $this->col_info[$location]->name)) {
                    $rowonclick=$item;
                }
                if(!empty($this->linked[$location]) && empty($this->skip[$location]))
                {
                    $atitle=$this->related_desc[$location];
                    while(ereg("\[FIELD ([0-9])\]",$atitle,$regs))
                        $atitle=ereg_replace("\[FIELD ".$regs[1]."\]", strip_tags($one_row[$regs[1]]),$atitle);
                    // key word linking
                    if(@in_array($this->linked[$location], $this->key_words))
                        $this->output .= "<li alt=\"".$this->col_info[$index]->name."\" $dblclick id=\"$this->query_run-" . ($this->ident+1) . "-$location\" onclick=\"this_select(this, '".str_replace('\"','',addslashes($one_row[0]))."','" . str_replace('\"','',addslashes($one_row[1])). "', '$rowonclick');\" nowrap class=\"data list-group-item item-". $this->col_info[$index]->name  ."\" valign=top " . $this->align[$location] . "><a title=\"".$this->related_desc[$location]."\" href=\"" . $this->sub_dir . "/" . $this->filename . "/" . $this->current_query . '.' . str_replace('/','',$item) . "," . $this->query_run . "," . (str_replace('/','',$item)) . "," . (str_replace('/','',$value_first)) . "," . (str_replace('/','',$item)) . $this->extension . "\">" . $item . "&nbsp;</a></li>\n";
                    // editr linking
                    elseif(strpos($this->linked[$location], "editr"))
                    {
                        $editr_link = $this->filename . '/' . $this->current_query . "," . $this->query_run . "," . str_replace(".editr", "", $this->linked[$location]) . "," . (str_replace('/','',$value_first)) . "," . (str_replace('"','%22',str_replace('#','%23',str_replace('/','',$item)))) . $this->extension ."&editr";
//						$dblclick="ondblclick='window.parent.key1=\"\";' ";
                        if ( strpos($editr_link, 'lite button by') > -1 ) {
                            $this->output .= "<li alt=\"".$this->col_info[$index]->name."\" $dblclickeditr ondblclick='window.parent.key1=\"\";' id=\"$this->query_run-" . ($this->ident+1) . "-$location\" onclick=\"this_select(this, '".str_replace('\"','',addslashes($one_row[0]))."','" . str_replace('\"','',addslashes($one_row[1])). "', '$rowonclick');\" nowrap class=\"data list-group-item item-". $this->col_info[$index]->name  ."\" valign=top " . $this->align[$location] . ">" . $item . "</li>\n";
                        } else {
                            $this->output .= "<li alt=\"".$this->col_info[$index]->name."\" $dblclickeditr ondblclick='window.parent.key1=\"\";' id=\"$this->query_run-" . ($this->ident+1) . "-$location\" onclick=\"this_select(this, '".str_replace('\"','',addslashes($one_row[0]))."','" . str_replace('\"','',addslashes($one_row[1])). "', '$rowonclick');\" nowrap class=\"data list-group-item item-". $this->col_info[$index]->name  ."\" valign=top " . $this->align[$location] . "><a ondblclick='window.parent.key1=\"\";' title=\"".$atitle."\" href=\"/" . $editr_link . "\">" . $item . "</a></li>\n";
                        }
                    }
                    // normal related query linking
                    else
                        $this->output .= "<li alt=\"".$this->col_info[$index]->name."\" $dblclick id=\"$this->query_run-" . ($this->ident+1) . "-$location\" onclick=\"this_select(this, '".str_replace('\"','',addslashes($one_row[0]))."','" . str_replace('\"','',addslashes($one_row[1])). "', '$rowonclick');\" nowrap class=\"data list-group-item item-". $this->col_info[$index]->name  ."\" valign=top " . $this->align[$location] . "><a title=\"".$atitle."\" href=\"" . $this->sub_dir . "/" . $this->filename . "/" . $this->current_query . '.' . $this->linked[$location] . "," . $this->query_run . "," . $this->linked[$location] . "," . (str_replace('/','',$value_first)) . "," . (str_replace('/','',$item)) . $this->extension . "\">" . $item . "&nbsp;</a></li>\n";
                }
                // not in the skip sub-program or matrix table
                elseif(empty($this->skip[$location]))
                {
                    // normal data
                    if($this->options[0] != 'm') {
                        if(ereg("(BID|CID|MED|SC|MISC|VORP|VIP)FILES", $item, $regs)) {
                            $filesexist=false; $filename="";
                            $filecid=$this->data[$this->ident][$this->identityrow[strtolower(($regs[1]=="VORP" || $regs[1]=="MED" || $regs[1]=="SC" || $regs[1]=="MISC" || $regs[1]=="VIP"?"CID":$regs[1]))]];
                            if(strlen($filecid)<3) $filecid.="XX";
                            if(MAKE_ALL_FOLDERS=="YES") {
                                @mkdir(DOCS_BASE_DIR.'/'.strtolower($regs[1]));
                                @mkdir(DOCS_BASE_DIR.'/'.strtolower($regs[1]).'/'.substr($filecid,strlen($filecid)-2));
                                @mkdir(DOCS_BASE_DIR.'/'.strtolower($regs[1]).'/'.substr($filecid,strlen($filecid)-2).'/'.substr($filecid,0,strlen($filecid)-2));
                                $basedir=DOCS_BASE_DIR.'/'.strtolower($regs[1]).'/'.substr($filecid,strlen($filecid)-2).'/'.substr($filecid,0,strlen($filecid)-2);
                                @chmod(DOCS_BASE_DIR.'/'.strtolower($regs[1]), 0777);
                                @chmod(DOCS_BASE_DIR.'/'.strtolower($regs[1]).'/'.substr($filecid,strlen($filecid)-2), 0777);
                                @chmod(DOCS_BASE_DIR.'/'.strtolower($regs[1]).'/'.substr($filecid,strlen($filecid)-2).'/'.substr($filecid,0,strlen($filecid)-2), 0777);
                                if(is_array($browse_auto_bid_dir) && ereg("BID", $regs[1]))
                                    foreach($browse_auto_bid_dir as $onedir) if(!file_exists($basedir."/".$onedir) && (!eregi("member",$onedir))) mkdir($basedir."/".$onedir);
                                if(!file_exists($basedir."/trash")) {
                                    mkdir($basedir."/trash");
                                }
                            }
                            $directory = DOCS_BASE_DIR.'/'.strtolower($regs[1]).'/'.substr($filecid,strlen($filecid)-2).'/'.substr($filecid,0,strlen($filecid)-2);
                            if($d = @opendir($directory)) {
                                while(false !== ($filename = readdir($d)))
                                    if ($filename != "." && $filename != ".." && $filename!='trash' && !ereg("^[.]", $filename)) { $filelist[]=$filename; if(!is_dir($filename)) $filesexist=true; }
                            }
                            if(!$this->is_empty_dir($directory)) $item="docs"; else $item="docsnone";
                            $context='';
                            if($this->networkfolder>'') {
                                $context='window.clipboardData.setData("Text","'.str_replace("\\", "\\\\", $this->networkfolder).$regs[1]."\\\\".substr($filecid,strlen($filecid)-2)."\\\\".substr($filecid,0,strlen($filecid)-2).'"); return false;';
                            }
                            $item="<a oncontextmenu='$context' title=\"Click here to add/manage/view documents associated with this record.\" href=\"javascript:window.parent.FileBrowser('".strtolower($regs[1])."', '".str_replace("X","",$filecid)."')\"><img src='/css/$item.gif' border=0></a>";
                            // onclick='document.getElementById(\"FILELIST$filecid\").style.display=\"block\"'><span style=\"display:none;position:absolute\" id='FILELIST$filecid' onmouseout='this.style.display=\"none\"'>$myfiles</span>"//							$item=$directory;
                        }
                        $this->output .= "<li alt=\"".$this->col_info[$index]->name."\" $jumpback id=\"$this->query_run-" . ($this->ident+1) . "-$location\" onclick=\"this_select(this, '".str_replace('\"','',addslashes($one_row[0]))."','" . str_replace('\"','',addslashes($one_row[1])). "', '$rowonclick');\" nowrap class=\"data list-group-item item-". $this->col_info[$index]->name  ."\" style=\"background:$background\" valign=top " . $this->align[$location] . ">" . $item . ($item==""?"&nbsp;":"")."</li>\n";
                    }
                    // matrix linking of data (run on all cells)
                    else
                    { // added the started linking variable to bypass invisible columns in 1st field skipping in the matrix tables
                        if($location === 0 || ($location == 1 && ($this->col_info[$location]->name{0} == "&" || $this->col_info[$location]->name{0} == "*" ||  $this->col_info[$location]->hidezeros)) || (!$this->started_linking[$this->ident])) {
                            $this->output .= "<li alt=\"".$this->col_info[$index]->name."\" id=\"$this->query_run-" . ($this->ident+1) . "-$location\" onclick=\"this_select(this, '".str_replace('\"','',addslashes($one_row[0]))."','" . str_replace('\"','',addslashes($one_row[1])). "', '$rowonclick');\" nowrap class=\"data list-group-item item-". $this->col_info[$index]->name  ."\" valign=top " . $this->align[$location] . ">" . $item . "&nbsp;</li>\n";
                        } else {
                            $this->output .= "<li alt=\"".$this->col_info[$index]->name."\" $jumpback id=\"$this->query_run-" . ($this->ident+1) . "-$location\" onclick=\"this_select(this, '".str_replace('\"','',addslashes($one_row[0]))."','" . str_replace('\"','',addslashes($one_row[1])). "');\" nowrap class=\"data list-group-item item-". $this->col_info[$index]->name  ."\" valign=top " . $this->align[$location] . "><a title=\"".$this->related_desc[$location]."\" href=\"" . $this->sub_dir . "/" . $this->filename . "/" . $this->current_query . '.' . $this->matrix_next . "," . urlencode($this->query_run) . "," . urlencode($this->field[$location]) . "," . urlencode(str_replace('/','',$value_first)) . "," . urlencode(str_replace('/','',$item)) . $this->extension . "\">" . $item . "&nbsp;</a></li>\n";
                        }
                        // if the next field isn't hidden then we need to start linking
                        if($this->col_info[($location+1)]->name{0} != "*") {
                            $this->started_linking[$this->ident] = true;
                        }
                    }
                }
                $location++;
            }
            $this->output .= $listremove."</ul></td></tr>\n";
        }
    }
}