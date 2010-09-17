<@ if $element.full @>
	<@ include file="form/element/type.tpl" @>
<@ else @>
	<div id="rapidev_form_<@ $form.id @>_<@ $element.id @>_row" class="form_row">
		<div id="rapidev_form_<@ $form.id @>_<@ $element.id @>_cell_name" class="form_name" <@ if $form.vertical_cell @> style="float:none"<@ /if @>>
			<@ include file="form/element/name.tpl" @>
		</div>
		<div id="rapidev_form_<@ $form.id @>_<@ $element.id @>_cell_type" class="form_type" <@ if $form.vertical_cell @> style="float:none"<@ /if @>>
			<@ include file="form/element/type.tpl" @>
		</div>
	</div>
<@ /if @>
<@ if !empty($element.filesbefore) @>
	<div class="form_row" id="<@ $element.field @>_filesbefore_row">
		<@ if isset($element.templatefilesbefore) @>
			<@ include file=$element.templatefilesbefore @>
		<@ else @>
			<@ include file="form/elements/file/filesbefore.tpl" @>
		<@ /if @>
	</div>
<@ /if @>
<@ if isset($element.errormsg) @>
	<div class="form_row" id="<@ $element.field @>_error_row">
		<@ include file="form/element/error.tpl" @>
	</div>
<@ /if @>