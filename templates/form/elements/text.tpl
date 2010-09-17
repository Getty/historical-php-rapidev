<input type="text" id="<@ $element.field @>" name="<@ $element.field @>" value="<@ $element.value|htmlspecialchars @>" 
	size="<@ if isset ($element.size) @><@ $element.size @><@ /if @>"
	class="rapidev_form_element_text rapidev_form_element_text_<@ $form.id @> rapidev_form_element_<@ $element.field @>" 
	<@ if isset ($element.maxlength) @>maxlength="<@ $element.maxlength @>"<@ /if @>
	<@ if isset ($element.tabindex) @>tabindex="<@ $element.tabindex @>"<@ /if @>
	<@ if isset ($element.disabled) @>disabled="disabled"<@ /if @>
	<@ if isset ($element.readonly) && ($element.readonly == "true") @>readonly="readonly"<@ /if @> />
