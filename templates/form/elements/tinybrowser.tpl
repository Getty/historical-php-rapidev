<input type="text" id="<@ $element.field @>" name="<@ $element.field @>" value="<@ $element.value|htmlspecialchars @>" 
	size="<@ if isset ($element.size) @><@ $element.size @><@ /if @>"
	class="rapidev_form_element_tinybrowser rapidev_form_element_text_<@ $form.id @> rapidev_form_element_<@ $element.field @>" 
	<@ if isset ($element.maxlength) @>maxlength="<@ $element.maxlength @>"<@ /if @>
	<@ if isset ($element.tabindex) @>tabindex="<@ $element.tabindex @>"<@ /if @>
	<@ if isset ($element.disabled) @>disabled="disabled"<@ /if @>
	<@ if isset ($element.readonly) && ($element.readonly == "true") @>readonly="readonly"<@ /if @> />
<span id="<@ $element.field @>_click" class="button">
	<u>...</u>
</span>
<div id="<@ $element.field @>_image">
	<@ if $element.value @>
		<img src="<@ $PHP_SELF @>?file=<@ $element.value @>?100x100" />
	<@ /if @>
</div>
<@ js @>

	$('#<@ $element.field @>_click').click(function(){
		tinyBrowserPopUp('image', '<@ $element.field @>');
	});

<@ /js @>

