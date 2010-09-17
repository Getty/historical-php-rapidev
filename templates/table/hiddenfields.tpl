<@ foreach from=$info.hidden_fields key=key item=value @>
	<input type="hidden" name="<@ $table @>_<@ $key @>" value="<@ $value @>" />
<@ /foreach @>
