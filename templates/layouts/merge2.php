<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>eBINDr - <[db]></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" type="text/css" href="/css/reportr_default.css" />
<link rel="stylesheet" type="text/css" href="/ebindr/styles/plugins/datepicker.css" />

<script type="text/javascript" src="/ebindr/scripts/framework/core-1.2.4.js"></script>
<script type="text/javascript" src="/ebindr/scripts/framework/more-1.2.2.2.js"></script>
<script type="text/javascript" src="/ebindr/scripts/plugins/datepicker.js"></script>

<script src="/js-bin/control.js" type="text/javascript"></script>
<script src="/js-bin/functions.js" type="text/javascript"></script>
<script src="/js-bin/date.js" type="text/javascript"></script>
<!-- <script src="/js-bin/moo.js" type="text/javascript"></script> -->
<script>
document.addEvent('domready', function() {
	if("<[temp_timeout]>"!="yes") setTimeout('Continue();', 100); else {submitted=true; setTimeout('checkfilesize();', 1500); }
});
//setTimeout('Continue();', 1000);
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
// window.opener = ''; 
window.open("","_self"); 
windowClose(); 
} 
var submitted=false;
var newlocationtimeout=window.setTimeout("window.location='/report/merge/<[heirarchy]>/?ebindr2=y&WAIT_FOR_TEMP_FILE=<[temp_file_name]>'", 90000);
checkfilesize = function() {
	$('progress').setStyle('display','block');
	$('merge_message').set('html','The document you requested is being generated... please wait.');
	var uri = String(document.location);
	
	$('adoptee').value = "Please wait...";
	$('adoptee').disabled = true;
	document.body.style.cursor = "wait";
	
	if( uri.indexOf(".rtf") < 0 ) {
		$('filesize').set('html','N/A');
	} else {

		try {
			myIFrame.set('src', '/js-bin/checkfilesize.php?filename=<[temp_file_name]>&stamp='+parseInt(new Date().getTime().toString().substring(0, 10)));
//			$('checkfilesize').load();
/*			new Request.HTML({
				url: "http://hurdmantest.hurdman.org/js-bin/checkfilesize.php",
				async:false,
				onSuccess: function( el, el2, html ) {
					if( html == 'done' ) {
						$('filesize').set('html','Your download will now start.');
						if("<[temp_timeout]>"=="yes") window.open('/report/merge/<[heirarchy]>/?WAIT_FOR_TEMP_FILE=<[temp_file_name]>&DOWNLOAD_FILE', '_blank');
						$('merge_message').set('html','Your document has been generated.');
						if(!submitted) return;
						if(window.name=='' || window.name=='new' || window.name=='_blank') {
							// window.opener = ''; 
							setTimeout('window.close();',15000); 
						} else history.go(-1);
					} else {
						$('filesize').set('html', html );
						setTimeout('checkfilesize();', 500);
					}
				}
			}).post( {
				'filename': '<[temp_file_name]>', 
				'stamp': parseInt(new Date().getTime().toString().substring(0, 10))
			} );*/
		} catch(error) {
//alert(error);
		}
	
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
//	checkfilesize();
}
var myIFrame;
window.addEvent( 'domready', function(e) {
myIFrame = new IFrame({
    styles: {
        width: 1,
        height: 1,
        border: 'none'
    },
 
    events: {
 
        load: function() {
			if(this.get('src')===null) return;
			var html=this.contentDocument.body.innerHTML;
			if( html.match('done') ) {
				$('filesize').set('html','Your download will now start.');
				if("<[temp_timeout]>"=="yes") {
					window.location='/report/merge/<[heirarchy]>/?ebindr2=y&WAIT_FOR_TEMP_FILE=<[temp_file_name]>&DOWNLOAD_FILE';
					clearTimeout(newlocationtimeout);
				}
				$('merge_message').set('html','Your document has been generated.');
				if(!submitted) return;
				if(window.name=='' || window.name=='new' || window.name=='_blank') {
					// window.opener = ''; 
					setTimeout('window.close();',15000); 
				} else history.go(-1);
			} else {
				$('filesize').set('html', html );
				setTimeout('checkfilesize();', 1500);
			}
		}
 
    }
 
}).inject(document.body);

	var tmp = new DatePicker( '.date', {
		pickerClass: 'datepicker_dashboard',
		format: 'm/d/Y',
		allowEmpty: true,
		inputOutputFormat: 'm/d/Y',
		toggleElements: '.picker'
	});
});
</script>
<[custom_header]>

    <div class="filename"><[heirarchy]></div>
    <div class="content">
        <div id="merge_message"><[merge_message]></div>
        <div id="progress">File Progress: <span id="filesize">--</span></div>
        <form name="limit" action="" method="post" onSubmit="setTimeout('checkfilesize();', 1000); submitted=true; if(window.parent.name=='Calendar') { window.parent.editbox.style.display='none'; window.parent.editboxclose.style.display='none'; }">
        <input type="hidden"  name="mergesubmit" id="mergesubmit" value="YES" />
        <input type="hidden"  name="which_table" id="which_table" value="" />
        <input type="hidden" name="temp_file_name" id="temp_file_name" value="<[temp_file_name]>" />
                <[content]>
                <[submit]>
        <br />
        </form>
    </div>

<script type="text/javascript" src="/ebindr/scripts/reports.js"></script>
</body>
</html>
