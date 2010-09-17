<@ if isset($element.page) && $element.maxpage > 1 @>
	<@ foreach from=$element.pages item=pagenumber @>
		<@ if $pagenumber == 1 || $pagenumber == 2 || 
			  $pagenumber == $element.page-2 || $pagenumber == $element.page-1 || 
			  $pagenumber == $element.page || 
			  $pagenumber == $element.page+1 || $pagenumber == $element.page+2 ||
			  $pagenumber == $element.maxpage-1 || $pagenumber == $element.maxpage @>
			<@ assign var=point value=0 @>
			<@ if $pagenumber == $element.page @>
				<b><@ $pagenumber @></b>
			<@ else @>
				<span class="<@ $element.field @>_pages"><@ $pagenumber @></span>
			<@ /if @>
		<@ else @>
			<@ if $point == 0 @>
				...
				<@ assign var=point value=1 @>
			<@ /if @>
		<@ /if @>
	<@ /foreach @>
	<input type="hidden" value="<@ $element.page @>" name="<@ $element.field @>_page" id="<@ $element.field @>_page" />
	<script language="javascript">
		
		$('.<@ $element.field @>_pages').click(function(){
			$('#<@ $element.field @>_page').val($(this).text());
			formsubmit();
		});
	
	</script>
<@ /if @>
