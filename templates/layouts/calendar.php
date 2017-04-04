<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><[application_name]></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" type="text/css" href="/css/reportr_calendar.css" />
<script src="/js-bin/control.js" type="text/javascript"></script>
<script src="/js-bin/functions.js" type="text/javascript"></script>
<script>
function KeyHandle() {
	var myKey=event.altKey*100000+event.ctrlKey*10000+event.shiftKey*1000+event.keyCode;
	if(myKey==27) {
		parent.editboxclose.click();
	}
}
</script>
</head>
<body STYLE="background:transparent" onkeydown="KeyHandle();">
<form name="limit" action="" method="post">
<input type="hidden"  name="which_table" id="which_table" value="" />
		<[content]>
		<[submit]>
</form>
<script>
window.focus();
if(document.complaintform) {
	for(var i=0;i<document.complaintform.elements.length;i++){
		if(document.complaintform.elements[i].type!='hidden') {
			document.complaintform.elements[i].focus();
			break;
		}
	}
}
</script>
</body>
</html>