<?

function smarty_prefilter_tplname($tpl_source,&$smarty)
{
	if ($smarty->_current_file != 'evaluated template') {
		$new_source = "\n\n<!-- ".$smarty->_current_file." START -->\n\n".$tpl_source."\n\n<!-- ".$smarty->_current_file." END -->\n\n";
	} else {
		$new_source = $tpl_source;
	}
	return $new_source;
}