<?

function form_param_captcha_prepare($main,$FormName) {
	$Form =& $main->SESSION['forms'][$FormName];
	
	if (isset($Form['captcha']) && $Form['captcha']) {
		$Form['captcha_data'] = captcha_prepare_data();
	}
	return;
}