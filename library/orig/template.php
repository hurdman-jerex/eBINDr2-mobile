<?php
/**
* Template class
*
* @author      James Moore <jmoore@servability.com> & James Kent <jkent@servability.com>
* @copyright   James Moore & James Kent
* @edited	   Doug Tanner <doug@melonn.com>
* @configured  Doug Tanner
*/

if ( !class_exists('template') ) {

	class template{
		
		var $version = "2.02"; // Current Version
		var $apiversion = "25032001"; // API Version

		var $defined = array(); // Array of defined values
		var $templates = array(); // Array of Templates
		var $path = "./"; // Path of template dir
		var $parseflag = array(); // Flag if Parse is on/off
		var $flagset = array(); // Flag if parser has been on/off
		var $level = 0; // Current Nesting level of ifs
		var $line = 0; // Current Line in template
		var $line_offset = 0; // Current line offset
		var $template_buff = array(); // Buffer for template
		var $output_buff = ""; // Output Buffer
		var $opening_tag = "<["; // Opening Tag
		var $closing_tag = "]>"; // Closing Tag
		var $last_char = ""; // Holder for last char fetched
		var $last_token = ""; // Holder for last token fetched
		var $tok_sep_char = " "; // a character that separates tokens shuch as |
		var $debug = TRUE; // Is debugging on
		var $template_name = ""; // holds current templatename
		
		/**
		* @param string $path path of the template
		* @desc passed the path of the template directory initializes the objects constants and assgins the path
		*/
		function template( $path )
		{
			define("PARSER_ON", TRUE);
			define("PARSER_OFF", FALSE);
			define("SUCCESS", TRUE);
			define("FAILED", FALSE);
			define("EOF", -1);
			
			$this->path = $path;
		} // end template() constructor
		
		/*******************************
		*        PUBLIC METHODS        *
		*******************************/
		
		/**
		* @param string $name name of file
		* @param string $file location of file
		* @desc adds a template to the catalog, accepts either an array or single template name
		*/
		function addtemplate( $name, $file="" )
		{
			// handle being passed as an array
			if( is_array($name) ) {
				$return = SUCCESS;
				
				while((list($tempname, $filename) = each($name)) && ($return == SUCCESS))
				{
					// wrapping function
					$return = $this->templateadd($tempname, $filename);
				}
			} else {
				// handle being passed as a single template
				$return = $this->templateadd($name, $file);
			}
			
			return $return;
		} // end addtemplate() method
		
		/**
		* @param string $name name of constant
		* @param string $contents the value of the constant
		* @param integer $flag set flag
		* @desc Defines a constant to be usr in the parseing of the script. Can be passed an array or a single value
		*/
		function define( $name, $contents="", $flag=0 )
		{
			// handle being passed as an array
			if( is_array($name) ) {
				while(list($constname, $constvalue) = each($name))
				{
					// if final arguement is 1 then append to previous constant that was defined
					if($flag) {
						// append value to defined array
						$this->defined[strtolower($constname)] .= $constvalue;
					} else {
						// write the value to defined array
						$this->defined[strtolower($constname)] = $constvalue;
					}
				}
			} else {
				// if final arguument is 1 then append to previous constant that was defined
				if($flag) {
					// append values to defined array
					$this->defined[strtolower($name)] .= $contents;
				} else {
					// write value to the defined array
					$this->defined[strtolower($name)] = $contents;
				}
			}
		} // end define() method
		
		/**
		* @param string $reference reference name
		* @param string $template the template to parse
		* @param integer $flag set flag
		* @desc Starts the parsing process of a template
		*/
		function parse( $reference, $template, $flag=0 )
		{
			// clear all vars used and resent to original values
			$this->template_buff = array();
			$this->line = 0;
			$this->line_offset = 0;
			$this->parseflag = array(PARSER_ON);
			$this->flagset = array(PARSER_OFF);
			$this->level = 0;
			$this->output_buff = NULL;
			$this->template_name = $template;
			
			// get contents of template and assign to needed vars
			// put contents of template into template_buff
			$this->template_buff = explode("\n", $this->templates[strtolower($template)]);
			
			// check for special cases where template starts with an instruction
			if( ($this->template_buff[$this->line][$this->line_offset] == $this->opening_tag[0]) && ($this->template_buff[$this->line][($this->line_offset + 1)] == $this->opening_tag[1]) ) {
				// if it does then move to the beginning of the instruction and call handler
				$this->line_offset += 2;
				$this->handle_instruction();
			} else {
				// the template starts with text so lets send it tot he correct handler
				$this->handle_text();
			}

			if($flag) {
				$this->defined[strtolower($reference)] .= $this->output_buff;
			} else {
				$this->defined[strtolower($reference)] = $this->output_buff;
			}

			return SUCCESS;
		} // end of parse() method
		
		/**
		* @param string $reference reference name of parser
		* @desc Returns the value of reference to the caller
		*/
		function output( $reference )
		{
			return $this->defined[strtolower($reference)];
		} // end of output() method
		
		/*******************************
		*       PRIVATE METHODS        *
		*******************************/
		
		/**
		* @param string $templatename name of the template to be used
		* @param string $templatefile the file location of the template
		* @desc Internal array wrapper to add templates
		*/
		function templateadd( $templatename, $templatefile )
		{
			// check to see if file exists
			if( file_exists($this->path . $templatefile) ) {
				if( $fp = fopen($this->path . $templatefile, "r") ) {
					// lock the file
					//flock($fp, LOCK_SH);
					// read the file
					$contents = fread($fp, filesize($this->path . $templatefile));
					// unlock the file
					//flock($fp, LOCK_UN);
					// close the file
					fclose($fp);
					// add to template catalog
					$this->templates[strtolower($templatename)] = $contents;
					
					return true;
				} else {
					// could not open file
					if($this->debug) {
						// will have error() handling installed here soon
						echo ("could not open file ".$this->path.$templatefile);
					}
					
					return false;
				}
			} else {
				// file does not exist
				if( $this->debug ) {
					// will have error() handling installed here soon
					echo ("could not find file ".$this->path.$templatefile);
				}
				
				return false;
			}
		} // end of templateadd() method
		
		/**
		* @param string $defined name of the template to be used
		* @param string $rval the file location of the template
		* @param string $operator is the operator being used
		* @desc evaluates an expression passed in three parts, the defined value is the first operand, the operator is the logical statement and the senond operand is the value for the first to be compared with
		*/
		function evaluate( $defined, $rval, $operator )
		{
			$lval = $this->defined[strtolower($defined)];
			
			switch( $operator ):
			
				case ">":	$retval = (($lval	>	$rval) ? TRUE : FALSE); break;
				case "<":	$retval = (($lval	<	$rval) ? TRUE : FALSE); break;
				case ">=":	$retval = (($lval	>=	$rval) ? TRUE : FALSE); break;
				case "<=":	$retval = (($lval	<=	$rval) ? TRUE : FALSE); break;
				case "==":	$retval = (($lval	==	$rval) ? TRUE : FALSE); break;
				case "!=":	$retval = (($lval	!=	$rval) ? TRUE : FALSE); break;

				// will have error() handling installed here
				default: echo("$lval, Your using an operator I dont understand");
				
			endswitch;

			return $retval;
		} // end of evaluate() method
		
		/**
		* @desc rewinds the line pointers
		*/
		function rewind()
		{
			if( $this->line_offset != 0 ) {
				$this->line_offset--;
				$this->last_char = " ";
			} else {
				$this->line--;
				$this->line_offset = strlen($this->template_buff[$this->line]);
				$this->last_char = " ";
			}
			
			return;
		} // end of rewind() method
		
		/**
		* @desc Skips all spaces/tabs until next char
		*/
		function skip_blanks()
		{
			$this->get_char();
			// loop until we got a non whitespace char
			while(preg_match("/\s/i",$this->last_char))
			{
				$this->get_char();
			}
			// rewind the pointers
			$this->rewind();
			
			return;
		} // end of skip_blanks() method
		
		/**
		* @desc Jumps past the end tags
		*/
		function jump_past_end_tags()
		{
			$this->get_char();
			while(1)
			{
				while($this->last_char != $this->closing_tag[0])
				{
					$this->get_char();
				}
				$this->get_char();
				if( $this->last_char == $this->closing_tag[1] ) {
					break;
				}
			}

			return;
		} // end of jump_past_end_tags() method
		
		/**
		* @desc Returns the next token in the file
		*/
		function get_token()
		{
			// reset variables
			$this->last_token = NULL;
			
			// get characters
			while($this->get_char())
			{
				if( (!(ereg("[A-Za-z0-9_!=><]",$this->last_char))) || (($this->last_char == $this->closing_tag[0]) && ($this->template_buff[$this->line][$this->line_offset] == $this->closing_tag[1])) ) {
					if( $this->last_char == $this->closing_tag[0] ) {
						$this->rewind();
					}
					
					return;
				} else {
					$this->last_token .= $this->last_char;
				}
			}
		} // end of get_token() method
		
		/**
		* @desc Gets the next char in the buffer
		*/
		function get_char()
		{
			// check to see if we are at the end of the file
			if( $this->line == sizeof($this->template_buff) ) {
				return EOF;
			}
			
			// get char and set return value
			$retval = $this->last_char = $this->template_buff[$this->line][$this->line_offset];
			
			// replace a \n and check if at the end of the line
			if( $this->line_offset == strlen($this->template_buff[$this->line]) ) {
				if( $this->parseflag[$this->level] == PARSER_ON) {
					$this->output_buff .= "\n";
				}
				
				$this->line++;
				$this->line_offset = 0;
			} else {
				$this->line_offset++;
			}

			return $retval;
		} // end of get_char() method
		
		/**
		* @desc Handles the parseing of text/html
		*/
		function handle_text()
		{
			// offset should not be at the first char of text
			while($this->get_char() != EOF)
			{
				if( ($this->last_char == $this->opening_tag[0]) && ($this->template_buff[$this->line][$this->line_offset] == $this->opening_tag[1]) ) {
					// get the final tag opening
					$this->get_char();
					
					// pass to handler
					$this->handle_instruction();
					
					// when handle instruction returns, so do we
					return;
				} else {
					if( $this->parseflag[$this->level] == PARSER_ON ) {
						$this->output_buff .= $this->last_char;
					}
				}
			} // get the next char

			return; // Occurs when get char returns flase
		} // end of handle_text() method
		
		/**
		* @desc Handles the processing of instructions
		*/
		function handle_instruction()
		{
			// offset is just after tag
			// skip and whitespace
			$this->skip_blanks();
			$this->get_token();
			$token = $this->last_token;
			
			// find which token it is
			switch ( strtoupper($token) ) :

				case "IF":
				
					++$this->level;
					if( ($this->parseflag[(($this->level)-1)] != PARSER_ON) ) { // The Parser is off
						$this->parseflag[$this->level] = PARSER_OFF; // Turn parser off
						$this->flagset[$this->level] = PARSER_ON; // Ignore and other logic statements at this level of nesting
						$this->jump_past_end_tags(); // Jump past end tags
						break; // Jump out of switch
					}
					
				// end of IF

				case "ELSEIF":
				
					$this->skip_blanks(); // Skip blanks before next token
					$this->get_token(); // Get token
					$var = $this->last_token; // Assign token
					$this->skip_blanks(); // Skip blanks before next token
					$this->get_token(); // Get token
					$operator = $this->last_token; // Assign Token
					$this->skip_blanks(); // Skip blanks before next token
					$this->get_token(); // Get token
					$operand = $this->last_token; // Assign Token
					$this->jump_past_end_tags(); // Jump past end tags

					if( strtoupper($token) == "ELSEIF" ) {
						if( $this->flagset[$this->level] == PARSER_ON ) {                
							$this->parseflag[$this->level] = PARSER_OFF;
							break;
						}
					}
					
					if( $operator && $operand ) {
						if( $this->evaluate($var, $operand, $operator) ) { // Evalute expression
							$this->parseflag[$this->level] = PARSER_ON;
							$this->flagset[$this->level] = PARSER_ON;
							break; // Jump out of switch
						} else {
							$this->parseflag[$this->level] = PARSER_OFF;
							$this->flagset[$this->level] = PARSER_OFF;
							break;
						}
					} else {
						if( $this->defined[strtolower($var)] ) {
							$this->parseflag[$this->level] = PARSER_ON;
							$this->flagset[$this->level] = PARSER_ON;
							break; // Jump out of switch
						} else {
							$this->parseflag[$this->level] = PARSER_OFF;
							$this->flagset[$this->level] = PARSER_OFF;
							break;
						}
					}
					
				break;
					
				// end of ELSEIF
					
				case "ELSE":
				
					if( !($this->flagset[$this->level] == PARSER_ON) ) {
						$this->parseflag[$this->level] = PARSER_ON;
					} else {
						$this->parseflag[$this->level] = PARSER_OFF;
					}
					
					$this->jump_past_end_tags(); // Jump past end tags

				break;
				
				// end of ELSE
				
				case "ENDIF":
				
					$this->level--;
					$this->jump_past_end_tags(); // Jump past end tags
				
				break;
				
				// end of ENDIF

				default:

					if( $this->parseflag[$this->level] == PARSER_ON ) {
						$this->output_buff .= $this->defined[strtolower($token)];
					}
					
					$this->jump_past_end_tags(); // Jump past end tags
					
				break;
				
				// end of DEFAULT
			
			endswitch;
			  
			$this->handle_text();
			return;
		} // end of handle_instruction() method
	}
}

if(!class_exists('display'))
{
	class display extends template
	{
		/**
		 * @return void
		 * @param templates array
		 * @desc class constructor. accepts what templates to initialize
		 */
		function display($templates)
		{
			$this->template("../templates/");
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
				//"layout_mobile" => "layout_mobile.php",
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
			if(file_exists("../templates/auth_".APPLICATION_FILENAME.".php"))
				$this->templates["auth"]=$auth_template="auth_".APPLICATION_FILENAME.".php";
			if(file_exists("../templates/layout_".APPLICATION_FILENAME.".php"))
				$this->templates["layout_".APPLICATION_FILENAME]=$auth_template="layout_".APPLICATION_FILENAME.".php";
			if(file_exists("../templates/layout_".APPLICATION_FILENAME."_branded.php"))
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
