<div class="rapidev_form_element_radio rapidev_form_element_radio_<@ $form.id @> rapidev_form_element_<@ $element.field @>">
	<@ foreach from=$element.data item=data @>
		<div>
			<input type="radio" name="<@ $element.field @>" id="<@ $element.field @><@ $data.id @>" 
						value="<@ $data.id @>"
						<@ if (isset($data.disabled) && $data.disabled) || (isset($element.readonly) && $element.readonly) @>disabled="disabled"<@ /if @>
						<@ if $data.id == $element.value @>checked="checked"<@ /if @>
						/><label for="<@ $element.field @><@ $data.id @>">&nbsp;&nbsp;<@ $data.name @></label>
		</div>
	<@ /foreach @>
</div>