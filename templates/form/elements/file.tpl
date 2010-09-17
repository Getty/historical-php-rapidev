<input type="file" id="<@ $element.field @>" name="<@ $element.field @><@ if $element.type == 'multifile' @>[]<@ /if @>"
	size="<@ if isset ($element.size) @><@ $element.size @><@ /if @>"
	class="rapidev_form_element_file rapidev_form_element_file_<@ $form.id @> rapidev_form_element_<@ $element.field @> <@ $element.class @> <@ $class @>" 
	<@ if isset ($element.disabled) @>disabled="disabled"<@ /if @>
	<@ if isset ($element.readonly) && ($element.readonly == "true") @>readonly="readonly"<@ /if @> />
