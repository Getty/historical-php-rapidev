<@ assign var=CRUD_First value=$CRUD_Table|@reset @>
<@ if $CRUD_First @>
	<table class="rapidev_crud">
		<thead>
			<tr>
				<@ foreach from=$CRUD_First|@array_keys item=Key @>
					<th><@ $Key @></th>
				<@ /foreach @>
				<th>Funktionen</th>
			</tr>
		</thead>
		<tbody>
			<@ foreach name=CRUD_Table_Rows from=$CRUD_Table item=Row @>
				<tr>
					<@ foreach name=CRUD_Table_Row_Values from=$Row item=Value @>
						<td><@ $Value|htmlentities @></td>
						<@ if $smarty.foreach.CRUD_Table_Row_Values.last @>
							<td>
								<@ link id_field=$CRUD.Field id_value=$Row.ID formclean=true text=$CRUD.TextUpdate @>
								<@ if $CRUD.TextDelete @>
									<@ link id_field=$CRUD.DeleteField id_value=$Row.ID formclean=true text=$CRUD.TextDelete @>
								<@ /if @>
								<@ if $CRUD.TextDuplicate @>
									<@ link id_field=$CRUD.DuplicateField id_value=$Row.ID formclean=true text=$CRUD.TextDuplicate @>
								<@ /if @>
								<@ if $CRUD.ExtraFunctions @>
										<@ foreach from=$CRUD.ExtraFunctions item=ExtraFunction @>
												<@ if $ExtraFunction.Template @>
													<@ include file=$ExtraFunction.Template @>
												<@ /if @>
										<@ /foreach @>
								<@ /if @>
							</td>
						<@ /if @>
					<@ /foreach @>
				</tr>
			<@ /foreach @>
		</tbody>
	</table>
<@ /if @>
<@ if $CRUD.TextCreate @>
	<h2>
		<@ link page=$page id_field=$CRUD.Field id_value=0 formclean=true text=$CRUD.TextCreate @>
	</h2>
<@ /if @>

