<@* Rapidev Debugging Console *@>
<@ capture assign=debug_output @>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <title>RapiDev Debug Console [<@ $STAGE @>]</title>

<style type="text/css">
/* <![CDATA[ */
body, h1, h2, h3, td, th, p {
    font-family: sans-serif;
    font-weight: normal;
    font-size: 0.9em;
    margin: 1px;
    padding: 0;
    border: 1px solid black;
}

h1 {
    margin: 0;
    text-align: left;
    padding: 2px;
    background-color: #8ab61f;
    color: black;
    font-weight: bold;
    font-size: 1.2em;
}

h2 {
    margin: 0;
    text-align: left;
    padding: 2px;
    background-color: #222222;
    color: white;
    font-weight: bold;
    font-size: 1.0em;
}

h3 {
    margin: 0;
    text-align: left;
    padding: 2px;
    background-color: #9bc730;
    color: black;
    text-align: left;
    font-weight: bold;
    padding: 2px;
    font-size: 0.8em;
}

body {
    background: black; 
}

p, table, div {
    background: #f0ead8;
} 

p {
    margin: 0;
    font-style: italic;
    text-align: center;
}

table {
    width: 100%;
}

th, td {
    font-family: monospace;
    vertical-align: top;
    text-align: left;
    padding: 0px 10px 0px 2px;
}

td {
    color: green;
}

.odd {
    background-color: #eeeeee;
}

.even {
    background-color: #fafafa;
}

.exectime {
    font-size: 0.8em;
    font-style: italic;
}

#table_assigned_vars th {
    color: blue;
}

#table_logfile td {
    color: black;
}

/* ]]> */
</style>
</head>
<body>

<h1>RapiDev Debug Console</h1>

<@ counter assign=ContextID start=0 @>

<@ foreach from=$Contexts item=Context @>

	<h2><@ $Context.Name @> <@ if $Context.Class @>(<@ $Context.Class @>)<@ /if @>:</h2>
	<@ counter assign=ContextID @>
	<h3 onclick="swap_content('RD_Console_Context_Templates_<@ $ContextID @>')" style="cursor:pointer;">Templates</h3>
	<span id="RD_Console_Context_Templates_<@ $ContextID @>" style="display:none">
		<@ include file="debug/templates.tpl" Templates=$Context.Templates @>
	</span>

	<h3 onclick="swap_content('RD_Console_Context_Vars_<@ $ContextID @>')" style="cursor:pointer;">Vars</h3>
	<span id="RD_Console_Context_Vars_<@ $ContextID @>" style="display:none">
		<@ include file="debug/vars.tpl" Vars=$Context.Vars SmartyVars=1 @>
	</span>

<@ /foreach @>

<h2>Core Informations:</h2>

<h3 onclick="swap_content('RD_Console_Global_Roots')" style="cursor:pointer;">Roots</h3>
<span id="RD_Console_Global_Roots" style="display:none">
	<@ include file="debug/roots.tpl" @>
</span>

<h3 onclick="swap_content('RD_Console_Global_LoadedModules')" style="cursor:pointer;">Modules</h3>
<span id="RD_Console_Global_LoadedModules" style="display:none">
	<@ include file="debug/modules.tpl" @>
</span>

<h3 onclick="swap_content('RD_Console_Global_Logfile')" style="cursor:pointer;">Logfile</h3>
<span id="RD_Console_Global_Logfile" style="display:none">
	<@ include file="debug/log.tpl" @>
</span>

<h3 onclick="swap_content('RD_Console_Global_CoreCache')" style="cursor:pointer;">CoreCache</h3>
<span id="RD_Console_Global_CoreCache" style="display:none">
	<@ include file="debug/vars.tpl" Vars=$CoreCache SmartyVars=0 @>
</span>

<h2>PHP Informations:</h2>

<h3 onclick="open_phpinfo('RD_Console_Global_Phpinfo')" style="cursor:pointer;">phpinfo()</h3>

<@ if isset($FileContent) @>
	<h3 onclick="open_filecontent('RD_Console_Global_Filecontent')" style="cursor:pointer;">Source Code of Request Page</h3>
<@ /if @>

<h3 onclick="swap_content('RD_Console_Global_Cookie')" style="cursor:pointer;">$_COOKIE (sent)</h3>
<span id="RD_Console_Global_Cookie" style="display:none">
	<@ include file="debug/vars.tpl" Vars=$_COOKIE SmartyVars=0 @>
</span>

<h3 onclick="swap_content('RD_Console_Global_Get')" style="cursor:pointer;">$_GET</h3>
<span id="RD_Console_Global_Get" style="display:none">
	<@ include file="debug/vars.tpl" Vars=$_GET SmartyVars=0 @>
</span>

<h3 onclick="swap_content('RD_Console_Global_Post')" style="cursor:pointer;">$_POST</h3>
<span id="RD_Console_Global_Post" style="display:none">
	<@ include file="debug/vars.tpl" Vars=$_POST SmartyVars=0 @>
</span>

<h3 onclick="swap_content('RD_Console_Global_Session')" style="cursor:pointer;">$_SESSION (saved)</h3>
<span id="RD_Console_Global_Session" style="display:none">
	<@ include file="debug/vars.tpl" Vars=$_SESSION SmartyVars=0 @>
</span>

<h3 onclick="swap_content('RD_Console_Global_Server')" style="cursor:pointer;">$_SERVER</h3>
<span id="RD_Console_Global_Server" style="display:none">
	<@ include file="debug/vars.tpl" Vars=$_SERVER SmartyVars=0 @>
</span>

<h3 onclick="swap_content('RD_Console_Global_Files')" style="cursor:pointer;">$_FILES</h3>
<span id="RD_Console_Global_Files" style="display:none">
	<@ include file="debug/vars.tpl" Vars=$_FILES SmartyVars=0 @>
</span>

<@ if !empty($ExtraInformation) @>

    <h2>Extra Informations:</h2>

<@ /if @>

<@ foreach from=$ExtraInformation key=InfoName item=InfoData @>
	
	<h3 onclick="swap_content('RD_Console_Extra_<@ $InfoName|replace:" ":"_" @>')" style="cursor:pointer;"><@ $InfoName @></h3>
	<span id="RD_Console_Extra_<@ $InfoName|replace:" ":"_" @>" style="display:none">
		<@ if is_array($InfoData) @>
			<@ include file="debug/vars.tpl" Vars=$InfoData SmartyVars=0 @>
		<@ else @>
			<@ $InfoData @>
		<@ /if @>
	</span>

<@ /foreach @>

<h2 style="font-style:italic;color:#9bc730">
	<span<@ if isset($FileContent) @> style="cursor:pointer;" onclick="open_filecontent('RD_Console_Global_Filecontent')"<@ /if @>><@ $Filename @></span>
	<span>
	<@ if isset($FileContent) @>
		| LastAccess: <@ $FileAccess|date_format:"%d.%m.%y %H:%M:%S" @> | Modify: <@ $FileModify|date_format:"%d.%m.%y %H:%M:%S" @> | Now: <@ $smarty.now|date_format:"%d.%m.%y %H:%M:%S" @> | Size: <@ $FileSize|number_format:"":"":"." @> Bytes |
	<@ /if @>
	Stage: <@ $STAGE @>
	</span>
</h2>

<h2 style="font-style:italic">
	Execution Time: <@ $LastLog.time|string_format:"%.5f" @>s
	<@ if !empty($MaxExecTime) @>
		<span style="color:red">(Allowed: <@ $MaxExecTime @>s)</span>
	<@ /if @>
	Memory Usage: <@ $LastLog.mem|number_format:"":"":"." @> Bytes
	<@ if !empty($MemoryLimit) @>
		<span style="color:red">(Allowed: <@ $MemoryLimit @>)</span>
	<@ /if @>
</h2>

<script type="text/javascript">
// <![CDATA[

	if (window.swap_content == null) {
		function swap_content(what) {
			document.getElementById(what).style.display = 
			(document.getElementById(what).style.display=='none' ) ? 'block' : 'none'; 
		} 
	}

	if (window.open_phpinfo == null) {
		function open_phpinfo() {
		    if ( self.name == '' ) {
		       var title = 'RD_ConsoleDebug_Phpinfo';
		    } else {
		       var title = 'RD_ConsoleDebug_Phpinfo_' + self.name;
		    }		    
    		_rd_consoledebug_phpinfo = window.open("",title,"width=1000,height=600,resizable,scrollbars=yes");
		    _rd_consoledebug_phpinfo.document.write('<@ phpinfo|escape:'javascript' @>');
		    _rd_consoledebug_phpinfo.document.close();
		}		
	}

	<@ if isset($FileContent) @>
		if (window.open_filecontent == null) {
			function open_filecontent() {
			    if ( self.name == '' ) {
			       var title = 'RD_ConsoleDebug_Filecontent';
			    } else {
			       var title = 'RD_ConsoleDebug_Filecontent_' + self.name;
			    }		    
	    		_rd_consoledebug_filecontent = window.open("",title,"width=1000,height=600,resizable,scrollbars=yes");
			    _rd_consoledebug_filecontent.document.write('<pre><@ $FileContent|escape:'html'|escape:'javascript' @></pre>');
			    _rd_consoledebug_filecontent.document.close();
			}		
		}
	<@ /if @>

// ]]>
</script>

</body>
</html>
<@ /capture @>
<script type="text/javascript">
// <![CDATA[

	var title;
    if ( self.name == '' ) {
       title = 'RD_ConsoleDebug';
    } else {
       title = 'RD_ConsoleDebug_' + self.name;
    }
    var _rd_consoledebug = window.open("",title,"width=1000,height=800,resizable,scrollbars=yes");
    _rd_consoledebug.document.write('<@ $debug_output|escape:'javascript' @>');
    _rd_consoledebug.document.close();
    
// ]]>
</script>
