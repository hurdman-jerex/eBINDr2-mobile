// add methods/functions to ebindr
ebindr.extend({

	'tickettrackr': {
	
		'newticket': function() {
			$('tickettrackr_iframe').contentWindow.location.reload();
			$('ttOpen').fireEvent('click');
			$('tickettrackr_new').contentWindow.location.href = 'b_ebindropen.html';
		},
		
		'addedcomment': function( ticket ) {
			$('tickettrackr_current').contentWindow.location = '/ebindr/tickettrackr.php/b_ebindrview.html?ticket=' + ticket;
		}
	
	}
	
});