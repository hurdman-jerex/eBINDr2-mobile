// add methods/functions to ebindr
ebindr.extend({

	/*
		Will set and open a bid
		TODO: clear out business fields before loading BID
	*/
	openBID: function( bid, start, cid, minimize ) {
		$( 'keydownfield' ).focus();
		// log the action
		ebindr.log( 'Attempting to open BID ' + bid );
	
		// check to see if eBINDr is just starting
		if( typeof(start) == 'undefined' ) var start = false;
		if( typeof(cid) == 'undefined' ) var cid = 0;
		if( typeof(minimize) == 'undefined' ) var minimize = ( ebindr.current.bid != bid );

		// show the loader for all of the frames
		$$('iframe.records').fireEvent('reload','/ebindr/blank.html');

		// check and make sure we have a valid bid
		if( $chk(bid) ) {
			// log the last bid
			ebindr.log( 'Previous BID ' + ebindr.current.bid );
			// set the last bid and dba
			ebindr.current.lastbid = ebindr.current.bid;
			ebindr.current.lastdba = $('bn').get('text');
			// set the current bid
			ebindr.current.bid = bid;
			ebindr.current.cid = cid;

			this.onOpenBID();
			
			this.onOpenBID=function(){return;};
		}

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
	}

});
