<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<[MYSQL_ERRORS]>
<title><[application_name]></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!--<link rel="stylesheet" type="text/css" href="/css/reportr_noheader.css" />-->
<link href="/m/assets/css/report.css" rel="stylesheet">
<link href="/m/assets/css/bootstrap.css" rel="stylesheet">
<link href="/m/assets/css/bootstrap-responsive.css" rel="stylesheet">
    <!-- BEGINNING OF THE SCRIPT -->
    <STYLE TYPE="text/css">
    <!-- 
    #cache {
    position:absolute; left=10; top:10px; z-index:10; visibility:hidden;
    }
    -->
    </STYLE>
    <!--
    Lines above are creating a layer which show a message
    displaying the 'PLEASE WAIT ... ' message
    -->
	<script type="text/javascript" src="/js-bin/moo.js"></script>
    <SCRIPT LANGUAGE="JavaScript">
	if( !e ) var e = window.event;
	if("<[current_query]>"!="lite button myalerts") {
		window.parent.currentInsertEditr="<[current_query]>";
		window.parent.currentEditr="<[current_query]>";
		window.parent.currentEditrExt="<[current_location]>";
	}
	var currentInsertEditr="<[current_query]>";
	var currentEditr="<[current_query]>";
	var currentEditrExt="<[current_location]>";
	var issearchdrop=false;
	var cache;
    ver = navigator.appVersion.substring(0,1)
    if (ver >= 4)
    	{
    	document.write('<DIV ID="cache"><TABLE WIDTH=200 align="left" BGCOLOR=#000000 BORDER=0 CELLPADDING=2 CELLSPACING=0><TR><TD ALIGN=left VALIGN=middle>		<TABLE WIDTH=100% BGCOLOR=#FFFFFF BORDER=0 CELLPADDING=0 CELLSPACING=0><TR>		<TD ALIGN=center VALIGN=middle><FONT FACE="Arial, Verdana" SIZE=4><B><BR>Loading...<BR><BR></B></FONT></TD> </TR></TABLE></TD> </TR></TABLE></DIV>');
    	var navi = (navigator.appName == "Netscape" && parseInt(navigator.appVersion) >= 4);
    	var HIDDEN = (navi) ? 'hide' : 'hidden';
    	var VISIBLE = (navi) ? 'show' : 'visible';
    	//cache = (navi) ? document.cache : document.all.cache.style;
    	cache = document.getElementById( 'cache' );
	//if( screen ) largeur = screen.width;
	 largeur = 150;
	//largeur = window.getWidth(); //screen.width;
    	cache.left = Math.round(0);
    	cache.visibility = VISIBLE;
    	}
    function cacheOff()
    	{
    	if (ver >= 4)
    		{
    		cache.visibility = HIDDEN;
    		}
    	}
    </SCRIPT>
<script src="/js-bin/control.js" type="text/javascript"></script>
<!-- script src="/js-bin/date.js" type="text/javascript"></script -->
<script src="/js-bin/functions.js" type="text/javascript"></script>
<script language='VBScript'>
Function dates(id, doprompt)
	setLocale("en-us")
	text_val = document.getElementById(id).value
	if len(text_val) > 0 then
		if isdate(text_val) = false then
			x = msgbox("Please enter a valid date.",vbOkCancel)
			with document.getElementById(id)
				if x = vbOk then
					.focus
					.select
				Else
					.value = ""
					.focus
					.select
				End if
			end with
		Else
			day_date = day(text_val)
			if doprompt and day_date < 12 and instr(text_val,monthname(month(text_val),true))<1 and day_date<>month(text_val) then
				d = msgbox("Did you want the date: " &day_date & "-" & monthname(month(text_val),true) & "-" & year(text_val),vbYesNoCancel)
				if d = vbyes then
					formated_date = day_date & "-" & monthname(month(text_val),true) & "-" & year(text_val)
					formatted_date2 = year(text_val) & "-" & string(2-len(month(text_val)),"0") & month(text_val) & "-" & string(2-len(day(text_val)),"0") & day(text_val)
				Elseif d=vbcancel then
					document.getElementById(id).focus
					exit function
				else
					formated_date = month(text_val) & "-" & monthname(day_date,true) & "-" & year(text_val)
					formatted_date2 = year(text_val) & "-" & string(2-len(day_date),"0") & day_date & "-" & string(2-len(month(text_val)),"0") & month(text_val)
				End if
			Else
				formated_date = day_date & "-" & monthname(month(text_val),true) & "-" & year(text_val)
				formatted_date2 = year(text_val) & "-" & string(2-len(month(text_val)),"0") & month(text_val) & "-" & string(2-len(day(text_val)),"0") & day(text_val)
			End if
			document.getElementById(id).value = formated_date
			document.getElementById(replace(id,"view_","")).value=formatted_date2
		End if
	else
		document.getElementById(replace(id,"view_","")).value=""
	End if
end function
</script>
<script language="JavaScript">
var lastlocation=get_cookie("lastUrl");
var ctrl_t=false;
var ctrl_last_name="";
set_cookie("lastUrl", window.location.href);
trapfunction=function(event) {

	/*	eBindr1
	 *	Findr Feature : open company on first row
	 *	By: Alan
	 */
	if(!window.event) {
		findrKey = event.keyCode;
	} else {
		findrKey = window.event.altKey*100000+window.event.ctrlKey*10000+window.event.shiftKey*1000+window.event.keyCode;
	}
	var findrTarget = document.getElementById('2-1-3');
	if ( findrKey == 13 && findrTarget != null ) {		
		var fnBid = findrTarget.getProperty('targetBid');
		window.parent.setbid(fnBid);
		window.parent.dopage("records");
	}
	// end Findr Feature

	if(!window.ie) return;
	if(window.event.srcElement.name!=ctrl_last_name) ctrl_t=false;
	ctrl_last_name=window.event.srcElement.name;
//	event.returnValue=false; 
	window.parent.editrLeft=document.body.scrollLeft;
	window.parent.editrTop=document.body.scrollTop;
	var myKey=window.event.altKey*100000+window.event.ctrlKey*10000+window.event.shiftKey*1000+window.event.keyCode;
	if(myKey==10075 || myKey==11075 || myKey==1114 || myKey==10077) {
		if(window.event.srcElement.type=='text') { // && (window.event.srcElement.name='name' || window.event.srcElement.name='Street1' || window.event.srcElement.name='FirstName' || window.event.srcElement.name='LastName')) {
			var oRange=window.event.srcElement.createTextRange();
			oRange.expand("textedit");
			oRange.text=titleCase(oRange.text.toLowerCase());
			return false;
		}
	} else if(myKey==10084 && !ctrl_t) {
		var mydate= new Date();
		if(window.event.srcElement.type=='text' || window.event.srcElement.type=='textarea') {
			window.event.srcElement.selection=document.selection.createRange();
			window.event.srcElement.selection.text=(mydate.getMonth()+1)+"/"+mydate.getDate()+"/"+mydate.getYear();
			ctrl_t=true;window.setTimeout("ctrl_t=false",2000);
			return false;
		}
	} else if(myKey==11084 && !ctrl_t) {
		var mydate= new Date();
		if(window.event.srcElement.type=='text' || window.event.srcElement.type=='textarea') {
			window.event.srcElement.selection=document.selection.createRange();
			window.event.srcElement.selection.text=(mydate.getMonth()+1)+"/"+mydate.getDate()+"/"+mydate.getYear();
			ctrl_t=true;window.setTimeout("ctrl_t=false",2000);
			return false;
		}
	} else if(myKey==10084 && ctrl_t) {
		var mydate= new Date();
		if(window.event.srcElement.type=='text' || window.event.srcElement.type=='textarea') {
			window.event.srcElement.selection=document.selection.createRange();
			window.event.srcElement.selection.text=" "+mydate.toLocaleTimeString();
			ctrl_t=false;
			return false;
		}
	} else if(myKey==11084 && ctrl_t) {
		var mydate= new Date();
		if(window.event.srcElement.type=='text' || window.event.srcElement.type=='textarea') {
			window.event.srcElement.selection=document.selection.createRange();
			window.event.srcElement.selection.text=" "+mydate.toLocaleTimeString();
			ctrl_t=false;
			return false;
		}
	}
	if(myKey==11054) { //six months
		var mydate= new Date();
		if(window.event.srcElement.type=='text' || window.event.srcElement.type=='textarea') {
			window.event.srcElement.selection=document.selection.createRange();
			window.event.srcElement.selection.text=(mydate.getMonth()>5?mydate.getMonth()-5:mydate.getMonth()+7)+"/"+mydate.getDate()+"/"+(mydate.getYear()+(mydate.getMonth()>5));
			return false;
		}
	}
	if(myKey==10049 || myKey==10050 || myKey==10051) {
		var mydate= new Date();
		if(window.event.srcElement.type=='text' || window.event.srcElement.type=='textarea') {
			window.event.srcElement.selection=document.selection.createRange();
			window.event.srcElement.selection.text=(mydate.getMonth()+1)+"/"+mydate.getDate()+"/"+(mydate.getYear()+(myKey-10048));
			return false;
		}
	}
	if(myKey==11049 || myKey==11050 || myKey==11051) {
		var mydate= new Date();
		if(window.event.srcElement.type=='text' || window.event.srcElement.type=='textarea') {
			window.event.srcElement.selection=document.selection.createRange();
			window.event.srcElement.selection.text=(mydate.getMonth()+1)+"/"+mydate.getDate()+"/"+(mydate.getYear()+(myKey-11048));
			return false;
		}
	}
	if(myKey==10083) {
		if(window.event.srcElement.type=='text' || window.event.srcElement.type=='textarea') {
			window.event.srcElement.selection=document.selection.createRange();
			window.event.srcElement.selection.text=window.parent.GetStaff();
			return false;
		}
	}
	if(window.parent.name!="eBINDr") return;
	if('<[prompt]>'=='Yes') return;
	var current_query='<[current_query]>';
	if((window.event.keyCode==27) && current_query.indexOf("editr")>0) window.event.returnValue=false;
	if(window.event.keyCode==116 || myKey==113 || myKey==114 || myKey==115 || ((window.event.keyCode==191 || window.event.keyCode==13) && current_query.indexOf("editr")<1)) { window.event.returnValue=false; window.event.keyCode=0; }
	key_select();
	if(!window.parent.readonly || myKey!=13) window.parent.KeyHandler(current_query, myKey);
}
function DblClickHandle() {
    console.log( 'dblClick Here' );
	if(window.parent.currentEditr.indexOf("editr")>0 && window.parent.document.getElementById("editr").style.display=="block") return;
	window.parent.editrLeft=document.body.scrollLeft;
	window.parent.editrTop=document.body.scrollTop;
	if(window.parent.name!="eBINDr") {
		status = "Not in eBINDr";
		return;
	}
	if(!window.parent.readonly) window.parent.DblClickHandler('<[current_query]>');
}
if("<[current_query]>"!="lite button myalerts" && window.parent.document.getElementById("editrdesc")) window.parent.document.getElementById("editrdesc").innerHTML="<[current_query_desc]>";
document.onkeydown=trapfunction;
</script>
</head>
<body id='body_id' STYLE="background:transparent" ondblclick="DblClickHandle();" onLoad="cacheOff(); ">
<form name="limit" action="" method="post">
		<[postinputs]>
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
			if((window.parent.currentEditr.indexOf("button m-.editr")>0 || window.parent.currentEditr.indexOf("+.editr")>0 || window.parent.currentEditr.indexOf("mp.editr")>0) && window.parent.currentEditr.indexOf("Message")<1 && document.complaintform.elements[i].type!='select-one')
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


if(window.parent.currentEditr) {
	if(window.parent.currentEditr.indexOf("findr")>0 && document.getElementById("1-1-3")) {
		fireClickEvent( document.getElementById("1-1-3") );
//		document.getElementById("1-1-3").fireEvent("onclick");
	}
	if(window.parent.currentEditr.indexOf("lite button")>-1 && document.getElementById("1-1-2")) {
		fireClickEvent( document.getElementById( "1-1-2" ) );
		//document.getElementById("1-1-2").fireEvent("onclick");
	}
	if(window.parent.currentEditr == "lite button c" && document.getElementById("2-1-4")) {
		document.getElementById("2-1-4").click(); 
		document.getElementById("2-1-4").children(0).click();
	}
	if(window.parent.currentEditr.indexOf("editr")<0) window.scrollTo(window.parent.editrLeft, window.parent.editrTop);
}
cacheOff();
</script>
<script>
//if(window.parent) window.parent.settimeout('<[current_query]>', <[logout_minus_five]>);
if(issearchdrop) {
  document.complaintform.next.disabled=true;
  document.complaintform.next.value='Please wait...';
}
</script>
<[restriction_link_noheader]><[iptracking]>
</body>
</html>
