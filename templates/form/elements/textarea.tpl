<textarea id="<@ $element.field @>" name="<@ $element.field @>"
	class="rapidev_form_element_textarea rapidev_form_element_textarea_<@ $form.id @> rapidev_form_element_<@ $element.field @>" 
			<@ if isset ($element.cols) @>cols="<@ $element.cols @>"<@ /if @>
			<@ if isset ($element.rows) @>rows="<@ $element.rows @>"<@ /if @>
			<@ if isset ($element.wrap) @>wrap="<@ $element.wrap @>"<@ /if @>
			<@ if isset ($element.disabled) @>disabled="disabled"<@ /if @>
			<@ if isset ($element.readonly) @>readonly="readonly"<@ /if @>
			<@ if isset ($element.tabindex) @>tabindex="<@ $element.tabindex @>"<@ /if @>
			><@ $element.value @></textarea>