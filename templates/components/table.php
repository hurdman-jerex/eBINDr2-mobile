<[selection_message]>
		<a id="top<[firsttid]>" name="top<[firsttid]>"></a>
		<!--<table cellspacing="0" cellpadding="1">-->
	<div class="container-fluid">
		<div class="row">
            <div class="col-md-12">
                <input type="hidden" name="limit<[firsttid]>" value="<[limit]>" /><input type="hidden" name="limitback<[firsttid]>" value="<[limitback]>" /><[options]><[data]>
            </div>
        </div>
	</div>
			<!--<tr>
				<td colspan="300"></td>
			</tr>
		</table>-->
		<a class="toplink" style="display: none;" onclick="window.scrollTo( 0, $('top<[firsttid]>').getCoordinates().top ); return false;" href="#top<[firsttid]>">Back to Top</a>
<script>
if( document.location.toString().test(/\/report\/menu/gi) || document.location.toString().test(/\/report\/lite%20findr/gi) ) {
	if( !$('prompt_value_1') ) {
		$$('a.toplink').setStyle( 'display', '' );
	}
}
</script>
				<[exceptionlist]>
