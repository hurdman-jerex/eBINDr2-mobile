ebindr.library.tab = new Class({

	Implements: [Events, Options],
	tabs: new $H(),
	
	initialize: function( options ) {
		// log that this class has been loaded
		ebindr.console( 'ebindr.tabs loaded' );
		this.setup();
		// add the click event to the tabs
		$$( '.col.one li, .col.five li' ).each( function( tab, i ) {
			// make sure we are on the utility or spacer
			if( !tab.hasClass( 'utility' ) && !tab.hasClass( 'spacer' ) ) {
				// add the click
				tab.addEvent( 'click', function() {
					ebindr.tab.tabs[ tab.lang ][ ( tab.getParent().hasClass( 'five' ) ? 'right' : 'left' )]['click']( this );
					window.fireEvent('resize');
				});
			}
		});
	},
	
	setup: function() {
		this.tabs = {
			'records': {
				'left': {
					'el': $$( '.col.one li' )[0],
					'click': function(el) {
						ebindr.user.active=true;
						ebindr.alerts();
						// set the current tab
						ebindr.current.tab = 'records';
						ebindr.current.page = 'records';
						ebindr.keyboard.frame('b');
						// hide all the other tabs
						$$( 'col.one li' ).addClass( 'hidden' );
						// make this one active
						this.el.addClass( 'active' ).removeClass( 'hidden' ).removeClass( 'inactive' );
						// hide all other left tabs and make them show on the right
						ebindr.tab.tabs.database.left.el.addClass( 'hidden' ).removeClass( 'active' );
						ebindr.tab.tabs.community.left.el.addClass( 'hidden' ).removeClass( 'active' );
						ebindr.tab.tabs.database.right.el.addClass( 'lead' ).removeClass( 'hidden' );
						ebindr.tab.tabs.community.right.el.removeClass( 'lead' ).removeClass( 'hidden');
						$$( 'li.utility' )[0].removeClass( 'lead' );
						// hide all pages
						$$( '.page' ).setStyle( 'display', 'none' );
						$$( '.page.' + el.lang ).setStyle( 'display', '' );
					}
				}
			},
			'database': {
				'left': {
					'el': $$( '.col.one li' )[1],
					'click': function(el) {
						// set the current tab
						ebindr.current.tab = 'database';
						ebindr.current.page = 'database';
						ebindr.keyboard.frame('r');
						// make records inactive and community hidden
						ebindr.tab.tabs.records.left.el.addClass( 'inactive' ).removeClass( 'active' );
						ebindr.tab.tabs.community.left.el.addClass( 'hidden' );
						// make this one active and the right element hidden, and the community element on the right lead
						this.el.addClass( 'active' ).removeClass( 'hidden' ).removeClass( 'inactive' );
						ebindr.tab.tabs.database.right.el.addClass( 'hidden' ).removeClass( 'lead' );
						ebindr.tab.tabs.community.right.el.addClass( 'lead' ).removeClass( 'hidden' );
						$$( 'li.utility' )[0].removeClass( 'lead' );
						// hide all pages
						$$( '.page' ).setStyle( 'display', 'none' );
						$$( '.page.' + el.lang ).setStyle( 'display', '' );
					}
				},
				'right': {
					'el': $$( '.col.five li' )[1],
					'click': function(el) {
						ebindr.tab.tabs[ el.lang ]['left']['click'](el);
					}
				}
			},
			'community': {
				'left': {
					'el': $$( '.col.one li' )[2],
					'click': function(el) {
						// set the current tab
						ebindr.current.tab = 'community';
						ebindr.current.page = 'community';
						ebindr.keyboard.frame('h');
						// make records inactive and community hidden
						ebindr.tab.tabs.records.left.el.addClass( 'inactive' ).removeClass( 'active' );
						ebindr.tab.tabs.database.left.el.addClass( 'inactive' ).removeClass( 'active' ).removeClass( 'hidden' );
						// make this one active and the right element hidden, and the community element on the right lead
						this.el.addClass( 'active' ).removeClass( 'hidden' ).removeClass( 'inactive' );
						ebindr.tab.tabs.database.right.el.addClass( 'hidden' ).removeClass( 'lead' );
						ebindr.tab.tabs.community.right.el.removeClass( 'lead' ).addClass( 'hidden' );
						$$( 'li.utility' )[0].addClass( 'lead' );
						// hide all pages
						$$( '.page' ).setStyle( 'display', 'none' );
						$$( '.page.' + el.lang ).setStyle( 'display', '' );
					}
				},
				'right': {
					'el': $$( '.col.five li' )[2],
					'click': function(el) {
						ebindr.tab.tabs[ el.lang ]['left']['click'](el);
					}
				}
			}
		}

	}
	
});