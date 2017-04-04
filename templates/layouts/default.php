<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <[MYSQL_ERRORS]>
    <title><[current_query]></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <!--<link rel="stylesheet" type="text/css" href="/ebindr/styles/reportr.css" />-->
    <link rel="stylesheet" type="text/css" href="/ebindr/styles/plugins/datepicker.css" />
    <link rel="stylesheet" type="text/css" href="/ebindr/styles/plugins/spellchecker.css" />

    <link href="/m/assets/css/report.css" rel="stylesheet">
    <link href="/m/assets/css/bootstrap.css" rel="stylesheet">
    <link href="/m/assets/css/bootstrap-responsive.css" rel="stylesheet">

    <!-- script type="text/javascript" src="/ebindr/scripts/framework/core.js"></script -->
    <script type="text/javascript" src="/ebindr/scripts/framework/core-1.2.4.js"></script>
    <!-- script type="text/javascript" src="/ebindr/scripts/framework/more.js"></script -->
    <script type="text/javascript" src="/ebindr/scripts/framework/more-1.2.2.2.js"></script>
    <script type="text/javascript" src="/ebindr/scripts/plugins/datepicker.js"></script>
    <script src="/js-bin/control2.js" type="text/javascript"></script>
    <script src="/js-bin/date.js" type="text/javascript"></script>
    <script src="/js-bin/functions.js" type="text/javascript"></script>
    <script type="text/javascript" src="/ebindr/scripts/plugins/spellchecker.js"></script>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
        //callback function
        function createChart() {
            $$('div.reportchart').each(function(el) {
                window[el.get('id')]();
            });
        }
        function initchart() {
            if($$('div.reportchart').length>0) {
                //load the Google Visualization API and the chart
                google.load('visualization', '1.1', {'packages': ['corechart'], callback: 'createChart' });
            }
        }
    </script>
    <script>
        function KeyHandle() {
//	event.returnValue=false; 
            window.parent.editrLeft=document.body.scrollLeft;
            window.parent.editrTop=document.body.scrollTop;
            var myKey=event.altKey*100000+event.ctrlKey*10000+event.shiftKey*1000+event.keyCode;
            if(myKey==10075 || myKey==11075 || myKey==1114 || myKey==10077) {
                if(event.srcElement.type=='text') { // && (event.srcElement.name='name' || event.srcElement.name='Street1' || event.srcElement.name='FirstName' || event.srcElement.name='LastName')) {
                    var oRange=event.srcElement.createTextRange();
                    oRange.expand("textedit");
                    oRange.text=titleCase(oRange.text.toLowerCase());
                }
            }
            if(myKey==10084) {
                var mydate= new Date();
                if(event.srcElement.type=='text' || event.srcElement.type=='textarea') {
                    event.srcElement.selection=document.selection.createRange();
                    event.srcElement.selection.text=(mydate.getMonth()+1)+"/"+mydate.getDate()+"/"+mydate.getYear();
                }
            }
        }

    </script>
    <script type="text/javascript">
        /*	 var scrubfocus=function() {
         $(this.get('id')+'_scrub').setStyles({ display:'', width:this.getSize().x, height:this.getSize().y});
         $(this.get('id')+'_scrub_inst').setStyles({ display:''});
         this.setStyle('display', 'none');
         this.getPrevious('div.scrub').setStyle('display', 'none');
         };*/
        var alertScrub=function(e) {
            $$('div.scrubdiv').each(function(el) {
                if(el.getStyle('display')=='block') window.scrollTo(0,el.getPosition().y-50);
            });
            window.parent.ebindr.alert('You are currently in the process of scrubbing a field. Please complete that process before submitting.', 'Scrub in process');
            e.stopPropagation();
            return false;
        }
        var showscrub=function(el) {
            $(el.get('id')+'_scrub').setStyles({ display:'', width:el.getSize().x, height:el.getSize().y});
            $(el.get('id')+'_scrub_inst').setStyles({ display:''});
            el.setStyle('display', 'none');
            el.getPrevious('div.scrub').setStyle('display', 'none');
//		$$('input[value="Submit"]')[0].disabled=true;
//		$$('input[value="Submit"]')[0].set('title','You are currently in the process of scrubbing a field. Please complete that process before submitting.');
            $$('input[value="Submit"]')[0].set('onclick','alertScrub()');
        };
        scrubtest=function(element,names) {
            if(element.get('rel')=='clean') { showscrub(element); return; }
            if(element.get('rel')=='noscrub') {
//			window.parent.ebindr.growl("No scrubbing needed", "No profanity, possible sensitive numbers, email addresses, or consumer or employee names were found in this text.");
                showscrub(element); return;
            }
            try {
                new Request.HTML({
                    url:"/ebindr/includes/scrub.php",
                    async:false,
                    onSuccess: function( el, el2, html ) {
                        if(html.contains('<span')) var needsscrub=true; else needsscrub=false;
                        if(!needsscrub) {
                            if(element.get('rel')!='first') {
//							window.parent.ebindr.growl("No scrubbing needed", "No profanity, possible sensitive numbers, email addresses, or consumer or employee names were found in this text.");
                                element.set('rel','noscrub');
                            }
                        }
//					if(html.contains('<span')) {
                        html=html.replace(/<w/g, "<span class=word");
                        html=html.replace(/<\/w>/g, "</span>");
                        if(needsscrub) element.addClass("has-acct-or-profane");
                        scrubdiv = new Element('div', { id:element.get('id')+'_scrub', class:'scrubdiv', 'html':html } );
                        scrubdiv.injectAfter(element);
                        scrubdiv.setStyles({ display:'none', 'overflow-y':'scroll', border:'inset grey 3px', 'font-family':element.getStyle('font-family'), 'font-size':element.getStyle('font-size'), width:element.getSize().x, height:element.getSize().y});
                        instdiv = new Element('div', { id:element.get('id')+'_scrub_inst', styles: { 'display':'none', 'font-size':'10pt' }, 'html':'<div style="float:left;"><span style="cursor:hand;" class="scrub"><img style="float:left" lang="'+element.get('id')+'" src="/ebindr/images/icons16x/accept.png" border="0" title="Scrub item(s) in red shown below">&nbsp;Accept</span><br><span style="cursor:hand;" class="scrubcancel"><img style="float:left" lang="'+element.get('id')+'" src="/ebindr/images/icons16x/cancel.png" border="0" title="Cancel">&nbsp;Cancel</span></div><div style="float:right;text-align:right;">'+(needsscrub?'<i><b>The text highlighted in <font color=red>red</font> (to be scrubbed) or <font color=green>green</font> (possible match, do not scrub) have been identified below.</b><br>Click on the text to toggle scrubbing <font color=red>on (red)</font> and <font color=green>off (green)</font> for that item.</i>':'<i><b>No text has been identified for scrubbing.</b><br>Any words containing capital letters or numbers can be redacted by clicking on them.</i>')+'</div>' } );
                        instdiv.injectBefore(scrubdiv);
//						element.addEvent('focus', scrubfocus);
                        //if(element.get('rows')>1) element.focus();
                        instdiv.getElement('span.scrubcancel').addEvent('click', function(e) {
                            this.getParent().getParent().setStyle('display', 'none');
                            this.getParent().getParent().getNext('div').setStyle('display', 'none');
                            element.setStyle('display', '');
                            element.getPrevious('div.scrub').setStyle('display', '');
                            var divshowing=false;
                            $$('div.scrubdiv').each(function(el) { if(el.getStyle('display')=='block') divshowing=true; });
                            if(!divshowing) {
                                //$$('input[value="Submit"]')[0].disabled=false;
                                $$('input[value="Submit"]')[0].set('title','');
                                $$('input[value="Submit"]')[0].set('onclick','submitform();' );
                            }
                        });
//						scrubdiv.addEvent('dblclick', function(e) {
//							alert('test');
//							alert(document.getSelection().toString());
//						});
                        instdiv.getElement('span.scrub').addEvent('click', function(e) {
//							element.removeEvent('focus', scrubfocus);
                            var redactidlist="";
                            redactids.each( function(id) { redactidlist=redactidlist+","+id; } );
                            var redactwordlist="";
                            redactwords.each( function(word, pos) { if(word!='') redactwordlist=redactwordlist+" "+pos+" "+word; });
                            new Request.HTML({
                                url:"/ebindr/includes/scrub.php",
                                async:false,
                                onSuccess: function( el, el2, html ) {
                                    var vals=html.split(String.fromCharCode(1));
                                    //					ebindr.alert(element.get("value"), "Current text");
                                    element.set("value", vals[4]);
                                    element.set('rel','dirty');
                                    if(element.hasClass("has-acct-or-profane")) element.removeClass("has-acct-or-profane");
                                    window.parent.ebindr.growl("Text has been scrubbed", "The following was redacted:<br><br>"+vals[0]+" instance" + (vals[0]==1?"":"s")+" of profanity<br>"+vals[1]+" possible sensitive number" + (vals[1]==1?"":"s")+"<br>"+vals[2]+" email address" + (vals[2]==1?"":"es")+"<br>"+vals[3]+" consumer or employee name" + (vals[3]==1?"":"s"));
                                    //					(function() { $('alert-box_content').set('html', element.get("value")); }).delay(1000);
                                }
                            }).post({
                                string:element.get("value"),
                                names:names,
                                toscrub: redactidlist,
                                redactwordlist: redactwordlist
                            });

                            this.getParent().getParent().setStyle('display', 'none');
                            this.getParent().getParent().getNext('div').setStyle('display', 'none');
                            element.setStyle('display', '');
                            element.getPrevious('div.scrub').setStyle('display', '');
                            var divshowing=false;
                            $$('div.scrubdiv').each(function(el) { if(el.getStyle('display')=='block') divshowing=true; });
                            if(!divshowing) {
                                $$('input[value="Submit"]')[0].set('onclick','submitform();' );
                                $$('input[value="Submit"]')[0].disabled=false;
                                $$('input[value="Submit"]')[0].set('title','');
                            }
//							element.getPrevious('div.spell_checker_cp').setStyle('display', '');

                        });
                        if(element.get('rel')!='first') { showscrub(element); }
//						element.setStyles({display:'none'});
                        element.set('rel', (needsscrub?'clean':'noscrub'));
//					} else if(element.get('lang')!='first') {
//						window.parent.ebindr.growl("No scrubbing needed", "No profanity, possible sensitive numbers, email addresses, or consumer or employee names were found in this text.");
//						element.set('lang','noscrub');
//					} else element.set('lang','noscrub');


                        var redactids=[];
                        var redactwords=[];
                        $$('#' + element.get('id') + '_scrub span.redact').each(function(el) {
                            redactids[el.get('lang')]=!el.hasClass('accept');//true;
                            el.addEvent('mouseover', function(e) {
                                this.addClass('hover');
                                if(this.hasClass('accept')) this.set('title', 'Click to redact this item'); else  this.set('title', 'Click if you do not wish to redact this item');
                            }).addEvent('mouseout', function(e) {
                                this.removeClass('hover');
                            }).addEvent('click', function(e) {
                                redactids[this.get('lang')]=this.hasClass('accept');
                                this.toggleClass('accept');
                                if(this.hasClass('accept')) this.set('title', 'Click to redact this item'); else  this.set('title', 'Click if you do not wish to redact this item');
                            });
                        });
                        $$('#' + element.get('id') + '_scrub span.word').each(function(el) {
                            el.addEvent('mouseover', function(e) {
                                this.addClass('wordhover');
                                if(!this.hasClass('redact')) this.set('title', 'Click to redact this word'); else this.set('title', 'Click if you do not wish to redact this word');
                            }).addEvent('mouseout', function(e) {
                                this.removeClass('wordhover');
                            }).addEvent('click', function(e) {
                                this.toggleClass('redact');
                                redactwords[this.get('lang')]=(this.hasClass('redact')?this.get('text'):'');
                                if(this.hasClass('redact')) this.set('title', 'Click to redact this word'); else  this.set('title', 'Click if you do not wish to redact this word');
                            });
                        });
                    }
                }).post({
                    string:element.get("value"),
                    names:names,
                    stepthrough: 'yes'
                });
            } catch(error) { console.log(error); }
        }
        var exportrString="<[exportr_string]>";
        var windowEl = null;
        /*				var pubprompt=function(e) {
         new window.parent.Request.HTML({url:"/report/e%20button%20publish%20prompt?ebindr2=y&noheaderhidden", async:false}).post({cid:window.parent.ebindr.current.cid});
         };*/
        window.addEvent( 'load', function() {
            initchart();
            (function() {
                window.parent.document.getElements('iframe').each( function(frame) {
                    // find the frames location object
                    if(frame.get('src').match(/https*:\/\/[^\/]+/)) return;
                    frame.removeEvents('complaintpublish');
                    if(frame.contentWindow.location.href.match(/(Process%20Complaints[.]On%20Hold|Process%20Complaints[.]Response|button%20c).+[?].*editr/)) {
                        frame.addEvent('complaintpublish', function(e) {
                            new window.parent.Request.HTML({url:"/report/e%20button%20publish%20prompt?ebindr2=y&noheaderhidden", async:false}).post({cid:window.parent.ebindr.current.cid});
                        });
                    }
                });
            }).delay(200);
        });
        var freezetop=function() {
            if($$('form[name=complaintform]').length>0) return;
            return;
            $$('form[name=limit]').setStyles({'height':windowEl.canvasEl.getHeight()-110, 'width':windowEl.canvasEl.getWidth(), 'overflow':'scroll'});
            $$('body')[0].setStyles({'overflow':'hidden'});
        }
        window.addEvent( 'load', function() {
            $$('a').each(function(el) {
                if(el.href.replace( /^(https*:\/\/[^\/]+).*$/g, "$1" )==window.location.href.replace( /^(https*:\/\/[^\/]+).*$/g, "$1" ) && el.href!="" && !el.href.match("[?].*ebindr2=") && el.href!="#") {
                    el.href=el.href.replace( /[?]/i, "?ebindr2=y&" );
                    el.href=el.href.replace( /(reportr=.+noheader)/i, "$1%26ebindr2%3dy" );
                }
                var myclick=String(el.get('onclick'));
                if($chk(myclick) && myclick.match('submitform') && !myclick.match("[?].*ebindr2")) {
                    el.set('onclick', el.get('onclick').replace( /[?]/i, "?ebindr2=y&" ));
                }
            });

            // get the parent frames and find ourselves
            // check out what our current url is
            var thisurl = unescape(window.location.href.replace(window.location.hostname,"").replace("http://",""));
            var thisframe = '';
            // go through each iframe in the parent to find itself
            window.parent.document.getElements('iframe').each( function(frame) {
                // find the frames location object
                if(frame.get('src').match(/https*:\/\/[^\/]+/)) return;
                var thislocation = frame.contentWindow.location;
                try {
                    // get it's url to compare
                    var compareurl = unescape(thislocation.href.replace(thislocation.hostname,"").replace("http://",""))
                    // if we have the same url then we're done searching for the iframe
                    if( compareurl == thisurl ) {
                        // get the window element from mochaui
                        windowEl = window.parent.ebindr.window.parent.Windows.instances.get(frame.id.replace("_iframe",""));
                        thisframe = frame;
                    }
                } catch(error) { };
            });

//	try { if($$('div.breadcrumbs div').length<2) $('backrptbtn').dispose(); } catch(err) { };

            try {  if(window.parent.$('params_'+thisframe.get('id')+'_'+$$('div.breadcrumbs div').length)) window.parent.$('params_'+thisframe.get('id')+'_'+$$('div.breadcrumbs div').length).destroy();  } catch(err) { };

            window.parent.$$('form.commonparams').each(function(el){
                if(window.parent.$(el.get('id').replace('params_','').replace(/_[0-9]+$/g,''))===null) {
//		   console.log('destroy '+el.get('id').replace('params_','').replace(/_[0-9]+$/g,''));
                    try {  window.parent.$(el.get('id')).destroy(); } catch(err) { };
                }
            });

            var newaction=$$('form[name=printform]')[0].get('action').split('?')[0];

            $$('form[name=exportform]')[0].clone().set('id','params_'+thisframe.get('id')+'_'+$$('div.breadcrumbs div').length).addClass('commonparams').set('action',newaction).inject(window.parent.document.body,'after');

            i=1;
            $$('div.breadcrumbs div').each(function(el){
                if(!el.hasClass('active')){
                    el.getElement('a').set('rel',i);
                    el.getElement('a').addEvent('click', function(e) {
                        if(window.parent.$('params_'+thisframe.get('id')+'_'+this.get('rel'))==null) return true;
                        e.stop();
//				console.log('submit form: params_'+thisframe.get('id')+'_'+this.get('rel'));
//				console.log(this.get('href'));
                        var newform=window.parent.$('params_'+thisframe.get('id')+'_'+this.get('rel')).clone();
                        //newform.set('action',this.get('href'));
                        newform.set('method','post');
                        newform.inject(document.body,'after');
                        newform.submit();
                    })
                }
                i++;
            });
            freezetop();
            windowEl.addEvent('resize', freezetop);

            var tmp = new DatePicker( '.date', {
                pickerClass: 'datepicker_dashboard',
                format: 'm/d/Y',
                debug:false,
                onSelect: function(field) {
                    if( tmp.input ) {
                        var onchange = tmp.input.get( 'onchange' );
                        if( typeof(onchange) != 'null' ) {
                            eval(onchange);
                        }
                    }
                },
                allowEmpty: true,
                inputOutputFormat: 'm/d/Y',
                toggleElements: '.picker'
            });
            tmp2 = new DatePicker( '.datetime', {
                pickerClass: 'datepicker_dashboard',
                format: 'm/d/Y g:i A',
                allowEmpty: true,
                inputOutputFormat: 'm/d/Y g:i A',
                toggleElements: '.pickertime',
                timePicker: true
            });
            // add the spell checkers
            (function() {
                if( $$('textarea').length > 0 ) {
                    if($('*scrubnames')) var names=$('*scrubnames').get('value'); else var names='';
                    var sc = new SpellChecker( $$('textarea'), {
                        confirmAfterAllWordsChecked: false
                    });
                    $$('img.scrub_field').each(function(el) {
                        $(el.get('lang')).set('rel', 'first');
                        /*				$(el.get('lang')).addEvent('dblclick', function(e) {
                         el.getNext('img').setStyles({'display':'block', 'position':'absolute', 'left':e.client.x, 'top':e.client.y-30});
                         el.getNext('img').getNext('img').setStyles({'display':'block', 'position':'absolute', 'left':e.client.x+30, 'top':e.client.y-30});
                         });*/
                        el.setStyle('cursor','hand');
                        $(el.get('lang')).getPrevious('div.spell_checker_cp').setStyle('display', 'none');
                        el.addEvent("click", function(e) {
                            scrubtest($(el.get('lang')), names);
//					ebindr.scrub($(this.get('lang')), names);
                            e.stopPropagation();
                        });
                        $(el.get('lang')).addEvent("change", function(e) { this.set('rel','dirty'); } );
//				ebindr.scrubcheck($(el.get('lang')), names);
                        scrubtest($(el.get('lang')), names);
                        //$(el.get('lang')).addEvent('click', function(e) { scrubtest(this, names); } );
                    });
//			if($$('img.scrub_field').length>0) $($$('img.scrub_field')[0].get('lang')).focus();
                    if($$('.has-acct-or-profane').length>0 && !window.parent.ebindr.dont_show_scrub_message) window.parent.ebindr.alert("There are fields on this page that have profanity, possible sensitive numbers, email addresses, or consumer or employee names. They are highlighted with a <span style='border:3px solid red'>red border</span>. Please review the field and/or click the <img border='0' src='/ebindr/images/icons16x/scrub.jpg'> icon to redact this information.<br><br><input type=checkbox id=dontshowagain onclick='ebindr.dont_show_scrub_message=this.checked;'> Don't show again", "Scrubbing" );

                    $$('img.scrub_one').each(function(el) {
                        el.setStyle('cursor','hand');
                        el.addEvent("click", function(e) {
                            var txtobj=$(el.get('lang'));
                            var replacechar=txtobj.getSelectedText();
                            if(replacechar.match(" $")) txtobj.selectRange(txtobj.getSelectionStart(),txtobj.getSelectionEnd()-1);
                            else txtobj.selectRange(txtobj.getSelectionStart(),txtobj.getSelectionEnd());
                            replacechar=txtobj.getSelectedText();
                            new Request.HTML({url:"/report/app.scrub.addtolist?ebindr2=y&noheaderhidden", async:false}).post({word:replacechar});
                            names=names+(names!=""?",":"")+replacechar;
                            txtobj.insertAtCursor(Array(txtobj.getSelectionEnd()-txtobj.getSelectionStart()+1).join("*"));
                            e.stopPropagation();
                        });
                    });
                    $$('img.scrub_multi').each(function(el) {
                        el.setStyle('cursor','hand');
                        el.addEvent("click", function(e) {
                            var txtobj=$(el.get('lang'));
                            var cursorpos=txtobj.getCaretPosition();
                            if(txtobj.getSelectedText().match(" $"))txtobj.selectRange(txtobj.getSelectionStart(),txtobj.getSelectionEnd()-1);
                            else txtobj.selectRange(txtobj.getSelectionStart(),txtobj.getSelectionEnd());
                            var redactchars=Array(txtobj.getSelectionEnd()-txtobj.getSelectionStart()+1).join("*");
                            var replacechar=txtobj.getSelectedText();
                            new Request.HTML({url:"/report/app.scrub.addtolist?ebindr2=y&noheaderhidden", async:false}).post({word:replacechar});
                            names=names+(names!=""?",":"")+replacechar;
                            var regex=new RegExp(replacechar,"g");
                            txtobj.set("value", txtobj.get("value").replace(regex,redactchars));
                            txtobj.setCaretPosition(cursorpos);
//					ebindr.scrub($(this.get('lang')), names);
                            e.stopPropagation();
                        });
                    });
                }
            }).delay(200);
            // add the click to the html
            $$('html')[0].addEvent( 'click', function() {

                if( !['frame_c','frame_m','frame_g'].contains(thisframe.id) ) {
                    // focus on that window
                    window.parent.ebindr.window.parent.focusWindow(window.parent.$(windowEl.options.id));
                    return true;
                } else {
                    switch( thisframe.id ) {
                        case "frame_c": window.parent.ebindr.keyboard.frame('c'); break;
                        case "frame_m": window.parent.ebindr.keyboard.frame('m'); break;
                        case "frame_g": window.parent.ebindr.keyboard.frame('b'); break;
                    }
                }
                // go through each iframe in the parent to find itself
                /*window.parent.document.getElements('iframe').each( function(frame) {
                 // find the frames location object
                 var thislocation = frame.contentWindow.location;
                 // get it's url to compare
                 var compareurl = unescape(thislocation.href.replace(thislocation.hostname,"").replace("http://",""))
                 // if we have the same url then we're done searching for the iframe
                 if( compareurl == thisurl ) {
                 // make sure it isn't one of the static frames
                 if( !['frame_c','frame_m','frame_g'].contains(frame.id) ) {
                 // get the window element from mochaui
                 windowEl = window.parent.ebindr.window.parent.Windows.instances.get(frame.id.replace("_iframe",""));
                 // focus on that window
                 window.parent.ebindr.window.parent.focusWindow(window.parent.$(windowEl.options.id));
                 return true;
                 } else {
                 // if it is a static frame then make clicking on it select that frame
                 switch( frame.id ) {
                 case "frame_c": window.parent.ebindr.keyboard.frame('c'); break;
                 case "frame_m": window.parent.ebindr.keyboard.frame('m'); break;
                 case "frame_g": window.parent.ebindr.keyboard.frame('b'); break;
                 }
                 }
                 }
                 });*/
            });
        });
        trapfunction=function(event) {
//	if(!window.ie) return;
//	if( event.target == null ) event.target = false;
//	var e=(event.target?event.target:event.srcElement);
            var e = new Event(event);
            var event = e;
            window.parent.ebindr.keyboard.keydown(event,'<[current_query]>');
            key_select(event);
            KeyHandle();
        }
        document.onkeydown=trapfunction;
    </script>
</head>
<body bgcolor="#ffffff">

<[custom_header]>

<div class="cr-top">
    <a href="javascript:Print('<[query_run]>');"><img src="/ebindr/images/icons16x/print.png" alt="Print" /></a>
    <a href="#" id="cr-pdf"><img src="/ebindr/images/icons16x/pdf.gif" alt="Export to PDF" /></a>
    <a href="#" id="cr-xml"><img src="/ebindr/images/icons16x/xml.png" alt="XML" /></a>
    <a href="#" id="cr-xls"><img src="/ebindr/images/icons16x/excel.png" alt="XLS" /></a>
    <a href="#" id="cr-xlsnew"><img src="/ebindr/images/icons16x/excelnew.png" alt="XLSX" /></a>
    <a href="#" id="cr-ggl"><img src="/ebindr/images/icons16x/google-apps.png" alt="Google Apps" /></a>
    <a href="#" id="cr-rtf"><img src="/ebindr/images/icons16x/word.png" alt="RTF" /></a>
    <a href="#" id="cr-txt"><img src="/ebindr/images/icons16x/text.png" alt="TXT" /></a>
    <a href="#" id="cr-refresh"><img src="/ebindr/images/icons16x/refresh.png" alt="Refresh" /></a>
    <a href="#" id="cr-help" title="<[current_query]>"><img src="/ebindr/images/icons16x/help.png" alt="Help" /></a>
    <a href="#" id="cr-email" title="Email this report" lang="<[current_query]>"><img src="/ebindr/images/icons16x/email.png" alt="Email this report" /><span style="display:none;"><[USED_PARAMETERS]></span></a>
    <a href="#" id="cr-favorites-add" title="<[current_query]>"><img src="/ebindr/images/icons16x/favorites_add.png" alt="Add to Favorites" /></a>
    <a href="#" id="cr-resize-left" title="Resize: Fit Left Page"><img src="/ebindr/images/icons16x/resize-left.png" alt="Resize: Fit Left Page" /></a>
    <a href="#" id="cr-resize-center" title="Resize: Center"><img src="/ebindr/images/icons16x/resize-middle.png" alt="Resize: Center" /></a>
    <a href="#" id="cr-resize-right" title="Resize: Fit Right Page"><img src="/ebindr/images/icons16x/resize-right.png" alt="Resize: Fit Right Page" /></a>
    <a href="#" style="color:red;line-height:normal" title="This report is a common report but has been customized in some way for your BBB"><[customcommon_message]></a>
    <!-- input type=button id=backrptbtn value="<< Back" style="margin-top:5px;" onclick="$$('div.breadcrumbs div')[$$('div.breadcrumbs div').length-2].getElement('a').click();" -->
    <div style="clear: both;"></div>
</div>
<div id="cr-email-list" style="display:none;">
    <div class="top"></div>
    <div class="list">
        <ul>
            <li>One-time</li>
            <li>Daily</li>
            <li>Weekly</li>
            <li>Monthly</li>
            <li>Yearly</li>
        </ul>
        <div style="clear: both;"></div>
    </div>
    <div class="bottom"></div>
</div>
<[heirarchy]>

<form name="limit" action="/<[APPLICATION_FILENAME]>/<[current_location]>?ebindr2=y" method="post">
    <input type="hidden"  name="auto_scroll" id="auto_scroll" value="<[auto_scroll]>"/>
    <[postinputs]>
    <input type="hidden"  name="which_table" id="which_table" value="" />

    <[content]>
    <[submit]>
    <br />
</form>
<form name="exportxmlform" action="/<[APPLICATION_FILENAME]>/exportr,<[exportr_string]>&EXPORT_OUTPUT=xml" method="post">
    <[postinputs]>
</form>
<form name="exportformnew" action="/<[APPLICATION_FILENAME]>/exportr,<[exportr_string]>&EXPORT_OUTPUT=xlsx" method="post">
    <[postinputs]>
</form>
<form name="exportform" action="/<[APPLICATION_FILENAME]>/exportr,<[exportr_string]>&EXPORT_OUTPUT=xls" method="post">
    <[postinputs]>
</form>
<form name="exportwordform" action="/<[APPLICATION_FILENAME]>/exportr,<[exportr_string]>&EXPORT_OUTPUT=rtf" method="post">
    <[postinputs]>
</form>
<form name="exporttextform" action="/<[APPLICATION_FILENAME]>/exportr,<[exportr_string]>&EXPORT_OUTPUT=txt&EXPORT_DELIM=%2C" method="post">
    <[postinputs]>
</form>
<form name="printform" action="/<[APPLICATION_FILENAME]>/<[printr_string]>" method="post" target="_blank">
    <input type="hidden" name="allrecords" id="allrecords" value="NO">
    <input type="hidden" name="printperpage" id="printperpage" value="<[printperpage]>">
    <input type="hidden" name="limit1" id="printpage" value="">
    <input type="hidden" name="which_table" id="print_which_table" value="">
    <[postinputs]>
</form>
<form name="exceptform" action="" method="post">
    <input type="hidden" name="EXCEPTIONLIST" id="EXCEPTIONLIST" value="">
    <[postinputs]>
</form>
<form name="reportlinkform" action="" method="get" target="_blank">
    <[setinputs]>
</form>
<script language="javascript">
    function MergeReport(myloc) {
        document.reportlinkform.action=myloc;
        document.reportlinkform.submit();
    }

</script>
<[restriction_link]><[iptracking]>
<script src="/ebindr/scripts/framework/core.js" type="text/javascript"></script>
<script src="/ebindr/scripts/framework/more.js" type="text/javascript"></script>
<script type="text/javascript" src="/ebindr/scripts/reports.js?version=3"></script>
</body>
</html>
