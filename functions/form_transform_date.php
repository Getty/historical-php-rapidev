<?

function form_transform_date($main,$Form,$Element,$Value) {

	if (is_array($Value)) {

		if (!isset($Value['day'])) {
			$Value['day'] = 0;
		}
	
		if (!isset($Value['month'])) {
			$Value['month'] = 0;
		}

		if (!isset($Value['year'])) {
			$Value['year'] = 0;
		}

		if ($Value['day'] && $Value['month'] && $Value['year']) {
			return date("U", mktime(0, 0, 0, $Value['month'], $Value['day'], $Value['year']));
		}

	}

	return '';

}
