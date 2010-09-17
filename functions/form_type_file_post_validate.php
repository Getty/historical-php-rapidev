<?

function form_type_file_post_validate($Element) {
	/* if (!$Element['valid']) {
		$Element['value'] = Array();
		unset($Element['file_saved']);
	} */
	return $Element;
}