<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>
			<@ $title @>
		</title>
		<@ include file="header.tpl" @>
	</head>
	<body>
		<form id="rapidev_form" name="rapidev_form" method="POST" action="<@ $PHP_SELF @>?page=<@ $page @>">
			<input type="hidden" id="rapidev_hidden_page" name="page" value="<@ $page @>" />
			<div id="all">
				<div id="rapidev_title" >
					<@ $title @>
				</div>
				<div id="rapidev_navigation" >
					<@ include file="navigation.tpl" @>
				</div>
				<div id="rapidev_notices">
					<@ if isset($notices) @>
						<@ include file="notices.tpl" @>
					<@ /if @>
				</div>
				<div id="rapidev_content" >
					<@ include file="content.tpl" @>
				</div>
				<div id="rapidev_footer" >
					<@ include file="footer.tpl" @>
				</div>
			</div>
		</form>
	</body>
</html>
