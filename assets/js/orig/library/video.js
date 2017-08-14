var ebindr = window.parent.ebindr;

ebindr.library.video = new Class({

	Implements: [Events, Options],
	// the tab options
	options: new Hash(),
	
	initialize: function( options ) {
		$extend(this.options,options);
		// log that this class has been loaded
		ebindr.console( 'ebindr.video loaded' );
		// make sure we are logged in
		if( ebindr.authenticate() ) {
			this.start();
		}
		
		this.current = 0;
	},
	
	start: function() {
		this.options.accordion = new Accordion( $('accordion'), 'h3.toggler', 'div.element', {
			opactiy: false,
			onActive: function( toggler, element ) {

			},
			onBackground: function( toggler, element ) {

			},
			show: -1
		});
		
		this.novideo();
		
		$$('ul.listing li').addEvent( 'click', function() {
			var htvid = this.id.replace("video-","");
//			alert( 'htvid: ' + htvid + ' current: ' + ebindr.video.current );
			if( htvid != ebindr.video.current ) {
				ebindr.video.current = htvid;
				if( $('video-player') ) $('video-player').dispose();
				ebindr.video.loadingvideo();
				new IFrame({
				 	id: 'video-player',
				    src: 'http://ebindr.com/2/howto.html?htvid=' + htvid,
				    styles: {
				        width: 399,
				        height: 400,
				        border: 'none'
				    },
				    events: {
				        load: function(){
				            ebindr.video.hidestatus();
				        }
				    }
				}).inject($('video'));
			} else {
				ebindr.alert( 'The video you clicked on is already open.' );
			}

		});
		
		$$('.content .tags ul li').addEvent( 'click', function(e) {
			var group = this.getParent().getElements('li');
			group.removeClass('active');
			this.addClass('active');
			var videos = this.getParent('.content').getElements('ul.listing li' );
			if( !this.id ) var frame_class = 'all';
			else var frame_class = 'frame-' + this.id.replace("-frame","");
			
			if( frame_class == 'all' ) videos.setStyle( 'display', '' );
			else {
				videos.setStyle( 'display', 'none');
				videos.each( function(video,i) {
					if( video.hasClass( frame_class ) ) video.setStyle( 'display', '' );
				});
			}
		});
		
		$('accordion').setStyle( 'display', '' );
		
	},
	
	novideo: function() {
		$('videonone').setStyles($('video').getCoordinates()).setStyles({
			'position': 'absolute',
			'display': ''
		});
	},
	
	loadingvideo: function() {
		this.hidestatus();
		$('videoloading').setStyles($('video').getCoordinates()).setStyles({
			'position': 'absolute',
			'display': ''
		});
	},
	
	hidestatus: function() {
		$('videonone').setStyle( 'display', 'none' );
		$('videoloading').setStyle( 'display', 'none' );
	},
	
	tab: function( name ) {
		if( $(name + '-tab') ) {
			$(name + '-tab').fireEvent( 'click' );
		}
	},
	
	frame: function( name ) {
		if( $(name + '-frame') ) {
			$(name + '-frame').getParent('.element').getPrevious().fireEvent( 'click' );
			$(name + '-frame').fireEvent('click');
		}
	}

});