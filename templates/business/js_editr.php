<?php
list(,,,$__mergecode) = explode( '/', $_menuPage );

if( $___ebindr2mobile_http['segments'][0] == 'business' &&
    (
        $___ebindr2mobile_http['segments'][2] == 'add' ||
        $___ebindr2mobile_http['segments'][2] == 'edit'
    ) ) {
    $info = $___ebindr2mobile_http['segments'][1];

    if ( $info == 'sales' )
        $redirect_to = $___ebindr2mobile_http['url'] . 'business/sales.html';
    elseif ($info == 'email' || $info == 'website')
        $redirect_to = $___ebindr2mobile_http['url'] . 'business.html?info=email-website';
    elseif ($info == 'phone' || $info == 'fax')
        $redirect_to = $___ebindr2mobile_http['url'] . 'business.html?info=phone-fax';
    elseif ($info == 'names-dba')
        $redirect_to = $___ebindr2mobile_http['url'] . 'business.html?info=business-names';
    elseif ($info == 'people')
        $redirect_to = $___ebindr2mobile_http['url'] . 'business.html?info=people-contacts';
    else
        $redirect_to = $___ebindr2mobile_http['url'] . 'business.html?info=' . $info;

    $_menuPage .= '&eform='. base64_encode( $redirect_to ) ;
    //echo '<pre>'.print_r( $_menuPage, true ).'</pre>';
}
?>

<script type="text/javascript">
    jQuery( document ).ready(function() {
        var _formtitle = jQuery( '.span12' ).find('h2');
        var _menupage = '<?=$_menuPage?>';
        var _mergecode = '<?=$__mergecode?>';
        var _formtype = '<?=$___ebindr2mobile_http['segments'][2]?>';
        var _iframe = jQuery('#reportIframe');

        var reportIframeHeight = ( window.screen.height - 150 );
        _iframe.css( 'height', reportIframeHeight + 'px' );

        _iframe.attr('aria-label', _formtype + ' Form. Items in RED are required in order to proceed.  ');
        _iframe.focus();

        ebindr.initializeEditr( function(){

            ebindr.current._report_url = '<?=$___ebindr2mobile_http[ 'report_url' ]?>';
            ebindr.current.segments = <?php echo json_encode($___ebindr2mobile_http[ 'segments' ] ); ?>;
            ebindr.initIFrame( 'reportIframe', { contentURL : _menupage } );

            /* For Cancel Button */
            ebindr.frameEl.lang = _mergecode + '.editr.undo';

            //var _required = jQuery( '<p style="margin-top:5px;"><span class="textLabel">Items in </span><b><span class="textLabelReqd">RED</span></b><span class="textLabel"> are required in order to proceed. </span></p>').html();
            //_required.insertBefore( ebindr.frameEl );
        } );
    });
</script>