<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_modifier_debugprintr($var, $depth = 0)
{
    return RD_Util_HTMLDebug::VarDump($var);
}

