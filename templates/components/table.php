<[selection_message]>
		<a id="top<[firsttid]>" name="top<[firsttid]>"></a>
		<table class="table table-striped table-bordered table-condensed" cellspacing="0" cellpadding="1">
			<tr>
				<td colspan="300"><input type="hidden" name="limit<[firsttid]>" value="<[limit]>" /><input type="hidden" name="limitback<[firsttid]>" value="<[limitback]>" /><[options]><[data]></td>
			</tr>
		</table>
		<a class="toplink" style="display: none;" onclick="window.scrollTo( 0, $('top<[firsttid]>').getCoordinates().top ); return false;" href="#top<[firsttid]>">Back to Top</a>
<script>
if( document.location.toString().test(/\/report\/menu/gi) || document.location.toString().test(/\/report\/lite%20findr/gi) ) {
	if( !$('prompt_value_1') ) {
		$$('a.toplink').setStyle( 'display', '' );
	}
}
</script>
				<[exceptionlist]>
