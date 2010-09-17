<div type="text" id="<@ $element.field @>_uidate"></div>
<input type="hidden" id="<@ $element.field @>" name="<@ $element.field @>" value="<@ $element.value @>" />
<@ js @>

$(function(){

	$("#<@ $element.field @>_uidate").datepicker({
		altField: '#<@ $element.field @>',
		dateFormat: '@',
		gotoCurrent: true,
		defaultDate: new Date(<@ $element.value @>),
<@ if $element.maxdate @>
		maxDate: '<@ $element.maxdate @>',
<@ /if @>
<@ if $element.mindate @>
		minDate: '<@ $element.mindate @>',
<@ /if @>
<@ if $element.nofuture && !$element.maxdate @>
		maxDate: new Date(),
<@ elseif $element.nopast && !$element.mindate @>
		minDate: new Date(),
<@ /if @>
		internetexplorer: 'isbadforyou'
	});
	
});

<@ /js @>