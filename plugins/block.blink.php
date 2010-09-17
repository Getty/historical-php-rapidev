<?

function smarty_block_blink($params, $content, &$smarty) {

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
		
		$config['text'] = $content;
		$config['ignore_entities'] = '1';
		
		$return = $smarty->main->Link($config);
		if (isset($assign)) {
			$smarty->assign($assign,$return);
			return;
		} else {
			return $return;
		}
	}

}
