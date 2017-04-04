<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><[application_name]> [<[version]>]</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" type="text/css" href="/css/reportr_print2.css" />
<script src="/js-bin/control.js" type="text/javascript"></script>
<script src="/js-bin/date.js" type="text/javascript"></script>
<script type="text/javascript" src="/ebindr/scripts/framework/core-1.2.4.js"></script>
<script type="text/javascript" src="/ebindr/scripts/framework/more-1.2.2.2.js"></script>
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
		window.setTimeout("window.print()", 1000);
	} else window.print();
}
</script>
</head>
<body onload="initchart();">
<form name="limit" action="" method="post">
<input type="hidden"  name="which_table" id="which_table" value="">
<br />
		<[content]>
		<[submit]>
<br />
</form>