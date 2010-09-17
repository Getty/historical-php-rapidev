<@ if $element.name @>
	<div class="form_row" style="text-align:center">
		<div class="rapidev_form_name">
			<@ $element.name @>
		</div>
	</div>
<@ /if @>
<@ if $element.count > 0 @>
	<@ foreach from=$element.counter item=count_no @>
		<@ form name=$element.multisubformnamebase|cat:"_"|cat:$count_no @>
	<@ /foreach @>
<@ /if @>
<div class="form_row" id="<@ $element.field @>_newform_row" style="text-align:center">
	<div class="rapidev_form_name">
		<input type="hidden" id="<@ $element.field @>_count" name="<@ $element.field @>_count" value="<@ $element.count @>">
		<div id="<@ $element.field @>_newform">
			<@ $element.textnew @>
		</div>

		<script language="javascript">

		$('#<@ $element.field @>_newform').bind('click',function(){

			var old_count = parseInt($('#<@ $element.field @>_count').val());
			var new_count = old_count+1;

			$('#<@ $element.field @>_count').val(new_count);

			var new_row = $('<tr><td colspan="2"><img src="/img/ajax-loader.gif" /></td></tr>');

			$('#<@ $element.field @>_newform_row').before(new_row);

			$.ajax({
				type: "POST",
				url: "<@ $PHP_SELF @>?module=form&form=<@ $form.id @>&multiform=<@ $element.id @>&multiform_count="+new_count,
				success: function(data){
					new_row.replaceWith(data);
				}
			});

		}); 

		</script>
	</div>
</div>

