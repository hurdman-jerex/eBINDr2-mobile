<?php

if( ! function_exists( 'dd' ) ){
    function dd( $str ){
        echo  '<pre>'.print_r( $str, true ).'</pre>';
    }
}

?>