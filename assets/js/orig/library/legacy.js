// see if there is another window.parent
var readonly=false;

setbid = function(bid, start, cid, minimize) {
	if( typeof(minimize) == 'undefined' ) var minimize = false;
	ebindr.openBID(bid, start, cid, minimize);
}
// do page
dopage = function(page) {
//	$( 'tab-' + page ).fireEvent( 'click' );
	//ebindr.tab.click(page);
	$( 'tab-' + page ).fireEvent( 'click' );
//	ebindr.tab.tabs[page].left.click();
}
// do shade
doshade = function(button,qty) {
	ebindr.button.shade(button,qty);
}
// editr_run
editr_run = function(button) {
	ebindr.button.editr(button);
}
// hidethem
hidethem = function() {
	var iframeid = ebindr.window.library.focusedWindow.options.id + '_iframe';
	(function() { ebindr.button.toolbar.escape( ebindr.window.library.focusedWindow, ebindr.window.iframe(iframeid), false ); }).delay(100);
}
FileBrowser = function(type, value) {
	ebindr.button.fileBrowser( type, value );
}
DoFindr = function(findrtype, findrval) {
	if( ebindr.findr2.started ) {
		MUI.focusWindow($('findr2'));
		if ($('findr2').retrieve('instance').isMinimized == true)	MUI.Dock.restoreMinimized.delay(25, MUI.Dock, $('findr2'));
		ebindr.findr2.setSearch(findrtype);
		ebindr.findr2.search(findrtype, findrval);
	} else {
		ebindr.current.link_search = findrval;
		ebindr.openFINDr( findrtype );
	}
	// ebindr.openFINDr( findrval );
	// ebindr.growl( 'Refreshing findr', 'If findr shows invalid data or no data at all it will refresh with the appropriate data in 5 seconds' );
	// setTimeout(function(){ ebindr.findr2.search(findrtype, findrval); }, 5000);
}
findr = function () {
	ebindr.openFINDr();
}

CheckSecurity = function( button ) {
	return ebindr.button.access(button);
}

editr_run_edit = function (button) {
	return ebindr.button.editr_edit( button + ".editr", ebindr.current.bid, true );
}