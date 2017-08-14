ebindr.library.tab = new Class({

	Implements: [Events, Options],
	// the tab options
	options: {
		tabs: new Hash({})
	},
	
	initialize: function( options ) {
		$extend(this.options,options);
		// log that this class has been loaded
		ebindr.console( 'ebindr.tab loaded' );
		this.find();
	},
	
	/*
		This method will find the tabs and organize them
	*/
	find: function() {
		// local reference
		var obj = this;
		// find the tab containers
		$$( '#left-tab,#right-tab,#tab-l,#tab-r' ).each( function(tabs,i) {
			// now get each tab inside the containers
			tabs.getElements( 'div.tab' ).each( function(tab,t) {
				// set the left and right
				if( typeof( obj.options.tabs[tab.lang] ) != "undefined" ) {
					var left = ( typeof( obj.options.tabs[tab.lang].left ) != "undefined" ? obj.options.tabs[tab.lang].left : '' );
					var right = ( typeof( obj.options.tabs[tab.lang].right ) != "undefined" ? obj.options.tabs[tab.lang].right : '' );
				}
				// add it to the tabs hash
				obj.add( tab.lang, {
					order: t,
					name: tab.lang,
					left: ( i == 0 ? tab : left ),
					right: ( i == 1 ? tab : right )
				});
				// add the click events
				tab.addEvent( 'click', function(e) {
					// fire the click event
					ebindr.tab.click( this.lang );
				});
			});
		});
	},
	
	/*
		This clicking of the tab event
	*/
	click: function( name ) {
		// set the tab for simplicity
		var tab = this.options.tabs[name];
		// make sure it isn't already active
		if( !tab.left.hasClass( 'active' ) ) {
			// log that we have clicked a new tab
			ebindr.log( 'Tab Clicked ' + name );
			// set the current tab
			ebindr.current.tab = name;
			ebindr.current.page = name;
			// show the left tab, hide the right
			tab.left.addClass( 'active' ).removeClass( 'hidden' ).removeClass( 'inactive' );
			tab.right.addClass( 'hidden' ).removeClass( 'top' );
			tab.right.getNext().addClass('top');
			// go through each tab
			i=0;
			this.options.tabs.each( function(mytab) {
				// if i is less than order (above the tab in order) then remove active and hide the right ones
				if( i < tab.order ) {
					mytab.left.removeClass('active').removeClass('hidden').addClass('inactive');
					mytab.right.addClass('hidden').removeClass('active');
				}
				// if i is greater than order then hide the left and show the right
				if( i > tab.order ) {
					mytab.left.addClass('hidden').removeClass('active');
					mytab.right.removeClass('hidden').removeClass('active').addClass('inactive');
				}
				// increment
				i++;
			});
			// hide all pages, and show the clicked one
			$$( 'div.page' ).setStyle( 'display', 'none' );
			$$( 'div.page.' + tab.name ).each( function(page) {
				page.setStyle( 'display', 'block' );
			});
			// run the resize
			ebindr.resize();
		}
	},
	
	/*
		This defines or adds to a tab
	*/
	add: function( name, properties ) {
		// create the hash if need be
		if( typeof(this.options.tabs[name]) == "undefined" ) this.options.tabs[name] = {};
		// merge the hash together
		$extend(this.options.tabs[name],properties || {});
	}

});