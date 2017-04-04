<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>eBINDr - <[db]></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" type="text/css" href="/css/reportr_default.css" />
<script src="/js-bin/control.js" type="text/javascript"></script>
<script src="/js-bin/date.js" type="text/javascript"></script>
<script src="/js-bin/functions.js" type="text/javascript"></script>
<script src="/js-bin/moo.js"></script>
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
<style type="text/css">
body {
	background-color: #cccccc;
	margin: 0px;
}
form {
	margin: 0px;
	padding: 0px;
}
table {
	width: auto;
}
div.filename {
	background-color: #C5DEBE;
	color: #006600;
	padding: 15px;
	border-bottom: 1px solid #999999;
	font-size: 20px;
}
div.content {
	padding-left: 15px;
	border-bottom: 1px solid #999999;
	background-color: #ffffff;
}
div.content div {
	padding-top: 20px;
	font-size: 18px;
}
div#merge_message {
	color: #999999;
}
span#filesize {
	color: #0099CC;
	font-weight: bold;
}
div#progress {
	display: none;
	padding-bottom: 15px;
}
</style>
<body onkeydown="key_select();" onFocus="">
<script language="JavaScript">
var windowClose = window.close; 
// Re-implement window.open 
window.close = function () 
{ 
window.opener = ''; 
window.open("","_self"); 
windowClose(); 
} 
var submitted=false;
window.setTimeout("window.location='/report/merge/<[heirarchy]>/?WAIT_FOR_TEMP_FILE=<[temp_file_name]>'", 240000);
checkfilesize = function() {
	$('progress').setStyle('display','block');
	$('merge_message').setHTML('The document you requested is being generated... please wait.');
	var uri = String(document.location);
	
	$('adoptee').value = "Please wait...";
	$('adoptee').disabled = true;
	document.body.style.cursor = "wait";
	
	if( uri.indexOf(".rtf") < 0 ) {
		$('filesize').setHTML('N/A');
	} else {
	
		new Ajax("/js-bin/checkfilesize.php", {
			method: 'get',
			onComplete: function() {
				if( this.response['text'] == 'done' ) {
					$('filesize').setHTML('Your download will now start.');
					if("<[temp_timeout]>"=="yes") window.open('/report/merge/<[heirarchy]>/?WAIT_FOR_TEMP_FILE=<[temp_file_name]>&DOWNLOAD_FILE', '_blank');
					$('merge_message').setHTML('Your document has been generated.');
					if(!submitted) return;
					if(window.name=='' || window.name=='new') {
						window.opener = ''; 
						setTimeout('window.close();',15000); 
					} else history.go(-1);
				} else {
					$('filesize').setHTML( this.response['text'] );
					setTimeout('checkfilesize();', 500);
				}
			},
			data: {
				'filename': '<[temp_file_name]>',
				'stamp': parseInt(new Date().getTime().toString().substring(0, 10))
			}
		}).request();
	
	}

}
function Disappear() {
	if(!submitted) return;
	if(window.name=='' || window.name=='new') window.setTimeout("window.close()", 45000); else history.go(-1);
}
function Continue() {
	if(limit.elements.length>6 || submitted) return;
	document.body.style.cursor="wait";
	document.getElementById("adoptee").value="Please wait...";
	document.getElementById("adoptee").click();
	document.getElementById("adoptee").disabled=true;
	checkfilesize();
}
</script>
<[custom_header]>

    <div class="filename"><[heirarchy]></div>
    <div class="content">
        <div id="merge_message"><[merge_message]></div>
        <div id="progress">File Progress: <span id="filesize">--</span></div>
        <form name="limit" action="" method="post" onSubmit="checkfilesize(); submitted=true; if(window.parent.name=='Calendar') { window.parent.editbox.style.display='none'; window.parent.editboxclose.style.display='none'; }">
        <input type="hidden"  name="mergesubmit" id="mergesubmit" value="YES" />
        <input type="hidden"  name="which_table" id="which_table" value="" />
        <input type="hidden" name="temp_file_name" id="temp_file_name" value="<[temp_file_name]>" />
                <[content]>
                <[submit]>
        <br />
        </form>
    </div>

</body>
<script>
if("<[temp_timeout]>"!="yes") setTimeout('Continue();', 100); else {submitted=true; checkfilesize(); }
//setTimeout('Continue();', 1000);
 </script>
</html>