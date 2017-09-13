ebindr.library.keyboard = new Class({

	Implements: [Events, Options],
	options: new Hash(),// the tab options	
	initialize: function (options) {
		$extend(this.options, options);
		// log that this class has been loaded
		ebindr.console('ebindr.keyboard loaded');
		// listen to the keystrokes
		this.listen();
	},	
	listen: function () {		
		this.keydown = function (event, editr) { 
			
			if(event.keyCode == 40) { 
				console.log(document.activeElement);
			}

			if( $('alert-box') && (event.keyCode == 27 || event.keyCode == 32 || event.keyCode == 13) ) {
				ebindr.window.library.closeWindow( $('alert-box') );
			}
			
			if (ebindr.authenticated) {
				if (typeof(editr) == "undefined") { 
					editr = ''; 
				}
				// make the user as active cause they have used the keyboard
				ebindr.user.setActive();
				//if( Browser.Engine.trident )
				//	var tag = ( $(ebindr.current.focus) ? ebindr.current.focus.get('tag') : 'non' );
				//else {
				// let's make sure that we aren't focused in a form element
				var tag = ($(event.target) ? $(event.target).get('tag') : 'non');
				// if we didn't find a tag
				
				if( tag == 'non' || typeof($(event.target)) == 'null' ) {
					if( $(ebindr.current.focus) ) tag = ebindr.current.focus.get('tag');
				}
	
				if( ebindr.findr2.typing ) tag = 'input';
				//}
				var editing_field = ['input','select','textarea'].contains(tag);
				if( $(event.target) && $(event.target).id=="keydownfield") {
					//if( event ) event.stop();
					editing_field=false;
				}
				//if( !['input','select','textarea'].contains(tag) ) {
				event = new Event(event);
				//if( Browser.Platform.mac ) event.control = event.meta;	
				if( event.control ) ebindr.keyboard.control = true;
				
				ebindr.console('KEY PRESSED ' + event.key +' KEYCODE '+ event.code +' ' + ebindr.current.frame);
				//control T shortcut
				if( event.key == 't' && event.code == '84' ){					
//					alert('do not open new tab');
					
					//var iframeid = ebindr.window.library.focusedWindow.options.id + '_iframe';
					//var iframe = ebindr.window.iframe( ebindr.window.library.focusedWindow.options.id + '_iframe' );	
					ebindr.console( 'focused window? ' + ebindr.window.library.focusedWindow );
					ebindr.console( 'current focus? ' + ebindr.current.focus );
					//ebindr.console( 'Frame ' + ebindr.current.page );
					//event.stop();
				}
				
				if( event.control && event.shift && editing_field ) {
					if( $(event.target).hasClass('date') ) {
						var today = new Date();
						var year = parseFloat(today.getFullYear());
						var day = ( parseFloat(today.getDate()) < 10 ? '0' + today.getDate() : today.getDate() );
						var month = ( parseFloat(today.getMonth()) < 9 ? '0' + new String(parseFloat(today.getMonth())+1) : parseFloat(today.getDate())+1 );
						if( event.key == 't' ) {
							$(event.target).value = month + '/' + day + '/' + today.getFullYear();
							$(event.target).value = month + '/' + day + '/' + today.getFullYear();
						} else if( event.key == '1' ) {
							$(event.target).value = month + '/' + day + '/' + (year+1);
						} else if( event.key == '2' ) {
							$(event.target).value = month + '/' + day + '/' + (year+2);
						}
						
						
						//05/01/2011
					}
					event.stop();
				} 
				
				if( ebindr.current.modal && !ebindr.current.winview && !ebindr.current.prompt ) {
					event.stop();
					// for the alerts
					if( $('confirm-box' ) ) {
						$$( '#confirm-box-buttons input[type=button]' ).each( function(button) {
							if( ( button.value == 'Yes' || button.hasClass('true') ) && event.key == 'y' ) button.fireEvent( 'click' );
							else if( ( button.value == 'No' || button.hasClass('false') ) && event.key == 'n' ) button.fireEvent( 'click' );
							else if( button.value == 'Cancel' && (event.key == 'c' || event.key == 'esc') ) button.fireEvent('click');
						});
					}
					// for the alert() method
					if( $('alert-box') ) {
						if( event.key == 'esc' || event.key == 'space' || event.key == 'enter' ) ebindr.window.library.closeWindow( $('alert-box') );
					} 
					//payment in input esc trigger
					else if( event.key == 'esc' && ebindr.window.library.focusedWindow ) {
						event.stop();
						var iframeid = ebindr.window.library.focusedWindow.options.id + '_iframe';
						ebindr.button.toolbar.escape( ebindr.window.library.focusedWindow , ebindr.window.iframe(iframeid) );
					}					
					//payment in input esc trigger end
				// not a modal window
				} else {
					//(&& editr.length > 0) -- bec. of this condition, the ESC shortcut wont work when the title bar is the focus
					//editr = is the actual <iframe> that usually gets tobe focused
					// for tab switching
					if( event.key == 'esc' && ebindr.window.library.focusedWindow ) {
						// close the editr
                        
						event.stop();
						var iframeid = ebindr.window.library.focusedWindow.options.id + '_iframe';
                        //alert(iframeid);
						//ebindr.button.toolbar.escape( ebindr.window.library.focusedWindow, ebindr.window.iframe(iframeid) );
						ebindr.button.toolbar.escape( ebindr.window.library.focusedWindow , ebindr.window.iframe(iframeid) );
					// otherwise try to logout
					} else if( event.key == 'space' && !editing_field && tag!="a" ) 
						ebindr.button.go( 'by' );
					else if(event.code == 45) { 
						//console.log('PRESSED INSERT --- show Add Journal Entry window ');
						var iframe = ebindr.window.iframe( ebindr.window.library.focusedWindow.options.id + '_iframe' );						
						iframe.document.location.href = ebindr.button.toolbar.insert(ebindr.window.iframe(iframeid));
						//console.log('done calling insert');
					}
				
					//} else if( event.key == "esc" ) $( "logout" ).fireEvent('click');
					// watch for findr
                    if( ! editing_field && event.code == 191 ) {
                        ebindr.button.go('b?');
                        event.stop();
                    }
                    
					if( !editing_field && event.control ) {
						switch( event.key ) {
							case "d": var tab = 'database'; break;
							case "r": var tab = "records"; break;
							case "m": var tab = "community"; break;
							case "f": if( ebindr.isHurdman() ) ebindr.findr2(); break;
						}
						// make sure we have a tab to fire
						if( $chk(tab) ) {
							new Event(event).stop();
							ebindr.current.page = tab;
							$( 'tab-' + tab ).fireEvent( 'click' );
						}
					} else {
						// watch for F5 reload
						if( event.key == 'f9' ) {
							ebindr.button.go('f 9');
							event.stop();
						}
						if( event.key == 'f5' ) {
							new Event(event).stop();
							event.stop();
							ebindr.refresh( 'Are you sure you want to reload eBINDr?' );
							/*ebindr.modal.confirm( 'Are you sure you want to reload eBINDr?', function(retVal) {
								if( retVal ) window.location.reload();
							}, ['Reload','Cancel']);*/
						}
						// if we press the enter key then fire the button action of the button we are focused on
						if( !editing_field && event.key == 'enter' && $(ebindr.current.focus) ) ebindr.current.focus.fireEvent('button');
						// don't use the tab switching key to select one of the frames
						if( !event.control ) {
							if( !editing_field && /* added preceding clause 4/25/2011 because occasionally field editing was getting disabled */ !ebindr.window.library.focusedWindow && !ebindr.current.modal ) {
								if( !event.meta ) {
									event.stop();
									ebindr.keyboard.frame(event.key); 
								
								}
							}
						}
                        // watch for email address @
                        if( !editing_field && $(ebindr.current.frame.button + (event.code == 50?'@':null) ) && editr.length < 1) {
                            ebindr.button.go( 'b@' );
                            event.stop();
                        }
			
						// individual buttons, fire event
						if( !editing_field && $(ebindr.current.frame.button + event.key) && editr.length < 1 ) { 
							ebindr.button.go( ebindr.current.frame.button + event.key );
							//$(ebindr.current.frame.button + event.key).fireEvent( 'click' );
						}
					}
				}
			}
		}
		
		var object = ( Browser.Engine.trident ? $('ebindr') : window );
		object.addEvent( 'keydown', this.keydown );
		//object.addEvent( 'keyup', this.keyup );
	},
	
	frame: function( key ) {
		// log what frame was focused
		//ebindr.console( 'Frame ' + ebindr.current.page + '.' + key + ' focused' );
		ebindr.log( 'Frame ' + ebindr.current.page + '.' + key + ' focused' );
		// listen for frame buttons
		switch( ebindr.current.page ) {
			case "records":
				switch( key ) {
					// frames
					case "b": ebindr.current.frame = { name: 'business', button: key }; break;
					case "i": ebindr.current.frame = { name: 'inquiry', button: key }; break;
					case "m": ebindr.current.frame = { name: 'membership', button: key }; break;
					case "c": ebindr.current.frame = { name: 'complaint', button: key }; break;
				}
			break;
			case "database":
				switch( key ) {
					// frames
					case "f": ebindr.current.frame = { name: 'favoritereports', button: key }; break;
					case "r": ebindr.current.frame = { name: 'myregisters', button: key }; break;
					case "d": ebindr.current.frame = { name: 'database', button: key }; break;
					case "a": ebindr.current.frame = { name: 'admin', button: key }; break;
					case "u": ebindr.current.frame = { name: 'customreports', button: key }; break;
					case "v": ebindr.current.frame = { name: 'vrs', button: key }; break;
				}
			break;
			case "community":
				switch( key ) {
					// frames
					case "q": ebindr.current.frame = { name: 'queue', button: key }; break;
					case "n": ebindr.current.frame = { name: 'connect', button: key }; break;
					case "e": ebindr.current.frame = { name: 'collaborate', button: key }; break;
				}
			break;
		}
		// set which box is active
		if( $( ebindr.current.frame.button ) ) {
			// clear all active classes
			$$( 'div.frame.active' ).removeClass( 'active' );
			// set the active one
			$( ebindr.current.frame.button ).addClass( 'active' );
		}
	}
	
});
