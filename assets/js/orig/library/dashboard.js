ebindr.library.dashboard = new Class({

	options: $H({
		win: null
	}), // options for the chat class

	initialize: function( options ) {
		//$extend(this.options,options);
		this.parameters = $H();
	},
	
	addWidgets: function() {
		ebindr.window.normal( 'widgets', {
			contentURL: '/ebindr/views/dashboard/widgets.html',
			title: 'Add Widgets',
			id: 'widgets',
			type: 'modal', 
			onContentLoaded: function() {
				$('widgets_iframe').contentWindow.$$('input.save').addEvent( 'click', function(e) {
					ebindr.window.parent.closeWindow($('widgets'));
				});
			},
			onClose: function() {
				var checkboxes = $('widgets_iframe').contentWindow.$$('div.section input[type=checkbox]');
				var changes = false;
				var widgets = [];
				// go through the checkboxes and get the source ids
				checkboxes.each( function(checkbox,i) {
					// if we are checked then add it to the table
					if( checkbox.checked ) {
						widgets.push(checkbox.value);
						changes = true;
					}
				});
				// there were changes to reload the dashboard
				if( changes ) {
					new Request({
    					url: '/report/dashboard.add.widget?ebindr2=y&noheader',
    					onComplete: function() {
	    					$('dashboard_iframe').contentWindow.location.reload();
    					}
    				}).post({
    					'width': 400,
    					'height': 100,
    					'sid': widgets.join(',')
    				});
				}
			},
			padding: { left: 0, right: 0, top: 0, left: 0 },
			width: 400,
			height: window.getSize().y-109
		});



	},
	
	/*
		Bind a variable to the dashboard
	*/
	bind: function( name, value ) {
		this.parameters[name] = value;
	},
	
	/* 
		Add the focus ability to bring one window in front of another
	*/
	focusability: function() {
		this.win.$$( '.module' ).addEvent( 'mousedown', function(e) {
			ebindr.dashboard.win.$$( '.module' ).each( function(el,i) {
				el.setStyle( 'z-index', (i+1) );
			});
			this.setStyle( 'z-index', 100 );
		});
	},
	
	/*
		Reload the dashboard
	*/
	reload: function() {
		//this.win.
	},
	
	/*
		Add events
	*/
	addevents: function() {
		// get each of the widgets
		this.win.$$( '.module' ).each( function(module) {
			// resize the modules initially
			module.getElements('.content')[0].setStyles({
				'height': (module.getCoordinates().height-35),
				'width': (module.getCoordinates().width-12)
			});
			// set the initial drag limits
			module.store( 'limits', {
				x: [0, (window.getCoordinates().width-module.getCoordinates().width )],
				y: [0, (window.getCoordinates().height-module.getCoordinates().height )]
			});
			// allow resizing
			var resize = module.makeResizable({
				grid: 10,
				snap: 10,
				onComplete: function(el) {
					ebindr.dashboard.save(el);
					el.store( 'limits', {
						x: [0, (window.getCoordinates().width-el.getCoordinates().width )],
						y: [0, (window.getCoordinates().height-el.getCoordinates().height )]
					});
					el.getElements('.content')[0].setStyles({
						'height': (el.getCoordinates().height-35),
						'width': (el.getCoordinates().width-12)
					});
					module.getElements( '.handle a.reload' )[0].fireEvent( 'click' );
				}
			});
			// store the resizing
			module.store('resize', 'detached');
			resize.detach();
			// add the clicking to the buttons
			module.getElements( '.handle a[class!=reload]' ).addEvent( 'click', function(e) {
				e.stop();
				var mod = this.getParent().getParent();
				// for the resizing
				if( this.hasClass('resize') ) {
					if( module.retrieve('resize') == 'detached' ) {
						this.getElements('img')[0].src = '/ebindr/images/body/dash-resize-active.png';
						resize.attach();
						module.store('resize','attached');
						module.setStyle( 'cursor', 'se-resize' );
					} else {
						this.getElements('img')[0].src = '/ebindr/images/body/dash-resize.png';
						resize.detach();
						module.store('resize','detached');
						module.setStyle( 'cursor', '' );
					}
				} else if( this.hasClass('close') ) {
					// confirm they want to remove this module
					ebindr.modal.confirm( 'Are you sure you want to remove this widget?', function(ret) {
						if( ret ) {
							// remove the dom element
							mod.destroy();
							// update the db
							new Request({
								url: '/report/dashboard.module.remove?ebindr2=y&noheader'
							}).post({
								'mid': mod.id.replace("mod-","")
							});
						}
					}, ['Yes, Remove', 'No' ]);
				} else if( this.hasClass('minimize') ) {
					var get = module.retrieve('height');
					if( !get ) get = 0;
					if( get.length > 1 ) {
						module.setStyle( 'height', get );
						module.store( 'height', 0 );
						module.getElements('.content').setStyle('display', '');
					} else {
						module.getElements('.content').setStyle('display', 'none');
						module.store( 'height', module.getStyle( 'height' ) );
						module.setStyle( 'height', 25 );
					}
				}
			});
			// add the dragging ability
			var moddrag = new Drag( module, {
				grid: 10,
				handle: module.getElements('.handle')[0],
				limit: module.retrieve('limits'),
				onBeforeStart: function() {
					resize.detach();
				},
				onDrag: function() {
					this.limit = module.retrieve('limits');
				},
				onComplete: function(el) {
					if( el.retrieve('resize') == 'attached' ) resize.attach();
					ebindr.dashboard.save(el);
				}
			});
		});
	},
	
	/*
		Handle a json report result
	*/
	report: function( query, update ) {
		new Request.JSON({
			url: '/report/exportr,'+ query +'/',
			onComplete: function(data,str) {
				var table = '';
				for(i=0;i<data.resultset.length;i++) {
				table += '<table cellpadding="0" cellspacing="0" width="100%" class="dataset">';
					if(! (typeof data.resultset[i]['data'] === 'undefined') ){
						var rowone = $H(data.resultset[i]['data'][0]);
						table += '<tr><td class="desc" colspan="' + data.resultset[i]['data'].length + '">'+ data.resultset[i]['desc'] +'</td></tr>';
						table += '<tr>';
						rowone.each(function(value,name) {
							table += '<th>' + name + '</th>';
						});
						table += '</tr>';
						$A(data.resultset[i]['data']).each( function(row) {
							table += '<tr>';
							$H(row).each(function(value,name) {
								if( value == null ) value = '';
								table += '<td' + ( value.test(/^[-+]?[0-9.]+$/) ? ' class="number"' : '' ) + '>' + value + '</td>';
							});
							table += '</tr>';
						});
					}
					table += '</table>';
				}
				update.set('html',table);
			}
		}).get({
			'ebindr2': 'y',
			'USEDEFAULTS': '',
			'json': 'y'
		});
	},
	
	/*
		Load RSS feed
	*/
	rss: function(url, update) {
		new Request.HTML({
			update: update
		}).get( '/ebindr/widgets/rss.php?url=' + url.substitute(ebindr.dashboard.parameters) );
	},
	
	/*
		Just load an html page
	*/
	html: function(url, update) {
		var width = update.getCoordinates().width-20;
		var height = update.getCoordinates().height-20;
		new Request.HTML({
			update: update
		}).get( url.substitute(ebindr.dashboard.parameters) + (url.contains('?') ? '&' : '?' ) + 'width=' + width + '&height=' + height );
	},
	
	/*
		Load an image as a graph or chart
	*/
	graph: function(url, update) {
		update.set( 'html', '<img src="' + url + '" />' );
	},
	
	/*
		Load the source of an image for a google chart
	*/
	chart: function(url, update) {
		update.set( 'html', '<img src="' + url + '" />' );
	},
	
	/*
		Load data for the widgets, accepts an object of sources
	*/
	load: function(sources) {
		// go through each source
		sources.each( function(src,i) {
			// find the reload button and the modules window
			var mod = ebindr.dashboard.win.$('mod-'+src[1]);
			var reload = mod.getElements('div.handle a.reload')[0];
			var content = mod.getElements('div.content')[0];
			// we actually have a source id
			if( src[0].length > 0 ) {
				switch( src[2] ) {
					case "report":
						reload.addEvent( 'click', function(e) {
							content.set( 'html', '<img src="/ebindr/themes/default/images/spinner.gif" />' );
							ebindr.dashboard.report( src[0], content );
						});
						reload.fireEvent( 'click' );
						break;
					case "rss":
						reload.addEvent( 'click', function(e) {
							content.set( 'html', '<img src="/ebindr/themes/default/images/spinner.gif" />' );
							ebindr.dashboard.rss( src[0], content );
						});
						reload.fireEvent( 'click' );
						break;
					case "html":
						reload.addEvent( 'click', function(e) {
							content.set( 'html', '<img src="/ebindr/themes/default/images/spinner.gif" />' );
							ebindr.dashboard.html( src[0], content );
						});
						reload.fireEvent( 'click' );
						break;
					case "graph":
						reload.addEvent( 'click', function(e) {
							content.set( 'html', '<img src="/ebindr/themes/default/images/spinner.gif" />' );
							ebindr.dashboard.graph( src[0], content );
						});
						reload.fireEvent( 'click' );
						break;
					case "chart":
						reload.addEvent( 'click', function(e) {
							var width = content.getCoordinates().width-20;
							var height = content.getCoordinates().height-20;
							content.set( 'html', '<img src="/ebindr/themes/default/images/spinner.gif" />' );
							new Request({
								onComplete: function(text) {
									ebindr.dashboard.graph( text, content );
								}
							}).get( '/ebindr/widgets/chart.php?sid=' + src[3] + '&width=' + width + '&height=' + height );
						});
						reload.fireEvent('click');
						break;
				}
			}
		});

	},
	
	/*
		Save the window coordinates to the database
	*/
	save: function( el ) {
		var coords = el.getCoordinates();
		new Request({
			url: '/report/dashboard.set.coordinates?ebindr2=y&noheader'
		}).post({
			'x': coords.left,
			'y': coords.top,
			'width': coords.width,
			'height': coords.height,
			'mid': el.id.replace("mod-","")
		});
	},
	
	/*
		Save whether this should popup on startup or not
	*/
	onstartup: function( action ) {
		new Request({
			url: '/report/dashboard.set.onstartup?ebindr2=y&noheader'
		}).post({
			'action': ( action ? '1' : '0' )
		});
	},
	
	/*
		Open the dashboard window
	*/
	open: function() {
		ebindr.window.normal( 'dashboard', {
			contentURL: '/ebindr/views/dashboard/index.html',
			title: 'Dashboard - <span style="font-weight: normal;">logged in as ' + Cookie.read("staff_name").replace(/\+/gi,' ') + '</span>',
			id: 'dashboard',
			//toolbar: true,
			//toolbarURL: '/ebindr/views/dashboard/toolbar.html',
			onContentLoaded: function() {
				ebindr.dashboard.win.$('dash-add').addEvent( 'click', function(e) {
					e.stop();
					ebindr.dashboard.addWidgets();
				});
				ebindr.dashboard.win.$('onlogin').addEvent( 'click', function(e) {
					ebindr.dashboard.onstartup( this.checked );
				});
			},
			storeOnClose: true,
			padding: { left: 0, right: 0, top: 0, left: 0 },
			width: window.getSize().x-65,
			height: window.getSize().y-80
		});
	}
	
});