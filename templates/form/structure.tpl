<@ include file="form/prestructure.tpl" @>

<div id="rapidev_form_structure_<@ $form.id @>" class="rapidev_form_structure rapidev_form_structure_<@ $form.id @>">
	<@ include file="form/name.tpl" @>
	<@ include file="form/elements.tpl" @>
	<div class="form_row">
		<div class="form_submit">
			<@ include file="form/form/submit.tpl" @>
		</div>
		<div class="form_error">
			<@ include file="form/form/error.tpl" @>
		</div>
	</div>
</div>
<@ include file="form/poststructure.tpl" @>