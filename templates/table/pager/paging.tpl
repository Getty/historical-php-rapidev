<@ if isset($tableinfo.prev_tablepage) @>
	<@ tablelink tablepage=$tableinfo.prev_tablepage text='<<' @>
<@ /if @>
<@ foreach from=1|range:$tableinfo.tablepagecount item=tablepage @>
	<@ if $tablepage == $tableinfo.tablepage_now @>
		<@ $tablepage @>
	<@ else @>
		<@ tablelink pager=true text=$tablepage @>
	<@ /if @>
<@ /foreach @>
<@ if isset($tableinfo.next_tablepage) @>
	<@ tablelink tablepage=$tableinfo.next_tablepage text='>>' @>
<@ /if @>
