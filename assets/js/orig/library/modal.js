ebindr.library.modal = new Class({

	options: {
		returnValue: false
	},
		
	initialize: function( options ) {
		$extend(this.options,options);
		// log that this class has been loaded
		ebindr.console( 'ebindr.modal loaded' );
	},

	// default onSuccess, does nothing needs to be over-ridden for outside funcationality
	onSuccess: function( returnValue ) {},
	
	/*
		Simple alert
	*/
	alert: function( message, title, onclose, bgcolor, mywidth, extraoptions ) {
//		console.log('TITLE = %s', title);
		// make that we have a modal to stop the keyboard listening
		if( ebindr.current.modal ) return false;
		ebindr.current.modal = true;
		// set the bgcolor
		if( typeof(bgcolor) == 'undefined' ) bgcolor = '#fff';
		var mydiv=new Element('table', {id:'testalert','html':'<tr><td width=600>'+message.toString()+'</td></tr>'});
		mydiv.inject($$('body')[0]);
		if( typeof(mywidth) == 'undefined' ) {
			mywidth=mydiv.measure(function() { return this.getSize().x; })+50;
			if(mywidth>(document.getSize().x-200)) mywidth=(document.getSize().x-200);
//			mywidth=600;
		}
		var myheight=mydiv.measure(function() { return this.getSize().y; })+50;
		if(myheight>(document.getSize().y-200)) myheight=(document.getSize().y-200);
		mydiv.destroy();
		
		if( message.match(/^file:/gi) ) {
			new Request.HTML({
				async:false,
				onSuccess: function( el, el2, html ) {
					message = html;
				}
			}).get(message.replace("file: ",""));
		}

		return new ebindr.window.library.Window({
    		title: ( typeof(title) != "undefined" ? title : 'Just Letting You Know ...' ),
    		id: 'alert-box',
    		type: 'modal',
    		modalOverlayClose: true,
			contentBgColor: bgcolor,
			bodyBgColor: bgcolor,
    		content: message.substitute(ebindr.current),
    		width: mywidth,
    		padding: { top: 10, bottom: 20, left: 12, right: 12 },
    		height: myheight,	
    		onContentLoaded: function() {
				var instance = $(this.options.id).retrieve('instance');
				var contentWrapperEl = instance.contentWrapperEl;
				var contentEl = instance.contentEl;
				if(typeof(extraoptions)!='undefined' && extraoptions.borderred) $('alert-box_contentBorder').setStyles({'border-top':'solid 3px red', 'border-bottom':'solid 3px red'});
	//			console.log(" START RESIZING MODAL (%s)... CONTENT HEIGHT = %d", this.options.id, contentEl.offsetHeight );
//					ebindr.window.parent.dynamicResize($(this.options.id));
	//			console.log(" DONE RESIZING MODAL (%s)... CONTENT HEIGHT = %d", this.options.id, contentEl.offsetHeight );
	//    			console.log(" height %d scrollHeight %d clientHeight %d", 
	//				this.contentWrapperEl.getStyle('height').toInt() , 
	//				this.contentWrapperEl.scrollHeight, 
	//				this.contentWrapperEl.clientHeight );
				if( $('alert-box_content') ){
	//				console.log('CONTENT HEIGHT %d', $('alert-box_content').getStyle('height').toInt());
					this.contentWrapperEl.scrollTop = this.contentWrapperEl.scrollHeight;
				}
				this.contentWrapperEl.scrollTo(0,0);
    		},		
    		onClose: function() {
    			ebindr.current.modal = false;
    			if( title == 'Open Windows' ) ebindr.current.winview = false;
    		},
			onCloseComplete: function() {
				if( $type(onclose) == 'function' && !ebindr.current.modal /* per ticket 1344524509: don't run 'onclose' for alerts that are attempted while another alert is still showing */ ) onclose();
			}
    	});

	},
	
	/*
		Prompt Box
	*/
	prompt: function( message, title, callback ) {
	
		// see if we have a callback
		if( typeof( callback ) != "undefined" ) this.onSuccess = callback;
		
		ebindr.current.prompt = true;
		
		ebindr.window.modal( message, {
			buttons: '<input type="button" value="Submit">',
			prompt: '<input type="text" style="width: 95%;" />',
			title: title
		});
		
		$$( '#confirm-box-buttons input[type=button]' ).addEvent( 'click', function(e) {
			ebindr.modal.options.clicked = true;
			ebindr.modal.options.returnValue = $$( '#confirm-box-input input[type=text]' )[0].value;
			ebindr.modal.close();
			ebindr.current.prompt = false;
			ebindr.modal.options.clicked = false;
		});
		
	},
	
	/*
		Save Box (Yes/No/Cancel)
	*/
	save: function( message, callback ) {
	
		// see if we have a callback
		if( typeof( callback ) != "undefined" ) this.onSuccess = callback;
		
		ebindr.window.modal( message, {
			buttons: '<input type="button" value="Yes">&nbsp;<input type="button" value="No">&nbsp<input type="button" value="Cancel">'
		});
		
		$$( '#confirm-box-buttons input[type=button]' ).addEvent( 'click', function(e) {
			ebindr.modal.options.clicked = true;
			ebindr.modal.options.returnValue = ( this.value == 'Yes' ? true : ( this.value == 'No' ? false : 'cancel' ) );
			ebindr.modal.close();
			ebindr.modal.options.clicked = false;
		});
	
	},
	
	/*
		Options Select Box (originally written for google apps export
	*/
	selectoption: function( title, url, callback ) {
		new ebindr.window.library.Window({
			title: title,
			id: 'confirm-box',
			type: 'modal',
			loadMethod: 'xhr',
			contentURL: url,
			width: 700,
			height: 200,
			padding: { top: 0, bottom: 0, left: 0, right: 0 },
			onContentLoaded: function() {
				//ebindr.window.parent.dynamicResize($(this.options.id));
				$$('#confirm-box .option').addEvent('click', function(e) {
					var value = this.get('id');
					if( $type(callback) == 'function' ) {
						callback(value);
						ebindr.modal.close();
					}
				});
			},
			onClose: function() {
			}
		});		
		
	},
	
	/*
		Confirm Box
	*/
	confirm: function( message, callback, buttonVals ) {
	
		// see if we have a callback
		if( typeof( callback ) != "undefined" ) this.onSuccess = callback;
		
		var btntrue = ( $type(buttonVals) == 'array' ? buttonVals[0] : 'Yes' );
		var btnfalse = ( $type(buttonVals) == 'array' ? buttonVals[1] : 'No' );
		var btnother = '';
		if($type(buttonVals) == 'array') {
			for(var i=2;i<buttonVals.length;i++) btnother = btnother + '&nbsp;<input type="button" class="other" value="' + buttonVals[i] + '">';
		}
		ebindr.window.modal( message, {
			buttons: '<input type="button" class="true" value="' + btntrue + '">&nbsp;<input type="button" value="' + btnfalse + '">' + btnother
		});
		
		$$( '#confirm-box-buttons input[type=button]' ).addEvent( 'click', function(e) {
			ebindr.modal.options.clicked = true;
			ebindr.modal.options.returnValue = ( this.hasClass('true') ? true : ( this.hasClass('other') ? this.get('value') : false ) );
			ebindr.modal.close();
			ebindr.modal.options.clicked = false;
		});
	
	},
	
	close: function() {
		MochaUI.closeWindow(MochaUI.Windows.instances.get('confirm-box').windowEl);
		this.onSuccess(this.options.returnValue);
	}
	
});