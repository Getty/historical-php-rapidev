<?

function notnull($var) {
	if ($var === 0 || $var === "0" || $var === NULL) {
		return "Dieser Wert darf nicht 0 sein";
	} else {
		return true;
	}
}
