<?php
if( isset( $_SERVER['css'] ) && is_array( $_SERVER['css'] ) ) {
    try{
        foreach ($_SERVER['css'] as $style)
            echo '<link rel="stylesheet" href="' . $style . '">';
    }catch ( Exception $e ){
        // Do nothing for now!!!
    }
}