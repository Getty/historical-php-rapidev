<select <@ if isset($select_class) @>class="<@ $select_class @>" <@ /if @> 
	<@ if isset($select_id) @>id="<@ $select_id @>" <@ /if @>
	<@ if isset($select_name) @>name="<@ $select_name @>" <@ /if @>
	<@ if isset($select_size) @>size="<@ if $select_size == 'max' @><@ $select_data|@count @><@ else @><@ $select_size @><@ /if @>" <@ /if @>
	<@ if isset($select_readonly) || $select_readonly @>readonly="readonly"<@ /if @>
	<@ if isset($select_onchange) || $select_onchange @>onchange="<@ $select_onchange @>"<@ /if @>
	<@ if isset($select_disabled) || $select_disabled @>disabled="disabled"<@ /if @>>
	<@ if isset($select_data) @>
		<@ foreach from=$select_data key=option_value item=option_data @>
			<@ if isset($select_optiontpl) @>
				<@ include file=$select_optiontpl @>
			<@ else @>
				<@ include file="form/elements/select/option.tpl" @>
			<@ /if @>
		<@ /foreach @>
	<@ /if @>
</select>