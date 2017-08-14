// add methods/functions to ebindr
ebindr.extend({

	body: {
    	width: window.getCoordinates().width,
    	height: window.getCoordinates().height,
    	setDimensions: function() {
    		// set the width and height but never below a certain threshold of 900x600
    		ebindr.body.width = ( window.getCoordinates().width < 900 ? 900 : window.getCoordinates().width );
    		ebindr.body.height = ( window.getCoordinates().height < 600 ? 600 : window.getCoordinates().height );
    		// set the overflow to auto if we are below 900x600
    		$$( 'html' ).setStyle( 'overflow', ( ( window.getCoordinates().height < 600 || window.getCoordinates().width < 900 ) ? 'auto' : 'hidden' ) );
    		// set the width and height of the body
    		$$( 'body' ).setStyles({
    			'width': ebindr.body.width,
    			'height': ebindr.body.height
    		});
    		// set the height of the 5 main elements
    		$$( '#left, #spine, #right' ).setStyle( 'height', ebindr.body.height );
    		// set the width of both tab containers
    		$$( '#tab-l, #tab-r' ).setStyles({
    			'width': 45,
    			'height': ebindr.body.height-50,
    			'margin-top': 25,
    			'margin-bottom': 25
    		});
    		// set the width of the spine, and the height of the inside div of the spine
    		$( 'spine' ).setStyle( 'width', 90 );
    		$$( '#spine div.span' ).setStyle( 'height', ebindr.body.height-50 );
    		// set the rings in their right place
    		$$( '#spine div.span img.ring-top' )[0].setStyle( 'margin-top', 10 );
    		$$( '#spine div.span img.ring-mid' )[0].setStyle( 'margin-top', ((ebindr.body.height-50-10-25-25-25-10-10)/2).round(0) );
    		$$( '#spine div.span img.ring-bottom' )[0].setStyle( 'margin-top', ((ebindr.body.height-50-10-25-25-25)/2).round(0) );
    		// set the width's of the pages
    		$$( '#left, #right' ).setStyle( 'width', ( ( ebindr.body.width-180 ) / 2 ).round(1) +'px' );
    		$$( '.workarea' ).setStyle( 'height', ebindr.body.height-48 );
    	}
    }
    
});