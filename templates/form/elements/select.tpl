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
<@ if isset($element.optiontemplate) @>
	<@ assign var=optiontpl value=$element.optiontemplate @>
<@ else @>
	<@ assign var=optiontpl value='form/elements/select/option.tpl' @>
<@ /if @>
<@ include file="form/elements/select/core.tpl" 
	select_class="rapidev_form_select rapidev_form_select_`$element.id`" 
	select_id=$element.field
	select_name=$element.field
	select_size=$element.size
	select_readonly=$readonly
	select_data=$element.data
	select_optiontpl=$optiontpl @>
<script type="text/javascript">

	function <@ $element.field @>_deleteempty(el) {
		$(el).bind('change',function(){
			$(this).find('option[@value=0]').remove();
		});
	}

	<@ if isset($element.data.0) && isset($element.deleteempty) @>

		<@ $element.field @>_deleteempty('#<@ $element.field @>');
	
	<@ /if @>

</script>
<div style="float:left">
	<@ include file="form/elements/select/pager.tpl" @>
</div>
