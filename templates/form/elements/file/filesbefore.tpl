<@ if $element.filetype == 'image' @>

	<div class="form_row" id="<@ $element.field @>_filesbefore_row">
		<ul>
			<@ foreach from=$element.filesbefore item=file @>
				<li>
					<a href="<@ $file.big @>"><img src="<@ $file.thumb @>"/></a>
				</li>
			<@ /foreach @>
		</ul>
	</div>

<@ else @>

	<div class="form_row" id="<@ $element.field @>_filesbefore_row">
		<div>
			<ul>
				<@ foreach from=$element.filesbefore item=file @>
					<li><a href="<@ $file.original @>"><@ $file.name @></a></li>
				<@ /foreach @>
			</ul>
		</div>
	</div>

<@ /if @>