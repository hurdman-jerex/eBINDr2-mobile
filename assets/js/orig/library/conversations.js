ebindr.library.conversations = new Class({

	instances: $H(), // an array containing all the conversations open
	options: $H({
		lastget: new Date().getTime(),
		increment: 0, // every 3 seconds to start (is the id of the intervals)
		intervals: [
			5000, 10000, 30000, 60000
		]
	}), // options for the conversations class
	skip: false, // skip a retrieval
	
	// class constructor
	initialize: function( options ) {
		$extend(this.options,options);
		// log that this class has been loaded
		ebindr.console( 'ebindr.conversations loaded' );
		// load the chat css
		ebindr.include( '/ebindr/styles/chat.css' );
	},
	
	/*
		Throw a message that this is the end of previous conversations
	*/
	previous: function( cid ) {
		this.instances.get(cid).add( 'previous', '', 'End of Previous Conversations' );
	},
	
	/*
		Update the chats and create a new chat if need be
	*/
	update: function( cid, self, name, message ) {
		// if a chat is found and there isn't an instance for that user then open a new window, minimize, and growl alert the user
		if( !this.instances.get(cid) ) {
			// set the function to get the chat started
			var start = function( alert ) {
				alert = ( typeof(alert) == 'undefined' ? false : alert );
				new Request.JSON({
					url: '/ebindr/community.php/chat/open',
					onComplete: function(data,str) {
						data.msg = {
							'self': self,
							'name': name,
							'message': message
						}
						ebindr.conversations.open(data);
						if( alert ) ebindr.conversations.alert( cid, name, message );
					}
				}).post({
					'cid': cid,
					'uid': Cookie.read("uid"),
					'key': ebindr.key,
					'id': ebindr.id
				});
			}

			// make sure the connect window is open
			if( !$('connect_content') ) {
				ebindr.button.go('nc', start);
			} else {
				start( true );
			}

			
//			this.open(id, css, name, message);
		} else {
			var chat = this.instances.get(cid);
			// add it to the chat
			chat.add( self, name, message );
			
			if( $('connect' ) ) {
				if( $('connect').getStyle('visibility') == 'hidden' || !$('connect').hasClass('isFocused') ) {
					this.alert( cid, name, message );
				}
			}
		}
	},
	
	/*
		Alert them
	*/
	alert: function( cid, name, message ) {
		ebindr.growl( '<b>' + name + ' says:</b>', message.substr(0,50) + ( message.length > 50 ? '...' : '' ), false, '#000000', function() {
    		ebindr.window.library.Dock.restoreMinimized($('connect'));
    		ebindr.window.library.focusWindow( $('connect') );
    		ebindr.conversations.instances.get(cid).tab.fireEvent('click');
    	});
	},
	
	/*
		Read the response of messages
	*/
	response: function( data ) {
    	// make sure we have something
    	if( data.length > 0 ) {
    		// reset the interval increment and mark that we had data
    		ebindr.conversations.options.increment = 0;
    		ebindr.conversations.options.lastget = new Date().getTime();
    		// go through each one
    		data.each( function(chat) {
    			ebindr.conversations.update( chat.cid, chat.self, chat.name, chat.message );
    		});
    	// if we don't have any data then mark the time
    	} else {
    		// if we are older than 60 seconds
    		if( ebindr.conversations.options.lastget < new Date().getTime()-60000 && ebindr.conversations.options.increment < 4 ) {
    			ebindr.conversations.options.increment++;
    			ebindr.conversations.options.lastget = new Date().getTime();
    		}
    	}
	},
	
	/*
		Send a message to a conversation
	*/
	send: function( cid, message ) {
		// make sure we have a message
		if( $chk(message) && message != '' && message.length > 0 && $chk(cid) ) {
			// skip the next retrieval
			this.skip = true;
			new Request.JSON({
				url: '/ebindr/community.php/chat/send',
				onSuccess: function(data,str) {
					ebindr.conversations.response(data);
				}
			}).post({
				'cid': cid,
				'uid': Cookie.read("uid"),
				'message': escape(message),
				'key': ebindr.key,
				'id': ebindr.id
			});
		}
	},
	
	/*
		Open up a chat, whether you started it or someone else did
	*/
	open: function( data ) {
		// create the div that contains this chat
		var screen = new Element( 'div', {
			'class': 'chat-screen',
			'id': 'chat-' + data.cid
		}).inject( $('connect_content').getElements('#conversations')[0] );
		// add the participants div
		var users = new Element( 'div', {
			'class': 'participants',
			'html': '<div>Participants</div><ul class="list"></ul>'
		}).inject( screen );
		// main chatting div
		var main = new Element( 'div', {
			'class': 'main'
		}).inject( screen );
		// now we need the messages div with scolling
		var msgs = new Element( 'div', {
			'class': 'msgs',
			'html': '<table class="chat"></table>'
		}).inject( main );
		// then finally the text area
		var textbox = new Element( 'textarea', {
			'events': {
				'focus': function(e) {
					ebindr.current.focus = this;
				},
				'keyup': function(e) {
					if( e.key == 'enter' ) {
						e.stop();
						ebindr.conversations.send( data.cid, this.get('value') );
						this.value = '';
					}
				}
			}
		}).inject( main );
		
		// add the tab to the chats list
		var tmp = [];
		data.participants.each(function(user) {
			tmp.push(user.name);
		});
		var title = tmp.join(', ');
		title = ( title.length > 30 ? title.substr(0,28) + '...' : title );
		var tab = new Element( 'div', {
			'events': {
				'click': function() {
					ebindr.conversations.instances.get(data.cid).show();
					$$('#chats-list div.active').removeClass('active');
					this.addClass('active');
				}
			}
		}).inject( $('chats-list'), 'bottom' ).set('text', title );
		
		ebindr.conversations.instances.set( data.cid, {
			screen: screen,
			participantlist: users.getElements('ul')[0],
			msgs: msgs,
			textbox: textbox,
			tab: tab,
			show: function() {
				$('connect_content').getElements('div.chat-screen').setStyle( 'display', 'none' );
				this.screen.setStyle( 'display', 'block' );
				this.screen.getElements('textarea')[0].focus();
			},
			table: msgs.getElements('table.chat')[0],
			participants: data.participants,
			add: function( self, name, message ) {
				var css = ( ( self == 'yes' || self == 'you' ) ? 'you' : ( self == 'previous' ? self : '' ) );
				var template = '<tr class="{css}"><td class="person"><span>{name}</span></td><td class="body"><div>{body}</div></td></tr>';
				this.table.set( 'html', this.table.get('html') + template.substitute({
					css: 'text_message' + ( css.length > 0 ? ' ' + css : '' ),
					name: name,
					body: message
				}));
				
				this.msgs.scrollTo( 0, this.msgs.getScrollSize().y );
			
			}
		});
		
		ebindr.layout.resize.chat();
		
		var list = ebindr.conversations.instances.get(data.cid).participantlist;
		data.participants.each(function(user) {
			new Element( 'li', {
				'text': user.name
			}).inject(list);
		});
		
		// if we have some history
		if( data.history.length > 0 ) {
			data.history.each( function(msg) {
				ebindr.conversations.instances.get(data.cid).add( msg.self, msg.name, msg.message );
			});
			// mark the end of previous conversations
			ebindr.conversations.previous(data.cid);
		}
		
		// if we have a current message
		if( $chk(data.msg) ) {
			ebindr.conversations.instances.get(data.cid).add( data.msg.self, data.msg.name, data.msg.message );
		}
	},
	
	/*
		Start a new conversation
	*/
	start: function( participants ) {
		new Request.JSON({
			url: '/ebindr/community.php/chat/start',
			onComplete: function(data,str) {
				ebindr.conversations.open(data);
			}
		}).post({
			'users': participants,
			'uid': Cookie.read("uid"),
			'key': ebindr.key,
			'id': ebindr.id
		});
		
	/*	
{
	"cid": "3",
	"participants":[{"uid":"2","name":"Nathan"},{"uid":"3","name":"Troy Teeples"}],
	"history":[
		{"stamp":"2009-12-21 09:36:53","cid":"1","self":"yes","name":"doug","message":"this is my first test"},
		{"stamp":"2009-12-21 13:52:27","cid":"1","self":"yes","name":"doug","message":"testing"}
	]
}*/
		
	},
	
	/*
		This method checks the server for new messages or conversations.
	*/
	routine: function() {
		if( ( ebindr.isHurdmantest() && ebindr.isHurdman() ) || !ebindr.isHurdman() ) {
			if( !this.skip ) {
				(function(){
					new Request.JSON({
						url: '/ebindr/community.php/chat/get',
						onSuccess: function(data) {
							ebindr.conversations.response(data);
						},
						onComplete: function() {
					    	// call itself again
					    	ebindr.conversations.routine();
						}
					}).post({
						"uid": Cookie.read("uid"),
						"key": ebindr.key,
						"id": ebindr.id
					});
				}).delay( this.options.intervals[ ( this.options.increment > 3 ? 3 : this.options.increment ) ] );
			} else {
				// reset it for next time
				this.skip = false;
				// and call it for next time
				this.routine();
			}
		}
	}
	
});