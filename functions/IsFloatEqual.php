<?
if (!function_exists('IsFloatEqual')) { // workarround connect/kehila migration
function IsFloatEqual($float_1, $float_2, $delta = 0.01) {
	return abs($float_1 - $float_2) < $delta;
}
}