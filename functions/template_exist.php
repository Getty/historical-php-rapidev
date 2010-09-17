<?

if (!function_exists('template_exist')) {

	function template_exist($Template) {
		return RD::$Self->TemplateExist($Template);
	}

}
