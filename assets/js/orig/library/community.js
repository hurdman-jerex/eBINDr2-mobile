// add methods to the community
ebindr.community.extend({

	/*
		This method will retrieve an array of active users
	*/
	activeUsers: function() {
		new Request.JSON({
			url: '/ebindr/components/user/activeusers',
			nocache: true,
			onComplete: function( data, str ) {
				if( typeof(data) == 'object' && str != '//No data' ) {
					data.resultset.each( function( row, i ) {
						// see if we are ourselves
						if( Cookie.read("name") == row.name && Cookie.read("reportr_db") == row.bbbdesc.replace("hurdman","hurdmantest") ) var self = true;
						else self = false;
						
						if( !self ) {
							var person = new Element( 'li', {
								'html': '<a href="#">' + row.name + '</a>',
								'styles': {
									'cursor': 'pointer'
								},
								'events': {
									'mouseenter': function() {
										this.setStyle( 'background-color', '#ececec' );
									},
									'mouseleave': function() {
										this.setStyle( 'background-color', '' );
									},
									'click': function() {
										ebindr.chat.open( row.uid, '', row.name );
									}
								}
							});
							// see if we are a co-worker
							if( row.bbbdesc == Cookie.read("reportr_db") )
								person.inject( $( 'chat-co-workers' ) );
							else
								person.inject( $('chat-' + row.bbbdesc ) );
								
							// if we are hurdman then add to the hurdman this is only if we want to show hurdman staff in
							// the co-workers as well as the hurdman group
							/*if( row.bbbdesc == 'hurdmantest' )
								person.clone().cloneEvents(person).inject( $('chat-hurdman' ) );*/
						}
					});
				}
			}
		}).get();
	}

});