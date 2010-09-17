<?php
   
function smarty_block_block($params, $content, $smarty, $open) {
        
if ($open) {
	// explicit does nothing on opening tag
	// will later change with a repeat= parameter
} else {
	$old_vars = $smarty->_tpl_vars;
	if (isset($params['template']) && !isset($params['tpl'])) {
		$params['tpl'] = $params['template'];
		unset($params['template']);
	}
	if (!isset($params['tpl'])) {
		 $smarty->trigger_error("block: needs a tpl/template var", E_USER_NOTICE);
	}
	$template = $params['tpl'];
	unset($params['tpl']);
	$smarty->assign($params);
	$smarty->assign('content',$content);
	$output = $smarty->fetch($template);
	$smarty->_tpl_vars = $old_vars;
	return $output;
}

}
