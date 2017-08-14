<?php
	
session_start();

class crm {

	public function __construct() {
		// bring in json encoding
		if( !function_exists('json_encode') ) include "/home/serv/library/json.php";
		// bring in functions and the definitions as are needed	
		include "/home/serv/public_html/ebindr/includes/functions.php";
		if(file_exists("../../definitions.php")) {
		        include "../../definitions.php"; // global definitions
		}
		if(file_exists("/home/definitions.php")) {
		        include "/home/definitions.php"; // global definitions
		}
		if(file_exists("../definitions.php")) {
		        include "../definitions.php"; // global definitions
		}
		include "/home/serv/includes/definitions.php"; // global definitions
		
		// now bring in mybindr and connect to the database
		include '/home/serv/library/mybindr.php';
		$mybindr=new mybindr; 
		$mybindr->database = LOCAL_DB;
		mysql_select_db( $mybindr->database, $mybindr->db );	
		$this->ebindr = $mybindr;
		
		if( !isset($_COOKIE['reportr_username'])  || strlen($_COOKIE['reportr_username']) < 1 ) {
			die("Access Denied");
		}
		
		if ( strpos($_SERVER['SERVER_NAME'], 'mbc-web') > -1 || strpos($_SERVER['SERVER_NAME'], 'vancouver') > -1 ) {
			$url = "mbc-web.app.bbb.org";
		} elseif (strpos($_SERVER['SERVER_NAME'], 'crm-dallas') > -1) {
			$url = "crm-dallas.hurdman.org";
		} elseif (strpos($_SERVER['SERVER_NAME'], 'atlanta') > -1) {
			$url = "127.0.0.1";
		} else {
			$url = "localhost"; //str_replace( $_SERVER['SERVER_NAME'], "localhost", $url );
		}
		
		$this->host = $url;
			
	}
	
	public function bind( $key, $value, $noescape = false ) {
		return $this->ebindr->addparm( $key, ( $noescape ? $value : mysql_real_escape_string($value) ) );
	}
	
	public function getMergeCode( $sql ) {
			// log that we are running this query
			$__q = mysql_real_escape_string( str_replace( array( "[", "]" ), "", $sql ) );
			// get the query
			list( $mergequeries ) = $this->ebindr->getquery( $__q );
			
			return $mergequeries;
	}
	
	public function query( $sql ) { 
		
		$this->bind( 'staff', $_COOKIE['reportr_username'] );

		// get for a mergequery
		if( substr( $sql, -1 ) == ']' ) {

			// log that we are running this query
			$__q = mysql_real_escape_string( str_replace( array( "[", "]" ), "", $sql ) );

			$__staff = mysql_real_escape_string($_COOKIE['reportr_username']);

			mysql_query( "insert into reportlog (mergecode, day, staff, count) values ('".$__q."', now(), '".$__staff."', 1) on duplicate key update count=count+1" );
			// get the query
			list( $mergequeries ) = $this->ebindr->getquery( $__q );
			
			$mergequeries = explode( "||", str_replace( "\r\n", "", $mergequeries ) );
			// return $mergequeries;
			//echo "<pre>"; print_r($mergequeries); echo "</pre>";
			foreach( $mergequeries as $i => $q ) {
				$result = mysql_query( $this->ebindr->resolvepipes($q) );

				//$res_q = mysql_fetch_object($result );
				//echo "<pre>"; print_r($res_q ); echo "</pre>";

				if( ($i+1) == sizeof($mergequeries) ) return $result;
				else unset($result);
			}
		} else {

			return mysql_query( $this->ebindr->resolvepipes($sql) ); 
		}
	}
	
	public function toarraynumeric( $result ) {
		if( isset($result) && $result && mysql_num_rows($result) < 1 ) {
			return false;
		} else {
			$dataset = array();
			while( $row = mysql_fetch_row( $result ) ) {
				$dataset[] = $row;
			}
			return $dataset;
		}
	}
	
	public function toarray( $result ) {
		if( isset($result) && $result && mysql_num_rows($result) < 1 ) {
			return false;
		} else {
			$dataset = array();
			while( $row = mysql_fetch_assoc( $result ) ) {
				$dataset[] = $row;
			}
			return $dataset;
		}
	}
	
	public function mergecodeToVariables( $name, $params ) {
		$get = '';
		if( sizeof($params) > 0 ) {
			foreach( $params as $k => $v ) {
				$get .= '&' . urlencode($k) . '=' . rawurlencode($v);
			}
		}
		
		//echo "http://".$this->host."/report/merge/JSON.htm?ebindr2=y&json=y&NOASK&query=".rawurlencode($name)."&BYPASS=5g9f4ds8r&staff=" . $_COOKIE['reportr_username'] . $get;
		
		//echo "http://hurdmantest.hurdman.org/report/merge/JSON.htm?ebindr2=y&json=y&NOASK&query=".rawurlencode($name)."&BYPASS=5g9f4ds8r&staff=" . $_COOKIE['reportr_username'] . $get;
		$data = json_decode(file_get_contents("http://".$this->host."/report/merge/JSON.htm?ebindr2=y&json=y&NOASK&query=".rawurlencode($name)."&BYPASS=5g9f4ds8r&staff=" . $_COOKIE['reportr_username'] . $get));
		
		if( sizeof($data->resultset) == 1 ) return $data->resultset[0];
		else return $data->resultset;		
	}
	
	public function mergecode( $name, $params = array() ) {
		
		$get = '';
		if( sizeof($params) > 0 ) {
			foreach( $params as $k => $v ) {
				$get .= '&' . urlencode($k) . '=' . rawurlencode($v);
			}
		}
		
		foreach( $this->segments as $k => $v ) {
			$get .= '&segment'.$k.'=' . $v;
		}

		//echo "http://hurdmantest.hurdman.org/report/merge/JSON.htm?ebindr2=y&json=y&NOASK&query=".rawurlencode($name)."&BYPASS=5g9f4ds8r&staff=" . $_COOKIE['reportr_username'] . $get;
		$tmp = file_get_contents("http://".$this->host."/report/merge/JSON.htm?ebindr2=y&json=y&NOASK&query=".rawurlencode($name)."&BYPASS=5g9f4ds8r&staff=" . $_COOKIE['reportr_username'] . $get);
		$data = json_decode($tmp);
		$html = '';

		if (count($data->resultset) > 0) {
		$html = '
      <table id="mergecode" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <thead>
	        <tr>';
	        foreach( $data->resultset[0] as $key => $value ) {
	        $html .= '<th>'.$key.'</th>';
	        }
	        $html .= '</tr>      
        </thead>
        <tbody>';
	        foreach( $data->resultset as $i => $row ) {
	        $html .= '<tr>';
		        foreach( $row as $k => $v ) {
		        $html .= '<td>'.$v.'</td>';
		        }
	        $html .= '</tr>';
	        } 
        $html .= '</tbody>
      </table>';
    }
					        
		return $html;
	}
	
	public function enabled( $page ) {
		$this->bind( 'staff', $_COOKIE['reportr_username'] );
		if( !isset($page->isenabled) ) return true;
		$results = $this->toarraynumeric( $this->query( $page->isenabled ) );
		return ( $results[0][0] == 1 ? true : false );
	}

	public function email($tmparray) {

		$new_message = str_replace("\\\"", "\"", $tmparray['message']);

		$this->query( "insert into emailqueue values ( null, '" . $tmparray['bid'] . "', '" . $tmparray['staff'] . "', now(), '" . $tmparray['sendto'] . "', '" . $tmparray['subject'] . "', '" . $new_message . "', null, null, '" . $tmparray['bcc'] . "', '" . $tmparray['replyto'] . "', '" . $tmparray['sender'] . "', 'HTML' )" );

		$tmp_email = $this->toarray( $this->query( "select setup(5705) as `option`" ) );
		if ( strtolower(substr($tmp_email[0]['option'], 0, 1)) == 'y' ) {
			$emails = $this->toarray($this->query( "select * from emailaddress where bid = '" . $tmparray['bid'] . "'" ));
			$email_present = false;
			if (!isset($emails[0])) {
				$this->query( "insert into emailaddress (BID, Email, Main, Report, eQuote, OptOut, MassEmail, Created, returned) values ('" . $tmparray['bid'] . "', '" . $tmparray['sendto'] . "', 'y', 'n', 'n', 'n', 'n', curdate(), 'n')" );
				$this->query( "insert into changeaudit (fid, type, bid, key2, day, staff, history) values ('20', 'Insert', '" . $tmparray['bid'] . "', curdate(), now(), '" . $tmparray['staff'] . "', concat('Inserted to emailaddress from CRM with email ', '" . $tmparray['sendto'] . "', ' to bid ', '" . $tmparray['bid'] . "', ' by staff ', '" . $tmparray['staff'] . "'))" );
			} else {
				foreach ($emails as $email) {
					if ($email['Email'] == $tmparray['sendto']) $email_present = true;
				}
				if (!$email_present) {
					$this->query( "insert into emailaddress (BID, Email, Main, Report, eQuote, OptOut, MassEmail, Created, returned) values ('" . $tmparray['bid'] . "', '" . $tmparray['sendto'] . "', 'n', 'n', 'n', 'n', 'n', curdate(), 'n')" );
					$this->query( "insert into changeaudit (fid, type, bid, key2, day, staff, history) values ('20', 'Insert', '" . $tmparray['bid'] . "', curdate(), now(), '" . $tmparray['staff'] . "', concat('Inserted to emailaddress from CRM with email ', '" . $tmparray['sendto'] . "', ' to bid ', '" . $tmparray['bid'] . "', ' by staff ', '" . $tmparray['staff'] . "'))" );
				}
			}
		}
		return;
	}

	public function upload($tmparray, $bbbid) {
		for($i=1;$i<4;$i++) {
			if($_FILES["attachment".$i][name]) {
				$filecontent=implode('',file($_FILES["attachment".$i][tmp_name]));
				if(!eregi("pdf$", $_FILES["attachment".$i][name])) {
					$filecontent=str_replace("\r\n","***LINEBREAK***",$filecontent);
					$filecontent=ereg_replace('[{][\]shprslt[^{]+[{][^{]+[{][^{}]+[}][^{}]+[}][^{}]*[}]', '', $filecontent);
					$filecontent=ereg_replace('[{][\]nonshppic[^{]+[{][^{]+[{][^{}]+[}][^{}]+[}][^{}]*[}]', '', $filecontent);
					$filecontent=ereg_replace('[{][\][*][\]themedata[^{}]+[}]', '', $filecontent);
					$filecontent=ereg_replace('[{][\][*][\]panose[^{}]+[}]', '', $filecontent);
					$filecontent=ereg_replace('[{][\]flomajor.+[}]([}][^{}]+[{][\]colortbl)', "\\1", $filecontent);
					$filecontent=ereg_replace('[{][\][*][\]colorschememapping[^{}]+[}]', '', $filecontent);
					$filecontent=ereg_replace('[{][\][*][\]latentstyles.+[{][\][*][\]datastore[^{}]+[}]', '', $filecontent);
					$filecontent=str_replace("***LINEBREAK***","\r\n",$filecontent);
				}

				$filename = $bbbid . '/' . $_SESSION['currentBID'] . '/' . $_FILES["attachment".$i][name];
				$accesskey = "AKIAJQWGS3GZG36P4E2A";
				$bucket = "hurdman-files";				
				$secret = "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";
				$policy = '{
          "expiration": "' . date("Y-m-d",strtotime("+2 day",time())) . 'T00:00:00Z' . '",
          "conditions": [
            {
              "bucket": "' . $bucket . '"
            },
            {
              "acl": "public-read"
            },
            [
              "starts-with",
              "$key",
              ""
            ],
            [
            	"starts-with",
            	"$Content-Type",
            	""
            ],
            {
              "success_action_status": "201"
            }
          ]
        }';

        $tmpfile = '@' . $_FILES["attachment".$i][tmp_name];
				$base64Policy = base64_encode($policy);
				$signature = base64_encode(hash_hmac("sha1", $base64Policy, $secret, $raw_output = true));
				$filetype = $_FILES["attachment".$i][type];

				$post = array('key' => $filename, 'AWSAccessKeyId' => $accesskey, 'acl' => 'public-read', 'success_action_status' => '201', 'policy' => $base64Policy, 'signature' => $signature, 'Content-Type' => $filetype, 'file'=> $tmpfile);
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL,'https://hurdman-files.s3.amazonaws.com/');
				curl_setopt($ch, CURLOPT_POST,1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
				$result=curl_exec ($ch);
				curl_close ($ch);
			}
		}
	}

}

$crm = new crm();

//include "/home/serv/library/json.php";

$menu = json_decode($crm->getMergeCode('salescrm.menu'));
$segments = explode("/",preg_replace("/^\/|\/$/","",str_replace("/ebindr/views/crm/index.php","",$_SERVER['REQUEST_URI'])));

if( empty($segments[0]) ) $segments[0] = 'dashboard';
if( $segments[0] == 'api' ) {
	if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
		$api_result = $crm->mergecodeToVariables($_POST['mergecode'],$_POST);
	}
	exit;
} elseif( $segments[0] == 'function' ) {
	if ($segments[1] == 'email') {
		$crm->email($_POST);
	} else if ($segments[1] == 'upload') {
		$crm->upload($_FILES, $segments[2]);
	} else if ($segments[1] == 'setbid') {
		$_SESSION['currentBID'] = $_POST['bid'];
	}
	exit;
} else {
	if (isset($_SESSION['current_url'])) {
		if (!isset($_GET['noremember'])) $_SESSION['previous_url'] = $_SESSION['current_url'];
	}
	if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") { 
    $_SESSION['current_url'] = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	} else { 
	  $_SESSION['current_url'] = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	}
}

if( empty($segments[1]) && isset($menu->{$segments[0]}->default) ) $segments[1] = $menu->{$segments[0]}->default;

if( isset($menu->{$segments[0]}->sub->$segments[1]) ) $page = $menu->{$segments[0]}->sub->$segments[1];
else $page = $menu->{$segments[0]};

$crm->segments = $segments;

if ($segments[1] == 'searchleads') {
	$_SESSION['searchleads_segments'] = $segments;
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Sales CRM</title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
	<link href="/ebindr/views/crm/css/bootstrap-tabs-x.min.css" media="all" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" href="https://cdn.datatables.net/plug-ins/1.10.7/integration/bootstrap/3/dataTables.bootstrap.css">
	<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.1.1/css/fixedHeader.dataTables.min.css">
	<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
	<script type="text/javascript" src="/ebindr/scripts/plugins/chosen.jquery.min.js"></script>
	<link href="/ebindr/styles/plugins/chosen.1.5.css" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css" />
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
	<style type="text/css">
		body, html {
			background-color: #3F3F3F;
		}
		.text-center .nav-tabs > li, .text-center .nav-pills > li {
		    float:none;
		    display:inline-block;
		    *display:inline; /* ie7 fix */
		     zoom:1; /* hasLayout ie7 trigger */
		}

		.text-center .nav-tabs, .text-center .nav-pills {
		    text-align:center;
		}	
		.text-center .nav-tabs { border-bottom: none; }
		.tab-pane, .tab-content { background-color: #fff; }
		.tab-no-left {
			border: 1px solid #ddd;
			border-radius: 4px;
		}
		.active {
			font-weight: bold;
		}
		.tab-content {
			padding: 25px;
		}

		thead {
			background-color: #666666;
			color: white;
		}

		.table-striped > tbody > tr:nth-of-type(2n+1) {
	    background-color: white;
		}

		.table-striped > tbody > tr:nth-of-type(2n) {
	    background-color: #cccccc;
		}

		.nav li:not(.active) {
	    background-color: #666666;
	    border-radius: 5px 5px 0 0;
	    margin-bottom: 0;
		}

		.nav li:not(.active) a {
			color: white;
		}

		.tabs-krajee.tab-sideways .nav-tabs > li {
	    height: 40px;
	    margin-bottom: 85px;
	    margin-left: 11px;
	    width: 120px;
		}

		li:not(.active) a:hover {
			color: black;
		}

		.tab-sideways.tabs-left .nav-tabs > li.active, .tab-sideways.tabs-right .nav-tabs > li.active {
	    border-top: 0;
		}
	</style>
	
  </head>
  <body>
	<div style="padding-left: 15px; padding-right: 15px;">
		<div class="row">
			<div class="col-lg-12">
				<div style="margin-top: 10px;"></div>
				<? if(! isset($menu->{$segments[0]}->notabs)) { ?>
				<div class="text-center">
					<ul class="nav nav-tabs" role="tablist">
						<? foreach( $menu as $key => $value ) : ?>
						<? if( $crm->enabled( $value ) ) : ?>
						<? $top_href = '/ebindr/views/crm/index.php/' . $key; ?>
						<? if ($key == 'findleads') {
							if (isset($_SESSION['searchleads_segments'])) {
								$top_href = $top_href . '/' . $_SESSION['searchleads_segments'][1] . '/' . $_SESSION['searchleads_segments'][2];
							}
						}	?>
						<li role="presentation"<?=($key == $segments[0] ? ' class="active"' : '' )?>>
							<a href="<?=($top_href);?>"><?=$value->display?></a>
						</li>
						<? endif; ?>
						<? endforeach; ?>
					</ul>
				</div>
				<? } ?>
  
				<? if( isset( $menu->{$segments[0]}->sub ) ) : ?>
				<div class='tabs-x tabs-left tab-sideways tab-bordered tabs-krajee'>
			    <ul id="myTab-19" class="nav nav-tabs" role="tablist">
				    <? foreach( $menu->{$segments[0]}->sub as $k => $v ) : ?>
				    <? if( $crm->enabled( $v ) ) : ?>
			        <li<?=( $k == $segments[1] ? ' class="active"' : '' )?>>
			        	<a href="/ebindr/views/crm/index.php/<?=$segments[0]?>/<?=$k?>" <?=(strlen($v->id) > 0 ? 'id="' . $v->id . '"' : '');?>>
			        		<?=$v->display?>
			        	</a>
			        </li>
			      <? endif; ?>
			      <? endforeach; ?>
			    </ul>
			    <? else : ?>
			    <div class="tab-no-left">
			    <? endif; ?>
			    <div class="tab-content" style="min-height: 500px;">
		        <div style="display: inline-block; width: 100%;">
			        <? if( $page->type == 'page' ) : ?>
			        <? include "/home/serv/public_html/ebindr/views/crm/pages/" . $page->url; ?>
			        <? endif; ?>
			        
			        <? if( $page->type == 'editr' ) : ?>
			        <iframe id="iframe-commonreport" frameborder="0" width="100%" height="500" src="/report/<?=str_replace(".editr","",$page->mergecode)?>/?ebindr2=y&editr&noheader<?=(isset($_GET['bid']) ? '&bid=' . $_GET['bid'] : '');?>"></iframe>
			        <? endif; ?>
			        
			        <? if( $page->type == 'iframe-commonreport' ) : ?>
				        <? if( $segments[0] == 'reports' ) : ?>
				        	<? if( isset($_COOKIE['reportsLastUrl']) ) : ?>
				        	<iframe id="iframe-commonreport" frameborder="0" width="100%" height="500" src="<?=$_COOKIE['reportsLastUrl']?>"></iframe>
				        	<? else : ?>
				        	<iframe id="iframe-commonreport" frameborder="0" width="100%" height="500" src="/report/<?=$page->mergecode?>/?ebindr2=y"></iframe>
				        	<? endif; ?>
				        <? else : ?>
				        <iframe id="iframe-commonreport" frameborder="0" width="100%" height="500" src="/report/<?=$page->mergecode?>/?ebindr2=y"></iframe>
				        <? endif; ?>
			        <? endif; ?>

			        <? if( $page->type == 'mergecode' && isset($page->mergecode) && !empty($page->mergecode) ) : ?>
								<?=$crm->mergecode($page->mergecode)?>
			        <? endif; ?>

		        </div>
			    </div>
				</div>
			</div>
		</div>
	</div>
	
<div id="href_dialog" title="Warning">
  <p>You are about to claim a batch of leads.</p>
  <p>Are you sure you want to continue?</p>
</div>				
    
    <script src="/ebindr/views/crm/js/js.cookie.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>		
    <script src="/ebindr/views/crm/js/bootstrap-tabs-x.min.js" type="text/javascript"></script>	
    <script src="//cdn.datatables.net/1.10.10/js/jquery.dataTables.min.js" type="text/javascript"></script>	

    <script type="text/javascript" src="https://cdn.datatables.net/plug-ins/1.10.7/integration/bootstrap/3/dataTables.bootstrap.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/fixedheader/3.1.1/js/dataTables.fixedHeader.min.js"></script>
    <script type="text/javascript">
    	<? if ($segments[1] != 'addlead' && $segments[1] != 'submitbusiness') : ?>
	    var ebindr = {
		  openBID: function(bid) {
		  	window.parent.ebindr.openBID(bid, false, 0, false);
				$.post('/ebindr/views/crm/index.php/function/setbid', {
					bid: bid,
				}, function(data) {
					window.location = '/ebindr/views/crm/index.php/leaddetails/' + bid;
				});
		  }  
	    };
	    <? endif; ?>
			$(document).ready(function(e){
				var data_table = $('#mergecode').DataTable({
				  "paging": false,
				  fixedHeader: true,
				  "order": []
				});

				// console.log(data_table);
				// data_table.fixedHeader.enable( true );

				$('iframe').load(function() {
					var iframe = this;
					iframe.style.height = 0;
					iframe.style.height = iframe.contentWindow.document.body.offsetHeight + 25 + 'px';
					<? if( $segments[0] == 'reports' ) : ?>
					Cookies.set('reportsLastUrl', $(iframe).contents().get(0).location.href, { path: ''});
					<? endif; ?>
					
				});

				var dialog_href = "";

		    $("#href_dialog").dialog({
		    	autoOpen: false,
		      resizable: false,
		      height:250,
		      modal: true,
		      buttons: {
		        "Yes": function() {
		          $( this ).dialog("close");
		          window.location.href = dialog_href;
		        },
		        "No": function() {
		          $( this ).dialog("close");
		        }
		      }
		    });
		 
		    $("#getlead_crm").click(function() {
		    	dialog_href = $(this).attr('href');
		      $("#href_dialog").dialog("open");
		      return false;
		    });

		    <? if ($segments[1] == 'addlead_n') : ?>
				$('#iframe-commonreport').on('load', function() {
					var current_content = $("#iframe-commonreport").contents().find("#myTab-19").eq(0).html();
					if (current_content != undefined) {
						window.location.href = "/ebindr/views/crm/index.php/leaddetails/new?noremember=y";
					}
				});
		    <? endif; ?>

		    <? if ($segments[1] == 'submitbusiness') : ?>
				$('#iframe-commonreport').on('load', function() {
					var current_content = $("#iframe-commonreport").contents().find("#myTab-19").eq(0).html();
					if (current_content != undefined) {
						window.location = '/ebindr/views/crm/index.php/findleads/';
					}
				});
		    <? endif; ?>
			});  
		</script>
  </body>
</html>