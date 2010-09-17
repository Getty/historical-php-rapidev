<?php
   
function smarty_block_js($params, $content, $smarty, $open) {
        
	if ($open) {
		// explicit does nothing on opening tag
		// will later change with a repeat= parameter
	} else {
		$old_vars = $smarty->_tpl_vars;
		$smarty->assign($params);
//		if ('dev' === STAGE) {
			$smarty->assign('content',$content);
//		} else {
//	$smarty->assign('content',trim(compress_javascript($content)));
//		}
		$output = $smarty->fetch('block/js.tpl');
		$smarty->_tpl_vars = $old_vars;
		return $output;
	}

}
