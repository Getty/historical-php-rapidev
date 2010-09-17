<?

function wx_formElementHasValidator($element, $validatorName) {
	if (!isset($element['validators']) || empty($element['validators'])) {
		return false;
	}
	
	foreach ($element['validators'] as $validator) {
		if ($validatorName === $validator['type']) {
			return true;
		}
	}
	
	return false;
}