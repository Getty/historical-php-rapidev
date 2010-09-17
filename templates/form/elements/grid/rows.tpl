<@ foreach from=$data key=datakey item=dataitem @>
	<tr id="rapidev_form_<@ $form.id @>_<@ $datakey @>" gridvalue="<@ $datakey @>" gridloaded="false">
		<@ foreach from=$dataitem key=itemkey item=itemvalue @>
			<td><@ $itemvalue @></td>
		<@ /foreach @>
	</tr>
<@ /foreach @>
