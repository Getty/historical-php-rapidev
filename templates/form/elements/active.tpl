<input type="hidden" id="<@ $element.field @>" name="<@ $element.field @>" value="<@ $element.value @>" />

<@ if $element.value @>
	<img id="<@ $element.field @>_pic" src="/img/form/greenbutton.png" />
<@ else @>
	<img id="<@ $element.field @>_pic" src="/img/form/redbutton.png" />
<@ /if @>

<script type="text/javascript">

	$('#<@ $element.field @>_pic').click(function(){
		var value = $('#<@ $element.field @>').val();
		if (value == 0) {
			$(this).attr('src','/img/form/greenbutton.png');
			$('#<@ $element.field @>').val(1);
		} else {
			$(this).attr('src','/img/form/redbutton.png');
			$('#<@ $element.field @>').val(0);
		}
	});

</script>
