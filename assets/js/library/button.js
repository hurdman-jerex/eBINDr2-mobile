/*
reportgo // opens a common report (fire the database tab, common reports button, then open the report) just pass the menu (snapshot button) appends a bid
reportgolink // pass the whole link for a common report
editr_run // opens an editor window, and loads the button shows a list so you can edit and delete from
editr_run_edit // same as editr_run expect it goes straight to editing
editredit
editrinsert
editrdelete
mylist // pulls up a small list in the middle (like documents etc)
setbid // sets a new bid
findr // opens up the findr window
findr_run // executes the search based on criteria and button pressed
DoLanguage // switches the language
LinkReport // opens a new window to either merge a document or open a link
hidethem // hides any floating windows
refreshdata // based on what you were editing it reloads the frames with data
dobutton // main controller
filebrowser // pulls up a file upload window
*/

ebindr.library.button = new Class({

	// hidethem = ebindr.window.minimizeAll()
	// editr = ebindr.doNormal( button )

	options: {
		selectors: 'h2.name, .rating div, #b .details td, #b .details th, #b .details th span, .button32, .button32-span2, .line-buttons div, .controls li, .btnbar li, div.datafields span.button, .utility img, a#bamap, #ms', // which buttons to include
		noinsert: [ 'lite button b!', 'lite button m!' ],
		nodelete: [ 'lite button b!', 'lite button m!' ]
	},
	
	/*
		Whether we show an editr type button based on this.options.noinsert and nodelete
	*/
	showbutton: function(type, button) {
		if( type == 'insert' ) {
			if( this.options.noinsert.contains(button) ) return false;
			else return true;
		} else if( type == 'delete' ) {
			if( this.options.noinsert.contains(button) ) return false;
			else return true;
		}
	},

	initialize: function( options ) {
		$extend(this.options,options);
		
		// log that this class has been loaded
		ebindr.console( 'ebindr.button loaded' );
		// activate the buttons
		//this.activate(this.options.selectors);
	
		this.bn = true;

		//set 'bcopy' button as the trigger to copy 'company info' to clipboard
		/*new ebindr.library.mooclip('#bcopy', {
			dataSource: function(target){
				//return "test";
				return document.getElementById("bcopy_text").innerHTML;
			},
			onCopy:function(){
				ebindr.alert('Company info copied!');
				//console.log("bcopied in initialize");
			}
		});*/

	},
	
	/*
		The toolbar sub class
	*/
	toolbar: {
	
		'insert': function( iframe ) {
			if(typeof iframe.el.bid=="undefined") iframe.el.bid=ebindr.current.bid;
//			console.log(iframe);
			if( ebindr.button.access(ebindr.current.editr.replace("lite button ",""), true) && ebindr.button.showbutton('insert', ebindr.current.editr ) ) {
				if(iframe.window.currentEditr!="") ebindr.button.setExt( iframe.window );
				var url = '/m/report/' + ebindr.current.editrInsert + '+/?noheader&ebindr2=y';
				url += '&bid=' + iframe.el.bid + ebindr.current.customeditrextension + '&lid=' + ebindr.current.lid;
				url += '&cid=' + ebindr.current.cid + '&key1=' + ebindr.current.key1.replace(/[+]/,"%2B");
				url += '&key2=' + ebindr.current.key2.replace(/[+]/,"%2B") + '&editr&reportr=';
				url += escape( '/m/report/' + ebindr.current.editr + '/?noheader&bid=' + iframe.el.bid + '&cid=' );
				url += escape( ebindr.current.cid + '&ebindr2=y&bbbid=' + Cookie.read('BBBID') ) + ebindr.current.customextension;
				//console.log('URL = %s', url);
				//console.dir(iframe);
				return url;
			} else {
				return false;
			}
		},

		'remove': function() {
			if( ebindr.button.access(ebindr.current.editr.replace("lite button ","")+(ebindr.current.editr=="lite button ca"?"-":""), true) && ebindr.button.showbutton('delete', ebindr.current.editr ) ) {
				var url = '/m/report/' + ebindr.current.editr + '-/?noheader&ebindr2=y';
				url += '&bid=' + ebindr.current.bid + ebindr.current.customeditrextension + '&lid=' + ebindr.current.lid;
				url += '&cid=' + ebindr.current.cid + '&key1=' + ebindr.current.key1.replace(/[+]/,"%2B");
				url += '&key2=' + ebindr.current.key2.replace(/[+]/,"%2B") + '&editr&reportr=';
				url += escape( '/m/report/' + ebindr.current.editr + '/?noheader&bid=' + ebindr.current.bid + '&cid=' );
				url += escape( ebindr.current.cid + '&ebindr2=y&bbbid=' + Cookie.read('BBBID') ) + ebindr.current.customextension;
				return url;
			} else {
				return false;
			}
		},

		'exportr': function( iframeWin ) {
			if(iframeWin.exportrString>"") {
				return "/m/report/exportr," + iframeWin.exportrString + "/?noheader&bid=" + ebindr.current.bid + "&cid=" + ebindr.current.cid;
			} else
				return "/m/report/exportr," + ebindr.current.editr + "/?noheader&bid=" + ebindr.current.bid + "&cid=" + ebindr.current.cid;
		},

		'escape': function( windowEl, iframe, doundo ) {

			if( typeof(doundo)=="undefined" ) var doundo=true;
			if(iframe.window.currentEditr!="") ebindr.button.setExt( iframe.window );
			if( $type(windowEl) == 'element' ) windowEl = ebindr.doWindows.instances.get(windowEl.id);
			if( iframe.window.location.toString().contains("editr") ){
				if( iframe.window.ChangesMade() != '' ) {
					ebindr.current.stopClose = false;
					ebindr.modal.save( 'Save Changes?', function( retVal ) {
						if( retVal == 'cancel' ) return;
						else if( !retVal ) {
							if( ( ebindr.current.editr.contains("+.editr") || ebindr.current.editr.contains("mp.editr") ) && ebindr.current.undo1 /* && ebindr.isHurdman() */ && doundo ) var undoinsert = new Request.HTML().get( '/m/report/' + ebindr.current.editr + '.undo/?editr&ebindr2=y&noheaderhidden&undo1=' + ebindr.current.undo1 + '&undo2=' + ebindr.current.undo2 );
							windowEl.close();
						} else if( retVal ) iframe.window.submitform();
					});
				} else {
					if ( ebindr.current.editr !== undefined ) {
						if( ( ebindr.current.editr.contains("+.editr") || ebindr.current.editr.contains("mp.editr") ) && ebindr.current.undo1 /* && ebindr.isHurdman() */ && doundo ) var undoinsert = new Request.HTML().get( '/m/report/' + ebindr.current.editr + '.undo/?editr&ebindr2=y&noheaderhidden&undo1=' + ebindr.current.undo1 + '&undo2=' + ebindr.current.undo2 );
					}
					ebindr.current.stopClose = false;
					windowEl.close();
//	    			ebindr.refreshdata();
				}
				// don't close or do anything
				//return;
			} else {
				ebindr.current.stopClose = false;
				windowEl.close();
			}
		}

	},

	/*
		Shade a button
	*/
	shade: function(button, qty) {
		switch (button) {
			case 'bn':
				if( qty > 1 ) {
					if( $(button) && $(button + '-more') ) {
						$(button + '-more').setStyle( 'display', 'block');
						$('bn').setStyle( 'padding-left', '22px' );
					}
					ebindr.data.store['isbuttonshade_'+button] = true;
				} else {
					if( $(button) && $(button + '-more') ) {
						$(button + '-more').setStyle( 'display', 'none' );
						$('bn').setStyle('padding-left', '10px' );
					}
				}
				break;
			case 'ba':
			case 'bp':
			case 'bf':
			case 'bo':
			case 'bt':
			case 'bu':
			case 'b@':
			/*case 'bs':*/
				if( $(button + '-more') ) {
				if( qty > 1 ) {
					$(button + '-more').addClass('more-btn');
					ebindr.data.store['isbuttonshade_'+button] = true;
				} else $(button + '-more').removeClass('more-btn');
				}
				break;
			case 'bj':
			case 'bq':
			case 'bl':
			case 'bx':
			case 'be':
			case 'bb':
			case 'bh':
			case 'ml':
			case 'md':
			case 'ma':
			case 'ca':
				if( qty > 0 ) {
					if( $(button) ) {
						if( !$(button).hasClass('red') ) $(button).addClass('more');
					}
					ebindr.data.store['isbuttonshade_'+button] = true;
				} else {
					if( $(button) ) $(button).removeClass('more');
					ebindr.data.store['isbuttonshade_'+button] = false;
				}
				break;
			case "bdocs":
				var docsbutton=$$('li#scanneddocs, li.scanneddocs')[0];
				if(docsbutton) {
					if(qty>0 && docsbutton.hasClass('empty')) docsbutton.removeClass('empty');
					if(qty==0 && !docsbutton.hasClass('empty')) docsbutton.addClass('empty');
				}
				ebindr.current.doccount=qty;
				break;
//				if(val>0) document.getElementById("docsimg").src="/css/docs.gif"; else document.getElementById("docsimg").src="/css/docsnone.gif"; break;
		}
	},

	activate: function(selectors) {
		// add the click event
		//jQuery( selectors ).each( function( button, i ){
		$$( selectors ).each( function( button, i ) {

			if( !button.hasClass( 'end' ) && !button.hasClass('noclick') ) {
                console.log( button );
				// add events to the button
				button.addEvents({
					'click': function(e) {
						console.log( this.text );
                        //document.title =
						var e = new Event(e);
						// lets get the target event element
						var tag = ( $(e.target) ? $(e.target).get('tag') : 'non' );
						if( tag == 'a' || button.id == 'bamap' ) {
							if(this.id.match(/-btn/))
								var mybutton=this.id.replace(/-btn$/, "");
							else var mybutton=this.id.replace(/-more$/, "");
							// log that a button was clicked
							ebindr.log( 'Button clicked: ' + mybutton );
							// only if we have an id
							if( $chk(this.id) )
								ebindr.button.go( mybutton, null, e );
						}
					},
					'contextmenu': function(e) {
						if( this.id == 'bn' ) {
							if( ebindr.button.bn ) ebindr.button.bn = false;
							else {
								ebindr.button.bn = true;
								return true;
							}
						}

//						if( this.id == 'bn' && !ebindr.button.bn ) return true;
						var e = new Event(e);
						e.preventDefault();
						//e.stopPropagation();//added 8/6/2013
						// lets get the target event element
						var tag = ( $(e.target) ? $(e.target).get('tag') : 'non' );
						if( tag == 'a' || button.id == 'bamap' ) {
							var mybutton=this.id.replace(/-btn$/, "");
							// log that a button was clicked
							ebindr.log( 'Right button clicked: ' + mybutton );
							// only if we have an id
							if( $chk(this.id) )
								ebindr.button.go( mybutton, null, e, true );
						}
					},
					'mouseover': function(e) {
						if( $(e.target).lang ) {
							ebindr.growl( "Did you know?", $(e.target).lang );
							setTimeout( "$('" + $(e.target).id + "').lang = $('" + $(e.target).id + "').title", 30000 );
							$(e.target).title = $(e.target).lang;
							$(e.target).lang = "";
						}
					},
					'button': function(e) {
						this.fireEvent('click');
					},
					'focus': function(e) {
						// set the current focus
						ebindr.current.focus = this;
					}
				});

				// add the tabindex
				button.setProperty( 'tabindex', i );
				button.setStyle( 'cursor', 'pointer' );
			}
		});
	},

	/*
		Checks security to see if the given user has access.
	*/
	access: function( button, forceeditor ) {

		if(button.substr(0,14)=='investigations') {
			if (ebindr.data.store.securitykeys.indexOf("binvestigations-") > -1) readonly=true;
			return true;
		}

		if( button == 'f 9' && document.URL.indexOf("charlotte") > -1 ) return true;
		else if ( button == 'f 9' && document.URL.indexOf("charlotte") == -1 ) return false;

		if( button == 'e button c.editr' ) button = 'ce';
		if( typeof(forceeditor) == 'undefined' ) var forceeditor = false;
		var editor = false;
		if(button=="b?") return true;

		// add the security logic here later
		var securitykeys=new String(ebindr.data.store.securitykeys);
		var allowed=false;

		//A negative substr value does NOT work in IE
		//if( button.substr(-6) == '.editr' ) {
		if( button.substr(button.length-6) == '.editr' ) {
			editor = true;
			var buttonchars = button.replace(".editr","");
		} else {
			var buttonchars = button;
		}

		if( forceeditor ) {
			editor = true;
		}

		securitychars = securitykeys.split(",");
		// allow all nk
/*		if( button == 'nk' ) {
			if( securitychars.contains("nk-") ) {
				ebindr.alert( 'You do not have access to this button (' + button + ').<br>Please contact your systems adminstrator if you feel this is in error.', 'Access Denied' );
				return false;
			}
			return true;
		}

		//These items need new security keys
		if(buttonchars.charAt(0)=="n") return true; //community tab buttons */
		if(buttonchars.charAt(0)=="q") return true; //queue buttons

		// business frame extra buttons
		var extrabbtns = [ 'br','bq','bx','bh','bl','bd','be','bb','bk' ];
		if( extrabbtns.contains(button) && securitychars.contains("bb") ) {
			allowed = false;
		}

		if(buttonchars=='m+-' || buttonchars=='m+' || buttonchars=='m-') buttonchars='mn';
		if(buttonchars=='bn' && ebindr.current.bid=='0') return false;
		if(buttonchars.charAt(2)=="-") {
			if(buttonchars=="ca-") {
				if((securitychars[0] & 8)==8) return false; //staff.Attributes "Cannot delete action steps" is checked
			}
			buttonchars=buttonchars.charAt(0)+buttonchars.charAt(1);
			return true;
		}

		readonly=false;
		for(var i=0;i<securitychars.length;i++) {
			if(buttonchars.length==2 && buttonchars.charAt(0)==securitychars[i].charAt(0)) {
				if(securitychars[i].charAt(1)=="*") allowed=true;
				if(securitychars[i].charAt(1)==buttonchars.charAt(1) && allowed && (securitychars[i].charAt(2)=='' || securitychars[i].charAt(2)==',')) {
					allowed=false;
					break;
				} else if(securitychars[i].charAt(1)==buttonchars.charAt(1) && securitychars[i].length<4) {
					allowed=true;
					if(securitychars[i].charAt(2)=='-') readonly=true;
				}
			}
		}
		if( editor && readonly ) {
			allowed = false;
		}

		if(!allowed && button.replace('.editr','').length>2) {
		//alert('here');	//security for other things besides buttons, like scanned folders, etc
			allowed = true;
		}
		//if(button=="masstransfer" && !securitykeys.match("[$]A")) allowed=false;
		if(button=="masstransfer" && securitykeys.match("bmasstransfer") ) allowed = true;

		// business frame extra buttons
		var extrabbtns = ['orderentry', 'scanneddocs','emaillink','faxreportlink','advertising','investigations','relatedreports','mycomplaints','customfields','forceupdate','bbbautologin','masstransfer', 'locationoverride', 'paymethod', 'postage'];
		if( extrabbtns.contains(button) ) {
			// unless they have given specific access
			if( ( securitychars.contains("b"+button) && securitychars.contains("b*") && !securitychars.contains("b"+button+'-') ) ||
			    ( !securitychars.contains("b"+button) && !securitychars.contains("b"+button+'-') && !securitychars.contains("b*") ) ) {
				allowed = false;
			}
		}
		// if we return false
		if(!allowed && button!="ak" ) {
			if( !forceeditor ) ebindr.alert( 'You do not have access to this button (' + button + ').<br>Please contact your systems adminstrator if you feel this is in error.', 'Access Denied' );
			return false;
		}
		// force ak to activate for hurdman
		if(securitychars.contains("HURDMAN") && button=="ak" ) {
			allowed = true;
		}

		return allowed;
		//return true;
	},

	'vaultcharge': function( bid, amount ) {
		new ebindr.doWindow({
			id: 'vaultcharge',
			title: 'Vault',
			loadMethod: 'iframe',
			contentURL: '/ebindr/views/vaultcharge.html?bid=' + bid + '&amt=' + amount,
			width: 500,
			height: 400,
			padding: { top: 10, bottom: 10, left: 10, right: 10 }
		});
	},

	go: function( button, callback, ev, contextmenu ) {
		//console.log("in go");
		if(contextmenu && button!="ia" && button!="iu" && button!="if" && button!="iv") return;
		// make sure that we have a bid
		if( button != "b?" && !$chk(ebindr.current.bid) || ebindr.current.bid == '(None)' ) {
			// let them know
			ebindr.notify( 'Please select a business record first.' );
		} else {
			// make sure they have access
			if( this.access( button ) ) {
				// log that we are running the button
				ebindr.log( 'Running button ' + button );
				// set the current button
				if( button != 'videos' ) ebindr.current.button = button;
				// run through the switch to find out what to do with the given button
				switch( button ) {
					case "f 9":
						//console.log("inbutton");
						this.editr( 'lite button f 9' );
						break;
					case "bamap":
						ebindr.alert( '<a href="http://maps.google.com/maps?q=' + escape($('ba').get('text').replace(/\r\n/g," ")) + '" target="_blank"><img border="0" src="/ebindr/map/' + escape($('ba').get('text').replace(/\r\n/g," ")) + '.png" width="555" height="400" /></a>', $('ba').get('text') );
					break;

					case "faxreportlink": ebindr.button.editr_edit( 'faxserver.SendFax' ); break;
					case "orderentry": ebindr.button.editr_edit( 'lite button orderentry' ); break;
					case "emaillink":
						this.logbutton( 'emaillink' );
						var emaila=new Element('a', {target: '_blank', href:'mailto:?subject=' + escape (ebindr.data.store.sendemailsubject.replace('{dba}', ebindr.data.store.button_bn.replace( '&amp;', '&' ).replace(/<span.*/g,'')).replace('{bid}', ebindr.current.bid))});
						//remove new tabs per ticket 1384555071 {target:'_blank', href:'mailto:?subject=' + escape (ebindr.data.store.button_bn.replace( '&amp;', '&' ).replace(/<span.*/g,'') + ' (Business ID:' + ebindr.current.bid + ')')}
						emaila.inject(document.body);
						emaila.setStyles({'display':'none'});
						emaila.click();
						emaila.dispose();
						emaila.destroy();
					break;

					case "qr":
						ebindr.data.get( '/ebindr/community.php/features/popular' );
					break;

					case "bbararrow":

						var displayed = 0;
						/*$$('ul#quick-launch li').each( function(btn,i) {
							if( i == 0 ) displayed = btn.getCoordinates().top;
							if( !btn.hasClass('spacer') ) {
								if( btn.getCoordinates().top != displayed ) {
									btn.clone().store('theid',btn.id).addClass(btn.id).inject($('bizmore-list').getElements('ul')[0]).addEvents({
										'click': function(e) {
											$('bizmore-list').setStyle( 'display', 'none' );
										},
										'mouseenter': function(e) {
											this.addClass('hover');
										},
										'mouseleave': function(e) {
											this.removeClass('hover');
										}
									});
								}
							}

						});*/

						// $$('li.emaillink')[0].addEvent('mousedown', function(e){
						// 	// if (e.event.button == 1) {
						// 		var emaila=new Element('a', {target: '_blank', href:'mailto:?subject=' + escape (ebindr.data.store.button_bn.replace( '&amp;', '&' ).replace(/<span.*/g,'') + ' (Business ID:' + ebindr.current.bid + ')')});
						// 		emaila.inject(document.body);
						// 		emaila.setStyles({'display':'none'});
						// 		emaila.click();
						// 		emaila.dispose();
						// 		emaila.destroy();
						// 	// } else if (e.event.button == 0) {
						// 	// 	var emaila=new Element('a', {target: '', href:'mailto:?subject=' + escape (ebindr.data.store.button_bn.replace( '&amp;', '&' ).replace(/<span.*/g,'') + ' (Business ID:' + ebindr.current.bid + ')')});
						// 	// 	emaila.inject(document.body);
						// 	// 	emaila.setStyles({'display':'none'});
						// 	// 	emaila.click();
						// 	// 	emaila.dispose();
						// 	// 	emaila.destroy();
						// 	// }
						// });
					/*
						if( !$('hiddenmenu') ) {
						var buttons = [], displayed = 0;
						$$('ul.btnbar li').each(function(button,i) {
							if( i == 0 ) displayed = button.getCoordinates().top;
							if( !button.hasClass('spacer') ) {
								if( button.getCoordinates().top != displayed ) {
									buttons.push(button.clone().store('theid',button.id).addClass(button.id));
								}
							}
						});
						var menu = new Element( 'ul', {
							'class': 'btnbar',
							'id': 'hiddenmenu',
							'styles': {
								'position': 'absolute',
								'left': $('bbararrow').getCoordinates().left-4,
								'top' : $('bbararrow').getCoordinates().top+26,
								'z-index': 100000
							}
						}).inject($('b')).addEvent('mouseleave', function(e) {
							this.dispose();
						});
						buttons.each( function(btn) {
							btn.addEvent( 'click', function() {
								ebindr.button.go( this.retrieve('theid') );
								menu.dispose();
							});
							btn.inject(menu);
						});
						menu.setStyle( 'left', menu.getCoordinates().left - menu.getCoordinates().width + $('bbararrow').getCoordinates().width + 5);
						} else {
							$('hiddenmenu').dispose();
						}
						*/
					break;

					// ebindr announcement button
					case "na":
						ebindr.button.editr( 'e button ebindr messages' );
						break;

					case "crdashboard":
						this.logbutton( 'crdashboard' );
						window.open( $('katana') + '?app=customerreviews?bid=' + ebindr.current.bid );
						break;

					case "newabapp":
						this.logbutton( 'newabapp' );
						window.open( $('katana') + '?app=newab/start/' + ( ebindr.data.store.otherbid>0 ? ebindr.data.store.otherbid : ebindr.current.bid ) );
						break;

					case "dealsso":
						this.logbutton( 'dealsso' );
						window.open( $('deals-sso').value );
						break;

					case "cloudfiles":
						this.logbutton( 'cloudfiles' );
						window.open( $('katana') + '?app=cloudfiles/community' );
						break;

					case 'googlesearch':
						this.logbutton( 'googlesearch' );
						var name = escape(ebindr.data.store.button_bn.replace(/<span.*$/g, "").replace(/&amp;/i,'&'));
						var city = escape(ebindr.data.store.currentCity);
						var state = escape(ebindr.data.store.currentState);
						window.open( 'https://www.google.com/#hl=en&q='+name+'%2C+'+city+'%2C+'+state );
						break;
					case 'bizweather':
//						window.open( 'http://www.weather.com/weather/right-now/' + ebindr.data.store.biz_postalcode );
						if(ebindr.data.store.weatherlink) window.open( ebindr.data.store.weatherlink );
						break;

					case "ns":
						this.logbutton( 'ebindrstatus' );
						window.open( 'http://status.hurdman.com' );
/*						new ebindr.doWindow({
							id: 'status-window',
							title: 'Hurdman System Status',
							loadMethod: 'iframe',
							contentURL: 'http://status.hurdman.com',
							width: window.getSize().x-65,
							height: window.getSize().y-80,
							padding: { top: 0, bottom: 0, left: 0, right: 0 }
						});*/
					break;

					case "nk":
						this.logbutton( 'kickstart' );
						new ebindr.doWindow({
							id: 'kickstart-window',
							title: 'Innovation Fund',
							loadMethod: 'iframe',
							contentURL: '/ebindr/views/kickstart',
							width: window.getSize().x-65,
							height: window.getSize().y-80,
							padding: { top: 0, bottom: 0, left: 0, right: 0 }
						});
					break;

					case "nt":
						this.logbutton( 'hurdmantoolbox' );
						new ebindr.doWindow({
							id: 'toolbox-window',
							title: 'Hurdman Toolbox',
							loadMethod: 'iframe',
							contentURL: '/ebindr/views/toolbox',
							width: window.getSize().x-65,
							height: window.getSize().y-80,
							padding: { top: 0, bottom: 0, left: 0, right: 0 }
						});
					break;

					case "training-stats":
						this.logbutton( 'trainingstats' );
						new ebindr.doWindow({
							id: 'training-stats-window',
							title: 'Training Center Stats',
							loadMethod: 'iframe',
							contentURL: '/ebindr/views/trainingcenter.html',
							width: 1000,
							height: 685,
							padding: { top: 0, bottom: 0, left: 0, right: 0 }
						});
					break;

					case "dg":
						this.logbutton( 'link-google' );
						new ebindr.doWindow({
							id: 'google-account',
							title: 'Link Your Google Account',
							loadMethod: 'iframe',
							contentURL: '/ebindr/views/google-link.html',
							width: 500,
							height: 220,
							padding: { top: 0, bottom: 0, left: 0, right: 0 }
						});
					break;

					case "surveybtn":
						this.logbutton( 'surveybutton' );
						new ebindr.doWindow({
							id: 'surveys',
							title: 'eBINDr Surveys',
							loadMethod: 'iframe',
							contentURL: '/ebindr/views/surveys/index.html',
							width: window.getSize().x-65,
							height: window.getSize().y-80,
							padding: { top: 0, bottom: 0, left: 0, right: 0 }
						});
					break;

					case "dk":
						this.logbutton( 'leadmark' );
						new ebindr.doWindow({
							id: 'leadmark',
							title: 'Lead Mark',
							loadMethod: 'iframe',
							contentURL: '/ebindr/views/leadmark.html',
							width: window.getSize().x-65,
							height: window.getSize().y-80,
							padding: { top: 0, bottom: 0, left: 0, right: 0 }
						});
					break;

					case "dz":
						this.logbutton( 'salescrm' );
						new ebindr.doWindow({
							id: 'salescrm',
							title: 'Sales CRM',
							loadMethod: 'iframe',
							contentURL: '/ebindr/views/crm/index.php',
							width: window.getSize().x-65,
							height: window.getSize().y-80,
							padding: { top: 0, bottom: 0, left: 0, right: 0 }
						});
					break;

					// url shortener
					case "dh":
						this.logbutton( 'urlshort' );
						new ebindr.doWindow({
							id: 'urlshortener',
							title: 'URL Shortener',
							loadMethod: 'iframe',
							contentURL: '/ebindr/views/shorturl.html',
							width: 700,
							height: 450,
							padding: { top: 0, bottom: 0, left: 0, right: 0 },
							toolbar: true,
							toolbarURL: '/ebindr/views/urlshort_tabs.html'
						});
						break;

					// chat/connect button
					case "nc":
						new ebindr.doWindow({
							id: 'connect',
							title: 'Connect',
							loadMethod: 'xhr',
							contentURL: '/ebindr/views/chat/conversations.html',
							width: 700,
							height: 450,
							padding: { top: 0, bottom: 0, left: 0, right: 0 },
							toolbar: true,
							onContentLoaded: function() {
								ebindr.layout.resize.chat();
								if( $type(callback) == 'function' ) callback();
							},
							onClose: function() {
								$('connect').dispose();
								ebindr.conversations.instances = $H();
							},
							toolbarURL: '/ebindr/views/chat/tabs.html',
							onMaximize: ebindr.layout.resize.chat,
							onRestore: ebindr.layout.resize.chat,
							onResize: ebindr.layout.resize.chat,
							useCanvas: false,
							addClass: 'no-canvas'
						});
					break;

					case "nq":
						new ebindr.doWindow({
							id: 'queue',
							title: 'Queue',
							loadMethod: 'iframe',
							contentURL: '/ebindr/community.php/features/suggest',
							width: 500,
							height: 400,
							padding: { top: 10, bottom: 10, left: 10, right: 10 }
						});
					break;

					// ticket trackr button
					case "nt":
						ebindr.data.get( 'tickettrackr auth', function() {
							new ebindr.doWindow({
								id: 'tickettrackr',
								title: 'TicketTRACKr',
								loadMethod: 'iframe',
								contentURL: '/ebindr/tickettrackr.php/b_ebindrlist.html?' + ebindr.data.store.tickets.toQueryString(),
								width: 700,
								height: 450,
								padding: { top: 0, bottom: 0, left: 0, right: 0 },
								toolbar: true,
								toolbarURL: '/ebindr/views/tickettrackr/tabs.html',
								onMaximize: ebindr.layout.resize.tickettrackr,
								onResize: ebindr.layout.resize.tickettrackr

							});
						});
					break;

					// join the general chat
					case "tg":
						ebindr.chat.open(0, '', 'General');
						break;
					// dashboard button
					case "dashboardlink":
						this.logbutton( 'dashboard' );
						ebindr.dashboard.open();
						break;
					// calendar button
					case "calendarlink":
						this.logbutton( 'calendar' );
						ebindr.doNormal( 'calendarlink', {
							id: 'calendar',
							contentURL: '/m/report/calendar/?ebindr2=y',
							width: 625,
							height: 600,
							resizable: false,
							maximizable: false
						});
						break;
					// system message button
					case "sysmsglink":
						ebindr.button.editr( 'System Messages' );
						break;
					// Misc. Files
					case "docsotherlink":
						this.logbutton( 'docsotherlink' );
						ebindr.button.fileBrowser( 'other', ebindr.current.bid );
						break;
					// help link
					case "helplinks":
						this.logbutton( 'helplinks' );
						ebindr.doList( 'helplinks', {
							contentURL: '/m/report/lite button help links/?noheader&ebindr2=y',
							title: 'Help Links',
							id: 'helpinfo'
						});
						break;
					// stream intelligence
					case "stream-intelligence":
						this.logbutton( 'stream-intelligence' );
						window.open( ebindr.data.store.streamintelligenceurl );
						break;
					// business intelligence
					case "nonabbbbbi":
						this.logbutton( 'nonabbbbbi' );
						window.open( ebindr.data.store.nonabbbbbi );
						break;
					case "bbbbi":
						this.logbutton( 'bbbbi' );
						window.open( ebindr.data.store.bbbbi );
					break;
					// customer reviews
					case "bcust-reviews":
						this.logbutton( 'bcust-reviews' );
						ebindr.doNormal( 'bcust-reviews', {
							id: 'bcust-reviews-' + ebindr.current.bid,
							title: 'Customer Reviews for ' + $('bn').get('text'),
							padding: { top: 0, bottom: 0, left: 0, right: 0 },
							contentURL: '/ebindr/views/bcust-reviews-mgr.html?bid=' + ebindr.current.bid
						});
						break;
					// stream interactions
					case "streaminteractions":
						this.logbutton( 'streaminteractions' );
						if( ebindr.data.store.streamisexported == '1' ) {
							window.open( ebindr.data.store.streaminteractionsurl );
						} else {
							ebindr.alert('No StreamPage Interactions report exists for this business record; it has not been exported to StreamPage. Please ensure an email address exists on record. If this is a Non-Accredited Business Record, please ensure it is a qualified sales lead.');
						}
						/*ebindr.doNormal( 'streaminteractions', {
							id: 'stream-' + ebindr.current.bid,
							title: 'Stream Interactions for ' + $('bn').get('text'),
							padding: { top: 0, bottom: 0, left: 0, right: 0 },
							contentURL: ebindr.data.store.streaminteractionsurl
						});*/
						break;
					// equote screen
					case "equote":
						this.logbutton( 'equote' );
						if( ebindr.data.store.newequote == 'y' ) window.open( $('katana') + '?app=equote/browse/' + ebindr.current.bid ); // /ebindr
						else {
								ebindr.doNormal( 'equote', {
								id: 'equotemgr-' + ebindr.current.bid,
								title: 'eQuotes for ' + $('bn').get('text'),
								padding: { top: 0, bottom: 0, left: 0, right: 0 },
								contentURL: '/ebindr/views/equotemgr.html?bid=' + ebindr.current.bid
							});
						}
						break;
					case "payform":
						// make sure the window gets closed before launching a new one
						if( $('payform-win') ) {
							MochaUI.closeWindow(MochaUI.Windows.instances.get('payform-win').windowEl);
						}
						this.logbutton( 'payform' );
						ebindr.doNormal( 'payform', {
							width: 800,
							id: 'payform-win',
							title: 'Online Payment Form for ' + $('bn').get('text'),
							padding: { top: 0, botton: 0, left: 0, right: 0 },
							contentURL: 'https://' + ebindr.data.store['appurl'] + '/invoice/' + ebindr.current.bid + '/' + ebindr.data.store['webpassword']
						});
						break;
					case "postage":
						this.logbutton( 'ups' );
						ebindr.doNormal( 'ups', {
							id: 'ups-' + ebindr.current.bid,
							title: 'UPS Postage',
							padding: { top: 0, bottom: 0, left: 0, right: 0 },
							contentURL: '/ebindr/views/ups2.php?bid=' + ebindr.current.bid
						});
						break;
					case "paymethod":
						var tempUrl = 'https://' + ebindr.data.store.appurl + '/ebindrpayments/?clean&staff='+ebindr.data.store.staff_initials+'&bid=' + ebindr.current.bid + '&key=9e11f14ff2a110ec0524df7733294028';
						if (ebindr.data.store.securitykeys.indexOf("bpaymethod-") > -1) {
							tempUrl += '&security=6612a70df0ecc5da85e2dafd5926b818';
						} else {
							tempUrl += '&security=84a37661fafa26503cbac3aa7e2f16d6';
						}
						this.logbutton( 'paymethod' );
						ebindr.doNormal( 'paymethod', {
							id: 'paymethod-' + ebindr.current.bid,
							title: 'Payment Methods',
							padding: { top: 0, bottom: 0, left: 0, right: 0 },
							contentURL: tempUrl

						});
						break;
					case "videos":
						this.logbutton( 'trainingcenter' );
						//ebindr.growl( 'search videos for', 'frame: ' + ebindr.current.frame.button + '<br />button: ' + ebindr.current.button + '<br />tab: ' + ebindr.current.page );
						ebindr.doNormal( 'videos', {
							id: 'video',
							//contentURL: '/ebindr/views/video.html?frame=' + ebindr.current.frame.button + '&button=' + ebindr.current.button + '&tab=' + ebindr.current.page,
							contentURL: '/ebindr/library',
							padding: { top: 0, bottom: 0, left: 0, right: 0 },
							width: window.getSize().x-65,
							height: window.getSize().y-80,
							resizable: false,
							draggable: false,
							maximizable: false,
							//width: 725,
							//height: 400,
							headerStartColor:  [250, 250, 250],
							headerStopColor:   [229, 229, 229]
						});
						break;
					case "bcopy":
						//console.log("bcopied");
						/*not needed :) - already placed this above in initialize:
						new ebindr.library.mooclip('#bcopy', {
							dataSource: function(target){
								//return "test";
								return document.getElementById("bcopy_text").innerHTML;
							},
							onCopy:function(){
								ebindr.alert('Company info copied!');
								console.log("bcopied in case bcopy");
							}
						});*/
					break;
					case "by":
						ebindr.doNormal( 'e button by', {
							id: 'frame_history',
							width: 300,
							height: 400,
							maximizable: false,
							title: "History",
							contentURL: "/m/report/lite button by?noheader&ebindr2=y&&cmd=&newbid=&bid=" + ebindr.current.bid
						});
						break;
					case "b+":
						ebindr.modal.confirm( 'Proceed to create a new business record?', function( retval ) {
							if( retval ) ebindr.button.editr_edit("lite button b+.editr");
						});
						break;
					case "b?":
						ebindr.openFINDr(ebindr.lastfindr);
						break;
					case "bm":
						// make sure the window gets closed before launching a new one
						//if( $('bm-payments') ) {
							//MochaUI.closeWindow(MochaUI.Windows.instances.get('bm-payments').windowEl);
						//}
						// open the payment form
						new ebindr.doWindow({
							id: 'bm-payments',
							title: 'Payment Methods',
							loadMethod: 'iframe',
							width: window.getSize().x-200,
							height: window.getSize().y-200,
							padding: { top: 0, bottom: 0, left: 0, right: 0 },
							contentURL: 'https://hurdman.app.bbb.org/payments/?clean'
						});
						break;
					case "mx":
					case "cx":
						// open a new window
						window.open( '/m/report/exportr,lite button ' + button + '/?ebindr2=y&bid=' + ebindr.current.bid + '&cid=' + ebindr.current.cid + '&lid=' + ebindr.current.lid );
						break;

					case "b!":
					case "c!":
					case "m!":
						this.editr( 'lite button ' + button );
						break;

					case "f 1":
						// TODO, define the userguidelink?
						window.open(userguidelink, "UserGuide");
						break;

					case "is": //Snapshot inquiry
						// open the snapshow in a window
						ebindr.doNormal( button, {
							contentURL: "/m/report/menu.Stats.Inquiry by Bid/?ebindr2=y&bid=" + ebindr.current.bid,
							width: 650
						});
						break;

					case "ir":
						this.reportlog( "lite button " + button );
						ebindr.openFINDr( "d", "T" );
						break;

					case "in":
						this.reportlog( "lite button " + button );
						ebindr.current.lid = ebindr.current.lid+1;
						if( ebindr.current.lid > 3 ) ebindr.current.lid = 1;
						ebindr.dolanguage();
						break;

					case "iu":
						this.reportlog( "lite button " + button );
						if(!contextmenu)
							ebindr.openFINDr( "l", "B" );
						else if(ebindr.data.store.currententity=='BBB') {
							ebindr.logstat(ebindr.current.bid, 'B', "bindr");
							ebindr.growl("Inquiry stat", "Bureau stat has been logged");
						} else
							ebindr.alert("This record is not a BBB");
						break;
					case "ia":
						this.reportlog( "lite button " + button );
						if(!contextmenu)
							ebindr.openFINDr( "k", "A" );
						else if(ebindr.data.store.currententity=='AGCY') {
							ebindr.logstat(ebindr.current.bid, 'A', "bindr");
							ebindr.growl("Inquiry stat", "Agency stat has been logged");
						} else {
							ebindr.doNormal( button, {
								contentURL: "/m/report/menu.Admin.Agency List/?ebindr2=y",
								width: 650
							});
//							ebindr.alert("This record is not an agency");
						}
						break;
					case "ig":
						this.reportlog( "lite button " + button );
						ebindr.openFINDr( "g", "G" );
						break;
					case "iv":
						this.reportlog( "lite button " + button );
						ebindr.logstat(ebindr.current.bid, 'R', "bindr");
						if(!contextmenu) {
							if(ev.control) this.linkreport(ebindr.data.store.localverbalreportlink);
							else this.linkreport(ebindr.data.store.verbalreportlink);
						} else {
							//ev.preventDefault(); //added 6/3/2013
							//ev.stopPropagation();//added 6/3/2013
							ebindr.growl("Inquiry stat", "Stat has been logged");
						}
						break;
					case "if":
						this.reportlog( "lite button " + button );
						ebindr.logstat(ebindr.current.bid, 'R', "bindr");
						if(!contextmenu) {
							if(ev.control) this.linkreport(ebindr.data.store.localfullreportlink);
							else this.linkreport(ebindr.data.store.fullreportlink);
						} else ebindr.growl("Inquiry stat", "Stat has been logged");
						break;

					case "mn":
						// make sure you want to cancel the membership
						if(ebindr.current.isAB) {
							ebindr.modal.confirm( 'Are you sure you wish to cancel this business\' Accreditation?', function( ret ) {
								if( ret ) {
									// if yes
									ebindr.button.editr( "lite button m-.editr", true );
								}
							});
						} else {
							// TODO, where are these set?
							//if(previouslysuspended) extramsg="\r\n\r\nNOTE: This business' Accreditation was previously suspended.";
							//if(previouslyrevoked) extramsg="\r\n\r\nNOTE: This business' Accreditation was previously revoked.";
							ebindr.modal.confirm( 'Are you sure you wish to make this record an Accredited Business?', function(ret) {
								if( ret ) {
									ebindr.button.editr( "lite button m+.editr", true );
								}
							});
						}
						break;
					case "mb":
						ebindr.doNormal( 'lite button mb', {
							id: 'lite button mb',
							//contentURL: '/ebindr/views/video.html?frame=' + ebindr.current.frame.button + '&button=' + ebindr.current.button + '&tab=' + ebindr.current.page,
							contentURL: '/ebindr/accreditation',
							padding: { top: 0, bottom: 0, left: 0, right: 0 },
							width: window.getSize().x-65,
							height: window.getSize().y-80,
							resizable: false,
							draggable: false,
							maximizable: false,
							//width: 725,
							//height: 400,
							headerStartColor:  [250, 250, 250],
							headerStopColor:   [229, 229, 229]
						});
						break;
					case "bw":
						if(typeof ev !== 'undefined') {
							if(ev.control) this.linkreport(ebindr.data.store.localfullreportlink);
							else this.linkreport(ebindr.data.store.fullreportlink);
						} else {
							this.linkreport(ebindr.data.store.fullreportlink);
						}
						//****LinkReport(nostatfullreportlink);
						break;
					case "bv":
						if(typeof ev !== 'undefined') {
							if(ev.control) this.linkreport(ebindr.data.store.localverbalreportlink);
							this.linkreport(ebindr.data.store.verbalreportlink);
						} else {
							this.linkreport(ebindr.data.store.verbalreportlink);
						}
						//****LinkReport(nostatverbalreportlink);
						break;
					case "dr":
						//****document.getElementById("frame_dr").contentWindow.location.reload();
						break;
					case "vn":
						ebindr.doNormal( 'vn', {
							contentURL: 'http://192.168.1.29/bbb_coloradosprings3478234.php',
							title: 'IVR Management Console',
							padding: { 'top': 0, 'bottom': 0, 'left': 0, 'right': 0 }
						});
					break;
					// security keys
					case "securitykeys":
						this.logbutton( 'securitykeys' );
						new ebindr.doWindow({
							id: 'seckeyswin',
							title: 'Security Access Management',
							loadMethod: 'iframe',
							contentURL: '/ebindr/views/security/index.html?staff=' + callback,
							width: 750,
							height: 450,
							padding: { top: 0, bottom: 0, left: 0, right: 0 },
							toolbar: true,
							toolbarURL: '/ebindr/views/security/tabs.html'
						});
					break;
					// bbb accounts
					case "aa":

						//console.log("bbb accounts");
						new ebindr.doWindow({
							id: 'bbbacct',
							title: 'BBB Accounts',
							loadMethod: 'iframe',
							contentURL: '/ebindr/views/bbbacct/linked.html',
							width: 750,
							height: 450,
							padding: { top: 0, bottom: 0, left: 0, right: 0 },
							toolbar: true,
							toolbarURL: '/ebindr/views/bbbacct/tabs.html'
						});



						/*ebindr.doNormal( 'aa', {
							contentURL: 'http://' + ebindr.data.store['appurl'] + '/account/bbb.html',
							title: 'BBB Account Management'
						});*/
					break;
					case "vm":
						this.logbutton( 'vrsmonitor' );
						ebindr.doNormal( 'vm', {
							contentURL: '/myphone.htm',
							title: 'VRS Monitor',
							width: 397,
							height: 456
						});
//						window.open("/myphone.htm","myphone", "toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=397,height=456");
						break;
					case "customer-reviews":
						if( ebindr.button.access('do') || ebindr.button.access('ac') ) {
							ebindr.doNormal( 'ac', {
								id: 'customer-reviews-win',
								tacable: true,
								contentURL: '/m/report/Process Customer Reviews/?ebindr2=y'
							});
						}
						//$( 'commonreports_iframe' ).contentWindow.location="/m/report/Process Complaints/?ebindr2";
						break;
					case "dp":
						ebindr.doNormal( 'dp', {
							id: 'process-complaints',
							tacable: true,
							contentURL: '/m/report/Process Complaints/?ebindr2=y'
						});
						//$( 'commonreports_iframe' ).contentWindow.location="/m/report/Process Complaints/?ebindr2";
						break;
					case "dd":
						ebindr.doNormal( 'dd', {
							id: 'process-ad-reviews',
							tacable: true,
							contentURL: '/m/report/Process Ad Reviews/?ebindr2=y'
						});
						//$( 'commonreports_iframe' ).contentWindow.location="/m/report/Process Complaints/?ebindr2";
						break;
					case "dp":
						ebindr.doNormal( 'dp', {
							id: 'process-complaints',
							tacable: true,
							contentURL: '/m/report/Process Complaints/?ebindr2=y'
						});
						//$( 'commonreports_iframe' ).contentWindow.location="/m/report/Process Complaints/?ebindr2";
						break;
					case "de":
						ebindr.doNormal( 'de', {
							id: 'process-mediation',
							tacable: true,
							contentURL: '/m/report/Process Mediations/?ebindr2=y'
						});
						//$( 'commonreports_iframe' ).contentWindow.location="/m/report/Process Complaints/?ebindr2";
						break;
					case "di":
						ebindr.doNormal( 'di', {
							id: 'process-small-claims',
							tacable: true,
							contentURL: '/m/report/Process Small Claims/?ebindr2=y'
						});
						//$( 'commonreports_iframe' ).contentWindow.location="/m/report/Process Complaints/?ebindr2";
						break;
					case "db":
						ebindr.doNormal( 'db', {
							id: 'process-sbqs',
							tacable: true,
							contentURL: '/m/report/ProcessSBQs/?ebindr2=y'
						});
						//$( 'commonreports_iframe' ).contentWindow.location="/m/report/ProcessSBQs/?ebindr2";
						break;
					case "dn":
						//$( 'commonreports_iframe' ).contentWindow.location="/m/report/menu/?ebindr2";
						ebindr.doNormal( 'dn', {
							id: 'dn_window:' + Math.round((new Date()).getTime() / 1000), // + ebindr.current.bid,
							title: 'Common Reports',
							tacable: true,
							contentURL: '/m/report/menu/?ebindr2=y',
							resize: function() {
								alert('test');
								$$('form[name=limit]').setStyles({'height':windowEl.canvasEl.getHeight()-110, 'overflow':'scroll'});
								$$('body')[0].setStyles({'overflow':'hidden'});
							}
						});
						break;
					/*
case "do":
						ebindr.doNormal( 'do', {
							id: 'do_window',// + ebindr.current.bid,
							title: 'Reportr',
							contentURL: '/reportr.html'
						});
						break;
*/
					case "di":
						ebindr.doNormal( 'di', {
							title: 'MIP Tickler',
							id: 'miptickler',
							contentURL: '/m/report/menu.Member.Member Tickler/?ebindr2=y'
						});
						break;
					case "du":
						ebindr.doNormal( 'du', {
							title: 'Audit Tickler',
							id: 'audittickler',
							tacable: true,
							contentURL: '/m/report/menu.Audit Tickler/?ebindr2=y'
						});
						break;
					case "dt":
						ebindr.doNormal( 'dt', {
							title: 'Database Stats',
							contentURL: '/m/report/lite button dt/?noheader&ebindr2=y'
						});
						break;
					case "vt":
						ebindr.doNormal( 'vt', {
							title: 'VRS Stats',
							contentURL: '/m/report/lite button vt/?noheader&ebindr2=y'
						});
						break;

					case "editorderbtn":
						this.logbutton( 'editorderbtn' );
						ebindr.doNormal( 'editorderbtn', {
							'title': 'Edit Order of Buttons',
							padding: {
								top: 0,
								bottom: 0,
								left: 0,
								right: 0
							},
							width: 300,
							loadMethod: 'iframe',
							contentURL: '/ebindr/views/edit-order-biz.html'
						});
					break;

					case "editorderbtnfindr":
						var msg = 'To re-order your buttons we need to close FINDr so that when re-launched it will pull the new ordering. This means any search currently open will be lost. Do you want to continue?';
						ebindr.modal.confirm( msg, function( ret ) {
							ebindr.button.logbutton( 'editorderbynfindr' );
							if( ret ) {
								$('findr2').dispose();
								$('findr2_dockTab').dispose();
								ebindr.doNormal( 'editorderbtnfindr', {
									'title': 'Edit Order of FINDr Buttons',
									padding: {
										top: 0,
										bottom: 0,
										left: 0,
										right: 0
									},
									width: 300,
									loadMethod: 'iframe',
									contentURL: '/ebindr/views/edit-order-findr.html',
									onClose: function() {
										ebindr.openFINDr();
									}
								});
							}
						}, [ 'Yes, close FINDr', 'No, not yet' ]);
					break;

					case "as":
						ebindr.doNormal( 'ds', {
							'title': 'Settings',
							padding: { 'top': 0, 'bottom': 0, 'left': 0, 'right': 0 },
							contentURL: '/ebindr/views/database/settings.html'
						});
						break;
					case "dc":
					case "df":
					case "ds":
					case "dm":
					case "da":
					case "dl":
					case "mr":
					case "br":
					case "aw":
					case "vr":
						this.list(button);
						break;
					case "mp":
					case "ms":
					case "password":
						this.editr( 'lite button ' + button + '.editr' );
						//****editr_run_edit(mybutton);
						break;
					case "ma":
					case "ml":
					case "md":
					// these three is for the new products section in ebindr
					case "my":
					case "mg":
					case "mu":

					case "aq":
					case "dv":
					case "au":
					case "dx":
					case "ak":

//					case "vo":
					case "vs":
					case "vb":
					case "vg":
					case "vf":
					case "vh":
					case "vp":
					case "vd":
					case "vl":
						this.editr( 'lite button ' + button);
						//****editr_run(mybutton);
						break;
					case "ao":
						ebindr.doNormal( 'ai', {
							'title': 'Setup Options',
							contentURL: '/ebindr/views/database/setup.html',
							padding: { 'top': 0, 'bottom': 0, 'left': 0, 'right': 0 }
						});
						break;
					case "mock":
						ebindr.doNormal( button, {
							id: button + '-' + ebindr.current.bid,
							title: 'mock',
							contentURL: "/m/report/lite button " + button + "/?noheader&ebindr2=y"
						});
						break;
					case "b-":
						/*mysrc = new String();
						mysrc = document.getElementById("button_m").src;*/
						if( ebindr.current.isAB ) {
							ebindr.alert( 'You cannot delete this business record. Please cancel their accreditation first.', 'Warning' );
						} else {
							ebindr.button.editr_edit( "lite button b-.editr", ebindr.current.bid );
						}
						break;
					case "in":
						ebindr.current.lid=ebindr.current.lid+1;
						if(ebindr.current.lid>2) ebindr.current.lid=1;
						//****DoLanguage();
						break;
					case "io":
					case "ih":
						this.editr( 'lite button ' + button);
						//****editr_run(mybutton);
						break;
					case "cf":
						if(ebindr.current.frame.name=='complaint') {//run only if in complaint frame also
							if(ebindr.current.cid==0){
								ebindr.alert("Please select a complaint.");
								//console.log('case:cf alert: '+ebindr.current.frame.button+', '+ebindr.current.frame.name);
								return;
							}else if(ebindr.current.cid>0){
								if(ebindr.current.complaintalert>"") { ebindr.alert( ebindr.current.complaintalert ); ebindr.current.complaintalert=""; }
								this.list(button);
							}else{
								if(ebindr.current.ctype=="historical" && ebindr.current.cid==-1) {
									ebindr.modal.confirm( 'This complaint record has been historicalized. Do you wish to retrieve this record so you can reopen it, view details, etc?</br></br>NOTE: If you do NOT reopen this complaint, it will automatically be re-historicalized tonight.', function( ret ) {
										if( ret ) {
											ebindr.current.cid=ebindr.current.key1;
											$('frame_c').addEvent('load', function(e) { ebindr.button.go( button ); } );
											$('frame_c').fireEvent('reload',"/m/report/e button c/?noheader&bid={bid}&lid={lid}&cid={cid}&closecode=555&NOPROMPT retrievecid={cid}&cmd=transfer");
										}

									}, [ 'Yes, retrieve record', 'No, do not retrieve record' ]);
									return;
								}
							}
						}
						/***** if(complaintalert>"") { alert(complaintalert); complaintalert=""; }
						if(ctype=="historical") {
							if(confirm("This complaint record has been historicalized. Click OK if you wish to retrieve this record so you can reopen it, view details, etc.\r\n\r\nNOTE: If you do NOT reopen this complaint, it will automatically be re-historicalized tonight.")) {
								cid=key1;
								editrTop=0;
								document.getElementById("frame_c").contentWindow.document.location.replace("/m/report/lite button c/?noheader&bid="+bid+"&lid="+lid+"&cid="+cid+"&NOPROMPT retrievecid="+cid+"&closecode=555&cmd=transfer");
							}
							return;
						}
						mylist('lite button cf'); */
						//if(ebindr.current.cid>0) this.list(button);
						break;
					case "ca":
						if(ebindr.current.frame.name=='complaint') {//run only if in complaint frame also
							if(ebindr.current.cid==0){
								ebindr.alert("Please select a complaint.");
								//console.log('case:ca alert: '+ebindr.current.frame.button+', '+ebindr.current.frame.name);
								return;
							}else if(ebindr.current.cid>0){
								if(ebindr.current.complaintalert>"") { ebindr.alert( ebindr.current.complaintalert ); ebindr.current.complaintalert=""; }
								ebindr.button.editr( "lite button " + button );
							}else{
								if(ebindr.current.ctype=="historical" && ebindr.current.cid==-1) {
									ebindr.modal.confirm( 'This complaint record has been historicalized. Do you wish to retrieve this record so you can reopen it, view details, etc?</br></br>NOTE: If you do NOT reopen this complaint, it will automatically be re-historicalized tonight.', function( ret ) {
										if( ret ) {
											ebindr.current.cid=ebindr.current.key1;
											$('frame_c').addEvent('load', function(e) { ebindr.button.go( button ); } );
											$('frame_c').fireEvent('reload',"/m/report/e button c/?noheader&bid={bid}&lid={lid}&cid={cid}&closecode=555&NOPROMPT retrievecid={cid}&cmd=transfer");
										}

									}, [ 'Yes, retrieve record', 'No, do not retrieve record' ]);
									return;
								}
							}
						}


						/***** if(complaintalert>"") { alert(complaintalert); complaintalert=""; }
						editr_run('lite button ca'); */
						break;
					case "ct":

						/***** if(complaintalert>"") { alert(complaintalert); complaintalert=""; }
						if(cid==0) { alert("Please select a complaint."); return; }
						if(!confirm("Are you sure you wish to transfer this complaint?")) return;
						transfer=true;
						findr();
						alert("Please find the business you wish to transfer this complaint to and open up its record."); */

						if(ebindr.current.frame.name=='complaint') {//run only if in complaint frame also
							if(ebindr.current.cid==0){
								ebindr.alert("Please select a complaint.");
								//console.log('case:ct alert: '+ebindr.current.frame.button+', '+ebindr.current.frame.name);
								return;
							}else if(ebindr.current.cid>0){
								if(ebindr.current.complaintalert>"") { ebindr.alert( ebindr.current.complaintalert ); ebindr.current.complaintalert=""; }
								ebindr.openFINDr("n");
								ebindr.transferComplaint();
								return;
							}else{
								if(ebindr.current.ctype=="historical" && ebindr.current.cid==-1) {
									ebindr.modal.confirm( 'This complaint record has been historicalized. Do you wish to retrieve this record so you can reopen it, view details, etc?</br></br>NOTE: If you do NOT reopen this complaint, it will automatically be re-historicalized tonight.', function( ret ) {
										if( ret ) {
											ebindr.current.cid=ebindr.current.key1;
											$('frame_c').addEvent('load', function(e) { ebindr.button.go( button ); } );
											$('frame_c').fireEvent('reload',"/m/report/e button c/?noheader&bid={bid}&lid={lid}&cid={cid}&closecode=555&NOPROMPT retrievecid={cid}&cmd=transfer");
										}

									}, [ 'Yes, retrieve record', 'No, do not retrieve record' ]);
									return;
								}
							}
						}
						break;
					case "cr":
						if(ebindr.current.frame.name=='complaint') {//run only if in complaint frame also
							if(ebindr.current.cid==0){
								ebindr.alert("Please select a complaint.");
								//console.log('case:cr alert: '+ebindr.current.frame.button+', '+ebindr.current.frame.name);
								return;
							}else if(ebindr.current.cid>0){
								if(ebindr.current.complaintalert>"") { ebindr.alert( ebindr.current.complaintalert ); ebindr.current.complaintalert=""; }
								this.list(button);
							}else{
								if(ebindr.current.ctype=="historical" && ebindr.current.cid==-1) {
									ebindr.modal.confirm( 'This complaint record has been historicalized. Do you wish to retrieve this record so you can reopen it, view details, etc?</br></br>NOTE: If you do NOT reopen this complaint, it will automatically be re-historicalized tonight.', function( ret ) {
										if( ret ) {
											ebindr.current.cid=ebindr.current.key1;
											$('frame_c').addEvent('load', function(e) { ebindr.button.go( button ); } );
											$('frame_c').fireEvent('reload',"/m/report/e button c/?noheader&bid={bid}&lid={lid}&cid={cid}&closecode=555&NOPROMPT retrievecid={cid}&cmd=transfer");
										}

									}, [ 'Yes, retrieve record', 'No, do not retrieve record' ]);
									return;
								}
							}

						}
/***** if(complaintalert>"") { alert(complaintalert); complaintalert=""; }
						if(ctype=="historical") {
							if(confirm("This complaint record has been historicalized. Click OK if you wish to retrieve this record so you can reopen it, view details, etc.\r\n\r\nNOTE: If you do NOT reopen this complaint, it will automatically be re-historicalized tonight.")) {
								cid=key1;
								editrTop=0;
								document.getElementById("frame_c").contentWindow.document.location.replace("/m/report/lite button c/?noheader&bid="+bid+"&lid="+lid+"&cid="+cid+"&NOPROMPT retrievecid="+cid+"&closecode=555&cmd=transfer");
							}
							return;
						}
						mylist('lite button cr'); */
						break;
					case "ce":
					case "co":
						if(ebindr.current.frame.name=='complaint') {//run only if in complaint frame also
							if(ebindr.current.cid==0){
								ebindr.alert("Please select a complaint.");
								//console.log('case:co alert: '+ebindr.current.frame.button+', '+ebindr.current.frame.name);
								return;
							}else if(ebindr.current.cid>0){
								if(ebindr.current.complaintalert>"") { ebindr.alert( ebindr.current.complaintalert ); ebindr.current.complaintalert=""; }
								ebindr.button.editr_edit( "lite button c." + ebindr.current.ctype.toUpperCase() + ".editr", true );
							}else{
								if(ebindr.current.ctype=="historical" && ebindr.current.cid==-1) {
									ebindr.modal.confirm( 'This complaint record has been historicalized. Do you wish to retrieve this record so you can reopen it, view details, etc?</br></br>NOTE: If you do NOT reopen this complaint, it will automatically be re-historicalized tonight.', function( ret ) {
										if( ret ) {
											ebindr.current.cid=ebindr.current.key1;
											ebind.frameEl.addEvent('load', function(e) { ebindr.button.go( button ); } );
											ebind.frameEl.fireEvent('reload',"/m/report/e button c/?noheader&bid={bid}&lid={lid}&cid={cid}&closecode=555&NOPROMPT retrievecid={cid}&cmd=transfer");
										}

									}, [ 'Yes, retrieve record', 'No, do not retrieve record' ]);
									return;
								}
							}
						}

						/***** if(complaintalert>"") { alert(complaintalert); complaintalert=""; } */

						break;
					case "cn":
					case "c+":
						// This section is when they try to file a complaint against a business where ANOTHER business is supposed to receive all complaints
						if(ebindr.current.complaintBID>0 && ebindr.current.complaintBID!=ebindr.current.bid) {
							ebindr.modal.confirm( 'You cannot file a complaint against this record. Do you wish to file a complaint against BID ' + ebindr.current.complaintBID + '?', function( ret ) {
								if( ret ) {
									//override the default of doing nothing when a new bid is opened
									ebindr.onOpenBID=function() { $( "cn" ).fireEvent( 'click' ); };
									//switch to the other business that is supposed to receive complaints
									ebindr.openBID( ebindr.current.complaintBID );
								} else return;
							}, [ 'Yes', 'No' ]);
						}

						if( !ebindr.current.modal ) { //This line disables it if the modal dialog from above is open
							ebindr.modal.confirm( 'Proceed to file a complaint against <b>' + $('bn').get('text') + '</b>?', function( ret ) {
								if( ret ) {
									// if yes
									ebindr.button.editr( "lite button c+.editr", ebindr.current.bid );
								} else {
									return;
								}
							}, [ 'Yes, File Complaint', 'No, Cancel' ]);
						}
						break;
					case "c-":
						/***** if(complaintalert>"") { alert(complaintalert); complaintalert=""; } */
						if(ebindr.current.complaintalert>"") { ebindr.alert( ebindr.current.complaintalert ); ebindr.current.complaintalert=""; }
						if( ebindr.current.cid>0 ) {
							ebindr.modal.confirm( 'Are you sure you wish to delete complaint #<b>' + ebindr.current.cid + '</b>?', function( ret ) {
								if( ret ) {
									// if yes
									ebindr.frameEl.fireEvent('reload',"/m/report/lite button c-.editr/?noheader&bid={bid}&lid={lid}&cid={cid}&closecode=555&cmd=delete");
								} else {
									return;
								}
							}, [ 'Yes, Delete Complaint', 'No, Cancel' ]);
						} else ebindr.alert( "Please choose a complaint first", 'Choose' );

						break;
					case "bh":
						//****editr_run_edit("lite button charity");
						ebindr.button.editr_edit( "lite button charity.editr", true );
						break;
					case "bb":
						if(ebindr.data.store.country=='CANADA') ebindr.button.editr_edit( "lite button bbCANADA.editr", true );
						else ebindr.button.editr_edit( "lite button bb.editr", true );
						break;
					case "ipaddresses":
						ebindr.button.editr( "lite button ipaddresses" );
						break;
					case "advertising":
						ebindr.button.editr( "lite button advertising" );
						break;
					case "investigations":
						if (ebindr.data.store.securitykeys.indexOf("binvestigations-") > -1) readonly=true;
						ebindr.button.editr( "investigationslist" );
						break;
					case "statusreview":
						ebindr.button.editr( "statusreview" );
						break;
					case "relatedreports":
						ebindr.button.editr( "lite button relatedreports" );
						break;
					case "locationoverride":
						ebindr.button.editr( "lite button locationoverride.editr" );
						break;
					case "bbbautologin":
						this.logbutton( 'bbbacct' );
						if( this.access( button ) ) window.open( ebindr.data.store['bbbautologin'] );

						break;
					case "forceupdate":
						ebindr.modal.confirm( 'This feature will force an update of all information associated with this business record. Do you wish to proceed?', function( ret ) {
							if( ret ) {
								new Request.HTML({
									url: '/m/report/merge/update.htm/?NOASK&ebindr2=y&bid='+ebindr.current.bid,
									method: 'get',
									onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
										ebindr.alert( responseHTML, 'Update' );
									},
									onFailure: function() {
										ebindr.alert( "Update failed!", 'Update' );
									}
								}).send();
							} else {
								return;
							}
						}, [ 'Yes, update', 'No, Cancel' ]);
						break;
					case "masstransfer":
//						if(!ebindr.isHurdman()) {
//							ebindr.alert("This feature is still in development");
//							return;
//						}
						ebindr.modal.confirm( 'This will allow you to mass-transfer complaints on the current business record to another BBB. It will ONLY allow you to transfer complaints that have not already been transferred (ie. those that have not been closed 400). Do you wish to proceed?', function( ret ) {
							if( ret ) {
								ebindr.button.editr_edit( "lite button masstransfer.editr" );
							} else {
								return;
							}
						}, [ 'Yes', 'No' ]);
						break;
					case "mycomplaints":
						ebindr.button.editr( "lite button mycomplaints" );
						break;
					case "customfields":
						ebindr.button.editr_edit( "special projects.editr", true );
						break;
					case "bk":
						this.logbutton( 'reportlink' );
						ebindr.alert( '<a target="_blank" href="http://' + ebindr.data.store.reporturl + '">' + ebindr.data.store.reporturl + '</a><br><br><a target="_blank" href="/m/report/merge/pdfurl.pdf?url='+escape('http://'+ebindr.data.store.reporturl+'?hidesides=Y')+'&ATTACHMENT_NAME='+escape('Business Review for '+$('bn').get('text').replace(/[^A-Za-z ]+/g, ""))+'.pdf"><img src="/ebindr/images/icons16x/pdf.gif"> Generate PDF of Business Review</a><br><br><a href="/m/report/merge/Business Summary.br.rtf/?bid='+ebindr.current.bid+'&alpdf=yes" target="_blank"><img src="/ebindr/images/icons16x/pdf.gif"> Generate PDF of Business Summary</a><br><br><a target="_blank" href="http://'+ebindr.data.store.reporturl.replace('business-reviews','mobile-business-reviews')+'">Mobile Business Review</a><br><br><a target="_blank" href="http://' + ebindr.data.store.reporturl.replace('business-reviews','old-business-reviews') + '">Old Reliability Report</a><br><br><a target="_blank" href="https://bbbreviews.we-print.it/'+ebindr.data.store.bbbid+'/'+ebindr.current.bid+'">Customer Review Invitation Order Form</a>', 'Report Link' );
						break;
					case "bn":
					case "bz-btn":
					case "bz":
					case "ba":
					case "bp":
					case "bf":
					case "bo":
					case "bt":
					case "bu":
					case "b@":
					case "bs":
					case "bj":
					case "bq":
					case "bl":
					case "bx":
					case "be":
						if( button == 'bn' && ebindr.button.bn ) {
							ebindr.button.editr( "lite button " + button );
						} else if( button == 'bn' && !ebindr.button.bn ) {
							return;
						} else {

						ebindr.button.editr( "lite button " + button );
						}
						//****editr_run(mybutton);
						break;
					case "bmain":
					case "bd":
					case "bg":
						//****editr_run_edit(mybutton);
						ebindr.button.editr( "lite button " + button + '.editr' );
						break;

					case "nf":
					case "ctrl-fo":
						ebindr.doNormal( 'ctrl-fo', {
							contentURL: '/ebindr/forum.php/',
							title: 'Forum'
						});
						break;

					case "scanneddocs":
						this.fileBrowser( 'bid', ebindr.current.bid );
						break;
					default:
						if(button.match(/^editr_/)) { ebindr.button.editr_edit( 'lite button ' + button.replace(/^editr_/g, '') ); break; }
						if(button.match(/^list_/)) { this.list(button.replace(/^list_/g, '') ); break; }
						if(button.match(/^findr_/)) { ebindr.openFINDr( button.replace(/^findr_/g, '') ); ebindr.findr2.search( button.replace(/^findr_/g, ''), ' ' ); }
						var custfn=window[button+"_run"];
						if(typeof custfn == "function") { custfn(); break; }

				}
			} else {
				if(button=="ak") this.editr( 'lite button password.editr' );
			}
		}
	},

	list: function( button ) {
		var listid = 'list-' + button;// + '-' + ebindr.current.bid;
		var liststore = true;
		switch(button) {
			case "cr":
			case "cf":
				listid = 'list-' + button;// + '-' + ebindr.current.bid + '-' + ebindr.current.cid;
				liststore = false;
				if( ebindr.current.cid == 0 && ebindr.current.frame.name=='complaint') {
						ebindr.alert( "Please select a complaint." );
						//console.log('case:cf list alert: '+ebindr.current.frame.button+', '+ebindr.current.frame.name);
						return;
				}
			break;
		}

		var title = 'List';
		switch( button ) {
			case "mr": title = "Accreditation Reports"; break;
		}

		ebindr.doList( button, {
			id: listid,
			title: title,
			storeOnClose: liststore,
			contentURL: "/m/report/lite button " + button + "/?noheader&ebindr2=y&cid={cid}&bid={bid}&bbbid={bbbid}&lid={lid}".substitute(ebindr.current)
		});

	},

	linkreport: function( link ) {
		if( link.contains("http") || link.match( "^/" ) )
			window.open( String( link.replace(/\[bid\]/gi,"{bid}") + ( link.match( "[?]" ) ? "&" : "?" ) + "language={lid}" ).substitute(ebindr.current) );
		else
			window.open( "/m/report/merge/"+link+"?lid={lid}&gen={key1}&tob={key1}&cid={cid}&bid={bid}&bbbid={bbbid}".substitute(ebindr.current) );
	},

	fileBrowser: function( type, value ) {
		switch( type ) {
			case "cid":
				ebindr.doNormal( 'filebrowser', {
					contentURL: '/m/report/filebrowser/' + type + '/' + value + '?sortby=date&ebindr2=y',
					title: 'File Browser',
					padding: { top: 0, bottom: 0, left: 5, right: 0 },
					width: 600,
					height: 400
				});
				break;

			case "other":
				ebindr.doNormal( 'filebrowser', {
					contentURL: '/m/report/filebrowsers/' + type + '?ebindr2=y',
					title: 'File Browser',
					padding: { top: 0, bottom: 0, left: 5, right: 0 },
					width: 600,
					height: 400
				});
				break;

			default:
				ebindr.doNormal( 'filebrowser', {
					contentURL: '/m/report/filebrowser/' + type + '/' + value + '?ebindr2=y',
					title: 'File Browser',
					padding: { top: 0, bottom: 0, left: 5, right: 0 },
					width: 600,
					height: 400,
					onClose: function() { ebindr.data.get( 'e button info.scanned docs' ); }
				});
				break;
		}
	},

	editr: function( button, extraquerystring ) {
		if(typeof(extraquerystring) == 'undefined') extraquerystring="";
		// if we are working with complaint actions check to make sure we have a cid
		if( button == 'lite button ca' && ( !$chk(ebindr.current.cid) || ebindr.current.cid == 0 ) && ebindr.current.frame.name=='complaint') {
				ebindr.alert( 'Please select a complaint.', 'Woops!' );
				//console.log('case:Woops! alert: '+ebindr.current.frame.button+', '+ebindr.current.frame.name);
		} else {
			if( $(button) ) {
				if( button.substr( 0, 4 ) == 'qvq ' && $(button).getParent().id != 'recent-registers' ) {
					var get = new Request({
						url: '/m/report/e recent reports.insert',
						nocache: true
					}).post({
						'mergecode': button
					});
					ebindr.data.get( 'e recent reports.list', function(data) {
						// read in the data
						ebindr.data.load( 'e recent reports.list', data );
					});
				}
			}

			// log that we are running an editr
			ebindr.log( 'Run editr ' + button );
			// TODO: set the export link to "/m/report/exportr,"+button+"/?noheader&bid="+bid+"&lid="+lid+"&cid="+cid;
			// set the current editr
			ebindr.current.editr = button;
			ebindr.current.editrExt = button;
			ebindr.current.editrInsert = button;
			if(button=="lite button b+.editr") {
				var findrValues=String("&FINDrA={FINDrA}&FINDrN={FINDrN}&FINDrE={FINDrE}&FINDrW={FINDrW}&FINDrP={FINDrP}").substitute(ebindr.current);
				ebindr.current.FINDrA="";
				ebindr.current.FINDrP="";
				ebindr.current.FINDrN="";
				ebindr.current.FINDrE="";
				ebindr.current.FINDrW="";
			} else var findrValues="";
			// launch the editor
			ebindr.doEditr( button, {
				id: button.replace( /[.]editr$/, "" ).replace( / /g, "-" ) + "-" + ebindr.current.bid,
				title: "EDITr",
				contentURL: '/m/report/' + button.replace( /[.]editr$/, "" ) + '/?' + ( button.match( /[.]editr$/ ) ? 'editr&' : '' ) + 'ebindr2=y&noheader&bid=' + ebindr.current.bid + '&lid=' + ebindr.current.lid + '&cid=' + ebindr.current.cid + '&bbbid=' + ebindr.bbbid + findrValues + extraquerystring,
				onFocus: function(windowEl) {
					ebindr.current.windowEl=windowEl;
					ebindr.current.frameEl=windowEl.getElements('iframe')[0];
					if(windowEl.getElements('iframe')[0].contentWindow.currentEditr=="undefined" || windowEl.getElements('iframe')[0].contentWindow.currentEditr=="") return;
					ebindr.button.setExt( windowEl.getElements('iframe')[0].contentWindow );
				},
				onBlur: function(windowEl) {
					ebindr.current.windowEl=null;
					ebindr.current.frameEl=null;
					ebindr.button.unSetExt();
				},
				onMinimize: function(windowEl) {
					ebindr.current.windowEl=null;
					ebindr.current.frameEl=null;
					ebindr.button.unSetExt();
				},
				thisLoad: function(windowEl) {
					ebindr.current.windowEl=windowEl;
					ebindr.current.frameEl=windowEl.getElements('iframe')[0];
					if(windowEl.getElements('iframe')[0].contentWindow.currentEditr=="undefined" || windowEl.getElements('iframe')[0].contentWindow.currentEditr=="") return;
					ebindr.button.setExt( windowEl.getElements('iframe')[0].contentWindow );
					if($type(ebindr.current.editrOnLoad) == 'function') {
						ebindr.current.editrOnLoad();
						ebindr.current.editrOnLoad=null;
					}
					var editrScroll = new Fx.Scroll(windowEl, {
					    offset: {
					        x: 0,
					        y: 0
					    }
					}).toTop();
					// console.log( $( button.replace( /[.]editr$/, "" ).replace( / /g, "-" ) + '_content' ).getElement('iframe').getChildren() );
				},
				onClose: function() {
//					var iframe = ebindr.window.iframe( button.replace( / /g, "-" ) + "-" + ebindr.current.bid + '_iframe' ).window;
	//				alert(ebindr.current.undo1);
		//			ebindr.button.setExt( iframe );
			//		alert(ebindr.current.undo1);
				//	alert( iframe.location.href);
					ebindr.refreshdata();
					ebindr.current.windowEl=null;
					ebindr.current.frameEl=null;
					if(ebindr.current.cid<0) ebindr.current.cid=abs(ebindr.current.cid);
					if( ebindr.current.editr ) {
						var thisframe=ebindr.current.editr.replace( /^.+button ([a-z]).+$/, "$1");
						if(thisframe=="c") {
							//$('frame_c').fireEvent('reload',"/m/report/e button c/?noheader&bid={bid}&lid={lid}&cid={cid}&closecode=555&NOPROMPT retrievecid={cid}&cmd=");
						} else if( $('frame_' + thisframe ) ) $( 'frame_' + thisframe ).contentWindow.location.reload();
					}
				}

			});
			//ebindr.current.windowEl = $( button.replace( / /g, "-" ) + "-" + ebindr.current.bid );
			// TODO: focus on the first field in the editr	(added in scripts/library/window.js [search "inputfocus"])
		}
	},

	setExt: function( editrwindow ) {
		if(!editrwindow) return;
		ebindr.current.editr=editrwindow.currentEditr;
		ebindr.current.editrExt=editrwindow.currentEditrExt;
		ebindr.current.editrInsert=editrwindow.currentInsertEditr;
		ebindr.current.customextension=editrwindow.customextension;
		ebindr.current.customeditrextension=editrwindow.customeditrextension;
		if(typeof(editrwindow.editrundo1)!="undefined") ebindr.current.undo1=editrwindow.editrundo1;
		if(typeof(editrwindow.editrundo2)!="undefined") ebindr.current.undo2=editrwindow.editrundo2;
	},

	unSetExt: function() {
		ebindr.current.editr='';
		ebindr.current.editrExt='';
		ebindr.current.editrInsert='';
		ebindr.current.customextension='';
		ebindr.current.customeditrextension='';
		ebindr.current.undo1=false;
		ebindr.current.undo2=false;
	},

	editr_edit: function( button, bid, dontsetExt ) {
		if( ebindr.button.access( button.replace("lite button ","") ) ) {
			if(ebindr.current.frameEl != null) this.setExt( ebindr.current.frameEl.contentWindow );
			button=button.replace( /^e button c.editr/, 'lite button c.' + ebindr.current.ctype + '.editr' );
			// lite button cn.editr
			var windowid=button.replace( /[.]editr$/, "" ).replace( / /g, "-" ) + '-' + bid;
	//		ebindr.growl(button);
	//ebindr.growl(ebindr.current.windowEl.id);
			if(ebindr.current.windowEl != null && button != "lite-button-cn" ) {
				var newlocation=ebindr.doCurrentlocation( ebindr.current.windowEl.id );
			} else var newlocation="undefined";
			//var newlocation = "undefined";
	//		var newlocation=ebindr.current.frameEl.contentWindow.document.location;
	//		ebindr.growl(newlocation);
			//ebindr.growl( 'newlocation', newlocation );
			if( newlocation == "undefined" ) {
				ebindr.button.editr( button );
				return;
			}
			//ebindr.growl( 'ids', ebindr.current.windowEl.id + ' - ' + button );
			//window.parent.ebindr.window.focusWindow(ebindr.current.windowEl.id);
			ebindr.doChange( ebindr.current.windowEl.id, '/m/report/' + button.replace( /[.]editr$/, "" ) + '/?' + ( button.match( /[.]editr$/ ) ? 'editr&' : '' ) + 'ebindr2=y&noheader' + ebindr.current.customeditrextension + '&bid=' + bid + '&lid=' + ebindr.current.lid + '&cid=' + ebindr.current.cid + '&bbbid=' + ebindr.bbbid + '&key1=' + ebindr.current.key1 + '&key2=' + ebindr.current.key2 + '&reportr=' + escape( '/m/report/' + ebindr.current.editrExt + '/?noheader&ebindr2=y&bid=' + bid + '&lid=' + ebindr.current.lid + '&cid=' + ebindr.current.cid + '&bbbid=' + ebindr.bbbid ) + ebindr.current.customextension );
		} else {
			//ebindr.notify('Sorry you do not have access to edit.');
		}
	},

	report: function( button, link ) {
		if( !ebindr.access( button ) ) {
			ebindr.notify( 'You do not have access!' );
		} else {
			// .. continue logic
		}
	},
	reportlog: function( mergecode ) {
		new Request.HTML({
			url: '/m/report/'+mergecode+'?ebindr2=y&bid='+ebindr.current.bid,
			method: 'get'
		}).send();
	},

	logbutton: function( buttonname ) {
		new Request.HTML({
			url: '/m/report/logbutton/?name='+buttonname+'&ebindr2=y&bid='+ebindr.current.bid,
			method: 'get'
		}).send();
	},
	
	cbbbvideo: function( embedcode ) {
		ebindr.doNormal( 'e button by', {
			id: 'cbbbvideo',
			width: 600,
			height: 400,
			padding: { top: 0, bototm: 0, left: 0, right: 0 },
			maximizable: false,
			title: "Submitted Video",
			contentURL: "/ebindr/views/cbbbvideo.html?embedcode=" + embedcode
		});
	}
	
});
