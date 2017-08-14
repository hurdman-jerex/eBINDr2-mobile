// add methods/functions to ebindr
ebindr.extend({

	findr2: function() {	
		ebindr.window.normal( 'e button findr', {
			id: 'findr2',
			loadMethod: 'xhr',
			savable: false,
			contentURL: '/ebindr/views/findr2.html',
			width: window.getSize().x-250,
			height: window.getSize().y-150,
			minimizable: true,
			maximizable: true,
			closable: true,
			//storeOnClose: true,
			padding: { top: 0, bottom: 0, left: 0, right: 0 },
			title: "FINDr2", 
			onContentLoaded: function(windowEl) {
				$('search-options').setStyle( 'height', $('findr2_contentWrapper').getStyle( 'height' ) );
				$('search-results').setStyle( 'width', $('findr2_contentWrapper').getCoordinates().width-174 ); // 16 for window borders
				$('search-box').setStyles({
					'width': $('search-results').getCoordinates().width-16
				});
			},
			onResize: function(e) {
				$('search-options').setStyle( 'height', $('findr2_contentWrapper').getStyle( 'height' ) );
			},
			onWindowOpen: function(e) {
				if( which ) {
					ebindr.findr.setsearch( which );
				}
			},
			onFocus: function(e) {
				ebindr.findr.setsearch( ebindr.lastfindr );
			},
			onRestore: function(e) {
				$( 'findr' ).focus();
				ebindr.findr.setsearch( ebindr.lastfindr );
			},
			onMinimize: function() {
				ebindr.findr2.typing = false;
				ebindr.window.library.focusedWindow = false;
			}
		});
	},
	
	openLeadmarkBID: function (bid) {
		var get = new Request.JSON({
			url: '/report/merge/JSON.htm/',
			nocache: true,
			onComplete: function( data, str ) {
				if( str.match(/resultset/gi) && typeof(data) == 'object' && str != '//No data' ) {
					ebindr.openBID(data.resultset.bid);
				} else {
					ebindr.openBID(bid);
				}
			}
		}).get({
			'json': 'y',
			'NOASK': '',
			'query': 'openbid.special.otherbid',
			'bid': bid,
			't': new Date().getTime()
		});
	},

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

			// clear out all business information
			//$$( '#bn, #bo, #bp, #bf, #b@, #bu, #ba, #bt, #bz-btn, #records-business' ).set('text','');

			// close the findr
			if( $('findr2') ) {
				ebindr.window.minimize( 'findr2' );
//				ebindr.window.library.closeWindow(ebindr.window.library.Windows.instances.get('findr2').windowEl);
			}

			// see if this bid is the same as the last bid
			if( ebindr.current.lastbid == bid ) {
				this.reloadFrames(true);
			} else {
				if( start ) {
					ebindr.current.switchedbid = false;
					ebindr.reloadFrames(false);
				} else {
					ebindr.current.switchedbid = true;
					ebindr.data.preload(function() {
						ebindr.reloadFrames(false);
					});
				}
				new Request.HTML().get( '/report/e button info.myalerts/?ebindr2=y&noheaderhidden&bid='+this.current.bid );
			}
			/*$$( 'div.mochaTitlebar' ).each( function( titlebar ) {
//				this.setColors();
				
				//ebindr.growl( 'id', 'is: ' + titlebar.id );
				if( titlebar.id != 'findr' && !titlebar.id.match( "-" + bid + "_titleBar$") && !titlebar.hasClass( 'otherbid' ) ) {
				
				var titleBarEl = $(titlebar.getParent().getParent().id).retrieve('instance');
				titleBarEl.newBgColor = '#ffffcc';
				//titleBarEl.titleBarEl.setStyle( 'background-color', '#ffffcc' );
				//titleBarEl.setColors();
				titleBarEl.drawWindow();
//				titleBarEl.changeColor( '100,100,100', '200,200,200' );


					//titleBarEl.addClass('otherbid');
//					titlebar.addClass( 'otherbid' );
					var titleH3 = titlebar.getElement( 'h3' );
					var newTitle = titleH3.get( 'text' ) + " (BID: " + titlebar.id.replace( /^.+[-]([0-9]+)[_]titleBar$/, '$1' ) + ")";
					titleH3.set( 'text', newTitle );
				} else if( titlebar.id.match( "-" + bid + "_titleBar$") ) {
					titlebar.removeClass( 'otherbid' );
				}
			});*/

			// set the bid
			$('records-business').set( 'text', ebindr.current.bid );

			if ($('frame_history') !== null) {
				ebindr.window.parent.closeWindow($('frame_history'));
				ebindr.window.normal( 'e button by', {
					id: 'frame_history',
					width: 300,
					height: 400,
					maximizable: false,
					title: "History",
					contentURL: "/report/lite button by?noheader&ebindr2=y&&cmd=&newbid=&bid=" + ebindr.current.bid
				});
			}

			// minimize all windows
			if( minimize ) ebindr.window.minimizeAll();
			//if there was a custom onOpenBID function set, run it here, then change it back to the default of doing nothing

			this.onOpenBID();
			
			this.onOpenBID=function(){return;};
			
			
			ebindr.window.library.Windows.instances.each( function(win) {
				if( win.options.id.contains("list-") ) {
					win.options.storeOnClose = false;
					win.close();
				}
				
				// for business editr's lets close them out unless they have this set in attributes
				// 4194304 is the bit in the staff.attributes table for Closing Windows when opening a new BID
				if( win.options.id.match(/^lite-button-b/gi) && !$(win.options.id+"_titleBar").hasClass("otherbid")) {// && (this.data.store.securitykeys.split(",")[0] & 4194304) == 0 ) {
					//win.options.storeOnClose = false;
					//win.close();
					
					$(win.options.id+"_titleBar").addClass('otherbid'); //setStyles({"background-color":"red"});
					$(win.options.id+"_title").set( 'text', $(win.options.id+"_title").get('text') + ' for ' + ebindr.current.lastdba );
				}
			});
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
	
	/*
		Will reload the frames of data in eBINDr
	*/
	reloadFrames: function( complaintonly ) {

		// set the default frames to reload if it isn't being passed
		if( typeof(complaintonly) == 'undefined' ) var complaintonly = false;
				
		// if we are just reloading the complaint frame
		if( complaintonly ) {
			if($('frame_customerexperience')) {
				$('frame_customerexperience').fireEvent("reload", "/report/e button customerexperience/?noheader&bid={bid}&lid={lid}&cid={cid}&closecode=555");
				$('frame_x').fireEvent("reload", "/report/e button x/?noheader&bid={bid}&lid={lid}&cid={cid}&closecode=555");
				$('frame_z').fireEvent("reload", "/report/e button z/?noheader&bid={bid}&lid={lid}&cid={cid}&closecode=555");
				$('frame_y').fireEvent("reload", "/report/e button y/?noheader&bid={bid}&lid={lid}&cid={cid}&closecode=555");
			}
			$('frame_c').fireEvent("reload", "/report/e button c/?noheader&bid={bid}&lid={lid}&cid={cid}&closecode=555");
			return;
		}
		
		// set that we had a traffic complaint file if the last bid was 0
		if( ebindr.current.lastbid == 0 ) ebindr.current.lastdba = 'the traffic complaint file';
		// check to see if we are transfering
		if( ebindr.current.lastbid != ebindr.current.bid && ebindr.current.transfer ) {
			ebindr.current.cid=ebindr.current.transfercid;
			// confirm that they want to do this
			ebindr.modal.confirm( ebindr.messages.transfer.substitute(ebindr.current), function(retVal) {
				if( retVal ) 
					$('frame_c').fireEvent('reload',"/report/e button ct.editr/?noheader&bid={bid}&lid={lid}&cid={cid}&closecode=555&cmd=transfer");
				else {
					ebindr.growl("Complaint Transfer", "Complaint transfer cancelled", false, "orange");
					ebindr.current.cid=0;
					if($('frame_customerexperience')) {
						$('frame_customerexperience').fireEvent("reload", "/report/e button customerexperience/?noheader&bid={bid}&lid={lid}&cid={cid}&closecode=555");
						$('frame_x').fireEvent("reload", "/report/e button x/?noheader&bid={bid}&lid={lid}&cid={cid}&closecode=555");
						$('frame_z').fireEvent("reload", "/report/e button z/?noheader&bid={bid}&lid={lid}&cid={cid}&closecode=555");
						$('frame_y').fireEvent("reload", "/report/e button y/?noheader&bid={bid}&lid={lid}&cid={cid}&closecode=555");
					}
					$('frame_c').fireEvent('reload',"/report/e button c/?noheader&bid={bid}&lid={lid}&cid={cid}&closecode=555");
				}
			})
		} else {
			if($('frame_customerexperience')) {
				$('frame_customerexperience').fireEvent("reload", "/report/e button customerexperience/?noheader&bid={bid}&lid={lid}&cid={cid}&closecode=555");
				$('frame_x').fireEvent("reload", "/report/e button x/?noheader&bid={bid}&lid={lid}&cid={cid}&closecode=555");
				$('frame_z').fireEvent("reload", "/report/e button z/?noheader&bid={bid}&lid={lid}&cid={cid}&closecode=555");
				$('frame_y').fireEvent("reload", "/report/e button y/?noheader&bid={bid}&lid={lid}&cid={cid}&closecode=555");
				$('frame_c').fireEvent('reload',"/report/e button c - new/?noheader&bid={bid}&lid={lid}&cid={cid}&closecode=555");
			} else
			$('frame_c').fireEvent('reload',"/report/e button c/?noheader&bid={bid}&lid={lid}&cid={cid}&closecode=555");
		}
		// set the general and member frames
		$('frame_g').fireEvent('reload',"/report/lite button bg/?noheader&bid={bid}&lid={lid}");
		$('frame_m').fireEvent('reload',"/report/e button m/?noheader&bid={bid}&lid={lid}");
		// set the bid, cid, and transfer
		if(ebindr.current.transfer>0) ebindr.current.transfergrowl.options.closeFunction();
		ebindr.current.transfer = false;
//		ebindr.current.cid = 0;
	
	},
	
	refreshdata: function() {
		if( typeof(ebindr.current.editr) == "undefined" ) ebindr.current.editr = '';
		switch( ebindr.current.editr.replace('lite ','').replace(".editr","") ) {
			case "button c+.ADV.editr":
			case "button c+.ADV":
			case "button c+.editr":
			case "button c+":
			case "button cn.editr":
			case "button cn":
				var mycomplaintinsert = new Request.HTML().get( '/report/e button c+/?ebindr2=y&noheaderhidden&cid='+ebindr.current.cid );
				break;
			case "button b+.editr":
			case "button b+":
				var mybusinessinsert = new Request.HTML().get( '/report/e button b+/?ebindr2=y&noheaderhidden&bid='+ebindr.current.undo1 );
				break;
			case "button b-.editr":
			case "button b-":
				var mybusinessdelete = new Request.HTML().get( '/report/e button b-/?ebindr2=y&noheaderhidden&bid='+ebindr.current.bid );
				break;
			case "button bn":
			case "button ba":
			case "button bp":
			case "button bf":
			case "button bo":
			case "button bt":
			case "button bu":
			case "button b@":
			case "button bs":
			case "button bj":
			case "button bq":
			case "button bl":
			case "button bx":
			case "button be":
			case "button bd":
				ebindr.data.get( 'e button info' );
				break;
			case "button bg":
				$('frame_g').contentWindow.location.reload();
				break;
			case "button bh":
				ebindr.data.get( 'e button info' );//so ebindr.data.store variables will be updated also
				$('frame_m').contentWindow.location.reload();
				break;
			case "button m+.editr":
			case "button m-.editr":
			case "button m+":
			case "button m-":
			case "button mr":
			case "button mp.editr":
			case "button mp":
			case "button ml":
			case "button md":
			case "button ms":
				//$('frame_g').contentWindow.location.reload(); 
				ebindr.data.get( 'e button info' );//so ebindr.data.store variables will be updated also
				$('frame_m').contentWindow.location.reload();
				break;
			
		}
	},
	logstat: function(val, type, field) {
		var mylogstat = new Request.HTML().get( '/report/lite button logstat/?ebindr2=y&noheaderhidden&bindrwebvrs='+field+'&bid='+val+'&type='+type );
	},
	openFINDr2: function( which, inquirystat ) {
		if( $type( inquirystat )=='undefined' ) inquirystat=false;
		ebindr.current.lastInquiry=inquirystat;
		ebindr.current.auto_findr=which;
		ebindr.window.normal( 'e button findr', {
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
				$('more-list').setStyles({
					'left': $('more-search').getCoordinates().left - 310,
					'top': $('more-search').getCoordinates().top + 10
				});
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
		ebindr.window.normal( 'e button findr', {
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
	
	isNumber: function(n) {
		return !isNaN(parseFloat(n)) && isFinite(n);
	},
	
	refresh: function(message) {
		if( typeof(message) != 'string' ) {
			var message= "By clicking reload eBINDr will immediately reload and any unsaved changes will be lost. Click cancel to continue working without loading the newest eBINDr.";
		}
		ebindr.modal.confirm( message, function(retVal) {
    		if( retVal ) window.location.reload();
    	}, ['Reload','Cancel']);
	},
	copyToClipboard:function(text_to_copy){
		document.getElementById('copy-binfo2').innerHTML=ShowLMCButton(text_to_copy,'Copy','','http://hurdmantest.hurdman.org/ebindr/scripts/plugins/lmcbutton_copytoclipboard/lmcbutton.swf');
	}
	,
	copyToClipboard2:function(){
		new MooClip('#bcopy', {
			dataSource: function(target){
				return document.getElementById("bcopy_text").innerHTML;
			},
			onCopy:function(){
				alert('Company info copied!');
			}
		});
	},
	shortURL:function(urlorder,thebid,longurl){
		console.log('longurl:'+longurl);
		var shorter_text = '';
		var shorter_text_returned = '';
		var myRequest = new Request({
			url: 'http://hurdmantest.hurdman.org/ebindr/views/shorturl_solo.php',
			method: 'get',
			
			onSuccess: function(responseText){
				//responseText's content is just a plain text: e.g. bbb.org/h/q9
				shorter_text='http://www.'+responseText;
				shorter_text=shorter_text.replace(/(\r\n|\n|\r)/gm,"");
				
				console.log(shorter_text+' ('+urlorder+': '+thebid+')');
				
				var saveshorturl = new Request.JSON({
					url: '/report/merge/JSON.htm/?json=y&NOASK=&query=save%20shorturl&shortURL=' + shorter_text+'&thebid='+thebid,
					
					onComplete: function( parseData, data_string) {
						console.log(urlorder +': saveshorturl done');
					}.bind(this)
				});
				
				saveshorturl.send();
				
			}.bind(this)
		});
		 myRequest.send('bid='+thebid+'&fullurl=' + longurl);
		 //return shorter_text_returned;
		 return shorter_text; //doesn't work - only 'link:' is returned
		 
	},
	shortURLallABs:function(){
		//call shortlurl_solo_all.php
		//var staff_initials = window.parent.ebindr.data.store.staff_initials;
		var staff_initials = ebindr.data.store.staff_initials;
		
		console.log(staff_initials+', shortURLallABs - started');
		
		var myRequest = new Request({
			url: 'http://hurdmantest.hurdman.org/ebindr/views/shorturl_solo_all.php',
			method: 'get',
			onProgress: function(event, xhr){
				var loaded = event.loaded, total = event.total;
				console.log('shortURLallABs: ' + parseInt(loaded / total * 100, 10));
				alert("Generating shortURLs may take a few minutes. Please wait...");
				
			},
			onSuccess: function(responseText){
				console.log(staff_initials+', shortURLallABs - onSuccess');
				//alert("All ABs now have their shortURLs.<br/>Just click the 'All ABs' tab again to refresh list.");
				alert(responseText+ "<br/>Just click the 'All ABs' tab again to refresh list.");
				
			}.bind(this)
		});
		myRequest.send('staff='+staff_initials);
		 
		console.log(staff_initials+', shortURLallABs - eof');
	},
	shortURLshow:function(){
		
	},
	returnShortURL:function(shorturl){
		return shorturl;
	}

});
