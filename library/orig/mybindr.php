<?
class mybindr {

	var $db;
	var $xdb;
	var $otherdb;
	var $host='166.70.32.197';
	var $user='hurdman';
	var $pass='6924637';
	var $database="hurdmantest";
	var $params; // query parameters
	var $alias;
	var $docsfolder="../templates/";
	var $begincode = "\[\[";
	var $endcode = "\]\]";
	var $entercode = "'&nbsp;</td></tr><tr|rowstyle|><td|cellstyle|>'";
	var $tabcode = "'&nbsp;</td><td|cellstyle|>'";
	var $linebreak="<br>";
	var $replaceif=false;
	var $breakonerror=true;
	var $buffer=false;
	var $lastmergecode="";
	var $fonttable="";
	var $runset=true;
	var $documents;
	var $DeleteSQL="";
	var $fontlist=array();
	var $colortbl="";
	var $shplid=array();
	var $bufferName="";
	var $didmerge=false;
	var $errormerge=false;
	var $RTFFileSize=0;
	var $overrideshowerror=false;
	var $jsonobject=array();
	var $doubledoublequotes=false;
	var $s3;
	var $pearMessage;
	function mybindr() {
		if(DATABASE_HOST>"") {
			$this->host=DATABASE_HOST;
			$this->user=DATABASE_USER;
			$this->pass=DATABASE_PASS;
		}
		if(DATABASE_PORT!="DATABASE_PORT" || $fp = fsockopen($this->host, 3306, $errno, $errstr, 10)) {
			$this->db = mysql_connect($this->host, $this->user, $this->pass); 
			if(class_exists("mysqli")) $this->mysqli=new mysqli($this->host, $this->user, $this->pass, $database);
		} else {
			if(defined("ERROR_EMAIL")) {
				$adit = "MIME-version: 1.0
From: Nathan Tanner <nathan.tanner@hurdmanivr.com>
CC: nathan.tanner@hurdmanivr.com
Reply-to: Nathan Tanner <nathan.tanner@hurdmanivr.com>
X-Mailer: PHP/".phpversion()."\r\n";
				mail(ERROR_EMAIL, "$this->host", 
					"You server is current unavailable and someone attempted to access the following URL:\r\n".
					"http://".$_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI], $adit);
			}
			die("The server is unavailable at the moment. Please try again later. If you need immediate assistance, click on the 'Contact Us' button to view contact telephone numbers.");
		}
		if(defined('DATABASE_HOST_MYSQL2')) $this->mysql2 = mysql_connect(DATABASE_HOST_MYSQL2, $this->user, $this->pass); else $this->mysql2=$this->db;
		$this->fixGET();
		$this->params = array_merge($_SERVER, $_COOKIE, $_POST, $_GET, get_defined_constants(), array("COUNTRY"=>(COUNTRY=="COUNTRY"?'USA':COUNTRY)));
		$results = $this->mybindr_query("select connection_id()", "common");
		$row = mysql_fetch_row($results);
		$this->addparm("THREADID", $row[0]);
		if(isset($_POST["THREADID"])) $this->addparm("THREADID",$_POST["THREADID"]);
	}
	
	function fixGET() {
		$argv=$_SERVER[argv];
		$getarr = explode("&", $argv[0]);
		for($i=0;$i<sizeof($getarr);$i++) {
			list($name, $val) = explode("=",$getarr[$i]);
			$_GET[urldecode($name)]=urldecode($val);
		}
	}
	function fixPipePost() {
		foreach($_POST as $key => $value) $this->params[$key]=str_replace("|"," ",$value);
	}

	function fixPost() {
		foreach($_POST as $key => $value) {
			if(is_array($value)) $value=implode(",", $value);
			$this->params[$key]=str_replace("|"," ",$value);
			$this->params[str_replace("_"," ",$key)]=$value;
			$this->params[str_replace("!","",str_replace("_"," ",$key))]=$value;
			if($value=="is_date") $this->params[str_replace("_", " ", $key)]=date("Y-m-d",strtotime($this->params["view_".$key]));
		}
		foreach($_GET as $key => $value) {
			$this->params[str_replace("_"," ",$key)]=$value;
			$this->params["!".str_replace("_"," ",$key)]=$value;
			$this->params["!".$key]=$value;
		}
	}
	function reconnect() {
		if($this->db) mysql_close($this->db);
		$this->db = mysql_connect($this->host, $this->user, $this->pass);
	}
	function resolve($mytext) {
		$this->StartBuffering();
		$this->ResolveSetMerge($mytext);
		$mytext=$this->ResolvePipes($mytext, false);
		if(!$this->SkipIfMerge) $mytext=$this->ResolveIfMerge($mytext);
/*		if($this->fonttable!='' && eregi("\{.fonttbl[^\x02]*\x02", str_replace("}}",chr(2),$mytext), $fonttbl)) {
			$fonttbl=str_replace(chr(2),"}}",$fonttbl[0]);
			$mytext=str_replace($fonttbl, "{\\fonttbl".$this->fonttable."}", $mytext);
		}*/
		$this->runset=false;
		if($this->buffer) {
			$this->ResolveMerge($mytext);
			$mytext="";
		} else
			$mytext=$this->ResolveMerge($mytext);
		$this->runset=true;
		$mytext=str_replace("___FIXED_COLOR_TABLE___", $this->MakeColortbl(), $mytext);
		$mytext=str_replace("___FIXED_FONT_TABLE___", $this->MakeFonttbl(), $mytext);
/*		if($this->fonttable!='' && eregi("\{.fonttbl[^\x02]*\x02", str_replace("}}",chr(2),$mytext), $fonttbl)) {
			$fonttbl=str_replace(chr(2),"}}",$fonttbl[0]);
			$mytext=str_replace($fonttbl, "{\\fonttbl".$this->fonttable."}", $mytext);
		}*/
//		$this->StopBuffering();
		if($this->SkipIfMerge) return $mytext;
		else return str_replace("<<","",str_replace(">>","",$mytext));
	}
	function StartBuffering() {
		if(!$this->buffer) return;
		if($this->bufferName>"") {
			$fp=fopen($this->bufferName, "a");
			fwrite($fp, ob_get_contents());
			fclose($fp);
			ob_end_clean();
		} else $this->bufferName=$_POST["temp_file_name"];
		ob_start();
	}
	function StopBuffering() {
		if(!$this->buffer) return;
		if($this->bufferName>"") {
			$fp=fopen($this->bufferName, "a");
			fwrite($fp, ob_get_contents());
			fclose($fp);
		}
		ob_end_clean();
	}	
	function MakeFonttbl() {
		$ret="{\\fonttbl";
		foreach($this->fontlist as $key=>$value) $ret.=$value."\r\n";
		return $ret."}";
	}
	function MakeColortbl() {
		return $this->colortbl;
	}
	
	function ResolveSetMerge($mytext) {
		list($mergecode, $mypos) = $this->GetNextSetMergeCode($mytext);
		do {
			if(!$mergecode) break;
			$this->didmerge=true;
			if(ereg("set:params:", $mergecode)) {
				$mytext = substr_replace($mytext, "", strpos($mytext,$mergecode), strlen($mergecode));
				continue;
			}
			$mytext=substr($mytext,$mypos);
			$mytext = substr_replace($mytext, $this->MergeCode(str_replace("[", "", str_replace("]", "", $mergecode))), strpos($mytext,$mergecode), strlen($mergecode));
		}
		while (list($mergecode, $mypos) = $this->GetNextSetMergeCode($mytext));
	}

	function GetNextSetMergeCode($mytext)
	{
		ereg($this->begincode."set:[^\x5D\x5B]*".$this->endcode,$mytext,$returned);
		return array($returned[0], strpos($mytext,$returned[0]));
	}
	function ResolveIfMerge($mytext) {
		$ifmerge=$this->GetNextIfMerge($mytext);
		do {
			if(!$ifmerge) break;
			$this->didmerge=true;
//			echo $ifmerge;
			if(eregi("^<<repeat(.*)>>$", $ifmerge, $regs)) {
				$this->addparm("repeat", $regs[1]);
				$mytext = str_replace($ifmerge, "", $mytext);
//				print_r($regs);
//				echo "***$mytext*";
			} else {
				$oldbuffer=$this->buffer;
				list($mergecode, )=$this->GetNextMergeCode($ifmerge);
				if(ereg("^\[+set:",$mergecode)) $is_set=true; else $is_set=false;
				$mymerge=$this->MergeCode(str_replace("[","",str_replace("]","",$mergecode)));
				if($this->replaceif) {
					$mymerge=str_replace("<", "&l1t;", $mymerge);
					$mymerge=str_replace(">", "&g1t;", $mymerge);
				}
				if($is_set) {
					$newmerge="<<".str_replace($mergecode,$mymerge,$ifmerge).">>";
					list($nextmergecode, $mergepos)=$this->GetNextMergeCode($newmerge);
					$newmerge=substr_replace($newmerge, $mergecode, $mergepos+strlen($nextmergecode), 0);
				} elseif($mymerge!="")
					$newmerge=str_replace($mergecode,$mymerge,$ifmerge);
				else
					$newmerge="<<>>";
				$mytext = substr_replace($mytext, substr($newmerge,2,strlen($newmerge)-4), strpos($mytext,$ifmerge), strlen($ifmerge));
//				$mytext=str_replace($ifmerge,substr($newmerge,2,strlen($newmerge)-4),$mytext);
			}
		} while ($ifmerge=$this->GetNextIfMerge($mytext));
		return $mytext;
	}

	function GetNextIfMerge($mytext)
	{
		if(!ereg ("<<repeat.*>>", $mytext, $returned))
			ereg ("<<[^<>]*".$this->begincode."[^]]*".$this->endcode."[^<>]*>>", $mytext, $returned);
		return $returned[0];
	}
	
	function ResolveFonts($content, $title) {
		if(ereg("\{.colortbl[^}]+\}", $content, $regs)) $this->colortbl=$regs[0];
		$content=ereg_replace("\{.colortbl[^}]+\}", "___FIXED_COLOR_TABLE___", $content);
		if(!eregi("\{.fonttbl([^\x02]*)\x02", str_replace("}}",chr(2),$content), $regs)) return $content;
		
		$content=str_replace(chr(2), "}}", eregi_replace("\{.fonttbl([^\x02]*)\x02", "___FIXED_FONT_TABLE___", str_replace("}}",chr(2),$content)));
		$fonttable=str_replace(chr(2), "}}", $regs[1])."}";
//		print_r($regs);
		while(eregi("\{[^{}]*[\\]f([0-9]+)[^0-9]+[^;]+[;]\}", $fonttable, $regs)) {
//			print_r($regs);
			if(ereg("\\f".$regs[1], $content)) {
				$fcode=str_pad($regs[1], 6, "0", STR_PAD_LEFT);
				if(empty($this->fontlist["F".$fcode]) || $this->fontlist["F".$fcode]==$regs[0]) $this->fontlist["F".$fcode]=$regs[0];
				else $this->fontlist["TORESOLVE".$fcode]=$regs[0];
			}
			$fonttable=str_replace($regs[0],"",$fonttable);
		}
		$fonts=array_keys($this->fontlist);
		array_multisort($fonts, SORT_ASC, SORT_STRING);
//print_r($fonts);
		foreach($fonts as $key) {
			$value=$this->fontlist[$key];
			if(ereg("^TORESOLVE(.+)",$key, $regs)) {
				$top++;
				$this->fontlist["F".$top]=str_replace("\\f".($regs[1]+0), "\\f".$top, $value);
				$content=str_replace("\\f".($regs[1]+0), "\\f".$top, $content);
				unset($this->fontlist[$key]);
			} else $top=str_replace("F","",$key)+0;
		}
		return $content;
	}
	
	function GetFontTables($title) {
		$content = $this->GetDoc($title);
		$md=$this->GetNextMergeDoc($content);
		while (sizeof($md)>1) {
			$mytext = $this->GetDoc($md[0]);
			if(eregi("\{.fonttbl([^\x02]*)\x02", str_replace("}}",chr(2),$mytext), $fonttbl)) {
				$this->fonttable.=$fonttbl[1]."}";
//				print_r($fonttbl);
			}
			$content=substr($content,$md[1]);
			$md=$this->GetNextMergeDoc($content);
		};
		return;
	}

	function FixShapes($content) {
			$mypos=0;
			while(ereg("shplid([0-9]+)", substr($content,$mypos), $shplid)) {        
					if(isset($this->shplid[$shplid[1]])) {
							$value=0;
							foreach($this->shplid as $shp) if($shp>$value) $value=$shp;
							$value=$value+1;
					} else $value=$shplid[1];
					$this->shplid[$value]=$value;    
					$content=str_replace($shplid[0], "shplid$value", $content);                                
					$mypos=strpos($content,"shplid$value")+strlen("shplid$value");                
			}
			return $content;
	}
		
	function GetNextMergeDoc($mytext) {
		if(!eregi($this->begincode."merge(:| )([^\x5D\x5B]*)".$this->endcode,  $mytext, $returned)) return false;
		$num=(strpos($mytext, $returned[0])+strlen($returned[0]));
		return array($returned[2], $num);
	}
	
	function GetAsks($title) {
		$content = $this->GetDoc($title);
		$ask=$this->GetNextAsk($content);
		while (sizeof($ask)>1) {
			$asks[] = $ask;
			$content=substr($content,$ask[2]);
			$ask=$this->GetNextAsk($content);
		};
		return $asks;
	}
	
	function GetNextAsk($mytext) {
		if(!eregi($this->begincode."ask:([^:]*):([^\x5D\x5B]*)".$this->endcode,  $mytext, $returned)) return false;
		$num=(strpos($mytext, $returned[0])+strlen($returned[0]));
		return array ($returned[1], $returned[2], $num);		
	}
	
	function GetDoc($title, $version=false) {
		if(ereg("[.]tpl$",$title) && isset($_GET["ebindr2"])) {
			$_GET["NOCACHE"]="y";
			$title="e2_$title";
		}
		if(!$version && isset($this->documents[$title])) return $this->documents[$title];
		if($version) {
			$sql1 = "select content from documentaudit where title='$title' and version=$version";
			$result = $this->mybindr_query($sql1);
			if(list($content) = mysql_fetch_row($result)) return $content;
		} elseif(file_exists(DIR_TEMP.$title) && !eregi("(pdf|rtf|csv|xls|htm|html|bbb|txt|php)$", $title) && !isset($_GET["NOCACHE"])) {
			$fp=fopen(DIR_TEMP.$title,"r");
			$content=fread($fp,filesize(DIR_TEMP.$title));
			fclose($fp);
/*		} elseif(file_exists($this->docsfolder.$title)) {
			$fp=fopen($this->docsfolder.$title,"r");
			$content=fread($fp,filesize($this->docsfolder.$title));
			fclose($fp); */
		} else {
			if(QUERY_DB>'') $common=QUERY_DB;
			
//			if(isset($this->params["QUERY_DB"]))
				$sql2 = "select content from $common.document where title='$title'";
	//		else
				$sql1 = "select content from document where title='$title'";
			$result = $this->mybindr_query($sql1);
			if(mysql_num_rows($result)<1)
				$result = $this->mybindr_query($sql2);
			if($row = mysql_fetch_row($result)) {
				$content = $row[0];
				$fp=fopen(DIR_TEMP.$title,"w");
				fwrite($fp, $content);
				fclose($fp);
			} else
				return "Document: $title does not exist.";
		}
		if(ereg("rtf$", $title)) $this->documents[$title]=ereg_replace('[{][\]sp[{][\]sn hspNext[}][{][\]sv [0-9]+[}][}]', "", str_replace("}\r\n{", "}{", str_replace("}\r\n}", "}}", $content))); else $this->documents[$title]=$content;
		return $this->documents[$title];
	}

	function MergeNoBuffer($title) {
		$this->didmerge=false;
		$this->errormerge=false;
		$this->fontlist=array();
		$this->fonttable='';
		$this->bufferName="";
		$this->buffer=false;
		$this->RTFFileSize=0;
		if(eregi("htm$", $title) || eregi("txt$", $title)) {
			$this->buffer=false;
			return $this->merge($title);
		}
		$_POST["temp_file_name"]=tempnam("/var/tmp", "MERGE");
		$mycontent=$this->merge($title);
		$this->StopBuffering();
		rename($this->bufferName, $this->bufferName."_done");          
		$fp=fopen($this->bufferName."_done","r");
		$start=true;
		while($data=fread($fp,4000)) {               
			if($start) {
				$mycontent.=str_replace("___FIXED_COLOR_TABLE___", $this->MakeColortbl(), str_replace("___FIXED_FONT_TABLE___", $this->MakeFonttbl(), $data));
				$start=false;
			} else
				$mycontent.=$data;
		}                
		fclose($fp);
		unlink($this->bufferName."_done");
		return $mycontent;
	}
	
	function merge($title, $bodyonly=false, $bufferoverride = false) {
		$this->params["document_filename"]=$title;
		if(strtolower($title)=="planetpress.txt" || strtolower($title)=="postcardproof.pdf") $this->doubledoublequotes=true; else $this->doubledoublequotes=false;
		if(eregi("proof", $title) && eregi("[.]pdf$", $title)) {
			list($query, ) = $this->getquery($title);
			$rp = $this->mybindr_query($this->ResolvePipes($query));
			if(mysql_num_rows($rp)>0 && !isset($_GET["showexport"])) {
				list($pdfresult)=mysql_fetch_row($rp);
				return $pdfresult;
			}
			list($query, ) = $this->getquery("$title - export");
			$pdfresult="\"Header Row\"\r\n";
			$rp = $this->mybindr_query($this->ResolvePipes($query));
			while($row=mysql_fetch_row($rp)) {
				$pdfresult .= "\"".implode('","', $row)."\"\r\n";
			}
			$pdfresult=$this->resolve("[hmh define letter parameters]".$pdfresult);
			if(isset($_GET["showexport"])) die($pdfresult);
			$ch = curl_init();
			$host = 'office.hurdmanivr.com:88'; // domain only, no path info
			$path = "/getproof.php"; // path to cgi, asp, php program
			$agent = "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.4) Gecko/20030624 Netscape/7.1 (ax)";
			$cookie_file_path = ""; 
			$parameters="proofdata=".urlencode($pdfresult);
			curl_setopt($ch, CURLOPT_URL,"http://hurdman.app.bbb.org/proof/");
			curl_setopt($ch, CURLOPT_USERAGENT, $agent);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file_path);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file_path);
			curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,
						$parameters);
			curl_setopt($ch, CURLOPT_REFERER, "http://hurdman.app.bbb.org/proof/");
			$pdfresult = curl_exec ($ch);
			curl_close ($ch);

			@unlink("out.pdf");
/*			$fp = fopen("proof.csv", "w");
			fwrite($fp, $pdfresult);
			fclose($fp);
			$ftp = ftp_connect("166.70.32.220", 2121);
			ftp_pasv($ftp, false);
			if(ftp_login($ftp, "proof", "foorp")) {
				@ftp_delete($ftp, "out.pdf");
				ftp_put($ftp, "proofnew.csv", "proof.csv", FTP_ASCII);
				ftp_rename($ftp, "proofnew.csv", "proof.csv");
				$getc=0;
				while(!@ftp_get($ftp, "out.pdf", "out.pdf", FTP_BINARY) && $getc<30) {
					sleep(1);
					$getc++;
				}
				if($getc<30) $pdfresult = file_get_contents("out.pdf"); else return "System error";
			} else $pdfresult="";*/
			list($query, ) = $this->getquery("$title - update");
			$this->mybindr_query(str_replace("[PDF FILE]", addslashes($pdfresult), $this->ResolvePipes($query,false)));
			return $pdfresult;
		}
//		$this->GetFontTables($title);
		$content=$this->GetDoc($title);
		if(eregi("[.]php$", $title)) {
			$this->begincode = "\[\[";
			$this->endcode = "\]\]";
			$this->linebreak="";
			$this->entercode="";
			$this->tabcode="";
			$this->replaceif=false;
			$content=$this->resolve($content);
			$content=ereg_replace("^<[?]php","",ereg_replace("^<[?]","",ereg_replace("[?]>$","",trim($content,"\r\n "))));
			eval($content);
			return "";
		} elseif(ereg("pdf$", $title)) {
			if(strlen($content)>1000) return $content;
			$this->begincode = "\[\[";
			$this->endcode = "\]\]";
			$this->SkipIfMerge=true;
			$this->linebreak="";
			$this->entercode="";
			$this->tabcode="";
			$this->replaceif=false;
			return str_replace("#00","", preg_replace('/[0-9]+ 0 obj\n<<\n\/Type \/Annot\n\/Subtype \/Link(.*?)endobj/s', "", $this->ResolveMerge($content)));
		} elseif(ereg("htm$", $title) || ereg("tpl$", $title)) {
			$content=str_replace("<","&l1t;",$content);
			$content=str_replace(">","&g1t;",$content);
			$content=str_replace("&lt;","<",$content);
			$content=str_replace("&gt;",">",$content);
			if(!isset($_GET[nojava])) {
				$this->begincode = "\[\[";
				$this->endcode = "\]\]";
			}
			$this->replaceif=true;
		} elseif(ereg("ics$", $title) || ereg("pdf$", $title) || ereg("txt$", $title) || ereg("csv$", $title) || ereg("xls$", $title)) {
			$this->buffer=ereg("txt$", $title) && isset($_GET["nobuffer"]);
			$this->linebreak="\r\n";
			$this->entercode="'\\r\\n'";
			$this->tabcode="char(9)";
			if(!isset($_GET[nojava])) {
				$this->begincode = "\[";
				$this->endcode = "\]";
			}
			$this->replaceif=true;
		} else {
			$content=$this->ResolveFonts($content, $title);
			$this->linebreak="\\par ";
			$this->entercode="'\\\\par '";
			$this->tabcode="char(9)";
			$this->replaceif=false;
			$this->buffer=($_SERVER['SCRIPT_NAME']!='/merge.php');
//			if($bodyonly==-99) $this->buffer=false;
//			if($bodyonly) $this->buffer=false;
			$this->breakonerror=false;
		}
		
		//if($bodyonly==-99) $bodyonly=false;
		if($bodyonly && eregi("<<header ".$this->begincode."[^\x5D\x5B]*".$this->endcode."(.*)>>", $content, $regs)) {
	/*		if(eregi("\{.fonttbl([^\x02]*)\x02", str_replace("}}",chr(2),$content), $fonttbl)) {
				$this->fonttable.=str_replace(chr(2),"}}",$fonttbl[1])."}";
			}*/
			$content=$regs[1];
		} elseif($bodyonly && eregi("<<(.*)>>", $content, $regs)) {
/*			if(eregi("\{.fonttbl([^\x02]*)\x02", str_replace("}}",chr(2),$content), $fonttbl)) {
				$this->fonttable.=str_replace(chr(2),"}}",$fonttbl[1])."}";
			}*/
			$content=$regs[1];
		} elseif($bodyonly && eregi("([\{][\\]info.*)[\}]", $content, $regs)) {
/*			if(eregi("\{.fonttbl([^\x02]*)\x02", str_replace("}}",chr(2),$content), $fonttbl)) {
				$this->fonttable.=str_replace(chr(2),"}}",$fonttbl[1])."}";
			}*/
			$content=$this->StripHeaderFooter($regs[1]);
			$this->documents[$title]=$content."}";
		} elseif(eregi("<<header ".$this->begincode."([^\x5D\x5B]*)".$this->endcode."(.*)>>", $content, $regs)) {
/*			if(eregi("\{.fonttbl([^\x02]*)\x02", str_replace("}}",chr(2),$content), $fonttbl)) {
				$this->fonttable.=str_replace(chr(2),"}}",$fonttbl[1])."}";
			} */
			$this->StartBuffering();
			$this->addparm("body", $content=$regs[2]);
//			$oldbuffer=$this->buffer;
			$content=$this->merge($this->ResolvePipes($regs[1])); //, -99);
	//		$this->buffer=$oldbuffer;
			if($this->buffer && !$bufferoverride) {
				$this->resolve($content);
				$content="";
			} else {
				$content=$this->resolve($content);
			}
		} else {
			if($this->buffer && !$bufferoverride) {
					//if(substr($content,0,6)=='{\rtf1') { echo substr($content,0,6).str_repeat("{}", 512); flush(); $content=substr($content,6); }  
					//elseif(substr($content,0,5)=='{\rtf') { echo substr($content,0,5).str_repeat("{}", 512); flush(); $content=substr($content,5); }  
					$this->resolve($content);
					$content='';
			} else {
				if(substr($content,0,6)=='{\rtf1') { echo substr($content,0,6).str_repeat("{}", 512); flush(); $content=substr($content,6); }  
				elseif(substr($content,0,5)=='{\rtf') { echo substr($content,0,5).str_repeat("{}", 512); flush(); $content=substr($content,5); }  
				$content=$this->resolve($content);
			}
		}
		
		if(ereg("htm$", $title) || ereg("tpl$", $title)) {
			$content=str_replace("&l1t;","<",$content);
			$content=str_replace("&g1t;",">",$content);
			if(isset($_GET["json"])) {
//				$content=print_r($this->jsonobject, true); //json_encode($this->jsonobject);
				if(empty($this->jsonobject)) $content="//No data";
				else {
$obj = new stdClass;
$obj->resultset = $this->jsonobject;
$content=json_encode($obj);
//					$content=json_encode($this->jsonobject);
				}
			}
		}
		
		return $content;
	}
	function StripHeaderFooter($content) {
		while(ereg("\{[^{}]*\}", $content, $regs)) {
			if(strpos($regs[0], "{\header")!==false || strpos($regs[0], "{\footer")!==false) $content=str_replace($regs[0], "", $content);
			$content=str_replace($regs[0], str_replace("{", chr(2), str_replace("}", chr(3), $regs[0])), $content);
		}
		return str_replace(chr(2), "{", str_replace(chr(3), "}", $content));
	}
	function runqueries($query) {
		global $reportr;
		if(is_object($reportr)) {
/*			list($lastrun,$lastpid,$lastrecent)=explode(",",$reportr->background->get_var("select concat(howlongago(day,now()),',',id,',',day>now()-interval 10 minute) from processlist where mergecode='".$reportr->current_query.".editr' and userid='".$_COOKIE['reportr_username']."' and mergecode not like 'auth%' and mergecode not like 'exportr%' and mergecode not like 'Process Complaints%' and mergecode not like 'menu.sales.Hot%' order by day desc limit 1"));
			if($lastrun>"") {
				$reportr->background->query("kill $lastpid");
				$reportr->background->query("delete from processlist where id=$lastpid", OBJECT, false);
			}*/
			if(!ereg("^(auth|exportr|JSON)",$reportr->current_query)) $this->mybindr_query("replace into processlist(mergecode, day, id, userid) values ('".$reportr->current_query.".editr', now(), connection_id(), '".$_COOKIE['reportr_username']."')", "", false);
		}

	//print_r($query);
//	exit();
		for($i=0;$i<sizeof($query);$i++) {
			if($query[$i]=='') {
				return false;
				print_r($query);
				exit();
			}
			if(eregi("^(sbqotto|otto):(.*)", $query[$i], $regs)) {
				if(eregi("^select", $regs[2])) {
					$result = $this->mybindr_query($this->ResolvePipes($regs[2]));
					$row = mysql_fetch_row($result);
					$regs[2]=$row[0];
				}
				if($regs[1]=="sbqotto")
					$this->addparm("bid", $regs[2]);
				else
					$this->addparm("cid", $regs[2]);
				$icount=0;
				$this->params[ottosid]='';
				list($query2, ) = $this->getquery("ebindr ".$regs[1]." queries params");
				$query2=$this->ResolveMerge($query2);
				if($this->params["ottomulti"]=='yes' && $this->params[ottosid]=='') {
					if(list($query2, ) = $this->getquery("ebindr ".$regs[1]." queries multi")) {
						$query2 = $this->querygroup($query2);
						$this->runqueries($query2[update][0]);
					}
				}
				while($this->params[ottosid]!='' && ($icount++)<100) {
					if($this->params["ottomulti"]=='yes') {
						if(list($query2, ) = $this->getquery("ebindr ".$regs[1]." queries multi")) {
							$query2 = $this->querygroup($query2);
							$this->runqueries($query2[update][0]);
						}
					}
//**Changed Sep 2	if(list($query2, ) = $this->getquery("lite button ".$regs[1])) {
					if(list($query2, ) = $this->getquery("ebindr ".$regs[1]." queries")) {
						$query2 = $this->querygroup($query2);
						$this->runqueries($query2[update][0]);
					}
//					$result = $this->mybindr_query($this->ResolvePipes("select action.timeout, action2.reminderdays, if(action2.sendto>'','','o') as sentby, '|staff|' as staff, action2.code, step.sid, action.sendto from ((complaint inner join step using(cid)) inner join action on action=action.code) left join action action2 on action.timeout=action2.code where complaint.cid=|cid| and step.reminderdays=1 and (action.timeout<0 or action.timeout=action.possible) and closecode=0 and sentby='' order by sid asc limit 1"));
//					list($query2, ) = $this->getquery("lite button ".$regs[1]." test");
	//				$result = $this->mybindr_query($this->ResolvePipes($query2));
					$this->params[ottosid]='';
					list($query2, ) = $this->getquery("ebindr ".$regs[1]." queries params");
					$this->resolve($query2);
				}
//				} while (mysql_num_rows($result)>0 && ($icount++)<100);
				if($icount>100) {
					$row = mysql_fetch_row($result);
					print_r($row);
					die("Too many otto entries");
				}
			} elseif(eregi("^copyscans:(.+)", $query[$i], $regs)) {
				$result = $this->mybindr_query($this->ResolvePipes($regs[1]));
				while(list($frombid, $tobid)=mysql_fetch_row($result)) {
					$d1 = $this->GetScanDir("bid", $frombid);
					$d2 = $this->GetScanDir("bid", $tobid);
					shell_exec("cp -fur $d1/* $d2/");
					/*
					if($d = @opendir($d1)) {
						while(false !== ($filename = readdir($d)))
							if ($filename != "." && $filename != ".." && $filename!='trash') {
								copy($d1."/".$filename, $d2."/".$filename);
							}
					}
					*/
				}
			} elseif(eregi("^set:", $query[$i], $regs)) {
				$result = $this->CheckSetParams($query[$i]);
			} elseif(eregi("^runsql:(.*)", $query[$i], $regs)) {
//				$result = $this->mybindr_query($this->ResolveSubQueries($this->ResolvePipes($regs[1])));
				$result = $this->mybindr_query($this->ResolvePipes($regs[1]));
				if($row = mysql_fetch_row($result))
					if($row[0]>'') {
						$runsql = $this->querygroup($row[0]);
//						$result = $this->mybindr_query($this->ResolveSubQueries($this->ResolvePipes($row[0])));				
						$this->runqueries($runsql["update"][0]);
					}
			} elseif(eregi("^assigncomplaint:(.*)", $query[$i], $regs)) {
				if($regs[1]=="all") $result=$this->mybindr_query("CALL AssignComplaints()");
				else $result=$this->mybindr_query("CALL AssignComplaint(".$regs[1].")");
				/*
				$result = $this->mybindr_query("select ruleorder, distributeevenly from complaintassignment order by distributeevenly='y' desc, catchall='y', ruleorder");
				$de='n';
				while($de!='y' && list($ruleorder, $de)=mysql_fetch_row($result)) {
					$this->AssignComplaint($ruleorder, $de, $regs[1]);
				} */
			} elseif(eregi("^commonqueries:(.*)", $query[$i], $regs)) {
				$result = $this->mybindr_query($this->ResolvePipes($regs[1]));
				$fp=fopen("/home/commonqueries.txt", "a");
				while(list($commonqueries)=mysql_fetch_row($result)) fwrite($fp, $commonqueries);
				fclose($fp);
			} elseif(eregi("^use (.+)$", $query[$i],$regs)) {
				$this->database=trim($regs[1]);
//				$result = $this->mybindr_query($this->ResolveSubQueries($this->ResolvePipes($query[$i], false)));
//				if(sizeof($query)==1) return $result; */
			} elseif(eregi("^replicate (.+)$", $query[$i],$regs)) {
				list($host,$port)=explode(";",DATABASE_HOST);
				passthru("mysqldump --add-drop-table -c -h".$host." -P".($port==""?"3306":$port)." -u".DATABASE_USER." -p".DATABASE_PASS." ".$this->database." ".$regs[1]." > /var/tmp/mysqldump_".$regs[1].".sql");
				passthru("mysql -h".$host." -P".($port==""?"3306":$port)." -u".DATABASE_USER." -p".DATABASE_PASS." ".$this->database." < /var/tmp/mysqldump_".$regs[1].".sql");
			} elseif(ereg("^DOWNLOADS3 (.+)$", $query[$i], $regs)) {
				$result = $this->mybindr_query($this->ResolvePipes($regs[1]));
				while(list($src, $dest)=mysql_fetch_row($result)) {
					$this->downloads3($src, $dest);
					@chmod($dest, 0777);
				}
			} elseif(ereg("^COPYS3 (.+)$", $query[$i],$regs)) {
				$result = $this->mybindr_query($this->ResolvePipes($regs[1]));
				while(list($src, $dest)=mysql_fetch_row($result)) {
					$this->copys3($src, $dest);
				}
			} elseif(ereg("^DELETES3PUBLIC (.+)$", $query[$i],$regs)) {
				$result = $this->mybindr_query($this->ResolvePipes($regs[1]));
				while(list($dest)=mysql_fetch_row($result)) {
					$this->deletes3public($dest);
				}
			} elseif(ereg("^COPYS3PUBLIC (.+)$", $query[$i],$regs)) {
				$result = $this->mybindr_query($this->ResolvePipes($regs[1]));
				while(list($src, $dest)=mysql_fetch_row($result)) {
					$this->copys3public($src, $dest);
				}
			} elseif(ereg("^SENDS3PUBLIC (.+)$", $query[$i],$regs)) {
				$result = $this->mybindr_query($this->ResolvePipes($regs[1]));
				while(list($src, $dest)=mysql_fetch_row($result)) {
					$this->sends3public($dest, $src);
				}
			} elseif(eregi("^merge (.+)$", $query[$i],$regs)) {
				$this->merge($regs[1]);
			} elseif(eregi("^mergecontent (.+)$", $query[$i],$regs)) {
				list($report,$params)=explode("?", $regs[1], 2);
				$this->mybindr_query("set @MERGE_RESULTS:='".addslashes($this->GetParm("getwebpage http://".(defined("LOCAL_HOST")?LOCAL_HOST:"127.0.0.1")."/report/merge/".rawurlencode($report)."?".$params."&BYPASS=gure8wh3&BYPASS2=9vfjesu3hgi&USEDEFAULTS=true&NOASK=true"))."'","",ereg("HURDMAN",$this->params["keys"]),true);
				
/*				list($content)=mysql_fetch_row($this->mybindr_query($this->queriesrun[]=$regs[1],"",false,true));
				$buffer=$this->buffer;
				$this->buffer=false;
				$begincode=$this->begincode;$endcode=$this->endcode;
				$this->begincode = "\[";$this->endcode = "\]";
				$this->mybindr_query("set @MERGE_RESULTS:='".addslashes($this->ResolveMerge($content))."'");
				$this->buffer=$buffer;
				$this->begincode = $begincode;$this->endcode = $endcode;*/
			} elseif(eregi("^GEOCODE (.+)", $query[$i], $regs)) {
				$url="http://maps.google.com/maps/api/geocode/xml?sensor=false&address=".urlencode($regs[1]);
				$ch = curl_init();
				$agent = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 2.0.50727; OfficeLiveConnector.1.3; OfficeLivePatch.0.0)";
				curl_setopt($ch, CURLOPT_USERAGENT, $agent);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_URL, $url);
				$buffer=str_replace(array(chr(10),chr(13)),"",curl_exec($ch));
				curl_close($ch);
				if(ereg("<location>[^<>]*<lat>([^<>]*)</lat>[^<>]*<lng>([^<>]*)</lng>", $buffer, $regs)) {
					list(,$lat,$lng)=$regs;
					$this->mybindr_query("set @latitude:=$lat");
					$result=$this->mybindr_query("set @longitude:=$lng");
				}
			} elseif(eregi("^PROCESSCC (.+)", $query[$i], $regs)) {
				$result = $this->mybindr_query($this->ResolvePipes($regs[1]));
				list($payid)=mysql_fetch_row($result);
				include_once "/home/serv/public_html/ebindr/includes/paymentq.php";
				$q = new paymentq();
				if($payment = $q->getPayment($payid) ) {
					$r = $q->process( $payment['amount'], $payment['vaultid'], $payment['profileid'] );
					$r = $r->results;
					$q->bind( 'payid', $payment['payid'] );
					$q->bind( 'bid', $payment['bid'] );
					$q->bind( 'success', ( $r->success ? 1 : 0 ) );
					$q->bind( 'error', ( $r->error ? 1 : 0 ) );
					$q->bind( 'transactionid', $r->transaction_id );
					$q->bind( 'authcode', $r->authcode );
					$q->bind( 'errormsg', ( $r->error ? $r->errormsg : '' ) );
					$q->bind( 'response', json_encode($r) );
					$q->query( "[vault.payment.processed]" );
				}
			} elseif(ereg("^JSONDECODE (.+)$", $query[$i], $regs)) {
//			die($regs[1]);
				if(eregi("^http", $regs[1])) $json=json_decode(file_get_contents($regs[1]));
				elseif(eregi("^select", $regs[1])) {
					list($json)=mysql_fetch_row($this->mybindr_query($this->ResolvePipes($regs[1]),"",ereg("HURDMAN",$this->params["keys"]),true));
					$json=json_decode(file_get_contents($json));
				} else $json=json_decode($regs);
				list($creates, $inserts) = $this->ObjToMySQL($json);
				foreach($creates as $create) $result = $this->mybindr_query($this->ResolvePipes($create, false),"",ereg("HURDMAN",$this->params["keys"]),true);
				foreach($inserts as $insert) $result = $this->mybindr_query($this->ResolvePipes($insert, false),"",ereg("HURDMAN",$this->params["keys"]),true);
			} else
				$result = $this->mybindr_query($this->ResolvePipes($query[$i], false),"",(!ereg("json_results", $query[$i]) && ereg("HURDMAN",$this->params["keys"])),true);
//				$result = $this->mybindr_query($this->ResolveSubQueries($this->ResolvePipes($query[$i], false)));
				if($_POST[STEP]==1 && $this->DeleteSQL>"" && eregi("^delete (.*)", $query[$i]) && mysql_affected_rows($this->db)>0) $this->mybindr_query($this->ResolvePipes($this->DeleteSQL),"",false);
				if(sizeof($query)==1) {
					if(!ereg("^(auth|exportr|JSON)",$reportr->current_query)) $this->mybindr_query("delete from processlist where id=connection_id()");
					return $result;
				}
		}
		if(!ereg("^(auth|exportr|JSON)",$reportr->current_query)) $this->mybindr_query("delete from processlist where id=connection_id()");
		return true;
	}
	
	function AssignComplaint($ruleorder, $de, $cid) {
		$this->addparm("ruleorder", $ruleorder);
		if($de=='y') list($query, ) = $this->getquery("complaint assignment even queries"); else list($query, ) = $this->getquery("complaint assignment queries");
		$query = $this->querygroup($query);
		$this->runqueries($query["update"][0]);
		$result = $this->mybindr_query($this->ResolvePipes($query["select"][0].($cid=="all" || $de=='y'?"":" and complaint.cid=$cid")));
		while($row=mysql_fetch_row($result)) {
			$this->mybindr_query("insert into changeaudit (fid, type, cid, day, staff, history) select 9, 'Edit', $row[0], now(), 'OTTO', concat('AssignedTo=>',assignedto,'=>$row[1] (RULE $ruleorder)') from complaint where cid=$row[0] and assignedto!='$row[1]'");
			$this->mybindr_query("update complaint, complaintassignment set assignedto='$row[1]', lastassigned=now() where cid=$row[0] and ruleorder=$row[2] #ca rule $ruleorder");
		}
		$this->runqueries($query["update"][1]);
	}
	
	function GetScanDir($bidcid, $filecid) {
		global $browse_auto_bid_dir;
		if(strlen($filecid)<3) $filecid.="XX";
		$basedir1=DOCS_BASE_DIR.'/'.strtolower($bidcid).'/'.substr($filecid,strlen($filecid)-2);
		$basedir2=DOCS_BASE_DIR.'/'.strtolower($bidcid).'/'.substr($filecid,strlen($filecid)-2).'/'.substr($filecid,0,strlen($filecid)-2);
		if(!file_exists($basedir1)) {
			@mkdir($basedir1);
		}
		if(!file_exists($basedir2)) {
			@mkdir($basedir2);
		}
		if(!file_exists($basedir."/trash")) {
			@mkdir($basedir."/trash");
		}
		$basedir=$basedir2;
        if(substr(sprintf('%o', fileperms($basedir)), -4)=="0755") { sleep(1); chmod($basedir, 0777); }
		if(is_array($browse_auto_bid_dir) && ereg("BID", $bidcid))
			foreach($browse_auto_bid_dir as $onedir) if(!file_exists($basedir."/".$onedir) && (!eregi("member",$onedir))) @mkdir($basedir."/".$onedir);
		if(!file_exists($basedir."/trash")) {
			@mkdir($basedir."/trash");
		}
		return $basedir2;

	}
	
	function DropIf($myquery) {
		if(!eregi("^(drop table .+) if( .*)$", ereg_replace("^\r\n","",$myquery), $regs)) return $myquery;
		if(eregi("^ exists", $regs[2])) return $myquery;
		$testsql=(eregi("^ select",$regs[2])?"":" select").$regs[2];
		$result=$this->mybindr_query($testsql);
		while($row=mysql_fetch_row($result)) {
			if($row[0]==0) return $testsql; else return $regs[1];
		}
		return " select null";
	}
	
	function CreateFromShow($myquery) {
		if(!eregi("^create( temporary | )table ([^ ]+)( select .* from | )(show .*)$",  ereg_replace("^\r\n","",$myquery), $regs)) return $myquery;
		$tabledef=""; $valuedef="";
		$result=$this->mybindr_query($regs[4]);
		while($onecol=mysql_fetch_field($result)) {
			switch($onecol->type) {
				case "string": $coldef="CHAR(".$onecol->max_length.")".($onecol->not_null?" NOT NULL":""); break;
				default: $coldef=strtoupper($onecol->type).($onecol->not_null?" NOT NULL":""); break;
			}
			$tabledef.="`".$onecol->name."` ".$coldef.", \r\n";
		}
		$tabledef="CREATE".strtoupper($regs[1])."TABLE ".$regs[2].($regs[3]==" "?"":"_temp")." (".substr($tabledef,0,strlen($tabledef)-4).")";
		if(mysql_num_rows($result)>0) {
			$valuedef="INSERT INTO ".$regs[2].($regs[3]==" "?"":"_temp")." VALUES ";
			while($row=mysql_fetch_row($result)) {
				$valuedef.="(";
				foreach($row as $key=>$value) {
					$valuedef.="'".addslashes($value)."',";
				}
				$valuedef=ereg_replace(",$", "),",$valuedef);
			}
			$valuedef=ereg_replace(",$", "",$valuedef);
		} else $valuedef="";
//							echo $valuedef;
		$result=$this->mybindr_query($tabledef);
		if($regs[3]!=" ") {
			$result=$this->mybindr_query($valuedef, $this->database, false);
			return "CREATE".strtoupper($regs[1])."TABLE ".$regs[2]." ".$regs[3]." ".$regs[2]."_temp";
		} else return $valuedef;
	}
	
	function getquery($name) {
		if(eregi("^action step ([0-9]+)$", $name, $regs)) {
			$this->mybindr_query($this->ResolvePipes("select @action_bid:=b.bid from business b inner join complaint c on b.bid=c.bid where c.cid='|cid|'"));
			$this->mybindr_query($this->ResolvePipes("select @action_mergecode:=GetCBIQuery($regs[1], @action_bid)"));
			$query = "select if(length(cbiquery)>100,cbiquery,ifnull(lm.sqlstatement,cm.sqlstatement)) as cbiquery, '' from (action left join mergequery lm on lm.mergecode=@action_mergecode) left join common.mergequery cm on cm.mergecode=@action_mergecode where cbiquery !='' and code = $regs[1]";
//			$this->mybindr_query($this->ResolvePipes("select @isab:=1 from business b inner join member m using(bid) inner join complaint c on b.bid=c.bid where c.cid='|cid|' and b.member='y' and m.pending!='y'"));
//			$query = "select cbiquery, '' from (select if(length(cbiquery)>100,cbiquery,ifnull(lm.sqlstatement,cm.sqlstatement)) as cbiquery from (action left join mergequery lm on concat(action.cbiquery,'.accredited')=lm.mergecode) left join ".QUERY_DB.".mergequery cm on concat(action.cbiquery,'.accredited')=cm.mergecode where cbiquery !='' and code = ".$regs[1]." and @isab union select if(length(cbiquery)>100,cbiquery,ifnull(lm.sqlstatement,cm.sqlstatement)) as cbiquery from (action left join mergequery lm on action.cbiquery=lm.mergecode) left join ".QUERY_DB.".mergequery cm on action.cbiquery=cm.mergecode where cbiquery !='' and code = ".$regs[1].") as a order by cbiquery is not null desc limit 1";
		} else {
			if(isset($_GET["ebindr2"])) $query = "select sqlstatement, description from ".QUERY_TABLE." where mergecode in (\"$name\",\"e2.$name\") and description!='MYBINDR ONLY' order by mergecode like 'e2.%' desc limit 1";
			else $query = "select sqlstatement, description from ".QUERY_TABLE." where mergecode = \"$name\" and description!='MYBINDR ONLY'";
		}
		$result = $this->mybindr_query($query);
		if(mysql_num_rows($result)==0) {
			if(isset($_GET["ebindr2"])) $query = "select sqlstatement, description from ".QUERY_DB.".".QUERY_TABLE." where mergecode in (\"$name\",\"e2.$name\") order by mergecode like 'e2.%' desc limit 1";
			else $query = "select sqlstatement, description from ".QUERY_DB.".".QUERY_TABLE." where mergecode = \"$name\"";
			$result = $this	->mybindr_query($query);
		}
		$row = mysql_fetch_row($result);
//		if(mysql_num_rows($result)>0 && $row[0]=='') die("$name: No query");
		return $row;
	}
	
	function ResolvePipes($mytext, $replacetildeandcaret=true)
	{
		if($replacetildeandcaret && !isset($_GET["notildereplace"])) {
			$mytext=str_replace("~", $this->entercode, $mytext);
			$mytext=str_replace("^", $this->tabcode, $mytext);
		}
		while ($parm = $this->GetNextPipe($mytext)) {
			$parmret=$this->GetParm(str_replace("|","",$parm));
			$this->didmerge=true;
			if((strpos("\r\n", $parm)!==false || strlen($parm)>100) && $parmret=="" && $this->linebreak=="\\par ") $mytext=substr_replace($mytext, "".str_replace("|","\\'7C",$parm)."", strpos($mytext,$parm), strlen($parm));
			else $mytext = substr_replace($mytext, "".$parmret."", strpos($mytext,$parm), strlen($parm));
		}
//			$mytext = substr_replace($mytext, $this->MergeCode(str_replace("[", "", str_replace("]", "", $mergecode))), strpos($mytext,$mergecode), strlen($mergecode));

//			$mytext=str_replace($parm,"".$this->GetParm(str_replace("|","",$parm))."",$mytext);
		return $mytext;
	}
	
	function ResolveSubQueries($string) {
		// in fall-back cases
		$original = $string;
		// Loops until it can't find any more ( ) pairs with no ( ) between them
		while(ereg("\( *([[:alpha:]]*)[^()]*\) *([[:alpha:]]*)", $string, $value)) {
				if(trim(strtolower($value[1])) !="select" || trim(strtolower($value[2])) != "as")
					// if there is no 'select' or 'as' before or after the ( ) pair, then replace the ( ) with &lp; and &rp;
				   $string = str_replace($value[0], str_replace(")", "&rp;", str_replace("(", "&lp;", $value[0])), $string);
				 else
					// looping ends if there is a 'select' and an 'as' before and after the ( ) pair
					break;
		}
		// Loops until it can't find any more subqueries in the format:   ( ... ) as ...
		while(eregi("\(([^()]*)\) *as ([[:alnum:]\x7c]*)", $string, $value))
		{
//print_r($value);
			// If 'select' is not at the beginning of the subquery found, then replace the ( ) with &lp; and &rp;
			if(!eregi("\( *select", $value[0]))
				$string = str_replace($value[0], str_replace(")", "&rp;", str_replace("(", "&lp;", $value[0])), $string);
			else
			{
				// Store the create table syntax for the subquery
				// $value[1] contains the select statement, and $value[2] contains the subquery table name
				$sub_query[] = "DROP TABLE IF EXISTS " . $value[2];
				$sub_query[] = "CREATE TABLE " . $value[2] . "  " . str_replace("&lp;", "(", str_replace("&rp;", ")", $value[1]));
//				$tables[] = "my" . $value[2];
				$tables[] = $value[2];
				$string = str_replace($value[0],$value[2], $string);
			}
		}
		$string = preg_replace(array("'&rp;'", "'&lp;'"), array(")", "("), $string);
//print_r($sub_query);
//		if($sub_query[0]>"") echo $string;
		$this->runqueries($sub_query);
		return $string; //array("num_sub" => sizeof($sub_query), "tables" => $tables, "query" => $string, "subquery" => $sub_query, "original" => $original);
	}
	
	function ResolveMerge($mytext)
	{
		list($mergecode, $mypos) = $this->GetNextMergeCode($mytext);
		do {
			if(!$mergecode) break;
			if(!ereg("h define letter parameters",$mytext)) $this->didmerge=true;
			if($this->buffer) {
				echo substr($mytext,0,$mypos);
				$mytext=substr($mytext,$mypos);
			}
			$this->StartBuffering();
			$mytext = substr_replace($mytext, $this->MergeCode(str_replace("[", "", str_replace("]", "", $mergecode))), strpos($mytext,$mergecode), strlen($mergecode));
			if($mergecode=='[repeat]') $mytext=$this->ResolveIfMerge($mytext);
		}
		while (list($mergecode, $mypos) = $this->GetNextMergeCode($mytext));

		if($this->buffer) echo $mytext=str_replace("___FIXED_COLOR_TABLE___", $this->MakeColortbl(), str_replace("___FIXED_FONT_TABLE___", $this->MakeFonttbl(), $mytext));
		$this->StartBuffering();
		if($this->buffer) return; else return $mytext;
	}

	function GetNextMergeCode($mytext)
	{
		ereg($this->begincode."[^\x5D\x5B]*".$this->endcode,$mytext,$returned);
		return array($returned[0], strpos($mytext,$returned[0]));
	}

	function mybindr_query($query, $database="", $showerror=true, $logquery=false) {
//		global $reportr;
		if(eregi("^table", $query)) return true;
		if($this->overrideshowerror) $showerror=false;
//		$showerror=false;
//		$query=str_replace(" mergequery ", " ".QUERY_DB.".mergequery ", $query);
		if($database=="") $database=$this->database;
	/*	if(class_exists("reportr")) {
			$reportr->logger("Database: $database Query called: $query", "query.log");
		} else { */
/*			$fp = fopen("/home/serv/temp/queries.log", "a");
			fwrite($fp, date('Y-m-d H:i:s') . " - " . getenv("REMOTE_ADDR") . " - Database: $database Query called: $query\r\n");
			fclose($fp); */
//		}
//		if(ereg("(hurdmantest|austin|huntsville|birmingham|sacramento|evansville|pensacola|vancouver|lubbock|macon|london|boise|clearwater|stlouis|saltlake|chattanooga|concord|elpaso|columbus|sanjose|seatac|raleigh|charlotte|omaha|oakland|greensboro|memphis|chicago|grandrapids|fortcollins|spokane|tucson|milwauke|worcester|tulsa|oklahomacity|dayton|ottawa|knoxville|denver|sanangelo|atlanta|wallingford|baltimore|hawaii|coloradosprings|fresno|toledo|trenton|chicago)",$database)) {
			$usedb=false;
			if(ereg("XMISSION.common", $query)) $usedb="common";
			elseif(ereg("XMISSION.otto", $query)) $usedb="otto";
			else $query=str_replace("XMISSION.", "", $query);
//		}
		if(ereg("XMISSION[.]", $query)) {
			if($this->host=="166.70.32.197") $this->xdb=$this->db;
			if($usedb=="common") $this->xdb=mysql_connect(COMMON_HOST, COMMON_USER, COMMON_PASS);
			if($usedb=="otto") $this->xdb=mysql_connect(OTTO_HOST, OTTO_USER, OTTO_PASS);
			$result = mysql_db_query(($usedb?$usedb:$database), str_replace("XMISSION.", "", $query), $this->xdb);
			return $result;
		}
		if(ereg("OTHERSERVER:([^:]*):([^:]*):([^:]*):([^:.]*)[.]", $query, $regs) || ereg("OTHERSERVER[.]", $query)) {
			if(sizeof($regs)>1) {
				$this->params["OTHERHOST"]=$regs[1];
				$this->params["OTHERUSER"]=$regs[2];
				$this->params["OTHERPASS"]=$regs[3];
				$this->params["OTHERDBNAME"]=$regs[4];
			}
			if($this->host==$this->params["OTHERHOST"]) $this->otherdb=$this->db;
			if(!$this->otherdb) $this->otherdb=mysql_connect(str_replace(";",":",$this->params["OTHERHOST"]), $this->params["OTHERUSER"], $this->params["OTHERPASS"]);
			$result = mysql_db_query($this->params["OTHERDBNAME"], str_replace("OTHERSERVER.", "", ereg_replace("OTHERSERVER:[^:]*:[^:]*:[^:]*:[^:.]*[.]","",$query)), $this->otherdb);
			return $result;
		}
		$query=$this->CreateFromShow($query);
		$query=$this->DropIf($query);
		if((ereg("^CALL ",$query) || ereg("^ CALL ", $query))) {// && !class_exists("mysqli")) {
			list($host,$port)=explode(":",DATABASE_HOST);
			if($port=="") $port="3306";
			shell_exec("mysql -h$host -P$port -u".DATABASE_USER." -p".DATABASE_PASS." $database -e\"".str_replace('"','\\"',$query)."\"");
//		}elseif((ereg("^CALL ",$query) || ereg("^ CALL ", $query)) && class_exists("mysqli")) {
//				$this->mysqli->select_db($database);
//				$this->mysqli->query($query) or die($this->mysqli->error); //$showerror=true;
		} elseif($showerror) { //&& !$this->breakonerror) {
			if(ereg("common[.]mergequery", $query)) {
				$starttime=explode(" ",microtime());
				$result = mysql_db_query($database, $query, $this->mysql2) or die($database.":".$this->lastmergecode.":".$query.":".mysql_error());
				$endtime=explode(" ",microtime());
				$timetook=number_format($endtime[0]+$endtime[1]-$starttime[0]-$starttime[1],3);
				if($logquery) $this->queriesrun[]=$query." /* ".mysql_affected_rows($this->mysql2)." rows affected, $timetook second(s) */;";
			} else {
				$starttime=explode(" ",microtime());
				$result = mysql_db_query($database, $query, $this->db) or die($database.":".$this->lastmergecode.":".$query.":".mysql_error());
				$endtime=explode(" ",microtime());
				$timetook=number_format($endtime[0]+$endtime[1]-$starttime[0]-$starttime[1],3);
				if($logquery) $this->queriesrun[]=$query." /* ".mysql_affected_rows($this->db)." rows affected, $timetook second(s) */;";
			}
			return $result;
/*		} elseif(!$this->breakonerror) {
			$result = mysql_db_query($database, $query, $this->db);
			if(mysql_error()>"") echo $database.":".$query.":".mysql_error();
			return $result; */
		} else {
			if(ereg("common[.]mergequery", $query)) {
				$starttime=explode(" ",microtime());
				$result = mysql_db_query($database, $query, $this->mysql2);
				$endtime=explode(" ",microtime());
				$timetook=number_format($endtime[0]+$endtime[1]-$starttime[0]-$starttime[1],3);
				if($logquery) $this->queriesrun[]=$query."/* ".mysql_affected_rows($this->mysql2)." rows affected, $timetook seconds */;";
			} else {
				$starttime=explode(" ",microtime());
				$result = mysql_db_query($database, $query, $this->db);
				$endtime=explode(" ",microtime());
				$timetook=number_format($endtime[0]+$endtime[1]-$starttime[0]-$starttime[1],3);
				if($logquery) $this->queriesrun[]=$query."/* ".mysql_affected_rows($this->db)." rows affected, $timetook seconds */;";
			}
			return $result;
		}		
	}
	function CheckSetParams($code) {
		if(ereg("set:([^:]*):(.*)", $code, $regs)) {
			if($regs[1]=='params') {
//				if(!$this->runset) return true;
				$regs[2]=str_replace("&l1t;","<",$regs[2]);
				$regs[2]=str_replace("&g1t;",">",$regs[2]);
				$queries = $this->querygroup($regs[2]);
				for($ii=0;$ii<sizeof($queries[update]);$ii++) {
					if(is_array($queries[update][$ii])){ 
						foreach($queries[update][$ii] as $oneupdate) $result = $this->mybindr_query($this->ResolvePipes($oneupdate));
					}
				}
				for($ii=0;$ii<sizeof($queries[select]);$ii++) {
					$result = $this->mybindr_query($this->ResolvePipes($queries[select][$ii]));
					while($fld[] = mysql_fetch_field($result)) {
					}
					$i=0;
					while($row = mysql_fetch_row($result)) {
						for($i=0;$i<sizeof($row);$i++) $this->params[$fld[$i]->name]=$row[$i];
					}
					unset($fld);
				}
				return true;
			} else {
				$this->params[$regs[1]]=$regs[2]."";
				return true; //$regs[2];
			}
		}
		return false;
	}
	function ParseReview($url) {
//		$outputfile=tempnam("/tmp", "BUSREVIEW");
		$folder=$this->GetScanDir("bid", $this->params["bid"]);
		$outputfile=$folder."/Business Review (Overview Tab as of ".date("m-d-Y").").pdf";
		$this->my_exec("/usr/local/bin/wkhtmltopdf -n --load-error-handling ignore -s Letter -q \"$url\" \"$outputfile\"", 90);
//		unlink($outputfile);
		return "[includecolorpdf $outputfile]";
	}
	function SendHTMLS3Image($url) {
		$outputfile=tempnam("/tmp", "HTMLPDF");
		$this->my_exec("/usr/local/bin/wkhtmltopdf --load-error-handling ignore -q \"$url\" \"$outputfile\"", 90);
		$dest="ebindrimages/".basename($outputfile).".pdf";
		$this->sends3public($dest, file_get_contents($outputfile));
		unlink($outputfile);
		return "http://s3.amazonaws.com/".$this->database."-bbb/".$dest;
	}
	function GetPDF($url) {
		$outputfile=tempnam("/tmp", "HTMLPDF");
		$this->my_exec("/usr/local/bin/wkhtmltopdf --javascript-delay 1000 --load-error-handling ignore -q \"$url\" \"$outputfile\"", 90);
		$buffer = file_get_contents($outputfile);
//		unlink($outputfile);
		return $buffer;
	}

	function alPDF($altitle) {
		$outputfile=tempnam("/tmp", "HTMLPDF");
		$this->my_exec("/usr/local/bin/wkhtmltopdf --javascript-delay 1000 --load-error-handling ignore -q /home/serv/temp/\"$altitle\" \"$outputfile\"", 90);
		$buffer = file_get_contents($outputfile);
		// print_r($buffer);
//		unlink($outputfile);
		return $buffer;
	}

	function ParseReport($url) {
		$ch = curl_init();
		$agent = "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.4) Gecko/20030624 Netscape/7.1 (ax)"; // identify as your own user
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_USERAGENT, $agent);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$ret = curl_exec($ch);
		curl_close($ch);
//		$fp = fopen($url, "r");
//		while($r=fread($fp,1000)) $ret.=$r;
//		fclose($fp);
		if(ereg('[{][\]rtf', $ret)) {
				$ret=substr(strstr($ret, '{\rtf'),6);
				$ret=ereg_replace("[}]([^}]*)$","\\1",$ret);
				$ret=str_replace("[","\\'5B", $ret);
				$ret=str_replace("]","\\'5D", $ret);
				$ret=str_replace("|","\\'7C",$ret); 
		}
		$ret=ereg_replace("(\n| )([a-zA-Z][a-z A-Z&'/]+):\\\\tab ([ a-zA-Z0-9$\\(])","\\1{\\b \\2}:\\tab \\3", $ret);
        $ret=ereg_replace("(\n| )([a-zA-Z][a-z A-Z&'/]+): *([ a-zA-Z0-9$])","\\1{\\b \\2}:\\tab \\3", $ret);
		return $ret; 				
		ereg("<style>(.+)</style>", $ret, $regs);
		$ret=ereg_replace("<!--", "\x01", $ret);
		$ret=ereg_replace("-->", "\x02", $ret);
		$styles=$regs[1];
		while(eregi("[.]([a-z_]+)[^{}]+[{]([^{}]+)[}]", $styles, $regs)) {
			if(eregi("font-size: *([0-9]+)", $regs[2], $sregs)) $style[$regs[1]]["font-size"]=$sregs[1];
			if(eregi("font-weight: *([a-z]+)", $regs[2], $sregs)) $style[$regs[1]]["font-weight"]=$sregs[1];
			if(eregi("font-style: *([a-z]+)", $regs[2], $sregs)) $style[$regs[1]]["font-style"]=$sregs[1];
			if(eregi("text-align: *([a-z]+)", $regs[2], $sregs)) $style[$regs[1]]["text-align"]=$sregs[1];
			$styles = str_replace($regs[0], "", $styles);
		}
//		print_r($style);
		while(eregi("\x01 ([^\x01 ]+) \x02([^\x01\x02]*)(\x01|</html>)", $ret, $regs)) {
			$section[]=$regs[2];
			$ret = str_replace($regs[0], $regs[3], $ret);
		}
		$ret="";
		foreach($section as $sec) {
			unset($rows);
			$sec = eregi_replace("<tr", "\x01", $sec);
			$sec = eregi_replace("</tr>", "\x02", $sec);
			while(eregi("\x01([^\x01\x02]+)\x02", $sec, $regs)) {
				$sec = str_replace($regs[0], "", $sec);
				$row = eregi_replace("<td[^>]*>", "\x01", $regs[1]);
				$row = eregi_replace("</td>", "\x02", $row);
				while(eregi("\x01([^\x01\x02]+)\x02", $row, $regs)) {
					$row=str_replace($regs[0], "", $row);
					$col=$regs[1];
					if(eregi("class='([^']+)'", $col, $regs)) $class=$regs[1]; else $class="";
					$col=html_entity_decode(str_replace("!br!", "}\\par{", strip_tags(ereg_replace("</p>", "!br!\r\n", $col))));
					$ret.="{".($style[$class]["text-align"]?'\qc':"").($style[$class]["font-size"]?'\fs'.($style[$class]["font-size"]*2):"").($style[$class]["font-weight"]?'\b':"").($style[$class]["font-style"]?'\i':"").$col.' }';
				}
				$ret.= "\\par\r\n";
			}
		}
//		print_r($section);
		return str_replace("\t","", $ret);
	}		
	function MergeCode($code)
	{
		$this->lastmergecode=$code;
		if($this->CheckSetParams($code)) return '';
		if(eregi("ask:([^:]*):([^\x5D\x5B]*)",$code)) return '';
		$code=ereg_replace("\n *","",ereg_replace("\r *","",$code));
		$code=ereg_replace("<br> *","",$code);
		$result=$this->GetParm($code);
		if(!($result===false)) { 
			if($this->linebreak=="\\par ") $result=preg_replace(array("/\xE2\x84\xA2/", "/\xC9/", "/\xE9/", "/\xE0/", "/\xE7/", "/\xEA/", "/\xEB/", "/\xE8/", "/\xEF/", "/\xAE/", "/\xEE/", "/\xE6/", "/\xF4/", "/\xFB/", "/\xFC/"), array("\\'99", "\\'C9", "\\'E9", "\\'E0", "\\'E7", "\\'EA", "\\'EB", "\\'E8", "\\'EF", "\\'AE", "\\'EE", "\\'E6", "\\'F4", "\\'FB", "\\'FC"), preg_replace(array("/\xC3\x89/", "/\xC3\xA9/", "/\xC3\xA0/", "/\xC3\xA7/", "/\xC3\xAA/", "/\xC3\xAB/", "/\xC3\xA8/", "/\xC3\xAF/", "/\xC2\xAE/", "/\xC3\xAE/", "/\xC3\xA6/", "/\xC3\xB4/", "/\xC3\xBB/", "/\xC3\xBC/"), array("\\'C9", "\\'E9", "\\'E0", "\\'E7", "\\'EA", "\\'EB", "\\'E8", "\\'EF", "\\'AE", "\\'EE", "\\'E6", "\\'F4", "\\'FB", "\\'FC"), $result));
			elseif($this->linebreak=="<br>") $result=preg_replace(array("/\xc9/","/xe2\xae/", "/\xae/", "/\xe0/", "/\xe6/", "/\xe7/", "/\xe8/","/\xe9/", "/\xea/", "/\xeb/", "/\xee/", "/\xef/", "/\xf4/", "/\xfb/", "/\xfc/"), array("&Eacute;","&reg;","&reg;", "&agrave;", "&aelig;", "&ccedil;","&egrave;", "&eacute;", "&ecirc;", "&euml;", "&icirc;", "&iuml;", "&ocirc;", "&ucirc;", "&uuml;"),$result);			
			if($this->doubledoublequotes) return str_replace(chr(34),chr(39).chr(39),$result);
			else return $result;
		}
		if(isset($_GET["ebindr2"])) $result = $this->mybindr_query("SELECT sqlstatement FROM ".QUERY_TABLE." WHERE mergecode in ('" . addslashes($code) . "','e2." . addslashes($code) . "') order by mergecode like 'e2.%' desc limit 1");
		else $result = $this->mybindr_query("SELECT sqlstatement FROM ".QUERY_TABLE." WHERE mergecode LIKE '" . addslashes($code) . "'");
		if(mysql_num_rows($result) < 1)
			if(isset($_GET["ebindr2"])) $result = $this->mybindr_query("SELECT sqlstatement FROM ".QUERY_DB.".".QUERY_TABLE." WHERE mergecode in ('" . addslashes($code) . "','e2." . addslashes($code) . "') order by mergecode like 'e2.%' desc limit 1");
			else $result = $this->mybindr_query("SELECT sqlstatement FROM ".QUERY_DB.".".QUERY_TABLE."  WHERE mergecode LIKE '" . addslashes($code) . "'");
		if(mysql_num_rows($result) < 1 && !eregi("^select ", $code)) {
			$this->errormerge=true;
			return "--Can't find merge code: $code--";
		}
	//		return $this->GetParm($code);
		elseif(mysql_num_rows($result) < 1 && eregi("^select ", $code))
			$row=array($code);
		else
			$row = mysql_fetch_row($result);
		if(eregi("^\[", $row[0])) return $row[0];
		if(eregi("(\r\n|^)graph", $row[0])) return $this->RTFGraph($row[0]);
		$sqlstatements = "||\r\n".$row[0]."\r\n||";
		$sqlstatements = $this->ResolvePipes($sqlstatements);
		$sqlstatement = $this->GetNextQuery($sqlstatements);
		$content="";
		// if subquery
//		eregi("\( *select", $sqlstatement);
	//	stristr("(select", $sqlstatement);
		// end of subquery
//		eregi("\) *as", $sqlstatement);
	//	stristr(") as", $sqlstatement);
		
		do
		{
			if(!$sqlstatement) break;
			$origsql = $sqlstatement;
//			$sqlstatement = $this->ResolveSubQueries($sqlstatement);
			if(eregi("^includewebreview (.*)",ereg_replace("^\r\n","",str_replace("|","",$sqlstatement)), $regs)) {
				list($url)=mysql_fetch_row($this->mybindr_query($regs[1],$this->database,false));
				$content.=$this->ParseReview($url);
//			} else if(!($result = $this->mybindr_query(str_replace("|","",$sqlstatement),$this->database,false)))
			} else if(eregi("^getpdf (.*)",ereg_replace("^\r\n","",str_replace("|","",$sqlstatement)), $regs)) {
//				list($url)=mysql_fetch_row($this->mybindr_query($regs[1],$this->database,false));
				$url=$regs[1];
				return $this->GetPDF($url);
//			} else if(!($result = $this->mybindr_query(str_replace("|","",$sqlstatement),$this->database,false)))
			} else if(eregi("^sendhtmls3image (.*)",ereg_replace("^\r\n","",str_replace("|","",$sqlstatement)), $regs)) {
//				list($url)=mysql_fetch_row($this->mybindr_query($regs[1],$this->database,false));
				$url=$regs[1];
				$content.=$this->SendHTMLS3Image($url);
//			} else if(!($result = $this->mybindr_query(str_replace("|","",$sqlstatement),$this->database,false)))
			} else if(eregi("^includewebreport (.*)",ereg_replace("^\r\n","",str_replace("|","",$sqlstatement)), $regs)) {
				list($url)=mysql_fetch_row($this->mybindr_query($regs[1],$this->database,false));
				$content.=$this->ParseReport($url);
//			} else if(!($result = $this->mybindr_query(str_replace("|","",$sqlstatement),$this->database,false)))
			} else if(!($result = $this->runqueries(array(trim(str_replace("|","",$sqlstatement),"\r\n"))))) {
				$this->errormerge=true;
				return "Error in mergecode: ".$this->lastmergecode.", Database: ".$this->database.", Query: ".str_replace("|","",$sqlstatement).", Error: ".mysql_error(); //""; //"--Error in merge code: $code: ".str_replace("|","",$sqlstatement)."--";
			}
			if(eregi("^use (.+)$",ereg_replace("^\r\n"," ",str_replace("|","",$sqlstatement)),$useregs)) $this->database=trim($useregs[1]);
			if(!eregi("^includewebr",ereg_replace("^\r\n","",str_replace("|","",$sqlstatement))) && !eregi("^drop",ereg_replace("^\r\n","",str_replace("|","",$sqlstatement))) && !eregi("^use ",ereg_replace("^\r\n","",str_replace("|","",$sqlstatement))) && !eregi("^ select", ereg_replace("^\r\n","",str_replace("|","",$sqlstatement)))) {
				if(isset($_GET["json"])) {
					while(@$row=mysql_fetch_array($result, MYSQL_ASSOC)) {
						$this->jsonobject[]=$row;
					}
				} else {
					while(@$row = mysql_fetch_row($result))
						for($i=0;$i<sizeof($row);$i++)
							$content.=(ereg("^RTF ",$code)?$row[$i]:str_replace("\r\n", $this->linebreak,$row[$i]));
				}
			}
			$sqlstatements=str_replace($origsql, "||", $sqlstatements);
		} while($sqlstatement = $this->GetNextQuery($sqlstatements));
	//	echo $code."\r\n";
//		return $content;
		if($this->linebreak=="\\par ") $content=preg_replace(array("/\xE2\x84\xA2/", "/\xC9/", "/\xE9/", "/\xE0/", "/\xE7/", "/\xEA/", "/\xEB/", "/\xE8/", "/\xEF/", "/\xAE/", "/\xEE/", "/\xE6/", "/\xF4/", "/\xFB/", "/\xFC/"), array("\\'99", "\\'C9", "\\'E9", "\\'E0", "\\'E7", "\\'EA", "\\'EB", "\\'E8", "\\'EF", "\\'AE", "\\'EE", "\\'E6", "\\'F4", "\\'FB", "\\'FC"), preg_replace(array("/\xC3\x89/", "/\xC3\xA9/", "/\xC3\xA0/", "/\xC3\xA7/", "/\xC3\xAA/", "/\xC3\xAB/", "/\xC3\xA8/", "/\xC3\xAF/", "/\xC2\xAE/", "/\xC3\xAE/", "/\xC3\xA6/", "/\xC3\xB4/", "/\xC3\xBB/", "/\xC3\xBC/"), array("\\'C9", "\\'E9", "\\'E0", "\\'E7", "\\'EA", "\\'EB", "\\'E8", "\\'EF", "\\'AE", "\\'EE", "\\'E6", "\\'F4", "\\'FB", "\\'FC"), $content));

		if(ereg("^RTF (business|consumer) response$", $code)) $this->RTFFileSize+=strlen($content);
		if($this->doubledoublequotes) return str_replace(chr(34),chr(39).chr(39),$this->ResolveIfMerge($content));
		else return $this->ResolveIfMerge($content);
	}
	
	function GetParm($myparm)
	{
//	echo "$myparm\r\n";
//		$myparm = strtolower($myparm);
		if($myparm=="repeat") {
			return $this->ResolveIfMerge($this->params["repeat"]);
		}
		if($this->CheckSetParams($myparm)) return '';

		if(eregi("merge(:| )(.*)", $myparm, $regs)) {
			return $this->ResolveIfMerge($this->merge($regs[2], true));
		}
		if(ereg("^stored files (cid|bid)$",$myparm,$regs)) {
			$filecid=$this->params[$regs[1]];
			if(strlen($filecid)<3) $filecid.="XX";
			$directory = DOCS_BASE_DIR.'/'.strtolower($regs[1]).'/'.substr($filecid,strlen($filecid)-2).'/'.substr($filecid,0,strlen($filecid)-2);
			if($d = @opendir($directory)) {
				while(false !== ($filename = readdir($d)))
					if ($filename != "." && $filename != ".." && $filename!='trash') $filelist[]=$filename;
			}
			if(!isset($filelist) || $this->is_empty_dir($directory)) return "0"; else return sizeof($filelist);
		}
		if(ereg("^stored file list (cid|bid)$",$myparm,$regs)) {
			$filecid=$this->params[$regs[1]];
			if(strlen($filecid)<3) $filecid.="XX";
			$directory = DOCS_BASE_DIR.'/'.strtolower($regs[1]).'/'.substr($filecid,strlen($filecid)-2).'/'.substr($filecid,0,strlen($filecid)-2);
			if($d = @opendir($directory)) {
				while(false !== ($filename = readdir($d)))
					if ($filename != "." && $filename != ".." && $filename!='trash') $filelist[]=str_replace('"','\\"',$filename);
			}
			if(!isset($filelist)) return "'***NOFILES***'"; else return "\"".str_replace(array("[","]","|"),"",implode("\",\"",$filelist))."\"";
		}
		if(ereg("^stored file list (cid|bid) (.+)$",$myparm,$regs)) {
			$filecid=$this->params[$regs[1]];
			if(strlen($filecid)<3) $filecid.="XX";
			$directory = DOCS_BASE_DIR.'/'.strtolower($regs[1]).'/'.substr($filecid,strlen($filecid)-2).'/'.substr($filecid,0,strlen($filecid)-2);
			if($regs[2]>"") $directory.="/".$regs[2];
			if($d = @opendir($directory)) {
				while(false !== ($filename = readdir($d)))
					if ($filename != "." && $filename != ".." && $filename!='trash') $filelist[]=str_replace('"','\\"',$filename);
			}
			if(!isset($filelist)) return "('***NOFILES***')"; else return "(\"".str_replace(array("[","]","|"),"",implode("\"),(\"",$filelist))."\")";
		}
		if(eregi("^includeurl (.*)", $myparm, $regs)) {
			return "[includepdf /home/serv/library/h2p/public_html/".exec("php /home/serv/library/html2pdf.php \"".$regs[1]."\"")." delete]";
		}
		if(isset($this->params["staff name"]) && eregi("^staff name$", $myparm, $regs)) {
			return stripslashes($this->params["staff name"]);
		}
		if(eregi("^RTFFileSize$", $myparm, $regs)) {
			return $this->RTFFileSize;
		}
		if(eregi("^biddata$", $myparm)) {
			$queries[]="USE ".LOCAL_DB;
			$this->mybindr_query($this->ResolvePipes(" CALL UpdateRating(|bid|, concat('myratingbusiness',connection_id()))"));
//			$this->mybindr_query($this->ResolvePipes("insert into bbbapi_business (lastmessageid, sourceupdated, sourcebusinessid) values (null, now(), |bid|) on duplicate key update lastmessageid=null, sourceupdated=now()"));
			$this->mybindr_query($this->ResolvePipes("insert into bbbapi2_charity (lastmessageid, sourceupdated, sourcebusinessid) select null, now(), b.bid from business b inner join .charity c using(bid) left join tobs t on b.bid=t.bid and t.main='y' and t.roster='y' left join tob tt on t.tob=tt.code where b.bid=|bid| and (b.reportcode in ('CHAR','PAS') or ifnull(tt.description,'') like 'charity%') and c.communitymember!='y' on duplicate key update lastmessageid=null, sourceupdated=now()"));
			if(mysql_affected_rows($this->db)==0) $this->mybindr_query($this->ResolvePipes("insert into bbbapi2_business (lastmessageid, sourceupdated, sourcebusinessid) values (null, now(), |bid|) on duplicate key update lastmessageid=null, sourceupdated=now()"));
/*			$r = $this->mybindr_query("select * from common.reportinfo");
			while($row=mysql_fetch_assoc($r)) { 
				$reportinfo[$row["TableName"]][]=$row["FieldName"];
			}
			foreach($reportinfo as $table=>$fields) {
				if($table=="complaintpublish") $r = $this->mybindr_query("select $table.".implode(", $table.",$fields)." from $table inner join complaint on $table.cid=complaint.cid where complaint.bid=".$this->params["bid"], "", false);
				else $r = $this->mybindr_query("select `".implode("`, `",$fields)."` from $table where bid=".$this->params["bid"], "", false);
				if(mysql_num_rows($r)<100 || $table=="complaint" || $table=="complaintpublish") {
					$i=0;
					$queries[]="lock table $table write";
					if($table=="complaintpublish") $queries[]="delete $table from $table inner join complaint on $table.cid=complaint.cid where complaint.bid=".$this->params["bid"];
					else $queries[]="delete from $table where bid=".$this->params["bid"];
					if(mysql_num_rows($r)>0) {
						do {
							$queries[]="replace into $table (`".implode("`, `",$fields)."`) values ";
							while($row=mysql_fetch_field($r)) { $types[$table.".".$row->name]=$row; }
							while($row=mysql_fetch_assoc($r)) {
								unset($vals);
								foreach($row as $key=>$val) {
									if(is_null($val)) $vals[]="NULL";
									elseif(!$types[$table.".".$key]->numeric || eregi("date",$types[$table.".".$key]->type) || eregi("time",$types[$table.".".$key]->type)) $vals[]="'".addslashes($val)."'";
									else $vals[]=$val;
								}
								$queries[sizeof($queries)-1].="(".implode(", ",$vals)."),";
								$i++;
								if($i>100) {
									$keeprunning=true;
									$i=0;
									break;
								} else $keeprunning=false;
							}
							$queries[sizeof($queries)-1]=rtrim($queries[sizeof($queries)-1],",");
						} while($keeprunning);
					}
					$queries[]="unlock tables";
				}
			}
			$queries[]="replace into bidchanged(bid) values (".$this->params["bid"].")";
			$i=0;
			foreach($queries as $query) {
				$params["queries[".$i++."]"]=$query;
			}
			$params["a"]="jv8re0jg4394f3jj94j3io";
			$ch = curl_init();
			$agent = "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.4) Gecko/20030624 Netscape/7.1 (ax)"; // identify as your own user agent (like ‘MSIE’) if you want
			curl_setopt($ch, CURLOPT_URL,"http://lb1.uberdb.hurdman.org/u.php");
			curl_setopt($ch, CURLOPT_USERAGENT, $agent);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
			curl_setopt($ch, CURLOPT_TIMEOUT, 120);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			$result = curl_exec ($ch);
			if(strlen($result)<5) $result="Error: ".ucfirst(curl_error($ch));
			else $r = $this->mybindr_query("update reportqueue set completed=now() where completed is null and reason is null and bid=".$this->params["bid"]);
			curl_close ($ch);*/
			$result="Business Profile update successfully started";
			list($launched, $statusday, $statusmsg) = mysql_fetch_row($this->mybindr_query("select setup(7124) like 'y%', date_format(now()-interval unix_timestamp(utc_timestamp())-unix_timestamp(utcday) second,'%l:%i %p'), concat(message,if(message like '%hour%' or substring_index(substring_index(message,' minute',1), ' ', -1)>20,'. Updates are currently delayed more than normal.','. This is within the normal processing time.')) as message from common.bbbapi2_status where day>now()-interval 24 hour and now()-interval unix_timestamp(utc_timestamp())-unix_timestamp(utcday) second>curdate() order by day desc limit 1"));
			if($launched) $result.=". <br><br>Business Profile data is no longer realtime. Council has indicated that normal update times are less than 20 minutes. As of $statusday $statusmsg ";
			return $result;

			
		}
		if(eregi("getwebpage(:| )(.*)", $myparm, $regs)) {
			$ch = curl_init();
			$agent = "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.4) Gecko/20030624 Netscape/7.1 (ax)";
			$cookie_file_path = "";
			$regs[2]=$this->ResolvePipes(str_replace("~", "|", $regs[2]));
			if(ereg("^addslashes ", $regs[2])) {
				$addslash=true;
				$regs[2]=ereg_replace("^addslashes ", "", $regs[2]);
			}
			if(!eregi("^http", $regs[2])) $regs[2]="http://".$regs[2];
//			$regs[2]=preg_replace("/(^.+\/report\/)([^\/]+)(\/.+$)/e", "'\\1'.rawurlencode('\\2').'\\3'", $regs[2]);
			$regs[2]=preg_replace("/(^.+\/report\/)([^\/?]+)(\/|[?])(.+$)/e", "'\\1'.rawurlencode('\\2').'\\3\\4'", $regs[2]);

			curl_setopt($ch, CURLOPT_URL, $myurl=$regs[2]);
			curl_setopt($ch, CURLOPT_USERAGENT, $agent);
//			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file_path);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file_path);
			curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_TIMEOUT, 43200);
//			curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
			$result = curl_exec ($ch);
			if(curl_error($ch)) $result.=curl_error($ch).":".$myurl;
			curl_close ($ch);
			if($addslash) return str_replace(array(">","|", "[", "]"), array(">\n", "',char(124),'", "',char(91),'", "',char(93),'"), addslashes($result)); else return $result;
		}
/*		if(eregi("getwebpage(:| )(.*)", $myparm, $regs)) {
			$ch = curl_init();
			$agent = "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.4) Gecko/20030624 Netscape/7.1 (ax)"; 
			$cookie_file_path = ""; 
			curl_setopt($ch, CURLOPT_URL,$regs[2]);
			curl_setopt($ch, CURLOPT_USERAGENT, $agent);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file_path);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file_path);
			curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_TIMEOUT, 43200);
			$result = curl_exec ($ch);
			curl_close ($ch);
			return $result;

			$fp=fopen($regs[2],"r");
			while($myret=fread($fp,1024)) {
				$ret.=$myret;
			}
			fclose($fp);
			return $ret;
		}*/
		if(eregi("runemailreports", $myparm, $regs)) {
				define('LOCAL_HOST', '127.0.0.1');
				if($this->params["attime"]>"") $attime="'".$this->params["attime"]."'"; else $attime="now()";
				$r = $this->mybindr_query("drop table if exists emailreportlists_temp");
				$r = $this->mybindr_query("create table emailreportlists_temp (primary key(mergecode,runreport,hashparameters)) select MergeCode, group_concat(staff) as Staff, group_concat(departments) as Departments, group_concat(title) as Title, RunReport, Day, WhatTime, UsedParameters, hashparameters from (select * from emailreportlists union select *, null, null, null from common.emailreportlists_common) as a group by mergecode, runreport, day, whattime, usedparameters");
				$r = $this->mybindr_query($query="select distinct if(instr(ifnull(m.sqlstatement,mc.sqlstatement),'|staff|'),s.initials,'') as myinit, t.mergecode, t.runreport, t.UsedParameters from emailreportlists_temp t inner join staff s on find_in_set(s.initials,t.staff) and s.active='y' and s.email>'' left join  mergequery m on t.mergecode=m.mergecode left join common.mergequery mc on t.mergecode=mc.mergecode where (date_format($attime,'%h')+0)=substring_index(whattime,' ',1) or '".$_GET["runall"]."'='yes' union 
				select if(instr(ifnull(m.sqlstatement,mc.sqlstatement),'|staff|'),s.initials,'') as myinit, t.mergecode, t.runreport, t.UsedParameters from emailreportlists_temp t inner join common.staff s on find_in_set(s.initials,t.staff) and s.active='y' and s.email>'' left join  mergequery m on t.mergecode=m.mergecode left join common.mergequery mc on t.mergecode=mc.mergecode where (date_format($attime,'%h')+0)=substring_index(whattime,' ',1) or '".$_GET["runall"]."'='yes' union 
				select if(instr(ifnull(m.sqlstatement,mc.sqlstatement),'|staff|'),s.initials,'') as myinit, t.mergecode, t.runreport, t.UsedParameters from emailreportlists_temp t inner join staff s on find_in_set(s.title,t.title) and s.active='y' and s.email>'' left join  mergequery m on t.mergecode=m.mergecode left join common.mergequery mc on t.mergecode=mc.mergecode where (date_format($attime,'%h')+0)=substring_index(whattime,' ',1) or '".$_GET["runall"]."'='yes' union 
				select if(instr(ifnull(m.sqlstatement,mc.sqlstatement),'|staff|'),s.initials,'') as myinit, t.mergecode, t.runreport, t.UsedParameters from emailreportlists_temp t inner join staff s on (s.departments & t.departments) and s.active='y' and s.email>'' left join  mergequery m on t.mergecode=m.mergecode left join common.mergequery mc on t.mergecode=mc.mergecode where (date_format($attime,'%h')+0)=substring_index(whattime,' ',1) or '".$_GET["runall"]."'='yes'");
				while(list($mystaff, $mc, $frequency, $usedparameters)=mysql_fetch_row($r)) {
//                              return "http://127.0.0.1/report/".urlencode($mc)."/?noheader&BYPASS=gure8wh3";
						$this->mybindr_query("replace into emailreportresults (mergecode, content, asof, staff, hashparameters) values ('".$mc."',
'".addslashes($this->GetParm("getwebpage http://".LOCAL_HOST."/report/$mc/?print=true&BYPASS=gure8wh3&BYPASS2=9vfjesu3hgi&staff=$mystaff&Frequency=$frequency&USEDEFAULTS=true&USED_PARAMETERS=$usedparameters"))."',curdate(),'$mystaff','".md5($usedparameters)."')");
						$codes[]=$mc;
				}
				$r = $this->mybindr_query("drop table if exists emailreportlists_temp");
				return $query."\r\n".implode("\r\n",$codes);
		}

		if(eregi("runnightreports", $myparm, $regs)) {
				define('LOCAL_HOST', '127.0.0.1');
				return "This is now done by runreportqueue.sh";
				$r = $this->mybindr_query("select distinct t.mergecode from nightreportlists t left join mergequery m on t.mergecode=m.mergecode where ".(LOCAL_DB=="hurdmantest"?"t.runonhurdman='y' and ":"")."case runreport when 'Daily' then 1 when 'Weekly' then dayofweek(curdate())=day when 'Monthly' then dayofmonth(curdate())=day when 'Yearly' then right(curdate(),5)=day end and (date_format(now(),'%h')+0)=substring_index(whattime,' ',1) and (t.runweekends='y' or dayofweek(curdate()) between 2 and 6) union select distinct t.mergecode from common.nightreportlists t left join common.mergequery m on t.mergecode=m.mergecode and m.mergecode not like 'menu.custom%' where ".(LOCAL_DB=="hurdmantest"?"t.runonhurdman='y' and ":"")."case runreport when 'Daily' then 1 when 'Weekly' then dayofweek(curdate())=day when 'Monthly' then dayofmonth(curdate())=day when 'Yearly' then right(curdate(),5)=day end and (date_format(now(),'%h')+0)=substring_index(whattime,' ',1) and (t.runweekends='y' or dayofweek(curdate()) between 2 and 6)");
				while(list($mc)=mysql_fetch_row($r)) {
					$reports[]=$mc;
					$this->mybindr_query("replace into nightreportresults (mergecode, content, asof) values ('".$mc."', '', curdate())");
				}
				foreach($reports as $mc) {
					$this->mybindr_query("update nightreportresults set content=concat(now(),'".addslashes($this->GetParm("getwebpage http://".LOCAL_HOST."/report/$mc/?BYPASS=gure8wh3"))."') where mergecode='".$mc."' and content=''");
				}
				return "";
		}
		
		if( eregi( "^qrbarcode (.*)($| delete)", $myparm, $regs ) ) {
			// look for the width and the height
			preg_match('/([\d]+)[\sx]+([\d]+)/i', $regs[1], $match);
			$regs[1] = str_replace( $match[0] . " ", "", $regs[1] );

			if( LOCAL_DB == 'lakecharles' || LOCAL_DB == 'sanjose' ) {
			
				if( isset($match[1]) && isset($match[2]) ) {
					if( $match[1] < 501 ) $width = $match[1];
					if( $match[2] < 501 ) $height = $match[2];
				} else {
					$width = 500;
					$height = 500;
				}
	
				$contents = file_get_contents("http://chart.googleapis.com/chart?chs=".$width."x".$height."&cht=qr&chl=".$regs[1]."&chld=H|0");
				
			} else {
			
				// bring in the qr barcode library
				if( !class_exists("qrcode") ) include "/home/serv/public_html/ebindr/includes/qrbarcode/qrlib.php";
			
				// create the barcode file
				$tmpfile = "/tmp/qr" . time();
				QRcode::png($regs[1], $tmpfile, 'H', 6, 0);
				// get the file
				list( $width, $height, $type, , $bits, $mime ) = GetImageSize($tmpfile);
				if( isset( $match[1] ) && isset($match[2]) ) {
					$width = $match[1];
					$height = $match[2];
				}
				$contents = file_get_contents($tmpfile);
				
			}
			$scale = 2000;
			// set the rtf code and the file info
			$barcode = '{\pict\picscalex'.$scale.'\picscaley'.$scale.'\pngblip\picw' . $width . '\pich' . $height . '\picwgoal' . $width . '\pichgoal' . $height . ' ' . chunk_split(bin2hex($contents),128) . '}';
			
			if( isset($tmpfile) && class_exists("qrcode") ) unlink($tmpfile);
			return $barcode;
		}
		
		if(eregi("password:(.*)", $myparm, $regs)) {
			$mypwd="";
			while(strlen($mypwd)<$regs[1]) {
				if(ord($a=chr(rand(49,122)))>57 && ord($a)<97)
					continue;
				else $mypwd.=$a;
			}
			return $mypwd;
		} elseif(strstr($myparm, ":"))
		{
			list($param, $options) = explode(":", $myparm);
			$this->pipe_options[$myparm] = $options;
			$myparm = $param;
		}
		if(eregi("^md5 (.*)",$myparm,$regs)) {
			$myparm=$regs[1];
// 			return md5($this->params[myparm].date("YmdH", time()));
			return "md5($myparm)";
		}
		if(eregi("^md5time (.*)",$myparm,$regs)) {
			$myparm=$regs[1];
// 			return md5($this->params[myparm].date("YmdH", time()));
			return "md5(concat($myparm,date_format(now(), \"%Y%m%d%H\")))";
		}
		if(eregi("^number (.*)",$myparm,$regs)) {
			$myparm=$regs[1];
			return "if($myparm=0,'-',format($myparm,2))";
		}
		if(eregi("^today (.*)",$myparm,$regs)) {
			$myparm=$regs[1];
			$myparm=str_replace("mmmm","F",$myparm);
			$myparm=str_replace("mm","m",$myparm);
			$myparm=str_replace("yyyy","Y",$myparm);
			$myparm=str_replace("yy","y",$myparm);
			$myparm=str_replace("dd","d",$myparm);
			$myparm=str_replace("d","j",$myparm);
			return date($myparm);
		}
		if(eregi("^fulladdress *(.*|$)",$myparm,$regs)) {
			$myparm=$regs[1];
			if($myparm=="") $myparm="address";
			return "concat($myparm.street1, ' ',$myparm.street2,' ',$myparm.city,', ',$myparm.stateprov,' ',$myparm.postalcode)";
		}
		if(eregi("^encodeapostrophe (.*)",$myparm,$regs)) {
			$myparm=$regs[1];
			return str_replace("'", "&#39;", $this->params[$myparm]);
		}
		if(eregi("^money (.*)",$myparm,$regs)) {
			$myparm=$regs[1];
			return "if(($myparm)=0,'-',format(($myparm)/100,2))";
		}
		if(eregi("^fixphone# (.*)",$myparm,$regs)) {
			$myparm=$regs[1];
			$this->params[$myparm] = (ereg("^[+]", $this->params[$myparm])?$this->params[$myparm]:ereg_replace("[^0-9]", "", $this->params[$myparm]));
			return $this->params[$myparm];
		}
		if(eregi("^makemoney (.*)",$myparm,$regs)) {
			$myparm=$regs[1];
			$this->params[$myparm] = ereg_replace("[^0-9.]", "", $this->params[$myparm])*100;
			return $this->params[$myparm];
		}
		if(eregi("^fieldurlencode (.*)$", $mypram, $regs)) {
			$this->params[$myparm] = "replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(".$regs[1].",char(37),'%25'),char(32),'%20'),char(33),'%21'),char(34),'%22'),char(35),'%23'),char(36),'%24'),char(38),'%26'),char(39),'%27'),char(40),'%28'),char(41),'%29'),char(42),'%2A'),char(43),'%2B'),char(44),'%2C'),char(45),'%2D'),char(46),'%2E'),char(47),'%2F'),char(59),'%3B'),char(60),'%3C'),char(61),'%3D'),char(62),'%3E'),char(63),'%3F'),char(64),'%40'),char(91),'%5B'),char(92),'%5C'),char(93),'%5D'),char(94),'%5E'),char(95),'%5F'),char(96),'%60')";
			return $this->params[$myparm];
		}
		if(eregi("^urlencode (.*)",$myparm,$regs)) {
			$myparm=$regs[1];
//			$this->params[$myparm] = urlencode($this->params[$myparm]);
//			return $this->params[$myparm];
			return urlencode($this->params[$myparm]);
		}
		if(eregi("^addslashes (.*)",$myparm,$regs)) {
			$myparm=$regs[1];
			return addslashes(str_replace("|","",$this->params[$myparm]));
		}
		if(eregi("get new cid(.*)",$myparm,$regs)) {
			$getexistcid = $this->ResolvePipes(str_replace("PIPE","|",trim($regs[1])));
			$result=$this->mybindr_query("lock table setup write, complaint read");
			do {
				if($getexistcid) {
					$result=$this->mybindr_query($getexistcid);
					if(mysql_num_rows($result)<1) $getexistcid="";
					else $row=mysql_fetch_row($result);
				}
				if(!$getexistcid) {
					$result=$this->mybindr_query("select value+1 from setup where code=10");
					$row=mysql_fetch_row($result);
					$result=$this->mybindr_query("update setup set value=value+1 where code=10");
				}
				$result=$this->mybindr_query("select cid from complaint where cid=".$row[0]);
			} while(mysql_num_rows($result)>0 && $getexistcid=='');
			$result=$this->mybindr_query("unlock tables");
			$this->params[$myparm] = $row[0];
			$this->params["cid"] = $row[0];
			return $this->params[$myparm];
		}
		if(eregi("^(\*|\@)",$myparm,$regs) && !empty($this->params[$myparm])) {
			return stripslashes($this->params[$myparm]);
		}
		if(eregi("^yahoomap (.*)$",$myparm,$regs)) {
			return "concat('<a target=new href=\'http://maps.yahoo.com/py/maps.py?addr=',".$regs[1].".street1,'&zip=',".$regs[1].".postalcode,'><img src='/css/map.gif'></a>')";
		}
		if(eregi("^get new bid$",$myparm,$regs)) {
			$result=$this->mybindr_query("lock table setup write, business read");
			do {
				$result=$this->mybindr_query("select value+1 from setup where code=9");
				$row=mysql_fetch_row($result);
				$result=$this->mybindr_query("update setup set value=value+1 where code=9");
				$result=$this->mybindr_query("select bid from business where bid=".$row[0]);
			} while(mysql_num_rows($result)>0);
			$result=$this->mybindr_query("unlock tables");
			$this->params[$myparm] = $row[0];
			$this->params["bid"] = $row[0];
			return $this->params[$myparm];
		}
		if(eregi("^getval (.*)$", $myparm, $regs)) {
			$r=$this->mybindr_query($regs[1]);
			$row=mysql_fetch_row($r);
			foreach($row as $key=>$value) return $value;
			return "";
		}
		if(eregi("^cibrinternal$", $myparm, $regs)) {
			$query="select userid, password from cibrinternal where staff='|staff|' and password>'' and userid>''";
			$result = $this->mybindr_query($this->ResolvePipes($query));
			if(mysql_num_rows($result)<1) {
				$query="select value from setup where code=679";
				$row=mysql_fetch_row($this->mybindr_query($this->ResolvePipes($query)));
				list($cibruser, $cibrpass)=explode("\r\n", $row[0]);
			} else {
				list($cibruser, $cibrpass) = mysql_fetch_row($result);
			}
			if($cibrpass=="") return "Error in authentication";
			require_once "/home/serv/library/cibrinternal.php";
			$this->c = new cibrinternal($cibruser, $cibrpass);
			$this->c->paramfile="/var/tmp/cibrinternal_params_".$_COOKIE["reportr_username"];
			if(isset($_GET["name"])) $search=array("name"=>$_GET["name"]);
			elseif(isset($_GET["address"])) $search=array("address"=>$_GET["address"], "city"=>$_GET["city"], "state"=>strtoupper($_GET["state"]), "zip"=>$_GET["zip"]);
			elseif(isset($_GET["phone"])) $search=array("phone"=>$_GET["phone"]);
			elseif(isset($_GET["lastname"])) $search=array("lastname"=>$_GET["lastname"], "firstname"=>$_GET["firstname"]);
			$this->c->newwindow="/report/merge/cibrinternal.tpl?NOASK&name=".$_GET["name"]."&address=".$_GET["address"]."&city=".$_GET["city"]."&state=".strtoupper($_GET["state"])."&zip=".$_GET["zip"]."&phone=".$_GET["phone"]."&lastname=".$_GET["lastname"]."&firstname=".$_GET["firstname"];
			$this->c->InternalSearch($search);
			return $this->c->styles."CIBR Internal Search Results (<a href=\"".$this->c->newwindow."\" target=_blank>Click here to open in new window</a>)".$this->c->results;
		}
		if(eregi("^(viewstate|eventvalidation)_(name|phone|employee|address)$", $myparm, $regs)) {
			require_once "/home/serv/library/cibrinternal.php";
			$this->c = new cibrinternal("", "");
			$this->c->paramfile="/var/tmp/cibrinternal_params_".$_COOKIE["reportr_username"];
			return $this->c->GetParm($regs[0]);
		}
		if(eregi("^setup[(]([0-9]+)[)]$", $myparm, $regs)) {
			$r=$this->mybindr_query("select value from setup where code=".$regs[1]);
			$row=mysql_fetch_row($r);
			foreach($row as $key=>$value) return $value;
		}
		if(eregi("^phone# (.*)", $myparm, $regs)) 
		{
			$myparm = $regs[1];
			if(defined("FORMAT_PHONE_FUNC")) $func=FORMAT_PHONE_FUNC; else $func="FormatPhone";
			return "$func($myparm)"; //concat(mid($myparm,1,3),' ',mid($myparm,4,3),'-',mid($myparm,7,4),if(length($myparm)>10,concat(' ext ',mid($myparm,11)),''))";
		}
		if(eregi("^ALL CID FILES", $myparm, $regs)) 
		{
			list($query,)=$this->getquery($myparm);
			$result = $this->mybindr_query($this->ResolvePipes($query));
			$filename=tempnam("/tmp","");
			unlink($filename);
			mkdir($filename);
			while(list($cid)=mysql_fetch_row($result)) {
				if(strlen($cid)<3) $cid.="XX";
				if(exec("dir -l ".DOCS_BASE_DIR."/cid/".substr($cid,strlen($cid)-2,2)."/".substr($cid,0,strlen($cid)-2)."/ | grep -v trash | wc -l")>1) {
					mkdir($filename."/".$cid);
					exec("cp -r ".DOCS_BASE_DIR."/cid/".substr($cid,strlen($cid)-2,2)."/".substr($cid,0,strlen($cid)-2)."/* $filename/$cid");
				}
			}
			chdir($filename);
			exec("zip -0 -r ".basename($filename).".zip *");
			if(file_exists(basename($filename).".zip"))	{
				readfile(basename($filename).".zip");
				exec("rm -fR $filename");
			}
			return "";
		}
		if(eregi("^includepdf (.*)($| delete)", $myparm, $regs) || eregi("^includecolorpdf (.*)($| delete)", $myparm, $regs)) {
			if(eregi("^includepdf ", $myparm)) $color="-colorspace GRAY -colors 2"; else $color="";
			if($regs[2]=="" && ereg(" delete$", $regs[1])) {
				$regs[2]="delete";
				$regs[1]=ereg_replace(" delete$", "", $regs[1]);
			}
			exec("rm -f temp*.png");
			exec("rm -f temp*.jpg");
			$tempname=tempnam("","pdfconv");
			$cmd="/usr/local/bin/convert";
			$ide="/usr/local/bin/identify";
			if(exec($cmd)=="") { $cmd="convert"; $ide="identify"; }
			//$identify=shell_exec("$ide \"$regs[1]\"");
			$identify=$this->my_exec("$ide \"$regs[1]\"", 30);
//			return "$ide \"$regs[1]\":".$identify.":identify";
			if(ereg("mb$",$identify) && ereg("PseudoClass", $identify)) { $scale="100"; $density=""; } else { $scale="75"; $density="-density 150 "; }
			$density="-density 200 "; $scale="50";
			if(ereg("PseudoClass 2c", $identify)) {
				//exec("$cmd -rotate \"-90 >\" ".$density."-trim -fuzz \"5%\" \"$regs[1]\" $tempname.jpg");
				//exec("$cmd -rotate \"-90 >\" $tempname*.jpg* -colorspace GRAY -colors 2 $tempname.png");
				$res=$this->my_exec("$cmd -rotate \"-90 >\" ".$density."-trim -fuzz \"5%\" \"$regs[1]\" $tempname.jpg", 30);
				if($res===false) return "Took longer than 30 seconds to process this file";
				$res=$this->my_exec("$cmd -rotate \"-90 >\" $tempname*.jpg* $color $tempname.png", 30);
				if($res===false) return "Took longer than 30 seconds to process this file";
			} else {
				$res=$this->my_exec("$cmd -rotate \"-90 >\" ".$density."-trim -fuzz \"5%\" \"$regs[1]\" $color $tempname.png", 60);
				if($res===false) return "Took longer than 30 seconds to process this file";
//				exec("$cmd -rotate \"-90 >\" ".$density."-trim -fuzz \"5%\" \"$regs[1]\" -colorspace GRAY -colors 2 $tempname.png");
			}
			if($regs[2]==" delete") exec("rm -f $regs[1]");
			$myfiles=array_merge(glob($tempname."*.png"),glob($tempname."*.png.*"));
			foreach($myfiles as $filename) { 
				ob_start();
				list($width, $height)=getimagesize($filename);
				$vertpos="{\sv 2}}{\sp{\sn posrelv}{\sv 1}";
				if(($width/$height)>(11520/15120)) { $height=ceil(15120/(($width/$height)/(11520/15120))); $width="11520"; }
				elseif(($width/$height)<(11520/15120)) { $width=ceil(11520*(($width/$height)/(11520/15120))); $height="15120"; }
				else { $height=15120; $width=11520; }
				if($height<"15120") $vertpos="{\sv 1}}{\sp{\sn posrelv}{\sv 0}";
				readfile($filename);
				$contents=ob_get_contents();
				ob_end_clean(); 
				$piccontents.=($piccontents>""?"\\page\r\n ":"").'{\shp{\*\shpinst\shpleft0\shptop0\shpright'.$width.'\shpbottom'.$height.'\shpfhdr0\shpbxpage\shpbxignore\shpbymargin\shpbyignore\shpwr3\shpwrk0\shpfblwtxt0\shpz4\shplockanchor\shplid1026
{\sp{\sn shapeType}{\sv 75}}{\sp{\sn fFlipH}{\sv 0}}{\sp{\sn fFlipV}{\sv 0}}{\sp{\sn pib}{\sv 
{\pict\picscalex'.$scale.'\picscaley'.$scale.'\piccropl0\piccropr0\piccropt0\piccropb0
\pngblip\bliptag1949255253{\*\blipuid 742f4655206b60de2a0988aba1ef24a0} '.chunk_split(bin2hex($contents),128)."}
}}{\sp{\sn fRecolorFillAsPicture}{\sv 0}}{\sp{\sn fUseShapeAnchor}{\sv 0}}{\sp{\sn fLine}{\sv 0}}{\sp{\sn posh}{\sv 2}}{\sp{\sn posrelh}{\sv 1}}{\sp{\sn posv}".$vertpos."}{\sp{\sn fBehindDocument}{\sv 0}}
{\sp{\sn fLayoutInCell}{\sv 1}}}}";
				unset($contents);
			}
			foreach(glob($tempname."*") as $onefile) unlink($onefile);
			return $piccontents;
		}
		if(eregi("^ifhurdman(.*)$", $myparm, $regs)) {
			if(ereg(",HURDMAN,",$this->params["keys"])) return $regs[1]; else return '';
		}
		if(eregi("^nothurdman(.*)$", $myparm, $regs)) {
			if(ereg(",HURDMAN,",$this->params["keys"])) return ''; else return $regs[1];
		}
		if(eregi("^validcc (.*)", $myparm, $regs)) {
			$myparam=$regs[1];
			return "case when ((case when length($myparam) between 15 and 16 then right($myparam,1) + if(mid($myparam,15-(length($myparam)=15),1)*2>9,mid($myparam,15-(length($myparam)=15),1)*2-9,mid($myparam,15-(length($myparam)=15),1)*2) + mid($myparam,14-(length($myparam)=15),1) + if(mid($myparam,13-(length($myparam)=15),1)*2>9,mid($myparam,13-(length($myparam)=15),1)*2-9,mid($myparam,13-(length($myparam)=15),1)*2) + mid($myparam,12-(length($myparam)=15),1) + if(mid($myparam,11-(length($myparam)=15),1)*2>9,mid($myparam,11-(length($myparam)=15),1)*2-9,mid($myparam,11-(length($myparam)=15),1)*2) + mid($myparam,10-(length($myparam)=15),1) + if(mid($myparam,9-(length($myparam)=15),1)*2>9,mid($myparam,9-(length($myparam)=15),1)*2-9,mid($myparam,9-(length($myparam)=15),1)*2) + mid($myparam,8-(length($myparam)=15),1) + if(mid($myparam,7-(length($myparam)=15),1)*2>9,mid($myparam,7-(length($myparam)=15),1)*2-9,mid($myparam,7-(length($myparam)=15),1)*2) + mid($myparam,6-(length($myparam)=15),1) + if(mid($myparam,5-(length($myparam)=15),1)*2>9,mid($myparam,5-(length($myparam)=15),1)*2-9,mid($myparam,5-(length($myparam)=15),1)*2) + mid($myparam,4-(length($myparam)=15),1) + if(mid($myparam,3-(length($myparam)=15),1)*2>9,mid($myparam,3-(length($myparam)=15),1)*2-9,mid($myparam,3-(length($myparam)=15),1)*2) + mid($myparam,2-(length($myparam)=15),1) + if(mid($myparam,1-(length($myparam)=15),1)*2>9,mid($myparam,1-(length($myparam)=15),1)*2-9,mid($myparam,1-(length($myparam)=15),1)*2) else -1 end) % 10)=0 then 1 else 0 end";
		}
		if(eregi("^addslashes (.*)", $myparm, $regs)) 
		{
			$myparm = $regs[1];
			return addslashes($this->params[$myparm]);
		}
		if(!(strpos("|" . $myparm, "|mydate ") === false))
		{
			$myparm = trim(str_replace("mydate ","",$myparm));
			if($this->params[$myparm]) {
				$myparm = $this->params[$myparm];
 }
			if(COUNTRY=='CANADA')
				return $myparm;
			else {
				$pieces=explode("/",$myparm);
				return $pieces[2]."/".$pieces[0]."/".$pieces[1];
			}
		}
		if(!(strpos("|" . $myparm, "|usdate ") === false))
		{
			$myparm=trim(str_replace("usdate ","",$myparm));
			if(COUNTRY=='CANADA')
				return $myparm;
			else
				return "date_format($myparm,'%m/%d/%Y')";
		}
		if(!(strpos("|" . $myparm, "|shadowval ") === false))
		{
			$myparm=trim(str_replace("shadowval ","",$myparm));
			return str_replace("-","",ereg_replace("[,'\.!@\#\$\^&\*\(\)_=\+ /;\\\"]","",eregi_replace(" and%$","%",eregi_replace(" and ","",eregi_replace("^([%]{0,1})the ","\\1",$this->params[$myparm])))));
		}
		if(!(strpos("|" . $myparm, "|shadow ") === false))
		{
			$myparm=trim(str_replace("shadow ","",$myparm));
			return "replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(if($myparm regexp concat(char(94),'the '),mid(lower($myparm),5),lower($myparm)),' and ',''),'.',''),'!',''),'@',''),'#',''),'$',''),'%',''),char(6),''),'&',''),'*',''),'(',''),')',''),'-',''),'_',''),'=',''),'+',''),' ',''),';',''),'\\'',''),char(34),''),'/',''),',','')";
		}
		if(!(strpos("|" . $myparm, "|stripped ") === false))
		{
			$myparm=trim(str_replace("stripped ","",$myparm));
			return "replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace($myparm,' and ',''),',',''),'.',''),\"'\",''),'!',''),'@',''),'#',''),'$',''),'%',''),char(6),''),'&',''),'*',''),'(',''),')',''),'-',''),'_',''),'=',''),'+',''),';',''),'\\'',''),char(34),''),'/','')";
		}
		if(isset($this->params[$myparm]))
			return $this->params[$myparm];
		else
			$this->orphan[] = $myparm;
		return false;
	}
	
	function subquery($string)
	{
		// in fall-back cases
		$original = $string;
		// Loops until it can't find any more ( ) pairs with no ( ) between them
		while(ereg("\( *([[:alpha:]]*)[^()]*\) *([[:alpha:]]*)", $string, $value)) {
				if(trim(strtolower($value[1]))!="select" || trim(strtolower($value[2])) != "as")
					// if there is no 'select' or 'as' before or after the ( ) pair, then replace the ( ) with &lp; and &rp;
				   $string = str_replace($value[0], str_replace(")","&rp;",str_replace("(","&lp;",$value[0])), $string);
				 else
					// looping ends if there is a 'select' and an 'as' before and after the ( ) pair
					break;
		}
		
		// Loops until it can't find any more subqueries in the format:   ( ... ) as ...
		while(ereg("\(([^()]*)\) *as ([[:alpha:]\|]*)", $string, $value))
		{
			// If 'select' is not at the beginning of the subquery found, then replace the ( ) with &lp; and &rp;
			if(!ereg("\( *select", $value[0]))
				$string = str_replace($value[0], str_replace(")","&rp;",str_replace("(","&lp;",$value[0])), $string);
			else
			{
				// Store the create table syntax for the subquery
				// $value[1] contains the select statement, and $value[2] contains the subquery table name
				$sub_query[] = "CREATE TABLE my" . $value[2] . "  " . str_replace("&lp;", "(", str_replace("&rp;", ")", $value[1]));
				$tables[] = "my" . $value[2];
				$string = str_replace($value[0], "my" . $value[2], $string);
			}
		}
		return array("num_sub" => sizeof($sub_query), "tables" => $tables, "query" => $string, "subquery" => $sub_query, "original" => $original);
	}

	function querygroup($query)
	{
	//	$sqlstatements = "||\r\n".$query."\r\n||";
		$i=0;
		$sqls = explode("\r\n||\r\n", $query);
		foreach($sqls as $thisquery) {
//		while($sqlstatement = $this->GetNextQuery($sqlstatements)) {
//			$thisquery=str_replace("\r\n","",str_replace("||","",$sqlstatement));
			$thisquery=str_replace("\r\n"," ",str_replace("||","",$thisquery));
//			$fp = fopen("/home/serv/temp/querygroup.log", "a");
//			fwrite($fp, "Database: $database Query called: $thisquery\r\n");
//			fclose($fp);
			if(eregi("^[(]{0,1}select",$thisquery) || eregi("^ *display",$thisquery)) {
				$selects[]=$thisquery;
				$i++;
			} else
				$updates[$i][]=$thisquery;
			$sqlstatements=str_replace($sqlstatement, "||", $sqlstatements);
		}
		return array("select" => $selects, "update" => $updates);
	}
	
	function GetNextQuery($myqueries) {
		$myqueries = str_replace("||","\xee",$myqueries);
		ereg("\xee[^\xee]+\xee", $myqueries, $returned);
		$returned[0] = str_replace("\xee","||",$returned[0]);
		return $returned[0];
	}
	

	function GetNextPipe($mytext)
	{
		ereg ("[^|](\|[^|]+\|)([^|]|$)",  $mytext, $returned);
//		ereg ("[^|](\|[[:alnum:] \)\(\#\.?\:_@,]+\|)([^|]|$)",  $mytext, $returned);
		return $returned[1];
	}

	function removealias($query) {
		$queries=explode(" union ",$query);
		foreach($queries as $query) {
			$query=str_replace("\r\n", " ", $query);
			if(!eregi("select(.*[^`])from",$query,$result))
				eregi("select(.*)$",$query,$result);
			$result = explode(", ",$result[1]);
			for($i=0;$i<sizeof($result);$i++) {
				$query = str_replace($result[$i],eregi_replace(" as .*"," ",$result[$i]),$query);
				list($dummy, $alias)=explode(" as ",$result[$i],2);
				$this->alias[]=stripcslashes($alias);
			}
			$retq[]=$query;
		}
		
		return implode(" union ",$retq);
	}

	function my_exec($cmd, $time_limit) {
		$descriptorspec = array(
			0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
			1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
			2 => array("pipe", "r") // stderr is a file to write to
		);
		$fp=proc_open($cmd, $descriptorspec, $pipes);
		for($i=0;$i<$time_limit;$i++) {
			$status=proc_get_status($fp);
			if(!$status["running"]) break;
			sleep(1);
		}
		if($status["running"]) {
			proc_terminate($fp, 9);
			return false;
		}
		while($str=fgets($pipes[1],1024)) $ret.=$str;
		$status=proc_get_status($fp);
		return $ret;
	}

	function getinfo($result) {
		$i=0;
		while($fld = mysql_fetch_field($result)) {
//		print_r($fld);

			$fld->help="";
			if($fld->table>"" && $fld->table!='masterlist' && !isset($tabledefs[$fld->table])) {
				unset($defs);
				if($r = $this->mybindr_query("desc ".$fld->table,"",false)) {
					while($row = mysql_fetch_row($r))
						$defs[$row[0]] = $row[1];
				}
				$tabledefs[$fld->table] = $defs;
			}
			if(ereg("^set\('(.*)'\)",$tabledefs[$fld->table][$fld->name], $regs)) {
				$regs = split("','", $regs[1]);
				$fld->set = $regs;
			}
			if($fld->table=="" && eregi("^#FILEUPLOAD#$",$fld->name, $regs)) {
				$fld->fileupload=true;
				$fld->name="FILEUPLOAD";
			}
			if($fld->table=="" && eregi("^#FILEUPLOAD([0-9]+)#$",$fld->name, $regs)) {
				$fld->fileupload=true;
				$fld->fileuploadmulti=$regs[1];
				$fld->name="FILEUPLOAD";
			}
			if($fld->table=="" && eregi("^(BC|C)ASS$",$fld->name, $regs)) {
				$fld->cass=true;
				$fld->triggersql="CASS";
			}
			if($fld->table=="" && eregi("^APPROVECC$",$fld->name, $regs)) {
				$fld->approvecc=true;
				$fld->triggersql="APPROVECC";
			}
			if($fld->table=="" && eregi("^#(.*):(.*)#$",$fld->name, $regs)) {
				if(ereg("Date",$regs[1])) { $fld->isdate=true; $fld->type="date"; }
				$fld->name=$regs[1];
				if(eregi("^select",$regs[2])) {
					$result2=$this->mybindr_query($regs[2]);
					list($regs[2])=mysql_fetch_row($result2);
				}
				if(ereg("^[*]MEMO[*]",$regs[2])) { $regs[2]=ereg_replace("^[*]MEMO[*]","",$regs[2]); $fld->blob=true; }
				$fld->overridevalue=$regs[2];
			}
			$fld->flags=mysql_field_flags($result, $i);
			$fld->fieldlen=mysql_field_len($result, $i);
			$fld->maxlen=($fld->blob?65536:mysql_field_len($result, $i));
			if($fld->fieldlen>255) $fld->fieldlen=2000;
			elseif($fld->fieldlen>50) $fld->fieldlen=50;
			if($this->alias[$i]) $fld->alias=$this->alias[$i]; else $fld->alias=$fld->name;
			//echo $fld->alias."\r\n";
			if(ereg("HidePassword",$fld->alias)) { $fld->password=true; $fld->alias=ereg_replace("HidePassword","Password",$fld->alias); }
			if(eregi("MAXLENGTH:([0-9]*)",$fld->alias,$regs)) {
				$fld->maxlen=$regs[1];
				$fld->alias = ereg_replace("MAXLENGTH:[0-9]*", "", $fld->alias);				
				$fld->lengthcounter=true;
			}
			if(eregi("VALIDATE:([^ ]+)",$fld->alias,$regs)) {
				$fld->validate=$regs[1];
				$fld->alias = ereg_replace("VALIDATE:([^ ]+)", "", $fld->alias);				
			}
			if($fld->alias!='`TITLE`' && $fld->alias!='`SUBTITLE`') {
				$fld->alias = ereg_replace("^ *'(.*)' *$", "\\1", $fld->alias);
				$fld->alias = ereg_replace("^ *`(.*)` *$", "\\1", $fld->alias);
			}
			if(COUNTRY=='CANADA') {
				$fld->alias = eregi_replace("(\!|^)zip([^a-z]|$)", "\\1PostalCode\\2", $fld->alias);
				$fld->alias = eregi_replace("(\!|^)state([^a-z]|$)", "\\1Province\\2", $fld->alias);
			}
			if(ereg("SENDS3PUBLIC:([^ ]+)",$fld->alias, $fileregs)) $fld->sends3public=$fileregs[1];
			$fld->alias=ereg_replace("SENDS3PUBLIC:([^ ]+) ", "", $fld->alias);
			if(ereg("ATTACHFILE( |:)([^ ]+)",$fld->alias, $fileregs)) $fld->isfile=true;
			if($fileregs[1]==":") $fld->browsefolder=$fileregs[2];
			if(trim($fld->name)=="" && $fld->alias!="!") $fld->name=$fld->alias;
			if($fld->name=="" && $fld->alias!="!") $fld->name=$fld->alias;
			if(eregi("^select",$fld->alias)) {
				$resultalias = $this->mybindr_query($fld->alias);
				$row=mysql_fetch_row($resultalias);
				$fld->alias=$row[0];
			}
			$fld->isprotectcol = ereg("^_", $fld->alias);
			if($fld->isprotectcol) $fld->blob=false;
			$fld->hidden = (ereg("^\*", $fld->alias) || ereg("^\&", $fld->alias) || $fld->alias=="bid" || $fld->alias=="cid");
			if((($fld->type=="int") && (ereg("^([0-9]*)%.*[$]$",$fld->alias, $regs)))) {
				$fld->taxamt = $regs[1];
			}
			$fld->creditcard = ereg("CardNumber", $fld->name);
			$fld->istotal = (($fld->type=="int") && (ereg("^[$]",$fld->alias)));
			$fld->tax = 0;
			$fld->isdate = ($fld->type=="date");
			if($fld->isdate) $fld->fieldlen=10;
			$fld->isdatecan = $fld->isdate && ereg("dd/mm/yyyy",$fld->alias);
			$fld->isdatetime = ($fld->type=="datetime");
			$fld->istime = ($fld->type=="time");
			$fld->ismoney = (($fld->type=="int") && (ereg("[$]$",$fld->alias)));
			$fld->ishtml = ereg("HTMLAREA",$fld->alias);
			$fld->scrub = ereg("SCRUB",$fld->alias);
			$fld->alias = ereg_replace("HTMLAREA","",$fld->alias);
			$fld->formatnumber = (($fld->type=="int") && (ereg("FORMATNUMBER",$fld->alias)));
			$fld->isphone = ereg("\#$",$fld->alias);
			$fld->isyesno = ereg("[^?]\?$", $fld->alias) || ereg("YESNO", $fld->alias);
			$fld->oneline = ereg("ONELINE", $fld->alias);
			$fld->isyesnopend = ereg("\?\?$", $fld->alias);
			$fld->alias=ereg_replace("\?\?$", "?", $fld->alias);
			$fld->isonly = ereg("^!{0,1}\?", $fld->alias);
			ini_set("display_errors","0");
			if($field[sizeof($field)-1]->name && @ereg("confirm".$field[sizeof($field)-1]->name,$fld->name)) $fld->confirm=true;
			$fld->isreqd = ereg("^!",$fld->alias);
			$fld->isreqdnum = ereg("^!#",$fld->alias);
			unset($regs);
			ereg("^[*]{0,1}%([^%]*)%",$fld->alias, $regs);
			$fld->reqdcond = $regs[1];
			$fld->isneed = ereg("!$",$fld->alias);
			if(ereg(".*_([[:alnum:]]*).*$",$fld->alias,$regs)) {
				list($fld->query, $fld->querydesc) = $this->getquery("!".$regs[1]);
				$fld->dropdownname=$regs[1];
				$fld->alias=ereg_replace("^(.*)_(.*)$", "\\1\\2", $fld->alias);
				if($fld->query=="COLORPICKER") $fld->ColorPicker=true;
			} else {
				list($fld->query, $fld->querydesc) = $this->getquery("!".ereg_replace("(^!)|(\")","",$fld->alias));
				$fld->dropdownname=ereg_replace("(^!)|(\")","",$fld->alias);
			}
			if(ereg("^PSEUDOSET($| NOUNCHECK$)",$fld->querydesc, $pseudoregs)) {
				$fld->pseudoset = true;
				$fld->pseudosetlocked=($pseudoregs[1]==" NOUNCHECK");
				$query = $this->ResolvePipes($fld->query);
				$presult = $this->mybindr_query($query, $this->database , false);
				$numrows=mysql_num_rows($presult);
				while($psetrow = mysql_fetch_row($presult)) $fld->set[]=$psetrow[0];
				if(sizeof($fld->set)==0) $fld->pseudoset=false;
			}
			if($fld->query>'' && ereg("\|SEARCH\|",$fld->query)) {
				$fld->searchdrop=str_replace("\r\n||\r\n","o0O0o",$fld->query);
			}
			if(eregi("doc files (bid|cid)", $fld->query, $regs)) {
				unset($fld->query);
				$temparr=$this->docslist($regs[1], (isset($this->params[$regs[1]])?$this->params[$regs[1]]:$this->params["value"]));
				$fld->query[]=array("","--NONE--");
				if(is_array($temparr)) foreach($temparr as $filename) $fld->query[]=array($filename, $filename);
			}
			if(ereg("^enum\('(.*)'\)",$tabledefs[$fld->table][$fld->name], $regs)) {
				$regs = split("','", $regs[1]);
				$fld->query = $regs;
				//print_r($fld->query);
			}
			$fld->defaultval = "";
			if($fld->table>'') { // && $_GET["test"]=="help") {
				$resulthelp = $this->mybindr_query("select description, help from helpfield where find_in_set(\"$fld->table.$fld->name\", replace(name, \" \", \"\")) limit 1");
				if(mysql_num_rows($resulthelp)<1)
					$resulthelp = $this->mybindr_query("select description, help from common.helpfield where find_in_set(\"$fld->table.$fld->name\", replace(name, \" \", \"\")) limit 1");
				$row = mysql_fetch_row($resulthelp);	
				$fld->help = str_replace("\r\n",'',$row[0]);
				$fld->extrahelp = ereg_replace("'","&#39;",str_replace("\r\n",'',$row[1]));
			} 
			list($fld->triggersql,) = $this->getquery("trigger ".ereg_replace("\"","",$fld->name));
			if($fld->triggersql!="") {
				$fld->triggername=ereg_replace("\"","",$fld->name);
			}
			if(ereg("TRIGGER ([a-zA-Z0-9]+)", $fld->alias, $regs)) {
				list($fld->triggersql,) = $this->getquery("trigger ".$regs[1]);
				$fld->alias=ereg_replace("TRIGGER ([a-zA-Z0-9]+)", "", $fld->alias);
				$fld->triggername=$regs[1];
			}
			if(eregi("NOTRIGGER",$fld->alias)) $fld->triggersql='';
			$fld->alias = ereg_replace("^!#{0,1}(.*)", "\\1", $fld->alias);
			$fld->alias = ereg_replace("^(\?)(.*)", "\\2\\1", $fld->alias);
			$fld->alias = ereg_replace("^[*]{0,1}%[^%]*%", "", $fld->alias);
//			$fld->alias = ereg_replace("(.{3,})<!--.+-->", "\\1", $fld->alias);
			$fld->name = ereg_replace("^\*(.*)", "\\1", $fld->name);
			if($fld->query>'') $fld->isyesno=false;
			$field[]=$fld;
			$i++;
		}
		return $field;
	}

	function gettables($query) {
		eregi(" from (.*) where", $query, $regs);
		while(eregi(" from (.*)$", $regs[1], $regs)) {
		}
		return $regs[1];
	}
	
	function cleanvals($field, $old="") {
		if($field->ismoney || $field->istotal)
			return ereg_replace("[^0-9.-]", "",$_POST[$field->name.$old]);
		elseif($field->formatnumber) return ereg_replace("[^0-9]", "",$_POST[$field->name.$old]);
		elseif($field->isdate || $field->isdatetime)
			if(trim($_POST[$field->name.$old])=="")
				return "null";
			else {
				if(!ereg('([[:alnum:]]*)[^[:alnum:]]+([[:alnum:]]*)[^[:alnum:]]+([[:alnum:]]*)(.*)', $_POST[$field->name.$old], $regs))
					$regs = array($_POST[$field->name.$old], substr($_POST[$field->name.$old],0,2), substr($_POST[$field->name.$old],2,2), substr($_POST[$field->name.$old],4,4),substr($_POST[$field->name.$old],8));
				//if(strlen($regs[3])==2) $regs[3]=substr(date("Y"),0,2).$regs[3];
				if(!checkdate($regs[1], $regs[2], $regs[3])) { $temp=$regs[2]; $regs[2]=$regs[1]; $regs[1]=$temp; }
				if(!checkdate($regs[1], $regs[2], $regs[3])) return 'null';
				else {
					if($field->isdatecan) return $regs[3]."-".$regs[2]."-".$regs[1].($field->isdatetime?date(" H-i-s", strtotime($regs[4])):"");
					else return $regs[3]."-".$regs[1]."-".$regs[2].($field->isdatetime?date(" H-i-s", strtotime($regs[4])):"");
				}
			}
//				return date("Y-m-d", strtotime($_POST[$field->name]));
		elseif($field->isphone && ereg("^[+]",$_POST[$field->name.$old]))
			return $_POST[$field->name.$old];
		elseif($field->isphone)
			return ereg_replace("[^0-9]", "",$_POST[$field->name.$old]);
		else
			return preg_replace(array("/\xC3\x89/", "/\xC3\xA9/", "/\xC3\xA0/", "/\xC3\xA7/", "/\xC3\xAA/", "/\xC3\xAB/", "/\xC3\xA8/", "/\xC3\xAF/", "/\xC2\xAE/", "/\xC3\xAE/", "/\xC3\xA6/", "/\xC3\xB4/", "/\xC3\xBB/", "/\xC3\xBC/"), array("\xC9", "\xE9", "\xE0", "\xE7", "\xEA", "\xEB", "\xE8", "\xEF", "\xAE", "\xEE", "\xE6", "\xF4", "\xFB", "\xFC"), $_POST[$field->name.$old]);
//			return $_POST[$field->name];
	}
	
	function addparm($key, $val) {

// Removed below on 5/6/2014 as backslashes do not work in HTML code
//		if ($key == "value") {
//			$val = addslashes($val);
//		}

		$this->params[$key]=$val;
	}
	
	function LoadData($tablename, $fieldnames, $filename, $ignorelines=1, $delimiter='|', $enclosure='"', $fixedwidth=false) {			
			global $titlecasefields, $autotable, $redirect, $phplinecode, $namefields, $logquery, $dieonerror;
			if(!($fp = fopen($filename, 'r'))) return false;
			$this->addparm("IMPORTR_FILENAME", $filename);
			$this->addparm("staff", $_COOKIE[reportr_username]);
			if($fixedwidth) $delimiter=chr(0);
			if($autotable) {
				$fieldnames=fgetcsv($fp,32000,($delimiter!=''?$delimiter:','));
				$tablename="load_".strtolower(eregi_replace("[^a-z0-9]","",ereg_replace("[.].+","",$_FILES["attachment"]["name"])));
				$origtablename=$tablename;
				while(mysql_num_rows($this->mybindr_query("show tables like '$tablename'", $this->database, true))>0) $tablename=$origtablename."_".(++$tablecount);
				$i=1;
				foreach($fieldnames as $fieldname) {
					if($fieldname=="") $fieldname="FIELD_$i";
					$fieldcount=0;
					$fieldname=strtolower(eregi_replace("[^a-z0-9]", "", $fieldname));
					$origfieldname=$fieldname;
					while(isset($myfields[$fieldname])) $fieldname=$origfieldname.(++$fieldcount);
					$myfields[$fieldname]=$i++;
					$fields[]="`$fieldname` text not null";
				}
				$fieldnames=$myfields;
				$this->mybindr_query("create table $tablename (".implode(", ", $fields).") comment='eBINDr:".addslashes($_FILES["attachment"]["name"])."'", $this->database, true);
			}
			if($this->loaddata) {
				fclose($fp);
				exec("chmod 777 $filename");
				exec("chown mysql:mysql $filename");
			} else {
				foreach($fieldnames as $name => $pos) {
					if(!ereg("^IGNORE", $name)) $fieldlist.="`".$name."`,";
					$poses[]=$pos;
				}	
				$ignored=0;
				$namedfields=false;
				while($fields=($fixedwidth?fgets($fp, 32000):fgetcsv($fp,32000,($delimiter!=''?$delimiter:',')))) { //,($enclosure!=''?$enclosure:'"'))) {
						if($namedfields!==false) {
							$i=0;
							foreach($namedfields as $field) $fields[$field]=$fields[$i++];
						}
						if($phplinecode!==false) eval($phplinecode);
						if($fixedwidth) {
							$myline=str_replace("\r","",str_replace("\n","",$fields)); unset($fields);
							$i=0;
							foreach($poses as $pos) { $fields[]=substr($myline, $i, $pos); $i+=$pos; }
						}
						if($ignored==0 && $ignorelines>0 && $namefields) $namedfields=$fields;
						if($ignored++>=$ignorelines) {
								$i=0;
								foreach($fieldnames as $name => $pos) {
									if($fixedwidth && !ereg("^IGNORE", $name)) {
										if($titlecasefields && strlen(trim($fields[$i]))>3) $values.="'".ucwords(strtolower(addslashes($fields[$i])))."',";
										else $values.="'".addslashes($fields[$i])."',";
										$i++;
									} elseif($namefields && !ereg("^IGNORE", $name)) {
										if($titlecasefields && strlen(trim($fields[$pos]))>3) $values.="'".ucwords(strtolower(addslashes($fields[$pos])))."',";
										else $values.="'".addslashes($fields[$pos])."',";
										$i++;
									} elseif(!ereg("^IGNORE", $name)) {
										if($titlecasefields && strlen(trim($fields[$pos-1]))>3) $values.="'".ucwords(strtolower(addslashes($fields[$pos-1])))."',";
										else $values.="'".addslashes($fields[$pos-1])."',";
										$i++;
									}
								}
								$query = "insert ignore into `$tablename` (".substr($fieldlist,0,strlen($fieldlist)-1).
										") values (".substr($values,0,strlen($values)-1).")";
								$this->mybindr_query($query, $this->database, $dieonerror, $logquery);
		//						echo $query."\r\n";
								$values="";
						}
				}
			}
			if($autotable) {
				foreach($fieldnames as $name => $pos) {
					if($key=="") $key=$name;
					list($len)=mysql_fetch_row($this->mybindr_query("select max(length(`$name`)) from $tablename", $this->database, true));
					$len=ceil($len/10)*10;
					$num=mysql_num_rows($this->mybindr_query("select * from $tablename where `$name`!='' and `$name` regexp '[^0-9]' limit 1", $this->database, true));
					$nondate=mysql_num_rows($this->mybindr_query("select * from $tablename where `$name`!='' and mydate(`$name`) is null limit 1", $this->database, true));
					if($num==0) $type="bigint not null"; 
					elseif($nondate==0) {
						$type="datetime"; 
						$this->mybindr_query("update $tablename set `$name`=mydate(`$name`)", $this->database, true);
					} else $type="char($len) not null";
					if($len<256) $this->mybindr_query("alter table $tablename change column `$name` `$name` $type", $this->database, true);
				}
				$this->mybindr_query("alter table $tablename add key (`$key`)", $this->database, true);
				if($redirect!="") {
					$redirect.="?NOPROMPTtablename=$tablename&NOPROMPTfile=".urlencode($_FILES["attachment"]["name"]);
					if(isset($_GET["ebindr2"])) $redirect=ereg_replace("[?]","?ebindr2=y&",$redirect);
					header("Location: $redirect");
					exit();
				}
			}

	}
	

	function RTFGraph($query) {
		global $reportr, $parse;
		$elements=explode("\r\n||\r\n", $query);
		$i=0;
		foreach($elements as $onequery)
			if(!eregi("^graph", $onequery, $regs)) { $i++; $reportr->background->query($parse->resolve($onequery)); } else break;
		$options = explode(",", eregi_replace("graph,", "", $elements[$i]), 4);
		$data=$reportr->results($parse->resolve($elements[$i+1]));
		list($title, $yaxis, $xaxis) = explode(":",str_replace("\r\n"," ",$options[3]),3);

		if(sizeof($data)>0) $offset=1; else $offset=0;
		if(sizeof($reportr->col_info)==2) {
			foreach($data as $onerow) {
				$j=0;
				foreach($onerow as $key=>$val) {
					if($j) $x2[] = $val; else $x[]=$val;
					$j++;
				}
			}
			$a[] = $x;
			$a[] = $x2;
			$legend=array();
			$drawsets=array("1");//++$ii;
		} else {
			for($i=$offset;$i<sizeof($reportr->col_info);$i++) {
				if(!($excludetotal && eregi("total",$reportr->col_info[$i]->name))) $x[]=$reportr->col_info[$i]->name;
			}
			$a[] = $x;
			foreach($data as $onerow) {
				$i=0; unset($row);
				foreach($onerow as $key=>$value) {
					if(!($excludetotal && eregi("total",$key))) {
						if(++$i>$offset) $row[]=$value; else  { $legend[]=$value; $drawsets[]=++$ii; }
					}
				}
				$a[]=$row;
				if($excludetotal && sizeof($a)>4) break;
			}
		}
		
		phpplot(array(
			"box_showbox"=> true,
			"grid"=> true,
			"cubic"=> true,
			"title_text"=> $title,
			"yaxis_labeltext"=> trim($yaxis),
			"xaxis_labeltext"=> trim($xaxis),
			"legend_shift"=> array(-450*(phpversion()<'4.3.0'?2:1),0),
			"size"=> array($options[1]*(phpversion()<'4.3.0'?2:1),$options[2]*(phpversion()<'4.3.0'?2:1)) ));
		
		phpdata($a);

		switch($options[0]) {
			case "v":$graphtype="bargraph";break;
			case "vl":$graphtype="linepoints";break;
			case "h":$graphtype="bargraph2";break;
			case "p":$graphtype="piegraph";break;
			case "a":$graphtype="areagraph";break;
			default: $graphtype="bargraph";
		}

		phpdraw($graphtype,array(
			"drawsets" => $drawsets,
			"legend"   => $legend,
			"barspacing"=> 8,
			"showvalue"=> false ));
		$fname=tempnam("", "");
		phpshow($fname);
//		ini_set("display_errors", "1");
	//	error_reporting(E_ALL);
		ob_start();
		readfile($fname);
		$contents=ob_get_contents();
		ob_end_clean(); 
		if(phpversion()<'4.3.0') 
			$contents='{\pict\picscalex50\picscaley50\piccropl0\piccropr0\piccropt0\piccropb0
\pngblip\bliptag1949255253{\*\blipuid 742f4655206b60de2a0988aba1ef24a0} '.chunk_split(bin2hex($contents),128)."}";
		else
			$contents='{\pict\picscalex100\picscaley100\piccropl0\piccropr0\piccropt0\piccropb0
\pngblip\bliptag1949255253{\*\blipuid 742f4655206b60de2a0988aba1ef24a0} '.chunk_split(bin2hex($contents),128)."}";
		@copy($fname, "./test.png");
		@unlink($fname);
		return $contents;
	}
	
	function docsdir($bidcid, $value) {
		if($value=="" || $bidcid=="") return;
		$filecid=$value;
		if(strlen($filecid)<3) $filecid.="XX";
		$directory = DOCS_BASE_DIR.'/'.strtolower($bidcid).'/'.substr($filecid,strlen($filecid)-2).'/'.substr($filecid,0,strlen($filecid)-2);
		return $directory;
	}
	
	function docslist($bidcid, $value) {
		if($value=="" || $bidcid=="") return;
		$filecid=$value;
		if(strlen($filecid)<3) $filecid.="XX";
		$directory = DOCS_BASE_DIR.'/'.strtolower($bidcid).'/'.substr($filecid,strlen($filecid)-2).'/'.substr($filecid,0,strlen($filecid)-2);
		if($d = @opendir($directory)) {
			while(false !== ($filename = readdir($d)))
				if ($filename != "." && $filename != ".." && $filename!='trash') $filelist[]=$filename;
		}

		return $filelist;
	}
	function PreauthCC($cardnumber, $exp, $amount, $transactionid=false) {
		$DEBUGGING					= 1;				# Display additional information to track down problems
		$TESTING					= 1;				# Set the testing flag so that transactions are not live
		$ERROR_RETRIES				= 2;				# Number of transactions to post if soft errors occur
		
		$auth_net_login_id			= AUTHORIZE_NET_LOGIN_ID;
		$auth_net_tran_key			= AUTHORIZE_NET_TRANSACTION_KEY;
		$auth_net_url				= AUTHORIZE_NET_URL;
		#  Uncomment the line ABOVE for shopping cart test accounts or BELOW for live merchant accounts
		#  $auth_net_url				= "https://secure.authorize.net/gateway/transact.dll";
		$authnet_values	= array(
			"x_login"				=> $auth_net_login_id,
			"x_version"				=> "3.1",
			"x_delim_char"			=> "|",
			"x_delim_data"			=> "TRUE",
			"x_url"					=> "FALSE",
			"x_type"				=> ($transactionid?"PRIOR_AUTH_CAPTURE":"AUTH_ONLY"),
			"x_trans_id"			=> ($transactionid?$transactionid:""),
			"x_method"				=> "CC",
			"x_tran_key"			=> $auth_net_tran_key,
			"x_relay_response"		=> "FALSE",
			"x_card_num"			=> $cardnumber,
			"x_exp_date"			=> $exp,
			"x_description"			=> "",
			"x_amount"				=> $amount);
		
		$fields = "";
		foreach($authnet_values as $key => $value ) $fields .= "$key=" . urlencode( $value ) . "&";
		
		$ch = curl_init(AUTHORIZE_NET_URL); // URL of gateway for cURL to post to
		curl_setopt($ch, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
		curl_setopt($ch, CURLOPT_POSTFIELDS, rtrim( $fields, "& " )); // use HTTP POST to send form data
		### curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment this line if you get no gateway response. ###
		$resp = curl_exec($ch); //execute post and get results
		curl_close ($ch);
		return explode("|", $resp);
	
	}
	
	function ProcessCC($cardnumber, $cvv2, $exp, $amount, $refund="n", $transactionid=false, $BID=0) {
		$DEBUGGING					= 1;				# Display additional information to track down problems
		$TESTING					= 1;				# Set the testing flag so that transactions are not live
		$ERROR_RETRIES				= 2;				# Number of transactions to post if soft errors occur
		
		$auth_net_login_id			= AUTHORIZE_NET_LOGIN_ID;
		$auth_net_tran_key			= AUTHORIZE_NET_TRANSACTION_KEY;
		$auth_net_url				= AUTHORIZE_NET_URL;
		#  Uncomment the line ABOVE for shopping cart test accounts or BELOW for live merchant accounts
		#  $auth_net_url				= "https://secure.authorize.net/gateway/transact.dll";
		if($BID>0) {
			list($firstname, $lastname) = mysql_fetch_row($this->mybindr_query("select firstname, lastname from person where bid=$BID order by billing desc, main desc limit 1", "", true));
			list($company) = mysql_fetch_row($this->mybindr_query("select name from dba where bid=$BID order by main desc limit 1", "", true));
			list($address, $city, $state, $zip) = mysql_fetch_row($this->mybindr_query("select trim(concat(street1,' ',street2)), city, stateprov, postalcode from address where bid=$BID order by billing desc, mailing desc, main desc limit 1", "", true));
			
			$authnet_values	= array(
				"x_login"				=> $auth_net_login_id,
				"x_version"				=> "3.1",
				"x_delim_char"			=> "|",
				"x_delim_data"			=> "TRUE",
				"x_url"					=> "FALSE",
				"x_type"				=> ($refund=="y"?"CREDIT":"AUTH_CAPTURE"),
				"x_trans_id"			=> ($refund=="y"?$transactionid:""),
				"x_method"				=> "CC",
				"x_tran_key"			=> $auth_net_tran_key,
				"x_relay_response"		=> "FALSE",
				"x_card_num"			=> $cardnumber,
				"x_card_code"			=> $cvv2,
				"x_exp_date"			=> $exp,
				"x_description"			=> "",
				"x_amount"				=> $amount,
				"x_first_name" => $firstname,
				"x_last_name" => $lastname,
				"x_company" => $company,
				"x_address" => $address,
				"x_city" => $city,
				"x_state" => $state,
				"x_zip" => $zip,
				"x_cust_id" => $BID);
		} else $authnet_values	= array(
			"x_login"				=> $auth_net_login_id,
			"x_version"				=> "3.1",
			"x_delim_char"			=> "|",
			"x_delim_data"			=> "TRUE",
			"x_url"					=> "FALSE",
			"x_type"				=> ($refund=="y"?"CREDIT":"AUTH_CAPTURE"),
			"x_trans_id"			=> ($refund=="y"?$transactionid:""),
			"x_method"				=> "CC",
			"x_tran_key"			=> $auth_net_tran_key,
			"x_relay_response"		=> "FALSE",
			"x_card_num"			=> $cardnumber,
			"x_card_code"			=> $cvv2,
			"x_exp_date"			=> $exp,
			"x_description"			=> "",
			"x_amount"				=> $amount);
		
		$fields = "";
		foreach( $authnet_values as $key => $value ) $fields .= "$key=" . urlencode( $value ) . "&";
		
		$ch = curl_init(AUTHORIZE_NET_URL); // URL of gateway for cURL to post to
		curl_setopt($ch, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
		curl_setopt($ch, CURLOPT_POSTFIELDS, rtrim( $fields, "& " )); // use HTTP POST to send form data
		### curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment this line if you get no gateway response. ###
		$resp = curl_exec($ch); //execute post and get results
		curl_close ($ch);
		return explode("|", $resp);
	}
	function SendEmailPear($to,$contact,$from,$sender,$cc,$bcc,$subject,$message,$ishtml=false,$replyto=null) {
		if(ereg("DONT SEND THIS EMAIL",$message) || ereg("Couldn't resolve host",$message)) return false;
		$newto = trim(str_replace(".","",$contact))." <$to>";
		require_once "Mail.php";
		$smtp = Mail::factory('smtp', array ('host' => ereg_replace(":.*$", "", PEAR_EMAIL_HOST), 'auth' => true, 'username' => PEAR_EMAIL_USERNAME, 'password' => PEAR_EMAIL_PASSWORD, 
		'port'=>(ereg(":(.+)$", PEAR_EMAIL_HOST, $regs)?$regs[1]:465)));

		$headers = array (
			'From' => "$sender <$from>", 
			'To' => $newto, 
			'Subject' => $subject, 
			'Reply-To'=> ( !is_null($replyto) ? $replyto : ($sender.' <'.$from.'>') ), 
			'Content-Type'=>($ishtml?"text/html":"text/plain")
		);

		$sendarray=array($newto);
		if($cc>'') { $headers["Cc"]=$cc; $sendarray[]=$cc; }
		if($bcc>'') { $headers["Bcc"]=$bcc; $sendarray[]=$bcc; }
		$mail = $smtp->send($sendarray, $headers, $message);

		if (PEAR::isError($mail)) {
				echo("<p>" . $mail->getMessage() . "</p>");
				$this->params["EMAIL_ERROR"]=$mail->getMessage();
				return false; //echo("<p>" . $mail->getMessage() . "</p>");
		} else {
				return true; //echo("<p>Message successfully sent!</p>");
		}
	}	

	function SendEmailPearAttach($to,$contact,$from,$sender,$cc,$bcc,$subject,$message,$attachmentname="",$attachmentfile="") {
//	ini_set("display_errors","1");
		if(ereg("DONT SEND THIS EMAIL",$message) || ereg("Couldn't resolve host",$message)) return false;
		$newto = trim(str_replace(".","",$contact))." <$to>";
		require_once "Mail.php";
        $crlf = "\n";

		$headers = array ('From' => "$sender <$from>", 'Return-Path'   => $sender, 'To' => $newto, 'Subject' => $subject, 'Reply-To'=>($sender.' <'.$from.'>'));
		
		if($attachmentname!="") {
			require_once "Mail/mime.php";
	
			$mime = new Mail_mime($crlf);
	 
			$mime->setTXTBody(stripslashes($message));
			$mime->setHTMLBody($message);
	 
			$mime->addAttachment (file_get_contents($attachmentfile), "application/octet-stream", $attachmentname, 0);  // Add the attachment to the email
			//$mime->addAttachment ($attachmentfile, "application/octet-stream");  // Add the attachment to the email
	 
			$body = $mime->get();
			$headers = $mime->headers($headers);
		}
		$smtp = Mail::factory('smtp', array ('host' => PEAR_EMAIL_HOST, 'auth' => true, 'username' => PEAR_EMAIL_USERNAME, 'password' => PEAR_EMAIL_PASSWORD, 'port'=>465));

		$sendarray=array($newto);
		if($cc>'') { $headers["Cc"]=$cc; $sendarray[]=$cc; }
		if($bcc>'') { $headers["Bcc"]=$bcc; $sendarray[]=$bcc; }
		$mail = $smtp->send($sendarray, $headers, $body);

		if (PEAR::isError($mail)) {
				echo("<p>" . $mail->getMessage() . "</p>");
				return false; //echo("<p>" . $mail->getMessage() . "</p>");
		} else {
			echo "Success!!!";
				return true; //echo("<p>Message successfully sent!</p>");
		}
	}	

	function SendEmailPearAttachment($to,$contact,$from,$sender,$cc,$bcc,$subject,$message,$attachmentname, $attachmentcontent, $attachmentcontenttype) {
		if(ereg("DONT SEND THIS EMAIL",$message) || ereg("Couldn't resolve host",$message)) return false;
		$newto = trim(str_replace(".","",$contact))." <$to>";
		require_once "Mail.php";
		require_once "Mail/mime.php";
        $crlf = "\n";
		$headers = array ('From' => "$sender <$from>", 'Return-Path'   => $sender, 'To' => $newto, 'Subject' => $subject, 'Reply-To'=>($sender.' <'.$from.'>'));
 
        $mime = new Mail_mime($crlf);
 
        $mime->setTXTBody(stripslashes($message));
        $mime->setHTMLBody($message);
 
        $mime->addAttachment ($attachmentcontent, $attachmentcontenttype, $attachmentname, 0);  // Add the attachment to the email
 
        $body = $mime->get();
        $headers = $mime->headers($headers);

		$smtp = Mail::factory('smtp', array ('host' => PEAR_EMAIL_HOST, 'auth' => true, 'username' => PEAR_EMAIL_USERNAME, 'password' => PEAR_EMAIL_PASSWORD, 'port'=>465));

		$sendarray=array($newto);
		if($cc>'') { $headers["Cc"]=$cc; $sendarray[]=$cc; }
		if($bcc>'') { $headers["Bcc"]=$bcc; $sendarray[]=$bcc; }
		$mail = $smtp->send($sendarray, $headers, $body);

		if (PEAR::isError($mail)) {
				return false; //echo("<p>" . $mail->getMessage() . "</p>");
		} else {
				return true; //echo("<p>Message successfully sent!</p>");
		}
	}	

	function SendEmail($to,$contact,$from,$sender,$cc,$bcc,$subject,$message,$ishtml=false) {
		if(ereg("DONT SEND THIS EMAIL",$message) || ereg("Couldn't resolve host",$message)) return false;
		if(ereg("^MIME-Version", $message)) {
			$headers  = "To: \"$contact\" <$to>".($cc>"" ?"\nCc: $cc" : "").($bcc>"" ?"\nBcc: $bcc" : "")."\nFrom: $sender <$from>\nReply-To: $from\nX-Mailer: PHP/".phpversion()."\n".str_replace("\r\n","\n",$message);
			$message="";
		} else
			$headers  = "MIME-Version: 1.0\nContent-type: ".($ishtml?"text/html":"plain/text")."; charset=iso-8859-1\nTo: \"$contact\" <$to>".($cc>"" ?"\nCc: $cc" : "").($bcc>"" ?"\nBcc: $bcc" : "")."\nFrom: $sender <$from>\nReply-To: $from\nX-Mailer: PHP/".phpversion()."\n";
		return mail(null, $subject, str_replace("\r\n","\n",$message), $headers);

		require_once "Mail.php";
	
		$to = "$contact <$to>";
	
		$host = "mail.hurdmanivr.com";
		$username = "donotreply@bbbmemberpages.org";
		$password = "f8u34lw9";
	
		$headers = array ('From' => "BBB Member Page User <donotreply@bbbmemberpages.org>", 'To' => $to, 'Subject' => $subject, 'Reply-To'=>($sender.' <'.$from.'>'), 'Cc'=>$cc, 'Content-Type'=>($ishtml?"text/html":"plain/text"));
		$smtp = Mail::factory('smtp', array ('host' => $host, 'auth' => true, 'username' => $username, 'password' => $password));
	
		$mail = $smtp->send(array($to,$cc,$bcc), $headers, $message);
	
		if (PEAR::isError($mail)) {
			return false; //echo("<p>" . $mail->getMessage() . "</p>");
		} else {
			return true; //echo("<p>Message successfully sent!</p>");
		}
	}
	function SetDocTable($bidcid, $value) {
		if($bidcid!="cid" && $bidcid!="bid") return;
		if($bidcid=="cid") $tablename="complaintdoc"; else $tablename="businessdoc";
		$sql = "delete from $tablename where $bidcid=$value";
		$this->mybindr_query($sql);
		$stack[] = DOCS_BASE_DIR.'/'.$bidcid.'/'.substr($value,strlen($value)-2,2).'/'.substr($value,0,strlen($value)-2);
		while ($stack) {
			$current_dir = array_pop($stack);
			if ($dh = @opendir($current_dir)) {
				while (($file = readdir($dh)) !== false) {
					if ($file !== '.' AND $file !== '..') {
						$current_file = "{$current_dir}/{$file}";
						if (is_file($current_file)) {
							if(ereg("^.*/docs/$bidcid/([0-9X]+)/([0-9]+)/trash/.*$", $current_file)) continue;
							if(ereg("^.*/docs/$bidcid/([0-9X]+)/([0-9]+)/(.+)$", $current_file, $regs)) {
								if($regs[1]=="XX") $regs[1]="";
								if($bidcid=="cid")
									$sql = "insert into $tablename (bid,cid,filename) values(null, ".$regs[2].$regs[1].", '".addslashes($regs[3])."')";
								else
									$sql = "insert into $tablename (bid,filename) values(".$regs[2].$regs[1].", '".addslashes($regs[3])."')";
								$this->mybindr_query($sql);
							}
						} elseif (is_dir($current_file)) {
							$stack[] = $current_file;
						}
					}
				}
			}
		}
	}
	function sends3public($remotepath, $file ) {
//die($remotepath." ".$file);
		if(!$this->s3) {
			include_once "/home/serv/library/s3.php";
			$this->s3 = new comfort();
			$this->s3->bucket = $this->database."-bbb";
		}
		$headers = array();
		if(eregi("[.]([^.]+)$", $remotepath, $regs)) $ext=strtolower($regs[1]);
		switch ($ext) {
			case 'gif':
			case "jpeg":
			case "png":
			case 'jpg': 
				$headers['Cache-Control'] = 'max-age=604800';
				$headers['Content-Type'] = 'image/'.$ext; break;
			case 'pdf': 
				$headers['Content-Type'] = 'application/'.$ext; break;
		}
		if( $this->s3->putObject($file, $this->s3->bucket, $remotepath, 'public-read', array(), $headers ) ) {
				return true;
		}

		return false;
	}
	function copys3($src, $dest ) {
//die($remotepath." ".$file);
		if(!$this->s3) {
			include_once "/home/serv/library/s3.php";
			$this->s3 = new comfort();
			$this->s3->bucket = $this->database."-bbb";
		}
		$headers = array();
		if(eregi("[.]([^.]+)$", $dest, $regs)) $ext=strtolower($regs[1]);
		switch ($ext) {
			case 'gif':
			case "jpeg":
			case "png":
			case 'jpg': 
				$headers['Cache-Control'] = 'max-age=604800';
				$headers['Content-Type'] = 'image/'.$ext; break;
		}
//		$this->s3->deleteObject($this->s3->bucket, $dest);
		if( $this->s3->copyObject($this->s3->bucket, $src, $this->s3->bucket, $dest ) ) {
				return true;
		}
		return false;
	}
	function copys3public($src, $dest ) {
//die($remotepath." ".$file);
		if(!$this->s3) {
			include_once "/home/serv/library/s3.php";
			$this->s3 = new comfort();
			$this->s3->bucket = $this->database."-bbb";
		}
		$headers = array();
		if(eregi("[.]([^.]+)$", $dest, $regs)) $ext=strtolower($regs[1]);
		switch ($ext) {
			case 'gif':
			case "jpeg":
			case "png":
			case 'jpg': 
				$headers['Cache-Control'] = 'max-age=604800';
				$headers['Content-Type'] = 'image/'.$ext; break;
		}
		$this->s3->deleteObject($this->s3->bucket, $dest);
		if( $this->s3->copyObject($this->s3->bucket, $src, $this->s3->bucket, $dest, 'public-read', array(), $headers ) ) {
				return true;
		}
		return false;
	}
	function deletes3public($dest ) {
//die($remotepath." ".$file);
		if(!$this->s3) {
			include_once "/home/serv/library/s3.php";
			$this->s3 = new comfort();
			$this->s3->bucket = $this->database."-bbb";
		}
		$headers = array();
		if($this->s3->deleteObject($this->s3->bucket, $dest)) return true; else return false;
	}
	function downloads3($src, $dest) {
//die($remotepath." ".$file);
		if(!$this->s3) {
			include_once "/home/serv/library/s3.php";
			$this->s3 = new comfort();
			$this->s3->bucket = $this->database."-bbb";
		}
		$headers = array();
		if( $this->s3->retrieve($src, $dest, false) ) {
				return true;
		}
		return false;
	}
	function is_empty_dir($path) {
		if($d = @opendir($path)) {
			while(false !== ($filename = readdir($d))) {
				if(!in_array($filename, array(".","..","trash")) && is_dir($path."/".$filename)) {
					if(!$this->is_empty_dir($path."/".$filename)) {
						@closedir($d);
						return false;
					}
				} elseif(!in_array($filename, array(".","..","trash"))) {
					@closedir($d);
					return false;
				}
			}
		}
		@closedir($d);
		return true;
	}
	function ObjToMySQL($obj, $table=0) {
		if(!is_object($obj)) {
			$value=$obj;
			$obj=new stdClass();
			$obj->value=$value;
		}
		$inserts=array(); $creates=array();
		foreach($obj as $key=>$val) {
				if(is_array($val)) {
						foreach($val as $obj) {
							list($create, $insert)=$this->ObjToMySQL($obj, $table+1);
							$inserts=array_merge($inserts,$insert);
						}
						$creates=array_merge($create, $creates);
						$fields[]=$key;
						$vals[]="'_json_results_".($table+1)."'";
				} else {
						$fields[]=$key;
						$vals[]="'".addslashes($val)."'";
				}
		}
		$inserts[]="insert into _json_results_$table (".implode(", ",$fields).") values (".implode(", ", $vals).");";
		$creates[]="create temporary table _json_results_$table (".implode(" text, ", $fields)." text);";
		return array($creates, $inserts);
	}
}
?>