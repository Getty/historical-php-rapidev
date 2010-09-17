<table id="table_assigned_vars">
    <@ foreach name=vars from=$Vars key=VarName item=Var @>
        <tr class="<@ cycle values="odd,even" @>">
            <th>
            	<@ if $SmartyVars @>
            		<@ ldelim @>&nbsp;$<@ $VarName @>&nbsp;<@ rdelim @>
            	<@ else @>
            		<@ $VarName @>
            	<@ /if @>
            </th>
            <td><@ $Var|@debugprintr @></td></tr>
    <@ foreachelse @>
        <tr><td><p>
			<@ if $SmartyVars @>
				<b>no template variables assigned</b>
			<@ else @>
				<i>empty</i>
			<@ /if @>
		</p></td></tr>
    <@ /foreach @>
</table>