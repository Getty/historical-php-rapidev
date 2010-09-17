<@ if isset($menu) @>
	<div id="rapidev_navigation" class="navigation">
		<@ foreach from=$menu key=menupage item=name @>
			<a href="<@ $PHP_SELF @>?page=<@ $menupage @>&formclean=true"><@ $name @></a>
		<@ /foreach @>
	</div>
<@ /if @>
