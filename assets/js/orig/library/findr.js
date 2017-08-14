// bring eBINDr into the finder
var ebindr = window.parent.ebindr;

ebindr.library.findr = new Class({
 
	Implements: [Events, Options],
	// the tab options
	options: new Hash(),
	focus: document.body, // element that has focus
	
	initialize: function( options ) {
		$extend(this.options,options);
		// log that this class has been loaded
		ebindr.console( 'ebindr.findr loaded' );
		// make sure we are logged in
		if( ebindr.authenticate() ) {
			this.start();
		}
		window.parent.$( 'custom-findr' ).clone( true, true ).inject( $( 'findr-more' ), 'after' );
		$( 'custom-findr' ).getElements( 'li' ).each( function (el) {
			el.addEvent( 'click', function(e) {
				ebindr.findr.find( this.id );
			});
		});

	},
	
	setsearch: function( which ) {
		$$( '.button32' ).removeClass( 'shade' );
		$$( '.button32' ).each( function(e) {
			if( e.getElement( 'u' ) ) { // Find underlined key
				var whichfind = e.getElement( 'u' ).innerHTML.toLowerCase();
				if( whichfind == which ) e.addClass( 'shade' );
			}
		});
		window.parent.$( 'findr' ).focus();
		$( 'findr-search' ).focus();
		$( 'findr-search' ).select();
	},
	
	start: function() {
		// log that findr was started
		ebindr.log( 'FINDr started' );
		// add the switch to the find type
		$( 'begins' ).addEvent( 'click', function(e) {
			if( this.get('text') == 'Find Begins With *' ) this.set( 'html', 'Find <u>*</u> Any Part Of *' );
			else this.set( 'html', 'Find Begins With <u>*</u>' );
		});
/*		$( 'findr_export' ).addEvent( 'click', function(e) {
			var searchstring = $( 'findr-results' ).get( 'src' );
			alert(searchstring.replace("/report/","/report/exportr,"));
			window.open(searchstring.replace("/report/","/report/exportr,"));
		}); */
		$( 'findr-more' ).addEvent( 'click', function(e) {
			$( 'custom-findr' ).setStyles({ display:"block", position:"absolute", left:"0px", top:"0px" });
			var newleft=e.page.x-200;
			var newtop=e.page.y;
			newleft = document.body.scrollWidth - $( 'custom-findr' ).getCoordinates().width - 10;
			newtop = this.getCoordinates().top + this.getCoordinates().height;
			$( 'custom-findr' ).setStyles({ display:"block", position:"absolute", left:newleft, top:newtop });
		});
//		$( 'findr-more' ).addEvent( 'mouseleave', function(e) {
	//		window.parent.$( 'custom-findr' ).setStyles({ display:"none" });
		//});
		// focus on the findr search box
		$( 'findr-search' ).focus();
		//ebindr.growl( $$('.button32').length + ' buttons' );
		var obj = this;
		$$( '.button32' ).addEvent( 'click', function(e) {
			if( this.getElement( 'u' ) ) { // Find underlined key
				var whichfind = this.getElement( 'u' ).innerHTML.toLowerCase();
			} else { // or find value within [ ]
				var whichfind = this.innerHTML.replace( /.+\[(.+)\].*/g, "$1" ).toLowerCase();
			}
			$$( '.button32' ).removeClass( 'shade' );
			this.addClass( 'shade' );
			// and pass that value to the find function
			obj.find( whichfind );
		});
		this.listen();
		this.resize();
	},
	
	find: function( which ) {
		switch( which ) {
			case 'esc': // Close FINDr
//				$( 'findr-search' ).blur();
				ebindr.window.minimize( 'findr' );
//				alert(window.parent.$( 'findr_closeButton' ).get('title'));
//				var closefindr=window.parent.$( 'findr_closeButton' );
//				closefindr.click();
//				window.parent.$( 'findr_titleBar' ).focus();
//				window.setTimeout("", 200);
				window.parent.document.getElementById("keydownfield").focus();
//				alert(window.parent.document.getElementById("keydownfield").value);
//				window.parent.MochaUI.closeWindow(window.parent.MochaUI.Windows.instances.get('findr').windowEl);
				break;
			case 'enter': // Retrieve record
				switch( ebindr.lastfindr ) {
					case "u": case "c": case "h": ebindr.openBID(ebindr.current.key1, false, ebindr.current.key2); break;
					case "s.Consumer Member": var oldbid=bid; bid=key1; editr_run("qvq Consumer Members"); bid=oldbid; break;
					case "s.Mediation Case": var oldbid=bid; bid=key1; editr_run("qvq Mediation Cases"); bid=oldbid; break;
					case "s.Small Claims Case": var oldbid=bid; console.log("here:" + key1); bid=key1; editr_run("qvq Small Claims Cases"); bid=oldbid; break;
					case "x": case "k": case "w": case "n": case "e": case "p": case "i":
						if(ebindr.current.lastInquiry=="A") {
							ebindr.logstat(ebindr.current.key1, 'A', "bindr");
							ebindr.button.linkreport(ebindr.data.store.agencyreportlink);
						}
						ebindr.openBID(ebindr.current.key1)
						break;
					case "l": case "z":
						if(ebindr.current.lastInquiry=="B") {
							ebindr.logstat(ebindr.current.key1, 'B', "bindr");
							ebindr.button.linkreport(ebindr.data.store.bureaureportlink);
						}
						ebindr.openBID(ebindr.current.key1)
						break;
					case "g": console.log('LINK == %s', ebindr.data.store.genadvreportlink);
						if(ebindr.current.lastInquiry=="G") ebindr.logstat(ebindr.current.key1, 'G', "bindr");
						else ebindr.alert("A stat was NOT logged for this general advice report", "Notice");
						ebindr.button.linkreport(ebindr.data.store.genadvreportlink);
    					break;
					case "d": case "t":
						if(ebindr.current.lastInquiry=="T") ebindr.logstat(ebindr.current.key1, 'T', "bindr");
						else ebindr.alert("A stat was NOT logged for this TOB roster", "Notice");
						ebindr.button.linkreport(ebindr.data.store.rosterreportlink);
						break;
				}
				ebindr.current.lastInquiry=false;
				break;
			default:
				if( which.match( /^findr-/ ) ) which = which.replace( /^findr-/g, "s." );
				var find = ( $( 'begins' ).get('text') == 'Find Begins With *' ? '' : '%' ) + $( 'findr-search' ).get('value') + '%';
				// log what we are searching for
				ebindr.log( 'FINDr Search Details: lite findr ' + which + ' "' + find + '"' );
				if(which=="a") ebindr.current.FINDrA=$( 'findr-search' ).get('value');
				if(which=="p") ebindr.current.FINDrP=$( 'findr-search' ).get('value');
				if(which=="n") ebindr.current.FINDrN=$( 'findr-search' ).get('value');
				if(which=="e") ebindr.current.FINDrE=$( 'findr-search' ).get('value');
				if(which=="w") ebindr.current.FINDrW=$( 'findr-search' ).get('value');
				//$( 'findr-results' ).innerHTML = "Searching... please wait.";
				var searchstring = '/report/lite findr ' + which + '/?noheader&ebindr2' + 
				'&find=' + escape(find) + 
				'&bid=' + ebindr.current.bid + 
				'&lid=' + ebindr.current.lid +
				'&key1=' + ebindr.current.bid;
				$( 'findr-results' ).set( 'src', searchstring );
				$( 'findr-results' ).focus();
				ebindr.lastfindr=which;
				
		}
	}, 

	resize: function () {
		(function() {
			$( 'findr-results' ).setStyle( 'height', window.getSize().y-195 );
			$( 'findr-results' ).setStyle( 'width', window.getSize().x );
			$( 'findr-search-div' ).setStyle( 'width', window.getSize().x-165 );
			$( 'begins' ).setStyle( 'width', '110px' );
			$( 'findr-more' ).setStyle( 'width', '50px' );
		}).delay(50);
	},
	
	listen: function() {
		( Browser.Engine.trident ? $$('body')[0] : window ).addEvent( 'keydown', function(event) {
			event = new Event(event);
			if( event.key == "esc" ) {
				ebindr.findr.find( 'esc' );
				event.preventDefault();
				event.stopPropagation();
			}
			switch( event.target.get('id') ) {
				case "findr-search":
					switch( event.key ) {
						case 'enter':
							$$( '.shade' ).fireEvent( 'click' );
							$( 'findr-search' ).blur();
							break;
						case 'tab':
							$( 'findr-search' ).blur();
							event.preventDefault();
							event.stopPropagation();
							break;
						default:
							break;
					}
					break;
				case null:
					switch( event.key ) {
						case "n": case "i": case "e": case "p": case "a": case "u": case "h": case "c": case "k": case "l": case "z": case "t": case "d": case "g":
							//alert( event.key );
							$( 'findr_' + event.key ).fireEvent( 'click' );
							break;
						case "tab":
							$( 'findr-search' ).focus();
							event.preventDefault();
							event.stopPropagation();
							break;
					}
					break;
				default:
					alert( event.key + event.target.get('id') );
					break;
			}
//			alert( event.key + event.target.get('id') );//+ ":" + ebindr.findr.focus.get('tag') );
		});
	} 
});

//var findr = ebindr.library.findr;