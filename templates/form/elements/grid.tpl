<@ if !empty($element.data) @>

	<@ assign var=first value=$element.data|@first @>
	<@ assign var=data value=$element.data|@array_slice:0:$element.gridsize:true @>
	
	<table class="rapidev_form_grid rapidev_form_grid_<@ $form.id @> rapidev_form_grid_<@ $form.id @>_<@ $element.id @>"
			id="rapidev_form_<@ $form.id @>_<@ $element.id @>"
		cellpadding="0" cellspacing="0"> <@* THE JS-PLUGIN NEEDS THIS CELLPADDING AND CELLSPACING! DONT REMOVE IT! *@>
	
		<thead>
			<tr>
				<@ foreach from=$first key=key item=item @>
					<th><@ $key|replace:"_":" " @></th>
				<@ /foreach @>
			</tr>
		</thead>
		
		<tbody>
			<@ include file="form/elements/grid/rows.tpl" data=$data @>
		</tbody>
	
	</table>

	<script type="text/javascript" src="<@ $PHP_SELF @>?file=javascripts/jquery/datagrid.js"></script>
	<script type="text/javascript">

		var rapidev_form_grid_<@ $form.id @>_<@ $element.id @>_selected;
	
		$(document).ready(function() {
			
				function init_rapidev_form_grid_<@ $form.id @>_<@ $element.id @>() {
				
					$("#rapidev_form_<@ $form.id @>_<@ $element.id @> tbody tr[@gridloaded='false']").each(
					
						function() {
							
							$(this).mouseover(
								function(){
									if ($(this).attr('gridselected') != 'true') {
										$(this).css('background','green');
									}
								}
							);
						
							$(this).mouseout(
								function(){
									if ($(this).attr('gridselected') != 'true') {
										$(this).css('background','white');
									}
								}
							); 
	
							$(this).click(
								function(){
									$("#<@ $element.field @>").val($(this).attr('gridvalue'));
									<@ if isset($form.submitonchange) @>
										$(this).css('background','red');
										$('#<@ $form.id @>').val('submit');
										$('#rapidev_form').submit();
									<@ else @>
										if (rapidev_form_grid_<@ $form.id @>_<@ $element.id @>_selected) {
											$('#'+rapidev_form_grid_<@ $form.id @>_<@ $element.id @>_selected)
												.css('background','white');
												.attr('gridselected','false');
										}
										rapidev_form_grid_<@ $form.id @>_<@ $element.id @>_selected = $(this).attr('id');
										$(this).attr('gridselected','true');
									<@ /if @>
								}
							);
							
							$(this).attr('gridloaded','true');
	
						}
	
					);

				}
				
				$("#rapidev_form_<@ $form.id @>_<@ $element.id @>").datagrid({
					url: '<@ $PHP_SELF @>',
					form: {
							module: 'form',
							form: '<@ $form.id @>',
							gridsize: '<@ $element.gridsize @>',
							element: '<@ $element.id @>'
						},
					pageParam: 'gridpage',
					height: 350,
					asIs: true,
					loadingHTML: 'Loading',
					idPrefix: '__datagrid_<@ $form.id @>_<@ $element.id @>',
					populateFunction: function(){
						init_rapidev_form_grid_<@ $form.id @>_<@ $element.id @>()
					},
					<@ if isset($element.gridwidth) @>
						width: <@ $element.gridwidth @>,
					<@ else @>
						width: [<@ foreach name=gridwidth from=$first item=null @>
									200<@ if !$smarty.foreach.gridwidth.last @>,<@ /if @>
								<@ /foreach @>],
					<@ /if @>
					isResizable: 0,
				});
			
				init_rapidev_form_grid_<@ $form.id @>_<@ $element.id @>();
				
			});
	
	</script>
	
	<input type="hidden" value="<@ $element.value @>" name="<@ $element.field @>" id="<@ $element.field @>" />
	
<@ /if @>