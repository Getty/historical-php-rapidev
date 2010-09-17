<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_modifier_explode($string,$sep)
{
	return explode($sep,$string);
}
