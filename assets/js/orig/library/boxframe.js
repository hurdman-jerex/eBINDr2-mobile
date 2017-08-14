// this library will give us the ability to, based on the size of a frame,
// fill it's contents as the screen grows or shrinks
ebindr.library.boxframe = new Hash({

	options: {
		staticWidth: 250,
		thresholds: [ 700, 435 ]
	},
	
	set: function( parent, options ) {
		// set the parent and combine the options
		this.parent = $(parent);
		$extend(this.options,options);
		
		// set the parents width and height
		this.parentWidth = this.parent.getScrollSize().x;
		this.parentHeight = this.parent.getSize().y;
		this.cols = this.parent.getElements( 'div.boxcol.optional div.boxcol' );
		this.numCols = this.cols.length;
		
		// setup
		this.adjustCols();
		this.setHeights();
		this.fixedPosition();
		this.setWidths();
	},
	
	/*
		Figure out when a threshold has been hit and adjust
		accordingly
	*/
	adjustCols: function() {
		this.options.thresholds.each( function(limit,i) {
			if( (ebindr.library.boxframe.parentWidth+15) < limit ) {
				ebindr.library.boxframe.numCols = ebindr.library.boxframe.cols.length - (i+1);
			}
		});
	},
	
	/*
		Set the heights of the resizables based on what is in their
		lang attributes
	*/
	setHeights: function() {
		// set the adjustable height divs
		this.parent.getElements( 'div.boxcol.optional div.boxcol div.resize' ).each( function(el,i) {
			el.setStyle( 'height', (ebindr.library.boxframe.parentHeight*(el.lang/100)).round(1)-( el.hasClass('top') ? 0 : 1 ) );
		});
	},
	
	/*
		Fix the optional sections so that they don't scroll
	*/
	fixedPosition: function() {
		this.parent.getElements( 'div.boxcol.optional' ).setStyles({
			'top': this.parent.getCoordinates().top,
			'left': this.parent.getCoordinates().left + this.parent.getElements( 'div.boxcol.always' )[0].getCoordinates().width
		});
	},
	
	/*
		Set the width of the optional columns based on the lang percentage
		attribute and the width of the always column
	*/
	setWidths: function() {
		var mywidth = ebindr.library.boxframe.parentWidth-ebindr.library.boxframe.options.staticWidth;

		this.parent.getElements( 'div.boxcol.optional div.boxcol' ).each( function(el,i) {
			var percent = ( ebindr.library.boxframe.numCols == 1 ? 100 : el.lang );
			if( ebindr.library.boxframe.numCols == i ) el.setStyle( 'display', 'none' );
			else if( ebindr.library.boxframe.numCols == 0 ) {
				el.setStyle( 'display', 'none' );
				ebindr.library.boxframe.parent.getElements( 'div.always' ).setStyle( 'width', ebindr.library.boxframe.parentWidth-1 );	
			} else {
				ebindr.library.boxframe.parent.getElements( 'div.always' ).setStyle( 'width', ebindr.library.boxframe.options.staticWidth );	
				ebindr.library.boxframe.fixedPosition();
				el.setStyles({
					'width': (mywidth*(percent/100)).round(1)-( el.hasClass('end') ? 0 : 1 ),
					'display': ''
				});
			}
		});
	}
	
});