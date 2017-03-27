<script type="text/javascript">
    jQuery( document ).ready(function() {
        ebindr.initializeEditr( function(){
            ebindr.current.segments = <?php echo json_encode($___ebindr2mobile_http[ 'segments' ] ); ?>;
            ebindr.initIFrame( 'reportIframe', '<?=$_menuPage?>' );
        } );
    });
</script>