ebindr.library.chat = new Class({

	instances: $H(), // an array containing all the chats open
	options: $H({
		lastget: new Date().getTime(),
		increment: 0, // every 3 seconds to start (is the id of the intervals)
		intervals: [
			5000, 10000, 30000, 60000
		]
	}), // options for the chat class
	skip: false, // skip a retrieval

	initialize: function( options ) {
		$extend(this.options,options);
		
		// log that this class has been loaded
		ebindr.console( 'ebindr.chat loaded' );
		// load the chat css
		ebindr.include( '/ebindr/styles/chat.css' );	
		
		
		
				
		//this.open( ( Cookie.read('reportr_username') == 'dst' ? 2 : 1 ) );
		
	},
	
	start: function() {
		ebindr.community.activeUsers();
		this.retrieve();
	},
	
	send: function( to, message ) {
		// make sure we have a message
		if( ( to != '' || to == 0 ) && $chk(message) && message != '' && message.length > 0 ) {
			// skip the next retrieval
			this.skip = true;
			new Request({
				url: '/ebindr/components/chat/send',
				onSuccess: function(data) {
					ebindr.chat.response(data);
				}
			}).post({
				'to': to,
				'message': message
			});
		}
	},
	
	update: function( id, css, name, message ) {
		// if a chat is found and there isn't an instance for that user then open a new window, minimize, and growl alert the user
		if( !this.instances.get(id) ) {
			this.open(id, css, name, message);
		} else {
			var chat = this.instances.get(id);
			// add it to the chat
			chat.add( css, name, message );
			
			if( chat.window.isMinimized || !ebindr.chat.instances.get(id).focused ) {
				ebindr.growl( '<b>' + name + ' says:</b>', message.substr(0,50) + ( message.length > 50 ? '...' : '' ) );
			}
			
		}
	},
	
	response: function( data ) {
		data = JSON.decode(data);
    	// make sure we have something
    	if( data.length > 0 ) {
    		// reset the interval increment and mark that we had data
    		ebindr.chat.options.increment = 0;
    		ebindr.chat.options.lastget = new Date().getTime();
    		// go through each one
    		data.each( function(chat) {
    			// returned format: { 'stamp': '2009-07-03 11:23:27', 'window': '2', 'class': '', 'name': 'doug', 'body': 'and another'}
    			ebindr.chat.update( chat.window, chat.css, chat.name, chat.body );
    		});
    	// if we don't have any data then mark the time
    	} else {
    		// if we are older than 60 seconds
    		if( ebindr.chat.options.lastget < new Date().getTime()-60000 && ebindr.chat.options.increment < 4 ) {
    			ebindr.chat.options.increment++;
    			ebindr.chat.options.lastget = new Date().getTime();
    		}
    	}
	},
	
	/*
		This method checks the servers for new messages or chats.
	*/
	retrieve: function() {
		if( !this.skip ) {
			(function(){
				new Request({
					url: '/ebindr/components/chat/get',
					onSuccess: function(data) {
						ebindr.chat.response(data);
					},
					onComplete: function() {
				    	// call itself again
				    	ebindr.chat.retrieve();
					}
				}).get();
			}).delay( this.options.intervals[ ( this.options.increment > 3 ? 3 : this.options.increment ) ] );
		} else {
			// reset it for next time
			this.skip = false;
			// and call it for next time
			this.retrieve();
		}

	},
	
	/*
		This method will start a new chat with given (user)
	*/
	open: function( user, p_css, p_name, p_message ) {
		// log that a chat was started
		ebindr.log( 'Chat initialized with UID ' + user );
		// add to instances
		this.instances.set( user, $H({
			user: user,
			add: function( css, name, body ) {
				// html template per message
				var table = $('chat-win-'+user).getElements('table.chat')[0];
				var template = '<tr class="{css}"><td class="person"><span>{name}</span></td><td class="body"><div>{body}</div></td></tr>';
				table.set( 'html', table.get('html') + template.substitute({
					css: 'text_message' + ( css.length > 0 ? ' ' + css : '' ),
					name: name,
					body: body
				}));
				// make sure we scroll down
				var msgs = $( 'chat-win-' + user + '_content' ).getElements( 'div.msgs' )[0];
				msgs.scrollTo( 0, msgs.getScrollSize().y );
			},
			window: new ebindr.window.parent.Window({
				loadMethod: 'xhr',
				id: 'chat-win-' + user,
				title: 'Chat (' + p_name + ')',
				contentURL: '/ebindr/views/chat.html',
				width: 300,
				height: 300,
				onContentLoaded: function() {
					var windowEl = ebindr.window.parent.Windows.instances.get(this.options.id);
					windowEl.fireEvent('onResize');
					if( typeof(p_message) != "undefined" ) {
						ebindr.chat.instances.get(user).add(p_css, p_name, p_message);
						//ebindr.chat.update( user, p_css, p_name, p_message );	
					}
				},
				onBlur: function() {
					ebindr.chat.instances.get(user).focused = false;
				},
				onFocus: function() {
					ebindr.chat.instances.get(user).focused = true;
					$( 'chat-win-' + user ).getElements( 'textarea' )[0].focus();
				},
				onMaximize: function() {
					ebindr.window.parent.Windows.instances.get(this.options.id).fireEvent('onResize');	
				},
				onRestore: function() {
					ebindr.window.parent.Windows.instances.get(this.options.id).fireEvent('onResize');	
				},
				onClose: function() {
					ebindr.chat.instances.erase(user);
				},
				onResize: function() {
					// shorten the object chain for these objects/elements
					var content = $(this.options.id + '_content');
					var wrapper = $(this.options.id + '_contentWrapper');
					var send = content.getElements('div.send')[0];
					var msgs = content.getElements('div.msgs')[0];
					var textarea = content.getElements('div.send textarea')[0];
					// set the height of the send
					send.setStyle( 'height', 40 );
					msgs.setStyle( 'height', wrapper.getCoordinates().height-43 ).scrollTo(0, msgs.getScrollSize().y);
					textarea.setStyle( 'width', content.getCoordinates().width-2 )
					// add the events
					textarea.addEvents({
						'focus': function(e) {
							ebindr.current.focus = this;
						},
						'keyup': function(e) {
							if( e.key == 'enter' ) {
								ebindr.chat.send( user, textarea.value );
								this.value = '';
							}
						}
					});
				},
				padding: { top: 0, bottom: 0, right: 0, left: 0 }
			})
		}));
	},
	
	/*
		Close/end a chat.
	*/
	close: function( user ) {
		// close chat window
		// send end chat procedure to the database
		// remove from instances
	}
	
});