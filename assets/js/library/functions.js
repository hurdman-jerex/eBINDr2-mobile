// add methods/functions to ebindr
ebindr.extend({
    /*
       Will open an iframe
     */
    initIFrame: function( iframe, url )
    {

        ebindr.frameEl = document.getElementById( iframe );

        ebindr.frameEl.onload = function() {

            /*var label = jQuery( '<p>' + ebindr.current.editr_desc + '</p>' ).text();
            var reportname = jQuery('#report-name');
            //console.log( label );
            reportname.val( label )
                .attr( 'aria-label', label );
            reportname.focus();*/

            ebindr.frameEl.setStyle( 'height', ( jQuery( window ).height() - 150 ) + 'px' );
            ebindr.frameEl.focus();
            /*console.log( window.screen.height );
            console.log( jQuery( window ).height() );
            console.log( ebindr.frameEl.height = jQuery( window ).height() )*/
        };

        if( undefined !== url )
            this.loadIframeSrc( url );

        /*$iframe.load( function(){
            this.setHeight( this );
        });*/
    },

    setHeight: function( e ){
        console.log( e.contentWindow.document.body.scrollHeight );
        e.height = e.contentWindow.document.body.scrollHeight;
    },

    loadIframeSrc: function( $option ){
        //console.log( $option );

        if( typeof $option === "undefined" )
            return false;

        //ebindr.frameEl.title = $option.title;
		if( typeof $option.contentURL === "undefined" ) {
            ebindr.frameEl.src = $option;
            //console.log( 'undefined Content URL' );
        }else{
            ebindr.frameEl.src = $option.contentURL;
            //console.log( 'Content URL' );
        }

        //ebindr.frameEl.setHeight( ebindr.frameEl );
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

    /* FINDr */
    openFINDr2: function( which, inquirystat ) {
        if( $type( inquirystat )=='undefined' ) inquirystat=false;
        ebindr.current.lastInquiry=inquirystat;
        ebindr.current.auto_findr=which;
        ebindr.doNormal( 'e button findr', {
            id: 'findr2',
            loadMethod: 'xhr',
            contentURL: '/ebindr/views/findr1.html',
            padding: { left: 0, right: 0, top: 0, bottom: 0 },
            width: window.getSize().x-65,
            height: window.getSize().y-200,
            minimizable: true,
            maximizable: false,
            resizable: true,
            closable: false,
            title: "FINDr",
            onContentLoaded: ebindr.findr2.start.bind(this),
            onMinimize: function() {
                ebindr.findr2.typing = false;
                ebindr.window.library.focusedWindow = false;
            },
            //onFocus: ebindr.findr2.windowInit.bind(this), // it fixed the shortcut key ? - Jerex
            onRestore: ebindr.findr2.windowInit.bind(this),
            onWindowOpen: ebindr.findr2.start.bind(this),
            onResize: function() {
                if(! ebindr.findr2.started ) return;
                /*$('more-list').setStyles({
                    'left': $('more-search').getCoordinates().left - 310,
                    'top': $('more-search').getCoordinates().top + 10
                });*/
            }
        });

    },
    openFINDr: function(which, inquirystat) {
        if( 1==1 /*Cookie.read("reportr_username") == 'dst'*/ ) {
            this.openFINDr2(which,inquirystat);
            return;
        }
        if( $type( inquirystat )=='undefined' ) inquirystat=false;
        ebindr.current.lastInquiry=inquirystat;
        ebindr.current.auto_findr=which;
        ebindr.doNormal( 'e button findr', {
            id: 'findr',
            contentURL: '/ebindr/views/findr.html',
            width: window.getSize().x-350,
            height: window.getSize().y-150,
            minimizable: true,
            maximizable: false,
            closable: false,
            storeOnClose: true,
            padding: { top: 0, bottom: 0, left: 0, right: 0 },
            title: "FINDr",
            onContentLoaded: function() {
                $( 'findr' ).focus();
                ebindr.findr.setsearch( ebindr.current.auto_findr );

                if( ebindr.current.link_search != '' ) {
                    $('findr_iframe').contentWindow.$('findr-search').value = ebindr.current.link_search;
                    ebindr.findr.find('x');
                    ebindr.current.link_search = '';
                }
            },
            onResize: function(e) {
                ebindr.findr.resize();
            },
            onWindowOpen: function(e) {
                if( which ) {
                    ebindr.findr.setsearch( which );
                }
                if( typeof(search_value) != 'undefined' ) {
                    this.options.onContentLoaded();
                }
                if( ebindr.current.link_search != '' ) {
                    $('findr_iframe').contentWindow.$('findr-search').value = ebindr.current.link_search;
                    ebindr.findr.find( ebindr.current.auto_findr );
                    ebindr.current.auto_findr=false;
                }
            },
            onRestore: function(e) {
//				alert(ebindr.current.link_search);
                if( ebindr.current.link_search != '' ) {
                    $('findr_iframe').contentWindow.$('findr-search').value = ebindr.current.link_search;
                    ebindr.findr.find( ebindr.current.auto_findr );
                    ebindr.current.auto_findr=false;
                }
                $( 'findr' ).focus();
                ebindr.findr.setsearch( ebindr.lastfindr );
                this.options.onContentLoaded();
            }
        });

    },

    backform: function( e ){
        //e.preventDefault();
        var current_page = ebindr.current.segments[2];
        var $self = this;

        /* Lets fire undo mergecode for Add forms */
        if( current_page == 'add' ){
            //console.log( undo2 );
            //console.log( undo1 );
            var undo_url = ebindr.current._report_url + ebindr.frameEl.lang + '/?editr&ebindr2=y&noheaderhidden&undo1=' + undo1 + '&undo2=' + undo2;
            //console.log( undo_url );
            $http.get( undo_url );
            /* Give time to undo record, then redirect */
            (function(){
                $self._get_cancel_redirection( ebindr.current.segments[0] );
            }).delay(800);
        }else if( current_page == 'edit' )
            $self._get_cancel_redirection( ebindr.current.segments[0] );
    },

    _get_cancel_redirection: function( $page ){
        if( $page == 'business' ){
            if( ebindr.current.segments[1] == 'general' )
                window.location.href = '/m/business/general.php';

            else if( ebindr.current.segments[1] == 'sales' )
                window.location.href = '/m/business/sales.html';

             else if( ebindr.current.segments[1] == 'email' || ebindr.current.segments[1] == 'website' )
             window.location.href = '/m/business.html?info=email-website';

             else if( ebindr.current.segments[1] == 'phone' || ebindr.current.segments[1] == 'fax' )
             window.location.href = '/m/business.html?info=phone-fax';

             else if( ebindr.current.segments[1] == 'names-dba' )
             window.location.href = '/m/business.html?info=business-names';

			else if( ebindr.current.segments[1] == 'people' )
             window.location.href = '/m/business.html?info=people-contacts';

             else
             window.location.href = '/m/business.html?info=' + ebindr.current.segments[1];
         }
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
        console.log( { do:'window', $button,  $options } );
        ebindr.loadIframeSrc( $options );
	},

    doEditr: function( $button, $options )
	{
        console.log( { do:'editr', $button, $options } );
        ebindr.loadIframeSrc( $options );
	},

	doList: function( $button, $options )
	{
		console.log( { do:'list', $button,  $options } );
        ebindr.loadIframeSrc( $options );
	},
	doNormal: function( $button, $options )
	{
        console.log( { do:'normal', $button,  $options } );
        ebindr.loadIframeSrc( $options );
	},
	doChange: function( $button, $options )
	{
        console.log( { do:'change', $button,  $options } );
		ebindr.loadIframeSrc( $options );
	},
    doCurrentlocation: function( $button, $options )
	{
        console.log( { do:'current', $button,  $options } );
		ebindr.loadIframeSrc( $options );
	}
});
