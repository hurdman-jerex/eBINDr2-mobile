<?php

// bring in the javascript packer
//include "/home/serv/public_html/ebindr/includes/jspacker.php";

// list of files to include and put in the javascript file
$files = array(
	'ebindr.js',
	'library/layout.js',
	'library/images.js',
	'library/security.js',
	'library/functions.js',
	'library/user.js',
	'library/community.js',
	'library/progressbar.js',
	'library/button.js',
	'library/tabs.js',
	'library/window.js',
	'library/modal.js',
	'library/keyboard.js',
	'library/findr1.js',
	'library/data.js',
	'library/dashboard.js',
	'library/conversations.js',
	'library/tickettrackr.js',
	'library/legacy.js',
	'library/mooclip.js',
);
$leaveout = array(12);
// merge all javascript code together
$code = '';
foreach ($files as $i => $file) {
	
	$code .= file_get_contents("/home/serv/public_html/ebindr/scripts/".$file);
	/*
	if( !in_array($i,$leaveout) ) $code .= $tmp;
	else {
		$packer = new JavaScriptPacker($tmp, 0, true, false);
		$code .= $packer->pack();
	}*/
}

$filename = "ebindr.js";

/*$packer = new JavaScriptPacker($code, 0, true, false);
$code = $packer->pack();*/

// Send HTTP headers
header("Cache-Control: must-revalidate");
header("Content-Type: text/javascript");
header('Content-Length: '.strlen($code));
header("Content-Disposition: inline; filename=$filename");

// Output merged code
echo $code;

?>