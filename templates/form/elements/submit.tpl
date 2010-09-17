<input type="submit" id="<@ $element.field @>" name="<@ $element.field @>" value="<@ $element.submitvalue @>" />
<script type="text/javascript">

	$(document).ready(function() {
		
		<@ if isset($element.submitform) @>
			<@ assign var=submitform value=$element.submitform @>
		<@ else @>
			<@ assign var=submitform value=$form.id @>		
		<@ /if @>

		$('#rapidev_form').click(function(e){
			if (e.explicitOriginalTarget == $('#<@ $element.field @>')[0]) {
				$('#<@ $submitform @>').val('submit');
			}
		});

		<@ if isset($element.updatefield) && isset($element.updatevalue) @>
			$('#rapidev_form').click(function(e){
				if (e.explicitOriginalTarget == $('#<@ $element.field @>')[0]) {
					$('#<@ $element.updatefield @>').val('<@ $element.updatevalue|escape:"javascript" @>');
				}
			});
		<@ /if @>
		
	});
	
</script>




