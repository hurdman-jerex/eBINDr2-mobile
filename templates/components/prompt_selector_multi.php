<select size="<[selectsize]>" multiple id="<[name]>multiselect" onchange="document.getElementById('<[id]>').value = getMultiSelect(this)">
	<[defaults]>
</select>
<script type="text/javascript">
window.setTimeout("document.getElementById('<[id]>').value = getMultiSelect(document.getElementById('<[name]>multiselect'));", 200);
</script>