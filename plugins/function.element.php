<?

function smarty_function_element($Params, &$Main)
{
	return RD::$Self->ElementPlugin('element',$Params);	
}
