<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><[application_name]></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" type="text/css" href="/css/reportr_calendar.css" />
<link rel="stylesheet" type="text/css" href="/ebindr/styles/plugins/datepicker.css" />
<link rel="stylesheet" type="text/css" href="/ebindr/styles/plugins/spellchecker.css" />
<script type="text/javascript" src="/ebindr/scripts/framework/core.js"></script>
<script type="text/javascript" src="/ebindr/scripts/framework/more-1.2.2.2.js"></script>
<script src="/js-bin/control2.js" type="text/javascript"></script>
<script src="/js-bin/functions.js" type="text/javascript"></script>
	<script type="text/javascript" src="/ebindr/scripts/plugins/datepicker.js"></script>
	<script type="text/javascript" src="/ebindr/scripts/plugins/spellchecker.js"></script>
    <SCRIPT LANGUAGE="JavaScript">
	if("<[current_query]>"!="lite button myalerts") {
		window.parent.ebindr.current.editr="<[current_query]>";
	}
	var currentInsertEditr="<[current_query]>";
	var currentEditr="<[current_query]>";
	var currentEditrExt="<[current_location]>";
	var exportrString="<[exportr_string]>";
	var customextension="";
	var customeditrextension="";
	var issearchdrop=false;

    </SCRIPT>
<script>
function KeyHandle() {
	var myKey=event.altKey*100000+event.ctrlKey*10000+event.shiftKey*1000+event.keyCode;
	if(myKey==27) {
		parent.editboxclose.click();
	}
}
</script>
<script language="JavaScript">
var ebindr=window.parent.ebindr;
var ctrl_t=false;
trapfunction=function(event) {
//	if(!window.ie) return;
//	if( event.target == null ) event.target = false;
//	var e=(event.target?event.target:event.srcElement);
	var e = new Event(event);
	var event = e;
    var keytarget=(event.target?event.target:event.srcElement);
    //alert(e.target.getPrevious('input[type=text]').id);
	//alert(e.target.get('class'));

    //keytarget.id ? keytarget.id : keytarget.set('id', e.target.getPrevious('input[type=text]').id);
    //keytarget.id ? alert('y') : alert('n');
    //alert(keytarget.id);
    
	
	//var altKey = e.alt;
//	event.returnValue=false; 
	window.parent.editrLeft=document.body.scrollLeft;
	window.parent.editrTop=document.body.scrollTop;
 	var myKey=( event.alt ? 1 : 0 )*100000+( event.control ? 1 : 0 )*10000+( event.shift ? 1 : 0 )*1000+event.code;

	if(myKey==10075 || myKey==11075 || myKey==1114 || myKey==10077) {
		if(keytarget.type=='text') { // && (e.name='name' || e.name='Street1' || e.name='FirstName' || e.name='LastName')) {
//			var oRange=keytarget.createTextRange();
//			oRange.expand("textedit");
//			oRange.text=titleCase(oRange.text.toLowerCase());
			keytarget.value=titleCase(keytarget.value.toLowerCase());
			e.preventDefault();
			e.stop();
			
//			window.parent.ebindr.notify(keytarget.type+":"+keytarget.value);
			return false;
		}
	} else if(myKey==10084 && !ctrl_t) {
		var mydate= new Date();
		if(keytarget.type=='text' || keytarget.type=='textarea') {
			$(keytarget).insertAtCursor((mydate.getMonth()+1)+"/"+mydate.getDate()+"/"+mydate.getFullYear());
			ctrl_t=true;window.setTimeout("ctrl_t=false",2000);
			e.preventDefault();
			e.stop();
			return false;
		}
	} else if(myKey==11084 && !ctrl_t) {
		var mydate= new Date();
		if(keytarget.type=='text' || keytarget.type=='textarea') {
			$(keytarget).insertAtCursor((mydate.getMonth()+1)+"/"+mydate.getDate()+"/"+mydate.getFullYear());
			ctrl_t=true;window.setTimeout("ctrl_t=false",2000);
			e.preventDefault();
			e.stop();
			return false;
		}
	} else if(myKey==10084 && ctrl_t) {
		var mydate= new Date();
		if(keytarget.type=='text' || keytarget.type=='textarea') {
			$(keytarget).insertAtCursor(" "+mydate.toLocaleTimeString());
			ctrl_t=false;
			e.preventDefault();
			e.stop();
			return false;
		}
	} else if(myKey==11084 && ctrl_t) {
		var mydate= new Date();
		if(keytarget.type=='text' || keytarget.type=='textarea') {
			$(keytarget).insertAtCursor(" "+mydate.toLocaleTimeString());
			ctrl_t=false;
			e.preventDefault();
			e.stop();
			return false;
		}
	}
	if(myKey==11054) { //six months
		var mydate= new Date();
		if(keytarget.type=='text' || keytarget.type=='textarea') {
			$(keytarget).insertAtCursor((mydate.getMonth()>5?mydate.getMonth()-5:mydate.getMonth()+7)+"/"+mydate.getDate()+"/"+(mydate.getFullYear()+(mydate.getMonth()>5)));
			e.preventDefault();
			e.stop();
			return false;
		}
	}
	if(myKey==10049 || myKey==10050 || myKey==10051) {
		var mydate= new Date();
		if(keytarget.type=='text' || keytarget.type=='textarea') {
			$(keytarget).insertAtCursor((mydate.getMonth()+1)+"/"+mydate.getDate()+"/"+(mydate.getFullYear()+(myKey-10048)));
			e.preventDefault();
			e.stop();
			return false;
		}
	}
	if(myKey==11049 || myKey==11050 || myKey==11051) {
		var mydate= new Date();
		if(keytarget.type=='text' || keytarget.type=='textarea') {
			$(keytarget).insertAtCursor((mydate.getMonth()+1)+"/"+mydate.getDate()+"/"+(mydate.getFullYear()+(myKey-11048)));
			e.preventDefault();
			e.stop();
			return false;
		}
	}
	if(myKey==10083) {
		if(keytarget.type=='text' || keytarget.type=='textarea') {
			$(keytarget).insertAtCursor(window.parent.GetStaff());
			e.preventDefault();
			e.stop();
			return false;
		}
	}
	//if(window.parent.name!="eBINDr") return;
	if('<[prompt]>'=='Yes') return;
	var current_query='<[current_query]>';
	if((event.key==27) && current_query.indexOf("editr")>0) {
		event.returnValue=false;
	}
	//if(event.key==116 || myKey==113 || myKey==114 || myKey==115 || ((event.key==191 || event.key==13) && current_query.indexOf("editr")<1)) { event.returnValue=false; event.key=0; }
	if( current_query.indexOf("findr")>0 ) key_select(event);
	if(event.key!='enter' && event.key!=13) window.parent.ebindr.keyboard.keydown(event,'<[current_query]>');//window.parent.KeyHandler(current_query, myKey);
}
function DblClickHandle() {
	if(window.parent.readonly) return false;
	window.parent.ebindr.current.editr="<[current_query]>";
	if(window.parent.ebindr.current.editr.indexOf("editr")>0) return;
	switch("<[current_query]>") {
		case "e button m": case "lite button bg": return; break;
		case "e button c": window.parent.ebindr.button.go("co"); return; break;
	}
	if( window.parent.ebindr.current.editr.match( '^lite findr' ) ) {
		if ( window.parent.ebindr.current.editr.match( 'Small Claims Case' ) ){
			window.parent.ebindr.button.editr_edit("qvq Small Claims Cases.editr", <[bid]>);
		} 
		else window.parent.ebindr.findr2.choose(); 
	}
	else window.parent.ebindr.button.editr_edit(window.parent.ebindr.current.editr+".editr", <[bid]>);

}
var editrtitle="<[current_query_desc]>";
document.onkeydown=trapfunction;
document.onkeypress=trapfunction;
//document.onkeydown=window.parent.ebindr.keyboard.keydown;
/*window.onerror = function( msg, file, line ) {
	if( msg.length > 0 && window.parent.ebindr.isHurdman() ) {
		window.parent.ebindr.growl( 'JavaScript Error', msg );
		window.parent.ebindr.log( "Script Error: " + msg );
	}

	return true;
}*/
var tmp2="";
window.addEvent( 'load', function() {
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
window.addEvent( 'domready', function(e) {
	if(window.parent.customextension) {
		customextension=window.parent.customextension;
		customeditrextension=window.parent.customeditrextension;
	}

	$$('a').each(function(el) {
		if(el.href.replace( /^(https*:\/\/[^\/]+).*$/g, "$1" )==window.location.href.replace( /^(https*:\/\/[^\/]+).*$/g, "$1" ) && el.href!="" && !el.href.match("[?].*ebindr2") && el.href!="#") {
			el.href=el.href.replace( /[?]/i, "?ebindr2=y&" );
			el.href=el.href.replace( /(reportr=.+noheader)/i, "$1%26ebindr2%3dy" );
		}
		/*if( el.href.contains('/report/merge/') && el.href.contains('ebindr2=y') ) {
			el.addEvent( 'click', function(e) {
				new Event(e).stop();
				e.preventDefault();
				var url = this.get('href');
				window.parent.ebindr.modal.selectoption( 'How would you like to save this file?', '/ebindr/views/google-apps.html', function(val) {
					switch( val ) {
						case "local": window.open(url + '&google=no'); break;
						case "both": window.open(url + '&google=both'); break;
						case "google": window.open(url + '&google=yes'); break;
					}
				});
				return false;
			});
		} else {*/
			var myclick=String(el.get('onclick'));
			if($chk(myclick) && myclick.match('submitform') && !myclick.match("[?].*ebindr2")) {
				el.set('onclick', el.get('onclick').replace( /[?]/i, "?ebindr2=y&" ));
			}
		//}
	});
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
			$$('.scrub').each(function(el) {
				el.addEvent("click", function(e) {
					ebindr.scrub($(this.get('lang')),names);
				});
				ebindr.scrubcheck($(el.get('lang')),names);
			});
			$$('.scrub-one').each(function(el) {
				el.addEvent("click", function(e) {
					var txtobj=$(el.get('lang'));
					var replacechar=txtobj.getSelectedText();
					if(replacechar.match(" $")) txtobj.selectRange(txtobj.getSelectionStart(),txtobj.getSelectionEnd()-1);
					replacechar=txtobj.getSelectedText();
					names=names+(names!=""?",":"")+replacechar;
					txtobj.insertAtCursor(Array(txtobj.getSelectionEnd()-txtobj.getSelectionStart()+1).join("*"));
				});
			});
			$$('.scrub-multi').each(function(el) {
				el.addEvent("click", function(e) {
					var txtobj=$(el.get('lang'));
					var cursorpos=txtobj.getCaretPosition();
					if(txtobj.getSelectedText().match(" $"))txtobj.selectRange(txtobj.getSelectionStart(),txtobj.getSelectionEnd()-1);
					var redactchars=Array(txtobj.getSelectionEnd()-txtobj.getSelectionStart()+1).join("*");
					var replacechar=txtobj.getSelectedText();
					names=names+(names!=""?",":"")+replacechar;
					var regex=new RegExp(replacechar,"g");
					txtobj.set("value", txtobj.get("value").replace(regex,redactchars));
					txtobj.setCaretPosition(cursorpos);
				});
			});
		}
	}).delay(200);
	// add the click to the html
	$$('html')[0].addEvent( 'click', function() {
		// check out what our current url is
		var thisurl = unescape(window.location.href.replace(window.location.hostname,"").replace("http://",""));
		// go through each iframe in the parent to find itself
		window.parent.document.getElements('iframe').each( function(frame) {
			// find the frames location object
    		var thislocation = frame.contentWindow.location;
    		// get it's url to compare
    		var compareurl = unescape(thislocation.href.replace(thislocation.hostname,"").replace("http://",""))
    		// if we have the same url then we're done searching for the iframe
    		if( compareurl == thisurl ) {
    			// make sure it isn't one of the static frames
    			if( !['frame_c','frame_m','frame_g'].contains(frame.id) ) {
    				// get the window element from mochaui
    				var windowEl = window.parent.ebindr.window.parent.Windows.instances.get(frame.id.replace("_iframe",""));
    				// focus on that window

    			// 	if (windowEl == null) {
							// window.parent.ebindr.window.parent.Windows.instances.each( function(subject) {
							// 	console.log(subject.contentEl.id);
							// 	if (subject.contentEl.id.indexOf("qvq-VORP-Cases") > -1) {
							// 		console.log("hmmm");
							// 		editr_run("qvq VORP Cases");
							// 		// ebindr.editr( "qvq VORP Cases.editr", true );
							// 	}
							// });    					
    			// 	}
    				if (windowEl != null)	window.parent.ebindr.window.parent.focusWindow(window.parent.$(windowEl.options.id));
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
		});
	});
});
//alert(window.parent.ebindr.window.parent.focusedWindow.options.id);
</script>
</head>
<body onFocus="" id='body_id' STYLE="background:transparent" ondblclick="DblClickHandle();">
<form name="limit" action="" method="post">
<input type="hidden"  name="which_table" id="which_table" value="" />
		<[content]>
		<[submit]>
</form>
<script>
if("<[current_query]>"!="lite button myalerts" && "<[current_query]>"!="lite button info2") window.focus();
if(document.complaintform) {
	for(var i=0;i<document.complaintform.elements.length;i++){
		if(document.complaintform.elements[i].type!='hidden' && (document.complaintform.elements[i].type!='select-one' || document.body.scrollHeight<=document.body.clientHeight)) {
			document.complaintform.elements[i].focus();
			if((window.parent.ebindr.current.editr.indexOf("button m-.editr")>0 || window.parent.ebindr.current.editr.indexOf("+.editr")>0 || window.parent.ebindr.current.editr.indexOf("mp.editr")>0) && window.parent.ebindr.current.editr.indexOf("Message")<1 && document.complaintform.elements[i].type!='select-one')
				document.complaintform.elements[i].select();
			break;
		}
	}
}
if(document.login) {
	for(i=0;i<document.login.elements.length;i++){
		if(document.login.elements[i].type!='hidden') {
			document.login.elements[i].focus();
			break;
		}
	}
}

function fireClickEvent( control ) {
	if( document.all ) control.fireEvent( "onclick" );
	else {
        	var clickEvent = window.document.createEvent("MouseEvent"); 
        	clickEvent.initEvent("click", false, true); 
        	control.dispatchEvent(clickEvent); 
	}
}

if(window.parent.ebindr.current.editr) {
	if(window.parent.ebindr.current.editr.indexOf("findr")>0 && document.getElementById("1-1-3")) {
		fireClickEvent( document.getElementById("1-1-3") );
//		document.getElementById("1-1-3").fireEvent("onclick");
	}
	if(window.parent.ebindr.current.editr.indexOf("lite button")>-1 && document.getElementById("1-1-2")) {
		fireClickEvent( document.getElementById( "1-1-2" ) );
		//document.getElementById("1-1-2").fireEvent("onclick");
	}
	if(window.parent.ebindr.current.editr == "lite button c" && document.getElementById("2-1-4")) {
		document.getElementById("2-1-4").click(); 
		document.getElementById("2-1-4").children(0).click();
	}
	if(window.parent.ebindr.current.editr.indexOf("editr")<0) window.scrollTo(window.parent.editrLeft, window.parent.editrTop);
}
</script>
<script>
//if(window.parent) window.parent.settimeout('<[current_query]>', <[logout_minus_five]>);
if(issearchdrop) {
  document.complaintform.next.disabled=true;
  document.complaintform.next.value='Please wait...';
}
</script>
</body>
</html>