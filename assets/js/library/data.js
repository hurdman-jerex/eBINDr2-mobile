

ebindr.library.data = new Class({

	// hidethem = ebindr.window.minimizeAll()
	// editr = ebindr.window.normal( button )
	
	preloadPointer: 0, // where the preload query processing is at
	preloadSQL: [
		'e button info2',
		'e button info',
		'e button info2 - custom',
		//'e button dr',
		//'e favorite reports.list',
		//'e recent reports.list',
		//'tickettrackr auth',
		//'/ebindr/community.php/features/popular',
		'e findr s'
	],

	options: {
		
	},
	
	store: $H(), // new hash to store all the values

	initialize: function( options ) {
		$extend(this.options,options);
		
		// log that this class has been loaded
		ebindr.console( 'ebindr.data loaded' );
	},
	
	/*
		Update a button or piece of data
	*/
	update: function( button, value ) {
		// find out which button it is
		switch( button ) {
			case "ms": 
			
				var isCharity = ( ebindr.data.store.reportcode == 'char' ? true : false );
				var isBusiness = !isCharity;
				var isMember = ( ebindr.data.store.member == 'y' ? true : false );
				
				var isPending = ( ebindr.data.store.pending == 'y' ? true : false );
				var isSealMember = ( ebindr.data.store.sealmember == 'y' ? true : false );
				
				//var isCharAccredited = ( value == 'standardmet' ? true : false ); //passed the 20 standards
				var isCharAccredited = ( ebindr.data.store.standardmet=='yes' ? true : false );
				
				var isAgency = ( ebindr.data.store.entity == 'agcy' ? true : false );
				var isCommunityMember = ( ebindr.data.store.communitymember == 'y' ? true : false );
				var isCharityReport = ( ebindr.data.store.charityreport == 'y' ? true : false );

				if( isCharity && !isMember ) $(button).addClass('charity');
				else $(button).removeClass('charity');
				ebindr.data.store.doug = value;
				// make sure this is gone
				$(button).removeClass('nonac');
				
				/*new Charity status logic*/
				//console.log('isCharity: '+isCharity + '('+ebindr.data.store.reportcode+')');
				//console.log('isCharAccredited: '+isCharAccredited + '('+value+')');
				//console.log('standardmet: '+ebindr.data.store.standardmet);
				//console.log('isMember: '+isMember+'('+ebindr.data.store.member+')');
				//console.log('isPending: '+isPending+'('+ebindr.data.store.pending+')');
				//console.log('isSealMember: '+isSealMember+'('+ebindr.data.store.sealmember+')');
				
				$(button).removeClass('charity');
				if(isCharity){

					$(button).erase('class');
						
					$(button).set('class','block');
					$(button).addClass('status');

					if(isCommunityMember){//add community member per ticket#1402331174
						//business.member = 'y' and charity.communitymember = 'y' 
						if(isMember && !isAgency && isCharityReport){
							//console.log('isCommunityMember: '+isCommunityMember+' : '+ebindr.data.store.communitymember);
							//console.log('isCharityReport: '+isCharityReport+' : '+ebindr.data.store.charityreport);
							//console.log('check 1 - community');
							$(button).addClass('char_ac_mem');
							$(button).set( 'html', 'BBB Community Member (<u>s</u>)' );
						}
					}else{
						if(isCharAccredited){//met 20 standards
							//$(button).removeClass('char_nonac');
							//$(button).removeClass('char_seal');
							
							$(button).erase('class');
							
							$(button).set('class','block');
							$(button).addClass('status');
							
							if(!isMember){
								if(isSealMember){
									$(button).addClass('char_ac_nonmem_seal');
									$(button).addClass('char_seal');
									$(button).set( 'html', 'BBB Accredited Non-Member Seal Charity (<u>s</u>)' );
								}else{
									$(button).addClass('char_ac_nonmem');
									$(button).set( 'html', 'BBB Accredited Non-Member Charity (<u>s</u>)' );
								}
								ebindr.current.isAB=false;//member=n
							}else
							if(isMember){
								if(isPending){
									if(isSealMember){
										$(button).addClass('char_ac_pendingmem_seal');
										$(button).addClass('char_seal');
										$(button).set( 'html', 'BBB Accredited Pending Member Seal Charity (<u>s</u>)' );
									}else{
										$(button).addClass('char_ac_pendingmem');
										$(button).set( 'html', 'BBB Accredited Pending Member Charity (<u>s</u>)' );
									}
									ebindr.current.isAB=false;//member=n
								}else{
									if(isSealMember){ 
										$(button).addClass('char_ac_mem_seal');
										$(button).addClass('char_seal');
										$(button).set( 'html', 'BBB Accredited Member Seal Charity (<u>s</u>)' );
									}else{
										$(button).addClass('char_ac_mem');
										$(button).set( 'html', 'BBB Accredited Member Charity (<u>s</u>)' );	
									}
									ebindr.current.isAB=true;//member=y
								}
							}
							//ebindr.current.isAB=true; //accredited charity
						}else{//non AC
							/*
							$(button).removeClass('char_seal');
							Array.each(['char_ac_nonmem_seal', 'char_ac_nonmem', 'char_ac_pendingmem_seal','char_ac_pendingmem','char_ac_mem_seal','char_ac_mem'], 
							function(ms_class, index){
								//console.log(index+ ' - ' + ms_class);
								$(button).removeClass(ms_class);
							});
							*/
							$(button).erase('class');
							
							$(button).set('class','block');
							$(button).addClass('status');
							
							$(button).addClass('char_nonac');
							$(button).set( 'html', 'Non Accredited Charity (<u>s</u>)' );

							ebindr.current.isAB=false;
						}
					}//!isCommunityMember
					
				}else{/*all of these previous code*/
					$(button).removeClass('char_seal');
					$(button).removeClass('char_nonac');

					Array.each(['char_ac_nonmem_seal', 'char_ac_nonmem', 'char_ac_pendingmem_seal','char_ac_pendingmem','char_ac_mem_seal','char_ac_mem'], 
					function(ms_class, index){
						//console.log(index+ ' - ' + ms_class);
						$(button).removeClass(ms_class);
					});
			
					if( value.toLowerCase() == 'no' ) {//else
						$(button).addClass( 'non' );
						$(button).removeClass( 'pending' );
						$(button).set( 'html', 'Non Accredited ' + ( ebindr.data.store.reportcode == 'char' ? 'Charity (<u>s</u>)' : 'Busines<u>s</u>' ) );
						ebindr.current.isAB=false;
					} /*
					else if( value.toLowerCase() == 'charity-accredited' ) {//not found in mergecode
						$(button).removeClass( 'pending' );
						$(button).removeClass( 'non' );
						$(button).set( 'html', 'BBB Accredited '+( ebindr.data.store.sealmember == 'y' ? 'Seal ' : '' )+'Charity (<u>s</u>)' );
						ebindr.current.isAB = false;
					}*/
					else if( value.toLowerCase() == 'pending' ) {//pending='y'
						$(button).addClass( 'pending' );
						$(button).removeClass( 'non' );
						$(button).set( 'html', 'Pending Accreditation (<u>s</u>)' );
						ebindr.current.isAB = false; // this used to be true changed per #1365169853 
					} else { //value = yes
						$(button).removeClass( 'non' );
						$(button).removeClass( 'pending' );
						if( ebindr.data.store.reportcode == 'char' ) {
							/*
							if( value.toLowerCase() == 'yes' ) { //based on window.parent.ebindr.data.update() call on [e button ms] - 6/1/2013
								$(button).set( 'html', 'BBB Accredited '+( ebindr.data.store.sealmember == 'y' ? 'Seal ' : '' )+'Charity (<u>s</u>)' );	
							}else{
								// display the blue bar with a red x cause they aren't an accredited charity even though they are paying
								$(button).addClass('nonac');
								$(button).set( 'html', 'Non Accredited ' + ( ebindr.data.store.sealmember == 'y' ? 'Seal ' : '' ) + 'Charity (<u>s</u>)' );
							}
							*/
						} else {
							$(button).set( 'html', 'BBB Accredited ' + ( ebindr.data.store.reportcode == 'char' ? ( ebindr.data.store.sealmember == 'y' ? 'Seal ' : '' )+'Charity (<u>s</u>)' : 'Busines<u>s</u>' ) );
						}

						if(isAgency && isMember){//community member per ticket#1402331174
							//business.entity = 'agcy' and business.member = 'y' 
							//console.log('isCommunityMember: '+isCommunityMember+' : '+ebindr.data.store.communitymember);
							//console.log('isCharityReport: '+isCharityReport+' : '+ebindr.data.store.charityreport);
							//console.log('check 2 - community');
							$(button).set( 'html', 'BBB Community Member (<u>s</u>)' );
						}

						ebindr.current.isAB=true;
					}
					if( isMember ) ebindr.current.isAB = true;	//accredited business

					//still check if its valid for community - 10/15/2014
					//if(isMember && isCommunityMember){//business.member = 'y' and charity.communitymember = 'y' 
					if(isMember && !isAgency && isCommunityMember && isCharityReport){
						//console.log('isCommunityMember: '+isCommunityMember+' : '+ebindr.data.store.communitymember);
						//console.log('isCharityReport: '+isCharityReport+' : '+ebindr.data.store.charityreport);
						//console.log('check 3 - community');
						$(button).addClass('char_ac_mem');
						$(button).set( 'html', 'BBB Community Member (<u>s</u>)' );
					}

				}//else if not Charity
			
				//$(button).getElements('span')[0].set('html', 'Accredited: ' + value );
				if( !isMember ) {
					ebindr.current.isAB = false;
					ebindr.data.update( 'mn', '<span><u>N</u>ew</span>' );
				}else{
					ebindr.current.isAB = true;
					this.update( 'mn', '<span>Ca<u>n</u>cel</span>' );	
				} 
				
			break;
			case "mn":
			
				$(button).set('html', value );
				if( value.replace(/(<([^>]+)>)/ig,"") == 'New' ) $(button).removeClass( 'cancel' ).addClass( 'new' );
				else $(button).removeClass( 'new' ).addClass( 'cancel' );
				
			break;
		}
	},
	
	/*
		This method will read the data hash returned by the query.
		There is also a switch to do different things with the data
	*/
	load: function( query, data ) {
		// to handle the register query
		if( query == 'e button dr' ) {
			ebindr.data.registers( data );
		// popular queue items
		} else if( query == '/ebindr/community.php/features/popular' ) {
		
			ebindr.data.queue( data );
		// load training center
		} else if( query == 'training.CourseSummary' ) {
			ebindr.data.trainingCenter(data);
		// favorite common reports
		} else if( query == 'e favorite reports.list' ) {
			ebindr.data.favoriteReports(data);
		
		// Custom FINDr searches
		} else if( query == 'e findr s' ) {
			ebindr.data.customFINDr(data);
		
		// the recent register update
		} else if( query == 'e recent reports.list' ) {
			ebindr.data.recentRegisters(data);
		} else {
			// get the current width to see if we need to do any trimming
			var framewidth = $(document).getCoordinates().width;
			// go through each element in the hash
			data.each( function( el ) {
				$extend($H(),el).each( function( value, key ) {
					// add it to the store
					ebindr.data.store[ key ] = value;
					// added things to do to the data
					if( key.substring(0,7) == "button_" ) {
						// remove the red color
						if( $chk(value) ) {
							// for the url button
							if( key == 'button_bu' ) {
								// make sure it isn't too long
								var nohtml = value.replace(/<[^>]*>?/g, "");
								var url = nohtml;
								if( nohtml.length > 23 && framewidth < 915 ) var shorter = nohtml.substring(0,20) + '...';
								else var shorter = nohtml;
								value = value.replace( nohtml, shorter ).toLowerCase();
								value = '<a href="' + ( url.match( '^http' ) ? '' : 'http://' ) + url + '" title="http://' + value + '" target="_blank" class="external-link">&nbsp;</a>' + value;
							} else if( key == 'button_b@' ) {
								var nohtml = value.replace(/<[^>]*>?/g, "");
								var email = nohtml.toLowerCase();
								// make sure it isn't too long
								if( nohtml.length > 23 && framewidth < 915 ) email = nohtml.substring(0,20) + '...';
								else email = nohtml;
								
								value = value.replace( nohtml, email ).toLowerCase();
								if( ebindr.data.store.usesgoogleapps == 'No' ) {
									value = '<a href="mailto:' +nohtml +'" title="' +value+ '" class="external-link">&nbsp;</a>' + value;
								} else {
									// https://mail.google.com/mail/?view=cm&fs=1&tf=1&to=
									value = '<a href="/ebindr/mailto.html#mailto:' + nohtml + '" title="' + value + '" target="_blank" class="external-link">&nbsp;</a>' + value;
								}
							} else if( key == 'button_bt' ) {
								var wohtml = value.replace(/<.*?>/g, '');								
								if( wohtml.length > 50 && framewidth < 915 ) {
									value = value.replace( wohtml, wohtml.substring(0,50) + '...' );
								}
							}
						}
						if( $( key.split("_")[1] ) ) $( key.split("_")[1] ).set( 'html', value );
					};
					if( key.substring(0,4) == 'css_' && !$('info2_'+key) ) {
			            var el = new Element('style', { 
						'id': 'info2_'+key,
            			    'type': 'text/css',
			                'text': value
			            }).inject(document.head); 
					};
					if(query=="e button info" && key.substring(0,3) == 'js_') {
						if($('info_'+key)) $('info_'+key).dispose();
						var el = new Element('script', { 
							'id': 'info_'+key,
								'type': 'text/javascript',
								'text': value
							}).inject(document.head); 
					} else {
						if( key.substring(0,3) == 'js_' && !$('info2_'+key) ) {
						var el = new Element('script', { 
							'id': 'info2_'+key,
								'type': 'text/javascript',
								'text': value
							}).inject(document.head); 
						};
					}
					if( key.substring(0,12) == 'buttoncolor_' ) {
						if( $( key.split("_")[1] ) ) {
							if( value.length > 0 ) $( key.split("_")[1] ).addClass(value);
							else $( key.split("_")[1] ).removeClass('red');
						}
					};
					if( key.substring(0,12) == "buttonshade_" ) {
						//alert( key.split("_")[1] + ' ' + value );
//						if( $( key.split("_")[1] ) ) {
							ebindr.button.shade( key.split("_")[1], value );
//						}
					};
				});
			
			});
			
			// some specific code to run after the data has been loaded
			switch( query ) {
				case "e button info2":
					//ebindr.current.bid = this.store.bid;
					if( ebindr.autoloadbid ) {
						ebindr.openBID( ebindr.autoloadbid, true );
						ebindr.autoloadbid = false;
					} else {
						if( !ebindr.current.switchedbid ) ebindr.openBID( this.store.bid, true );
						edittitlecase = this.store.edittitlecase;
					}
								
					// login alert
					if( this.store.loginalert.length > 0 && !ebindr.shownloginalert ) {
						ebindr.alert( this.store.loginalert, 'Remember' );
						//ebindr.growl( 'Remember', this.store.loginalert, true, 'black' );
						ebindr.shownloginalert = true;
					}
					break;
				case "e button info":
					if( ebindr.data.store['forcepasscodechange'] == 'y' ) {
						(function() {
							ebindr.button.editr( 'lite button password.editr' );
						}).delay(5000);
					}
					ebindr.current.complaintBID = this.store.complaintBID;
					var myTips = new Tips('#bs span', {
						className: 'misc-tip',
						onShow: function(tip) {
							tip.setStyle( 'opacity', 0.75 );
							tip.setStyle('display', 'block');
						},
						offsets: { x: -25, y: 25 },
						fixed: true
					});

					var binfo_copy = ebindr.data.store.button_bo+'\r\n';
					binfo_copy += ebindr.data.store.copy_primary_dba+'\r\n';
		
					binfo_copy += ebindr.data.store.copy_primary_address+'\r\n';

					binfo_copy += ebindr.data.store.button_bp+'\r\n';
					binfo_copy += ebindr.data.store.button_bf+'\r\n';
					binfo_copy += ebindr.data.store.copy_primary_email;
					
					//<br />&nbsp; \r\n
					

					binfo_copy = binfo_copy.replace('undefined','');
					binfo_copy = binfo_copy.replace(undefined,'');
					binfo_copy = binfo_copy.replace(null,'');
					binfo_copy = binfo_copy.replace('null','');
					
					binfo_copy = binfo_copy.replace('<br>','\r\n');
					binfo_copy = binfo_copy.replace('<br/>','\r\n');
					binfo_copy = binfo_copy.replace('<br />','\r\n');
					
					if($('bcopy_text')) $('bcopy_text').set('html', binfo_copy);
					//ebindr.copyToClipboard(binfo_copy);
					break;
			}
		}
	
	},
	
	preload: function( callback ) {
			
		// go through each query and when it's done move to the next one
		this.get( this.preloadSQL[this.preloadPointer], function(data) {
			console.log( data );
			if( data != 'empty' ) {
				// read in the data
				ebindr.data.load( ebindr.data.preloadSQL[ebindr.data.preloadPointer], data );
			}
			// find the percentage
			//var completed = ebindr.images.length + (ebindr.data.preloadPointer);
			//var total = ebindr.images.length + (ebindr.data.preloadSQL.length);
			//var percent = (completed/total)*100;
			//var percent = ((ebindr.images.length+ebindr.data.preloadPointer+1) * (100 / ebindr.images.length + ebindr.data.preloadSQL.length));
			// move the progressbar along
			
			//ebindr.preloadprogress.set( percent.round() );
			//$('test-percent').set('text',percent.round() + '%');

			switch( ebindr.data.preloadSQL[ebindr.data.preloadPointer] ) {
				case "e button info2" : var text = 'Global Variables'; 
					if(ebindr.data.store.securitykeys.split(',')[2]=="") window.location.href="/logout.php";
					break;
				case "e button info" : var text = 'Business Data'; break;
				case "e button dr" : var text = 'Registers'; break;
				case "e favorite reports.list" : var text = 'Favorite Reports'; break;
				case "e recent reports.list" : var text = 'Recent Registers'; break;
				case "tickettrackr auth" : var text = 'Authenticating with TicketTRACKr'; break;
				case "/ebindr/community.php/features/popular" : var text = 'Retrieving Queue Items'; break;
				case "e findr s" : var text = 'Extended FINDr Searches'; break;
			}
			
			 
			// see if we are done the preloading of data
			if( (ebindr.data.preloadPointer+1) == ebindr.data.preloadSQL.length ) {
				// if we are and our callback is a function then execute it
				if( $type(callback) == 'function' ) callback();
				// set the pointer back to 0
				//$('test-percent').set('text','100%');
				ebindr.data.preloadPointer = 0;
			} else {
				// increment the pointer
				ebindr.data.preloadPointer++;
				// run it
				ebindr.data.preload( callback );
			}
		});

	},

	runQuery: function(query, gets) {
		if( typeof(gets) == 'undefined' ) gets = new Object();

		var newQuery = ( query.substr( 0, 8 ) == '/ebindr/' ? query : '/report/merge/JSON.htm/' );

		var abisEmpty = true;
    if (gets == null) abisEmpty = true;

    if (gets.length > 0) abisEmpty = false;
    if (gets.length === 0) abisEmpty = true;

    for (var key in gets) {
      if (hasOwnProperty.call(gets, key)) abisEmpty = false;
    }

    if (!abisEmpty) {
    	newQuery = newQuery + '?';
			for( var name in gets ) {
			  newQuery = newQuery + name + '=' + gets[name] + '&';
			}
    }

		var get = new Request.JSON({
			url: newQuery,
			nocache: true,
			onComplete: function( data, str ) {
				if( typeof(data) == 'object' && str != '//No data' ) {
					// log that we loaded a query
					ebindr.log( 'Query loaded: ' + query + ', the results were: ' + str );
					// run the callback
				} else {
				}
			},onSuccess: function(data){
		    return data;
			}
		}).get({
			'json': 'y',
			'NOASK': '',
			'query': query,
			'bid': ( $chk(ebindr.current.bid) ? ebindr.current.bid : ebindr.data.store.bid ),
			't': new Date().getTime()
		});
	},

	get: function( query, callback, options ) {
	
		if( typeof(options) == 'undefined' ) options = {};
	
		if( $type(callback) != 'function' ) {
			callback = function( data ) {
				ebindr.data.load( query, data );
			}
		}
		
		if( query == 'tickettrackr auth' ) {
			
			var get = new Request({
				url: '/ebindr/tickettrackr.php/b_ebindrauth.html',
				nocache: true,
				onComplete: function(text) {
					if( text.length > 3 ) {
						ebindr.log( 'Query loaded: ' + query + ', the results were: ' + text );
						
						// store the result
						ebindr.data.store.tickets = new Hash({
							'user': text.split("|")[0].replace(" ",""),
							'pass': text.split("|")[1].replace(" ",""),
							'id': text.split("|")[2].replace(" ",""),
							'main': text.split("|")[3].replace(" ","")
						});
						
						// login based on results
						Cookie.write( "HurdmanTicketTRACKr[user]", ebindr.data.store.tickets.user );
						Cookie.write( "HurdmanTicketTRACKr[pass]", ebindr.data.store.tickets.pass );
						Cookie.write( "HurdmanTicketTRACKr[id]", ebindr.data.store.tickets.id );
						Cookie.write( "HurdmanTicketTRACKr[main]", ebindr.data.store.tickets.main );
					}
					callback( 'empty' );
				}
			}).post({
				'ttemail': Cookie.read("staff_email"),
				'ttbbbid': Cookie.read("bbbidreal")
			});
			
		} else if( query == 'e button sort' ) {
		
			var get = new Request.JSON({
				url: ( query.substr( 0, 8 ) == '/ebindr/' ? query : '/report/merge/JSON.htm/' ),
				nocache: true,
				onComplete: function( data, str ) {
					if( typeof(data) == 'object' && str != '//No data' ) {
						// log that we loaded a query
						ebindr.log( 'Query loaded: ' + query + ', the results were: ' + str );
						// run the callback
						if( data !== null ) callback( data.resultset );
					} else {
						callback( 'empty' );
					}
				}
			}).get({
				'json': 'y',
				'NOASK': '',
				'query': query,
				'bid': ( $chk(ebindr.current.bid) ? ebindr.current.bid : ebindr.data.store.bid ),
				't': new Date().getTime(),
				'type': options.type
			});
			
		} else {
		
			if( query == 'e button info' ) {
				// clear out all business information
				/*$('bn').set('text','');
				$('bo').set('text','');
				$('bp').set('text','');
				$('bf').set('text','');
				$('b@').set('text','');
				$('bu').set('text','');
				$('ba').set('text','');
				$('bt').set('text','');
				$('bz-btn').set('text','');*/
			}
		
			var get = new Request.JSON({
				url: ( query.substr( 0, 8 ) == '/ebindr/' ? query : '/report/merge/JSON.htm/' ),
				nocache: true,
				onComplete: function( data, str ) {
					if( typeof(data) == 'object' && str != '//No data' ) {
						// log that we loaded a query
						ebindr.log( 'Query loaded: ' + query + ', the results were: ' + str );
						// run the callback
						if( data !== null ) callback( data.resultset );
					} else {
						callback( 'empty' );
					}
				}
			}).get({
				'json': 'y',
				'NOASK': '',
				'query': query,
				'bid': ( $chk(ebindr.current.bid) ? ebindr.current.bid : ebindr.data.store.bid ),
				't': new Date().getTime()
			});
		}
	},
	
	/* 
		Update the training center bar chart
	*/
	trainingCenter: function( data ) {

		var percent = Math.round(parseFloat(data[0]['Percent Complete']));
		
		/*$('training-progress-container').setStyle( 'height', (100-percent) + '%' )
		$('training-progress-label').set( 'text', new String(percent) + '%' );
		$('training-chart').setStyle( 'display', 'block' );*/

		
	},
	
	/*
		Set the recent registers
	*/
	recentRegisters: function( data ) {
		$('recent-registers').empty();
    	data.each( function(el, i ) {
    		new Element( 'a', {
    			events: {
    				'click': function(e) {
    					new Event(e).stop();
    					ebindr.button.editr( this.id );
    				}
    			},
    			id: el.mergecode,
    			href: '#',
    			text: ( typeof(el.display) != "string" ? '' : el.display.trim() )
    		}).inject( $('recent-registers' ) );
    	});
	},
	
	/*
		Set the FINDr Searches
	*/
	customFINDr: function( data ) {
    	/*
        data.each( function(el, i ) {
    		new Element( 'li', {
    			id: ( "findr-" + el.Menu ),
    			href: '#',
    			text: el.Menu,
				title: el.Description
    		}).inject( $('custom-findr' ).getChildren( 'ul' )[0] );
    	});*/
	},
	
	/*
		Get all the registers
	*/
	registers: function( data ) {
		var html = '<table cellpadding="0" cellspacing="0">';
    	html += '<tr><td colspan="2" class="recent-space top">Recently Used Registers</td></tr>';
    	html += '<tr><td colspan="2" id="recent-registers"></td></tr>';
    	html += '<tr><td colspan="2" class="recent-space">All Registers</td></tr>';
    	html += '<tr><td class="alphabet">*</td><td>';
    	var start = '*';
    	// go through the data
    	data.each( function(el, i) {
    		if( typeof el.key1 != 'undefined' ) {
    			if( typeof el.test == 'undefined' ) el.test = el.key1.replace("qvq ","");
    			if( el.test.trim().substr(0,1).toLowerCase() != start ) {
    				start = el.test.trim().substr(0,1).toLowerCase();
    				html += '</td></tr>';
    				html += '<td class="alphabet">' + el.test.trim().substr(0,1).toUpperCase() + '</td><td>';
    			}
    			html += '<a href="#" id="' + el.key1 + '">' + el.test.trim() + '</a>';
    		}
    	});
    	html += '</td></tr></table>';
    	/*$('registers').set( 'html', html );*/
    	$$( '#registers a' ).each( function( reg, i ) {
    		if( !reg.hasClass( 'alphabet' ) ) {
    			reg.addEvent( 'click', function(e) {
    				new Event(e).stop();
    				//ebindr.current.editr = this.id;
    				ebindr.button.editr( this.id );
    			});
    		}
    	});
	},
	
	/*
		Update the favorite reports
	*/
	favoriteReports: function( data ) {
		$$( '#f .button32.favorite' ).dispose();
    	if( data.length > 0 && data != 'empty' ) {
    		data.each( function(el, i) {
    			if( $chk(el.mergecode) && $chk(el.alias) && $chk(el.description) ) {
    				new Element( 'div', {
    					events: {
    						click: function(e) {
    							ebindr.window.normal( this.id, {
    								id: 'favorite-' + this.id,
    								contentURL: '/m/report/' + this.id + '/?ebindr2=y',
    								tacable: true
    							});
    						}
    					},
    					id: el.mergecode,
    					title: '<b>' + el.alias + '</b><br />' + el.description,
    					text: el.shortalias
    				}).addClass( 'button32' ).addClass( 'favorite' ).addClass( 'default' ).inject( $('f') );
    			}
    		});
    	}
    	new Tips('.button32.favorite', {
    		beforeText: '',
    		afterText: '',
    		className: 'favorite-tip',
    		offsets: { x: -50, y: 25 }
    	});
	},
	
	/*
		Update popular features and voting in the queue
	*/
	queue: function( data ) {
		/*var ul = new Element( 'ul', {
    		'class': 'top-queue'
    	});

    	document.ondragstart = function() { return false; }; // IE hack
    	
    	if( $chk(data.votes[0]) ) $('q-1').set('text', data.votes[0].title );
    	else $('q-1').set( 'text', '-' );
    	
    	if( $chk(data.votes[1]) ) $('q-2').set('text', data.votes[1].title );
    	else $('q-2').set( 'text', '-' );
    	
    	if( $chk(data.votes[2]) ) $('q-3').set('text', data.votes[2].title );
    	else $('q-2').set( 'text', '-' );

    	data.popular.each( function(item,i) {
    		var li = new Element( 'li', {
    			'html': '<img src="/ebindr/images/icons16x/qview.png" /><span class="item">' + item.title + ' (' + item.votes + ' votes)</span>',
    			'events': {
    				'mouseenter': function(e) {
    					this.addClass('over');
    				},
    				'mouseleave': function(e) {
    					this.removeClass('over');
    				}
    			}
    		}).inject( ul );
    		li.store( 'fid', item.fid );
    		
    		li.getElements('img')[0].addEvent( 'click', function() {
    			var fid = this.getParent().retrieve('fid');
    			var name = this.getParent().get('text');
    			ebindr.alert( 'file: /ebindr/community.php/features/get/' + fid, name );
    		});
    		
    		li.getElements('span')[0].addEvents({
    			'mousedown': function(e) {
					new Event(e).stop();
					var clone = this.clone().set('id','q-clone').setStyles(this.getCoordinates()).setStyles({
						'opacity': 0.7,
						'position': 'absolute'
					}).inject(document.body);
					clone.store('text',clone.get('text'));
					clone.store( 'fid', li.retrieve('fid' ) );
					$$('.q-drop').addEvent( 'populate', function() {
						this.set( 'text', clone.retrieve('text') );
						var position = this.id.replace("q-","");
						var tmp = new Request({
							url: '/ebindr/community.php/features/vote',
							onComplete: function() {
								ebindr.data.get( '/ebindr/community.php/features/popular' );
							}
						}).post({
							'fid': clone.retrieve('fid'),
							'position': position
						});
						
					});
					var drag = new Drag.Move( clone, {
						droppables: '.q-drop',
		    			onDrop: function(el,droppable) {
		    				if( droppable ) droppable.fireEvent('populate');
		    				this.detach();
		    				clone.dispose();
		    			},
		    			onLeave: function(el,droppable) {
		    				droppable.set( 'text', droppable.retrieve('text') );
		    			},
		    			onEnter: function(el,droppable) {
		    				droppable.store( 'text', droppable.get('text') );
		    				droppable.set('text','');
		    			}				
					});
					drag.start(e);
					
				},
				'mouseup': function(e) {
					if( $('q-clone') ) $('q-clone').dispose();
				}
    		});
    	});
    	(function() {
    	$('popular-queue').empty();
    	ul.inject( $('popular-queue') );			
    	}).delay(1000);
    	*/
	}

});
