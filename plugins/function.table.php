<?

function smarty_function_table($params, &$smarty)
{
    
	if (isset($params['name']) && is_string($params['name'])) {

        $old_vars = $smarty->_tpl_vars;

        $TableName = $params['name'];
		$smarty->assign('table',$TableName);
		$smarty->assign('tabledata',$smarty->main->Modules['table']->TableCache[$TableName]['data']);
		$smarty->assign('tableinfo',$smarty->main->Modules['table']->TableCache[$TableName]['info']);

		if (isset($params['template'])) {
			$return = $smarty->fetch($params['template']);
		} else {
			$return = $smarty->fetch('table/structure.tpl');			
		}
		
        $smarty->_tpl_vars = $old_vars;

        return $return;

	} else {

    	$smarty->trigger_error("table: need a tablename", E_USER_NOTICE);
    	return;

    }
	
}
