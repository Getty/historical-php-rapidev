<?

function smarty_function_step($params, &$smarty)
{
	
   	if (isset($params['base']) && is_string($params['base'])) {
   		if (isset($smarty->_tpl_vars['element']) && isset($smarty->_tpl_vars[$smarty->_tpl_vars['element'].'_step_finish'])) {
			$file = 'finish';
   		} else {
	   		if (isset($params['step'])) {
	   			$step = $params['step']+0;
	   		} else {
	   			if (isset($smarty->_tpl_vars['element']) && isset($smarty->_tpl_vars[$smarty->_tpl_vars['element'].'_step'])) {
	   				$step = $smarty->_tpl_vars[$smarty->_tpl_vars['element'].'_step']+0;
	   			} else {
	   				$step = 1;
	   			}
	   		}
		   	$file = 'step'.$step;
   		}
	   	if (isset($params['prefix'])) {
	   		$prefix = $params['prefix'];
	   	} else {
	   		$prefix = '.tpl';
	   	}
   		return $smarty->fetch($params['base'].DIR_SEP.$file.$prefix);
    } else {
    	$smarty->trigger_error("step: need a base", E_USER_NOTICE);
    	return;
    }
	
}
