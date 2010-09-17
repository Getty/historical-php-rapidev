<div class="paging element pagechooser">
	<p>
	<strong>Seiten:</strong> 
	
	<span>

		<@ if $previous @>
			<a class="paging_prev" href="<@ $previous|string_format:$URL @>">&lt;&lt;</a> 
		<@ /if @>
	
		<@ if $jumpBackward @>
			<@ if $current == 1 @>
				<strong class="paging_current">1</strong>
			<@ else @>
				<a class="paging_first" href="<@ "1"|string_format:$URL @>">1</a> 
			<@ /if @>	
			<a class="paging_jmp_backward" href="<@ $jumpBackward|string_format:$URL @>">...</a> 
		<@ /if @>
		
		<@ foreach from=$range item=page @>
			<@ if $page == $current @>
				<strong class="paging_current"><@ $page @></strong> 
			<@ else @>
				<a class="paging_range" href="<@ $page|string_format:$URL @>"><@ $page @></a> 
			<@ /if @>
		<@ /foreach @>
	
		<@ if $jumpForward @>
			<a class="paging_jmp_forward" href="<@ $jumpForward|string_format:$URL @>">...</a> 
			<@ if $current == $pageCount @>
				<strong class="paging_current"><@ $pageCount @></strong> 
			<@ else @>
				<a class="paging_last" href="<@ $pageCount|string_format:$URL @>"><@ $pageCount @></a> 
			<@ /if @>
		<@ /if @>
	
		<@ if $next @>
			<a class="paging_next" href="<@ $next|string_format:$URL @>">&gt;&gt;</a> 
		<@ /if @>

	</span>
	&nbsp;

</div>
</p>
</center>
