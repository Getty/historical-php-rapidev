<table id="table_logfile">
    <@ foreach name=logfile from=$Log item=Logline @>
        <tr class="<@ cycle values="odd,even" @>"
        	<@ if $Logline.level == 1 @>
        		style="background-color:#dedede"
        	<@ elseif $Logline.level == 2 @>
        		style="background-color:white"
        	<@ elseif $Logline.level == 3 @>
        		style="background-color:lightblue"
        	<@ elseif $Logline.level == 4 @>
        		style="background-color:#f90"
        	<@ elseif $Logline.level == 5 @>
        		style="background-color:#ff9999"
        	<@ elseif $Logline.level == 6 @>
        		style="background-color:#ff0000"
        	<@ /if @>>
            <td>
            	<@ if $Logline.level == 1 @>
            		<@ $Logline.trace_level @>
            	<@ /if @>
            </td>
            <@ if isset($Logline.mem) @>
            	<td>
            		<@ $Logline.mem @>
            	</td>
            <@ /if @>
            <td>
            	<@ $Logline.from @>
		<@ if isset($Logline.fulltrace) @>
			<@ $Logline.fulltrace|@debugprintr @>
		<@ /if @>
            </td>
            <td>
	            <@ $Logline.time|string_format:"%.5f" @>
            </td>
            <td>
            	<@ if is_string($Logline.text) @>
   		            	<@ $Logline.text @>
   		        <@ elseif is_array($Logline.text) @>
   		        		<@ $Logline.text|@debugprintr @>
   		        <@ else @>
   		        		<@ $Logline.text|debugprintr @>
            	<@ /if @>
            </td>
		</tr>
    <@ foreachelse @>
		<tr><td><p>no template variables assigned</p></td></tr>
    <@ /foreach @>
</table>
