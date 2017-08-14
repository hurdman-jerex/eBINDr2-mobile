ebindr.library.window = new Class({

	parent: MochaUI,
		
	initialize: function( options ) {
		$extend(this.options,options);
		// log that this class has been loaded
		ebindr.console( 'ebindr.window loaded' );
		this.parent = MochaUI;
		this.library = this.parent;
	},
	
	/*
		Loads a clock
	*/
	test: function() {
	
		new this.library.Window({
			'id': 'testwin',
			'title': 'Testing Doug',
			addClass: 'transparent',
			loadMethod: 'iframe',
			contentURL: '/ebindr/views/video.html?frame=' + ebindr.current.frame.button + '&button=' + ebindr.current.button + '&tab=' + ebindr.current.page,
			onContentLoaded: function() {
				alert( 'loaded' );
			},
			shape: 'chromeless',
			headerHeight: 30,
			savable: true,
			width: ( $defined( MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')] ) ? MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')].width : 400 ),
			height: ( $defined( MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')] ) ? MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')].height : 300 ),
			x: ( $defined( MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')] ) ? MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')].x : 100 ),
			y: ( $defined( MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')] ) ? MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')].y : 100 ),
			padding: { top: 5, right: 5, bottom: 15, left: 5 },
			bodyBgColor: [250,250,250]
		});
	},
	clock: function() {
		new this.library.Window({
			id: 'clock',
			title: 'Clock',
			addClass: 'transparent',
			loadMethod: 'xhr',
			contentURL: '/ebindr/views/clock.html?t=' + new Date().getTime(),
			onContentLoaded: function(){
				if ( !ebindr.window.library.clockScript == true ){
					new Request({
						url: '/ebindr/scripts/plugins/clock.js?t=' + new Date().getTime(),
						method: 'get',
						onSuccess: function() {
							if (Browser.Engine.trident) {
								myClockInit = function(){
									CoolClock.findAndCreateClocks();
								};
								window.addEvent('domready', function(){
									myClockInit.delay(10); // Delay is for IE
								});
								ebindr.window.library.clockScript = true;
							}
							else {
								CoolClock.findAndCreateClocks();
							}
						}.bind(this)
					}).send();
				}
				else {
					if (Browser.Engine.trident) {
						myClockInit = function(){
							CoolClock.findAndCreateClocks();
						};
						window.addEvent('domready', function(){
							myClockInit.delay(10); // Delay is for IE
						});
						ebindr.window.library.clockScript = true;
					}
					else {
						CoolClock.findAndCreateClocks();
					}
				}
			},
			shape: 'gauge',
			headerHeight: 30,
			savable: true,
			width: ( $defined( MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')] ) ? MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')].width : 110 ),
			height: ( $defined( MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')] ) ? MUI.Windows.windowStates[options.id].height : 110 ),
			x: ( $defined( MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')] ) ? MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')].x : $('ebindr').getCoordinates().width - 125 ),
			y: ( $defined( MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')] ) ? MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')].y : $('ebindr').getCoordinates().height - 155 ),
			padding: { top: 0, right: 0, bottom: 0, left: 0 },
			bodyBgColor: [250,250,250]
		});	
	},
	
	/*
		Loads a modal box
	*/
	modal: function( message, properties ) {
	
		// make that we have a modal to stop the keyboard listening
		ebindr.current.modal = true;
	
		if( typeof(properties) == "undefined" ) var properties = {};
	
		// confirmation template
		var template = {
			body: "<p id=\"message\">{message}</p><p id=\"confirm-box-input\">{prompt}</p><div id=\"confirm-box-buttons\">{buttons}</div>",
			message: message,
			buttons: ( $chk(properties.buttons) ? properties.buttons : '' ),
			prompt: ( $chk(properties.prompt) ? properties.prompt : '' )
		};
	
		new this.parent.Window({
			title: ( typeof(properties.title) == 'undefined' ? 'Just Making Sure ...' : properties.title ),
			id: 'confirm-box',
			type: 'modal',
			content: template.body.substitute(template),
			savable: false,
			width: ( $defined( properties.width ) ? properties.width : 500 ), //( $defined( MUI.Windows.windowStates[options.id] ) ? MUI.Windows.windowStates[options.id].width : 500 ),
			height: ( $defined( properties.height ) ? properties.height : 65 ), //( $defined( MUI.Windows.windowStates[options.id] ) ? MUI.Windows.windowStates[options.id].height : 65 ),
			x: ( $defined( properties.x ) ? properties.x : null ), //( $defined( MUI.Windows.windowStates[options.id] ) ? MUI.Windows.windowStates[options.id].x : 100 ),
			y: ( $defined( properties.y ) ? properties.y : null ), //( $defined( MUI.Windows.windowStates[options.id] ) ? MUI.Windows.windowStates[options.id].y : 100 ),			
			minimizable: true,
			onContentLoaded: function() {
				ebindr.window.parent.dynamicResize($(this.options.id));
			},
			onClose: function() {
				ebindr.current.modal = false;
				if( !ebindr.modal.options.clicked ) {
					ebindr.modal.options.returnValue = false;
					ebindr.modal.close();
				}
			}
		});
	
	},
	
	notification: function( message, type ) {
		// log the notification
		ebindr.log( 'Notification Type: ' + type + ' and Message: ' + message );
		// set the default
		if( !$chk(type) ) type = 'notify';
		switch( type ) {
			case "error": var bg = [219,88,88]; break;
			case "warning": var bg = [253,191,87]; break;
			case "growl": var bg = [50,50,50]; break;
			case "notify":
			default: var bg = [175,189,211]; break;
		}
	
		var error = new this.parent.Window({
			loadMethod: 'html',
			type: 'notification',
			addClass: 'notification ' + type,
			closeAfter: ( type != 'error' ? 5000 : false ),
			content: message,
			shadowBlur: 5,
			savable: true,
			width: ( $defined( MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')] ) ? MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')].width : 500 ),
			height: ( $defined( MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')] ) ? MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')].height : 32 ),
			x: ( $defined( MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')] ) ? MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')].x : 100 ),
			y: ( $defined( MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')] ) ? MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')].y : ( type == 'growl' ? (window.getSize().y-40) : null ) ),				
			padding:  { top: 10, right: 0, bottom: 10, left: 10 },
			bodyBgColor: bg,
			onContentLoaded: function() {
				var id = this.options.id;
				$(id).addEvent( 'click', function(e) {
					MochaUI.closeWindow(MochaUI.Windows.instances.get(id).windowEl);
				});
			}
		});
	},
	
	/*
		Minimize all windows
	*/
	minimizeAll: function() {
		this.parent.minimizeAll();
	},
	
	/*
		Minimize A Window
	*/
	minimize: function( id ) {
		this.parent.Dock.minimizeWindow( $(id) );
	},
	
	/*
		Change location of window
	*/
	change: function( id, src ) {
		var iframeEl = MochaUI.Windows.instances.get(id).iframeEl;
		if( $('findr2') ) ebindr.window.minimize('findr2');
		// set the title on the window
		iframeEl.set( 'src', src );
	},
	
	/*
		Get current location of window
	*/
	currentlocation: function( id ) {
		var mywindow = MochaUI.Windows.instances.get(id);
		if( ! mywindow ) return "undefined";
		var iframeEl = mywindow.iframeEl;
		// set the title on the window
		return window.parent.document.getElementById( iframeEl.id ).contentWindow.document.location;
//		return iframeEl.get( 'src' );
	},
	
	/*
		Get the title object from the window
	*/
	title: function( id, title ) {
		var _tb = $(id).getElements('div.mochaTitlebar');
		if( _tb.length > 0 ) {
			if( _tb[0].hasClass('otherbid') ) return;
		}
		
		if( $chk(title) && title.length > 0 ) {
			if( title == ' ' ) title = id;
			if( title.match(/^lite-button-cn/gi) ) title = "New Complaint";
			var titleEl = MochaUI.Windows.instances.get(id).titleEl;
			// set the title on the window
			titleEl.set( 'html', title );
			// set the title in the dock
			this.dockTitle( id, title );
		}
	},
	
	sizetofit: function( id ) {
//		ebindr.growl("id:"+id);
//		MochaUI.Windows.instances.get(id).dynamicResize();
		(function() {
			MochaUI.dynamicResize($(id));
		}).delay(1000);
	},
	/*
		Set the title in the dock as well.
	*/
	dockTitle: function( id, title ) {
		// set it if the dock exists
		if( $( id + '_dockTabText' ) ) $( id + '_dockTabText' ).set( 'html', title.substring(0,15) + (title.length > 15 ? '...' : '' ) );
	},
	
	/*
		This is the global iframe controller. References to the iframes contentWindow
		will all be directed through here for a more centralized operation.
	*/
	iframe: function( id ) {
		// get the iframe
		var obj = $( id );
		var obj2 = $ ( id.replace('_iframe', '') );
		// return all the stuff we want/need
		return {
			el: obj,
			window: obj.contentWindow,
			document: obj.contentWindow.document,
			height: ( $defined( MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')] ) ? MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')].height : ( obj2.getSize().y ? obj2.getSize().y : null ) ),
			width: ( $defined( MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')] ) ? MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')].width : ( obj2.getSize().x ? obj2.getSize().x : null ) ),
			x: ( $defined( MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')] ) ? MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')].x : 100 ),
			y: ( $defined( MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')] ) ? MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')].y : 100 ),			
			url: obj.contentWindow.location,
			title: ( !obj.contentWindow.editrtitle ? obj.contentWindow.document.title : obj.contentWindow.editrtitle.replace(/<br>/gi, " ") ),
			src: obj.src
		};
	},
	
	list: function( button, options ) {
		// if we don't have any custom options
		if( typeof( options ) == "undefined" ) var options = {};
				
		new this.parent.Window($extend(options,{
			title: ( options.title ? options.title : 'Loading ...' ),
			loadMethod: ( options.loadMethod ? options.loadMethod : 'iframe' ),
			contentURL: ( options.contentURL ? options.contentURL : '/ebindr/blank.html' ),
			maximizeable: false,
			minimizable: false,			
			savable: true,
			storeOnClose: ( options.storeOnClose ? options.storeOnClose : false ),
			height: ( $defined(options.id) && $defined( MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')] ) ? MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')].height : ( options.height ? options.height : window.getSize().y-300 ) ),//( options.height ? options.height : window.getSize().y-300 ),
			width: ( $defined(options.id) && $defined( MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')] ) ? MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')].width : ( options.width ? options.width : 400 ) ),//( options.width ? options.width : 400 ),
			x: ( $defined(options.id) && $defined( MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')] ) ? MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')].x : null ),
			y: ( $defined(options.id) && $defined( MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')] ) ? MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')].y : null ),
			onContentLoaded: function(windowEl) {
				if( this.options.loadMethod == 'iframe' ) {
					// find the iframe
					ebindr.window.sizetofit(windowEl);
					var iframe = ebindr.window.iframe( this.options.id + '_iframe' );
					// if we are in debug mode then show which button it is loading and the url
					if( ebindr.debug ) ebindr.growl( 'button: ' + button + ', url: ' + this.options.contentURL );
					// set the title
					ebindr.window.title(this.options.id, iframe.title );
				}
			}
		}));
	
	},
	
	editr: function( button, options ) {
		// if we don't have any custom options
		if( typeof( options ) == "undefined" ) var options = {};
		new this.parent.Window($extend(options,{
			title: 'Loading ...',
			loadMethod: 'iframe',
			toolbar: true,
			//closable: false,
			toolbarURL: '/ebindr/views/toolbar.html',
			padding: ( options.padding ? options.padding : { top: 0, right: 0, bottom: 0, left: 10 } ),
			contentURL: ( options.contentURL ? options.contentURL : '/ebindr/blank.html' ),
			savable: true,
			width: ( $defined( MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')] ) ? MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')].width : ( options.width ? options.width : window.getSize().x-200 ) ),
			height: ( $defined( MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')] ) ? MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')].height : ( options.height ? options.height : window.getSize().y-200 ) ),
			// changed 100 to null for the x and y
			x: ( $defined( MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')] ) ? MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')].x : null ),
			y: ( $defined( MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')] ) ? MUI.Windows.windowStates[(options.id.split(":")[0]).replace(/\-[0-9]+$/g, '')].y : null ),
			/*
			onClose: function(windowEl) {
				alert( 'custom close' );
				ebindr.current.stopClose = true;
				var iframeid = ebindr.window.library.focusedWindow.options.id + '_iframe';
				ebindr.button.toolbar.escape( windowEl, ebindr.window.iframe(iframeid) );
			},
			*/
			onContentLoaded: function(windowEl) {
				if( this.options.loadMethod == 'iframe' ) {
					// find the iframe
					ebindr.window.sizetofit(this.options.id);
					var iframe = ebindr.window.iframe( this.options.id + '_iframe' );
					
					//Picks first visible input box  (code: inputfocus)
					// console.log(iframe);
					// Nathan removed this section 4/29 because this is picking the wrong input box and is unnecessary
/*					var text_boxes = iframe.document.getElements('input[type="text"]');
					
					for ( var box_counter = 0; box_counter < text_boxes.length; box_counter++) {
							if ( text_boxes[box_counter].getStyle('display') != 'none' ) {
								text_boxes[box_counter].focus();
								break;
							}
					}*/

					$(this.options.id).getElements( '.toolbar-btn' ).each( function( btn, i ) {
						switch( btn.getProperty('class').split(' ')[0] ) {
    						case "escape":
    							btn.addEvent( 'click', function() {
    								ebindr.button.toolbar.escape( windowEl, iframe );									
    							});
    							break;
    							
    						case "export":
    							if( !iframe.document.location.href.contains('editr') ) {
    								var url = ebindr.button.toolbar.exportr(iframe.window);
    								if( !url ) {
    									btn.setStyle( 'display', 'none' );
    								} else {
    									btn.addEvent( 'click', function() {
    										iframe.document.location.href = url;
    									})
    								}
    								
    							}
    							break;
    							
    						case "insert": 
    							if( !iframe.document.location.href.contains('editr') ) {
    								var url = ebindr.button.toolbar.insert( iframe );
    								if( !url ) {
    									btn.setStyle( 'display', 'none' );
    								} else {
    									btn.addEvent('click', function(e) {											
    										if( typeof(ebindr.tmpInsertEDITr) == 'function' ) {
    											ebindr.tmpInsertEDITr();
    											ebindr.tmpInsertEDITr = null;
    										} else {
												//iframe.document.location.href = ebindr.button.toolbar.insert( iframe );
												
												if(!iframe.document.location){
												//fix, so we can 'insert' more than once, w/o closing the iframe window
													ebindr.current.frameEl.contentWindow.document.location.href=url;
												}else{
													iframe.document.location.href = url;
												}
    										}
    										btn.setStyle( 'visibility', 'hidden' );
    										(function() {
    											btn.setStyle( 'visibility', 'visible' );
    										}).delay(1000);
    									});
    								}
    							}
    							break;
    							
    						case "delete":
    							if( !iframe.document.location.href.contains('editr') ) {
    								var url = ebindr.button.toolbar.remove();
    								if( !url ) {
    									btn.setStyle( 'display', 'none' );
    								} else {
    									btn.addEvent( 'click', function() {
											if(readonly) return false;
											
												//iframe.document.location.href = ebindr.button.toolbar.remove();
												
												if(!iframe.document.location){
												//fix, so we can 'delete' more than once, w/o closing the iframe window
													ebindr.current.frameEl.contentWindow.document.location.href=ebindr.button.toolbar.remove();
												}else{
													iframe.document.location.href = ebindr.button.toolbar.remove();
												}
    									});
    								}
    							}
    							break;
    					}
					});
					// set the title
					ebindr.window.title(this.options.id, iframe.title );

					this.options.thisLoad(windowEl);
				}
			}
		}));
	},
	
	/*map: function( id, options ) {
		// if we don't have any custom options
		if( typeof( options ) == "undefined" ) var options = {};
		new this.parent.Window($extend(options,{
			title: options.title,
			id: id,
			loadMethod: 'iframe',
			contentURL: options.contentURL,	
			width: ( options.width ? options.width : 500 ),
			height: ( options.height ? options.height : 400 ),
			padding: { top: 0, right: 0, bottom: 0, left: 0 },
		}));			
	},*/
	
	normal: function( button, options ) {

		contentLoaded = function(windowEl) {
				if( this.options.loadMethod == 'iframe' ) {
					// find the iframe
					ebindr.window.sizetofit(windowEl);
					if( !this.options.contentURL.contains("http") ) var iframe = ebindr.window.iframe( this.options.id + '_iframe' );
					// if we are in debug mode then show which button it is loading and the url
					if( ebindr.debug ) ebindr.growl( 'button: ' + button + ', url: ' + this.options.contentURL );
					// set the title
					if( !this.options.contentURL.contains("http") ) ebindr.window.title(this.options.id, iframe.title );
					if( $type( options.onWindowOpen ) == "function" ) options.onWindowOpen();
				}
		}

		// if we don't have any custom options
		if( typeof( options ) == "undefined" ) var options = {};
		
		if( undefined == options.id ) var stateID = '';
		else {
			if( options.id.indexOf(":") > 0 ) var stateID = (options.id.split(":")[0]).replace(/\-[0-9]+$/g, '');
			else var stateID = options.id;
		}
		
		new this.parent.Window($extend(options,{
			title: ( options.title ? options.title : 'Loading ...' ),
			type: ( options.type ? options.type : 'window' ),
			tacable: ( options.tacable ? options.tacable : false ),
			savable: ( options.savable ? options.savable : true ),
			changeLocation: ( options.changeLocation ? options.changeLocation : false ),
			loadMethod: ( options.loadMethod ? options.loadMethod : 'iframe' ),
			modalOverlayClose: ( options.modalOverlayClose ? options.modalOverlayClose : true ),
			headerStartColor:  ( options.headerStartColor ? options.headerStartColor: [255,255,255] ),
			headerStopColor:   ( options.headerStopColor ? options.headerStopColor: [220,221,222] ),
			contentURL: ( options.contentURL ? options.contentURL : '/ebindr/blank.html' ),
			width: ( $defined( MUI.Windows.windowStates[stateID] ) ? MUI.Windows.windowStates[stateID].width : ( options.width ? options.width : window.getSize().x - 200 ) ),
			height: ( $defined( MUI.Windows.windowStates[stateID] ) ? MUI.Windows.windowStates[stateID].height : ( options.height ? options.height : window.getSize().y - 200 ) ),
			// ( options.width ? ((window.getSize().x - options.width)/2) : 100 ) instead of null below
			// ( options.height ? ((window.getSize().y - options.height)/2) : 100 ) instead of null below
			x: ( $defined( MUI.Windows.windowStates[stateID] ) ? MUI.Windows.windowStates[stateID].x : null ),
			y: ( $defined( MUI.Windows.windowStates[stateID] ) ? MUI.Windows.windowStates[stateID].y : null ),			
			padding: ( options.padding ? options.padding : { top: 0, right: 10, bottom: 0, left: 10 } ),
			onContentLoaded: ( typeof(options.onContentLoaded) == 'function' ? options.onContentLoaded : contentLoaded ),
			onMinimize: ( typeof(options.onMinimize) == 'function' ? options.onMinimize : $empty )
		}));
	}

});
