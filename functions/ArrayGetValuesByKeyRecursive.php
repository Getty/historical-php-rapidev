<?

function ArrayGetValuesByKeyRecursive($Array,$ByKey) {
	$Return = Array();
	if (!is_array($Array)) {
		return $Return;
	}
	foreach($Array as $Key => $Value) {
		if ($Key === $ByKey && !empty($Value)) {
			$Return[] = $Value;
		} elseif (is_array($Value)) {
			$Result = ArrayGetValuesByKeyRecursive($Value,$ByKey);
			if (!empty($Result)) {
				foreach($Result as $NewValue) {
					$Return[] = $NewValue;
				}
			}
		}
	}
	return $Return;
}