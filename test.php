<?php
include 'library/e2mobile.web.php';
$___e2m = new e2mobileWeb();

$__http = $___e2m->getHttp();
//dd( $__http );
$path = str_replace( '/m/test.php/', '', $__http['uri'] );
echo $___e2m->getContent(  $path  );

//echo session_id();

?>