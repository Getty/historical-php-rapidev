<div class="rapidev_form_type rapidev_form_type_<@ $form.id @>">
	<@ if isset($element.template) @>
		<@ assign var=type_template value=$element.template @>
	<@ else @>
		<@ assign var=type_template value=$element.type @>
	<@ /if @>
	<@ include file="form/elements/"|cat:$type_template|cat:".tpl" @>
</div>