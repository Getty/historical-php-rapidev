<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_modifier_utf8_encode($string)
{
	return utf8_encode($string);
}
