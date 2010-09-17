<?

function form_param_captcha_validate($main,$FormName) {
	$Form =& $main->SESSION['forms'][$FormName];

	$Value = $main->GetPost($FormName.'_captcha_'.$Form['captcha_data']['hash']);
	
	if ($Value == $Form['captcha_data']['result']) {
		form_param_captcha_prepare($main,$FormName);
		unset($Form['captcha_data']['error']);
		return true;
	} else {
		unset($Form['captcha_data']);
		form_param_captcha_prepare($main,$FormName);
		$Form['captcha_data']['error'] = true;
		return false;
	}
	
}