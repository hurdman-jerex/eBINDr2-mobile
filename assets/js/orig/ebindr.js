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

		// start the base libraries up
		this.button = new ebindr.library.button();
		this.tab = new ebindr.library.tab();
		this.window = new ebindr.library.window();
		this.modal = new ebindr.library.modal();
		this.keyboard = new ebindr.library.keyboard();
		this.data = new ebindr.library.data();
		this.dashboard = new ebindr.library.dashboard();
		this.findr2 = new ebindr.library.findr2();
		//this.chat = new ebindr.library.chat();
		this.conversations = new ebindr.library.conversations();
		// make sure we are logged in, if we are not launch the login method
		ebindr.authenticate({
			onFalse: function() {
//				ebindr.alert( 'file: /ebindr/views/beta-reminder.html', 'Friendly Reminder' );
				//ebindr.growl( 'Friendly Reminder', 'We just wanted to remind you that you are currently using <b>eBINDr2 Beta</b>. Meaning that this version of eBINDr is technically still <i>under development</i>.<br /><br /><b>Click</b> to close.', true);
				ebindr.login();
			}
		});

		// check to see if we are authenticated
		if( ebindr.authenticate() ) ebindr.load();
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
		Preload the images, start the progress bar and fire an event when it's done
		This loads images as well as queries to fetch data that we need to populate
		and run eBINDr properly
	*/
	preload: function( done ) {
		$('test-load').setStyle( 'opacity', 0.25 );
		$('spinner').setStyle( 'display', '' );
		//done();
		// set the progress bar
    	this.preloadprogress = new ebindr.library.progressbar();
		// preload all images
    	var loader = new Asset.images(ebindr.images, {
    		onProgress: function(counter,index) {
    		
    			var completed = counter;
    			var total = ebindr.images.length + ebindr.data.preloadSQL.length;
    			var percent = (completed/total)*100;
//    			var percent = counter*(100/total);Ê
  //  			var percent = ((counter) * (100 / (ebindr.images.length + ebindr.data.preloadSQL.length)));
    			
    			
    			
	    		//ebindr.preloadprogress.set( percent.round() );
	    		//$('test-load').set('text', 'GUI' );
	    		//$('test-percent').set( 'text', percent.round() + '%' );
    		},
    		// when it's done loading, fire the done function
    		onComplete: done
    	});
    	
	},
	
	/*
		This will show eBINDr after being logged in, and will preload everything that is
		needed for eBINDr. This is ebindr.initialize()'s compliment. Meaning this is only ever
		called after they have authenticated, whereas initialize is used to actually start up
		eBINDr.
	*/
	load: function() {
		// hide the form
		$( 'login' ).setStyle( 'display', 'none' );
		$( 'splash-title-txt' ).set( 'text', 'Loading ...' );
		ebindr.authenticated = true;
		// remove the image rotator if it exists
		if( $('rotate-images') ) {
			RotateImages = function() { return false; }
			$('rotate-images').set('src', 'about:blank');
			$('rotate-images').destroy();
		}


	   	/*( function() {
			ebindr.serverAuthenticate( true );
    	}).periodical(600000); // every 10 minutes*/
		// preload
		this.preload( function() {
			// bring in the styles
			ebindr.include( "/ebindr/styles/button.css" );
			// bring in the datepicker
			ebindr.include( "/ebindr/styles/plugins/datepicker.css" );
			ebindr.include( "/ebindr/scripts/plugins/datepicker.js" );
			
			ebindr.include( "/ebindr/styles/findr.css" );
			ebindr.include( "/ebindr/styles/findr1.css" );
			// bring in the stuff for the spellchecker
			//ebindr.include( "/ebindr/styles/plugins/spellchecker.css" );
			//ebindr.include( "/ebindr/scripts/plugins/spellchecker.js" );
			
			/*
			var utilTips = new Tips( '.utility img', {
				beforeText: '',
				afterText: '',
				fixed: true,
				onShow: function(tip) {
						tip.setStyle( 'opacity', 0.85 );
						tip.setStyle( 'display', 'block' );
				},
				offsets: { x: 10, y: 0 },
				className: 'util-tip'
			});
			*/
			
			
//			var utilTips = new FloatingTips('.utility img', { position: 'left' });
			
			var utilTips = new Tips( '#ticket-bar div', {
				beforeText: '',
				afterText: '',
				fixed: true,
				onShow: function(tip) {
						tip.setStyle( 'opacity', 0.85 );
						tip.setStyle( 'display', 'block' );
				},
				offsets: { x: 30, y: 0 },
				className: 'util-tip'
			});
			
			/*var myTips = new Tips('.btnbar li img, #bs a', {
				beforeText: '',
				afterText: '',
				className: 'btnbar-tip',
				onShow: function(tip) {
						tip.setStyle( 'opacity', 0.85 );
						tip.setStyle( 'display', 'block' );
				},
				offsets: { x: -25, y: 25 },
				fixed: true
			});*/

			// add the events to each frame
			$$( 'iframe' ).addEvent( 'reload', function(url) {
				url = url.substitute( ebindr.current ) + ( url.contains('?') ? '&ebindr2=y' : '?ebindr2=y');
				ebindr.log( url );
				// set the new url
				this.src = url;
			});

			// add the hover to the fax button
			$( 'bf-btn' ).addEvent( 'contextmenu', function(e) {
				if( ! ebindr.button.access('faxreportlink') ) return false;
				$('b-fax-list').setStyles({
					'left': $('bf-btn').getCoordinates().left+20,
					'top': $('bf-btn').getCoordinates().top + 23
				});
				$('b-fax-list').setStyle( 'display', '' );
			});
			$( 'bf-btn' ).addEvent( 'click', function(e) {
				$('b-fax-list').setStyle( 'display', 'none' );
			});
		
			$('b-fax-list').addEvent( 'mouseleave', function(e) {
				this.setStyle( 'display', 'none' );
			});
			$$( '#b-fax-list li' ).each( function(btn) {
				btn.addEvents({
					'mouseenter': function(e) {
						this.addClass('hover');
					},
					'mouseleave': function(e) {
						this.removeClass('hover');
					}, 
					'click': function(e) {
						new Event(e).stop();
						ebindr.button.editr( "faxserver.SendFax."+this.get('lang')+".editr", "&fax="+$( 'bf' ).get('text') );
						$('b-fax-list').setStyle( 'display', 'none' );
					}
				});
			});


			// load the data from SQL
			ebindr.data.preload(function() {			
			
				// set the bbbid
				ebindr.bbbid = Cookie.read("bbbidreal");
				// hide the loading message and show eBINDr
				$('splash').setStyle( 'display', 'none' );
				$('ebindr').setStyle( 'display', 'block' );
				// show the logout button
				$('logout').setStyle( 'display', '' );
				(function() {
					window.fireEvent('resize'); 
					// check that we have a uid
					ebindr.communityUser();					
				}).delay(1000);
				(function() {
					// set the community information 3 minutes later to make sure it was set properly
					ebindr.communityUser();
				}).delay(180000);
				$$( '.page' ).setStyle( 'display', 'none' );
				$$( '.page.records.' ).setStyle( 'display', 'block' );
				// start the chat engine
				//ebindr.conversations.routine();
				// start the periodical that checks for activity
				ebindr.user.checkActivity();
				// check for the existence of firebug
				/*if (window.console && window.console.firebug) {
					ebindr.alert( 'file: /ebindr/views/firebug.html' );
				}*/
				// start the clock and run the community user
				(function() {
					//ebindr.window.test();
				}).delay(3000);
				
				// set the title
				if( ebindr.isHurdman() ) document.title = ebindr.data.store.bbbname + ' (eBINDr2)';
				else document.title = 'eBINDr2 ('+ebindr.data.store.bbbname+')';
				
				ebindr.platform();
				
				// make double clicking on the ebindr logo dump debug information
				$('logo').addEvent('dblclick', function(e) {
					new Event(e).stop();
					var dump = '';
					ebindr.history.each( function(item) {
						dump = dump + item + "\n";
					});
					
					new Request({
						'url': '/ebindr/debug.php'
					}).post({
						'log': escape(dump)
					});
					
					ebindr.history = [];
					
					ebindr.alert( 'We have recorded your debug information successfully.', 'Thanks' );
					
				});
				
				// set the katana token login
				var basekatana = $('katana').get( 'href' );
				basekatana = basekatana.substring(0,basekatana.indexOf("ebindr") + 7);
				if(ebindr.data.store.katanatoken.match("^http")) $('katana').set( 'href', ebindr.data.store.katanatoken );
				else $('katana').set( 'href', basekatana + Cookie.read("reportr_username") + '/' + ebindr.data.store.katanatoken );

				ebindr.alerts();
				ebindr.timeTrack();
				ebindr.sysstatus(false);
				// launch the dashboard
				
				(function() {
				ebindr.getBizButtons();
				ebindr.data.get('e button dr', function(data) {
					ebindr.data.load( 'e button dr', data );
					ebindr.data.get('e recent reports.list');				
				});
				if( ebindr.data.store.traininggraph == 'block' ) ebindr.data.get('training.CourseSummary');
				ebindr.data.get('e favorite reports.list');
				ebindr.data.get('/ebindr/community.php/features/popular');
				}).delay(1000);
				
				// show leadmark if we are coloradosprings and rene
				if( (Cookie.read( "reportr_username" ) == 'rb') || ebindr.isHurdman() ) {
					if( $('dk') ) $('dk').setStyle( 'display', '' );
				}
				
		//'e button dr',
		//'e favorite reports.list',
		//'e recent reports.list',
		
				//(function() { ebindr.alerts(); }).periodical(60000);

				if( ebindr.data.store['dashonlogin'] == 1 ) {
					if ($('dashboard') == null)	{
						ebindr.dashboard.open();
					} else {
						$('dashboard').setStyle('z-index', '113');
						$('dashboard').removeClass('windowClosed');
						$('dashboard').addClass('mocha');
					}
				}


				if(ebindr.data.store.attributes.indexOf("Suppress pop up publishing message") > -1) {
					ebindr.dont_show_scrub_message = true;
				}
			});
			
		});
	},
	
	getBizButtons: function( secondTime ) {
		if( undefined == secondTime ) var secondTime = false;
		ebindr.data.get('e button sort', function(data) {
			if( data == 'empty' && !secondTime ) return ebindr.getBizButtons(true);
			$$('#bizbuttons li.dynamic').dispose();
			data.each(function(btn,i) {
				if( i > 0 ) {
					new Element('li',{
						'class': 'spacer dynamic'
					}).inject($('editorderbtn'),'before');
				}
				var htmlForBtn = btn.name;

				// if (btn.id == 'emaillink') {
				// 	htmlForBtn = "<span id='myemailurl' myurl='' >" + btn.name + "</span>";
				// }

				new Element('li',{
					'html': htmlForBtn,
					'id': btn.id,
					'class': (i==0?'left dynamic':'dynamic') + ( ebindr.data.store.buttoncolor_bl == 'red' && btn.id == 'bl' ? ' red' : ( ebindr.data.store['isbuttonshade_'+btn.id] ? ' more' : '' ) ) + ( (btn.id=="scanneddocs" && ebindr.current.doccount==0) ? ' empty' : '' )  + (btn.class!='' && btn.class!=btn.id?' '+btn.class:'')
				}).inject($('editorderbtn'),'before');
			});

			// $('myemailurl').addEvent('mousedown', function(e){
				// if (e.event.button == 1) {
					// var emaila=new Element('a', {target: '_blank', href:'mailto:?subject=' + escape (ebindr.data.store.button_bn.replace( '&amp;', '&' ).replace(/<span.*/g,'') + ' (Business ID:' + ebindr.current.bid + ')')});
					// emaila.inject(document.body);
					// emaila.setStyles({'display':'none'});
					// emaila.click();
					// emaila.dispose();
					// emaila.destroy();
				// } else if (e.event.button == 0) {
				// 	var emaila=new Element('a', {target: '', href:'mailto:?subject=' + escape (ebindr.data.store.button_bn.replace( '&amp;', '&' ).replace(/<span.*/g,'') + ' (Business ID:' + ebindr.current.bid + ')')});
				// 	emaila.inject(document.body);
				// 	emaila.setStyles({'display':'none'});
				// 	emaila.click();
				// 	emaila.dispose();
				// 	emaila.destroy();
				// }
			// });

			//this.button = new ebindr.library.button();
			ebindr.button.activate('.btnbar li');
		}, { type: 'b' });

	},

	/*
		This will show eBINDr after being logged in
	*/
	unload: function() {
		// hide the form
		$( 'login' ).setStyle( 'display', 'block' );
		// show the login/splash page
		$( 'splash' ).setStyle( 'display', 'block');
		$( 'ebindr' ).setStyles({
			'display': 'none'
		});
		Cookie.dispose("uid");
		Cookie.dispose("Hurdman.TicketTRACKr[id]");
		Cookie.dispose("Hurdman.TicketTRACKr[main]");
		Cookie.dispose("Hurdman.TicketTRACKr[pass]");
		Cookie.dispose("Hurdman.TicketTRACKr[user]");
		// hide the logout button
		$('logout').setStyle( 'display', 'none' );
		$('test-percent').set('text','');
		$('test-load').set('text','');
		$('spinner').setStyle( 'display', 'none' );
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

		var resolution = $(window).getSize().x + 'x' + $(window).getSize().y;
//		var resolution = screen.width + 'x' + screen.height;
		var bbbid = ( ebindr.isHurdman() ? '9999' : this.bbbid );
		
	},

	/*
		Will throw a notification on the screen.
	*/
	notify: function( message ) {
		return ebindr.window.notification( message );
	},
	
	/*
		Will display an error on the screen
	*/
	error: function( message ) {
		return "";
		return ebindr.window.notification( message, 'error' );
	},
	
	/*
		Display a warning
	*/
	warning: function( message ) {
		return ebindr.window.notification( message, 'warning' );
	},
	
	/*
		Display an alert
	*/
	alert: function( message, title, onclose, bgcolor, extraoptions ) {
		return ebindr.modal.alert( message, title, onclose, bgcolor, undefined, extraoptions );
	},
	
	/*
		MAC-Like Growl
	*/
	growl: function( title, message, sticky, bgcolor, onclick, position, duration ) {
		if( typeof(position) == 'undefined' ) position = 'topRight';
		// bring in the plugin if it isn't already
		if( $type(this.plugin.roar) != "object" ) {
			this.include( "/ebindr/scripts/plugins/roar.js" );
			this.include( "/ebindr/styles/plugins/roar.css" );
			this.plugin.roar = new Roar({
				'position': position
			});
		}
		if( $type(this.plugin.roar) ) {
			if( typeof(duration) != 'undefined' ) this.plugin.roar.options.duration = duration;
			this.plugin.roar.options.sticky=( typeof(sticky) == 'undefined' ? false : sticky );
			this.plugin.roar.options.bgcolor=( typeof(bgcolor) == 'undefined' ? '#000000' : bgcolor );
			this.plugin.roar.options.onClick=( typeof(onclick) == 'undefined' ? $empty : onclick );
			this.plugin.roar.options.position=position;
		}
		// send the message		
		return ( $type(this.plugin.roar) ? this.plugin.roar.alert(title,message) : ebindr.window.notification( title, 'growl' ) );
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
		if( this.debug ) this.notify( 'The included file "' + source + '" could not be loaded.' );
	},
	
	/*
		alerts function runs e button alerts every minute
	*/
	alerts: function() {
		if( this.user.isActive() ) {
			if(this.windowtrack.length>0) var mytracks="('"+this.windowtrack.join("),('").replace(/,([0-9])/g,"',$1")+")"; else var mytracks="()";
			this.windowtrack=[];
			var thisalert = new Request.HTML().get( '/report/e button myalerts/?ebindr2=y&noheaderhidden&bid='+this.current.bid+'&systemmessagemid='+this.systemmessagemid+'&@NOPROMPTwindowtracks='+escape(mytracks) );
		}
		if(this.myalerts_timeout) window.clearTimeout(this.myalerts_timeout);
		this.myalerts_timeout=window.setTimeout( "ebindr.alerts();", 30000);
	},

    timeTrack: function() {
		if( this.user.isActive() ) {
			if($$('div.mocha.isFocused').length>0) var focusedwindow=escape($$('div.mocha.isFocused')[0].get('id')); else var focusedwindow=this.lastwindowtrack;//'none';
			if(focusedwindow=='alert-box') focusedwindow=this.lastwindowtrack;
			this.lastwindowtrack=focusedwindow;
			focusedwindow=focusedwindow.replace(/%3A[0-9]+$/g, '').replace(/[-:][0-9]+$/g, '').replace(/^favorite-/g,'').replace(/^menu[.]/g, '');
			for(var i=0;i < this.windowtrack.length;i++) if(this.windowtrack[i][0]==focusedwindow) { this.windowtrack[i][1]+=5; var added=true; break; }
			if(!added) this.windowtrack.push([focusedwindow,5]);
		} else this.lastwindowtrack='none';
		if(this.timetrack_timeout) window.clearTimeout(this.timetrack_timeout);
		this.timetrack_timeout=window.setTimeout( "ebindr.timeTrack();", 5000);
    },
	sysstatus: function(doalert) {
		if( this.user.isActive() ) {
			$('ns').removeClass('sysstatus-green').removeClass('sysstatus-red').removeClass('sysstatus-yellow').removeClass('sysstatus-unknown');
			$('ns').addClass('sysstatus-loading' );
			new Request({
				'method': 'get', 
				'timeout': 15000,
				'url': '/ebindr/systemstatus.php',
				'onComplete': function(data) {
					$('ns').removeClass('sysstatus-loading');
					$('ns').addClass('sysstatus-' + data );
					if(doalert) ebindr.alert('Thank you for checking on our system status. We have updated your button to reflect the latest status, which is: '+( data == 'red' ? 'some systems down' : ( data == 'yellow' ? 'limited issues' : ( data == 'unknown' ? 'unknown' : 'everything is ok' ) ) )+'. If you wish for a more detailed report please click <a href="http://status.hurdman.com" target="_blank">here</a>.' );
				},
				'onTimeout': function(data) {
					$('ns').removeClass('sysstatus-loading');
					$('ns').addClass('sysstatus-unknown' );
					if(doalert) ebindr.alert('Thank you for checking on our system status. We currently are unable to determine system status, possible due to internet connectivity issues.' );
				}
			}).send();
		}
		if(this.sysstatus_timeout) window.clearTimeout(this.sysstatus_timeout);
		this.sysstatus_timeout=window.setTimeout( "ebindr.sysstatus(false);", 600000);
	},

	isHurdman: function() {
		return this.data.store.securitykeys.match("HURDMAN");
	},
	
	isHurdmantest: function() {
		return ( Cookie.read("reportr_db") == 'hurdmantest' ? true : false );
	}, 
	
	openReport: function(mergecode) {
		ebindr.window.normal( mergecode, {
			contentURL: '/report/' + mergecode + '/?ebindr2=y'
		});
	},
	
	transferComplaint: function() {
		this.current.transfercid=this.current.cid;
		if(this.current.transfer) return;
		this.current.transfer=true;
		this.current.transfergrowl=ebindr.growl("Complaint Transfer", "Please find the business you wish to transfer this complaint to and open up its record.<br>Click on this message to cancel the transfer.", true, "orange", function() { 
			ebindr.current.transfer=false; 
			ebindr.growl("Complaint Transfer", "Complaint transfer cancelled", false, "orange" );
		}, 'bottomRight' );
	}
	
});
