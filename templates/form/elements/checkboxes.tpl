<@ if isset($element.description) @>
	<@ assign var=select_name_field value=$element.description @>
<@ else @>
	<@ assign var=select_name_field value='Description' @>
<@ /if @>
<@ if isset($element.value) && !isset($select_value) @>
	<@ assign var=select_value value=$element.value @>
<@ /if @>
<@ if isset($element.readonly) || isset($readonly) @>
	<@ assign var=readonly value=true @>
<@ else @>
	<@ assign var=readonly value=false @>
<@ /if @>
<div class="rapidev_form_element_checkboxes rapidev_form_element_checkboxes_<@ $form.id @> rapidev_form_element_<@ $element.field @>">
	<@ foreach from=$element.data key=ID item=option_data @>
		<div>
			<input type="checkbox" name="<@ $element.field @>[<@ $ID @>]"
						<@ if (isset($option_data.disabled) && $option_data.disabled) || (isset($element.readonly) && $element.readonly) @>disabled="disabled"<@ /if @>
						<@ if isset($element.value.$ID) @>checked="checked"<@ /if @>
						/>
			<span>	
				<@ if is_array($option_data) && isset($select_name_field) @>
					<@ if ($option_data.ID) @>
						<@ $option_data.$select_name_field @>
					<@ else @>
						<@ $option_data.$select_name_field @>
					<@ /if @>
				<@ else @>
					<@ $option_data @>
				<@ /if @>
			</span>
		</div>
	<@ /foreach @>
</div>
<div style="float:left">
	<@ include file="form/elements/select/pager.tpl" @>
</div>