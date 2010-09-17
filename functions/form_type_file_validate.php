<?

function form_type_file_validate($Element) {
	/* if (isset($Element['value']) && is_array($Element['value']) &&
		isset($Element['value']['tmp_name'])) {
		if (!empty($Element['value']['tmp_name']) && $Element['value']['size'] > 0) {
			if (!empty($Element['file_saved'])) {
				unlink($Element['file_saved']['tmp_name']);
				unset($Element['file_saved']);
			}
			$SavedFilename = $Element['value']['tmp_name'].'_rapidev_saved';
			copy($Element['value']['tmp_name'],$SavedFilename);
			$Element['file_saved'] = $Element['value'];
			$Element['file_saved']['tmp_name'] = $SavedFilename;
		} elseif (!empty($Element['file_saved'])) {
			$Element['value'] = $Element['file_saved'];
		}
	} */
	return $Element;
}