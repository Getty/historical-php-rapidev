<?

function str_to_uts($String) {
	$Time = @strtotime($String);
	if ($Time) {
		return @date('U',$Time);
	}
	return -1;
}