<@ if isset($element.readonly) @>
	<@ assign var=readonly value=$element.readonly @>
<@ /if @>
<@ if isset($element.noday) @>
	<@ assign var=noday value=$element.noday @>
<@ /if @>

<@ assign var=field value=$element.field @>

<@ if $noday @>

	<input type="hidden" value="0" id="<@ $element.field @>_day" name="<@ $element.field @>[day]" />

<@ else @>

	<@ include file="form/elements/select/core.tpl" 
		select_class="rapidev_form_select rapidev_form_select_`$element.key` rapidev_form_select_`$element.key`_day" 
		select_id="`$field`_day"
		select_name="`$field`[day]"
		select_size=1
		select_readonly=$readonly @>

<@ /if @>

<@ include file="form/elements/select/core.tpl" 
	select_class="rapidev_form_select rapidev_form_select_`$element.key` rapidev_form_select_`$element.key`_month" 
	select_id="`$field`_month"
	select_name="`$field`[month]"
	select_size=1
	select_readonly=$readonly
	select_data=$element.month_data 
	select_value=$element.value.month @>
	
<@ include file="form/elements/select/core.tpl" 
	select_class="rapidev_form_select rapidev_form_select_`$element.key` rapidev_form_select_`$element.key`_year" 
	select_id="`$field`_year"
	select_name="`$field`[year]"
	select_data=$element.year_data 
	select_value=$element.value.year @>

<@ js @>

	<@ if !$noday @>

		var <@ $element.field @>_month_days = new Object();

		<@ $element.field @>_month_days[0] = 31;
		<@ foreach from=$element.month_days key=month item=days @>
			<@ $element.field @>_month_days[<@ $month @>] = <@ $days @>;
		<@ /foreach @>

		function <@ $element.field @>_setdays(month) {
		
			if (!<@ $element.field @>_month_days[month]) {
				<@ $element.field @>_month = 0;
			}
	
			var old_day = $('#<@ $element.field @>_day').val();
			$('#<@ $element.field @>_day').find('option').remove();
			$('#<@ $element.field @>_day').append('<option value="0"><@ $element.day @></option>');
			for(var i=1; i <= <@ $element.field @>_month_days[month]; i++) {
				$('#<@ $element.field @>_day').append('<option value="'+i+'">'+i+'</option>');
			}
			if (old_day) {
				$('#<@ $element.field @>_day').val(old_day);
			} else {
				$('#<@ $element.field @>_day').val(0);
			}
			$('#<@ $element.field @>_day').show();
	
		}

		var <@ $element.field @>_month = $('#<@ $element.field @>_month').val();

		<@ $element.field @>_setdays(month);
		$('#<@ $element.field @>_day').val(<@ $element.value.day @>);

		$('#<@ $element.field @>_month').bind('change',function(){		
			<@ $element.field @>_month = $(this).val();
			if (<@ $element.field @>_month_days[month]) {
				<@ $element.field @>_setdays(month);
			}
		});

	<@ /if @>

	$('#<@ $element.field @>_month,#<@ $element.field @>_year').bind('change',function(){		
		$(this).find('option[value=0]').remove();
	});

<@ /js @>
