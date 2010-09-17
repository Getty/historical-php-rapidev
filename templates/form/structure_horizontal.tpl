<@ assign var=count value=$form.elements|@count @>
<table id="rapidev_form_structure_<@ $form.id @>">
<@ if isset($form.name) @>
<tr>
	<td colspan="<@ $count @>">
		<h1>
			<@ $form.name @>
		</h1>
	</td>
</tr>
<@ /if @>
<tr>
<@ foreach from=$form.elements item=element key=key @>
	<td>
		<@ include file="form/element/name.tpl" @>
	</td>
<@ /foreach @>
</tr>
<tr>
<@ foreach from=$form.elements item=element key=key @>
	<td>
		<@ include file="form/element/type.tpl" @>
	</td>
<@ /foreach @>
</tr>
<tr>
<@ foreach from=$form.elements item=element key=key @>
	<td>
		<@ if isset($element.errormsg) @>
			<@ include file="form/element/error.tpl" @>
		<@ /if @>
	</td>
<@ /foreach @>
</tr>
<tr>
	<td>
		<@ include file="form/form/submit.tpl" @>
	</td>
	<td colspan="<@ math equation="x-1" x=$count @>">
		<@ include file="form/form/error.tpl" @>
	</td>
</tr>
</table>
<@ include file="form/poststructure.tpl" @>