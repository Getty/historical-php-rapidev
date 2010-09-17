<@ if isset($element.name) @>
	<div class="rapidev_form_name rapidev_form_name_<@ $form.id @>">
		<label for="<@ $element.field @>">
			<@ $element.name @><@ include file="form/element/mandatoryfield.tpl" @>
		</label>
	</div>
<@ /if @>
