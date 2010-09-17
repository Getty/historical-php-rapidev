<@ if $tableinfo.tablepagecount > 1 @>
	<tr>
		<td colspan="<@ $tableinfo.headlines_count @>">
			<center>
				<@ include file="table/pager/paging.tpl" @>
			</center>
		</td>
	</tr>
<@ /if @>
