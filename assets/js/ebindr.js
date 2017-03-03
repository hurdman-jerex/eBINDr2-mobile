/*
	This is the controlling hash (pseudo class) or library. This will
	act as a sort of register or controller for all that is eBINDr.
*/
Element.Events.complaintpublish = {
    base: 'load', // the base event type
    condition: function(event){ //a function to perform additional checks
        return this.contentWindow.location.href.match(/(Process%20Complaints[.]On%20Hold|Process%20Complaints[.]Response|button%20c).+[?].*editr/); // this means the event is free to fire
    }
};

var key1 = '';
var key2 = '';
var customextension="";
var customeditrextension="";
var edittitlecase=false;
var ebindr = new Hash({

	key: 'b2a4133041f3dff98f76abab32143dba',
	id: 'hurdmantest.hurdman.org', // used for the API

	has_acct_or_profane: false,
	dont_show_scrub_message: false,

	// Used for processing alerts
	timeelapsed: true,
	nocanvas: false,
	// stores mid of last ebindr announcement, so we know if we need to alert to the fact there is a new one available
	announcementmid: 0, 
	systemmessagemid: 0,
	// whether or not we are in debug mode
	debug: false,
	// whether we are logged in or not
	authenticated: false,
	// an array of all the included files,
	included: [], 
	// stores all the loaded plugins
	plugin: $H(), 
	// stores all of eBINDr's sub classes
	library: $H(), 
	// reference to the preloading progress bar
	preloadprogress: '',
	// stores all the messages to be prompted or displayed
	messages: new Hash({
		transfer: "You are about to transfer complaint #{cid} from {lastdba} to BID #{bid}. Click Yes to continue or No to stop this operation"
	}),
	// user object
	user: new Hash({
		// set's whether they are actived, defined by mouse movement or keystrokes
		active: true,
		preactive: true
	}),	
	shownloginalert: false, // shown the login alert yet or not
	// the different psuedo scrollers
	scrollers: [],
	// stores all the layout specific properties
	layout: $H(),
	// array that stores all the actions of a user in eBINDr, can be dumped for support
	history: [],
	// the comunity object
	community: $H(),
	// the business id
	bbbid: 0,
	myalerts_timeout: false,
	timetrack_timeout: false,
	windowtrack: [],
	lastwindowtrack: 'none',
	sysstatus_timeout: false,
	lastfindr: "n",	
	// stores what is current whether it be a tab, frame, or editr, etc.
	// also this is included as a stutitute object for each url that is
	// loaded in eBINDr so that {bid} will become the actualy value in the url string
	current: new Hash({
		// whether the window should be tacked
		tacked: false,
		// whether we are stopping the default close action
		stopClose: false,
		// whether we are showing a list of open windows or not
		winview: false,
		// whether there is a modal or not
		modal: false,
		// whether we have a prompt up (we need to type)
		prompt: false,
		// which tab we are on
		tab: 'records', 
		// alias to tab
		page: 'records',
		// the current frame hash
		frame: new Hash({
			// name of the frame
			name: 'business', 
			// it's button
			button: 'b' 
		}),
		// which editr is open or was open most recently
		editr: new String(''), 
		doccount: 0, 
		editrInsert: '', 
		editrExt: '', 
		customextension: '',
		customeditrextension: '',
		undo1: false,
		undo2: false,
		windowEl: new Object(),
		frameEl: new Object(),
		// the button currently running
		button: '', 
		// element that has focus
		focus: document.body, 
		// current business id
		bid: null, 
		isAB: false,
		// whether the bid was switched
		switchedbid: false, 
		// the last bid
		lastbid: null, 
		// the last dba
		lastdba: null, 
		// whether we are transfering or not
		transfer: false, 
		transfercid: 0, 
		transfergrowl: new Object(),
		// complaint id
		cid: 0, 
		// language id
		lid: 1, 
		// editr key 1
		key1: '',
		// editr key 2 
		key2: '',
		// complaint type
		ctype: null,
		//BID that complaints SHOULD be filed against
		complaintBID: 0,
		lastInquiry: false,
		//complaint alert
		complaintalert: null,
		auto_findr: false,
		FINDrN: "",
		FINDrA: "",
		FINDrP: "",
		FINDrE: "",
		FINDrW: "",
		link_search: ''
	}),
	
	/*
		This is called onDomReady to initialize eBINDr and make the object
		available globally to the program.
	*/
	initialize: function() {

		// make sure we are logged in, if we are not launch the login method
		/*ebindr.authenticate({
			onFalse: function() {
				ebindr.login();
			}
		});*/

		// check to see if we are authenticated
		//if( ebindr.authenticate() )
            ebindr.load();
	},
	
	/*
		This global method can be used to log all the actions of a user. This will
		only be used if they need/want to dump the actions if they are submitting a
		support/trouble ticket.
	*/
	log: function( message ) {
		// if we have more than 200 history items (change it to 100 or lower when in production)
		if( this.history.length > 200 ) {
			this.history.reverse();
			this.history.pop();
			this.history.reverse();
		}
		this.history.push(message);
	},
	
	/*
		For debuggin purposes. We can send messages to the console in firebug (Firefox only
		but will not break in other browsers).
	*/
	console: function( message ) {
		// only try to log it if console does exist
		if( typeof(console) != "undefined" && this.debug ) {
			console.log( message );
		}
	},
	
	/*
		This will show eBINDr after being logged in, and will preload everything that is
		needed for eBINDr. This is ebindr.initialize()'s compliment. Meaning this is only ever
		called after they have authenticated, whereas initialize is used to actually start up
		eBINDr.
	*/
	load: function() {
		ebindr.authenticated = true;
        // set the bbbid
        ebindr.bbbid = Cookie.read("bbbidreal");

        function dopage( request )
        {
            window.location = '/m/searchlink.html?bid='.ebindr.current.bid;
        }
	},

	ipaddress: function( url ) {
		new Element( 'script', {
			'type': 'text/javascript',
			'src': url
		}).inject($(document.body));
	},
	
	/*
		Detect IE version
	*/
	ie8: function() {
		return !!( (/msie 8./i).test(navigator.appVersion) && window.ActiveXObject && XDomainRequest );
	},
	
	/*
		Logs the platform, browser, and IP address of the given
		user
	*/
	platform: function() {
	
		if( ebindr.ie8() ) var browser = 'ie8';
		else if( Browser.Engine.trident5 ) var browser = 'ie7';
		else if( Browser.Engine.trident ) var browser = 'ie';
		else if( Browser.Engine.gecko ) var browser = 'firefox';
		else if( Browser.Engine.presto ) var browser = 'opera';
		else if( Browser.Engine.webkit ) {
			var browser = 'safari';
			if( navigator.userAgent.toLowerCase().indexOf('chrome') > -1 ) browser = 'chrome';
		}
		
		if( Browser.Platform.mac ) var os = 'mac';
		else if( Browser.Platform.win ) var os = 'win';
		else if( Browser.Platform.linux ) var os = 'linux';
		else if( Browser.Platform.ipod ) var os = 'ipod';
		else var os = 'other';
	},
	
	/*
		Include Function
	*/
	include: function( source ) {
		// if it's already been included once
		if( this.included[source] ) return true;
		// set that we are including this file
		this.included[source] = 1;
		// find the file type
		switch ( source.match(/\.\w+$/)[0] ) {
			case '.php':
			case '.js': return Asset.js(source);
			case '.css': return Asset.css(source, {media: "screen, projection, print"});
			case '.jpg':
			case '.png':
			case '.gif': return Asset.image(source);
		}
		// if the switch didn't return anything or returned false
		//if( this.debug ) this.notify( 'The included file "' + source + '" could not be loaded.' );
	}
});
