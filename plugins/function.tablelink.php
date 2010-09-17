<?

function smarty_function_tablelink($params, &$smarty)
{

	if (!isset($smarty->_tpl_vars['table'])) {
    	$smarty->trigger_error("element: need a name", E_USER_NOTICE);
    	return;		
	}

	$table = $smarty->_tpl_vars['table'];
	
	if (isset($params['assign'])) {
		$assign = $params['assign'];
		unset($params['assign']);
	}

	if (isset($params['tablepage'])) {
		$params[$table.'_tablepage'] = $params['tablepage'];
		unset($params['tablepage']);
	} elseif (isset($params['pager']) && isset($smarty->_tpl_vars['tablepage'])) {
		$params[$table.'_tablepage'] = $smarty->_tpl_vars['tablepage'];
	} else {
		$params[$table.'_tablepage'] = $smarty->_tpl_vars['tableinfo']['tablepage_now'];
	}

	if (isset($params['search'])) {
		$params[$table.'_search'] = $params['search'];
		unset($params['search']);
	} elseif (isset($smarty->_tpl_vars['tableinfo']['search'])) {
		$params[$table.'_search'] = $smarty->_tpl_vars['tablepage'];
	}	

	$return = $smarty->main->Link($params);

	if (isset($assign)) {
		$smarty->assign($assign,$return);
		return;
	} else {
		return $return;
	}

}
