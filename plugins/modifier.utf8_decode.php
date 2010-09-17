<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_modifier_utf8_decode($string)
{
	return utf8_decode($string);
}
