<[instructions]>
<tr>
	<td style="color: #000000; font-size: 9pt; padding-bottom: 10px; padding-right: 10px; border-right: 1px dotted #000000;"><[name]>:</td>
	<td style="color: #000000; padding-bottom: 10px; padding-left: 10px;"><input value="<[value]>" id="<[id]>" title="<[title]>" name="<[name]>" type="hidden"><input onblur="dates(id,1)" value="<[date_value]>" id="view_<[id]>" title="<[title]>" name="view_<[name]>" type="text"><img src="/js-bin/calendar.gif" onclick='var newDateVar=window.showModalDialog("/js-bin/calendar.htm?timeenabled=disabled<[country_var]>", document.getElementById("<[id]>"), "help:no;status:no;resizable:no;dialogwidth:274px;dialogheight:282px;center:yes;scroll:no;"); if(newDateVar) { document.getElementById("view_<[id]>").value=newDateVar; dates("view_<[id]>",0); }'>
	</td>
	<td style="color: #000000; padding-bottom: 10px; padding-left: 10px;"><[default_options]></td>
</tr>