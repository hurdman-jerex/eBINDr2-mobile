// add security/authentication methods/functions to ebindr
ebindr.extend({
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
	},

	/*
	    The login method
	 */

    login: function(){
        window.location = '/m/sign-in.html';
    }
});