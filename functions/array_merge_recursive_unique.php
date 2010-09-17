<?

if (!function_exists('array_merge_recursive_unique')) {
	function array_merge_recursive_unique()
	{
		$arrays = func_get_args();
		$remains = $arrays;

		// We walk through each arrays and put value in the results (without
		// considering previous value).
		$result = array();

		// loop available array
		foreach($arrays as $array) {

			// The first remaining array is $array. We are processing it. So
			// we remove it from remaing arrays.
			array_shift($remains);

			// We don't care non array param, like array_merge since PHP 5.0.
			if(is_array($array)) {
				// Loop values
				foreach($array as $key => $value) {
					if(is_array($value)) {
						// we gather all remaining arrays that have such key available
						$args = array();
						foreach($remains as $remain) {
							if(array_key_exists($key, $remain)) {
								array_push($args, $remain[$key]);
							}
						}

						if(count($args) > 2) {
								// put the recursion
								$result[$key] = call_user_func_array(__FUNCTION__, $args);
							} else {
								foreach($value as $vkey => $vval) {
									$result[$key][$vkey] = $vval;
							}
						}
					} else {
						// simply put the value
						$result[$key] = $value;
					}
				}
			}
		}
		return $result;
	}
}
