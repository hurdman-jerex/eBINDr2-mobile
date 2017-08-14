var forum = new Hash({

	/*
		Attach all the events to elements that are found on the
		page.
	*/
	attach: function() {
		// find all of the forum actions
		$$( '.forum-action' ).each( function(el) {
			// find out what it is
			switch( el.name ) {
				case "reply": 
					// convert the text box to a html editor
					var editor = $('forum-reply-message');//.mooEditable();
					// add the click to this button
					el.addEvent( 'click', function(e) {
						// save the editor content
						//editor.saveContent();
						// get the value
						forum.reply( editor, $('forum-reply-tid').get('value'), $('forum-reply-message').get('value') );
					});
				break;
				case "topic":
					// convert the text box to a html editor
					var editor = $('forum-topic-message');//.mooEditable();
					// add the click to this button
					el.addEvent( 'click', function(e) {
						// save the editor content
						//editor.saveContent();
						// get the value
						forum.startTopic( editor, $('forum-topic-fid').get('value'), $('forum-topic-title').get('value'), $('forum-topic-message').get('value') );
					});
				break;
			}
		});
		// add the back/forward and reload buttons
		/*$( 'back' ).addEvent( 'click', function(e) {
			window.history.go(-1);
		});
		$( 'forward' ).addEvent( 'click', function(e) {
			window.history.go(1);
		});
		$( 'reload' ).addEvent( 'click', function(e) {
			window.location.reload();
		});*/
	},

	/*
		Post a reply to a topic
	*/
	reply: function( editor, tid, message ) {
		var post = new Request.JSON({
			url: '/ebindr/forum.php/reply/',
			onComplete: function(data) {
				// log to eBINDr that we replied to a topic in the forum
				if( typeof(window.parent.ebindr) != "undefined" )
					window.parent.ebindr.log( 'Replied to forum topic TID ' + tid );
				// clear the textbox
				$('forum-reply-message').value = '';
				editor.value = '';
				//editor.setContent('');
				// get the results and create a new post into the topics
				new Element( 'div', {
					'class': 'bubble',
					html: '<blockquote><p>' + data.body + '<p></blockquote><cite><strong>' + data.name + '</strong> ' + data.date + '</cite>'
				}).inject( $$( 'div.discussion')[0] );
			}
		}).post({
			tid: tid.replace(" ",""),
			body: message
		});
	},
	
	startTopic: function( editor, fid, title, body ) {
		var post = new Request.JSON({
			url: '/ebindr/components/forum/newtopic/',
			onComplete: function(data) {
				// log to eBINDr that we started a topic in the forum
				if( typeof(window.parent.ebindr) != "undefined" )
					window.parent.ebindr.log( 'New forum topic started TID ' + data.tid );
				// clear the textbox
				$('forum-topic-message').value = '';
				editor.value = '';//setContent('');
				// forward to the topic
				window.location.replace( "/ebindr/forum.php/topic/{tid}".substitute(data) );
				// ge tthe results and create a new topic
				/*
				new Element( 'li', {
					html: '<h5>Posted: {date}</h5><h3><a href="/ebindr/forum/topic/?tid={tid}">{title}</a></h3><div class="total">Replies: <b>0</b></div>'.substitute(data)				
				}).inject( $$( 'ol.forum-list' )[0], 'top' );
				*/
			}
		}).post({
			fid: fid.replace(" ",""),
			title: title,
			body: body
		});
	}

});

window.addEvent( 'domready', function(e) {
	forum.attach({
		baseCSS: 'body { background-color: #fff; }'
	});
});