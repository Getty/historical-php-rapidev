<?

function smarty_function_hidden($params, &$smarty)
{
    
	if (isset($params['name']) && is_string($params['name'])) {
		if (isset($params['value'])) {
			$value = $params['value'];
		} else {
			$value = $smarty->_tpl_vars[$params['name']];
		}
		echo '<input type="hidden" name="'.$params['name'].'" value="'.$value.'" />';
    } else {
    	$smarty->trigger_error("hidden: need a name", E_USER_NOTICE);
    	return;
    }
	
}
