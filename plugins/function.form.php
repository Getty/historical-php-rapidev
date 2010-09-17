<?

function smarty_function_form($params, &$smarty)
{
    
	// TODO: Backward comp.
	if (isset($params['name']) && !isset($params['id'])) {
		$params['id'] = $params['name'];
	}

	if (isset($smarty->_tpl_vars['form']) && isset($smarty->_tpl_vars['form']['id'])) {
		if (!isset($params['id'])) {
			$params['id'] = $smarty->_tpl_vars['form']['id'];
			$running_form = $params['id'];
		}
	}

	if (isset($params['id']) && is_string($params['id'])) {

		$FormName = $params['id'];
		
		if (!isset($running_form)) {
			$old_vars = $smarty->_tpl_vars;
		}

        if (isset($smarty->main->SESSION['forms'][$FormName])) {
	        $smarty->assign('form',$smarty->main->SESSION['forms'][$FormName]);
        } else {
	    	$smarty->trigger_error("form: not prepared form", E_USER_NOTICE);
        }
        $return = '';
        
		if (isset($params['template'])) {
			$template = $params['template'];
		}
        
        if (isset($params['element']) || isset($params['fieldname']) || isset($params['field'])) {
        	
	        if (isset($params['element'])) {
    	    	$element = $params['element'];
	        } elseif (isset($params['fieldname'])) {
	        	$element = $params['fieldname'];
	        	$template = 'form/element/name.tpl';
	        } elseif (isset($params['field'])) {
	        	$element = $params['field'];
	        	$template = 'form/element/type.tpl';
	        }
        	
        	$smarty->assign('element',$smarty->main->SESSION['forms'][$FormName]['elements'][$element]);
        	$smarty->assign('key',$element);
        	
        	if (isset($template)) {
        		$return .= $smarty->fetch($template);
        	} else {
        		$return .= $smarty->fetch('form/element.tpl');
        	}
        	
        } else {

        	if (isset($smarty->main->SESSION['forms'][$FormName]['pretemplate'])) {
        		$return .= $smarty->fetch($smarty->main->SESSION['forms'][$FormName]['pretemplate']);
        	}

        	if (isset($template)) {
        		$return .= $smarty->fetch($template);
        	} elseif (isset($smarty->main->SESSION['forms'][$FormName]['template'])) {
        		$return .= $smarty->fetch($smarty->main->SESSION['forms'][$FormName]['template']);
        	} elseif (isset($smarty->main->SESSION['forms'][$FormName]['horizontal'])) {
        		$return .= $smarty->fetch('form/structure_horizontal.tpl');
        	} else {
        		$return .= $smarty->fetch('form/structure.tpl');
        	}

        	if (isset($smarty->main->SESSION['forms'][$FormName]['posttemplate'])) {
        		$return .= $smarty->fetch($smarty->main->SESSION['forms'][$FormName]['posttemplate']);
        	}

        }

		if (!isset($running_form)) {
			$smarty->_tpl_vars = $old_vars;
		}

        return $return;
    } else {
    	$smarty->trigger_error("form: need a form", E_USER_NOTICE);
    	return;
    }
	
}
