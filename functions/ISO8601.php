<?

function ISO8601($Timestamp) {
	$Date = date("c",$Timestamp);
	if ($Date) {
		$Date = str_replace('-','',$Date);
		$Date = str_replace(':','',$Date);
	}
	return $Date;
}