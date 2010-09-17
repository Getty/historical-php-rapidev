<?

function ArraySortBy($Array,$ValueKey,$SortFlag = SORT_NUMERIC,$Reverse = false, $DefaultValue = NULL) {
	$Keys = Array();
	if (is_array($ValueKey)) {
		$ValueFunction = $ValueKey[1];
		$ValueKey = $ValueKey[0];
	}
	foreach($Array as $Key => $SubArray) {
		if (is_array($SubArray)){
			if (array_key_exists($ValueKey,$SubArray)) {
				if (isset($ValueFunction)) {
					$Keys[$Key] = $ValueFunction($SubArray[$ValueKey]);
				} else {
					$Keys[$Key] = $SubArray[$ValueKey];
				}
			} else {
				$Keys[$Key] = $DefaultValue;
			}
		}
	}
	if ($Reverse) {
		arsort($Keys,$SortFlag);				
	} else {
		asort($Keys,$SortFlag);		
	}
	$Result = Array();
	foreach($Keys as $Key => $Value) {
		$Result[] = $Array[$Key];
	}
	return $Result;
}