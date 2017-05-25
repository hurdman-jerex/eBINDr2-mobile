<?php
// list of files to include and put in the javascript file
$files = array(
	'ebindr.js',
	'library/security.js',
	'library/functions.js',
	'library/user.js',
	'library/legacy.js',
	'library/button.js',
	'library/keyboard.js',
	'library/findr1.js',
	'library/findr2.js',
	'library/mfindr.js',
	'library/data.js'
);
$leaveout = array(12);
// merge all javascript code together
$code = '';
foreach ($files as $i => $file) {

	$code .= file_get_contents("/home/serv/public_html/m/assets/js/".$file);
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