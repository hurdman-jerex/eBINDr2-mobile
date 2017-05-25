<?php
if( isset( $_SERVER['css'] ) && is_array( $_SERVER['css'] ) ) {
    try{
        foreach ($_SERVER['css'] as $style)
            echo '<link rel="stylesheet" href="' . $style . '">';
    }catch ( Exception $e ){
        // Do nothing for now!!!
    }
}

if( isset( $__business_info['info_js'] ) && !empty( $__business_info['info_js'] ) ): ?>
    <script type="text/javascript">
        <?php
            foreach( $__business_info['info_js'] as $js )
                echo $__business_info[ $js ] . " ";
        ?>
    </script>
<?php endif; ?>
