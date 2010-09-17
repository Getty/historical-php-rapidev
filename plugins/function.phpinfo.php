<?

function smarty_function_phpinfo($params, &$smarty)
{
	ob_start();
	phpinfo();
	$return = ob_get_contents();
	ob_end_clean();
	return $return;
}
