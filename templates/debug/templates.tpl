<div>
	<@ foreach name=Templates from=$Templates item=Template @>
	    <@ if isset($Template.exec_time) @>
	        <span class="exectime">
				(<@ $Template.exec_time|string_format:"%.5f" @>)
				<@ if $smarty.foreach.Templates.index eq 0 @>(total)<@ /if @>
	        </span>
	    <@ /if @>
		<font color=<@ if $Template.type eq "template" @>brown<@ elseif $Template.type eq "insert" @>insert<@ else @>green<@ /if @>>
			<@ $Template.filename|escape:html @>
		</font>
	    <br />
	<@ foreachelse @>
		<p>no templates included</p>
	<@ /foreach @>
</div>
