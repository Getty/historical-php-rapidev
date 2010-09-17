<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_modifier_first($var)
{

	if (!is_array($var)) {
		return false;
	}

	reset($var);
	
	return current($var);
	
}
