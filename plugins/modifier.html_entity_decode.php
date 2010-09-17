<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_modifier_html_entity_decode($string)
{
	return html_entity_decode($string);
}
