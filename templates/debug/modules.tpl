<table>
	<@ foreach name=vars from=$LoadedModules key=Module item=ModuleFile @>
		<tr>
			<td>
				<b style="color:black"><@ $Module @></b>
			</td>
			<td>
				<i style="color:black"><@ $ModuleFile @></i>
			</td>
		</tr>
	<@ /foreach @>
</table>