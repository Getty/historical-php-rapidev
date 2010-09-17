<?

function smarty_function_link($params, &$smarty) {

	if (isset($params['config'])) {
		$config = $params['config'];
		unset($params['config']);
		foreach($params as $key => $value) {
			$config[$key] = $value;
		}
	} else {
		$config = $params;
	}
	if (isset($config['assign'])) {
		$assign = $config['assign'];
		unset($config['assign']);
	}
	$return = $smarty->main->Link($config);
	if (isset($assign)) {
		$smarty->assign($assign,$return);
		return;
	} else {
		return $return;
	}

}
