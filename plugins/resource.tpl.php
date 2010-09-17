<?

function smarty_resource_tpl_source($tpl_name, &$tpl_source, &$smarty)
{
	$tpl_source = $tpl_name;
    return true;
}

function smarty_resource_tpl_timestamp($tpl_name, &$tpl_timestamp, &$smarty)
{
	return true;
}

function smarty_resource_tpl_secure($tpl_name, &$smarty)
{
	return true;
}

function smarty_resource_tpl_trusted($tpl_name, &$smarty)
{
	return true;
}
