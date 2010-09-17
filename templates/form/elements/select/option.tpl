<option value="<@ $option_value @>" <@ if $option_value == $select_value @> selected="selected" <@ if isset($selectedcolor) @>style="color:<@ $selectedcolor @>"<@ /if @><@ /if @>>
	<@ if is_array($option_data) && isset($select_name_field) @>
		<@ if ($option_data.ID) @>
			<@ $option_data.$select_name_field @>
		<@ else @>
			<@ $option_data.$select_name_field @>
		<@ /if @>
	<@ else @>
		<@ $option_data @>
	<@ /if @>
</option>