<?php include "/home/serv/public_html/m/includes/readme.php"; ?>
<?php

$url= $___ebindr2mobile_http[ 'api_url' ] .'business/basic/staff/update/'.$_GET['bid'].'/'.$_SESSION['user_info']->sid;
$bbapi->get($url);

$_SESSION['bid'] = $_GET['bid'];

$business_info = json_decode( $bbapi->get( $___ebindr2mobile_http[ 'api_url' ] .'business/info/'. $_GET['bid'] ) )->results;

//echo '<pre>'.print_r( $business_info, true ).'</pre>';
//exit();

$results = array(
    'business' => $business_info[0],
    'success' => true
);

header('Content-Type: application/json; charset=utf-8');
echo print_r( json_encode( $results ) , true );
?>
