<div class="rapidev_form_error rapidev_form_error_<@ $form.id @>" style="color:red">
	<@ foreach from=$element.errormsg item=errormsg @> 
		<@ if is_array($errormsg) @>
			<@ $errormsg.name @>: <@ $errormsg.errormsg @><br />
		<@ else @>
			<@ $errormsg @><br />
		<@ /if @>
	<@ /foreach @>
</div>
