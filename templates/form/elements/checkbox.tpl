<input type="checkbox" value="1" name="<@ $element.field @>" 
	<@ if (isset($option_data.disabled) && $option_data.disabled) || (isset($element.readonly) && $element.readonly) @>disabled="disabled"<@ /if @>
	<@ if isset($element.value) && $element.value @>checked="checked"<@ /if @> 
	<@ if isset($element.class) @>class="<@ $element.class @>"<@ /if @> />