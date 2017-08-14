// add security/authentication methods/functions to ebindr
ebindr.extend({

	communityUser: function() {
	
		new Request.JSON({
			url: '/ebindr/community.php/user/get',
			onComplete: function(data) {
				if( data ) {
				// see if we need to prompt
				if( data.prompt == 'username' ) {
					ebindr.modal.prompt( 'Please choose a name to display in all community activities such as chats and the forum', 'We need some info...', function( retVal ) {
						if( retVal == '' || !retVal ) ebindr.communityUser();
						else {
							new Request.JSON({
								url: '/ebindr/community.php/user/get',
								onComplete: function(data,str) {
									// log the community user data
									ebindr.log( 'Community User Data: ' + str );
									
									$extend($H(),data).each( function(value,key) {
										Cookie.write( key, value );
										ebindr.data.store[key] = value;
									});
								}
							}).post({
								'username': retVal,
								"key": window.parent.ebindr.key,
								"id": window.parent.ebindr.id,
								"bbbid": Cookie.read( "bbbidreal" ),
								"initials": Cookie.read("reportr_username")
							});
						}
					});
				} else {
					$extend($H(),data).each( function(value,key) {
						Cookie.write( key, value );
						ebindr.data.store[key] = value;
					});
				}
				}
			}
		}).post({
			"key": window.parent.ebindr.key,
			"id": window.parent.ebindr.id,
			"bbbid": Cookie.read( "bbbidreal" ),
			"staffname": Cookie.read( "staff_name" ),
			"initials": Cookie.read("reportr_username")
		});
	
	},

	/*
		This method will return boolean true or false if the person is logged in.
		If they are not logged in, it will use the call back function
	*/
	authenticate: function( options ) {
		if( typeof(options) == 'undefined' ) var options = {};
		// set the options
		$extend({
			onFalse: function() { return false; },
			onSuccess: function() { return true; },
			serverAuth: false
		}, options);
		
		// make sure the right cookies are set
		if( $chk(Cookie.read( "reportr_username" )) && $chk(Cookie.read( "reportr_conf" )) && $chk(Cookie.read( "reportr_keys")) ) {
			// make sure we should authenticate against the server
			if( options.serverAuth ) {
				// validate against some server logic
				if( this.serverAuthenticate() ) {
					ebindr.authenticated = true;
					if( $type(options.onSuccess) == 'function' ) {
						options.onSuccess();
					}
					return true;
				} else {
					ebindr.authenticated = false;
					if( $type(options.onFalse) == 'function' ){
						options.onFalse();
					}
					return false;
				}
			} else {
				ebindr.authenticated = true;
				if( $type(options.onSuccess) == 'function' ) {
					options.onSuccess();
				}
				return true;
			}
		} else {
			ebindr.authenticated = false;
			if( $type(options.onFalse) == 'function' ) {
				options.onFalse();
			}
			return false;
		}
	},
	
	/*
		TODO: add logic if we want that will take the cookies and validate that this person
		can be and is logged into the system
	*/
	serverAuthenticate: function( logout ) {
		// set logout
		logout = ( typeof(logout) !== 'undefined' ? logout : false );
		// check the server
		var secure = new Request({
			url: '/ebindr/security.php',
			onSuccess: function( html) {
    			if( html == 'false' && logout ) {
	    			new Request.HTML().get( '/logout.php' );
    				ebindr.alert( 'file: /ebindr/views/logout-message.html', 'We thought you left ...', function() {
						ebindr.logout();
					});
    			}
    		}		
		}).send();
		
		return true;
	},
		
	/*
		The login method
	*/
	login: function(e) {
		//$$( 'body' ).setStyle( 'background-image', '/ebindr/images/body/login/bg.png' );
		$( 'splash-title-txt' ).set( 'text', 'Log into eBINDr' );
		// get the login page and add the actions to it
		new Request.HTML({
			onSuccess: function( tree, el, html ) {
				// dump the form into the page
				$( 'login' ).set ( 'html', html );
				// add the focus events to the input fields
				$$( '#login input' ).addEvents({
					'focus': function() {
						ebindr.current.focus = this;
						this.set('value','');
						if( this.id == 'password2' ) {
							$('password2').destroy();
							$('password').setStyle( 'display', '' );
							$('password').focus();
						}
						this.setStyle( 'color', '#000000' );
					},
					'keyup': function(e) {
						if( e.key == 'enter' ) {
							//e.stop();
							//$('login-button').fireEvent( 'click', e );
						}
					}
				});
				// add the form events
				if( $$( 'div#login form' ).length > 0 ) {
					$( 'login-button' ).addEvent( 'click', function(e) {
						e.stop();
						// do our own form submit
    					var auth = new Request({
    						url: $$( 'div#login form' )[0].action,
    						method: 'post',
    						data: {
    							form_submit: 'y',
    							username: $('username').value,
    							password: $('password').value,
    							usertime: new Date().toGMTString()+'~'+new Date().getTimezoneOffset(),
    							database: $('login-db-name').get('value')//'hurdmantest'
    						},
    						onSuccess: function(response) {
    							(function() {
    							//var keys = ( Cookie.read("reportr_keys") ? Cookie.read("reportr_keys") : new String() );
								// lets check for the ,fee in the reportr_keys cookie
								//if( keys.contains(",fee") ) {
	    							// see if the right cookie was set
	    							var cookie = ( $chk(Cookie.read("reportr_username")) ? Cookie.read("reportr_username") : "" ).toLowerCase();

	    							ebindr.log(response);
	    							ebindr.console(response);
	    							// check to see if it matches up
	    							if( cookie == $('username').value.toLowerCase() && cookie != '' ) ebindr.load();
	    							else ebindr.alert( 'We\'re sorry the username and/or password was incorrect.', 'Woops!' );
	    						//} else {
	    						//	ebindr.alert( 'file: /ebindr/views/4902.html', 'Security Message' );
	    						//	ebindr.logout();
	    						//}
	    						}).delay(1000);
    						}
    					}).post();
					});
				}
			}
		}).get( '/ebindr/views/login.html' );
	},
	
	/*
		The logout method
	*/
	logout: function(e) {
		// ajax the logout function
		new Request.HTML().get( '/logout.php' );
		ebindr.authenticated = false;
		// unload eBINDr and show the login screen
		ebindr.unload();
		ebindr.login();
		// get rid of the progress bar so that it can show when the login again
		if( this.preloadprogress ) this.preloadprogress.destroy();
	}

});