<?php
include '/home/serv/public_html/m/includes/readme.php';

if( isset( $__business_info['search_page'] ) && file_exists( '/home/serv/public_html/m/' . $__business_info['search_page'] ) )
    include $__business_info['search_page'];
else
    header("Location: " . $___ebindr2mobile_http[ 'url' ] . "search.html"); // Default Search Page
?>