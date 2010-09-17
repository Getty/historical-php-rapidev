<@ if !empty($tabledata) @>
	<table>
		<@ include file="table/pager/structure.tpl" @>
		<tr>
			<@ foreach from=$tableinfo.headlines item=headline @>
				<th>
					<@ $headline @>
				</th>
			<@ /foreach @>
		</tr>
		<@ foreach from=$tabledata item=row @>
			<tr>
				<@ foreach from=$row item=field @>
					<td>
						<@ $field @>
					</td>
				<@ /foreach @>
			</tr>
		<@ /foreach @>
		<@ include file="table/pager/structure.tpl" @>
	</table>
	<@ tablelink text='test' @>
<@ /if @>
<@ include file="table/hiddenfields.tpl" @>