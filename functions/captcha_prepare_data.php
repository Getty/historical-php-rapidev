<?
function captcha_prepare_data() {
	$CaptchaData = Array();

	$ops = array(
		array("PLUS", "+"),
		array("MINUS", "-")
	);

	$op = $ops[mt_rand(0, count($ops) - 1)];

	$val1 = mt_rand(1, 100);
	$val2 = mt_rand(1, 20);

	switch ($op[0]) {

		case "PLUS":
			$result = $val1 + $val2;
			break;

		case "MINUS":
			if ($val1 < $val2) {
				$temp = $val1;
				$val1 = $val2;
				$val2 = $temp;
			} else if ($val1 === $val2) {
				$val1++;
			}
			$result = $val1 - $val2; // diese in session speichern
			break;

	}
	
	$CaptchaData['hash']	= md5(mt_rand());
	$CaptchaData['result']	= $result;
	$CaptchaData['string']	= $val1." ".$op[1]." ".$val2." = "; // diese in template ausgeben
	
	return $CaptchaData;
}