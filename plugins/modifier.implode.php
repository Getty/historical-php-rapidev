<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_modifier_implode($array,$sep)
{
	return implode($sep,$array);
}
