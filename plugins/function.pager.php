<?

RD::RequireClass('RD_Util_Paging');

function smarty_function_pager($params, &$smarty)
{    
	if (!isset($params['PageSize'])) {
		$params['PageSize'] = 1;
	}
	
	if (!isset($params['CurrentPage'])) {
		$params['CurrentPage'] = 1;
	}

	if (!isset($params['PageVar'])) {
		$params['PageVar'] = 'paging_page';
	}

	if (!isset($params['URL'])) {
		
		$params['URL'] = $_SERVER['PHP_SELF'].'?'.$params['PageVar'].'=%d';
	}

	if (!isset($params['Max'])) {
		$params['Count'] = 1;
	} else {
		$params['Count'] = $params['Max'];
	}
	
	if (isset($params['Template'])) {
		$Template = $params['Template'];
		unset($params['Template']);
	}
	
	$Paging = new RD_Util_Paging();
	$Paging->SetFrom($params);
	
	$OldTplVars = $smarty->_tpl_vars;
	
	$smarty->assign($params);
	$smarty->assign($Paging->GetPaging());
	if (isset($Template)) {
		$return = $smarty->fetch($Template);
	} else {
		$return = $smarty->fetch('pager.tpl');
	}
	$smarty->_tpl_vars = $OldTplVars;
	return $return;
}
