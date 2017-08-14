// add methods/functions to ebindr.user
ebindr.user.extend({
	
	/*
    	This will update the database every minute that the user
    	is active. Unless the mouse hasn't been moved or the keyboard used.
    */
    checkActivity: function() {
    	// add the mouseenter to each of the frames
    	$$( 'body' )[0].addEvent( 'mouseover', function() {
    		if( !ebindr.user.preactive ) ebindr.user.preactive=true;
//    		console.log('isactive');
    	});
    	$$( 'body' )[0].addEvent( 'mouseover:relay(iframe)', function() {
    		if( !ebindr.user.preactive ) ebindr.user.preactive=true;
//    		console.log('isactive iframe');
    	});
    	// the periodical function
    	( function() {
	    	if(!ebindr.user.preactive) ebindr.user.setInactive();
    		if( ebindr.user.active ) {
    			new Request({
    				url: '/ebindr/community.php/user/set_activity'
    			}).post({
 					"key": window.parent.ebindr.key,
					"id": window.parent.ebindr.id
    			});
    			ebindr.user.preactive=false;
    		}
    	}).periodical(60000); // every minute
    },
    
    /*
    	Checks whether a user is active
    */
    isActive: function() {
    	return ( ebindr.user.active ? true : false );
    },
    
    /*
    	Checks whether a user is inactive
    */
    isInactive: function() {
    	return ( !ebindr.user.active ? true : false );
    },
    
    /*
    	Use this method to mark the user as active
    */
    setActive: function() {
    	ebindr.user.active = true;
    },
    
    /*
    	This method will set the user as inactive
    */
    setInactive: function() {
    	ebindr.user.active = false;
    }


});