// see if there is another window.parent
var readonly=false;

setbid = function(bid, start, cid, minimize) {
	if( typeof(minimize) == 'undefined' ) var minimize = false;
	ebindr.openBID(bid, start, cid, minimize);

    $http.get( '/m/reports/openbid.php',
        {
            bid: ebindr.current.bid
        },
        function( $json_response ){
            if( $json_response.success && $json_response.business.bid != ebindr.current.lastbid ){
                console.log( 'Update Business Information' );
                jQuery( '#business-information-name' ).text( $json_response.business.name );
                jQuery( '#business-information-name-edit' ).attr('href', '/m/business/names-dba/edit.php?did=' + $json_response.business.did);
            }else
                console.log( 'Same BID loaded!' );
        }
    );
};
// do page
dopage = function(page) {
	if( page == 'records' ) {

	    if( ebindr.current.page === 'findr' )
            window.location = '/m/searchlink.html?bid='+ebindr.current.bid;

	    /* Show Preloader */
        var preloader = '<div id="iframe-loading" style="text-align: center;"><img src="/ebindr/images/findr1/loading2.gif" alt="Loading ..." style="padding-top: 100px;"><br><br>Loading BID: '+ ebindr.current.bid +' </div>';
	    ebindr.modal.alert( preloader, '' );

	    if( typeof ebindr.current.lastbidloaded !== "undefined" && ebindr.current.lastbid === ebindr.current.bid ){
            /* Update Modal */
            $json_response = ebindr.current.lastbidloaded;
            $Modal.content( $json_response.html );
            $Modal.modalElement.find( '.modal-title' ).html( $json_response.business.name );
            $Modal.modalElement.find( '.submit' ).hide();

            console.log( 'Same BID loaded!' );
            return;
        }

        $http.get( '/m/reports/openbid.php',
            {
                bid: ebindr.current.bid
            },
            function( $json_response ){
                if( $json_response.success ){
                    ebindr.current.lastbidloaded = $json_response;
                    /* Update Modal */
                    $Modal.content( $json_response.html );
                    $Modal.modalElement.find( '.modal-title' ).html( $json_response.business.name );
                    $Modal.modalElement.find( '.submit' ).hide();

                    console.log( 'Update Business Information' );
                    jQuery( '#business-information-name' ).text( $json_response.business.name );
                    jQuery( '#business-information-name-edit' ).attr('href', '/m/business/names-dba/edit.php?did=' + $json_response.business.did);
                }
            }
        );

	    //$('business-record-information-tab').
	    //setbid( ebindr.current.bid );
        //window.location = '/m/searchlink.html?bid='+ebindr.current.bid;
    }
};
// do shade
doshade = function(button,qty) {
	ebindr.button.shade(button,qty);
};
// editr_run
editr_run = function(button) {
	ebindr.button.editr(button);
};
// hidethem
hidethem = function() {
	var iframeid = ebindr.window.library.focusedWindow.options.id + '_iframe';
	(function() { ebindr.button.toolbar.escape( ebindr.window.library.focusedWindow, ebindr.window.iframe(iframeid), false ); }).delay(100);
};
FileBrowser = function(type, value) {
	ebindr.button.fileBrowser( type, value );
};
DoFindr = function(findrtype, findrval) {
	if( ebindr.findr2.started ) {
		MUI.focusWindow($('findr2'));
		if ($('findr2').retrieve('instance').isMinimized == true)	MUI.Dock.restoreMinimized.delay(25, MUI.Dock, $('findr2'));
		ebindr.findr2.setSearch(findrtype);
		ebindr.findr2.search(findrtype, findrval);
	} else {
		ebindr.current.link_search = findrval;
		ebindr.openFINDr( findrtype );
	}
	// ebindr.openFINDr( findrval );
	// ebindr.growl( 'Refreshing findr', 'If findr shows invalid data or no data at all it will refresh with the appropriate data in 5 seconds' );
	// setTimeout(function(){ ebindr.findr2.search(findrtype, findrval); }, 5000);
};
findr = function () {
	ebindr.openFINDr();
};

CheckSecurity = function( button ) {
	return ebindr.button.access(button);
};

editr_run_edit = function (button) {
	return ebindr.button.editr_edit( button + ".editr", ebindr.current.bid, true );
};