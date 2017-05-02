ebindr.library.findr2 = new Class({

	Implements: [Events, Options],
	// the tab options
	options: new Hash(),
	focus: document.body, // element that has focus
	await: false,
	type: 'begins',
	what: 'n',
	typing: false,
	started: false,
	onstart: [],
	historyitems: [],
	lastsearchvalue: '',

	initialize: function( options ) {
		$extend(this.options,options);
	},

	/*
		What gets fired when the window is restored or opened for the first time
	*/
	windowInit: function() {
		// focus on the box
		if( $('search-q') ) {
			$('search-q').focus();
			$('search-q').select();
			if( ebindr.current.auto_findr ) ebindr.findr2.setSearch( ebindr.current.auto_findr );
			if( ebindr.current.link_search != '' ) {
				$('search-q').value = ebindr.current.link_search;
				if (ebindr.current.auto_findr != '') {
					ebindr.findr2.search(ebindr.current.auto_findr, ebindr.current.link_search);
				} else {
					ebindr.findr2.search('x');
				}
				// $('findr_iframe').contentWindow.$('findr-search').value = ebindr.current.link_search;
				// ebindr.findr.search('x');
				ebindr.current.link_search = '';
				// $('search-q').value = ebindr.current.link_search;
				// ebindr.findr2.search('x');
				// ebindr.current.link_search = '';
			}
			ebindr.findr2.typing = true;
		}
	},

	findrhistory: function() {
		if($('findrhistory') && ebindr.data.store.dofindrhistory=='yes') {
//			if($('findrhistory').style.display!='none') return;
			if($('search-q').value==this.lastsearchvalue) return;
			this.lastsearchvalue=$('search-q').value;
			$('findrhistory').empty();
			this.historyitems=[];
			for(var i=49;i>-1;i--) {
				if(Cookie.read('findr_'+i)===null) continue;
				else {
					var searchoption=Cookie.read('findr_'+i).split(":");
					if(searchoption.length!=2) continue;
//					if(!searchoption[1].replace(/$%/g,'').match('^'+$('search-q').value)) continue;
					this.historyitems[this.historyitems.length]=[searchoption[1].replace(/^%/g,'')+(searchoption[1].match(/^%/)?' (any part of ':' (')+ebindr.findr2.getButtonByClass(searchoption[0])+')', Cookie.read('findr_'+i), searchoption[1].replace(/^%/g,'')];
				}
			}
//			this.historyitems.sort(function(a,b) { return a[2]>b[2]; });
			this.historyitems.sort(function(a,b) {
				if( b[2].match('^'+$('search-q').value) && !a[2].match('^'+$('search-q').value) ) return true;
				if( a[2].match('^'+$('search-q').value) && !b[2].match('^'+$('search-q').value) ) return false;
				return a[2]>b[2];
			});
			for(var i=0;i<this.historyitems.length;i++) {
				var searchoption=this.historyitems[i][1].split(":");
				var newoption = new Option(this.historyitems[i][0], this.historyitems[i][1]);
				newoption.addEvent('mouseover', function(e) {
					this.selected=true;
				}).addEvent('mouseout', function(e)	{
					this.selected=false;
				}).addEvent('click', function(e) {
					var searchoption=this.value.split(":");
					if(searchoption[1].match(/^%/)) {
						$('search-by-type').set('text', '*Any Part Of*' );
						ebindr.findr2.type = 'any';
					} else {
						$('search-by-type').set( 'text', 'Begins With *' );
						ebindr.findr2.type = 'begins';
					}
					$('search-type').set( 'text', ebindr.findr2.getButtonByClass(searchoption[0]) );
					ebindr.findr2.search(searchoption[0],searchoption[1].replace(/^%/g, ''));
				});
				$('findrhistory').add(newoption);
			}
			$('findrhistory').setStyle('display','block');
			var newoption = new Option("- Clear search history -","clear");
			newoption.addEvent('mouseover', function(e) {
				this.selected=true;
			}).addEvent('mouseout', function(e)	{
				this.selected=false;
			}).addEvent('click', function(e) {
				for(var i=49;i>-1;i--) {
					if(Cookie.read('findr_'+i)===null) continue;
					Cookie.dispose('findr_'+i);
				}
				$('findrhistory').empty().setStyle('display','none');
			});
			$('findrhistory').add(newoption);
		}
	},

	start: function() {
		// log that findr was started
		console.log( 'FINDr started' );

		//$('findr2').setStyle('top', 40);

		$('search-q').addEvent( 'keydown', ebindr.findr2.keyboard.bind(this) );
		$('search-q').addEvent( 'keyup', ebindr.findr2.keyup.bind(this) );
		var dd=new Element('select', {
			id: 'findrhistory',
			multiple:true,
			size:6,
			styles: { display:'none', position:'absolute', width:'380px', 'padding-left':'15px', 'cursor':'pointer' }
		});
		dd.injectAfter($('search-q'));
//		$('search-q').addEvent( 'mouseover', ebindr.findr2.findrhistory );
		$('search-q').addEvent('blur', function(e) {
			(function() { if($$('::focus').length>0 && $$('::focus')[0].get('id')!='findrhistory')$('findrhistory').setStyle('display','none') }).delay(100);
		});
		ebindr.findr2.searchType();
		ebindr.findr2.buttonEvents();
		ebindr.findr2.windowInit();

		if( ebindr.current.link_search != '' ) {
			$('search-q').value = ebindr.current.link_search;
			if (ebindr.current.auto_findr != '') {
				ebindr.findr2.search(ebindr.current.auto_findr, ebindr.current.link_search);
			} else {
				ebindr.findr2.search('x');
			}
			// $('findr_iframe').contentWindow.$('findr-search').value = ebindr.current.link_search;
			// ebindr.findr.search('x');
			ebindr.current.link_search = '';
		}

		ebindr.findr2.started = true;
		ebindr.findr2.more();

		if( ebindr.findr2.onstart.length > 0 ) {
			ebindr.findr2.onstart.each( function(run,i) {
				eval(run);
			});
		}


	},

	keyup: function(event) {
		//ebindr.findr2.typing = false;
	},

	keyboard: function(event) {
		var numericSearches = [
			'u',
			'h',
			'p',
			't',
			'check',
			'consumer',
			'license',
			'mediation'
		];

		(function(){ebindr.findr2.findrhistory();}).delay(50);

		switch(event.keyCode) {
			case 38: //up arrow
				if($('findrhistory').selectedIndex>0) $('findrhistory').selectedIndex--; else $('findrhistory').selectedIndex=0;
				break;
			case 40: //down arrow
				if($('findrhistory').selectedIndex<($('findrhistory').options.length-1)) $('findrhistory').selectedIndex++; else $('findrhistory').selectedIndex=($('findrhistory').options.length-1);
				break;
		}

		//if( !event ) event = new Event(event);
		ebindr.findr2.typing = true;
		switch( event.key ) {
    		case "enter":
				if($('findrhistory').selectedIndex>-1) {$('findrhistory').options[$('findrhistory').selectedIndex].click(); break; }
				if( ebindr.isNumber($('search-q').get('value')) && !numericSearches.contains(ebindr.findr2.what) ) {
					what = 'i';
					ebindr.findr2.what = 'i';
					$('search-type').set( 'text', ebindr.findr2.getButtonByClass('i') );
				}
    			ebindr.findr2.search( ebindr.findr2.what );
    		break;
    		case "tab": event.stop(); ebindr.findr2.await = true; break;
    		case "esc": event.stop(); ebindr.window.minimize('findr2'); $('search-q').blur(); break;
    		case "up":
				if($('findrhistory').options.length>0) {
					if($('findrhistory').selectedIndex>0) $('findrhistory').selectedIndex--; else {
						$('findrhistory').selectedIndex=-1;
						$('findrhistory').setStyle('display','none');
					}
					break;
				}
    		case "down":
				if($('findrhistory').options.length>0) {
					if($('findrhistory').selectedIndex<($('findrhistory').options.length-1)) $('findrhistory').selectedIndex++; else $('findrhistory').selectedIndex=($('findrhistory').options.length-1);
					break;
				}
    			$('findr2-search-frame').focus();
    			$('findr2-search-frame').contentWindow.key_select(event);
    		break;
			case "n":
			case "i":
			case "e":
			case "p":
			case "a":
			case "u":
			case "h":
			case "c":
			case "k":
			case "l":
			case "z":
			case "t":
			case "d":
			case "g":
    			if( ebindr.findr2.await ) {
    				ebindr.findr2.buttonByClass( event.key );
    				event.stop();
    				ebindr.findr2.await = false;
    				return false;
    			}
    		break;
    	}
	},

	/*
		Fire the button click based on the class name
	*/
	buttonByClass: function( name ) {
    	var potential = $$('#findr2-body .' + name);
		if( potential.length > 0 ) potential[0].fireEvent('click');
	},

	/*
		Get the text of the button by class
	*/
	getButtonByClass: function( name ) {
    	var potential = $$('#findr2-body .' + name);
		if( potential.length > 0 ) return potential[0].get('text');
		else return '';
	},


	/*
		Search Type actions and setting
	*/
	searchType: function() {
		$('search-by-type').addEvent( 'click', function(e) {
			if( this.get('text') == 'Begins With *' ) {
				this.set('text', '*Any Part Of*' );
				ebindr.findr2.type = 'any';
			}
			else {
				this.set( 'text', 'Begins With *' );
				ebindr.findr2.type = 'begins';
			}
		});
	},

	/*
		Add the click events to the buttons
	*/
	buttonEvents: function() {
		$$( '#more-list li, .findr-btns li' ).each( function(btn) {
			if( !btn.hasClass('spacer') ) {
				btn.addEvent('click', function(e) {
					if( this.get('class').split(" ")[0] == 'editorderbtn' ) return ebindr.button.go('editorderbtnfindr');
					else {
						//new Event(e).stop();
						if( btn.get('text') != 'Edit Order' ) $('search-type').set( 'text', btn.get('text') );
						// if we have something in the search box to search by
						if( $('search-q').get('value').length > 0 || btn.get('class').split(" ")[0] == 'salescomment' ) {
							ebindr.findr2.what = btn.get('class').split(" ")[0];
							ebindr.findr2.search( ebindr.findr2.what );
						}
					}
				});
			}
		});
	},

	/*
		Build the more search options list
	*/
	more: function() {
        var $self = this;
		$('more-list').setStyles({
			'position': 'absolute',
			'right': 5,
			'top': $('more-search').getCoordinates().top + 10,
			'z-index': 10000
		});

		$('more-search').addEvent( 'click', function(e) {//when the >> button is hovered/clicked
            e.preventDefault();
			var $moreList = jQuery('#more-list');
            if( $moreList.hasClass( 'hide-more' ) ){
                $self._initMore();
                $moreList.removeClass('hide-more');
                $moreList.addClass('show-more');
                $moreList.setStyle('display', '');
            }else{
                $moreList.removeClass('show-more');
                $moreList.addClass('hide-more');
                $moreList.setStyle('display', 'none');
            }

            /*$('more-list').setStyle( 'display', '' ).addEvent( 'mouseleave', function(e) {
				this.setStyle( 'display', 'none' );
			});*/
		});

        $self._initButtons();
	},

    _initMore: function(){
        var findr2_details = $('findr2_contentWrapper').getCoordinates();

        if(findr2_details.height<480){

            $('more-list').setStyles({
                'width': '600px',
                'right': 5,
                'top': $('more-search').getCoordinates().top + 20,
                'position': 'absolute',
                'z-index': 10000
            });

            $$('div#more-list div.top').setStyles({
                'background-image': 'url( \'/ebindr/images/findr1/findrmore-top2.png\' )',
                'width': '600px',
                'height': '25px'
            });
            $$('div#more-list div.bottom').setStyles({
                'background-image': 'url( \'/ebindr/images/findr1/findrmore-bottom2.png\' )',
                'width': '600px',
                'height': '25px'
            });

            $$('div#more-list div.list').setStyles({
                'width': '580px',
                'padding-left':'10px',
                'padding-right':'10px'
            });

        }else{
            $('more-list').setStyles({
                'width': '300px',
                'right': 5,
                'top': $('more-search').getCoordinates().top + 10,
                'position': 'absolute',
                'z-index': 10000
            });

            $$('div#more-list div.top').setStyles({
                'background-image': 'url( \'/ebindr/images/findr1/findrmore-top.png\' )',
                'width': '300px',
                'height': '25px'
            });
            $$('div#more-list div.bottom').setStyles({
                'background-image': 'url( \'/ebindr/images/findr1/findrmore-bottom.png\' )',
                'width': '300px',
                'height': '12px'
            });

            $$('div#more-list div.list').setStyles({
                'width': '280px',
                'padding-left':'10px',
                'padding-right':'10px'

            });
        }
    },

    _initButtons: function(){
        var displayed = 0;
        $$('ul.findr-btns li').each( function(btn,i) {
            if( !btn.hasClass('spacer') ) {
                if( i == 0 ) displayed = btn.getCoordinates().top;
                if( btn.getCoordinates().top != displayed ) {
                    btn.clone().inject($('more-list').getElements('ul')[0]).addEvents({
                        'click': function(e) {
                            //$('more-list').setStyle( 'display', 'none' );
                            if( this.get('class').split(" ")[0] == 'editorderbtn' )
                                return ebindr.button.go('editorderbtnfindr');
                            else ebindr.findr2.buttonByClass( this.get('class').split(" ")[0] );
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

        });
    },

	setSearch: function( what ) {
		ebindr.findr2.what = what;
		$('search-type').set( 'text', ebindr.findr2.getButtonByClass(what) );
	},

	search: function( what, q ) {
		if(what=="more") return;
		$('findrhistory').setStyle('display','none');

		if( !ebindr.findr2.started ) {
			ebindr.findr2.onstart.push( "ebindr.findr2.search('" + what + "'" + ( typeof(q) != 'undefined' ? ",'" + q + "'" : "" ) + ");" );
			return;
		}
		if (document.getElementById('search-type').textContent == "ABs Near Address") {
			ebindr.findr2.what = "ab-address";
			what = "ab-address";
		}

		// if ( ! window.console ) console = { log: function(){} };
		// console.log(what); //to check where chicago stops

		// see if we want to do an auto-search
		if( typeof(q) != 'undefined' ) $('search-q').value = q;
		// set what we are search on and what we are searching for
		var q = ( ebindr.findr2.type == 'begins' ? '' : '%' ) + $('search-q').get('value') + '%';
		if( what.match( /^findr-/ ) ) what = what.replace( /^findr-/g, "s." );
		var savedfindr=false;
		if($('findrhistory') && ebindr.data.store.dofindrhistory=='yes') {
			for(var i=0;i<10;i++) {
				if(Cookie.read('findr_'+i)===null && !savedfindr) {
					Cookie.write('findr_'+i, what+':'+q.replace(/%+$/g, ''), {duration:365});
					savedfindr=true;
					break;
				}
				if(Cookie.read('findr_'+i)==what+':'+q.replace(/%+$/g, '')) {
					savedfindr=true;
					break;
				}
	
			}
			if(!savedfindr) {
				for(i=0;i<49;i++) {
					Cookie.write('findr_'+i, Cookie.read('findr_'+(i+1)), {duration:365});
				}
				Cookie.write('findr_'+i, what+':'+q.replace(/%+$/g, ''), {duration:365});
			}
		}
		switch( what ) {
			case "non-ab-address": what = "s.Non-ABs Near Address"; break;
			case "ab-address": what = "s.ABs Near Address"; break;
			case "email": what = "s.Business Email"; break;
			case "charity": what = "s.Charity-NonProfit"; break;
			case "check": what = "s.Check Number"; break;
			case "address": what = "s.Consumer Address"; break;
			case "consumer": what = "s.Consumer Member"; break;
			case "email-c": what = "s.Consumer Email"; break;
			case "text": what = "s.Custom Text"; break;
			case "journal": what = "s.Journal Notes"; break;
			case "license": what = "s.License Number"; break;
			case "mediation": what = "s.Mediation Case"; break;
			case "naturedispute": what = "s.Nature of Dispute"; break;
			case "currentbid": what = "s.Nature on Current Bid"; break;
			case "newname": what = "s.New Name Search"; break;
			case "complaint": what = "s.other Complaint Numbers"; break;
			case "reportcode": what = "s.Report Code"; break;
			case "reportstatus": what = "s.Report Status"; break;
			case "salescomment": what = "s.Sales Comment"; break;
			case "smallclaims": what = "s.Small Claims Case"; break;
			case "zip-postal": what = "s.ZIP"; break;
			case "arb": what = "s.ARBNumber"; break;
		}

		// log what the search
		ebindr.log( 'FINDr Search Details: lite findr ' + what + ' "' + q + '"' );

		if(what=="a") ebindr.current.FINDrA=$( 'search-q' ).get('value').replace('&','%26').replace('#','%23');
		if(what=="p") ebindr.current.FINDrP=$( 'search-q' ).get('value');
		if(what=="n") ebindr.current.FINDrN=$( 'search-q' ).get('value').replace('&','%26').replace('#','%23');
		if(what=="e") ebindr.current.FINDrE=$( 'search-q' ).get('value');
		if(what=="w") ebindr.current.FINDrW=$( 'search-q' ).get('value');

		/*if( $('findr2-search-frame') ) $('findr2-search-frame').dispose();
		ebindr.findr2.showLoading();*/

		var searchurl = 'report/e2m lite findr ' + what + '/?noheaderfindr&e2mfindr&ebindr2=y' +
		'&find=' + escape(q) +
		'&bid=' + ebindr.current.bid +
		'&lid=' + ebindr.current.lid +
		'&key1=' + ebindr.current.key1;

		/*(function() {
			if( ebindr.isHurdman() ) ebindr.growl( 'search url', searchurl );
		}).delay(300);*/
		ebindr.findr2.loadIFrame( searchurl );
        ebindr.lastfindr = what;
		/*new IFrame({
		 	id: 'findr2-search-frame',
		    src: searchurl,
		    styles: ebindr.findr2.frameSize(),
		    events: {
		        load: function(){
		            ebindr.findr2.hideLoading();
		            $('findr2-search-frame').setStyle( 'display', '' );
		            $('findr2-search-frame').focus();
		            // $('2-1-2').focus();
		            ebindr.lastfindr = what;
		        }
		    }
		}).inject($('findr2-search-container'));*/
	},



	showLoading: function() {
		$('findr2-loading').setStyles( ebindr.findr2.frameSize() ).setStyle( 'display', '' );
	},

	hideLoading: function() {
		$('findr2-loading').setStyle( 'display', 'none' );
	},

    initIFrame: function( iframe )
    {
        ebindr.frameEl = document.getElementById( iframe );
        this._initIFrameEvent();
    },

    _initIFrameEvent: function(){
        var $self = this;
        ebindr.frameEl.onload = function(){
            ebindr.findr2.hideLoading();
            ebindr.frameEl.setStyle( 'height', ( jQuery( window ).height() - 150 ) + 'px' );
            ebindr.frameEl.setStyle( 'display', '' );
            ebindr.frameEl.focus();
        };
    },

    loadIFrame: function( src ){
        ebindr.frameEl.setStyle( 'display', 'none' );
        ebindr.findr2.showLoading();
        ebindr.frameEl.src = src;
    },

	frameSize: function() {
		var full = $('findr2_contentWrapper').getCoordinates();
		var used = $('findr2-body').getCoordinates();

		return {
			'width': full.width,
			'height': full.height-used.height,
			'padding': 0,
			'margin': 0,
			'border': 'none'
		};
	},

	choose: function() {
		switch( ebindr.lastfindr ) {
			case "u": case "c": case "h": ebindr.openBID(ebindr.current.key1, false, ebindr.current.key2); break;
			case "s.Consumer Member": var oldbid=bid; bid=key1; editr_run("qvq Consumer Members"); bid=oldbid; break;
			case "s.Mediation Case":
				var oldbid=ebindr.current.bid; bid=ebindr.current.key1;
				ebindr.openBID(bid);
				editr_run("qvq Mediation Cases"); bid=oldbid;
				break;
			case "s.Small Claims Case":
				var oldbid=ebindr.current.bid;
				bid=ebindr.current.key1;
				ebindr.openBID(bid);
				editr_run("qvq Small Claims Cases");
				bid=oldbid;
				break;
			case "s.VORP Case":
				var oldbid=ebindr.current.bid;
				bid=ebindr.current.key1;
				ebindr.openBID(bid);
				editr_run("qvq VORP Cases");
				bid=oldbid;
				ebindr.current.bid = oldbid;
				break;
			case "x": case "k": case "w": case "n": case "e": case "p": case "i":
				ebindr.openBID(ebindr.current.key1);
				if(ebindr.current.lastInquiry=="A") {
					ebindr.logstat(ebindr.current.key1, 'A', "bindr");
					ebindr.button.linkreport(ebindr.data.store.agencyreportlink);
				}
				break;
			case "l": case "z":
				ebindr.openBID(ebindr.current.key1);
				if(ebindr.current.lastInquiry=="B") {
					ebindr.logstat(ebindr.current.key1, 'B', "bindr");
					ebindr.button.linkreport(ebindr.data.store.bureaureportlink);
				}
				break;
			case "g":
				//if(ebindr.current.lastInquiry=="G") ebindr.logstat(ebindr.current.key1, 'G', "bindr");
				if(ebindr.current.lastInquiry=="G" || ebindr.lastfindr=="g") ebindr.logstat(ebindr.current.key1, 'G', "bindr");
				else ebindr.alert("A stat was NOT logged for this general advice report", "Notice");
				ebindr.button.linkreport(ebindr.data.store.genadvreportlink);
    			break;
			case "d": case "t":
				//if(ebindr.current.lastInquiry=="T") ebindr.logstat(ebindr.current.key1, 'T', "bindr");
				if(ebindr.current.lastInquiry=="T" || (ebindr.lastfindr=="d" || ebindr.lastfindr=="t")) ebindr.logstat(ebindr.current.key1, 'T', "bindr");
				else ebindr.alert("A stat was NOT logged for this TOB roster", "Notice");
				ebindr.button.linkreport(ebindr.data.store.rosterreportlink);
				break;
		}
		ebindr.current.lastInquiry=false;
	},

	find: function( which ) {
		switch( which ) {
			case 'esc': // Close FINDr
//				$( 'findr-search' ).blur();
				ebindr.window.minimize( 'findr' );
//				alert(window.parent.$( 'findr_closeButton' ).get('title'));
//				var closefindr=window.parent.$( 'findr_closeButton' );
//				closefindr.click();
//				window.parent.$( 'findr_titleBar' ).focus();
//				window.setTimeout("", 200);
				window.parent.document.getElementById("keydownfield").focus();
//				alert(window.parent.document.getElementById("keydownfield").value);
//				window.parent.MochaUI.closeWindow(window.parent.MochaUI.Windows.instances.get('findr').windowEl);
				break;
			case 'enter': // Retrieve record
				switch( ebindr.lastfindr ) {
					case "u": case "c": case "h": ebindr.openBID(ebindr.current.key1, false, ebindr.current.key2); break;
					case "s.Consumer Member": var oldbid=bid; bid=key1; editr_run("qvq Consumer Members"); bid=oldbid; break;
					case "s.Mediation Case": var oldbid=bid; bid=key1; editr_run("qvq Mediation Cases"); bid=oldbid; break;
					case "s.Small Claims Case": var oldbid=bid; bid=key1; editr_run("qvq Small Claims Cases"); bid=oldbid; break;
					case "x": case "k": case "w": case "n": case "e": case "p": case "i":
						if(ebindr.current.lastInquiry=="A") {
							ebindr.logstat(ebindr.current.key1, 'A', "bindr");
							ebindr.button.linkreport(ebindr.data.store.agencyreportlink);
						}
						ebindr.openBID(ebindr.current.key1)
						break;
					case "l": case "z":
						if(ebindr.current.lastInquiry=="B") {
							ebindr.logstat(ebindr.current.key1, 'B', "bindr");
							ebindr.button.linkreport(ebindr.data.store.bureaureportlink);
						}
						ebindr.openBID(ebindr.current.key1)
						break;
					case "g":
						if(ebindr.current.lastInquiry=="G") ebindr.logstat(ebindr.current.key1, 'G', "bindr");
						else ebindr.alert("A stat was NOT logged for this general advice report", "Notice");
						ebindr.button.linkreport(ebindr.data.store.genadvreportlink);
    					break;
					case "d": case "t":
						if(ebindr.current.lastInquiry=="T") ebindr.logstat(ebindr.current.key1, 'T', "bindr");
						else ebindr.alert("A stat was NOT logged for this TOB roster", "Notice");
						ebindr.button.linkreport(ebindr.data.store.rosterreportlink);
						break;
				}
				ebindr.current.lastInquiry=false;
				break;
			default:
				if( which.match( /^findr-/ ) ) which = which.replace( /^findr-/g, "s." );
				var find = ( $( 'begins' ).get('text') == 'Find Begins With *' ? '' : '%' ) + $( 'findr-search' ).get('value') + '%';
				// log what we are searching for
				ebindr.log( 'FINDr Search Details: lite findr ' + which + ' "' + find + '"' );
				if(which=="a") ebindr.current.FINDrA=$( 'findr-search' ).get('value');
				if(which=="p") ebindr.current.FINDrP=$( 'findr-search' ).get('value');
				if(which=="n") ebindr.current.FINDrN=$( 'findr-search' ).get('value');
				if(which=="e") ebindr.current.FINDrE=$( 'findr-search' ).get('value');
				if(which=="w") ebindr.current.FINDrW=$( 'findr-search' ).get('value');
				//$( 'findr-results' ).innerHTML = "Searching... please wait.";
				var searchstring = '/report/lite findr ' + which + '/?noheader&ebindr2' +
				'&find=' + escape(find) +
				'&bid=' + ebindr.current.bid +
				'&lid=' + ebindr.current.lid +
				'&key1=' + ebindr.current.key1;
				$( 'findr-results' ).set( 'src', searchstring );
				$( 'findr-results' ).focus();
				ebindr.lastfindr=which;

		}
	},

	resize: function () {
		(function() {
			$( 'findr-results' ).setStyle( 'height', window.getSize().y-195 );
			$( 'findr-results' ).setStyle( 'width', window.getSize().x );
			$( 'findr-search-div' ).setStyle( 'width', window.getSize().x-165 );
			$( 'begins' ).setStyle( 'width', '110px' );
			$( 'findr-more' ).setStyle( 'width', '50px' );
		}).delay(50);
	},

	listen: function() {
		( Browser.Engine.trident ? $$('body')[0] : window ).addEvent( 'keydown', function(event) {
			event = new Event(event);
			if( event.key == "esc" ) {
				ebindr.findr.find( 'esc' );
				event.preventDefault();
				event.stopPropagation();
			}
			switch( event.target.get('id') ) {
				case "findr-search":
					switch( event.key ) {
						case 'enter':
							$$( '.shade' ).fireEvent( 'click' );
							$( 'findr-search' ).blur();
							break;
						case 'tab':
							$( 'findr-search' ).blur();
							event.preventDefault();
							event.stopPropagation();
							break;
						default:
							break;
					}
					break;
				case null:
					switch( event.key ) {
						case "n": case "i": case "e": case "p": case "a": case "u": case "h": case "c": case "k": case "l": case "z": case "t": case "d": case "g":
							//alert( event.key );
							$( 'findr_' + event.key ).fireEvent( 'click' );
							break;
						case "tab":
							$( 'findr-search' ).focus();
							event.preventDefault();
							event.stopPropagation();
							break;
					}
					break;
				default:
					alert( event.key + event.target.get('id') );
					break;
			}
//			alert( event.key + event.target.get('id') );//+ ":" + ebindr.findr.focus.get('tag') );
		});
	}
});