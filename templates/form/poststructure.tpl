<script type="text/javascript">
	
	$(document).ready(
		function() {
			<@ if isset($form.submitonchange) @>
				$('#rapidev_form_structure_<@ $form.id @>').change(
						function(){
							$('#<@ $form.id @>').val('submit');
							$('#rapidev_form').submit();
							document.forms[0].submit();
						}
					);
			<@ /if @>
		}
	);

</script>