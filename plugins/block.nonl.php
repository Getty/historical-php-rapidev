<?

function smarty_block_nonl($params, $content, &$smarty) {

	if (isset($content)) {
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
		
		$return = str_replace(array("\x0A", "\x0D"), '', $content);
		if (isset($assign)) {
			$smarty->assign($assign,$return);
			return;
		} else {
			return $return;
		}
	}

}
