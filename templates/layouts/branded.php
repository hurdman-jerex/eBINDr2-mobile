<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<[MYSQL_ERRORS]>
<title><[db]> - <[application_name]></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="/css/reportr_default.css" />
<link rel="stylesheet" type="text/css" href="/css/council_external.css" />
<script src="/js-bin/control.js" type="text/javascript"></script>
<script src="/js-bin/date.js" type="text/javascript"></script>
<script src="/js-bin/functions.js" type="text/javascript"></script>
<script>
function KeyHandle() {
//	event.returnValue=false; 
	window.parent.editrLeft=document.body.scrollLeft;
	window.parent.editrTop=document.body.scrollTop;
	var myKey=event.altKey*100000+event.ctrlKey*10000+event.shiftKey*1000+event.keyCode;
	if(myKey==10075) {
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

</head>
<body onkeydown="key_select();KeyHandle();">
<img class="logo" src="/css/brand_logo.gif" alt="BBB" />
<div class="boxit">
	<div class="top"><img class="right" src="/css/brand_corner_top_right.gif" alt="" /><img class="left" src="/css/brand_corner_top_left.gif" alt="" /></div>
	<div class="box_content">
	<div id="form">
<[custom_header]>

<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
	<tr>
		<td class="menu">
		<table>
			<tr>
				<td id="menu">
<a href="javascript:Print('<[query_run]>')">Print</a>
				</td>
				<td id="menu">
				<map name="exportmap">
				<area alt="Export to Excel" title="Export to Excel" shape="rect" coords="2,2,17,17" href="#" onClick="Export()">
				<area alt="Export to Word" title="Export to Word" shape="rect" coords="29,2,44,17" href="#" onClick="ExportWord()">
				<area alt="Export Text File" title="Export Text File" shape="rect" coords="56,2,71,17" href="#" onClick="ExportText()">
				</map>
<img border="0" src="/css/export.gif" usemap="#exportmap">
				<td id="menu"><a href="" onClick="document.location.reload();">Refresh</a></td>
				<td id="menu"><a target="_blank" href="/<[APPLICATION_FILENAME]>/<[about_link]>/?noheader&mymergecode=<[current_query]>"><[about_link]></a></td>
				<td id="menu"><a href="/logout.php">Logout</a></td>
			</tr>
		</table>
		
		</td>
	</tr>
	<tr>
		<td style="background: #666666; font-size: 2px;">&nbsp;</td>
	</tr>
	<tr>
		<td style="background: #C5DEBE; height: 35px;" width="50%" valign="bottom">
		<[heirarchy]>
		</td>
	</tr>
</table>

<form name="limit" action="" method="post">
		<[content]>
		<[postinputs]>
<input type="hidden"  name="which_table" id="which_table" value="" />
		<[submit]>
<br />
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
	</div>
	</div>
	<div class="bottom"><img class="right" src="/css/brand_corner_bottom_right.gif" alt="" /><img class="left" src="/css/brand_corner_bottom_left.gif" alt="" /><img src="/css/brand_white_space_10x10.gif" alt="" /></div>
</div>
</body>
</html>