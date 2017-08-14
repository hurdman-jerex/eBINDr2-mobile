// add methods/functions to ebindr
ebindr.extend({

	layout: {
    	// set the widths of the tabs and the spine spacer, and rings
    	tabWidth: 32,
    	spineWidth: 5,
    	ringWidth: 70,
    	ringHeight: 10,
    	taskbarThreshold: 40,
    	
    	// a resize hash to store resizing functions
    	resize: {
    		tickettrackr: function() {
				var height = $('tickettrackr_iframe').getStyle( 'height' );
	    		$('tickettrackr_current').setStyle( 'height', height );
	    		$('tickettrackr_new').setStyle( 'height', height );
    		},
    		
    		chat: function() {
				var height = $('connect_contentWrapper').getStyle('height');
				$('connect_content').setStyle( 'height', height );
	    		if( $('connect_people') ) $('connect_people').setStyle( 'height', height );
	    		$('connect_content').getElements('div#chats-list, div.msgs, div.participants').each( function(div) {
	    			if( div.hasClass( 'msgs' ) ) div.setStyle( 'height', (height.toInt()-50) );
	    			else if( div.id == 'chats-list' ) div.setStyle( 'height', height );
	    			else if( div.hasClass( 'participants' ) ) div.setStyle( 'height', height );
	    		});
    		},
    		
    		center: function( windowEl ) {
	    		ebindr.window.parent.resizeWindow($(windowEl.options.id), {
	    			width: window.getSize().x-200,
	    			height: window.getSize().y-200,
	    			centered: true,
	    			left: 85,
	    			top: 60
	    		});
    		},
    		
    		fitleft: function( windowEl ) {
    			//alert( windowEl.options.id );
	    		ebindr.window.parent.resizeWindow($(windowEl.options.id), {
	    			width: (ebindr.layout.set.pageWidth()-10),
	    			height: (ebindr.layout.height - 82),
	    			top: 1,
	    			left: 17
	    		});
	    		
	    		
    		},
    		
    		fitright: function( windowEl ) {
    			var width = (ebindr.layout.set.pageWidth()-10);
	    		ebindr.window.parent.resizeWindow($(windowEl.options.id), {
	    			width: width,
	    			height: (ebindr.layout.height - 82),
	    			top: 1,
	    			left: (17+width+25)
	    		});

    		}
    	},
    	
    	// taskbar hash
    	taskbar: {
    		visible: false,
    		// fx tween
    		fx: new Fx.Tween( $('taskbar'), {
    			duration: 200
    		}),
    		
    		// setup some defaults for the taskbar
    		build: function() {
    			// set styles and the mouselave event
    			$('taskbar').setStyles({
    				'width': ebindr.layout.width,
    				'height': 25
    				
    			}).addEvent( 'mouseleave', function(e) {
    				ebindr.layout.taskbar.hide();
    			});
    			
    			// add the event to watch for the taskbar
    			( Browser.Engine.trident ? $('ebindr') : window ).addEvent( 'mousemove', function(e) {	
    				// show the taskbar when the mouse goes to the bottom
    				if( e.client.y > ( ebindr.layout.port.height-ebindr.layout.taskbarThreshold ) ) ebindr.layout.taskbar.show();
    				// hide the taskbar
    				if( ebindr.layout.taskbar.visible ) {
    					if( e.client.y < ( ebindr.layout.port.height-ebindr.layout.taskbarThreshold ) ) ebindr.layout.taskbar.hide();
    				}
    			});
    		},
    		
    		// show the taskbar
    		show: function(e) {
    			ebindr.layout.taskbar.visible = true;
    			if( $('taskbar').getStyle( 'bottom' ).toInt() < 0 )
    				this.fx.start( 'bottom', -28, 0 );
    		},
    		
    		// hide the taskbar
    		hide: function(e) {
    			if( ebindr.layout.taskbar.visible ) {
	    			if( $('taskbar').getStyle( 'bottom' ).toInt() > -1 )
    					this.fx.start( 'bottom', 0, -28 );
    			}
    		}
    		
    	},
    	
    	// the set collection
    	set: {
    	
    		// the main function to set the page dimensions
    		dimensions: function() {
    			// find the width and height
    			ebindr.layout.width = ( window.getCoordinates().width < 800 ? 800 : window.getCoordinates().width );
    			ebindr.layout.height = ( window.getCoordinates().height < 600 ? 600 : window.getCoordinates().height );
    			ebindr.layout.port = {
    				width: window.getSize().x,
    				height: window.getSize().y
    			}
    			// set ebindr to these heights
    			$('ebindr').setStyles({
    				'width': ebindr.layout.width,
    				'height': ebindr.layout.height
    			});
    			
    	   		// set the overflow to auto if we are below 900x600
	    		$$( 'html' ).setStyle( 'overflow', ( ( window.getCoordinates().height < 600 || window.getCoordinates().width < 800 ) ? 'auto' : 'hidden' ) );
	    		// set the width and height of the body
    			$$( 'body' ).setStyles({
    				'width': ebindr.layout.width,
    				'height': ebindr.layout.height
	    		});
    
    			this.colHeight();
    			var pW = this.pageWidth();
    			this.rings();
    			this.pageHeight();
    			ebindr.layout.taskbar.build();
    			this.frames();
    			this.autosize(pW);
    	if( $('always') ) {		
		ebindr.library.boxframe.set( 'frame_m', {
			staticWidth: 200,
			thresholds: [ 700, 435 ]
		});
    	}
    		},
    		
    		// autosizing block elements in frames
    		autosize: function( pageWidth ) {
    			// resize the business frame details as the page grows
    			var b_avail = $$('#b .container')[0].getCoordinates().width;
    			// available - 10(padding) - 75 for rating column
    			$$('#b .container .details')[0].setStyle( 'width', b_avail-10-71 );
    		
    		
    			var height = 0;
    			// get all first child elements in the business frame
    			$('b').getChildren( '.block' ).each( function(el) {
    				// find the height
    				height = height + el.getSize().y;
    			});

    			$('b').getChildren( '.autosize' )[0].setStyle( 'height', $('b').getCoordinates().height - height- 28 );
    			
    			height = 0;    			

          		$('m').getChildren( '.block' ).each( function(el) {
    				// find the height
    				height = height + el.getSize().y;
    			});
    			
    			$('m').getChildren( '.autosize' )[0].setStyle( 'height', $('m').getCoordinates().height - height );
    			
    			if( height > 0 ) {
    				// delay the following to set it correctly
					/*(function() {
						// need to find each scoller
						$$( '.scroller' ).each( function(section, i) {
							if( section.getElements( '.vScrollbar' ).length > 0 ) {
								// update the scrolling dimensions
								if( section.getParent().id == 'm' ) pageWidth = pageWidth - 105;
								ebindr.scrollers[i].update( pageWidth );
							} else {
								// initialize the scroller
								ebindr.scrollers.push( new ScrollBar( section, section.getElements('.content')[0], {
									arrows: false,
									hScroll: false
								}));
							}
						});
					// end delayed function
					}).delay(100);*/
    			
    			}
    			
    			height = 0;
    			
    			
       			$('c').getChildren( '.block' ).each( function(el) {
    				// find the height
    				//alert( el.getCoordinates().height );
    				height = height + el.getSize().y;
    			});
//    			$('c').getChildren( '.autosize' )[0].setStyle( 'height', $('c').getStyle('height').toInt() - height + 'px' );
    			$('c').getChildren( '.autosize' ).each(function(mydiv) {
	    			mydiv.setStyle( 'height', $('c').getStyle('height').toInt() - height + 'px' );
	    		});
    			
    			$$('#b .btnbar').setStyle( 'width', $('b').getChildren( '.autosize' )[0].getCoordinates().width );
    			
    			// set the height of the inside window pane of the business frame
    			$('b').getChildren( '.autosize' )[0].getChildren( 'iframe' )[0].setStyles({
    				'width': $('b').getChildren( '.autosize' )[0].getCoordinates().width + 'px',
    				'height': $('b').getChildren( '.autosize' )[0].getCoordinates().height + 'px'
    			});
    			// set the height of the iframe in the membership frame
    			$('m').getChildren( '.autosize' )[0].getChildren( 'iframe' )[0].setStyles({
    				'height': $('m').getChildren( '.autosize' )[0].getCoordinates().height/*-5-31*/ + 'px',
    				'width': $('m').getChildren( '.autosize' )[0].getCoordinates().width/*-10*/ + 'px'
    			});
       			// set the height of the iframe in the complaint frame
    			$('c').getChildren( '.autosize' ).each(function(mydiv) {
	    			mydiv.getChildren( 'iframe' )[0].setStyles({
	    				'height': mydiv.getCoordinates().height + 'px',
	    				'width': mydiv.getCoordinates().width + 'px'
	    			});
	    		});
       			// set the height of the iframe in the registers frame
    			$('registers').setStyles({
    				'height': $('r').getCoordinates().height - 40 + 'px',
    				'width': $('r').getCoordinates().width - 3 + 'px', // -15 for the alphabet on the side
    				'overflow': 'auto'
    			});
    			
    			$('popular-queue').setStyles({
    				'height': $('q').getCoordinates().height - 40 - $('popular-queue').getPrevious().getCoordinates().height + 'px',
    				'overflow': 'auto'
    			});
    			
    		},
    		
    		// set the heights of the frames
    		frames: function() {
    			// set the height of the inquiry and business frames
    			$('i').setStyle( 'height', 90 );
    			$('b').setStyle( 'height', ebindr.layout.height - 90 - 40 ); // -40 for top and bottom
    			// accredition and complaint frames
    			$('m').setStyle( 'height', ( (ebindr.layout.height-40)/2 ).round(1) + 'px' );
    			$('c').setStyle( 'height', ( (ebindr.layout.height-40)/2 ).round(1) + 'px' );
    			
    			// may not need to do this, just make it fluid
//    			$('a').setStyle( 'height', ( (ebindr.layout.height-40)/4 ).round(1) + 'px' );
//    			$('u').setStyle( 'height', ( (ebindr.layout.height-40)/4 ).round(1) + 'px' );
//    			$('v').setStyle( 'height', ( (ebindr.layout.height-40)/4 ).round(1) + 'px' );
//     			$('d').setStyle( 'height', ( (ebindr.layout.height-40)/4 ).round(1) + 'px' );
				/*if( window.getCoordinates().width < 1024 ) $$( '.col.four div.wrapper div.frame' ).setStyles({
					'width': '98%',
					'margin': '1%'
				});
				else $$( '.col.four div.wrapper div.frame' ).setStyles({
					'width': '46%',
					'margin': '1%'
				});*/
    			// need to do it here for registers
    			$('r').setStyle( 'height', ( (ebindr.layout.height-40)*0.50 ).round(1) + 'px' );
    			$('f').setStyle( 'height', ( (ebindr.layout.height-40)*0.50 ).round(1) + 'px' );
    			
    			// features, help, directory
    			$('q').setStyle( 'height', ebindr.layout.height-40 );
//    			$('t').setStyle( 'height', ( (ebindr.layout.height-200-40)/2 ).round(1) + 'px' );
//    			$('o').setStyle( 'height', ( (ebindr.layout.height-200-40)/2 ).round(1) + 'px' );
    			// chat,forum
//    			$('n').setStyle( 'height', ( (ebindr.layout.height-40)*0.6 ).round(1) + 'px' );
//    			$('e').setStyle( 'height', ( (ebindr.layout.height-40)*0.4 ).round(1) + 'px' );
    		},
    		
    		// position the rings on the page
    		rings: function() {
    			// for each ring add a top and left position
    			$$( '.ring' ).each( function( ring, i ) {
    				// left position
    				ring.setStyle( 'left', ( ( ebindr.layout.width - ebindr.layout.ringWidth ) / 2 ).round(1) + 'px' );
    				// top position for middle ring
    				if( i == 1 ) {
    					ring.setStyle( 'top', ( ( ebindr.layout.height - ebindr.layout.ringHeight ) / 2 ).round(1) + 'px' );
    				}
    			});
    		},
    		
    		// function to set the height of the columns
    		colHeight: function() {
    			$$( '.col' ).setStyle( 'height', ebindr.layout.height );
    		},
    		
    		// set the heights of the pages
    		pageHeight: function() {
    			// set the top and bottom heights
    			$$( '.col.two .row.one, .col.two .row.three, .col.four .row.one, .col.four .row.three' ).setStyle( 'height', 20 );
    			// set the height of the main body of the page
    			$$( '.col.two .row.two, .col.four .row.two' ).setStyle( 'height', ebindr.layout.height - 40 );
    		},
    		
    		// set the page widths
    		pageWidth: function() {
    			var width = ( ( ebindr.layout.width - ( (ebindr.layout.tabWidth*2)+ebindr.layout.spineWidth ) ) / 2 ).round(1);
    			$$( '.col.two, .col.four' ).setStyle( 'width', width + 'px' );
    			ebindr.layout.pagewidth = width;
    			return width;
    		}
    	}
    }

});