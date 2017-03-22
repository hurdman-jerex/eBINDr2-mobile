// add methods/functions to ebindr
ebindr.extend({
    /*
       Will open an iframe
     */
    initIFrame: function( iframe, url )
    {
        ebindr.frameEl = document.getElementById( iframe );
        if( undefined !== url )
            this.loadIframeSrc( url );

        /*$iframe.load( function(){
            this.setHeight( this );
        });*/
    },

    setHeight: function( e ){
        e.height = e.contentWindow.document.body.scrollHeight;
    },

    loadIframeSrc: function( $option ){
        console.log( $option );
        //ebindr.frameEl.title = $option.title;
		if( undefined === $option.contentUrl )
			ebindr.frameEl.src = $option;
		else
        	ebindr.frameEl.src = $option.contentURL;
    },

	/*
		Will set and open a bid
		TODO: clear out business fields before loading BID
	*/
	openBID: function( bid, start, cid, minimize ) {
		// log the action
		ebindr.log( 'Attempting to open BID ' + bid );
		ebindr.console( 'Attempting to open BID ' + bid );

		// check to see if eBINDr is just starting
		if( typeof(start) == 'undefined' ) var start = false;
		if( typeof(cid) == 'undefined' ) var cid = 0;
		if( typeof(minimize) == 'undefined' ) var minimize = ( ebindr.current.bid != bid );

		// check and make sure we have a valid bid
		if( $chk(bid) ) {
			// log the last bid
			ebindr.log( 'Previous BID ' + ebindr.current.bid );
			ebindr.console( 'Previous BID ' + ebindr.current.bid );
			// set the last bid and dba
			ebindr.current.lastbid = ebindr.current.bid;
			//ebindr.current.lastdba = $('bn').get('text');
			// set the current bid
			ebindr.current.bid = bid;
			ebindr.current.cid = cid;

			this.onOpenBID();
			
			this.onOpenBID=function(){return;};
		}

        //window.location = '/m/searchlink.html?bid='+ebindr.current.bid;
	},
	
	onOpenBID: function( ) {
		return;
	},
	
	dolanguage: function() {
		switch( ebindr.current.lid ) {
			case 1: $('in').set( 'html', '1 - E<u>n</u>glish' ); break;
			case 2: $('in').set( 'html', '2 - Spa<u>n</u>ish' ); break;
			case 3: $('in').set( 'html', '3 - Fre<u>n</u>ch' ); break;
		}
	},

	logstat: function(val, type, field) {
		var mylogstat = new Request.HTML().get( '/report/lite button logstat/?ebindr2=y&noheaderhidden&bindrwebvrs='+field+'&bid='+val+'&type='+type );
	},
	
	isNumber: function(n) {
		return !isNaN(parseFloat(n)) && isFinite(n);
	},

	doWindow: function( $button, $options )
	{
        console.log( { $button,  $options } );
	},

    doEditr: function( $button, $options )
	{
        console.log( { $button, $options } );
        ebindr.loadIframeSrc( $options );
	},

	doList: function( $button, $options )
	{
		console.log( { $button,  $options } );
        ebindr.loadIframeSrc( $options );
	},
	doNormal: function( $button, $options )
	{
        console.log( { $button,  $options } );
	},
	doChange: function( $button, $options )
	{
        console.log( { $button,  $options } );
	},
    doCurrentlocation: function( $button, $options )
	{
        console.log( { $button,  $options } );
	}
});
