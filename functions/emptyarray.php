<?

function emptyarray($Val) {
	if (empty($Val)) {
		return true;
	} elseif (is_array($Val)) {
		$empty = true;
		foreach($Val as $Value) {
			if (!emptyarray($Value)) { $empty = false; }
		}
		return $empty;
	} else {
		return false;
	}
}