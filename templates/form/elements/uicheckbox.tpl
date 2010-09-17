<input type="checkbox" class="checkbox" value="1" name="<@ $element.field @>" id="<@ $element.field @>"
	<@ if (isset($option_data.disabled) && $option_data.disabled) || (isset($element.readonly) && $element.readonly) @>disabled="disabled"<@ /if @>
	<@ if isset($element.value) && $element.value @>checked="checked"<@ /if @> />
<@ js @>

     $('#<@ $element.field @>').checkbox();

<@ /js @>

