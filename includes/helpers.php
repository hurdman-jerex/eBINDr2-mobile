<?php

if( ! function_exists( 'dd' ) ){
    function dd( $str ){
        echo  '<pre>'.print_r( $str, true ).'</pre>';
    }
}

if( ! function_exists( 'mobile_title' ) ){
    function mobile_title( $pagetitle, $bizinfo ){
        $custom_title = isset( $bizinfo['mobilecustomtitle'] ) ?  ( $bizinfo['mobilecustomtitle'] . " " ) : "" ;

        $pagetitle = (String) "eBINDr2go " . $custom_title . $pagetitle;

        if( isset( $bizinfo['bbbname'] ) )
            $pagetitle = (String) $bizinfo['bbbname'] . " " . $pagetitle;

        return $pagetitle;
    }
}

?>