<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><[db]> - <[application_name]></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" type="text/css" href="/css/reportr_default.css" />
</head>
<body onkeydown="key_select();" onFocus="Disappear();">
<script language="JavaScript">
var submitted=false;
function Disappear() {
	if(!submitted) return;
	if(window.name=='' || window.name=='new') setTimeout("window.close();", 10000); else history.go(-1);
}
function Continue() {
	if(limit.elements.length>5 || submitted) return;
	document.body.style.cursor="wait";
	document.getElementById("adoptee").value="Please wait...";
	document.getElementById("adoptee").click();
	document.getElementById("adoptee").disabled=true;
}
</script>
<[custom_header]>

<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
	<tr>
		<td style="background: #666666; font-size: 2px;">&nbsp;</td>
	</tr>
	<tr>
		<td style="background: #C5DEBE; height: 35px;" width="50%" valign="middle">
		<[heirarchy]>
		</td>
	</tr>
</table>
<[merge_message]>
<form name="limit" action="" method="post" onSubmit="submitted=true;">
<input type="hidden"  name="mergesubmit" id="mergesubmit" value="YES" />
<input type="hidden"  name="which_table" id="which_table" value="" />
		<[content]>
		<[submit]>
<br />
</form>

</body>
<script>
setTimeout('Continue();', 100);
//setTimeout('Continue();', 1000);
 </script>
</html>